# 18 — Data Standards

> **Sprint 6.2A · Blueprint Only.** Standar penamaan dan tipe data — harus diikuti seluruh ERD, migration, dan model.
> Memperluas `docs/Naming.md` (Sprint 4).

---

## 1. Konvensi Penamaan

| Objek | Konvensi | Contoh |
|---|---|---|
| **Tabel** | `snake_case` jamak | `requests`, `service_orders`, `sale_items` |
| **Kolom** | `snake_case` tunggal | `tracking_code`, `technician_id`, `pickup_branch_id` |
| **Primary Key** | `id` (increment/uuid) | `id` |
| **Foreign Key** | `<entity>_id` | `request_id`, `customer_id`, `branch_id` |
| **Pivot table** | `<a>_<b>` (alfabetis) | `user_role`, `role_permission` |
| **Timestamp** | `created_at`, `updated_at`, `deleted_at` | Laravel default |
| **Soft delete** | `deleted_at` + `deleted_by` | — |
| **Status column** | `status` (string enum) | `status` = `'menunggu_alokasi'` |
| **JSON column** | `data` / `features` / `metadata` | Untuk data semi-structured |
| **Boolean** | `is_<kondisi>` | `is_active`, `is_walk_in`, `is_system` |
| **Amount** | `<entity>_amount` (integer, dalam Rupiah/sen) | `total_amount`, `cost_amount` |

---

## 2. Tipe Data (Konseptual — bukan SQL)

| Kategori | Tipe | Contoh |
|---|---|---|
| **ID** | UUID / BigInt | `id`, `tenant_id` |
| **String pendek** | varchar(100) | `name`, `phone`, `email` |
| **String panjang** | text | `notes`, `address`, `description` |
| **Nomor** | varchar(50) | `service_number`, `request_number`, `IMEI` |
| **Status enum** | varchar(50) | `menunggu_alokasi`, `active`, `pending` |
| **Tanggal** | date / datetime | `created_at`, `scheduled_at` |
| **Amount** | bigint (integer, sen) | `total_amount` (Rp 50.000 = 5000000 dalam sen) |
| **Quantity** | integer / decimal(10,2) | `qty` (stok), `discount_percent` |
| **Boolean** | boolean / tinyint(1) | `is_active`, `is_walk_in` |
| **JSON** | json / text | `policy_rules`, `features`, `metadata` |

---

## 3. Konvensi Amount

- **Semua amount dalam satuan terkecil (sen)** — Rp 50.000 = `5000000` (bigint).
- Tidak ada FLOAT untuk uang — hindari pembulatan.
- Format tampilan: Rp (dari `useFormatter.js`).
- Kolom amount: `*_amount` (total_amount, cost_amount, deposit_amount).

---

## 4. Konvensi Status

- **Status = string snake_case** (bukan integer/enum DB).
- Daftar status resmi (`docs/Naming.md`): 14 service, 5 payment, 4 subscription, 14 request, dll.
- Jangan menambah status baru tanpa persetujuan.

---

## 5. Verifikasi

Konsisten dengan `docs/Naming.md` (Sprint 4) — diperluas dengan konvensi amount, FK, pivot, soft delete.
