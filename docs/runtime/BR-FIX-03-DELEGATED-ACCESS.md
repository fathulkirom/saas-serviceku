# BR-FIX-03 — CONTROLLED DELEGATION & RESTRICTED OPERATIONAL ACCESS

**Tanggal**: 2026-08-07
**Mode**: AUDIT → EXECUTABLE TESTS → MINIMAL FIX → REGRESSION
**Scope**: BR-001 (CS Temporary Replacement) + BR-016 (Owner Family / Restricted Operational Access)
**Aturan**: Tidak ada role baru (family/spouse/backup_cs/acting_cs/temporary_cs). Tidak ada authorization engine kedua. Delegasi direalisasikan lewat arsitektur **Role + Permission + Branch Scope + Delegation** yang sudah ada. Tidak ada fitur baru di luar scope (warranty, komisi, payroll, dsb.).

---

## 1. Previous Authorization Model

Sebelum BR-FIX-03, otorisasi ditentukan oleh tiga lapis yang **tidak terhubung**:

1. **Kolom `users.role`** + trait `HasRoles` (`isOwner/isCs/isTechnician/...`). Banyak controller memakai **role-name hardcoding** `in_array($user->role, [...])`.
2. **`User::getPermissionKeys()`** (cache `user:{id}:permissions`, 300s): mengecek tabel `roles`/`role_permission` dulu; jika kosong (lingkungan test / tenant lama), fallback ke `getLegacyPermissions()` — array hardcoded di `User.php` yang **diduplikasi** di `HandleInertiaRequests` sebagai prop `role_permissions`.
3. **`canViaPermission($key)`** = `in_array($key, getPermissionKeys())`. Middleware `permission:` (RequirePermission) **sudah ada tapi tidak pernah dipasang** di route mana pun.

Masalah inti:
- **Tidak ada mekanisme delegasi.** Tidak ada tabel/model/controller `delegations`. Referensi `\App\Models\Tenant\Delegation::class` di `WorkflowDefinitions.php:255` adalah **dangling** (model tidak ada).
- **Kunci permission `delegation.grant`/`delegation.revoke` sudah di-seed** tapi tidak dipakai di mana pun.
- **Role-name hardcoding** membatasi CS-replacement: teknisi tidak bisa menangani tugas CS tanpa mengganti role-nya; tidak ada cara memberi akses terbatas/berbatas waktu.
- **Endpoint finansial sensitif tidak dijaga**: `/reports/finance`, `/keuangan`, `/dashboard/owner`, `/dashboard/owner-kpi` hanya dijaga `check.plan.feature:*` (level paket), bukan role/permission.

## 2. Existing Delegation Architecture

**Tidak ada** yang bisa di-reuse sebagai engine delegasi:
- Tidak ada model/table/controller `Delegation`.
- `custom_permissions` JSON pada `users` hanya dipakai untuk `menu_access` (UI) dan `custom_fields`; `User::hasPermission()` **dead code** (0 caller).
- Engine permission (`Role`/`Permission`/`role_permission`/`user_role` + `PermissionEngineSeeder`) ada tapi **dormant** (hanya diuji PermissionEngineTest).

Karena tidak ada engine delegasi yang ada, BR-FIX-03 **membangun lapisan delegasi minimal DI ATAS arsitektur Role+Permission+Branch Scope yang ada** — bukan authorization engine kedua, melainkan perluasan resolusi permission (`User::canViaPermission()`) yang menghormati grant granular, berbatas waktu, dan berskop cabang.

## 3. Role Hardcoding Found (yang di-migrasi)

| Lokasi | Sebelum (hardcode) | Sesudah (BR-FIX-03) |
|---|---|---|
| `ServicePolicy::create` | `canViaPermission('service.create') \|\| canWorkOnServices()` → **teknisi bisa buat intake** | `canViaPermission('service.create')` saja — teknisi **tidak** bisa kecuali di-delegasi |
| `ServiceDeliveryController::pickup` | `in_array(role, ['owner','admin','manager','cs'])` + `BranchAccessService::canAccess` | `canViaPermissionInBranch('service.pickup', custodyBranchId)` |
| `SalePaymentController::payDraft` | `in_array(role, ['owner','admin','manager','cs','cashier'])` | `canViaPermissionInBranch('sales.create', sale->branch_id)` |
| `ServiceIntakeController::store` | hanya `authorize('create', Service)` | + `canViaPermissionInBranch('service.create', user->branch_id)` (delegasi wajib menutup cabang) |
| `CustomerPolicy::view` | hanya `canManageCustomers()` | + follow capability `service.create` berskop cabang (baca pelanggan demi intake) |

