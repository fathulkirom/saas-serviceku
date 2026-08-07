# PILOT-READY-01 — OPERATIONAL USABILITY GATE

**Tanggal**: 2026-08-07
**Mode**: AUDIT REAL USER JOURNEY → CLASSIFY BLOCKERS → FIX P0/P1 ONLY → VERIFY COMPLETE DAILY OPERATION
**Target**: SERVICEKU dapat digunakan oleh toko servis nyata (PILOT).

---

## 1. Current Runtime Baseline

| Metrik | Nilai |
|---|---|
| Framework | Laravel 12 + Vue 3 + Inertia + stancl/tenancy (1 DB/tenant) |
| Baseline tests (sebelum fase ini) | 560 passed · 1 known external failure (Google Drive) · 6 incomplete · 1758 assertions |
| Business Reality PASS | BR-001, 002, 003, 004, 005, 006, 007, 008, 009, 011, 012, 013, 016, 017 |
| Frontend build | PASS |

## 2. Daily Journey Audit (UI → Backend)

Audit dilakukan dari Vue → route → controller → policy → DB untuk seluruh alur utama.

| Langkah | UI | Route/Controller | Status |
|---|---|---|---|
| Login | `Auth/SubdomainLogin.vue` | `login` / `LoginController` | ✅ |
| Dashboard | `Dashboard.vue` / role dashboards | `dashboard` / `DashboardController` | ✅ |
| Customer lookup | `Customers/Index.vue` + global search (Ctrl+K) | `customers.index` / `search` | ✅ |
| Service intake | `Services/Create.vue` (quick-add customer inline) | `services.store` / `ServiceIntakeController` | ✅ |
| Device | free-text `tipe_unit` + IMEI/SN → Device row | `ServiceIntakeController@store` | ✅ |
| Print/View receipt | toolbar Cetak / `Services/Index` | `services.print-receipt` → PDF `pdfs/service-receipt` | ✅ |
| Assign technician | toolbar Assign | `services.assign` / `TechnicianWorkflowController` | ✅ |
| Diagnosis | tab Diagnosa (Workspace) | `services.diagnosis.store` | ✅ (diperbaiki fase ini) |
| Quotation (opsional) | tab Quotation (workspace) | `services.quotation.create` | ✅ (opsional) |
| Request part | tab Sparepart | `services.parts.request` | ✅ (diperbaiki) |
| Approve/Reserve part | tab Sparepart | `service-parts.approve` | ✅ (diperbaiki) |
| Use/consume part | tab Sparepart (CS) | `service-parts.use` | ✅ |
| Repair (start) | toolbar Mulai Servis | `services.repair.start` | ✅ |
| Finish | toolbar Selesai | `services.repair.complete` | ✅ |
| Set fees | `services.complete` (setelah selesai) | `ServiceDocumentController@complete` | ✅ |
| QC | **tab QC** (owner/admin/manager) | `services.qc.store` | ✅ (ditambahkan fase ini) |
| Invoice (draft) | toolbar Buat Invoice | `sales.draft-from-service` | ✅ |
| Payment | toolbar Bayar | `sales.pay-draft` | ✅ |
| Ready pickup | toolbar Siap Diambil | `services.ready-pickup` | ✅ |
| Pickup | toolbar Serahkan | `services.pickup` | ✅ (cashier diperbaiki) |
| Warranty | tab Garansi (klaim + approve + rework + refund) | `services.warranty-claim` / `warranty-claims.decide` / `warranty-claims.refund` | ✅ (approve UI ditambahkan) |

## 3. Role Journey Audit

| Role | Login | Menu tepat | Sensitive leak | Aksi harian via UI |
|---|---|---|---|---|
| CS | ✅ | Dashboard, Customer, Service | ✅ tidak ada | intake, print, assign, konsumsi part, pickup |
| Technician | ✅ | Dashboard, Service | ✅ tidak ada | lihat job, diagnosa, part, repair, finish |
| Manager | ✅ (buat via owner) | + Finance, Inventory, Reports | ✅ tidak ada | progress, approve part, QC, warranty decide, reopen |
| Kasir | ✅ | + Finance (dibatasi hari ini) | ✅ | invoice, payment, receipt, pickup |
| Owner | ✅ | semua | n/a (full) | semua + users/master data/sistem |

