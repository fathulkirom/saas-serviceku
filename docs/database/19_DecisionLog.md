# 19 — Decision Log (Sprint 6.2D)

> **Sprint 6.2D · Table Blueprint Only.** Seluruh keputusan desain tabel.

---

## DEC-T01 — BIGINT AUTO_INCREMENT untuk PK tenant DB
- **Keputusan:** Semua PK di tenant DB = BIGINT UNSIGNED AUTO_INCREMENT. Central DB: UUID untuk `tenants.id`.
- **Alasan:** BIGINT cukup untuk 9 miliar baris (50+ tahun); auto-increment efisien. UUID untuk tenant = keamanan (tidak bisa ditebak dari luar).
- **Status:** FINAL.

## DEC-T02 — Amount = BIGINT (sen)
- **Keputusan:** Semua kolom amount = BIGINT. Rp 50.000 = 5000000.
- **Alasan:** Akurasi finansial; hindari FLOAT rounding.
- **Status:** FINAL.

## DEC-T03 — Status = VARCHAR (string enum)
- **Keputusan:** Semua kolom status = VARCHAR(50). Bukan integer/enum DB.
- **Alasan:** Readable (tidak perlu mapping); fleksibel untuk status baru (additive tanpa ALTER enum).
- **Status:** FINAL.

## DEC-T04 — `request_id` NULLABLE + immutable
- **Keputusan:** FK `request_id` di service_orders/sales_orders = NULLABLE. Setelah di-set, tidak boleh UPDATE.
- **Alasan:** Backward compatible untuk data legacy; origin trace permanen (ADR-001).
- **Status:** FINAL.

## DEC-T05 — `request_devices` pivot N:M
- **Keputusan:** Device↔Request = N:M via pivot. Bukan FK `device_id` di requests.
- **Status:** FINAL.

## DEC-T06 — Inventory = append-only movement
- **Keputusan:** `inventory_items` tidak punya kolom `qty`. Qty = VIEW/SUM dari `inventory_movements`.
- **Status:** FINAL.

## DEC-T07 — Attachments = polymorphic
- **Keputusan:** Satu tabel `attachments` dengan `attachable_type` + `attachable_id`.
- **Status:** FINAL.

## DEC-T08 — Audit = `audit_logs` append-only
- **Keputusan:** Semua tabel transaksional + master wajib diaudit via `audit_logs`. 17 tabel.
- **Status:** FINAL.

## DEC-T09 — History = mixed strategy
- **Keputusan:** Product price = snapshot; Policy = versioning; Request = append-only (`request_history`); Customer/Device = change log (`history_logs`).
- **Status:** FINAL.

## DEC-T10 — Soft delete = semua transaksional
- **Keputusan:** `deleted_at` di semua L3/L4/L5. Tidak ada hard delete transaksional.
- **Status:** FINAL.

## DEC-T11 — Tenant DB = BIGINT PK; Central DB = UUID untuk tenants
- **Keputusan:** `tenants.id` = UUID. Tabel lain di central DB = BIGINT.
- **Status:** FINAL.

## DEC-T12 — `user_role` pivot = TARGET
- **Keputusan:** Saat ini: kolom `role` di `users`. Target: pivot `user_role`. Desain tabel siap.
- **Status:** TARGET.

## DEC-T13 — Tidak ada tabel provider (marketplace/payment/AI)
- **Keputusan:** Provider = infrastructure (code), bukan entity. Hanya `provider_credentials`. Sprint 6.2B.
- **Status:** FINAL.

## DEC-T14 — Arsip = storage file, bukan tabel DB
- **Keputusan:** Data arsip = file compressed di storage provider. Tidak ada tabel `archived_*` di DB.
- **Status:** FINAL.

## DEC-T15 — Partisi = future
- **Keputusan:** Partisi untuk audit_logs, request_history, inventory_movements — blueprint, belum implementasi.
- **Status:** TARGET (P2).

---

## Ringkasan

| Status | Jumlah |
|---|---|
| FINAL | 13 |
| TARGET | 2 (user_role, partisi) |
