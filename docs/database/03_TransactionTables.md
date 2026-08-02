# 03 — Transaction Tables

> **Sprint 6.2D · Table Blueprint Only.** Spesifikasi tabel Transaksional (L4) — Request, ServiceOrder, SalesOrder, PurchaseOrder, Warranty, CashShift, Post-Sale.
> Format per tabel: 13 poin analisis. **Tidak ada SQL.**

---

## T01 — `requests`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | **ADR-001 Core Entry Point.** Semua interaksi operasional masuk sebagai Request — walk-in, pickup, WA, marketplace, API. |
| **2. Aggregate Owner** | ✅ Request Aggregate Root. |
| **3. Lifecycle** | 14 status: draft→created→scheduled→waiting_pickup→picked_up→in_transit→received→assigned→processing→completed→delivered→closed/cancelled→archived. |
| **4. Business Responsibility** | Funnel semua channel; fork ke service_orders/sales_orders/warranties. `request_id` immutable di turunan. |
| **5. Primary Key** | `id` BIGINT UNSIGNED AUTO_INCREMENT. |
| **6. Foreign Key** | `customer_id` → customers(id) NULLABLE (walk-in guest); `branch_id` → branches(id) NOT NULL; `pickup_branch_id` → branches(id) NULLABLE (BR-001). |
| **7. Candidate Unique** | `(tenant_id, request_number)` UNIQUE. |
| **8. Index** | `(tenant_id, branch_id, status, created_at)` — list aktif; `(tenant_id, customer_id)` — per customer; `(request_number)` exact; `(tenant_id, type, status)` — filter channel. |
| **9. Soft Delete?** | ✅ Soft delete + cascade soft ke turunan. |
| **10. Audit?** | ✅ request_history (append-only, tabel terpisah). |
| **11. History?** | ✅ `request_history` — setiap perubahan status/assign. |
| **12. Retention** | 1 tahun setelah closed → arsip; 7 tahun total. |
| **13. Future** | AI auto-classify, customer self-service portal. |

---

## T02 — `request_devices` (Pivot N:M)

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Hubungkan 1 Request dengan N Device. BR-019 multi-device visit. |
| **2. Aggregate Owner** | ❌ Pivot — child of Request. |
| **3. Lifecycle** | dibuat saat Request fork; immutable. |
| **4. Business Responsibility** | 1 baris per device dalam 1 Request. ServiceOrder dibuat per device. |
| **5. Primary Key** | Composite: `(request_id, device_id)`. |
| **6. Foreign Key** | `request_id` → requests(id) ON DELETE CASCADE; `device_id` → devices(id). |
| **7. Candidate Unique** | `(request_id, device_id)` UNIQUE. |
| **8. Index** | `(device_id)` — riwayat servis per device; `(request_id)` — device per request. |
| **9. Soft Delete?** | ❌ (ikut request cascade). |
| **10. Audit?** | ❌ (via request_history). |
| **11. History?** | ❌. |
| **12. Retention** | Ikut requests. |
| **13. Future** | — |

---

## T03 — `service_orders`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Core domain — tiket servis dengan 14 status. |
| **2. Aggregate Owner** | ✅ ServiceOrder Aggregate Root. |
| **3. Lifecycle** | menunggu_alokasi→diterima→diagnosa→dikerjakan→menunggu_konfirmasi_pelanggan/internal→siap_diambil→selesai→diambil/close. indent/onpartner/cancel/void. |
| **4. Business Responsibility** | Eksekusi servis; biaya jasa + part; checklist; assignment teknisi. |
| **5. Primary Key** | `id` BIGINT UNSIGNED AUTO_INCREMENT. |
| **6. Foreign Key** | `request_id` → requests(id) NULLABLE (legacy) IMMUTABLE; `customer_id` → customers(id); `device_id` → devices(id) via request_devices; `branch_id` → branches(id). |
| **7. Candidate Unique** | `(tenant_id, service_number)` UNIQUE. |
| **8. Index** | `(tenant_id, branch_id, status)` — list aktif; `(request_id)` — origin trace; `(customer_id)` — per customer; `(device_id)` — per device; `(technician_id)` — assignment. |
| **9. Soft Delete?** | ✅ Soft delete. |
| **10. Audit?** | ✅ Audit setiap transisi status & perubahan biaya. |
| **11. History?** | ✅ Status history (service_history — bisa digabung audit). |
| **12. Retention** | 1 tahun setelah terminal → arsip; 7 tahun total. |
| **13. Future** | SLA, estimasi waktu, self-service tracking. |

---

