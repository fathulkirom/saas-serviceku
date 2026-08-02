# Scalability — ServiceKU

> Analisis **skalabilitas** (target). 5 kasus utama yang memengaruhi arsitektur ServiceKU, beserta strategi penanganan.
> ⚠️ Sebagian besar strategi adalah **target/roadmap**; kondisi saat ini dicantumkan per kasus.

---

## Kasus 1 — Pertumbuhan Data per Tenant (Tiket & Transaksi)

**Masalah:** tiket servis, transaksi POS, dan log bertambah terus (ribuan–jutaan baris per tenant).

**Strategi:**
- Indeks pada kolom pencarian/filter (`tenant`, `status`, `created_at`, `service_number`).
- Agregasi laporan pada **tabel ringkasan** (report cache / aggregate) — bukan hitung ulang real-time.
- Arsip data lama (soft delete / arsip tahunan).
- Pagination & filter wajib di semua list (sudah ada di UI).

**Saat ini:** DB SQLite (dev) / MySQL (prod); query langsung. **Target:** agregasi & indeks dioptimasi.

---

## Kasus 2 — Banyak Tenant (Multi-Tenancy)

**Masalah:** jumlah tenant bertambah → banyak database (`tenant_*`) + beban platform.

**Strategi:**
- Pola **1 DB per tenant** (stancl/tenancy v3) — isolasi penuh, tanpa cross-tenant query (sudah berjalan).
- Koneksi database dikelola pool; koneksi dibuka per request sesuai tenant.
- Central DB hanya untuk data platform (tenant, plan, payment, super admin).
- Monitoring per-tenant (kuota, ukuran DB) untuk deteksi tenant besar.

---

## Kasus 3 — Multi-Cabang & Transfer Stok

**Masalah:** operasional lintas cabang → konsistensi stok & laporan gabungan.

**Strategi:**
- Model **cabang** dengan stok per cabang; transfer stok = mutasi tercatat (sudah ada).
- Laporan gabungan via agregasi per cabang (bukan double-write).
- Batas cabang sesuai plan (Subscription Engine).

---

## Kasus 4 — File & Storage (Foto Servis, Dokumen)

**Masalah:** foto servis, lampiran, dokumen SOP bertambah → penyimpanan.

**Strategi:**
- Simpan file di storage (S3-compatible untuk prod), path di DB.
- Kuota storage per plan (target — Subscription Engine).
- Compress/thumbnail foto servis.
- Backup & pembersihan berkala.

---

## Kasus 5 — Beban & Concurrency (POS & Kas)

**Masalah:** banyak kasir/transaksi bersamaan; race condition pada stok & kas.

**Strategi:**
- **Transactions & locking** (pessimistic/optimistic) pada update stok & saldo kas.
- Nomor transaksi unik per tenant/cabang (sequence).
- Antrian (queue) untuk notifikasi & tugas berat (sudah ada Jobs).
- Rate limit API & endpoint berat (target).

---

## 6. Matriks Ringkas

| Kasus | Saat Ini (source) | Target |
|---|---|---|
| Data per tenant | Query langsung, SQLite/MySQL | Agregasi, indeks, arsip |
| Banyak tenant | 1 DB per tenant ✅ | Pool koneksi, monitoring kuota |
| Multi-cabang | Transfer stok ada ✅ | Laporan gabungan ter-agregat |
| Storage | File di storage | Kuota per plan, thumbnail, backup |
| Concurrency | Queue (Jobs) ada | Locking stok/kas, rate limit |

---

## 7. Verifikasi

Fondasi (1 DB per tenant, transfer stok, queue) terkonfirmasi dari source. Strategi optimasi (agregasi, kuota, locking, rate limit) adalah **target/roadmap**.
