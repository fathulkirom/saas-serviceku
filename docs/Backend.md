# ServiceKU — Backend

> Standar backend Laravel (Laravel 12). Berdasarkan source code saat ini. Prinsip: controller ber-role, pemisahan central vs tenant, validasi di FormRequest bila memungkinkan, dan setiap resource tenant dilindungi middleware fitur plan.

---

## 1. Stack & Versi

- PHP `^8.2`, `laravel/framework ^12.0`
- Multi-tenancy `stancl/tenancy ^3.10`
- `inertiajs/inertia-laravel ^3.1` (server-side Inertia)
- `tightenco/ziggy ^2.6` (route helper untuk frontend)
- `sentry/sentry-laravel`, `midtrans/midtrans-php`, `pragmarx/google2fa-laravel`, `barryvdh/laravel-dompdf`, `laravel/socialite`, `laravel/sanctum`, `laravel/reverb`, `google/apiclient`
- Testing: `phpunit ^11` + Playwright (e2e)

---

## 2. Bootstrap & Konfigurasi Global

`bootstrap/app.php` (pola Laravel 11/12) menetapkan:

- **Routing**: `web` → `routes/web.php`, `api` → `routes/api.php` (prefix `api`), `commands`, `channels`, health check `/up`.
- **Rate limiter**: `login` (6/menit), `register` (3/menit), `otp` (2/menit), `api` (60/menit).
- **Scheduler**: backup, gdrive upload, `subscription:check`, `tenants:cleanup`, `queue:retry`, `cache:clear`, `session:gc`.
- **Middleware alias**:
  - `check.subscription` → `CheckSubscription`
  - `check.plan.feature` → `CheckPlanFeature`
  - `tenant.auth` → `RedirectIfNotTenant`
  - `tenancy.session` → `InitializeTenancyBySession`
  - `admin.auth` → `AdminAuthenticate`
  - `verified`, `cors`, `ensure.json`
- **Web group append**: `HandleInertiaRequests`, `SecurityHeaders`.
- **API group**: `throttle:api` + `EnsureJsonForApi` + `HandleCors`.
- `trustProxies(at: '*')` (Cloudflare/LB).
- **CSRF exception**: `payment/webhook`, `customers/ajax-store`.
- **Exceptions**: Sentry (non-local), JSON 401 untuk `/api/*`, render Inertia error pages untuk 403/404/419/500/503 (non-local).

---

## 3. Routes

| File | Baris | Isi |
|---|---|---|
| `routes/web.php` | 219 | Landing, auth central, admin panel, tracking publik, payment webhook, voucher |
| `routes/tenant.php` | 366 | Seluruh route tenant (dalam group `tenancy.session` + `auth` + `check.subscription`) |
| `routes/api.php` | 27 | API JSON publik/tenant (mis. tracking, service) |
| `routes/console.php` | 8 | `inspire` |
| `routes/channels.php` | 7 | Broadcast channel `tenant.{id}` |

### Konvensi Route Tenant
- Semua route tenant **wajib** berada dalam group middleware `tenancy.session, auth, check.subscription`.
- Setiap resource dijaga dengan `middleware('check.plan.feature:<feature>')` — `<feature>` harus cocok dengan key fitur plan (`services`, `customers`, `products`, `sales`, `reports`, `multi_branch`, `transfer_stock`, `users`, `expenses`, `purchases`, `deposits`, `checklist`, `indents`, `cash_register`, `master_data`, `monitoring`, `settings`).
- Nama route memakai **dot notation** (`services.store`, `sales.print`).
- Endpoint aksi/status memakai `POST` dengan nama deskriptif (`services.accept`, `services.finish`).

---

## 4. Controllers

Jumlah: **104** — `Tenant/` (78), `Admin/` (9), `Auth/` (7), `Api/` (2), root (8).