## T04 — `sales_orders`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Transaksi penjualan (POS). |
| **2. Aggregate Owner** | ✅ SalesOrder Aggregate Root. |
| **3. Lifecycle** | draft→selesai→pending→success/failed/expired→refunded/void. |
| **4. Business Responsibility** | Stok keluar (via inventory_movements); kas bertambah (via cash_shifts). |
| **5. Primary Key** | `id` BIGINT UNSIGNED AUTO_INCREMENT. |
| **6. Foreign Key** | `request_id` → requests(id) NULLABLE; `customer_id` → customers(id) NULLABLE (walk-in); `branch_id` → branches(id); `cashier_id` → users(id). |
| **7. Candidate Unique** | `(tenant_id, invoice_number)` UNIQUE. |
| **8. Index** | `(tenant_id, branch_id, status)` — list; `(request_id)`; `(customer_id)`; `(cashier_id, created_at)` — performa kasir. |
| **9. Soft Delete?** | ✅ Soft delete. |
| **10. Audit?** | ✅ Audit create/void/refund. |
| **11. History?** | ✅ Status history. |
| **12. Retention** | 3 tahun → arsip; 7 tahun total. |
| **13. Future** | Split payment, e-invoice. |

---

## T05 — `sale_items`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Item dalam transaksi penjualan (keranjang). |
| **2. Aggregate Owner** | ❌ Child of SalesOrder. |
| **5. Primary Key** | `id` BIGINT. FK: `sales_order_id` → sales_orders(id) ON DELETE CASCADE; `product_id` → products(id). |
| **7. Candidate Unique** | `(sales_order_id, product_id)` UNIQUE (satu produk satu baris per transaksi). |
| **8. Index** | `(product_id)` — laporan penjualan per produk. |

---

## T06 — `purchase_orders`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Pembelian ke supplier. |
| **2. Aggregate Owner** | ✅ PurchaseOrder Aggregate Root. |
| **3. Lifecycle** | draft→PO→terima→bayar→close/void. |
| **5. Primary Key** | `id` BIGINT. FK: `supplier_id` → suppliers(id); `branch_id` → branches(id) (opsional). |
| **7. Candidate Unique** | `(tenant_id, po_number)` UNIQUE. |
| **8. Index** | `(tenant_id, supplier_id, status)`; `(po_number)`. |
| **9. Soft Delete?** | ✅. |

---

## T07 — `purchase_items`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Item per PO. |
| **5. Primary Key** | `id` BIGINT. FK: `purchase_order_id` CASCADE; `product_id` → products(id). |

---

## T08 — `warranties`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Garansi dari service selesai. |
| **2. Aggregate Owner** | ✅ Warranty Aggregate Root. |
| **3. Lifecycle** | aktif→diklaim→resolved/expired. |
| **5. Primary Key** | `id` BIGINT. FK: `service_order_id` → service_orders(id) UNIQUE (1:1). |
| **8. Index** | `(tenant_id, status)`; `(service_order_id)`. |
| **13. Future** | Garansi berbayar, transfer garansi. |

---

## T09 — `warranty_claims`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Klaim garansi (BR-012). |
| **5. Primary Key** | `id` BIGINT. FK: `warranty_id` → warranties(id); `resolution_type` VARCHAR — re-service/replacement/refund/reject. |
| **8. Index** | `(warranty_id, status)`. |

---

## T10 — `suplier_claims`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Klaim ke supplier (BR-013, target). |
| **5. Primary Key** | `id` BIGINT. FK: `warranty_claim_id` → warranty_claims(id); `supplier_id` → suppliers(id). |
| **Status** | Target — belum implementasi. |

---

## T11 — `replacements`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Barang pengganti dari supplier claim (BR-013, target). |
| **5. Primary Key** | `id` BIGINT. FK: `suplier_claim_id` → suplier_claims(id); `product_id` → products(id). Wajib hasilkan inventory_movement (StockIn). |
| **Status** | Target — belum implementasi. |

---

## T12 — `cash_shifts`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Shift kasir — buka/tutup kas. |
| **2. Aggregate Owner** | ✅ CashShift Aggregate Root. |
| **3. Lifecycle** | buka→transaksi→tutup→final. |
| **5. Primary Key** | `id` BIGINT. FK: `branch_id` → branches(id); `cashier_id` → users(id). |
| **Invariant** | Tidak boleh 2 shift terbuka di branch sama. |
| **9. Soft Delete?** | ❌ (tidak boleh hapus). |

---

## T13 — `deposits`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Setoran harian dari shift. |
| **5. Primary Key** | `id` BIGINT. FK: `shift_id` → cash_shifts(id); `confirmed_by` → users(id) NULLABLE. |

---

## T14 — `expenses`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Pengeluaran/biaya operasional. |
| **5. Primary Key** | `id` BIGINT. FK: `branch_id` → branches(id); `category_id` — opsional. |

---

## Verifikasi

13 tabel transaksional + 4 post-sale. Semua memakai `request_id` (ADR-001). Amount = BIGINT (sen). Status = VARCHAR. Soft delete wajib. Audit wajib. Konsisten dengan `docs/erd/` (Sprint 6.2C).