Role-name hardcoding lain di `TechnicianWorkflowController` (10 check) dan `SalePolicy` **tidak diubah** karena berada di luar scope BR-001/BR-016; dicatat di §20.

## 4. Schema Reused / Added

**Ditambah (tenant-local, additive, rollback-safe, ber-index)**:
- `database/migrations/tenant/2026_08_07_000006_create_delegations_table.php`
  - `user_id` (grantee), `permission`, `branch_id` (nullable), `granted_by`, `starts_at`, `expires_at`, `revoked_at`, `revoked_by`, `reason`, timestamps.
  - Index: `delegations_user_perm_active_idx (user_id, permission, revoked_at)`, `delegations_perm_expires_idx (permission, expires_at)`.

**Tidak ditambah**: tabel role baru, kolom role baru, tabel permission baru. Kolom `users.role` **tidak pernah diubah** oleh delegasi.

## 5. Permission Resolution

`User::canViaPermission($key)` sekarang = **role-based (cached) ATAU active delegation (fresh)**:

```
canViaPermission(key):
  if key ∈ getPermissionKeys() (cache 300s, role-based) → true
  return hasActiveDelegation(key)          // query fresh, tidak di-cache
```

`User::canViaPermissionInBranch(key, branchId)` (baru):
- key role-based → butuh `BranchAccessService::canAccess(user, branchId)`.
- key delegated → butuh grant aktif yang **menutup branchId** (`Delegation::coversBranch`: branch_id null = semua cabang terjangkau; selain itu harus sama).

`User::hasActiveDelegation(key, branchId)` (baru): query `Delegation::scopeActive()` — `revoked_at IS NULL`, `starts_at <= now()`, `expires_at > now()` — **dievaluasi saat request**, tanpa cron. Karena capability delegated **tidak pernah digabung** ke cache permission, pencabutan/kedaluwarsa **langsung berlaku**.

**Vocabulary baru** ditambahkan ke legacy map (`User::getLegacyPermissions()` + `HandleInertiaRequests` role_permissions) + `PermissionEngineSeeder`:
- `service.create`, `service.pickup`, `sales.create`, `finance.view`, `report.view`, `report.export`, `delegation.grant`, `delegation.revoke`.
- Owner/admin/manager/head_store/cs → `service.create`, `service.pickup`, `sales.create`.
- `finance.view` → **hanya** owner/admin/manager/head_store (sejalan `canManageFinance()`). CS/teknisi/kasir **tidak**.
- `delegation.grant`/`revoke` → owner/admin/manager.

## 6. Delegation Lifecycle

`DelegationController` (route di bawah `check.plan.feature:users`):
- `GET /delegations` → `DelegationPolicy::viewAny` (butuh `delegation.grant`/`revoke`).
- `POST /delegations` → `DelegationPolicy::grant` (butuh `delegation.grant`) + validasi `user_id exists:users,id`; **non-owner** hanya boleh mendelegasikan capability yang **ia sendiri punya** dan hanya ke cabang dalam jangkauannya; tidak boleh ke diri sendiri / ke owner.
- `POST /delegations/{delegation}/revoke` → `DelegationPolicy::revoke` (butuh `delegation.revoke` + akses cabang, owner bebas).

Grant/revoke menginvalidate cache permission grantee (`clearPermissionCache`) dan menulis `ActivityLog` (`delegation_granted` / `delegation_revoked`).

## 7. Branch Scope

- Grant memiliki `branch_id` **nullable**: null = "semua cabang yang terjangkau grantee via role"; terisi = hanya cabang itu.
- `canViaPermissionInBranch` memastikan capability delegated **hanya berfungsi di cabang yang dicakup grant**.
- Intake selalu di-stamp ke `users.branch_id` (primary), sehingga delegasi `service.create` **harus menutup primary branch** agar bisa dipakai untuk intake — ini yang membuat scope cabang bermakna.
- `ServiceDeliveryController::pickup` memakai custody branch (`currentCustodyBranchId`) sebagai scope: delegasi `service.pickup` harus menutup cabang custody.

