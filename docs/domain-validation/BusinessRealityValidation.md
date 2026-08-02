# ServiceKU — Business Reality Validation

> **Sprint 6.1A · Blueprint Validation.** Simulasi seluruh Business Reality terhadap Core Domain Model (`docs/domain/`, Sprint 6.1).
> Status: **VALIDASI** — tidak membuat kode/DB/ERD/implementasi.
> Format per kasus: Business Problem · Current Domain Support · Mampu? · Entity · Aggregate · Engine · Lifecycle · Domain baru? · Policy saja? · Rekomendasi.

**Legenda status:**
- ✅ **YA** — domain sudah mampu (tanpa perubahan).
- 🟡 **YA (revisi kecil)** — mampu setelah penambahan atribut/VO/kardinalitas/policy.
- 🟠 **REVISI BESAR** — butuh penambahan domain baru / engine target.
- ❌ **TIDAK** — tidak didukung (tidak ditemukan pada kasus ini).

---

## BR-001 — Multi Branch Pickup
1. **Business Problem:** Pelanggan menitipkan device di Cabang A untuk diservis, tetapi ingin **mengambil di Cabang B** (atau sebaliknya). Device berpindah antar cabang tanpa menjadi penjualan.
2. **Current Domain Support:** Service Order berada di satu Branch; `Branch` & `transfer_stock` ada. Belum ada konsep "cabang penjemputan" berbeda dari "cabang servis" untuk sebuah tiket, dan belum ada mutasi fisik **device antar cabang** (yang ada transfer stok sparepart).
3. **Mampu?** 🟡 **YA (revisi kecil)**
4. **Entity:** ServiceOrder (+ atribut `pickup_branch`), Device, Branch, (opsional) DeviceTransfer.
5. **Aggregate:** ServiceOrder, Branch.
6. **Engine:** Service Engine (menetapkan pickup branch), Branch Engine (transfer device antar cabang).
7. **Lifecycle:** Service selesai di cabang servis → device ditransfer → diambil di pickup branch → `diambil`.
8. **Perlu domain baru?** Tidak. Cukup atribut `pickup_branch` + mutasi device (bisa dipakai mekanisme movement yang sudah ada).
9. **Hanya perlu policy?** Tidak — perlu atribut + alur mutasi device.
10. **Rekomendasi:** Tambah VO `PickupLocation` pada ServiceOrder; perluas Branch Engine untuk transfer device (analog `transfer_stock`). Masuk ke ERD 6.2.

---

## BR-002 — Talangan Sparepart
1. **Business Problem:** Toko **memakai stok sparepart sendiri** untuk menyelesaikan servis, tetapi biaya part **ditanggung dulu (talangan)** dan akan diganti — oleh pelanggan (ditagih) atau oleh klaim supplier (garansi). Perlu jejak siapa yang menanggung biaya part.
2. **Current Domain Support:** `SparepartUsed → StockOut` dan Inventory ada; Finance mengagregasi biaya; SupplierClaim (target) ada. Namun **atribut penanggung biaya** (customer/supplier/toko) pada pemakaian part belum eksplisit.
3. **Mampu?** 🟡 **YA (revisi kecil)**
4. **Entity:** ServiceOrder (pemakaian part), InventoryMovement, Claim/SupplierClaim, Finance.
5. **Aggregate:** ServiceOrder, InventoryItem, Warranty.
6. **Engine:** Service Engine (pemakaian part), Inventory Engine (stok), Finance Engine (reconciliation talangan), Warranty/Supplier Engine (klaim).
7. **Lifecycle:** part dipakai → `StockOut` → status talangan (ditanggung customer/supplier/toko) → ditagih/diklaim → selesai di Finance.
8. **Perlu domain baru?** Tidak.
9. **Hanya perlu policy?** Sebagian — **policy menentukan** siapa penanggung default; perlu atribut `cost_bearing` pada pemakaian part.
10. **Rekomendasi:** Tambah VO `PartCostBearing` (customer/supplier/toko) pada pemakaian sparepart + alur finance. Masuk 6.2.

