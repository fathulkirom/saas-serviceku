# 13 — Index Strategy

> **Sprint 6.2A · Blueprint Only.** Strategi indeks — **konsep saja, bukan SQL**. Tujuan: memastikan performa query utama tanpa menjelaskan implementasi.

---

## 1. Konsep Indeks (Bukan SQL)

| Jenis Indeks | Digunakan untuk | Contoh kolom target |
|---|---|---|
| **B-tree** | Pencarian exact, range, sorting | `tenant_id`, `branch_id`, `status`, `created_at`, `request_id`, `customer_id`, `device_id`, `service_number`, `IMEI` |
| **Unique** | Mencegah duplikasi | `email` (per tenant), `service_number`, `request_number`, `sku` (per tenant) |
| **Full-text** | Pencarian teks | `customer.name`, `device.model`, `product.name`, `service.notes` |
| **Composite** | Query multi-kolom | `(tenant_id, branch_id, status)`, `(tenant_id, customer_id, created_at DESC)` |
| **Foreign Key** | Referential integrity + join | Semua FK: `request_id`, `branch_id`, `customer_id`, `device_id`, `technician_id` |

---

## 2. Indeks Kunci per Domain

| Domain | Indeks kunci (kolom — bukan SQL) | Alasan query utama |
|---|---|---|
| **Request** | (tenant_id, status, created_at), (customer_id), (request_number UNIQUE) | Daftar request aktif; cari per customer; cari per nomor |
| **Service Order** | (tenant_id, branch_id, status), (customer_id), (device_id), (technician_id), (request_id), (service_number UNIQUE) | Daftar servis; filter status/branch/teknisi; cek origin request |
| **Sales Order** | (tenant_id, branch_id, status), (customer_id), (cashier_id), (request_id), (invoice_number UNIQUE) | Daftar penjualan; laporan kasir; cek origin |
| **Customer** | (tenant_id, name FULLTEXT), (phone UNIQUE per tenant), (created_at) | Cari pelanggan; deteksi duplikat |
| **Device** | (tenant_id, customer_id), (IMEI UNIQUE per tenant), (serial UNIQUE per tenant) | Cari perangkat pelanggan; cek duplikat IMEI |
| **Product** | (tenant_id, category), (sku UNIQUE per tenant), (barcode), (name FULLTEXT) | Cari produk; scan barcode |
| **Supplier** | (tenant_id, name FULLTEXT) | Cari supplier |
| **Purchase Order** | (tenant_id, supplier_id, status), (po_number UNIQUE) | Daftar PO; hutang supplier |
| **Warranty** | (tenant_id, service_order_id), (status) | Cek garansi servis |
| **Cash Shift** | (tenant_id, branch_id, cashier_id, opened_at) | Shift aktif; laporan kasir |
| **Deposit** | (tenant_id, branch_id, status) | Setoran menunggu konfirmasi |
| **Inventory Movement** | (tenant_id, branch_id, product_id, created_at) | Mutasi stok; audit |
| **Audit Log** | (tenant_id, entity_type, entity_id, created_at) | Cari audit per entity |
| **History Log** | (tenant_id, entity_type, entity_id, created_at) | Cari history per entity |

---

## 3. Aturan Indeks

1. **Setiap FK wajib diindeks** — referential integrity + join performance.
2. **Kolom unique global** (email, service_number) diindeks unique **per tenant** (composite dengan `tenant_id`).
3. **Kolom status + created_at** = composite index untuk list/filter yang paling sering dipakai.
4. **Full-text index** terpisah dari B-tree — untuk pencarian teks.
5. **Jangan over-index** — setiap indeks memperlambat write. Indeks hanya untuk kolom yang benar-benar dicari/difilter/diurutkan.

---

## 4. Verifikasi

Konsisten dengan `12_SearchStrategy.md`. Detail indeks akan ditentukan di Sprint 6.2B (ERD Concept). Dokumen ini hanya konsep — **bukan SQL, bukan DDL**.