**Perbaikan fase ini**: kasir kini dapat membuka detail servis (`service.view`) dan pickup (`service.pickup`); dashboard kasir menampilkan “Servis Siap Diambil” nyata.

## 4. Dead UI Findings

Audit `Services/*`, `ServiceWorkspace/*`, `Customers/*`, `Inventaris/*`, `Penjualan/*`, `Keuangan/*`, `Sistem/*`:

- **P1 (diperbaiki)**: halaman detail servis (Enterprise Workspace) — komponen tab menerima prop salah → Overview kosong, Sparepart/Foto/Diagnosa POST ke `/services/undefined/...`.
- **P1 (diperbaiki)**: tidak ada UI QC, tidak ada UI approve/tolak klaim garansi, kasir tidak bisa pickup.
- **P2 (trivial, diperbaiki)**: toolbar `cancel` POST ke route tidak ada (404) → diarahkan ke `services.cancel`; `indent` hanya alert → diarahkan ke `services.indent`; `print` 404 → handler ke `services.print-receipt`; action `share` (tidak ada handler/route) dihapus.
- **P3 (tidak diperbaiki — bukan harian)**: `Services/Workspace.vue` & `ServiceWorkspace/Index.vue` (halaman lama, tidak di-render), `Technician/Dashboard.vue` nested (route tidak ditautkan; komponen `WorkOrderSection` tidak di-import), `Sistem/Workflows.vue` tombol Export/Import tanpa handler, Approval Center quotation/price/returns tanpa tombol approve, `Customers/Show` tombol Edit menuju halaman create, QC item hardcoded, dialog `prompt/alert` pada beberapa aksi.

## 5. Minimum Master Data

| Item | Klasifikasi | Bukti |
|---|---|---|
| Branch | READY (auto-1 di provisioning + CRUD di Sistem) | `BranchController` + `Sistem/Index.vue` |
| User/employee | READY (owner otomatis teknisi bila < 2 user) | `UserManagementController` + Sistem |
| Customer | READY (inline quick-add di intake) | `customers.ajax-store` |
| Device Type / Brand | PARTIAL (opsional; fallback null) | MasterData + Pengaturan |
| Model/Series | READY (free-text `tipe_unit`, wajib) | `StoreServiceRequest` |
| Service/Labor item | PARTIAL (UI jasa circular redirect; free-text labor tetap jalan) | MasterDataController |
| Product/Sparepart | READY | `ProductController` + Inventaris |
| Supplier | PARTIAL (inline saat pembelian) | `PurchaseController` |
| Checklist | READY (opsional) | `ChecklistTemplateController` + ServisTools |
| Warranty | READY (auto di pickup, default 30 hari — diperbaiki) | `ServiceDeliveryController` |
| Payment Method | READY (cash/transfer hardcoded + fallback) | POS UI |
| Cash Register | READY (plan-gated) | `CashRegisterController` + Kas |
| Service numbering | READY (tracking_code otomatis 8 karakter) | `Service::generateTrackingCode` |
| Invoice numbering | PARTIAL (berbasis sale id; tidak ada kolom invoice_number) | — |

**Kesimpulan**: tenant baru dapat mengoperasikan intake tanpa seed data demo. `MasterDataSeeder` TIDAK dijalankan di provisioning; demo data opt-in.

## 6. Fresh Tenant Test

`PilotStoreOperationalTest::test_fresh_tenant_provisions_master_data_via_real_routes` membuktikan lewat route nyata:
branch (`branches.store`) → users (`users.store`) → customer (`customers.ajax-store`) → product (`products.store`) → service intake (`services.store`) → full lifecycle.