## 8. BR-001 — CS Temporary Replacement Flow

Skenario: CS cuti → teknisi/pegawai lain menangani tugas CS sementara **tanpa mengganti role**.

1. Owner/manager (dengan `delegation.grant`) membuka **Sistem → Delegasi Akses**.
2. Grant `service.create` (dan bila perlu `service.pickup`, `sales.create`) ke teknisi, skop cabang, batas waktu.
3. Teknisi kini **bisa** membuat intake (ServicePolicy::create + CustomerPolicy::view + IntakeController branch check), **tetap role=technician**.
4. Pencatatan aktor substitusi: `services.created_by = id teknisi` (bukan CS).
5. `sales.create` **tidak otomatis** ikut — kasir harus di-delegasi terpisah (least privilege).
6. Setelah `expires_at` atau dicabut → teknisi **langsung tidak bisa** lagi (tanpa cron).

## 9. BR-016 — Restricted Operational Access

Pengguna terbatas (mis. anggota keluarga owner) dimodelkan memakai role operasional **yang sudah ada (CS)** + permission eksplisit — **bukan** role khusus keluarga, **bukan** role owner.

- Bisa: intake (CS work), kasir **hanya jika** `sales.create` ada (role CS memuatnya secara eksplisit).
- **Tidak bisa**: melihat P&L bulanan, memanggil endpoint P&L, payload dashboard owner, mengelola user, memodifikasi subscription, mengakses cabang di luar scope.
- Grant capability operasional **tidak** memberikan capability finansial (lihat §23 test).

## 10. Financial Visibility

Endpoint P&L/profit kini dijaga `permission:finance.view` (middleware `RequirePermission`, alias `permission:`):
- `GET /reports/finance` (`ReportController::finance` — revenue/expenses/profit).
- `GET /services/{service}/profit` (`ServicePartController::profit` — profit per servis).
- `GET /dashboard/owner` (`OperationalControlController::ownerDashboard`).
- `GET /dashboard/owner-kpi` (`OperationalDashboardController::owner` — gross profit).

`/keuangan` **tidak** di-gate (tetap pakai desain eksisting: CS/kasir mendapat view transaksi "hari ini" via `FinanceController::shouldRestrictToTodayCompletedTransactions`; pengujian eksisting `TenantFinanceTransactionVisibilityTest` tetap hijau). Kunci: P&L/profit di laporan & dashboard owner yang dijaga, bukan daftar transaksi operasional.

## 11. Dashboard Security

- `dashboard.owner` & `dashboard.owner-kpi` sebelumnya hanya plan-gated; kini **double-gated** (`check.plan.feature:dashboard` + `permission:finance.view`). Jika sebuah plan mencantumkan fitur `dashboard`, permission tetap menahan akses CS/teknisi/kasir.
- Plan test (`full_test`) ditambah `dashboard => true` agar gate permission menjadi kontrol efektif yang teruji (defense-in-depth).

## 12. Audit Trail

- `delegation_granted` / `delegation_revoked` via `ActivityLog::log(...)` dengan subject = grantee, properties berisi `permission`, `branch_id`, `starts_at`, `expires_at`, `granted_by`/`revoked_by`, `reason`.
- Diuji: `BR01 #11` (grant) dan `BR01 #12` (revoke).

## 13. UI Wiring

- `Sistem/Index.vue` mendapat tab **"Delegasi Akses"** (hanya tampil bila `canManageDelegations` = punya `delegation.grant`/`revoke`).
- `SystemController::index` mengirim `canManageDelegations` + `delegations` (dengan user/role/branch/granter/status aktif).
- Drawer "Beri Delegasi": pilih karyawan, capability, cabang (opsional), mulai/berakhir, alasan → `POST /delegations`.
- Tombol **Cabut** per baris → `POST /delegations/{id}/revoke`.
- Capability label: `service.create`, `service.pickup`, `sales.create`, `finance.view`, `report.view`, `customer.communicate`.

## 14. Tenant Isolation

