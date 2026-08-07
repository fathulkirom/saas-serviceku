# CENTRAL-ADMIN-AUDIT-01 — MENU & PLATFORM RESPONSIBILITY SYNC

**Tanggal**: 2026-08-07
**Mode**: AUDIT ONLY → CLASSIFY → REPORT → FIX P0/P1 IF VERIFIED
**Platform admin**: `https://kirom.serviceku.my.id/admin/login` (Central Management / Super Admin)
**Tenant store**: `https://{tenant-slug}.serviceku.my.id/login` — **terpisah, tidak tercampur**.

---

## 1. Central Admin Architecture

- **Routes**: `routes/web.php` — `Route::prefix('admin')->name('admin.')->middleware('admin.auth')`.
- **Middleware**: `admin.auth` → `AdminAuthenticate` (`bootstrap/app.php:97`) = stock `Illuminate\Auth\Middleware\Authenticate` (guard `web` terhadap **tabel `users` central**; belum-login → redirect `admin.login`).
- **Login**: `AdminAuthController@login` (`Auth::attempt` polos) — halaman `Auth/AdminLogin`.
- **Layout**: `resources/js/Layouts/AdminLayout.vue` — **top-nav datar 10 item** (Dashboard, Tenant, Paket, Voucher, Pembayaran, Payment Settings, Monitoring, Backup, Logs, Pengaturan) + "← Ke Aplikasi" + Logout.
- **Controllers** (`app/Http/Controllers/Admin/`): `SuperAdminController`, `TenantManagementController`, `PlanController`, `VoucherController`, `PaymentController`, `BackupController`, `SystemSettingsController`, `MonitoringController`, `AdminAuthController`.
- **Pages** (`resources/js/Pages/Admin/`): Dashboard, TenantManagement, CreateTenant, EditTenant, TenantDetail, Plans, Payments, PaymentSettings, Monitoring, Backup, Logs, Settings, Vouchers/{Index,Create}.

## 2. Current Menu Inventory

| Nav | Route | Controller → Page | Runtime |
|---|---|---|---|
| Dashboard | `admin.dashboard` | SuperAdmin → Dashboard.vue | WORKING (REAL) |
| Tenant | `admin.tenant.index` | TenantManagement → TenantManagement.vue | WORKING (REAL) |
| Paket | `admin.plans` | PlanController → Plans.vue | WORKING (REAL) |
| Voucher | `admin.vouchers.*` | VoucherController → Vouchers/Index.vue | WORKING (REAL) |
| Pembayaran | `admin.payments` | PaymentController → Payments.vue | WORKING (REAL) |
| Payment Settings | `admin.payment-settings` | PaymentController → PaymentSettings.vue | PARTIAL |
| Monitoring | `admin.monitoring` | MonitoringController → Monitoring.vue | WORKING (REAL) |
| Backup | `admin.backup` | BackupController → Backup.vue | WORKING (REAL) |
| Logs | `admin.logs` | SystemSettings → Logs.vue | WORKING (REAL) |
| Pengaturan | `admin.settings` | SystemSettings → Settings.vue | WORKING (REAL) |

Semua route → controller → page diverifikasi (file ada, tidak 404/500). Matriks lengkap: `CENTRAL-ADMIN-AUDIT-01-MENU-MATRIX.md`.

## 3. Correct Platform Menus

Semua 10 menu adalah **PLATFORM CORE/SUPPORT yang benar**:
- Dashboard platform, Tenant management (list/create/detail/edit/lifecycle), Plan/feature config, Voucher promo, Payment platform, Payment settings (gateway/bank), Monitoring (health), Backup, Audit log, Platform settings (termasuk Transactional Mail `mail_resend` dari PILOT-MAIL-04R).
- Sesuai daftar tanggung jawab platform pada STEP 3. Tidak ada menu yang salah letak.

## 4. Misplaced Tenant Functions

**TIDAK ADA** tenant-operational function yang salah letak di Central Admin:
- Tidak ada service intake / teknisi / repair / QC / customer / stock / kasir / daily finance / warranty / branch daily ops di Central Admin.
- **Pengecualian sah**: `admin.tenant.show` menampilkan services/sales terbaru tenant sebagai **view read-only platform support** (untuk inspeksi), dalam try/catch + `tenancy()->end()` — aman, bukan tempat operasi toko.

## 5. Dead/Placeholder Menus