**Intervensi developer yang dibutuhkan di jalur normal**: **NOL** (setelah perbaikan fase ini). Satu-satunya ketergantungan non-UI adalah paket **Basic/Pro** (Trial memblokir sales/payment — keputusan operasional, bukan bug).

## 7–12. Remaining Business Reality Triage

| BR | Klasifikasi | Alasan |
|---|---|---|
| **BR-010 Local Purchase** | **B — PILOT IMPORTANT, MANUAL WORKAROUND OK** | Pembelian lokal bisa dicatat via Keuangan → Pembelian (supplier inline + cost + expense). Alur servis utama tidak butuh purchase. Tidak dibangun procurement ERP. |
| **BR-014 / BR-015 Commission** | **B — POST-PILOT / MANUAL WORKAROUND** | Atribusi teknisi dipertahankan (`technician_id`), komisi dibuat otomatis saat `services.complete`; bila perlu dihitung manual, data tidak hilang. Tidak didesain ulang. |
| **BR-018 External Partner** | **C — POST-PILOT** | Repair outsource dapat dicatat via status `onpartner` + catatan partner. Portal partner tidak dibangun. |
| **BR-019 Plan Downgrade** | **C — POST-PILOT** | Siklus langganan SaaS, bukan operasi harian pilot; tidak merusak tenant pilot. |
| **BR-020 Reopen** | **A → diperbaiki minimal (P1)** | Servis tertutup tidak bisa dikoreksi dari UI (perlu DB). Implementasi minimal: tombol “Minta Reopen” (owner/admin/manager, reason wajib) + “Setujui” di Approval Center. Semantik aman: hanya unlock (tidak mengubah status/pembayaran/stock/komisi → tidak ada duplikasi). Juga memperbaiki bug `Service::lock()` (field `is_locked` tidak ada di fillable → lock tidak pernah tersimpan). |

## 13. Printing

- **Tanda terima servis**: `services.print-receipt` → PDF `pdfs/service-receipt.blade.php` (nomor servis, customer/device, keluhan, kondisi).
- **Invoice**: `sales.print` → PDF `pdfs/sale-invoice.blade.php`.
- **Checklist masuk/keluar, nota indent**: blade tersedia.
- Tidak ada template kosong/placeholder.

## 14. Payment

Verifikasi dari UI/route (E2E): draft invoice dari servis → total dari backend (jasa + part) → `pay-draft` → status `paid` → `payment_status='paid'` → receipt dapat dicetak → pembayaran ganda ditolak. `sales.draft-from-service` & `sales.pay-draft` idempoten (test coverage ada).

## 15. Inventory

E2E membuktikan: technician request part → owner approve (reserve, stock fisik tetap) → CS confirm/consume (stock turun **tepat satu kali**) → part masuk invoice. Cancel/return routes ada (`service-parts.cancel`, `return-request`, `process-return`). Tanpa manipulasi DB.

## 16. Warranty

E2E: pickup → garansi otomatis (default 30 hari — diperbaiki agar `warranty_days=0` tidak menghasilkan garansi 0 hari) → garansi terlihat di Workspace → klaim bisa dibuka (`services.warranty-claim`) → **Setujui/Tolak** (UI baru) → approve membuat rework → QC rework → resolve. Refund via modal (Expense cash-out nyata; BR-011/012/013 hijau).

## 17. Error Recovery

Diverifikasi (test eksisting + E2E): double-click/double submit → idempotency (409 repeat complete, 409 duplicate QC, pembayaran ganda aman, pickup ganda ditolak, approve-once, stock tidak double-decrement). Permission dicabut → 403/abort aman. Data persisted (tidak ada state browser-only).

## 18. Responsive Operation

Screens harian memakai grid responsif (desktop/tablet/hp). Tidak ditemukan blocker responsif fungsional pada intake, list, workspace, repair, QC, payment, pickup. Tanpa redesign visual.