---

## BR-003 — Multi Function Owner Family
1. **Business Problem:** Toko keluarga; **beberapa anggota keluarga** perlu akses level Owner (bukan satu orang).
2. **Current Domain Support:** Role `owner` dapat dipegang oleh banyak user (role = kumpulan permission; User→Role). Guard "minimal satu owner aktif" ada di invariant Aggregate.
3. **Mampu?** ✅ **YA**
4. **Entity:** User, Role, Permission.
5. **Aggregate:** User, Role.
6. **Engine:** Permission Engine, Role Engine, User Engine.
7. **Lifecycle:** user owner dibuat → aktif → (jika terakhir) tidak bisa dinonaktifkan tanpa pengganti.
8. **Perlu domain baru?** Tidak.
9. **Hanya perlu policy?** Tidak — mekanisme role sudah cukup.
10. **Rekomendasi:** Konfirmasi guard "minimal satu owner aktif" di ERD 6.2. Multi-role (target) menambah fleksibilitas.

---

## BR-004 — Manager Multi Function
1. **Business Problem:** Satu **Manager menangani banyak fungsi** (operasional + keuangan + pembelian) dalam satu peran.
2. **Current Domain Support:** Role `manager` sudah punya `manage_finance, manage_products, manage_customers, manage_sales, manage_cash_register, manage_deposits, manage_purchases, manage_indents, work_on_services` — **sudah multi-fungsi**.
3. **Mampu?** ✅ **YA**
4. **Entity:** User, Role, Permission.
5. **Aggregate:** User, Role.
6. **Engine:** Permission Engine.
7. **Lifecycle:** sesuai role.
8. **Perlu domain baru?** Tidak.
9. **Hanya perlu policy?** Tidak.
10. **Rekomendasi:** Supported. Multi-role per user (target) memperluas kombinasi (mis. manager+teknisi).

---

## BR-005 — Cluster Branch Stock
1. **Business Problem:** **Beberapa cabang berbagi satu gudang stok** (cluster/central warehouse). Stok bukan per-cabang terisolasi, melainkan **shared pool** untuk kelompok cabang.
2. **Current Domain Support:** Inventory **per Branch**; `transfer_stock` ada. **Belum ada** konsep "Stock Cluster"/Gudang yang dimiliki sekelompok cabang.
3. **Mampu?** 🟠 **REVISI BESAR** (perlu domain/aggregate baru — sudah terdaftar di FutureExpansion: multi-gudang)
4. **Entity:** (baru) **StockCluster / Gudang (Warehouse)**, BranchGroup, InventoryItem (berpindah scope ke cluster).
5. **Aggregate:** (baru) **StockCluster** (root), Branch, InventoryItem.
6. **Engine:** Branch Engine (perluas), Inventory Engine (scope cluster), Module Engine (module `multi_warehouse`, target).
7. **Lifecycle:** cluster dibuat → branch bergabung → stok bersama → (target) gudang diarsip.
8. **Perlu domain baru?** **YA — StockCluster/Gudang** (additive; tidak mengubah domain inti).
9. **Hanya perlu policy?** Tidak — butuh entitas baru.
10. **Rekomendasi:** Masuk **Future Expansion / Pro+**; jangan blokir 6.2. Desain ERD agar `InventoryItem` siap mendukung scope cabang ATAU cluster (additive).

---

## BR-006 — Technician Specialization
1. **Business Problem:** Teknisi punya **spesialisasi** (HP vs laptop vs MacBook); alokasi harus cocok dengan tipe device.
2. **Current Domain Support:** Role `technician`, `assign_technician` ada; Device punya tipe/merek/model. **Belum ada atribut spesialisasi** pada user/teknisi dan kecocokan saat assign.
3. **Mampu?** 🟡 **YA (revisi kecil)**
4. **Entity:** User (teknisi) + atribut `specialization`/`skill`, Device (tipe), ServiceOrder.
5. **Aggregate:** User, ServiceOrder.
6. **Engine:** Service Engine (matching saat assign), Permission Engine.
7. **Lifecycle:** tidak berubah — hanya filter alokasi.
8. **Perlu domain baru?** Tidak.
9. **Hanya perlu policy?** Sebagian — aturan kecocokan bisa jadi policy; butuh atribut skill.
10. **Rekomendasi:** Tambah VO `Skill`/`Specialization` pada User + logika rekomendasi assign di Service Engine. Masuk 6.2.

