# 02 — Master Data Tables

> **Sprint 6.2D · Table Blueprint Only.** Spesifikasi tabel Master (L3) — customers, devices, suppliers, service_partners, products + legacy customer_visits.
> Format per tabel: 13 poin analisis. **Tidak ada SQL.**

---

## M01 — `customers`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Menyimpan identitas pelanggan tenant. Sumber semua Request & transaksi. |
| **2. Aggregate Owner** | ✅ Customer Aggregate Root. |
| **3. Lifecycle** | dibuat → aktif → inactive/blacklist → soft delete → arsip (7 tahun). |
| **4. Business Responsibility** | Data PII (L3) — nama, telepon, alamat. Wajib untuk Request (kecuali walk-in guest). |
| **5. Primary Key** | `id` BIGINT UNSIGNED AUTO_INCREMENT. |
| **6. Foreign Key** | — (tidak ada FK keluar; customer adalah root). FK masuk: `requests.customer_id`, `devices.customer_id`. |
| **7. Candidate Unique** | `(tenant_id, phone)` — unique constraint untuk deteksi duplikat. |
| **8. Index** | `(tenant_id, name)` — full-text untuk pencarian; `(tenant_id, phone)` — exact lookup; `(tenant_id, created_at)` — list terbaru. |
| **9. Soft Delete?** | ✅ Soft delete (`deleted_at`). Tidak hard delete — PII & histori. |
| **10. Audit?** | ✅ Audit log untuk create/update/delete. |
| **11. History?** | ✅ Change log (`history_logs`) untuk perubahan nama/telepon/alamat. |
| **12. Retention** | 7 tahun setelah inactive; arsip; bisa dianonymize (UU PDP). |
| **13. Future** | Segmentasi tag, loyalitas poin, customer portal, blacklist flag. |

---

## M02 — `devices`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Menyimpan perangkat (asset) milik customer — HP, laptop, tablet, aksesoris. |
| **2. Aggregate Owner** | ✅ Device Aggregate Root. |
| **3. Lifecycle** | didaftarkan → aktif → ganti pemilik/arsip. Tidak hard delete jika berriwayat servis. |
| **4. Business Responsibility** | IMEI/serial = identitas unik. Riwayat servis (via request_devices → requests → service_orders). Lifetime cost (BR-014). |
| **5. Primary Key** | `id` BIGINT UNSIGNED AUTO_INCREMENT. |
| **6. Foreign Key** | `customer_id` → `customers(id)` NOT NULL. |
| **7. Candidate Unique** | `(tenant_id, imei)` UNIQUE (jika diisi); `(tenant_id, serial_number)` UNIQUE (jika diisi). |
| **8. Index** | `(tenant_id, customer_id)` — device per customer; `(tenant_id, type, brand)` — pencarian; `(imei)` — exact lookup. |
| **9. Soft Delete?** | ✅ Soft delete. |
| **10. Audit?** | ✅ Audit create/update. |
| **11. History?** | ✅ Change log (perubahan pemilik, IMEI). |
| **12. Retention** | 7 tahun setelah servis terakhir. |
| **13. Future** | Device model compatibility, part otomatis, IoT telemetry. |

---

## M03 — `suppliers`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Menyimpan data pemasok — sumber Purchase Order & SupplierClaim. |
| **2. Aggregate Owner** | ✅ Supplier Aggregate Root. |
| **3. Lifecycle** | dibuat → aktif → nonaktif → soft delete. |
| **4. Business Responsibility** | Kontak, saldo hutang. FK dari purchase_orders, suplier_claims. |
| **5. Primary Key** | `id` BIGINT UNSIGNED AUTO_INCREMENT. |
| **6. Foreign Key** | — (root). |
| **7. Candidate Unique** | `(tenant_id, name)` — opsional unik; `(tenant_id, phone)` — deteksi duplikat. |
| **8. Index** | `(tenant_id, name)` full-text. |
| **9. Soft Delete?** | ✅ Soft delete. |
| **10. Audit?** | ✅ Create/update. |
| **11. History?** | ❌ (jarang berubah). |
| **12. Retention** | 7 tahun. |
| **13. Future** | Rating supplier, lead time, auto-reorder. |

---

## M04 — `service_partners`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Pihak eksternal — servis dilempar (onpartner) atau teknisi eksternal (BR-009). |
| **2. Aggregate Owner** | ✅ ServicePartner Aggregate Root. |
| **3. Lifecycle** | dibuat → aktif → nonaktif → soft delete. |
| **4. Business Responsibility** | Partner servis; capability (teknisi/logistik); komisi (policy). |
| **5. Primary Key** | `id` BIGINT UNSIGNED AUTO_INCREMENT. |
| **6. Foreign Key** | — (root). |
| **7. Candidate Unique** | `(tenant_id, name)` opsional. |
| **8. Index** | `(tenant_id, is_active)` — filter partner aktif. |
| **9. Soft Delete?** | ✅ Soft delete. |
| **10. Audit?** | ✅ Create/update. |
| **11. History?** | ❌. |
| **12. Retention** | 7 tahun. |
| **13. Future** | Rating, komisi policy, capability matrix. |

---

## M05 — `products`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Katalog produk/sparepart — dijual & dipakai servis. |
| **2. Aggregate Owner** | ✅ Product Aggregate Root. |
| **3. Lifecycle** | dibuat → aktif → discontinued → soft delete. |
| **4. Business Responsibility** | Nama, SKU, barcode, harga beli/jual. Stok via inventory_items. |
| **5. Primary Key** | `id` BIGINT UNSIGNED AUTO_INCREMENT. |
| **6. Foreign Key** | — (root). |
| **7. Candidate Unique** | `(tenant_id, sku)` UNIQUE; `(tenant_id, barcode)` UNIQUE (jika diisi). |
| **8. Index** | `(tenant_id, category)` — filter; `(tenant_id, name)` full-text; `(barcode)` exact. |
| **9. Soft Delete?** | ✅ Soft delete. |
| **10. Audit?** | ✅ Create/update. |
| **11. History?** | ✅ **Snapshot harga** (`product_prices` — tabel terpisah): setiap perubahan harga → snapshot. |
| **12. Retention** | 7 tahun. |
| **13. Future** | Grade/variant (BR-017), part compatibility per device model, bundling. |

---

## M06 — `customer_visits` (LEGACY)

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Data historis kunjungan pelanggan (didepresiasi oleh ADR-001). Tidak digunakan untuk entry point baru. |
| **2. Aggregate Owner** | ✅ CustomerVisit Aggregate Root (legacy). |
| **3. Lifecycle** | dibuat (legacy) → selesai. Tidak dibuat untuk data baru. |
| **4. Business Responsibility** | Data historis. Entry point baru = `requests(type=walk_in)`. |
| **5. Primary Key** | `id` BIGINT UNSIGNED AUTO_INCREMENT. |
| **6. Foreign Key** | `customer_id` → `customers(id)`. |
| **7. Candidate Unique** | — |
| **8. Index** | `(tenant_id, customer_id, created_at)`. |
| **9. Soft Delete?** | ✅ Soft delete. |
| **10. Audit?** | ❌ (legacy). |
| **11. History?** | ❌. |
| **12. Retention** | 7 tahun. |
| **13. Future** | Tidak ada — tabel legacy, tidak dikembangkan. |

---

## Verifikasi

Semua master table mengikuti `18_DataStandards.md` (Sprint 6.2A). PK = BIGINT UNSIGNED AUTO_INCREMENT. Soft delete wajib. Audit wajib. History = snapshot untuk harga, change log untuk customer/device.