- Migration delegasi berada di `database/migrations/tenant` → tabel `delegations` **hanya ada di DB tenant**, tidak pernah di central. Terbukti di `BR19 #1` (`Schema::connection('central')->hasTable('delegations') === false`).
- Delegasi tenant A tidak terlihat tenant B (DB per-tenant). Terbukti `BR19 #2`.
- Grant ke user di luar tenant ditolak validasi `exists:users,id`. Terbukti `BR19 #3`.
- Tidak ada role `family/spouse/backup_cs/acting_cs/temporary_cs` yang dibuat. Terbukti `BR19 #4`.

Klarifikasi klasifikasi role per ServiceKU Blueprint (dokumentasi saja — tidak ada penghapusan/migrasi):
- **Platform**: Super Admin.
- **Tenant operasional (blueprint)**: Owner, Manager, Admin, CS, Kasir, Teknisi.
- Nilai code lain diklasifikasikan berdasarkan bukti penggunaan di codebase:
  - `head_store` ("Kepala Toko") → **LEGACY** (19 penggunaan di `app/`, punya permission-map sendiri + logika pembatasan `FinanceController`; nilai operasional historis yang dipertahankan, bukan bagian daftar resmi blueprint).
  - `courier` ("Kurir") → **FUTURE / COMPATIBILITY** (dideklarasikan di `getAvailableRoles()`, permission kosong, dipakai minimal).
  - `custom` ("Kustom 🎨") → **CUSTOM** (mekanisme `custom_role`/`custom_permissions` resmi untuk role yang didefinisikan pengguna, bukan role tetap).

BR-FIX-04 tidak menghapus nilai-nilai tersebut dan tidak memigrasi user historis.
- Migration verification (kolom + index). Terbukti `BR19 #5`.

## 15. Tests Before

Sebelum perbaikan, `BR01TemporaryCsReplacementTest` dan `BR16LimitedOwnerFamilyAccessTest` adalah **skeleton** (`markTestIncomplete('FAIL: ...')`). Hasil audit:
- Teknisi **bisa** membuat intake (via `canWorkOnServices()`).
- Tidak ada mekanisme delegasi → CS-replacement / akses terbatas **tidak mungkin**.
- Endpoint P&L/dashboard owner **tidak dijaga** permission.

## 16. Tests After

**BR-001 (13 tes, STEP 17)** — `BR01TemporaryCsReplacementTest`:
1. Teknisi tanpa delegasi tidak bisa buat intake ✅
2. Teknisi tetap role=technician setelah delegasi ✅
3. Delegasi service.create mengizinkan intake ✅
4. Delegasi hanya berlaku di cabang yang diizinkan ✅
5. Delegasi tidak otomatis memberi kasir (payment) ✅
6. Permission kasir terpisah (`sales.create`) memberi payment ✅
7. Delegasi kadaluarsa ditolak ✅
8. Delegasi dicabut ditolak ✅
9. User tak berwenang tidak bisa membuat delegasi ✅
10. User ter-delegasi tidak bisa mendelegasikan onward ✅
11. Audit mencatat grant ✅
12. Audit mencatat revoke ✅
13. Servis di bawah delegasi mencatat aktor substitusi sebenarnya (created_by=teknisi) ✅

**BR-016 (10 tes, STEP 18)** — `BR16LimitedOwnerFamilyAccessTest`:
14. User operasional terbatas bisa kerja CS yang diizinkan ✅
15. Kasir hanya jika `sales.create` eksplisit ✅
16. Tidak bisa lihat P&L bulanan (`/reports/finance` → 403) ✅
17. Tidak bisa panggil endpoint P&L langsung (`/services/{id}/profit` → 403) ✅
18. Tidak bisa lihat payload dashboard owner (`/dashboard/owner` & `/dashboard/owner-kpi` → 403) ✅
19. Tidak bisa kelola user (`users.store` → 403) ✅
20. Tidak bisa modifikasi subscription (tanpa `subscription.manage`, tak bisa self-grant) ✅
21. Tidak bisa akses cabang tidak berwenang (intake cabang B → 403) ✅
22. Owner tetap akses penuh (P&L 200, intake, grant) ✅
23. Grant capability operasional TIDAK memberi capability finansial ✅

**STEP 19/21 — Isolasi tenant + schema safety** — `BR19DelegationTenantIsolationSchemaTest` (5 tes): tenant-local, invisible lintas tenant, tidak bisa grant ke user luar tenant, tanpa role keluarga, schema migration benar.