### Pola
- **Resource controller** untuk CRUD standar (mis. `CustomerController`, `ProductController`).
- **Workflow controller** untuk aksi stateful (mis. `ServiceWorkflowController`, `SaleStoreController`) — menampung transisi status, idempotensi, transaksi DB.
- Controller mengembalikan **Inertia** untuk halaman: `return inertia('Page/Name', props)`.
- Aksi mutasi biasanya `POST` + redirect back (`->back()`) dengan flash (`success`/`error`).
- **Fat controller**: sebagian besar logika bisnis berada di controller (bukan service/domain layer). Integrasi eksternal di `app/Services/*`. Halaman yang memerlukan penyederhanaan → ekstrak ke service/action class (best practice, belum seragam).

### Contoh Signature
```php
public function store(StoreServiceRequest $request) // pakai FormRequest
{
    $this->authorize('create', Service::class);     // pakai Policy
    // ... logic
    return back()->with('success', 'Servis dibuat.');
}
```

---

## 5. Models

### Central (`app/Models/`) — 11 model, connection `central`
`Tenant` (HasDatabase+HasDomains, `data` JSON untuk business_type), `Plan` (features JSON), `Payment`, `Voucher`, `User` (admin), `SystemSetting`, `SystemLog`, `TenantStat`, `TenantOtp`, `RegistrationVerification`, `GoogleDriveToken`.

### Tenant (`app/Models/Tenant/`) — 92 model, connection tenant dinamis
`User`, `Service`, `Sale`, `Customer`, `Product`, `Branch`, `Expense`, `Indent`, `InventoryMutation`, `CashRegister`, `DailyDeposit`, `ChecklistTemplate`, `TenantSetting`, `ActivityLog`, dll. + `Traits/HasRoles.php`, `Traits/HasCustomFields.php`. Sebagian dikonsolidasi dalam 4 file multi-class (`RetailModels.php`, `WarehouseModels.php`, `InventoryModels.php`, `DailyOpsModels.php`).

### Konvensi Model
- **Central**: set `protected $connection = 'central';`
- **Tenant**: biarkan connection default (stancl mengarahkan ke DB tenant aktif).
- Gunakan `$casts` untuk `json`/`array`/`datetime`/`boolean`/`hashed`.
- Nama tabel **snake_case jamak** (`service_spareparts`, `checklist_items`).
- Helper domain (state machine, status label) bisa hidup di model (contoh: `Service::canTransitionTo`, `Payment::getStatusLabel`).
- **Tidak ada query antar tenant** di model tenant.

---

## 6. Middleware (`app/Http/Middleware/`) — 11

| Middleware | Alias | Fungsi |
|---|---|---|
| `AdminAuthenticate` | `admin.auth` | Guard panel superadmin |
| `CheckPlanFeature` | `check.plan.feature` | Gate fitur plan (none/read_only/full) per route |
| `CheckSubscription` | `check.subscription` | Auto-expire trial/langganan, blokir kecuali route upgrade (`pengaturan.index`, `settings.*`, `payment.*`, `billing.apply-voucher`) |
| `EnsureJsonForApi` | `ensure.json` | Paksa JSON untuk `/api/*` |
| `HandleCors` | `cors` | CORS |
| `HandleInertiaRequests` | — | Shared props Inertia (auth, tenant, plan_access, role_permissions, flash, dll) |
| `InitializeTenancyBySession` | `tenancy.session` | Initialize tenancy dari `session('tenant_id')` |
| `RedirectIfNotTenant` | `tenant.auth` | Guard route tenant |
| `SecurityHeaders` | — | CSP, X-Frame-Options, nosniff, HSTS |
| `TrustProxies` | — | No-op (konfigurasi sebenarnya di `bootstrap/app.php`) |

---

## 7. Validasi

- **FormRequest** (`app/Http/Requests/`): `StoreCustomerRequest`, `StoreExpenseRequest`, `StoreSaleRequest`, `StoreServiceRequest` (+ `Tenant/` duplikasi — konsolidasi disarankan).
- Sebagian besar validasi masih **inline** `$request->validate([...])` di controller (105 titik). Standar yang diinginkan: **gunakan FormRequest** untuk request mutasi kompleks; inline `validate` hanya untuk payload kecil.
- Gunakan `authorize()` dari **Policy** untuk otorisasi aksi.

---

## 8. Policies — 14 (semua terdaftar di `AuthServiceProvider`)

