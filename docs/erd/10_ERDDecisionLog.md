# 10 — ERD Decision Log

> **Sprint 6.2C · Conceptual Blueprint.** Semua keputusan desain ERD.

---

## DEC-E01 — `request_devices` pivot (N:M)
- **Keputusan:** Device↔Request = N:M via pivot `request_devices`.
- **Alasan:** BR-019 (multi-device visit) + corporate batch + repeat repair. 1:1 tidak cukup.
- **Alternatif ditolak:** FK `device_id` di `requests` (1:1).
- **Status:** FINAL.

## DEC-E02 — `request_id` di semua tabel transaksional
- **Keputusan:** `service_orders.request_id`, `sales_orders.request_id` FK ke `requests.id`. `warranties` via `service_orders.request_id`. **Nullable untuk legacy data.**
- **Alasan:** ADR-001 — origin trace tunggal.
- **Status:** FINAL.

## DEC-E03 — `request_id` immutable setelah fork
- **Keputusan:** Setelah `request_id` di-set (NOT NULL), tidak boleh UPDATE.
- **Alasan:** Origin trace harus permanen.
- **Status:** FINAL.

## DEC-E04 — Inventory = append-only movement
- **Keputusan:** `inventory_items.qty` adalah VIEW/AGGREGATE dari `SUM(inventory_movements.qty)`. Tidak ada UPDATE langsung ke `qty`.
- **Alasan:** Data Is Sacred; audit trail penuh.
- **Status:** FINAL.

## DEC-E05 — Attachments = polymorphic
- **Keputusan:** Satu tabel `attachments` dengan `attachable_type` + `attachable_id`.
- **Alasan:** Hindari tabel attachment per domain (service_photos, sales_attachments, product_images).
- **Alternatif ditolak:** Tabel attachment per entity.
- **Status:** FINAL.

## DEC-E06 — User ↔ Role = N:M (target pivot)
- **Keputusan:** Target: pivot `user_role`. Saat ini: 1 kolom `role` di `users`. Migrasi bertahap.
- **Status:** TARGET.

## DEC-E07 — Policy = versioning
- **Keputusan:** `policies` dengan `version`, `valid_from`, `valid_to`. Revisi = insert baru + update `valid_to` versi lama.
- **Alasan:** Kompensasi historis harus tetap mengacu policy saat itu.
- **Status:** FINAL.

## DEC-E08 — Amount dalam sen (bigint)
- **Keputusan:** Semua kolom amount = bigint (integer). Rp 50.000 = 5000000.
- **Alasan:** Hindari FLOAT; akurasi finansial.
- **Status:** FINAL.

## DEC-E09 — Soft delete semua transaksional
- **Keputusan:** Semua tabel L3/L4/L5 memiliki `deleted_at`. Tidak ada hard delete.
- **Status:** FINAL.

## DEC-E10 — Tenant DB vs Central DB
- **Keputusan:** L1 (platform) = Central DB. L2–L5 = Tenant DB. Tidak ada cross-DB query.
- **Status:** FINAL (existing architecture).

## DEC-E11 — CustomerVisit didepresiasi
- **Keputusan:** `customer_visits` tetap ada sebagai data legacy. Entry point baru = `requests(type=walk_in)`.
- **Status:** FINAL (ADR-001).

## DEC-E12 — `request_id` nullable untuk legacy
- **Keputusan:** FK `request_id` NULLABLE. Data existing tanpa `request_id` tetap berfungsi. Data baru wajib.
- **Alasan:** Backward compatible. Tidak perlu backfill.
- **Status:** FINAL.

## DEC-E13 — Tidak ada tabel marketplace/payment/AI
- **Keputusan:** Provider (marketplace, payment gateway, AI) = code/infrastructure, bukan entity. Hanya `provider_credentials`.
- **Alasan:** Sprint 6.2B — Provider Pattern.
- **Status:** FINAL.

---

## Ringkasan

| Status | Jumlah |
|---|---|
| FINAL | 12 |
| TARGET | 1 (user_role pivot) |