**Total tes baru: 28 (BR01 13 + BR16 10 + BR19 5), semua PASS.**

## 17. BR-FIX-01 Regression

Semua hijau (tidak berubah):
- `BR07PartApprovalInvoiceTest` (11) ✅
- `BR08PartReturnTest` (7) ✅
- `BR09ReservedStockTest` (5) ✅
- Total 23 passed (110 assertions).

## 18. BR-FIX-02 Regression

Semua hijau:
- `BR17MultiBranchManagerTest` (7) ✅
- `BR04CrossBranchPickupTest` (8) ✅
- `BR05StockVisibilityTest` (6) ✅
- `TenantIsolationBranchTest` (3) ✅
- `TenantCsWorkflowGuardTest` + `ServicePolicyTest` + `ServiceIntakeTest` (11) ✅
- Total 21 + 3 + 11 passed.

## 19. Full Suite

`php artisan test` (run setelah semua perbaikan final):

**474 passed · 1 failed · 12 incomplete · 1449 assertions · 726.56s**

Satu-satunya kegagalan: `GoogleDrivePhotoServiceTest > constructor with unexpired token initializes client` — **klasifikasi D (environment/external integration dependency)**: Google Client tidak bisa diinisialisasi tanpa kredensial Google API (`services.google.client_id/client_secret`). Ini kegagalan **yang sudah ada sebelum BR-FIX-03** (pada final acceptance BR-FIX-02 juga tercatat "1 failed" yang sama) dan **tidak berkaitan** dengan BR-FIX-03. Sesuai instruksi, **tidak ada kredensial yang ditambahkan**; kegagalan ini diklasifikasikan terpisah dan tidak menghalangi PASS BR-FIX-03.

Kegagalan yang sempat muncul lalu **diperbaiki**:
- `PermissionEngineTest` — `QueryException: no such table: delegations` pada skema `:memory:` yang tidak menjalankan migrasi tenant. Diperbaiki dengan guard `Schema::hasTable('delegations')` di `User::hasActiveDelegation()` (juga melindungi tenant yang belum migrasi agar tidak 500 pada setiap cek permission). Setelah perbaikan: **6/6 PASS**.

12 incomplete = skeleton Business Reality yang sudah ada sebelumnya, semua di luar scope (lihat §3 tabel).

`npm run build` ✅ — `✓ built in 19.39s`, PWA `generateSW` sukses (175 precache entries).

Catatan khusus:
- `TenantPaymentHttpIntegrationPhase4CTest > wrong branch rejected via http`: ekspektasi diubah dari **302 (validasi)** menjadi **403 (otorisasi)** — dengan gate permission berskop cabang, pembayaran lintas cabang ditolak di lapisan otorisasi (lebih benar). Sisa perilaku (sale tetap draft) dipertahankan.

## 20. Remaining Risks

- `TechnicianWorkflowController` masih memakai ~10 role-name hardcode (quotation/QC/repair). Berfungsi untuk skenario normal; tidak diubah karena di luar scope BR-001/BR-016. Idealnya dimigrasi ke permission serupa `service.pickup`.
- `SalePolicy::update`/`PosController::pay` tidak konsisten dengan `payDraft` (owner/admin only). Dicatat untuk migrasi permission konsisten di masa depan.
- Delegasi **berskop cabang untuk payment** dijalankan melalui `canViaPermissionInBranch`; tetapi `payDraft` masih punya kendala sekunder `sale->branch_id === user->branch_id` (primary). Grant `sales.create` lintas cabang tidak akan bisa membayar di cabang non-primary sampai kendala ini direlaksasi ke `BranchAccessService` — aman (tidak melebar) dan didokumentasikan.
- Capability delegated tidak masuk cache permission → query per-request. Acceptable untuk volume grant kecil.
- Jika `PermissionEngineSeeder` dijalankan di produksi, vocab baru (`service.pickup`, `sales.create` untuk cs, `delegation.grant/revoke` untuk admin/manager) sudah disinkronkan.

---

## VERDICT

