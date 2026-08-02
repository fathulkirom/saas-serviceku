# ServiceKU — Decision Log (Sprint 6.1A)

> **Sprint 6.1A · Blueprint Validation.** Catatan keputusan yang diambil selama validasi. Setiap keputusan dapat ditelusuri ke Business Reality / engine / prinsip.
> Tanggal: 2026-08-02 · Status: `DRAFT` (validasi) → akan menjadi `APPROVED` saat Sprint 6.2 (ERD) menerapkannya.

---

## DEC-001 — Visit→ServiceOrder 0..n
- **Kasus:** BR-019 (Customer Visit Multi Device).
- **Keputusan:** Ubah kardinalitas CustomerVisit → ServiceOrder dari 0..1 menjadi **0..n**.
- **Alasan:** satu kunjungan dapat membawa banyak device → banyak tiket.
- **Dampak:** ADJ-01; `DomainRelationship.md`, `Entity.md`.
- **Status:** DRAFT → APPROVED saat 6.2.

## DEC-002 — Pickup branch (Multi Branch Pickup)
- **Kasus:** BR-001.
- **Keputusan:** Tambah VO `PickupLocation` pada ServiceOrder + mutasi device antar cabang (reuse mekanisme movement).
- **Alasan:** layanan lintas cabang tanpa menjadi transaksi penjualan.
- **Dampak:** ADJ-02.
- **Status:** DRAFT.

## DEC-003 — Talangan sparepart (PartCostBearing)
- **Kasus:** BR-002.
- **Keputusan:** Tambah atribut penanggung biaya part (`customer/supplier/toko`) pada pemakaian sparepart + reconciliation Finance.
- **Alasan:** jejak siapa menanggung biaya part talangan; data is sacred.
- **Dampak:** ADJ-03.
- **Status:** DRAFT.

## DEC-004 — Teknisi: spesialisasi & eksternal
- **Kasus:** BR-006, BR-009.
- **Keputusan:** Tambah `Skill`/`Specialization` pada User; perluas ServicePartner dengan `capability` (teknisi eksternal) + policy komisi.
- **Alasan:** alokasi sesuai keahlian; freelancer tercatat.
- **Dampak:** ADJ-04, ADJ-05.
- **Status:** DRAFT.

## DEC-005 — Delegation (No Single Point Of Failure)
- **Kasus:** BR-011.
- **Keputusan:** Tambah konsep **Delegation** (temporary permission/role grant, expiry, revoke, audit) di Permission/Role Engine.
- **Alasan:** take over / override / delegation tanpa titik kegagalan tunggal.
- **Dampak:** ADJ-06.
- **Status:** DRAFT.

## DEC-006 — Warranty ResolutionType
- **Kasus:** BR-012.
- **Keputusan:** Tambah VO `ResolutionType` (re-service/replacement/refund/reject) pada Claim + alur terkait.
- **Alasan:** penyelesaian garansi harus eksplisit & auditable.
- **Dampak:** ADJ-07.
- **Status:** DRAFT.

## DEC-007 — Lifetime cost sebagai laporan
- **Kasus:** BR-014.
- **Keputusan:** Lifetime cost = reporting aggregation (bukan entitas baru), via Finance/Report Engine + aggregate table.
- **Alasan:** profitabilitas per device/pelanggan; hindari hitung ulang real-time.
- **Dampak:** ADJ-08, ADJ-16.
- **Status:** DRAFT.

## DEC-008 — Human Error Policy & reversal
- **Kasus:** BR-015.
- **Keputusan:** Buat tipe policy `human_error` + `CorrectionRecord`/ReversalLog dengan approval & audit.
- **Alasan:** koreksi aman tanpa merusak data (Data Is Sacred).
- **Dampak:** ADJ-09.
- **Status:** DRAFT.

## DEC-009 — Part Upgrade (grade/variant)
- **Kasus:** BR-017.
- **Keputusan:** Tambah grade/variant pada Product + opsi part upgrade + policy surcharge.
- **Alasan:** pelanggan dapat memilih part lebih baik dengan selisih harga.
- **Dampak:** ADJ-10.
- **Status:** DRAFT.

## DEC-010 — WorkOrder progresif
- **Kasus:** BR-018.
- **Keputusan:** Konfirmasi WorkOrder sebagai child ServiceOrder 0..n **progresif** (ditambahkan bertahap saat ditemukan kerusakan baru).
- **Alasan:** servis berkembang seiring diagnosa.
- **Dampak:** ADJ-11.
- **Status:** DRAFT.

## DEC-011 — Module terpisah dari BusinessType (Hybrid Store)
- **Kasus:** BR-008.
- **Keputusan:** **Mempertahankan & menegaskan** keputusan ERP Modular: tabel `modules`/`features` terpisah dari business type; business type = template seeding onboarding.
- **Alasan:** hybrid store (service+retail+gadget) = kombinasi modul, bukan business type tunggal.
- **Dampak:** ADJ-13; validasi `docs/architecture-engine/BusinessTemplateEngine.md`.
- **Status:** DRAFT (validasi keputusan existing → KONFIRMASI).

## DEC-012 — Policy Engine sebagai prioritas
- **Kasus:** BR-015, BR-016.
- **Keputusan:** Naikkan **Policy Engine** ke prioritas tertinggi implementasi (tipe: compensation, warranty, pricing, human_error, commission).
- **Alasan:** banyak Business Reality bergantung pada aturan sebagai data (Configuration over Code).
- **Dampak:** ADJ-14.
- **Status:** DRAFT.

## DEC-013 — StockCluster/Gudang ditunda (future)
- **Kasus:** BR-005.
- **Keputusan:** **TIDAK masuk 6.2.** Catat sebagai domain future (P2); desain `InventoryItem` agar scope branch ATAU cluster (additive).
- **Alasan:** tidak menghalangi ERD; hindari kompleksitas sebelum kebutuhan nyata; Simple by Default.
- **Dampak:** ADJ-15; `FutureExpansion.md`.
- **Status:** DRAFT (deferred).

## DEC-014 — Tidak ada redesign fundamental
- **Kasus:** seluruh validasi.
- **Keputusan:** Domain inti (Tenant, Branch, Service, Customer, Device, Inventory, Finance, Warranty, Subscription) **tidak dirombak**; hanya penambahan additive.
- **Alasan:** 20/20 BR terpenuhi; 0 kasus TIDAK didukung.
- **Dampak:** kesimpulan LULUS.
- **Status:** KEPUTUSAN AKHIR.

---

## Catatan
- Semua keputusan **DRAFT** akan menjadi **APPROVED** saat diterapkan pada Sprint 6.2 (ERD) & revisi dokumen domain.
- Tidak ada keputusan yang mengubah source code / struktur project / business type / role resmi.
