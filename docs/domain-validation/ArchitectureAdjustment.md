# ServiceKU — Architecture Adjustment

> **Sprint 6.1A · Blueprint Validation.** Perubahan (delta) yang disetujui atas Core Domain Model (`docs/domain/`) hasil validasi. **Blueprint only** — dokumen ini MENCATAT perubahan untuk dipertimbangkan di ERD 6.2, tidak mengubah source.
> Format: ADJ-01..n · Dokumen domain terkait · Perubahan · Alasan · Prioritas.

---

## 1. Daftar Penyesuaian Disetujui

| # | Dokumen domain terkait | Perubahan | Alasan (BR) | Prioritas |
|---|---|---|---|---|
| ADJ-01 | `DomainRelationship.md`, `Entity.md` | **Kardinalitas CustomerVisit → ServiceOrder: 0..1 → 0..n** (satu kunjungan banyak tiket, per device) | BR-019 | P0 |
| ADJ-02 | `Entity.md`, `ValueObject.md`, `Aggregate.md` | Tambah VO `PickupLocation` pada ServiceOrder + alur mutasi device antar cabang (Branch Engine) | BR-001 | P0 |
| ADJ-03 | `ValueObject.md`, `Engine.md` (Service/Finance) | Tambah VO `PartCostBearing` (customer/supplier/toko) pada pemakaian sparepart + reconciliation talangan di Finance | BR-002 | P0 |
| ADJ-04 | `Entity.md`, `ValueObject.md` | Tambah `Skill`/`Specialization` pada User (teknisi) + matching saat `assign_technician` | BR-006 | P0 |
| ADJ-05 | `Entity.md` (ServicePartner) | Perluas ServicePartner dengan `capability` (mis. teknisi eksternal) + policy komisi | BR-009 | P0 |
| ADJ-06 | `PermissionEngine.md`, `Engine.md` | Tambah konsep **Delegation** (temporary grant permission/role, expiry, revoke, audit) | BR-011 | P0 |
| ADJ-07 | `Entity.md`, `ValueObject.md`, `Engine.md` (Warranty) | Tambah VO `ResolutionType` (re-service/replacement/refund/reject) pada Claim + alur ke Service/Inventory/Finance | BR-012 | P0 |
| ADJ-08 | `Engine.md` (Finance/Report) | Tambah kebutuhan **laporan lifetime cost per device** (Device+Service+Part+Warranty+Compensation) | BR-014 | P0 |
| ADJ-09 | `Entity.md`, `Engine.md` (Workflow/Policy) | Tambah `CorrectionRecord`/ReversalLog + alur koreksi human error (reversal, approval, audit) + policy `human_error` | BR-015 | P0 |
| ADJ-10 | `Entity.md`, `ValueObject.md` | Tambah grade/variant pada Product + opsi part upgrade + policy surcharge | BR-017 | P0 |
| ADJ-11 | `Entity.md` (WorkOrder) | Konfirmasi WorkOrder sebagai child ServiceOrder 0..n **progresif** (dapat ditambahkan bertahap) | BR-018 | P0 |
| ADJ-12 | `Engine.md` (Supplier/Warranty/Inventory) | Formal implementasi target: SupplierClaim → Replacement → Inventory (invariant wajib) | BR-013 | P1 |
| ADJ-13 | `ModuleEngine.md`, `SubscriptionEngine.md` | **Pisahkan Module dari BusinessType** — template hanya seeding onboarding; hybrid store = kombinasi modul | BR-008 | P0 |
| ADJ-14 | `Engine.md` (Policy/Compensation) | Prioritaskan implementasi **Policy Engine** (tipe: compensation, warranty, pricing, human_error, commission) + Compensation Engine | BR-015/016 | P0 |
| ADJ-15 | `CoreDomain.md`, `Entity.md`, `FutureExpansion.md` | Daftarkan **StockCluster/Gudang** sebagai domain future (additive; scope Inventory branch ATAU cluster) | BR-005 | P2 |
| ADJ-16 | `Scalability.md`, `Engine.md` (Finance) | Tambah **aggregate/rollup** untuk laporan (lifetime cost, talangan) agar tidak hitung ulang real-time | BR-014 | P1 |

---

## 2. Prinsip Perubahan

1. **Semua additive** — tidak ada perubahan yang menghapus/mengubah struktur inti yang sudah berjalan.
2. **Backward compatible** — perubahan tidak memaksa migrasi data besar tenant.
3. **Diaktifkan sesuai kebutuhan** — fitur baru hanya aktif bila tenant butuh (modul/plan/policy).
4. **Diimplementasikan di 6.2+** — sprint ini hanya **mencatat** (validation), tidak menulis kode/ERD.

---

## 3. Dokumen yang Perlu Diperbarui (Sprint 6.1 → revisi)

| Dokumen | Perubahan yang masuk |
|---|---|
| `docs/domain/DomainRelationship.md` | ADJ-01 (kardinalitas) |
| `docs/domain/Entity.md` | ADJ-02, 03, 04, 05, 07, 09, 10, 11, 15 |
| `docs/domain/ValueObject.md` | ADJ-02, 03, 04, 07, 10 |
| `docs/domain/Aggregate.md` | ADJ-02, 11, 15 |
| `docs/domain/Engine.md` | ADJ-02, 03, 06, 07, 08, 09, 12, 13, 14, 15, 16 |
| `docs/domain/DomainEvent.md` | ADJ-06 (delegation event), 09 (reversal event) |
| `docs/domain/Ownership.md` | ADJ-06 (delegasi), 09 (koreksi) |
| `docs/domain/FutureExpansion.md` | ADJ-13, 15 |
| `docs/architecture-engine/*` | ADJ-13 (Module vs BusinessType), ADJ-14 (Policy prioritas), ADJ-16 (aggregate) |

> Pembaruan dokumen dilakukan pada **sprint revisi berikutnya** (bukan sekarang — sprint ini validation only).

---

## 4. Catatan Anti-Regresi

- Perubahan **tidak boleh** melemahkan: tenant isolation, invariant Business Reality chain (Replacement→Inventory→Finance), guard "minimal satu owner aktif", Data Is Sacred (tidak hapus fisik).
- Setiap penambahan atribut/VO harus tetap mengikuti daftar resmi (`docs/Naming.md`) — jangan membuat key baru tanpa persetujuan.