---

## BR-007 — Financial Ownership
1. **Business Problem:** Kepemilikan aksi finansial harus jelas: siapa boleh void, refund, konfirmasi setoran, approval kompensasi.
2. **Current Domain Support:** `manage_finance` (owner/admin/manager/head_store), `void_transactions` (owner/admin), `canConfirmDeposit` (owner/admin), `delete_models` (owner/admin), Ownership doc menetapkan ini.
3. **Mampu?** ✅ **YA**
4. **Entity:** User, Role, Permission, Deposit, SalesOrder, Compensation (target).
5. **Aggregate:** User, Role, CashShift.
6. **Engine:** Permission Engine, Finance Engine.
7. **Lifecycle:** sesuai permission.
8. **Perlu domain baru?** Tidak.
9. **Hanya perlu policy?** Opsional — ambang approval kompensasi via policy.
10. **Rekomendasi:** Supported. Untuk approval kompensasi bertingkat, gunakan Policy (target).

---

## BR-008 — Hybrid Store (Service + Retail + Gadget)
1. **Business Problem:** Satu toko **gabungan servis + retail + jual gadget baru/second** — bukan salah satu business type.
2. **Current Domain Support:** Business type saat ini **single value** (`Tenant::getBusinessTypes`). Target **ERP Modular** (`BusinessTemplateEngine`) memungkinkan kombinasi modul → hybrid = aktifkan modul service+retail+gadget.
3. **Mampu?** 🟠 **YA (target, lewat Module Engine)** — kondisi saat ini single type, belum hybrid.
4. **Entity:** Tenant, Module, Feature, BusinessTemplate (target).
5. **Aggregate:** Tenant, Module.
6. **Engine:** Module Engine, BusinessTemplateEngine, Subscription Engine.
7. **Lifecycle:** tenant onboarding pilih template hybrid → modul aktif.
8. **Perlu domain baru?** Tidak (modul sudah ada; kombinasi adalah fungsi Module Engine).
9. **Hanya perlu policy?** Tidak.
10. **Rekomendasi:** Ini **validasi utama** keputusan ERP Modular (Sprint 5.2). Di ERD 6.2, pastikan modul/feature **tidak terikat** business type tunggal (template hanya seeding).

---

## BR-009 — External Technician
1. **Business Problem:** Teknisi **bukan karyawan** (freelance/eksternal) ikut mengerjakan servis.
2. **Current Domain Support:** ServicePartner (pihak eksternal, `onpartner`) ada; tekniker = user tenant. **Belum ada** penanda teknisi eksternal yang bekerja sebagai teknisi (bukan sekadar partner lempar).
3. **Mampu?** 🟡 **YA (revisi kecil)**
4. **Entity:** ServicePartner (+ tipe/kapabilitas), User (opsional eksternal), ServiceOrder.
5. **Aggregate:** ServicePartner, ServiceOrder.
6. **Engine:** Service Engine (alokasi), Supplier/Partner Engine, Compensation Engine (komisi, target).
7. **Lifecycle:** terdaftar sebagai partner/teknisi eksternal → dikerjakan → selesai → komisi (target).
8. **Perlu domain baru?** Tidak — perluas ServicePartner dengan `capability` (teknisi) atau flag eksternal pada User.
9. **Hanya perlu policy?** Sebagian — komisi lewat policy.
10. **Rekomendasi:** Perluas ServicePartner (tipe/kapabilitas) + policy komisi. Masuk 6.2.

---

