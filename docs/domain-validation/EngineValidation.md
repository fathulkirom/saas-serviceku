# ServiceKU — Engine Validation

> **Sprint 6.1A · Blueprint Validation.** Audit seluruh engine terhadap Business Reality & prinsip.
> Format per engine: Masih valid? · Kekurangan · Bertentangan? · Terlalu kompleks? · Terlalu sederhana? · Rekomendasi.

---

## EV-01 — Customer Engine
- **Masih valid?** ✅ Valid.
- **Kekurangan:** Deteksi duplikat pelanggan belum eksplisit; kunjungan multi-device (BR-019) perlu kardinalitas Visit→ServiceOrder **0..n**; segmentasi/loyalitas = future.
- **Bertentangan?** Tidak.
- **Terlalu kompleks?** Tidak — tepat.
- **Terlalu sederhana?** Sedikit (duplikat & multi-device visit).
- **Rekomendasi:** Revisi kecil: dukungan multi-device visit + aturan anti-duplikat.

## EV-02 — Service Engine
- **Masih valid?** ✅ Valid (core domain).
- **Kekurangan:** Pickup branch (BR-001), talangan part (BR-002), spesialisasi teknisi (BR-006), teknisi eksternal (BR-009), part upgrade (BR-017), progressive work order (BR-018).
- **Bertentangan?** Tidak.
- **Terlalu kompleks?** Berisiko kompleks (banyak tanggung jawab) — **arahkan** detail transisi ke Workflow Engine.
- **Terlalu sederhana?** Tidak — sudah kaya.
- **Rekomendasi:** Pertahankan sebagai core; pindahkan aturan transisi & persetujuan ke Workflow/Policy Engine agar tidak membengkak.

## EV-03 — Workflow Engine
- **Masih valid?** ✅ Valid (target).
- **Kekurangan:** Belum menangani: progressive WO (BR-018), warranty resolution (BR-012), alur reversal human error (BR-015).
- **Bertentangan?** Tidak.
- **Terlalu kompleks?** Tidak.
- **Terlalu sederhana?** Sedikit — perlu hook `onEnter` (notifikasi, audit, efek samping).
- **Rekomendasi:** Prioritaskan; jadikan tempat transisi status (Service, POS, Purchase, Warranty, Subscription) + reversal flow.

## EV-04 — Policy Engine
- **Masih valid?** ✅ Valid (target) — **engine paling dibutuhkan**.
- **Kekurangan:** Banyak BR bergantung padanya: HumanError (BR-015), Compensation (BR-016), harga/upgrade (BR-017), garansi (BR-012), komisi partner (BR-009/010), talangan (BR-002).
- **Bertentangan?** Tidak — justru menegakkan "Configuration over Code".
- **Terlalu kompleks?** Tidak.
- **Terlalu sederhana?** **Perlu prioritas** — saat ini masih target; jadikan salah satu fondasi 6.2.
- **Rekomendasi:** Naikkan prioritas implementasi. Tipe policy awal: `compensation`, `warranty`, `pricing`, `human_error`, `commission`.

## EV-05 — Compensation Engine
- **Masih valid?** ✅ Valid (target).
- **Kekurangan:** Bergantung Policy (BR-016) + Finance; lifetime cost (BR-014) memakainya.
- **Bertentangan?** Tidak.
- **Terlalu kompleks?** Tidak — tepat.
- **Terlalu sederhana?** Tidak.
- **Rekomendasi:** Implementasi bersama Policy Engine; kompensasi = komponen biaya Finance.

## EV-06 — Inventory Engine
- **Masih valid?** ✅ Valid.
- **Kekurangan:** Cluster stock (BR-005) butuh scope Gudang/StockCluster; talangan part (BR-002) memengaruhi mutasi; replacement (BR-013) masuk stok.
- **Bertentangan?** Tidak.
- **Terlalu kompleks?** Tidak.
- **Terlalu sederhana?** Sedikit — scope stok (branch vs cluster) perlu dibuat fleksibel (additive).
- **Rekomendasi:** Desain `InventoryItem` agar scope-nya branch ATAU cluster (additive, tidak memaksa migrasi). Cluster = future/Pro+.

## EV-07 — Supplier Engine
- **Masih valid?** ✅ Valid.
- **Kekurangan:** Supplier claim (BR-013) + replacement masih target; rating/lead time = future.
- **Bertentangan?** Tidak.
- **Terlalu kompleks?** Tidak.
- **Terlalu sederhana?** Sedikit — claim perlu status & alur.
- **Rekomendasi:** Implementasi target (SupplierClaim→Replacement→Inventory) di 6.2+; jaga invariant.

