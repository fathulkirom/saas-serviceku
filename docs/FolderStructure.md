# ServiceKU — Folder Structure

> Struktur folder resmi proyek (berdasarkan kondisi source code saat ini). Setiap folder baru harus mengikuti konvensi di bawah ini.

---

## Root

```
saas/
├── app/                  # Kode backend Laravel (lihat di bawah)
├── artisan               # Entry CLI Laravel
├── bootstrap/            # Bootstrap aplikasi + cache
│   ├── app.php           # Konfigurasi middleware, scheduler, exceptions, rate limiter
│   └── providers.php
├── composer.json         # Dependensi PHP (Laravel 12)
├── config/               # Konfigurasi Laravel + tenancy + services
├── database/
│   ├── factories/        # UserFactory
│   ├── migrations/       # 19 migrasi CENTRAL (platform)
│   ├── migrations/tenant # 30 migrasi TENANT (business per toko)
│   ├── seeders/          # PlanSeeder, DemoSeeder, TenantSeeder, MasterDataSeeder, ...
│   └── tenant_*/         # SQLite per-tenant (runtime, GITIGNORED)
├── docs/                 # Dokumentasi resmi proyek (ini)
├── docker/               # Cloudflare, MySQL (infra)
├── e2e/                  # Playwright (dashboard.spec.js, login.spec.js)
├── lang/id/              # Terjemahan (Indonesia)
├── public/               # Assets publik + build Vite (public/build)
├── resources/
│   ├── css/              # app.css, themes.css (design tokens)
│   ├── js/               # Frontend Vue (lihat di bawah)
│   └── views/            # Blade: app.blade.php, welcome.blade.php + legacy
├── routes/               # web.php, tenant.php, api.php, console.php, channels.php
├── scripts/              # Script release/ops
├── storage/              # Log, cache, upload, framework
├── tests/                # PHPUnit (Feature 44, Unit 12)
├── vendor/               # Composer
├── package.json          # Dependensi frontend
├── vite.config.js        # Vite + Laravel plugin + PWA
├── tailwind.config.js    # Tailwind
├── postcss.config.js
└── .env                  # Konfigurasi lingkungan (tidak di-commit)
```

---

## Backend — `app/`

```
app/
├── Console/
│   └── Commands/         # BackupRun, CheckSubscriptionExpiry, TenantCleanup
├── Events/               # ServiceStatusUpdated (broadcast, saat ini tidak dipakai)
├── Http/
│   ├── Controllers/
│   │   ├── Admin/        # 9 controller superadmin (central)
│   │   ├── Api/          # ServiceController, TrackingController (JSON publik)
│   │   ├── Auth/         # Login, 2FA, email verify, reset, Google, register tenant
│   │   ├── Tenant/       # 54 controller business (per-tenant)
│   │   └── *.php         # Dashboard, PublicTracking, TenantLookup, VoucherApply, DevLogin
│   ├── Middleware/       # 10 middleware (lihat docs/Backend.md)
│   └── Requests/         # FormRequest (StoreCustomerRequest, StoreExpenseRequest, dst)
├── Jobs/                 # GenerateInvoicePdf, SendInvoiceEmail
├── Mail/                 # OtpMail, WelcomeMail
├── Models/
│   ├── *.php             # 11 model CENTRAL (Tenant, Plan, Payment, Voucher, ...)
│   └── Tenant/           # 46 model TENANT + Traits/ (HasRoles, HasCustomFields)
├── Notifications/        # ResetPassword, TwoFactorCode, VerifyEmail, TenantRegistered
├── Policies/             # 13 policy (Service, Sale, Customer, Product, ...)
├── Providers/            # AppServiceProvider, AuthServiceProvider, TenancyServiceProvider
├── Services/             # 6 service integrasi (Payment, GoogleDrive, Mail, WhatsApp, FeatureFlag)
```

---

## Frontend — `resources/js/`

