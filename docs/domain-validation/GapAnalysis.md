# ServiceKU — Gap Analysis

> **Sprint 6.1A · Blueprint Validation.** Klasifikasi gap antara Core Domain Model dan Business Reality.
> Kategori: **GAP-NONE** (didukung) · **GAP-KECIL** (atribut/VO/kardinalitas/policy) · **GAP-BESAR** (domain baru) · **GAP-POLICY** (perlu policy).
> Prioritas: **P0** (wajib dipertimbangkan di ERD 6.2) · **P1** (segera setelah 6.2) · **P2** (future).

---

## 1. Ringkasan Gap

| Kategori | Jumlah | Rincian |
|---|---|---|
| GAP-NONE (didukung penuh) | 5 | BR-003, 004, 007, 010, 020 |
| GAP-KECIL (atribut/VO/kardinalitas/policy) | 12 | BR-001, 002, 006, 009, 011, 012, 013, 014, 015, 017, 018, 019 |
| GAP-BESAR (domain baru) | 1 | BR-005 (StockCluster/Gudang) |
| GAP-POLICY / target | 3 | BR-008 (hybrid via Module), BR-015 (policy), BR-016 (policy) |
| **Total kasus** | **20** | — |

> **0 kasus TIDAK didukung.** Tidak ada kebutuhan redesign fundamental.

---

## 2. Daftar Gap Detail

### GAP-KECIL (P0 — masuk pertimbangan ERD 6.2)

| # | Gap | Kasus | Perubahan yang dibutuhkan | Jenis |
|---|---|---|---|---|
| G1 | Pickup branch | BR-001 | VO `PickupLocation` pada ServiceOrder + mutasi device antar cabang (Branch Engine) | Atribut + alur |
| G2 | Talangan sparepart | BR-002 | VO `PartCostBearing` (customer/supplier/toko) pada pemakaian part + reconciliation Finance | Atribut + alur |
| G3 | Spesialisasi teknisi | BR-006 | VO `Skill`/`Specialization` pada User + matching saat assign | Atribut |
| G4 | Teknisi eksternal | BR-009 | `capability`/tipe pada ServicePartner + policy komisi | Atribut + policy |
| G5 | Delegation / override | BR-011 | Konsep `Delegation` (temporary grant, expiry, revoke, audit) di Permission/Role Engine | Konsep baru (ringan) |
| G6 | Warranty resolution | BR-012 | VO `ResolutionType` (re-service/replacement/refund/reject) pada Claim | Atribut |
| G7 | Supplier warranty | BR-013 | Implementasi target: SupplierClaim→Replacement→Inventory | Implementasi target |
| G8 | Lifetime cost | BR-014 | Laporan agregasi per device (Device+Service+Part+Warranty+Compensation) | Laporan |
| G9 | Human error reversal | BR-015 | `CorrectionRecord`/ReversalLog + alur reversal + policy | Atribut + policy |
| G10 | Part upgrade | BR-017 | Grade/variant pada Product + opsi pemakaian + policy surcharge | Atribut + policy |
| G11 | Progressive work order | BR-018 | Konfirmasi WorkOrder 0..n progresif (target) | Konfirmasi relasi |
| G12 | Multi-device visit | BR-019 | **Kardinalitas Visit→ServiceOrder 0..1 → 0..n** | Kardinalitas |

### GAP-BESAR (P2 — future, additive)
| # | Gap | Kasus | Perubahan | Jenis |
|---|---|---|---|---|
| G13 | Cluster stock | BR-005 | Domain baru **StockCluster/Gudang** + relasi Branch→Cluster; InventoryItem scope branch ATAU cluster | Domain baru (additive) |

### GAP-POLICY / TARGET (P0–P1)
| # | Gap | Kasus | Perubahan | Jenis |
|---|---|---|---|---|
| G14 | Hybrid store | BR-008 | Pisahkan `Module` dari `BusinessType`; template = seeding (keputusan ERP Modular) | Arsitektur (target) |
| G15 | Compensation policy | BR-016 | Implementasi Policy + Compensation Engine | Implementasi target |
| G16 | Human error policy | BR-015 | Tipe policy `human_error` | Policy |

---

## 3. Analisis Dampak Gap ke Prinsip

| Prinsip | Dampak gap | Status |
|---|---|---|
| Simple by Default | Semua gap bersifat additive; kasus umum (walk-in, servis dasar) tetap sederhana | ✅ aman |
| Progressive Complexity | G13 (cluster) & G14 (hybrid) hanya aktif bila tenant butuh (modul/plan) | ✅ aman |
| Configuration over Code | G5/G9/G15/G16 menegaskan Policy & Delegation sebagai data | ✅ diperkuat |
| Grow Without Migration | G13 didesain additive (scope stok fleksibel) tanpa migrasi data lama | ✅ aman (dengan disain) |
| No Single Point Of Failure | G5 (delegation) menutup celah; multi-owner sudah ada (BR-003) | ✅ ditutup |
| Tenant Data Isolation | Tidak ada gap yang melanggar isolasi tenant | ✅ aman |
| Business Driven | 20/20 kasus dipetakan dari realita | ✅ aman |
| Data Is Sacred | G9 (reversal dengan audit) memperkuat prinsip ini | ✅ diperkuat |

---

## 4. Kesimpulan Gap

- **Tidak ada gap struktural yang memaksa redesign.**
- 12 gap kecil = penambahan atribut/VO/kardinalitas/policy — **semua bisa ditampung di ERD 6.2 secara additive**.
- 1 gap besar (G13 cluster) = future, additive, tidak menghalangi 6.2.
- 3 gap policy/target = implementasi target yang sudah dimodelkan, bukan domain baru.