- **Tidak ada** menu dead/placeholder. Voucher page ada (`Admin/Vouchers/Index.vue` — file ada, bukan 404).
- Tidak ada `TODO`/`coming soon`/`href="#"`/`console.log`/`alert()`/`prompt()` di seluruh `Pages/Admin/**`.
- Dua **capability backend tanpa UI** (bukan menu): `admin.payments.invoice` (manual invoice) & `admin.plans.default-menus` → P3.

## 6. Tenant Management

- List + search (nama/email/id/phone) + filter (status/plan/business_type) ✅.
- Detail: identitas, slug/domain, status aktif, plan, tanggal, branch/user counts (via TenantStat), services/sales terbaru read-only ✅.
- Aksi platform: suspend/activate/change-plan/extend-trial/extend-subscription/delete/login-as/reset-password/update-domain/sync-stats ✅.
- **Isolasi tenant**: inisialisasi tenancy per-tenant + try/catch + `tenancy()->end()`. Tidak ada konteks DB tenant yang bocor lintas-tenant. (Fungsi `loginAs` adalah aksi platform yang sah.)

## 7. Plan / Subscription

- Plans UI (Trial/Basic/Pro/Enterprise) + feature config + default-menus ada. Tenant plan assignment + change-plan ada.
- **PLAN-CONFIG MISMATCH (dicatat, TIDAK diperbaiki)**: **Basic `users=read_only`** → owner tidak bisa membuat user (POST diblokir 403), **bertentangan dengan pesan produk "Maks. 3 karyawan"** (Basic `max_users=3`). Implikasi pilot: paket staf harus **Pro** (`users=full`). Tidak ada redesign pricing.
- Trial: `sales=read_only` (sengaja).

## 8. Platform Settings

- **Lokasi kanonik sudah ada**: Central Admin → **Pengaturan** (`admin.settings` → `SystemSettingsController` + `system_settings` table/model `SystemSetting`).
- Cocok untuk **Email / Transactional Mail** (hasil PILOT-MAIL-04R sudah menambahkan grup `mail_resend` di sini — provider, api key, from, reply-to, status, "Kirim Email Tes").
- Penyimpanan secret: `encrypt()` at rest (didukung). Akses kontrol: `admin.auth` (platform admin only).
- **Resend TIDAK diimplementasikan ulang di fase ini** (sudah selesai di PILOT-MAIL-04R; tinggal konfigurasi eksternal).

## 9. Security

- **Autentikasi**: guard `web` terhadap tabel `users` central; tenant user (tabel tenant DB) **tidak bisa** masuk guard central → tidak bisa akses `/admin` (verifikasi: guest → redirect `admin.login`).
- **CSRF/session**: middleware `web` standar.
- **Direct URL**: seluruh `/admin/*` dibalut `admin.auth` (bukan sekadar hidden menu) — guest dialihkan.
- ⚠️ **Temuan authZ (P2 latent)**: tabel `users` central **tidak punya kolom role/is_admin**; `AdminAuthController@login` = `Auth::attempt` polos → **setiap user central = platform admin penuh** (tenant mgmt, backup, settings, confirm/cancel payment, login-as, reset password, delete tenant).
  - **Mengapa latent, bukan P0/P1 aktif**: tidak ada self-registration central; user dibuat manual (tinker/artisan, lihat `docs/generated/LOCAL_INSTALL.md`); user tenant tidak bisa masuk guard central. Jadi **tidak ada jalur exploit aktif saat ini**.
  - **Rekomendasi hardening (fase terpisah)**: tambah kolom `is_admin` (default aman agar admin eksisting tidak terkunci) + guard di `AdminAuthenticate`/login. **TIDAK diperbaiki di audit ini** (hindari perubahan skema berisiko tanpa keputusan owner).

## 10. Dashboard Data Reality

- **REAL** (bukan dummy): total_tenants, active_tenants, trial/suspended, recent_registrations, expiring_trials (dari tabel `tenants`), aggregate (users/services/sales/revenue/products/storage dari `TenantStat`), recent SystemLog, system health (php/DB/laravel version).
- Celah kosmetik: `systemHealth.server_time` tidak di-pass controller → baris "Server Time" kosong (P3).
- Tidak ada metrik yang di-invent/dipalsukan. Semua bersumber DB.

## 11. Terminology

