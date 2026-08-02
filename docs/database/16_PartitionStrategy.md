# 16 — Partition Strategy · 17 — Backup Impact · 18 — Performance Blueprint

> **Sprint 6.2D · Table Blueprint Only.** Analisis partisi, dampak backup, dan performa. Dokumen gabungan.
> **Belum implementasi — blueprint saja. Tidak ada SQL.**

---

## Part A — Partition Strategy (16)

### Tabel Kandidat Partisi

| Tabel | Estimasi baris/tahun | Perlu partisi? | Strategi |
|---|---|---|---|
| `audit_logs` | 500K–5M / tenant aktif | ✅ Ya (tenant besar) | PARTITION BY RANGE (`created_at`) — per bulan atau per tahun |
| `request_history` | 200K–2M / tenant aktif | ✅ Ya | PARTITION BY RANGE (`created_at`) — per tahun |
| `inventory_movements` | 100K–1M / tenant | ✅ Opsional (tenant besar) | PARTITION BY RANGE (`created_at`) — per tahun |
| `notifications` | 100K–1M / tenant | ✅ Opsional | PARTITION BY RANGE (`created_at`) — per bulan |
| `history_logs` | 50K–500K / tenant | ❌ (belum perlu) | — |
| `service_orders` | 10K–500K / tenant | ❌ (belum perlu — indeks cukup) | — |
| `sales_orders` | 10K–500K / tenant | ❌ | — |

### Aturan Partisi
- **Hanya untuk tabel append-only / log** — audit, history, inventory movement.
- **Partisi = transparan** — aplikasi tidak perlu tahu partisi.
- **Prune otomatis** — partisi lama di-drop setelah diarsipkan.
- **Belum implementasi** — blueprint untuk masa depan (tenant > 100K baris log).

---

## Part B — Backup Impact (17)

### Dampak Tabel ke Backup

| Tabel | Ukuran estimasi | Impact | Strategi |
|---|---|---|---|
| `audit_logs` | Besar (log) | ⚠️ Backup membesar | Arsip rutin; exclude arsip dari backup harian |
| `attachments` | Metadata kecil; file di storage | ✅ Ringan | — |
| `inventory_movements` | Sedang | ⚠️ | Arsip tahunan |
| `notifications` | Sedang–Besar | ⚠️ | Arsip > 1 tahun |
| Tabel master/transaksi | Kecil–Sedang | ✅ Ringan | Backup standar |

### Strategi Backup
- **Harian**: DB penuh (tenant DB + central DB) — tanpa tabel arsip.
- **Mingguan**: DB penuh + arsip metadata.
- **Bulanan**: Full backup + arsip + storage file.
- **Enkripsi**: AES-256; kunci per tenant.

---

## Part C — Performance Blueprint (18)

### Tabel Terbesar (Estimasi setelah 5 tahun tenant aktif)

| Tabel | Estimasi baris | Risiko |
|---|---|---|
| `audit_logs` | 2.5M–25M | Query lambat → partisi + arsip |
| `request_history` | 1M–10M | Partisi + arsip |
| `inventory_movements` | 500K–5M | Indeks + arsip |
| `notifications` | 500K–5M | Arsip > 1 tahun |
| `service_orders` | 50K–2.5M | Indeks cukup; arsip > 1 tahun |

### Tabel Paling Sering Query (Read)

| Tabel | Query utama |
|---|---|
| `requests` | Daftar request aktif (filter status + branch), pencarian nomor |
| `service_orders` | Daftar servis aktif (filter status + teknisi), pencarian nomor |
| `customers` | Pencarian nama/telepon, auto-complete |
| `products` | Pencarian nama/SKU/barcode |
| `audit_logs` | Investigasi per entity |

### Tabel Paling Sering Update

| Tabel | Operasi |
|---|---|
| `requests` | Status change (setiap transisi) |
| `service_orders` | Status change, assign teknisi |
| `inventory_items` | qty berubah via movement INSERT |
| `cash_shifts` | Transaksi selama shift |

### Strategi Performa

| Strategi | Detail |
|---|---|
| **Indeks optimal** | Composite index untuk query utama; hindari over-index. |
| **Arsip rutin** | Pindahkan data > 1 tahun dari tabel aktif. |
| **Aggregate/rollup** | `finance_transactions` = tabel agregat; dashboard query dari sini, bukan SUM real-time. |
| **Pagination** | Semua list wajib pagination (sudah ada). |
| **Eager loading** | FK di-join atau eager load untuk menghindari N+1. |
| **Queue** | Operasi berat (generate report, backup) = background job. |

---

## Verifikasi

Partisi = blueprint masa depan (belum implementasi). Backup = strategi harian/mingguan/bulanan. Performa = indeks + arsip + aggregate. Konsisten dengan Sprint 6.2A `13_IndexStrategy.md` dan `docs/architecture-engine/Scalability.md` (Sprint 5.2).