| Scope | Hasil |
|---|---|
| **BR-001 — CS Temporary Replacement** | ✅ **PASS** — 13/13 tes; teknisi bisa menangani tugas CS sementara via delegasi tanpa ganti role; ekspirasi/revokasi request-time; audit + cabang + aktor substitusi teruji. |
| **BR-016 — Restricted Operational Access** | ✅ **PASS** — 10/10 tes; user operasional terbatas (role CS) bisa kerja CS/kasir eksplisit, TIDAK bisa P&L/dashboard owner/manage user/subscription/cabang luar scope; owner akses penuh; capability operasional tidak bocor ke finansial. |
| Isolasi Tenant + Schema Safety | ✅ **PASS** — 5/5 tes. |
| BR-FIX-01 Regression | ✅ PASS (23/23). |
| BR-FIX-02 Regression | ✅ PASS. |

---

## 21. Final Acceptance Verification

Verifikasi final repository-wide setelah implementasi BR-FIX-03 selesai (2026-08-07).

| Item | Hasil | Bukti |
|---|---|---|
| **BR-001** | ✅ **PASS** | `BR01TemporaryCsReplacementTest` 13/13 (34 assertions) |
| **BR-016** | ✅ **PASS** | `BR16LimitedOwnerFamilyAccessTest` 10/10 (25 assertions) |
| **Delegation tenant/schema** | ✅ **PASS** | `BR19DelegationTenantIsolationSchemaTest` 5/5 (34 assertions) |
| **BR-FIX-01 regression** | ✅ **PASS** | `BR07` (11) + `BR08` (7) + `BR09` (5) = 23/23 (110 assertions) |
| **BR-FIX-02 regression** | ✅ **PASS** | `BR17` (7) + `BR04` (8) + `BR05` (6) = 21/21 (94 assertions) |
| **Full Lifecycle** | ✅ **PASS** | `ServiceFullLifecycleTest` 1/1 |
| **E2E** | ✅ **PASS** | `TenantE2EPhase5ProductionQATest` 7/7 (63 assertions) |
| **Full PHP Suite** | ✅ **474 passed / 1 failed / 12 incomplete / 1449 assertions / 726.56s** | `php artisan test` — satu-satunya failed = `GoogleDrivePhotoServiceTest` (klasifikasi D, kredensial, sudah ada sebelum BR-FIX-03) |
| **Frontend Build** | ✅ **PASS** | `npm run build` — `✓ built in 19.39s`, PWA generateSW sukses |
| **Permission vocabulary consistency** | ✅ **PASS** | 8 kunci (`service.create`, `service.pickup`, `sales.create`, `finance.view`, `report.view`, `report.export`, `delegation.grant`, `delegation.revoke`) konsisten di seeder + legacy map + `HandleInertiaRequests` + middleware + kode; kunci UI-only `customer.communicate` dihapus |
| **Official role integrity** | ✅ **PASS** | Tidak ada `family/spouse/backup_cs/acting_cs/temporary_cs/temporary_manager`. Role tetap tidak berubah. Per Blueprint: Platform = Super Admin; Tenant = Owner, Manager, Admin, CS, Kasir, Teknisi. Nilai code `head_store`/`courier`/`custom` diklasifikasikan sebagai LEGACY / FUTURE-COMPATIBILITY / CUSTOM (lihat §14) — tidak dihapus, tidak dimigrasi. |

### Klasifikasi Kegagalan (hanya 1 di suite penuh)

| Test | Klasifikasi | Bukti |
|---|---|---|
| `GoogleDrivePhotoServiceTest > constructor with unexpired token initializes client` | **D — environment/external integration dependency** | `assertNotNull($service->client)` gagal karena Google Client tidak dapat diinisialisasi tanpa kredensial `services.google.client_id/client_secret`. Pre-existing (sama pada final acceptance BR-FIX-02). Tidak ada kredensial ditambahkan. |

### Delegation Security Sanity Check