## EV-08 — Finance Engine
- **Masih valid?** ✅ Valid.
- **Kekurangan:** Lifetime cost (BR-014), talangan (BR-002) reconciliation, kompensasi (BR-016) sebagai biaya.
- **Bertentangan?** Tidak.
- **Terlalu kompleks?** Tidak — tapi perlu **aggregate table** (Scalability) agar laporan cepat.
- **Terlalu sederhana?** Sedikit — belum formal (double-entry = future).
- **Rekomendasi:** Tambah kebutuhan laporan (lifetime cost, talangan) + aggregate/rollup untuk kinerja.

## EV-09 — Warranty Engine
- **Masih valid?** ✅ Valid.
- **Kekurangan:** Resolution type (BR-012), supplier warranty (BR-013) — keduanya sudah tercakup di Domain Model target.
- **Bertentangan?** Tidak.
- **Terlalu kompleks?** Tidak.
- **Terlalu sederhana?** Sedikit — perlu `ResolutionType` (re-service/replacement/refund/reject).
- **Rekomendasi:** Revisi kecil: tambah ResolutionType pada Claim; alur ke Service/Inventory/Finance.

## EV-10 — Permission Engine
- **Masih valid?** ✅ Valid (target) — pusat otorisasi.
- **Kekurangan:** Delegation/override (BR-011) belum dimodelkan.
- **Bertentangan?** Tidak — mendukung "No Single Point Of Failure".
- **Terlalu kompleks?** Tidak.
- **Terlalu sederhana?** Sedikit — perlu konsep **Delegation** (temporary grant + audit).
- **Rekomendasi:** Tambah Delegation (berjangka, expire, revoke, log) — target prioritas tinggi.

## EV-11 — Dashboard Engine
- **Masih valid?** ✅ Valid (target).
- **Kekurangan:** Lifetime cost view (BR-014); widget builder = future.
- **Bertentangan?** Tidak.
- **Terlalu kompleks?** Tidak.
- **Terlalu sederhana?** Tidak.
- **Rekomendasi:** Supported; tambah laporan lifetime cost sebagai widget.

## EV-12 — Subscription Engine
- **Masih valid?** ✅ Valid.
- **Kekurangan:** Hybrid store (BR-008) — plan/modul harus mendukung kombinasi (bukan terikat business type tunggal).
- **Bertentangan?** Tidak.
- **Terlalu kompleks?** Tidak.
- **Terlalu sederhana?** Tidak.
- **Rekomendasi:** Pastikan ERD 6.2 memisahkan `Module` dari `BusinessType` (template hanya seeding) — validasi keputusan ERP Modular.

## EV-13 — Module Engine
- **Masih valid?** ✅ Valid (target) — **enabler perluasan**.
- **Kekurangan:** Hybrid (BR-008) & cluster stock (BR-005) diaktifkan via module baru (`multi_warehouse`).
- **Bertentangan?** Tidak.
- **Terlalu kompleks?** Tidak.
- **Terlalu sederhana?** Tidak.
- **Rekomendasi:** Registry modul sebagai dasar seluruh perluasan; daftarkan modul future eksplisit.

## EV-14 — Branch Engine
- **Masih valid?** ✅ Valid.
- **Kekurangan:** Pickup branch (BR-001) & transfer device; cluster stock (BR-005) butuh relasi Branch→StockCluster.
- **Bertentangan?** Tidak.
- **Terlalu kompleks?** Tidak.
- **Terlalu sederhana?** Sedikit.
- **Rekomendasi:** Perluas: mutasi device antar cabang (analog transfer stok) + (future) relasi ke StockCluster.

---

## Ringkasan Engine

| Engine | Valid? | Tindakan utama |
|---|---|---|
| Customer | ✅ | + multi-device visit, anti-duplikat |
| Service | ✅ | pertahankan core; detail → Workflow/Policy |
| Workflow | ✅ | + progressive WO, warranty resolution, reversal |
| Policy | ✅ | **prioritas tertinggi** (fondasi banyak BR) |
| Compensation | ✅ | implementasi bersama Policy |
| Inventory | ✅ | scope branch/cluster fleksibel (additive) |
| Supplier | ✅ | implementasi claim→replacement |
| Finance | ✅ | + aggregate/rollup, lifetime cost |
| Warranty | ✅ | + ResolutionType |
| Permission | ✅ | + Delegation/override |
| Dashboard | ✅ | + lifetime cost view |
| Subscription | ✅ | pisahkan Module vs BusinessType |
| Module | ✅ | registry = dasar perluasan |
| Branch | ✅ | + transfer device, (future) cluster |

**Tidak ada engine yang bertentangan dengan Business Reality. Tidak ada engine yang harus dirombak.** Seluruh penyesuaian bersifat additive (atribut/VO/kardinalitas) atau implementasi target.
