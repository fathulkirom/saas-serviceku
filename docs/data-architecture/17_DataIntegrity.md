# 17 — Data Integrity

> **Sprint 6.2A · Blueprint Only.** Invariant data — apa yang **tidak boleh terjadi** di ServiceKU.

---

## 1. Invariant Global

| # | Invariant | Konsekuensi jika dilanggar |
|---|---|---|
| I01 | **Tidak boleh ada data lintas tenant** (tenant isolation) | Kebocoran data, pelanggaran regulasi |
| I02 | **Tidak boleh ada hard delete untuk data transaksional** | Data hilang, audit trail putus |
| I03 | **Tidak boleh stok negatif** | Ketidakakuratan inventori, kerugian |
| I04 | **Tidak boleh finance orphan** (transaksi tanpa jejak finance) | Laporan keuangan tidak akurat |
| I05 | **Tidak boleh transaksi tanpa origin trace** (`request_id` / audit) | Tidak terlacak |

---

## 2. Invariant per Domain

| Domain | Invariant |
|---|---|
| **Tenant** | Satu tenant = satu DB. Tidak boleh ada dua tenant dengan subdomain sama. |
| **Branch** | Cabang hanya milik satu tenant. Tidak boleh hapus cabang dengan transaksi aktif. |
| **User** | Minimal satu user dengan role `owner` aktif per tenant. Email unik per tenant. |
| **Role** | Role resmi 7 tidak bisa dihapus (sistem). Permission hanya dari registry. |
| **Customer** | Tidak boleh ada duplikat (telepon unik per tenant — deteksi, bukan blokir keras). Tidak hapus customer dengan servis aktif. |
| **Device** | IMEI/serial unik per tenant. Tidak hapus device berriwayat servis. |
| **Request** | Request harus punya `customer_id` (atau walk-in guest) + `branch_id`. `request_id` immutable di domain turunan. |
| **Service Order** | Harus punya `request_id` (kecuali legacy). Tidak boleh transisi status mundur (kecuali reversal). Void = owner/admin saja. |
| **Sales Order** | Harus punya `request_id` (retail dari Request). Stok keluar hanya saat `success`. Void → rollback stok & kas. |
| **Purchase Order** | Tidak boleh "terima" tanpa PO. Pembayaran tidak boleh melebihi total PO. |
| **Warranty** | Hanya dari Service Order `selesai`. Claim hanya dalam periode policy. |
| **Claim** | Harus punya warranty. Resolution wajib diisi sebelum resolved. |
| **Replacement** | Wajib memengaruhi inventory (masuk/keluar). |
| **Cash Shift** | Tidak boleh dua shift terbuka di cabang yang sama. Selisih kas wajib dicatat. |
| **Deposit** | Hanya owner/admin yang konfirmasi. |
| **Inventory Movement** | Setiap mutasi wajib tercatat (append-only). Tidak boleh update stok langsung tanpa movement. |
| **Finance** | Setiap peristiwa finansial wajib tercatat. Tidak boleh menghapus catatan finance. |
| **Compensation** | Wajib mengikuti policy. Harus punya dasar (Service Order). |
| **Policy** | Setiap revisi = versi baru; versi lama tetap berlaku untuk data historis. |

---

## 3. Mekanisme Penjagaan

| Mekanisme | Untuk invariant |
|---|---|
| **Unique constraint** | I02, I04 (duplikasi), I06, I07, I10, I13 |
| **Foreign key** | I05 (request_id), I16 (warranty→service), I17 (claim→warranty) |
| **Check constraint / validasi** | I03 (stok ≥ 0), I18 (shift open), I19 (pembayaran ≤ total) |
| **Application logic** | I01 (tenant scope), I08 (soft delete), I11 (void permission), I20 (append movement) |
| **Audit + History** | Semua invariant — untuk mendeteksi pelanggaran |
| **Policy validation** | I21 (kompensasi), I22 (garansi) |

---

## 4. Aturan

1. **Invariant = tidak bisa dikompromikan.** Setiap pelanggaran harus dicegah, bukan ditangani setelahnya.
2. **Constraint di DB + validasi di aplikasi** — dua lapis (defense in depth).
3. **Data historis tidak diubah** — jika salah, koreksi via reversal (BR-015), bukan update langsung.
4. **Tenant isolation** = lapis paling fundamental — setiap query HARUS tenant-scoped.

---

## 5. Verifikasi

Konsisten dengan `docs/domain/Aggregate.md` (invariant per aggregate), `docs/domain-validation/BusinessRealityValidation.md` (BR-015 Human Error), prinsip **Data Is Sacred** & **Tenant Data Isolation**.
