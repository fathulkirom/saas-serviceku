# BR-FIX-04 — WARRANTY CLAIM, REWORK, REFUND FOUNDATION & DISTRIBUTOR WARRANTY

**Tanggal**: 2026-08-07
**Mode**: AUDIT → EXECUTABLE BUSINESS REALITY TESTS → MINIMAL FIX → REGRESSION
**Prioritas**: BR-011 (P1 Core Operational) · BR-012 (P2 Financial After-Sales) · BR-013 (P3 Warranty Source Tracking)
**Aturan inti**: **Warranty claim BUKAN reopen servis asli.** Original completed Service adalah bukti historis; rework harus terpisah & terlacak.

---

## 1. Previous Warranty Architecture

Sebelum BR-FIX-04, representasi garansi **ganda & tidak konsisten**:

1. **`Service.warranty_days` + `warranty_expired_at`** — di-set saat pembayaran (`SalePaymentController::payDraft`, `SaleStoreController::store`).
2. **`ServiceWarranty` row** — dibuat otomatis saat **pickup** (`ServiceDeliveryController::pickup` → `createFromService`), `start_date`=hari ini, `end_date`=+days, `status='active'`.

Eligibility terpecah: `Service::isWarrantyValid()` (cek `warranty_expired_at`) vs `ServiceWarranty::isActive()` (cek status+end_date).

**Bug laten yang ditemukan saat audit:**
- `ServiceExceptionController@createClaim` memakai `$service->warranty` — relasi **tidak ada** → selalu `null` → route `services.warranty-claim.create` selalu gagal "Garansi tidak aktif". (Route mati.)
- `Service::warrantyClaims()` (hasMany via `parent_service_id`) ambigu — mengembalikan **child Service**, bukan `ServiceWarrantyClaim`.
- `ServiceWarrantyClaim.service_id` sudah mereferensikan **original** service, tapi **tidak ada link ke rework** (`rework_service_id`).
- `ServiceClaimController@createWarrantyClaim` membuat child Service (rework) **tanpa** membuat `ServiceWarrantyClaim` row → klaim tidak tercatat secara auditable.
- **Tidak ada** model/controller/route refund. `SaleReturn.type='refund'` hanya restock, **tidak** membalik uang.
- **Tidak ada** upstream/supplier warranty sama sekali (Product/Purchase/PurchaseItem/ServiceSparepart/Supplier).
- `Service::device()` relasi **tidak ada** walau `device_id` dipakai (bug laten).

## 2. Existing Models Reused

| Model | Status | Peran BR-FIX-04 |
|---|---|---|
| `ServiceWarranty` | Diperluas | Store warranty; + `claims()` hasMany; `isActive()` null-safe |
| `ServiceWarrantyClaim` | Diperluas | Klaim + link `rework_service_id`, `branch_id`, `resolved_by`, `resolution_note`; `linkRework()`, `resolve()` |
| `Service` | Diperluas | + relasi `warranty()` (hasOne) & `device()` (belongsTo); kolom `parent_service_id`, `is_warranty_claim` **reused** |
| `ServiceSparepart` | Diperluas | + `supplier_id`, `supplier_warranty_days`, `supplier_warranty_lifetime` (upstream) |
| `ServiceRequiredPart` | Reused | Alur part BR-FIX-01 (request→approve→reserve→use) |
| `Sale` | Reused | Basis refundable (paid_amount) |
| `Supplier` | Reused | Link upstream warranty (hanya kolom yang ada: name, phone, email, address) |

Tidak ada engine warranty baru — semua direalisasikan di atas arsitektur eksisting + `WarrantyService` (service domain tunggal).

## 3. Schema Changes

Migration additive tenant-local: `database/migrations/tenant/2026_08_07_000007_add_warranty_rework_refund_supplier.php`
- `service_warranty_claims` += `rework_service_id` (FK nullOnDelete, indexed), `branch_id` (FK nullOnDelete, indexed), `resolved_by`, `resolution_note`.
- **`sale_refunds`** (baru): `claim_id` (FK nullOnDelete, indexed), `sale_id`, `service_id`, `branch_id`, `amount`, `reason`, `method`, `authorized_by`, `created_by`, `refunded_at`, `status`; index lookup.
- `service_spareparts` += `supplier_id` (FK nullOnDelete), `supplier_warranty_days`, `supplier_warranty_lifetime` (bool default false).

