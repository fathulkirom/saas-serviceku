# 09 — Index Blueprint · 10 — Foreign Key Blueprint · 11 — Naming Convention · 12 — Data Type Standard

> **Sprint 6.2D · Table Blueprint Only.** Spesifikasi indeks, FK, penamaan, dan tipe data. Dokumen gabungan.
> **Tidak ada SQL.**

---

## Part A — Index Blueprint (09)

### Jenis Indeks

| Jenis | Penggunaan | Contoh |
|---|---|---|
| **PRIMARY** | PK — identitas unik | `id` BIGINT AUTO_INCREMENT |
| **UNIQUE** | Cegah duplikasi | `(tenant_id, email)`, `(tenant_id, service_number)` |
| **Composite (Lookup)** | Query paling sering: filter + sort | `(tenant_id, branch_id, status, created_at DESC)` |
| **Foreign Key** | Join + referential integrity | Setiap FK wajib diindeks |
| **Full-text** | Pencarian teks | `customers.name`, `products.name`, `service_orders.notes` |
| **Covering (Dashboard)** | Agregasi laporan | `(tenant_id, branch_id, status, total_amount)` untuk SUM |

### Indeks Kunci per Tabel (Frekuensi Query Tinggi)

| Tabel | Indeks kunci | Tujuan query |
|---|---|---|
| `requests` | `(tenant_id, branch_id, status, created_at DESC)` | Daftar request aktif |
| `requests` | `(tenant_id, customer_id, created_at DESC)` | Riwayat customer |
| `requests` | `(request_number)` | Pencarian exact |
| `service_orders` | `(tenant_id, branch_id, status, created_at DESC)` | Daftar servis aktif |
| `service_orders` | `(tenant_id, device_id, created_at DESC)` | Riwayat device |
| `service_orders` | `(service_number)` | Pencarian exact |
| `sales_orders` | `(tenant_id, branch_id, status, created_at DESC)` | Daftar penjualan |
| `inventory_movements` | `(inventory_item_id, created_at DESC)` | Mutasi stok |
| `audit_logs` | `(tenant_id, entity_type, entity_id, created_at DESC)` | Audit per entity |
| `notifications` | `(recipient_id, is_read, created_at DESC)` | Notifikasi user |

---

## Part B — Foreign Key Blueprint (10)

### Aturan FK

| Aturan | Detail |
|---|---|
| **Nama** | `<entity>_id` |
| **Tipe** | BIGINT UNSIGNED — sama dengan PK yang direferensi |
| **Nullable** | NULLABLE untuk backward compatibility (`request_id` di service_orders) |
| **ON DELETE** | CASCADE untuk child; RESTRICT untuk root yang tidak boleh dihapus |
| **ON UPDATE** | CASCADE (jarang — PK tidak berubah) |
| **Indeks** | Setiap FK wajib diindeks |

### FK Kunci

| Tabel (Child) | FK | Tabel (Parent) | ON DELETE |
|---|---|---|---|
| `service_orders` | `request_id` | `requests` | SET NULL (soft delete request → service tetap ada) |
| `sales_orders` | `request_id` | `requests` | SET NULL |
| `request_devices` | `request_id` | `requests` | CASCADE |
| `request_devices` | `device_id` | `devices` | RESTRICT |
| `work_orders` | `service_order_id` | `service_orders` | CASCADE |
| `warranties` | `service_order_id` | `service_orders` | RESTRICT |
| `warranty_claims` | `warranty_id` | `warranties` | CASCADE |
| `inventory_movements` | `inventory_item_id` | `inventory_items` | RESTRICT |
| `sale_items` | `sales_order_id` | `sales_orders` | CASCADE |
| `purchase_items` | `purchase_order_id` | `purchase_orders` | CASCADE |
| `deposits` | `shift_id` | `cash_shifts` | RESTRICT |

---

## Part C — Naming Convention (11)

| Objek | Konvensi | Contoh |
|---|---|---|
| **Tabel** | `snake_case` plural | `service_orders`, `sale_items`, `request_devices` |
| **PK** | `id` | `id` |
| **FK** | `<entity>_id` | `request_id`, `customer_id`, `pickup_branch_id` |
| **Pivot** | `<a>_<b>` (alfabetis) | `request_devices`, `role_permission`, `user_role` |
| **Timestamp** | `created_at`, `updated_at`, `deleted_at` | — |
| **Soft delete** | `deleted_at` TIMESTAMP NULL | — |
| **Status** | `status` VARCHAR(50) | `status` |
| **Amount** | `<context>_amount` BIGINT | `total_amount`, `cost_amount`, `deposit_amount` |
| **Boolean** | `is_<kondisi>` | `is_active`, `is_walk_in`, `is_system` |
| **JSON** | `<context>` (deskriptif) | `rules`, `metadata`, `credentials` |
| **Polymorphic** | `<context>_type` + `<context>_id` | `attachable_type`, `attachable_id` |

---

## Part D — Data Type Standard (12)

| Kategori | Tipe | Contoh | Alasan |
|---|---|---|---|
| **PK** | `BIGINT UNSIGNED AUTO_INCREMENT` | `id` | Kapasitas 9 miliar baris; cukup untuk 50+ tahun operasional. Central DB: UUID untuk keamanan (tidak bisa ditebak). |
| **FK** | `BIGINT UNSIGNED` | `customer_id` | Sama dengan PK referensi. |
| **String pendek** | `VARCHAR(100)` | `name`, `phone` | — |
| **String panjang** | `TEXT` | `notes`, `address`, `description` | — |
| **Nomor** | `VARCHAR(50)` | `service_number`, `IMEI`, `request_number` | Nomor = string — tidak ada operasi matematika. |
| **Status** | `VARCHAR(50)` | `menunggu_alokasi` | String enum — lebih readable dari integer; fleksibel untuk status baru (additive). |
| **Amount** | `BIGINT` | `total_amount` | Integer (sen). Rp 50.000 = 5000000. Akurat — tidak ada FLOAT. |
| **Quantity** | `INTEGER` / `DECIMAL(10,2)` | `qty` | Stok = integer; diskon persen = decimal. |
| **Boolean** | `BOOLEAN` / `TINYINT(1)` | `is_active` | — |
| **Tanggal** | `DATE` | `scheduled_at`, `claim_date` | — |
| **Timestamp** | `TIMESTAMP` | `created_at`, `updated_at` | — |
| **JSON** | `JSON` | `rules`, `metadata`, `credentials` | Hanya bila benar-benar perlu data semi-structured. |
| **UUID** | `CHAR(36)` / `UUID` | `tenants.id` (central) | Keamanan — ID tidak bisa ditebak. |

---

## Verifikasi

Semua konvensi konsisten dengan `docs/data-architecture/18_DataStandards.md` (Sprint 6.2A). Indeks, FK, naming, dan tipe data menjadi acuan tunggal seluruh migration.