```
resources/js/
├── app.js                # Entry Inertia + Vue (resolve page glob, Ziggy route)
├── Components/           # Komponen reusable
│   ├── K*.vue            # PRIMITIF STANDAR: KButton, KInput, KTextarea, KSelect,
│   │                     #   KCheckbox, KRadio, KBadge, KCard, KDialog, KDrawer,
│   │                     #   KAvatar, KLoading, KTable, KModal (alias KDialog)
│   ├── Badge.vue, Drawer.vue, Dropdown.vue, DropdownLink.vue, EmptyState.vue,
│   ├── PageHeader.vue, Pagination.vue, ProgressBar.vue, Skeleton.vue,
│   ├── StatCard.vue, TabPage.vue, ThemeSwitcher.vue, Toast.vue, Logo.vue,
│   ├── DynamicFormFields.vue, Icons.js
│   ├── Services/         # Komponen khusus halaman detail servis
│   │   ├── ServiceHeader, ServiceActionBar, ServiceStatusStepper,
│   │   ├── ServiceInfoCards, ServiceSections, ServicePhotos, ServiceHistory,
│   │   └── Service*Modal.vue (Assign/Cancel/Partner/Complete/Checklist)
│   └── ui/               # LEFTOVER shadcn-vue (Button/Card/Input/Label) — TIDAK dipakai
├── Composables/
│   ├── useFormatter.js   # formatNumber, formatCurrency, formatDate, getInitials, ...
│   ├── useToast.js       # Toast singleton (setToastInstance + helpers)
│   ├── useServiceStatus.js # status servis: label/warna/timeline/format (Services)
│   └── layoutHelpers.js  # groupColors, getGroupAccent, isActive (layout)
├── Layouts/
│   ├── AuthenticatedLayout.vue  # Facade tenant: filter menu 4 lapis + branding
│   ├── AdminLayout.vue          # Layout panel superadmin
│   ├── GuestLayout.vue          # Layout auth/guest
│   └── Themes/
│       ├── LayoutNew.vue        # Orkestrator layout utama (tenant)
│       ├── Sidebar.vue          # Sidebar + nav + branch switcher
│       ├── HeaderBar.vue        # Topbar + user menu + clock
│       └── GlobalSearch.vue     # Modal search global (Cmd/Ctrl+K)
├── Pages/                # Halaman Inertia (di-resolve dari nama route)
│   ├── Admin/            # Panel superadmin
│   ├── Auth/             # Login, Register, 2FA, Verify, Reset
│   ├── Customers/ Sales/ Services/ Reports/ Monitoring/ Tools/ Users/
│   ├── Dokumen/ Inventaris/ Kas/ Keuangan/ Pengaturan/ ServisTools/ Sistem/
│   ├── Landing/ Public/ Onboarding/ Profile/ Tenant/ Errors/
│   └── *.vue             # Dashboard, TechnicianDashboard, CsDashboard, ...
├── Utils/statusMaps.js   # Peta status ke label/warna
└── lib/utils.js          # cn() (twMerge + clsx) — dipakai ui/ leftover
```

> **Aturan lokasi halaman**: nama route Inertia = path relatif dari `Pages/` tanpa ekstensi. Contoh: `inertia('Services/Show')` → `Pages/Services/Show.vue`. Komponen sub-halaman besar diletakkan di `Components/` (mis. `Components/Services/`), bukan di `Pages/`.

---

## Database — `database/`

```
database/
├── migrations/           # Central (19): users, tenants, domains, plans, vouchers,
│                         #   payment_transactions, system_settings, system_logs, ...
├── migrations/tenant/    # Tenant (30): users, services, customers, products, sales,
│                         #   expenses, branches, inventory_mutations, tenant_settings,
│                         #   checklist_templates, cash_registers, daily_deposits, ...
├── seeders/              # PlanSeeder, SystemSettingSeeder, DemoDataSeeder, ...
└── factories/
```

> `database/tenant_*` dan `database/testing_tenant_*` adalah **artefak runtime** SQLite dan sudah di-`gitignore` (jangan commit, jangan hapus manual).

---

## Konfigurasi — `config/`

`app.php`, `auth.php`, `broadcasting.php`, `cache.php`, `database.php`, `filesystems.php`, `logging.php`, `mail.php`, `queue.php`, `sentry.php`, `services.php`, `session.php`, **`tenancy.php`**.

---

## Aturan Penempatan File (Rule of Thumb)

| Jenis | Lokasi |
|---|---|
| Controller tenant (business) | `app/Http/Controllers/Tenant/` |
| Controller superadmin (platform) | `app/Http/Controllers/Admin/` |
| Controller auth | `app/Http/Controllers/Auth/` |
| Model data tenant | `app/Models/Tenant/` |
| Model data platform | `app/Models/` |
| Integrasi eksternal (payment/Drive/mail/WA) | `app/Services/` |
| Komponen UI reusable | `resources/js/Components/` |
| Komponen khusus 1 modul | `resources/js/Components/<Modul>/` |
| Halaman Inertia | `resources/js/Pages/<Modul>/` |
| Helper frontend | `resources/js/Composables/` |
| Migrasi tenant | `database/migrations/tenant/` |
| Migrasi central | `database/migrations/` |