| Pemeriksaan | Hasil | Bukti |
|---|---|---|
| Expired delegation denied | ✅ | BR01 #7 |
| Revoked delegation denied | ✅ | BR01 #8 |
| Wrong branch denied | ✅ | BR01 #4, BR16 #21 |
| Wrong tenant denied | ✅ | BR19 #1–3 |
| User role unchanged | ✅ | BR01 #2, BR19 #4 |
| Operational permission does not imply finance.view | ✅ | BR16 #23 |
| Delegation cannot bypass Feature/Plan | ✅ | Semua endpoint yang di-delegasi tetap memakai middleware `check.plan.feature:*` (mis. `services.store` → `check.plan.feature:services`); route delegasi berada di bawah `check.plan.feature:users` |
| Delegation cannot bypass BranchAccessService | ✅ | `canViaPermissionInBranch` jalur role-based butuh `BranchAccessService::canAccess`; jalur delegated butuh `Delegation::coversBranch` (scope cabang grant) |
| Grant/revoke audit contains actor | ✅ | BR01 #11/#12 (`activity_logs.user_id` = granter/revoker) |
| Delegated business action stores actual executing user | ✅ | BR01 #13 (`services.created_by` = teknisi substitusi) |

### Tabel Incomplete (12, semuanya di luar scope)

| Test | Business Reality ID | Reason | Priority |
|---|---|---|---|
| `BR02TechnicianOverrideTest` | BR-002 | Skeleton — PASS: None (sudah terpenuhi) | Out of scope |
| `BR03MultipleDevicesTest` | BR-003 | Skeleton — PASS: None (sudah terpenuhi) | Out of scope |
| `BR06CrossTenantIsolationTest` | BR-006 | Skeleton — PASS: None (sudah terpenuhi) | Out of scope |
| `BR10LocalPurchaseTest` | BR-010 | PARTIAL: purchase ada, tapi historical date + petty cash/emergency tag | Out of scope (Purchasing) |
| `BR11WarrantyReworkTest` | BR-011 | NOT IMPLEMENTED: rework warranty | Out of scope (Warranty) |
| `BR12WarrantyRefundTest` | BR-012 | NOT IMPLEMENTED: refund/accounting warranty | Out of scope (Warranty) |
| `BR13DistributorWarrantyTest` | BR-013 | NOT IMPLEMENTED: store vs distributor warranty | Out of scope (Warranty) |
| `BR14CrossBranchComplaintTest` | BR-014 | NOT IMPLEMENTED: cross-branch rework commission | Out of scope (Commission) |
| `BR15CommissionCapabilityTest` | BR-015 | PARTIAL: komisi dasar, persen kompleks belum | Out of scope (Commission) |
| `BR18ExternalPartnerTest` | BR-018 | PARTIAL: STATUS_ONPARTNER ada, vendor cost/portal belum | Out of scope (External Partner) |
| `BR19PlanDowngradeTest` | BR-019 | FAIL: max_users tidak otomatis dibatasi saat downgrade | Out of scope (Plan Downgrade) |
| `BR20ServiceReopenTest` | BR-020 | NOT IMPLEMENTED: tidak ada route reopen closed service | Out of scope |

**BR-001 dan BR-016 SUDAH TIDAK incomplete** — keduanya kini berupa test executable yang PASS (13/13 dan 10/10).

---

## FINAL VERDICT

**BR-FIX-03 — Controlled Delegation & Restricted Operational Access:**
**C. PASS** ✅

- BR-001 (CS Temporary Replacement): PASS — 13/13.
- BR-016 (Restricted Operational Access): PASS — 10/10.
- Isolasi tenant + schema safety: PASS — 5/5.
- Tidak ada kebocoran otorisasi yang diperkenalkan BR-FIX-03 (semua kunci permission konsisten, endpoint P&L/dashboard owner dijaga `finance.view`, delegasi tidak bisa bypass plan/cabang/role).
- Satu-satunya kegagalan suite penuh adalah `GoogleDrivePhotoServiceTest` (klasifikasi D — dependensi kredensial eksternal, sudah ada sebelum BR-FIX-03). Sesuai ketentuan, BR-FIX-03 tetap PASS karena kegagalan tersebut tidak berkaitan dengan otorisasi.

**Repository readiness untuk Business Reality Fix berikutnya:**
**B. CORE REPOSITORY CLEAN, KNOWN DEFERRED GAPS REMAIN** ✅

- 474 passed / 1 failed (kredensial Google Drive, terpisah) / 12 incomplete (skeleton out-of-scope) / 1449 assertions.
- Frontend build bersih.
- Gap yang ditunda terdokumentasi di §20 (role-hardcode `TechnicianWorkflowController`, konsistensi `SalePolicy::update` vs `payDraft`, kendala primary-branch pada `sales.create` lintas cabang, capabilitas delegated tidak di-cache).