`ServicePolicy`, `SalePolicy`, `CustomerPolicy`, `ProductPolicy`, `BranchPolicy`, `ExpensePolicy`, `PurchasePolicy`, `CashRegisterPolicy`, `DailyDepositPolicy`, `IndentPolicy`, `InventoryMutationPolicy`, `SupplierPolicy`, `TenantUserPolicy`, `RequestPolicy`.

- `$this->authorize(...)` dipakai di workflow controller utama (mis. `ServiceWorkflowController`).
- CRUD sederhana boleh memakai cek role (`canManageX()` dari `HasRoles`) bila policy belum diterapkan — target: penuhi `authorize()` di semua aksi mutasi.

---

## 9. Services — `app/Services/` (31 file, 22 top-level + subdirs)

Mencakup integrasi eksternal **dan** domain service/engine:

- `PaymentGatewayService` — Midtrans (snap, webhook, status).
- `GoogleDriveService` / `GoogleDrivePhotoService` — backup & foto servis ke Drive.
- `MailConfigService` — konfigurasi SMTP dari DB (`SystemSetting`) di `AppServiceProvider::boot`.
- `WhatsAppService` — gateway WA (Fonnte).
- `FeatureFlagService` — toggle fitur global (2FA, email verification).

---

## 10. Jobs / Mail / Notifications

- **Jobs** (`app/Jobs/`): `GenerateInvoicePdf` (dompdf), `SendInvoiceEmail` — dipakai untuk invoice sale.
- **Mail** (`app/Mail/`): `OtpMail` (registrasi), `WelcomeMail`.
- **Notifications** (`app/Notifications/`): `ResetPasswordNotification`, `TwoFactorCodeNotification`, `VerifyEmailNotification` (dipakai); `TenantRegistered` (belum dipakai).

---

## 11. Console & Scheduler

Command: `backup:run`, `subscription:check`, `tenants:cleanup`. Scheduler di `bootstrap/app.php` (lihat `docs/Architecture.md` §7). Gunakan `php artisan` untuk perintah maintenance; jangan menambah job logika bisnis ke web request bila bisa di-queue.

---

## 12. Multi-Tenancy Backend (`config/tenancy.php` + `TenancyServiceProvider`)

- Tenant model: `App\Models\Tenant` (UUID), `HasDatabase` + `HasDomains`.
- DB tenant: prefix `tenant_` + UUID; SQLite (dev) / MySQL (prod).
- Migrasi tenant otomatis saat `TenantCreated` (sinkron).
- `tenants:migrate` untuk menjalankan migrasi tenant (path `database/migrations/tenant`).
- Bootstrappers aktif: Database, Filesystem, Queue.
- **Jangan** menonaktifkan/memodifikasi bootstrapper tanpa memahami konsekuensi isolasi data.

---

## 13. Permission & Feature (Subscription-driven)

- **Plan features**: JSON bersarang `features[business_type][feature] = full|read_only|none` (lihat `Plan::featureAccessLevel`).
- **Business types tenant**: `full_service`, `aksesoris_service`, `aksespare_service`, `gadget_full`, `retail_only` (lihat `Tenant::getBusinessTypes`).
- **Akses efektif**: `Tenant::getFeatureAccessLevel(feature)` → dipakai `CheckPlanFeature` dan shared prop `plan_access` frontend.
- **Role user tenant** (string): `owner`, `admin`, `manager`, `head_store`, `cs`, `technician`, `cashier`, `courier`, `custom` — dengan method `canX()` di `Traits/HasRoles` dan matriks `role_permissions` (frontend, lihat `docs/Frontend.md`).

---

## 14. Testing

- `phpunit.xml`: suites `Unit` + `Feature`, DB `sqlite :memory:`, `QUEUE_CONNECTION=sync`, `SESSION_DRIVER=array`, `APP_ENV=testing`.
- 44 Feature + 12 Unit, termasuk isolasi tenancy, idempotensi sale, branch guard, policy, model.
- Jalankan: `php artisan test`.
- e2e Playwright: `npm run test:e2e` (spec `dashboard`, `login`).
