# CENTRAL-ADMIN-AUDIT-01 — MENU MATRIX

**Tanggal**: 2026-08-07
**Mode**: AUDIT → CLASSIFY → REPORT → FIX P0/P1 IF VERIFIED
**Skala**: Platform (Super Admin) — `https://kirom.serviceku.my.id/admin/*`

**Klasifikasi**: A=Platform Core · B=Platform Support · C=Tenant Misplaced · D=Duplicate · E=Dead/Placeholder · F=Legacy · G=Security Risk
**Severity**: P0=security/tenant-context/data · P1=blocks platform function · P2=workaround · P3=polish
**Runtime**: WORKING (route→controller→page→data real) · PARTIAL (ada celah) · DEAD · MISPLACED · SECURITY

| Menu | Submenu | Route | Runtime | Responsibility | Class | Severity | Action |
|---|---|---|---|---|---|---|---|
| Dashboard | — | `admin.dashboard` (`SuperAdminController@dashboard` → `Admin/Dashboard.vue`) | WORKING — data REAL (count tenant, TenantStat aggregate, SystemLog, health) | Platform dashboard | A | P3 (`server_time` blank) | Deferred |
| Tenant | Daftar Tenant | `admin.tenant.index` (`TenantManagementController@index` → `Admin/TenantManagement.vue`) | WORKING — REAL (paginated tenants + filter search/status/plan/business_type) | Tenant management (list/cari) | A | — | none |
| Tenant | Create Tenant | `admin.tenant.create` / `store` (`CreateTenant.vue`) | WORKING — REAL (provision tenant+DB+migrasi+branch+owner+domain) | Tenant provisioning | A | — | none |
| Tenant | Tenant Detail | `admin.tenant.show` (`TenantDetail.vue`) | WORKING — REAL; tenant DB dibaca read-only (services/sales terbaru) dalam try/catch + `tenancy()->end()` — aman, tidak bocor lintas-tenant | Inspect + platform actions | A | — | none |
| Tenant | Edit Tenant | `admin.tenant.edit` / `update` (`EditTenant.vue`) | WORKING — REAL | Edit metadata tenant | A | — | none |
| Tenant | Aksi: suspend / activate / change-plan / extend-trial / extend-subscription / delete / login-as / reset-password / update-domain / sync-stats | `admin.tenant.*` | WORKING — REAL (platform powers) | Platform lifecycle control | A | — | none |
| Paket | — | `admin.plans` (`PlanController@index`/`store`/`update`/`default-menus` → `Admin/Plans.vue`) | WORKING — REAL | Plan/feature config | A | — | none |
| Voucher | — | `admin.vouchers.*` (`VoucherController` → `Admin/Vouchers/Index.vue` + `Create.vue`) | WORKING — REAL | Promo voucher (platform support) | B | P2 (`extra_months` field di-validate-drop) | Deferred |
| Pembayaran | — | `admin.payments` (`PaymentController@index` → `Admin/Payments.vue`) | WORKING — REAL | Payment list platform | A | P2 (tidak ada UI manual invoice meski route `admin.payments.invoice` ada) | Deferred |
| Payment Settings | — | `admin.payment-settings` (`PaymentController@settings`/`updateSettings` → `Admin/PaymentSettings.vue`) | PARTIAL — data rekening tidak pre-filled (prop `bankAccounts` tak terpakai; form memakai `config.bank_*` kosong) | Gateway/bank config | A | P2 | Deferred |
| Monitoring | — | `admin.monitoring` (`MonitoringController@index` → `Admin/Monitoring.vue`) | WORKING — REAL (health PHP/config, disk, TenantStat) | Health/runtime platform | A | P3 (3 undefined prop: `system_alerts`, `mysql_data_size`, `file_count`) | Deferred |
| Backup | — | `admin.backup` (`BackupController@index`/`run`/`delete`/`settings`/`upload-drive` → `Admin/Backup.vue`) | WORKING — REAL (filesystem listing + disk + rclone/Drive status) | Platform backup | A | — | none |
| Logs | — | `admin.logs` / `logs.clear` (`SystemSettingsController@logs` → `Admin/Logs.vue`) | WORKING — REAL (SystemLog paginated + filter) | Platform audit log | A | — | none |
| Pengaturan | — | `admin.settings` (`SystemSettingsController@index`/`update`/`feature-flags`/`test-mail` → `Admin/Settings.vue`) | WORKING — REAL (general/registration/maintenance/mail + transactional mail `mail_resend`) | Platform settings | A | P2 (2 dari 5 toggle feature-flag `registration` & `maintenance_mode` tidak ter-submit) | Deferred |
| — (auth gap) | seluruh `/admin/*` | `admin.auth` (`AdminAuthenticate`) | WORKING untuk akses — tapi **tanpa role-check** (tabel users central tidak punya kolom role/is_admin; login = `Auth::attempt` polos) | Platform admin authZ | **G** | **P2 (latent — tidak ada jalur exploit aktif; rekomendasi hardening)** | Rekomendasi: guard `is_admin` |
| — (capability tak terpakai) | — | `admin.payments.invoice`, `admin.plans.default-menus` | Defined, tanpa UI/pemanggil | — | E (partial) | P3 | Deferred |

---

## Ringkasan

- **10 item navigasi (AdminLayout.vue) semuanya WORKING** → route → controller → page (file ada) → data real. Tidak ada dummy/hardcode/TODO/`console.log`/`alert()`/`prompt()`/`href="#"`.
- **Tidak ada tenant-operational function yang salah letak di Central Admin** (intake/teknisi/QC/customer/stock/kasir/warranty TIDAK ada di sini). Detail tenant menampilkan services/sales terbaru hanya sebagai **view read-only platform support** — sah.
- **Tidak ada menu dead/duplicate/legacy** yang aktif. Voucher page ada (`Admin/Vouchers/Index.vue`), bukan 404.
- **Isolasi tenant** pada `admin.tenant.show`: inisialisasi tenancy per-tenant + try/catch + `tenancy()->end()` — aman.
- **Temuan utama**: authZ gap (setiap user central = platform admin penuh). Tidak ada jalur exploit aktif (tidak ada self-registration central; user tenant tidak bisa akses guard central). **P2 latent — rekomendasi hardening** (kolom `is_admin` + guard), TIDAK diperbaiki di fase audit (hindari risiko lockout + tanpa desain spekulatif).
- **PLAN-CONFIG MISMATCH** (dicatat, TIDAK diperbaiki): Basic `users=read_only` vs pesan produk "Maks. 3 karyawan".