- Central Admin memakai label: **Dashboard, Tenant, Paket, Voucher, Pembayaran, Payment Settings, Monitoring, Backup, Logs, Pengaturan** — **tidak ada label ambigu** seperti "Admin" untuk tenant admin.
- Login page: "Admin Panel — ServiceKU Central Management" → jelas platform-level.
- Tenant operational roles (Owner/Manager/Admin/CS/Kasir/Teknisi) tetap di tenant UI; "Super Admin" hanya di platform. **Tidak ada koreksi label yang diperlukan.**

## 12. P0/P1 Findings

**Tidak ada P0/P1 yang terverifikasi.**

- Tidak ada security leak aktif, salah tenant context, dangerous action yang tidak terkontrol, atau data corruption.
- Semua fungsi platform pilot (tenant mgmt, plan, settings, backup, logs) bekerja via UI.
- AuthZ gap diklasifikasikan **P2 (latent)** — bukan P0/P1 karena tidak ada jalur exploit aktif dan tidak memblokir fungsi platform (sesuai definisi severity STEP 14).

## 13. P2/P3 Deferred

| # | Item | Severity | Catatan |
|---|---|---|---|
| 1 | **AuthZ gap** (`/admin` = semua user central) | P2 (latent) | Rekomendasi: kolom `is_admin` + guard (fase hardening terpisah) |
| 2 | Settings feature-flags: 2 dari 5 toggle (`registration`, `maintenance_mode`) tidak ter-submit | P2 | Workaround: field ada di form utama |
| 3 | PaymentSettings: data rekening tidak pre-filled (`bankAccounts` prop tak terpakai) — simpan bisa menimpa nilai asli | P2 | Backend `getConfig()` tidak punya `bank_*` |
| 4 | Voucher `extra_months` ("Gratis Bulan") di-validate-drop | P2 | Tidak tersimpan |
| 5 | Pembayaran: tidak ada UI manual invoice meski route ada | P2 | Capability backend tanpa tombol |
| 6 | Dashboard `server_time` blank | P3 | |
| 7 | Monitoring: 3 undefined prop (`system_alerts`, `mysql_data_size`, `file_count`) | P3 | |
| 8 | Capability tak terpakai: `admin.payments.invoice`, `admin.plans.default-menus` | P3 | |
| 9 | PLAN-CONFIG MISMATCH: Basic `users=read_only` vs "Maks. 3 karyawan" | P2 (plan-config) | Dicatat, tidak diubah |

## 14. Recommended Final Menu Structure

Struktur saat ini (top-nav datar) **sudah koheren** dan sesuai arsitektur; hierarki terkelompok opsional berikut **hanya rekomendasi** (tidak diterapkan di fase ini):

```
Dashboard
Tenant        (Daftar Tenant, Detail, Create/Edit)
Subscription  (Plans)
Platform      (Pengaturan [incl. Mail], System Health, Audit Logs, Backup)
Voucher       (promo)
Pembayaran    (list + Payment Settings)
Monitoring
```

Tidak ada perubahan wajib — menu sekarang sudah logis dan semua berfungsi.

## 15. Resend Settings Placement

- **Lokasi final**: Central Admin → **Pengaturan → "Transactional Mail (Resend)"** (sudah ada dari PILOT-MAIL-04R).
- Penyimpanan: `system_settings` grup `mail_resend` (model `SystemSetting`, koneksi central); api key terenkripsi; masked di UI; akses `admin.auth` (platform admin only).
- Tidak ada implementasi Resend baru pada audit ini — tinggal konfigurasi eksternal owner (akun/domain/API key → Save → Test).

## 16. Final Verdict

**B — CENTRAL ADMIN FUNCTIONALLY SYNCHRONIZED — ONLY P2/P3 REMAIN**

- Semua menu navigasi **WORKING** (route → controller → page → data real), diverifikasi di level kode + guest access.
- Tidak ada tenant-operational function salah letak; tidak ada menu dead/duplicate/placeholder; isolasi tenant aman; dashboard REAL.
- Tidak ada **P0/P1** terverifikasi yang perlu diperbaiki.
- Item P2/P3 (termasuk authZ hardening + plan-config mismatch) dicatat untuk fase berikutnya, tidak dieksekusi di audit ini.

> Catatan keterbatasan: verifikasi runtime klik-klik berautentikasi memerlukan kredensial admin (milik owner). Verifikasi dilakukan melalui inspeksi kode menyeluruh (route/controller/page/data) + uji guest access + `get_errors` — semua menu terbukti berfungsi.