## BR-010 — Service Partner
1. **Business Problem:** Servis **dilempar ke partner** (status `onpartner`) — alur sudah ada.
2. **Current Domain Support:** Entity ServicePartner, status `onpartner`, Service Engine, Ownership (owner/admin/manager).
3. **Mampu?** ✅ **YA**
4. **Entity:** ServicePartner, ServiceOrder.
5. **Aggregate:** ServicePartner, ServiceOrder.
6. **Engine:** Service Engine.
7. **Lifecycle:** tiket → onpartner → kembali → dikerjakan → selesai.
8. **Perlu domain baru?** Tidak.
9. **Hanya perlu policy?** Tidak.
10. **Rekomendasi:** Supported. Detail komisi/biaya partner → policy (target).

---

## BR-011 — No Single Point Of Failure (Take Over, Override, Delegation)
1. **Business Problem:** Tidak boleh bergantung pada satu orang: **take over** saat owner/tim absen, **override** untuk aksi kritis, **delegation** wewenang sementara.
2. **Current Domain Support:** Multi-owner (BR-003) ✅; multi-manager ✅; Super Admin override platform ✅. **Delegation sementara** (grant permission berjangka) **belum dimodelkan**.
3. **Mampu?** 🟡 **YA (revisi kecil — delegation)**
4. **Entity:** User, Role, Permission, (baru) Delegation/OverrideLog (target).
5. **Aggregate:** User, Role.
6. **Engine:** Permission Engine (delegation), Role Engine.
7. **Lifecycle:** delegasi dibuat (ada masa berlaku) → aktif → berakhir/revoked → log.
8. **Perlu domain baru?** Tidak — tambah konsep **Delegation** (role/permission sementara + audit).
9. **Hanya perlu policy?** Sebagian — batas override via policy.
10. **Rekomendasi:** Tambah Delegation (temporary grant, expiry, audit) di Permission Engine (target). Prioritaskan bersama Policy Engine.

---

## BR-012 — Warranty Resolution
1. **Business Problem:** Klaim garansi perlu **jenis penyelesaian**: servis ulang / ganti part / ganti device / refund / tolak.
2. **Current Domain Support:** Warranty + Claim (`diterima`/`ditolak`) + Replacement (target) + servis ulang (service baru) ada. **Jenis penyelesaian (resolution type)** belum eksplisit.
3. **Mampu?** 🟡 **YA (revisi kecil)**
4. **Entity:** Warranty, Claim (+ `resolution_type`), Replacement, ServiceOrder (re-service).
5. **Aggregate:** Warranty.
6. **Engine:** Warranty Engine (kelola outcome), Service Engine (re-service), Inventory Engine (replacement).
7. **Lifecycle:** klaim → evaluasi → **resolution** (re-service/replacement/refund/reject) → selesai.
8. **Perlu domain baru?** Tidak.
9. **Hanya perlu policy?** Sebagian — syarat tiap resolution via policy.
10. **Rekomendasi:** Tambah VO `ResolutionType` pada Claim + alur ke re-service/replacement/refund. Masuk 6.2.

---

## BR-013 — Supplier Warranty
1. **Business Problem:** Garansi toko ke pelanggan **didukung klaim ke supplier** (SupplierClaim) → replacement.
2. **Current Domain Support:** SupplierClaim (target) + Replacement (target) sudah di Domain Model (`docs/domain/Entity.md`, `Factory.md`, `Engine.md`).
3. **Mampu?** 🟡 **YA (target — tinggal implementasi, bukan domain baru)**
4. **Entity:** Warranty, SupplierClaim, Replacement, Supplier, Inventory.
5. **Aggregate:** Warranty, PurchaseOrder (asal supplier), InventoryItem.
6. **Engine:** Supplier Engine, Warranty Engine, Inventory Engine.
7. **Lifecycle:** klaim → supplier claim → approved → replacement → stok masuk → selesai.
8. **Perlu domain baru?** Tidak (sudah di Domain Model sebagai target).
9. **Hanya perlu policy?** Sebagian — syarat klaim supplier.
10. **Rekomendasi:** Prioritas implementasi di 6.2+ (target sudah benar). Jaga invariant Replacement→Inventory.

---