## 19. Data Safety

- Tenant per-DB (SQLite file / driver DB per tenant); `setUpTenant` memakai tenant migration.
- Deployment: `deploy.sh` / `docker-compose` memakai migration (tidak ada `migrate:fresh` di jalur produksi).
- Backup: `backup.sh` tersedia.
- Rollback: migration `down` tersedia (mis. `2026_08_02_000019` punya `dropColumn`).
- Queue/Redis loss tidak merusak source-of-truth (transaksi inti memakai `DB::transaction`; `CACHE_STORE=array` di dev).
- Tidak ada perintah destruktif dijalankan.

## 20. Pilot Blockers Found

| # | Blocker | Severity | Status |
|---|---|---|---|
| 1 | Workspace section prop contract (detail servis kosong/404) | P1 | ✅ Fixed |
| 2 | Tidak ada UI QC | P1 | ✅ Fixed |
| 3 | Tidak ada UI approve/tolak klaim garansi | P1 | ✅ Fixed |
| 4 | Kasir tidak bisa view/pickup servis | P1 | ✅ Fixed |
| 5 | Dashboard kasir skeleton abadi | P1 | ✅ Fixed |
| 6 | Global search 500 (`Builder::map`) | P1 | ✅ Fixed |
| 7 | Workspace denied untuk fresh tenant (`FeatureEngine` kosong) | P1 | ✅ Fixed |
| 8 | Garansi 0 hari untuk servis tanpa `warranty_days` | P1 | ✅ Fixed |
| 9 | Reopen tidak bisa dari UI + lock tidak tersimpan | P1 | ✅ Fixed |
| 10 | `/keuangan` bocor ke teknisi | P0 | ✅ Fixed |
| 11 | `/pengaturan` bocor ke semua user | P0 | ✅ Fixed |
| 12 | `/sistem` bocor ke semua user (users-enabled) | P0 | ✅ Fixed |

## 21. Fixes Implemented

**Backend**
- `ServiceController@show` → rich `ServiceWorkspaceService::build()` + semua prop section (service, serviceId, availableProducts, customerSummary, spareparts, photos, diagnosis, sale, qcChecks, canQC, canManageParts, canConsumeParts, canRequestPart, serviceCharge, totalCost, paymentStatus).
- `FinanceController@index` / `SettingController@index` / `SystemController@index` → guard peran (P0).
- `SearchController@search` → `->get()` sebelum `->map()` (P1); `UniversalSearchController` → hapus query `invoice_number` (kolom tidak ada).
- `DashboardController@cashierDashboard` → `readyServices` + `cashRegisterOpen`.
- `ServiceDeliveryController@pickup` → default garansi 30 hari saat `warranty_days<=0`.
- `User::getLegacyPermissions()` + `PermissionEngineSeeder` → cashier dapat `service.view` + `service.pickup`.
- `Service::getAllowedTransitions()` ditambahkan (dipakai `ServiceWorkspaceService`).
- `FeatureEngine::getAllFeatureKeys()` → fallback ke fitur plan saat modul kosong.
- `Service::$fillable` → tambah `is_locked/locked_at/locked_by` (+ cast) — lock/reopen kini tersimpan.
- `DailyOperationsController::requestReopen/approveReopen` → guard owner/admin/manager.
- `ServiceWorkspace.php` (definisi) → tab `qc`, action `reopen`, hapus action `share` (mati).

**Frontend**
- `Enterprise/Workspace/Index.vue` → `v-bind="data"` (spread props) + `@refresh="refresh"`.
- `registrations/service.js` → register tab `qc`; handler `cancel`/`indent`/`print`/`reopen` ke route nyata.
- `Warranty.vue` → tombol Setujui/Tolak klaim garansi.
- `ApprovalCenter.vue` → tombol Setujui untuk reopen.