Tidak ada rewrite destruktif, tidak ada hapus Service/Sale/Payment, tidak mengubah `service.branch_id` historis. Divirifikasi test `BR13 #33` (tenant-local), `BR12 #26` (tenant-local), BR19 #5 (migration verification).

## 4. Warranty Activation

- Store warranty tetap dibuat saat **pickup** (`ServiceDeliveryController::pickup`) dan **payment** (kolom `warranty_expired_at`). Keduanya dipertahankan (tidak diubah).
- `WarrantyService::ensureWarrantyRow()` memastikan ada `ServiceWarranty` row (dari window `warranty_expired_at`) saat klaim dibuka — agar klaim selalu punya `service_warranty_id`.

## 5. Claim Eligibility

`WarrantyService::isEligibleForStoreWarranty(Service, $claimDate)` — **satu-satunya** perhitungan eligibility (backend source of truth, bukan teks frontend "Garansi masih aktif"):
- Status void/cancel → tidak eligibel.
- `warranty_expired_at` ≥ claim date → eligibel.
- `ServiceWarranty` row aktif (bukan void, `end_date` ≥ claim date) → eligibel.
- **Store warranty expired → gagal**, meskipun upstream warranty masih aktif (BR-013 test #28/#32).

## 6. Claim Lifecycle (BR-FIX-04.1 canonical)

Status dipakai dari vocab eksisting: `submitted → checking → approved → repairing → completed` / `rejected`.

Urutan kanonik (OPEN ≠ APPROVE):

1. **OPEN** (`WarrantyService::openClaim`): mencatat komplain → klaim `submitted`, `checked_by=NULL`, `approved_by=NULL`, `rework_service_id=NULL`. **TIDAK** membuat rework, **TIDAK** menandai persetujuan.
2. **REVIEW/APPROVE** (`decideClaim` → `WarrantyService::approveClaim`): otoritas finance-level + branch access; eligibility **di-cek ulang**; hanya klaim terbuka (submitted/checking) yang bisa; set `approved` + `approved_by`; **buat NEW rework Service tepat sekali** (idempotent) + link `rework_service_id`; audit/event `WarrantyClaimApproved`.
3. **REJECT** (`WarrantyService::rejectClaim`): reason wajib; `rejected`; **tidak** membuat rework; original tidak berubah; event `WarrantyClaimRejected`.
4. **Duplicate protection**: selama ada klaim terbuka (submitted/checking/approved/repairing) → klaim baru ditolak (BR-011 #14).
5. **Repair finish** (`ServiceWorkflowController::finish`): **TIDAK** me-resolve klaim (selesai teknisi ≠ resolusi warranty).
6. **QC fail**: klaim tetap terbuka (`approved`, `completed_at=NULL`, `resolved_by=NULL`); rework kembali ke `dikerjakan`; tidak ada rework ganda/revenue/komisi baru.
7. **QC pass** (`TechnicianWorkflowController::storeQcCheck`): rework → `siap_diambil`; klaim **diresolusi** di titik kanonik QC-PASS / READY-PICKUP → `completed` + `completed_at` + `resolved_by` + `resolution_note`; event `WarrantyClaimResolved`.

## 7. Rework Service Architecture

`WarrantyService::createReworkService` (dipanggil saat **APPROVAL**, bukan saat open) membuat **NEW `Service`**:
- `is_warranty_claim=true`, `parent_service_id=original`, `customer_id` & `device_id` SAMA dengan original, `branch_id`=handling branch, `status=menunggu_alokasi`, `service_charge=0`, `total_cost=0`.
- Hanya membawa konteks sah (tipe unit, merek, imei, posisi) — **tidak** menyalin diagnosis/part/QC/pembayaran original.
- Memiliki `ServiceWarrantyClaim.rework_service_id` → **klaim ↔ rework terhubung dua arah**.

## 8. Diagnosis History Integrity

- Original diagnosis (`ServiceDiagnosis` original) **tidak pernah disentuh** oleh klaim/rework (row terpisah).
- Rework punya `ServiceDiagnosis` sendiri. BR-011 #8: original `Fault A` + rework `Fault B` → keduanya tersimpan (tidak overwrite).
- `ServiceDiagnosisHistory` (append-only) tetap tersedia untuk revisi diagnosis.

## 9. Part/Inventory Integration

- Rework memakai alur **BR-FIX-01** penuh: Technician Request → Approval → Reservation → Confirmation → Physical deduction → Mutation. BR-011 #11: part di rework mengurangi stok tepat sekali (`reference_type=service_part_usage`), membuat `ServicePartUsage` terhubung ke **rework**, dan original service **tidak** mendapat usage baru.
- Status warranty **tidak** melewati kontrol inventori.

## 10. QC

- Rework harus melewati QC kanonik (`services.qc.store` → role owner/admin/manager, status harus `selesai`). BR-011 #12: rework di-QC → `service_qc_checks` + status `siap_diambil`.
- **QC fail**: rework kembali ke `dikerjakan` (workflow kanonik), klaim tetap terbuka. **QC pass**: klaim diresolusi di titik kanonik (BR-011 #13/#25/#26/#27).
- Tidak ada shortcut QC khusus warranty.

## 11. Financial Behavior

- Rework dibuat `service_charge=0`, `total_cost=0` → **tidak** menduplikasi revenue original (BR-011 #9).
- **Tidak** membuat Sale baru untuk rework (BR-011 #10) — pembayaran original tetap historis.
- Top-up/part tambahan di luar cakupan adalah kebijakan toko (di luar scope; tidak di-invent).

## 12. Refund Architecture (BR-FIX-04.1 — with REAL cash-out)

`SaleRefund` (tabel `sale_refunds`) — **event finansial reversal terpisah & append-only**:
- Original `Sale` / `payment_details` JSON **tidak pernah diedit/dihapus** (BR-012 #18).
- Guard: otorisasi finance (`canManageFinance()` / `finance.manage`), branch access, `amount ≤ refundable` (`paid_amount − Σ refund`), duplicate dicegah via saldo (BR-012 #20/#21), partial refund diizinkan (BR-012 #19), cross-tenant mustahil (BR-012 #26).
- `refundClaim` menandai klaim `completed` (resolusi via refund) + `ActivityLog` + event `WarrantyRefunded`.

**REAL cash/finance outflow (BR-FIX-04.1)**: audit menemukan satu-satunya ledger uang keluar ServiceKU adalah tabel **`expenses`** (`ReportController::finance` menghitung `profit = revenue − expenses`). Karena itu setiap refund menulis **dua entri atomik** (dalam satu `DB::transaction`):
1. `SaleRefund` (event auditable).
2. **`Expense`** (cash-out nyata): `category='lainnya'` (enum SQLite tidak dilebarkan), `amount` = refund, `branch_id`, `expense_date = refunded_at`, `description` berawalan "Refund …", `sale_refund_id` (FK → `sale_refunds`, additive) sebagai jejak.

Efek: gross revenue tetap historis (1.000.000), refund 400.000 → `profit = revenue − expenses` mencerminkan **net 600.000** (BR-012 #18 + tes net). Non-cash (transfer/QRIS) memakai jalur yang sama karena tidak ada ledger rekening bank; `method` tersimpan di `SaleRefund`. CashRegister/CashierShift adalah snapshot money-in — bukan store transaksi, jadi tidak dipakai.

## 13. Commission Safety

- `Commission::autoCreateForService()` **melewati** service `is_warranty_claim=true` → rework **tidak** otomatis membayar komisi standar (tidak menduplikasi pendapatan).
- Komisi original **tidak dihapus**; klaim dibuka tidak menggandakan komisi original.
- BR-014 (kompensasi lintas cabang) tetap **out of scope**.

## 14. Store Warranty

- Tetap dua representasi yang sudah ada (`warranty_expired_at` + `ServiceWarranty`), kini disatukan lewat `WarrantyService::isEligibleForStoreWarranty`.
- Customer-facing store warranty tetap jujur (BR-013 #32): store expired ≠ store aktif, meskipun upstream aktif.

## 15. Distributor/Supplier Warranty

- Kolom baru pada `service_spareparts`: `supplier_id`, `supplier_warranty_days`, `supplier_warranty_lifetime`.
- `WarrantyService::upstreamWarrantyFor()` / `isUpstreamWarrantyActive()`: window upstream dimulai dari `selesai_at` service; lifetime menang.
- Store ≠ upstream: store expired tidak menandai upstream expired (BR-013 #28), upstream aktif bisa di-query setelah store expired (BR-013 #29), link ke part+supplier (BR-013 #31).

## 16. Lifetime Warranty

- Direpresentasikan eksplisit via flag `supplier_warranty_lifetime` (bukan magic date jauh di masa depan). BR-013 #30: lifetime upstream → selalu aktif.
- Keterbatasan didokumentasikan: `ServiceWarranty.end_date` tetap NOT NULL (store lifetime tidak didukung; tidak perlu redesign besar — kebutuhan BR-013 adalah upstream lifetime).

## 17. Cross-Branch Handling

- Claim dapat dibuka oleh cabang yang **sah berwenang** (`BranchAccessService::canAccess(user, custody branch)`), bukan hanya cabang primary. BR-011 #16: manager dengan akses A+B bisa menangani di B.
- Cabang tak berwenang ditolak (BR-011 #15). Original branch dipertahankan (tidak diubah). Claim & rework mencatat handling `branch_id`.

## 18. Authorization

- **Open claim**: `service.create` (operational) + branch access.
- **Approve/reject claim** (`decideClaim`): `canManageFinance()` (owner/admin/manager/head_store) + branch access.
- **Refund**: `canManageFinance()` / `finance.manage` + branch access.
- Delegasi `service.create` (intake) **tidak** memberi otoritas refund (BR-012 #22: teknisi & CS ditolak 403).
- Tidak ada role baru.

## 19. Workspace UI

- `ServiceWorkspaceService::transformService()` kini mengirim `warranty` (coverage/start/expiry/status), `warranty_claims` (claim_number, status, linked rework, resolution, branch, `sale_id`, `refundable`), `can_refund` (otoritas finance + branch access), dan `upstream_warranty`.
- `Warranty.vue` menampilkan: Status Garansi, Coverage, Klaim Garansi (dengan Rework Service + Resolusi), Garansi Upstream, notifikasi expired. Akses "Klaim Garansi" ada di `ServiceActionBar` (route `services.warranty-claim`).
- **Refund UI (BR-FIX-04.1)**: tombol **Refund** pada klaim (hanya bila `can_refund` && `refundable > 0` && status klaim valid) → **Modal** (komponen `Overlay/Modal` eksisting) berisi Jumlah (prefilled refundable), Metode, Alasan, Konfirmasi → `POST warranty-claims.refund` → `router.reload` refresh data. Tanpa `prompt()`.
- **UI security**: tombol disembunyikan saat tidak ada permission / refundable 0 / fully refunded / branch salah / state klaim invalid. **Backend tetap menolak HTTP langsung** (BR-012: csA/teknisi 403, wrong-branch 403).

## 20. Events/Automation/Notification

- Reused canonical events: `WarrantyClaimCreated`, `WarrantyClaimApproved`, `WarrantyClaimRejected` (ExceptionEvents).
- Ditambahkan (bukan class duplikat): `WarrantyClaimResolved`, `WarrantyRefunded` di ExceptionEvents.
- `WarrantyCreated` tetap di-dispatch saat pickup. Tidak ada listener baru (tidak ada automasi warranty eksisting yang diaktifkan; dicatat sebagai gap).
- Tidak ada WhatsApp fake success.

## 21. Tests Before

- `BR11WarrantyReworkTest`, `BR12WarrantyRefundTest`, `BR13DistributorWarrantyTest` = skeleton `markTestIncomplete`.
- Kondisi sebelum fix (hasil audit): rework tidak punya klaim auditable; `$service->warranty` rusak; refund tidak ada; upstream warranty tidak ada; komisi bisa terbentuk di service klaim.

## 22. Tests After (BR-FIX-04 + BR-FIX-04.1 corrections)

**BR-011 — 28/28 PASS** (STEP 24 + STEP 16 additions): warranty valid; claim diterima (submitted); expired ditolak; open ≠ approval (tidak set approved_by, tidak buat rework); approval buat rework tepat sekali; rejection tanpa rework; repeated approval tanpa rework ganda; original utuh; customer/device sama; status+workorder independen; diagnosis berbeda tidak overwrite; tidak duplikasi Sale revenue; tidak duplikasi Payment; part ikut BR-FIX-01; QC wajib; **repair finish tidak me-resolve klaim**; QC fail klaim tetap terbuka & rework kembali ke perbaikan; **QC pass me-resolve di titik kanonik**; duplicate diblock; cabang tak berwenang ditolak; cabang sah menangani; original tidak berubah sepanjang siklus.

**BR-012 — 18/18 PASS** (STEP 25 + STEP 16 additions): refund event terpisah; payment original historis; partial refund; tidak melebihi saldo; duplicate tidak ganda; unauthorized ditolak; **wrong-branch ditolak**; tidak restore stok; link ke Sale/Payment/claim; ada actor/reason/timestamp; **cash-out Expense nyata (amount = refund)**; retry tidak double-outflow; **net finansial mencerminkan refund**; fully-refunded tidak bisa refund lagi; **Workspace action mencapai endpoint nyata**; **backend direct unauthorized ditolak**; cross-tenant mustahil.

**BR-013 — 7/7 PASS** (STEP 26): store vs supplier distinguish; store expired tidak menandai upstream expired; upstream aktif queryable setelah store expired; lifetime upstream; link part+supplier; store tetap jujur; no cross-tenant leakage.

**Total tes warranty: 53 (28 + 18 + 7), semua PASS.**

## 23. BR-FIX-01 Regression

`BR07` (11) + `BR08` (7) + `BR09` (5) = **23 PASS** — alur part/reservation/stock/return tidak berubah.

## 24. BR-FIX-02 Regression

`BR17` (7) + `BR04` (8) + `BR05` (6) + `TenantIsolationBranchTest` (3) = **24 PASS** — multi-branch, custody, cross-branch pickup, visibility, isolation hijau.

## 25. BR-FIX-03 Regression

`BR01` (13) + `BR16` (10) + `PermissionEngineTest` (6) + `BR19` (5) + payment/finance visibility = **PASS** — delegasi, restricted access, finance gating tidak terganggu.

## 26. Full Suite — FINAL AUTHORITATIVE RESULT (BR-FIX-04.1)

`php artisan test` (run setelah BR-FIX-04.1 corrections):

**527 passed · 1 failed · 9 incomplete · 1650 assertions · 752.93s**

Satu-satunya kegagalan: `GoogleDrivePhotoServiceTest > constructor with unexpired token initializes client` — **klasifikasi D (environment/external integration dependency)**: Google Client tidak bisa diinisialisasi tanpa kredensial Google API. Pre-existing (sama pada acceptance sebelumnya), tidak berkaitan dengan BR-FIX-04/04.1, dan **tidak ada kredensial yang ditambahkan**.

9 incomplete = skeleton Business Reality di luar scope (BR02, BR03, BR06, BR10, BR14, BR15, BR18, BR19, BR20). BR-011/012/013 **tidak lagi incomplete**.

`npm run build` ✅ `✓ built in 22.29s` (PWA generateSW sukses).

## 26b. Final Acceptance Verification — FINAL AUTHORITATIVE RESULT

| Item | Hasil | Bukti |
|---|---|---|
| **BR-011** | ✅ **PASS** | `BR11WarrantyReworkTest` 28/28 (113 assertions) |
| **BR-012** | ✅ **PASS** | `BR12WarrantyRefundTest` 18/18 (82 assertions) |
| **BR-013** | ✅ **PASS** | `BR13DistributorWarrantyTest` 7/7 (24 assertions) |
| **BR-FIX-01 regression** | ✅ **PASS** | BR07/08/09 = 23/23 |
| **BR-FIX-02 regression** | ✅ **PASS** | BR17/04/05 + TenantIsolationBranch = 24/24 |
| **BR-FIX-03 regression** | ✅ **PASS** | BR01 13/13, BR16 10/10, PermissionEngine 6/6, BR19 5/5 |
| **Full Lifecycle** | ✅ **PASS** | ServiceFullLifecycleTest |
| **E2E** | ✅ **PASS** | TenantE2EPhase5ProductionQATest 7/7 |
| **Payment/Finance/Isolation** | ✅ **PASS** | Phase4B/Phase4C, FinanceVisibility, TenantIsolation, BR19 = 35/35 |
| **Full PHP Suite** | ✅ **527 passed / 1 failed / 9 incomplete / 1650 assertions / 752.93s** | 1 failed = GoogleDrivePhotoServiceTest (kredensial, D, pre-existing) |
| **Frontend Build** | ✅ **PASS** | `✓ built in 22.29s` |

---

## 27. Remaining Risks

- **Automation/notifikasi warranty** (`notif.warranty_reminder`, listener `ServiceAutomationListener`) masih belum terdaftar di Provider — di luar scope BR-FIX-04 (dicatat).
- **Store warranty lifetime** tidak didukung (`end_date` NOT NULL); kebutuhan BR-013 dipenuhi via upstream `supplier_warranty_lifetime`.
- `ServiceWarrantyClaim.status` tidak memuat `refunded` — refund menandai klaim `completed` (vocab eksisting, sesuai instruksi "jangan invent vocab baru").
- `Service` `device()` relasi ditambahkan (memperbaiki bug laten `service->device`), perlu diverifikasi di seluruh surface UI setelahnya.
- **Non-cash refund** (transfer/QRIS) diposting lewat jalur `Expense` yang sama (tidak ada ledger rekening bank); `method` tersimpan di `SaleRefund` — keterbatasan didokumentasikan.
- Refund expense memakai `category='lainnya'` (enum SQLite tidak dilebarkan); identifikasi refund via `sale_refund_id` + awalan deskripsi "Refund".

---

## 28. BR-FIX-04.1 — Business Correction & Final Acceptance

Koreksi bisnis (Claim Approval → QC Resolution → Real Refund Financial Effect):

| Titik | Status / Perilaku |
|---|---|
| **Claim open** | `submitted` — `approved_by=NULL`, `checked_by=NULL`, `rework_service_id=NULL`; **tidak** membuat rework |
| **Approval** | `approved` — `approved_by` diisi; **rework dibuat tepat sekali** di titik approval; eligibility di-cek ulang; `rework_service_id` ter-link |
| **Rejection** | `rejected` — reason wajib; **tidak** membuat rework |
| **Repair finish** | klaim tetap `approved` (selesai teknisi ≠ resolusi warranty) |
| **QC fail** | klaim tetap `approved` (terbuka) — `completed_at=NULL`, `resolved_by=NULL`; rework kembali ke `dikerjakan` |
| **QC pass** | rework → `siap_diambil`; klaim → `completed` (titik kanonik QC-PASS / READY-PICKUP) + `completed_at` + `resolved_by` + `resolution_note` |
| **Refund — SaleRefund** | ✅ ya (event auditable append-only; original Payment tidak diubah) |
| **Refund — cash/finance posting** | ✅ ya — `Expense` cash-out (atomik, `sale_refund_id` ter-link, `profit = revenue − expenses`) |
| **Refund — Workspace action** | ✅ ya — tombol + Modal `Overlay/Modal`, `POST warranty-claims.refund`, refresh; disembunyikan sesuai permission/saldo/cabang; backend tetap menolak HTTP langsung |

Keputusan titik resolusi: **QC PASS / READY-PICKUP** adalah titik kanonik resolusi klaim (bukan repair finish, bukan pickup/close) — sesuai preferensi pada task Step 7.

### Final Acceptance Verification

| Item | Hasil | Bukti |
|---|---|---|
| **BR-011** | ✅ **PASS** | `BR11WarrantyReworkTest` 28/28 (113 assertions) — OPEN→REVIEW→APPROVE→REWORK→REPAIR→QC→RESOLUTION berurutan |
| **BR-012** | ✅ **PASS** | `BR12WarrantyRefundTest` 18/18 (82 assertions) — SaleRefund + real cash-out + Workspace path |
| **BR-013** | ✅ **PASS** | `BR13DistributorWarrantyTest` 7/7 (24 assertions) |
| BR-FIX-01 regression | ✅ PASS | BR07/08/09 23/23 |
| BR-FIX-02 regression | ✅ PASS | BR17/04/05 + isolation |
| BR-FIX-03 regression | ✅ PASS | BR01/BR16/PermissionEngine/BR19 |
| Lifecycle + E2E | ✅ PASS | ServiceFullLifecycle + TenantE2EPhase5 |
| Payment/Finance/Isolation | ✅ PASS | Phase4B/4C, FinanceVisibility, TenantIsolation |
| Full PHP Suite | ✅ **527 passed / 1 failed / 9 incomplete / 1650 assertions / 752.93s** | 1 failed = `GoogleDrivePhotoServiceTest` (kredensial, D, pre-existing) |
| Frontend Build | ✅ **PASS** | `✓ built in 22.29s` |

---

## VERDICT (BR-FIX-04.1 final)

| Scope | Hasil |
|---|---|
| **BR-011 — Warranty Repair Return** | ✅ **PASS** — urutan kanonik OPEN (submitted) → REVIEW → APPROVE (rework dibuat tepat sekali) → REPAIR → QC FAIL (klaim tetap terbuka) / QC PASS (klaim diresolusi di titik kanonik). Original service/financial history tidak berubah. 28/28 tes. |
| **BR-012 — Warranty Refund** | ✅ **PASS** — refund = `SaleRefund` (auditable) **DAN** real cash-out `Expense` (profit = revenue − expenses), plus operational Workspace path (Modal → endpoint). Original payment tidak diubah, tanpa perubahan stok. 18/18 tes. |
| **BR-013 — Distributor/Supplier Warranty** | ✅ **PASS** — store vs upstream distinguish, upstream lebih panjang/lifetime direpresentasikan jujur; 7/7 tes. |