## BR-014 — Lifetime Cost
1. **Business Problem:** Mengetahui **biaya seumur hidup** sebuah device/pelanggan: semua servis, part, garansi, kompensasi — untuk profitabilitas.
2. **Current Domain Support:** Finance mengagregasi transaksi; Device punya riwayat servis. **Belum ada** agregat/rollup "lifetime cost per device".
3. **Mampu?** 🟡 **YA (revisi kecil — agregasi laporan)**
4. **Entity:** Device, ServiceOrder, Finance, Compensation.
5. **Aggregate:** Device (konsumen), ServiceOrder.
6. **Engine:** Finance Engine (agregasi), Dashboard/Report Engine (view lifetime cost).
7. **Lifecycle:** agregasi query/rollup — tidak ada state baru.
8. **Perlu domain baru?** Tidak.
9. **Hanya perlu policy?** Tidak.
10. **Rekomendasi:** Lifetime cost = **reporting aggregation** (Device + Service + Part + Warranty + Compensation). Tambah sebagai laporan di Finance/Report Engine (target). Masuk 6.2 sebagai kebutuhan laporan.

---

## BR-015 — Human Error Policy
1. **Business Problem:** Kesalahan manusia (void salah, part salah, harga salah) harus punya **alur koreksi** yang aman: reversal, re-entry, approval, **audit penuh** — tanpa merusak data.
2. **Current Domain Support:** void/refund/cancel + audit trail (ServiceHistory, activity log) + `delete_models` dibatasi + prinsip **Data Is Sacred**. **Policy khusus human error** belum ada.
3. **Mampu?** 🟡 **YA (revisi kecil — policy + reversal flow)**
4. **Entity:** ServiceOrder, SalesOrder, InventoryMovement, Finance, (baru) CorrectionRecord/ReversalLog.
5. **Aggregate:** ServiceOrder, SalesOrder, InventoryItem.
6. **Engine:** Policy Engine (HumanErrorPolicy), Workflow Engine (reversal), Finance Engine, Inventory Engine (rollback).
7. **Lifecycle:** kesalahan → koreksi (reversal) → approval → audit tercatat → data final.
8. **Perlu domain baru?** Tidak — tambah **CorrectionRecord** (audit reversal) + policy.
9. **Hanya perlu policy?** **YA — ini terutama policy** + alur reversal.
10. **Rekomendasi:** Buat **HumanErrorPolicy** (tipe policy) + alur koreksi/reversal dengan approval & audit. Prioritaskan Policy Engine.

---

## BR-016 — Compensation Policy
1. **Business Problem:** Kompensasi teknisi/karyawan **mengikuti policy** tenant (persen/nominal/tier).
2. **Current Domain Support:** Compensation domain (target) + Compensation Engine (target) + Policy sudah di Domain Model (`docs/domain/`).
3. **Mampu?** 🟡 **YA (target — tinggal implementasi)**
4. **Entity:** Compensation, Policy, ServiceOrder, User, Finance.
5. **Aggregate:** Policy, Compensation.
6. **Engine:** Compensation Engine, Policy Engine, Finance Engine.
7. **Lifecycle:** event → hitung (policy) → approval → bayar → selesai.
8. **Perlu domain baru?** Tidak (sudah dimodelkan target).
9. **Hanya perlu policy?** **YA** — ini esensi policy.
10. **Rekomendasi:** Target sudah benar. Prioritaskan implementasi Compensation + Policy Engine.

---

## BR-017 — Part Upgrade
1. **Business Problem:** Pelanggan memilih **part yang lebih baik** dari standar saat servis; ada selisih harga.
2. **Current Domain Support:** Pemakaian part di ServiceOrder + pricing + policy harga ada. **Grade/opsi part** belum eksplisit.
3. **Mampu?** 🟡 **YA (revisi kecil)**
4. **Entity:** Sparepart/Product (+ grade/variant), ServiceOrder (pemakaian part + opsi).
5. **Aggregate:** ServiceOrder, InventoryItem.
6. **Engine:** Service Engine, Policy Engine (surcharge upgrade), Inventory Engine.
7. **Lifecycle:** tawarkan opsi → pilih upgrade → tambah biaya → part dipakai.
8. **Perlu domain baru?** Tidak.
9. **Hanya perlu policy?** Sebagian — aturan selisih harga via policy.
10. **Rekomendasi:** Tambah grade/variant pada Product + opsi pada pemakaian part + policy surcharge. Masuk 6.2.