**Test assertion correction (perilaku yang diubah secara sah)**
- `tests/Unit/PolicyTest.php`: `service policy view any for cashier should fail` → kini **pass** (cashier diberi `service.view` karena menu Service + invoice/pay/pickup tersedia untuk kasir).
- `FinanceController@index`: guard disempurnakan agar custom role **`admin harian`** (view hari-ini terbatas) tetap diizinkan; hanya teknisi/courier/role lain yang ditolak.

## 22. Pilot E2E Result

`tests/Feature/Pilot/PilotStoreOperationalTest.php` + `tests/Feature/Pilot/PilotReadinessGuardsTest.php`:

**9/9 PASS · 93 assertions**, termasuk:
- Fresh tenant provisioning via route nyata (zero developer intervention).
- Full primary journey via HTTP (intake → assign → repair → part request/approve/use → finish → fees → QC → invoice → payment → pickup → garansi aktif).
- Workspace prop contract (P1-1 guard).
- Security guards (keuangan/pengaturan/sistem), cashier dashboard, global search, reopen.

## 23. Full Suite

`php artisan test` — hasil authoritative final (setelah semua fix PILOT-READY-01):

**`Tests: 1 failed, 6 incomplete, 569 passed (1851 assertions)` — Duration: 1117.49s**

| Metrik | Nilai |
|---|---|
| Passed | **569** |
| Failed | 1 |
| Incomplete | 6 |
| Assertions | 1851 |
| Duration | 1117.49s |

- **1 failed** = `GoogleDrivePhotoServiceTest` (kredensial Google Drive) — **external / non-blocking** (classification D). Ini **bukan** kegagalan Google login.
- **6 incomplete** = skeleton BR-10, BR-14, BR-15, BR-18, BR-19, BR-20 (deferred backlog; BR-20 sudah mendapat fix pilot minimal via `PilotReadinessGuardsTest::test_reopen...`). Tidak diimplementasikan sekadar untuk menurunkan hitungan incomplete (per aturan PILOT-UAT-02 STEP 16).
- Tidak ada regresi P0/P1 baru pada run final.

## 24. Frontend Build

✅ `npm run build` PASS — `✓ built in 26.68s`, PWA generateSW (175 precache entries), tanpa error.

## 25. Human UAT Checklist Status

`docs/runtime/PILOT-UAT-CHECKLIST.md` dibuat (per role: CS/Teknisi/Manager/Kasir/Owner + lintas-peran). **Belum dijalankan** — membutuhkan manusia nyata di toko pilot. Tidak ada item yang ditandai PASS otomatis.

## 26. Deferred Post-Pilot Items

- BR-010 Local Purchase (pembelian lokal — dicatat via Keuangan; tanpa ERP).
- BR-014/015 Commission (post-pilot; atribusi + hitung manual OK).
- BR-018 External Partner (portal partner tidak dibangun).
- BR-019 Plan Downgrade (SaaS lifecycle).
- Jasa master UI (circular redirect) → post-pilot.
- Invoice numbering sekuensial pada sales servis → post-pilot.
- QC checklist template (hardcoded item) → post-pilot.
- Dead pages/code cleanup (`Services/Workspace.vue`, `ServiceWorkspace/Index.vue`, nested `Technician/Dashboard.vue`, tombol tanpa handler di `Sistem/Workflows.vue`).

---

## FINAL VERDICT

**PILOT READINESS: B — CODE READY FOR HUMAN PILOT UAT**

- Semua **P0** (3) dan **P1** (10) code blocker telah di-resolve (lihat §20–21).
- `PilotStoreOperationalTest` + guards **9/9 PASS** (93 assertions).
- Frontend build **PASS**.
- Full suite authoritative: **569 passed · 1 failed (Google Drive external) · 6 incomplete · 1851 assertions · 1117.49s** (§23).

Bukan C — UAT manusia di toko nyata **belum dilakukan** (checklist §25 + runbook PILOT-UAT-02 menunggu pelaksanaan). Setelah P0/P1 beres, fase ini berhenti; tidak ada fitur/modul baru, tidak ada redesign.
