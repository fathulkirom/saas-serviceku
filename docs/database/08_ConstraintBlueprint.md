# 08 — Constraint Blueprint

> **Sprint 6.2D · Table Blueprint Only.** Spesifikasi constraint — UNIQUE, CHECK, NOT NULL.
> **Tidak ada SQL.**

---

## 1. UNIQUE Constraints

| Tabel | Kolom | Scope | Alasan |
|---|---|---|---|
| `tenants` | `subdomain` | Global | Tidak boleh duplikat subdomain |
| `tenants` | `email` | Global | Tidak boleh duplikat email tenant |
| `branches` | `(tenant_id, name)` | Per tenant | Tidak boleh duplikat nama cabang |
| `users` | `(tenant_id, email)` | Per tenant | Tidak boleh duplikat email user |
| `roles` | `(tenant_id, key)` | Per tenant | Tidak boleh duplikat key role |
| `permissions` | `key` | Global | Tidak boleh duplikat key permission |
| `customers` | `(tenant_id, phone)` | Per tenant | Deteksi duplikat (soft — peringatan, bukan tolak) |
| `devices` | `(tenant_id, imei)` | Per tenant | Tidak boleh duplikat IMEI (jika diisi) |
| `devices` | `(tenant_id, serial_number)` | Per tenant | Tidak boleh duplikat serial |
| `products` | `(tenant_id, sku)` | Per tenant | Tidak boleh duplikat SKU |
| `products` | `(tenant_id, barcode)` | Per tenant | Tidak boleh duplikat barcode |
| `requests` | `(tenant_id, request_number)` | Per tenant | Nomor request unik |
| `service_orders` | `(tenant_id, service_number)` | Per tenant | Nomor servis unik |
| `sales_orders` | `(tenant_id, invoice_number)` | Per tenant | Nomor invoice unik |
| `purchase_orders` | `(tenant_id, po_number)` | Per tenant | Nomor PO unik |
| `warranties` | `service_order_id` | Per tenant | 1:1 garansi per servis |
| `policies` | `(tenant_id, type, version)` | Per tenant | Tidak boleh duplikat versi policy |
| `provider_credentials` | `(tenant_id, provider_type, provider_key)` | Per tenant | Tidak boleh duplikat provider |
| `inventory_items` | `(branch_id, product_id)` | Per tenant | Tidak boleh duplikat stok |
| `request_devices` | `(request_id, device_id)` | — | Tidak boleh duplikat pivot |
| `role_permission` | `(role_id, permission_id)` | — | Tidak boleh duplikat pivot |
| `user_role` | `(user_id, role_id)` | — | Tidak boleh duplikat pivot |

---

## 2. CHECK Constraints (Konsep — validasi aplikasi)

| Tabel | Aturan |
|---|---|
| `inventory_movements` | `qty != 0`. Stok tidak negatif = enforced di aplikasi (SUM >= 0). |
| `cash_shifts` | Maksimal 1 shift terbuka per branch — enforced di aplikasi. |
| `sales_orders` | `total_amount >= 0`. |
| `purchase_orders` | `total_amount >= 0`. |
| `deposits` | `amount > 0`. |
| `warranty_claims` | `claim_date BETWEEN warranty.start_date AND warranty.end_date` — enforced di aplikasi. |

---

## 3. NOT NULL Constraints

| Kolom | Tabel | Alasan |
|---|---|---|
| `request_id` | `service_orders` | NULLABLE — backward compatible dengan data legacy. Data BARU = NOT NULL (aplikasi). |
| `request_id` | `sales_orders` | NULLABLE — backward compatible. |
| `customer_id` | `requests` | NULLABLE — walk-in guest. |
| `pickup_branch_id` | `requests` | NULLABLE — hanya untuk pickup/courier. |
| `device_id` | `request_devices` | NOT NULL. |
| `deleted_at` | Semua L3/L4/L5 | NULLABLE — NULL = belum dihapus. |

---

## 4. Verifikasi

Unique constraint mencegah duplikasi data kritis. CHECK constraint minimal (validasi utama di aplikasi — defense in depth). NOT NULL fleksibel untuk backward compatibility.