---

## BR-018 — Progressive Work Order
1. **Business Problem:** Servis **berkembang progresif**: saat diagnosa ditemukan kerusakan tambahan → work order tambahan ditambahkan dalam satu tiket.
2. **Current Domain Support:** WorkOrder (target, optional) + status service progresif (diagnosa→dikerjakan→konfirmasi) ada. WorkOrder dapat ditambahkan bertahap.
3. **Mampu?** 🟡 **YA (revisi kecil)**
4. **Entity:** ServiceOrder, WorkOrder (target), Checklist.
5. **Aggregate:** ServiceOrder (induk), WorkOrder.
6. **Engine:** Service Engine, Workflow Engine.
7. **Lifecycle:** WO awal → temuan baru → WO tambahan → konfirmasi pelanggan → selesai.
8. **Perlu domain baru?** Tidak — WorkOrder sudah dimodelkan (target).
9. **Hanya perlu policy?** Sebagian — persetujuan biaya tambahan.
10. **Rekomendasi:** Konfirmasi WorkOrder sebagai child ServiceOrder (0..n, progresif). Masuk 6.2.

---

## BR-019 — Customer Visit (Multi Device)
1. **Business Problem:** Satu **kunjungan membawa banyak device** → banyak tiket servis dari satu kunjungan.
2. **Current Domain Support:** `CustomerVisit → 0..1 ServiceOrder` (kardinalitas saat ini di `DomainRelationship.md`). **Belum 0..n**.
3. **Mampu?** 🟡 **YA (revisi kecil — kardinalitas)**
4. **Entity:** CustomerVisit, Device, ServiceOrder.
5. **Aggregate:** Customer (visit), ServiceOrder.
6. **Engine:** Customer Engine, Service Engine (ServiceOrderFactory).
7. **Lifecycle:** kunjungan dicatat → **1..n Service Order** (per device) → masing-masing berjalan.
8. **Perlu domain baru?** Tidak.
9. **Hanya perlu policy?** Tidak.
10. **Rekomendasi:** **Ubah kardinalitas Visit→ServiceOrder dari 0..1 menjadi 0..n** di `DomainRelationship.md`. Masuk 6.2.

---

## BR-020 — Walk In Retail
1. **Business Problem:** Pelanggan **mampir tanpa terdaftar**, langsung beli di POS (guest checkout).
2. **Current Domain Support:** `SalesOrderFactory` sudah mendukung **Customer opsional** (anonim). POS walk-in didukung.
3. **Mampu?** ✅ **YA**
4. **Entity:** SalesOrder, SaleItem, CashShift.
5. **Aggregate:** SalesOrder, CashShift.
6. **Engine:** Commerce (POS), Customer Engine (opsional).
7. **Lifecycle:** keranjang → bayar → lunas (tanpa customer terdaftar).
8. **Perlu domain baru?** Tidak.
9. **Hanya perlu policy?** Tidak.
10. **Rekomendasi:** Supported. Opsional: penanda `is_walk_in` pada SalesOrder untuk laporan.

---

## Ringkasan Status

| Status | Jumlah | Kasus |
|---|---|---|
| ✅ YA (didukung penuh) | 5 | BR-003, BR-004, BR-007, BR-010, BR-020 |
| 🟡 YA (revisi kecil: atribut/VO/kardinalitas/policy) | 12 | BR-001, 002, 006, 009, 011, 012, 013, 014, 015, 017, 018, 019 |
| 🟠 Revisi besar (domain baru / target) | 3 | BR-005 (Gudang), BR-008 (hybrid via Module), BR-016 (Compensation target) |
| ❌ TIDAK didukung | 0 | — |
