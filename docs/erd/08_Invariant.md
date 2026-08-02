# 08 — Invariant (Data Integrity)

> **Sprint 6.2C · Conceptual Blueprint.** Invariant yang tidak boleh dilanggar — di-enforce oleh constraint + validasi aplikasi.
> Dari Sprint 6.2A `17_DataIntegrity.md` + Sprint 6.1 `Aggregate.md`.

---

## 1. Invariant Wajib

| # | Invariant | Entity/Relasi | Mekanisme |
|---|---|---|---|
| I01 | Request tidak boleh tanpa Customer (kecuali walk-in guest) | `requests.customer_id NOT NULL` (kecuali `is_walk_in=true`) | Constraint + Validasi |
| I02 | Device harus berada dalam Tenant | `devices.tenant_id = current_tenant()` | Tenant scope query |
| I03 | Warranty harus memiliki ServiceOrder | `warranties.service_order_id NOT NULL` | FK constraint |
| I04 | ServiceOrder tidak boleh tanpa Request (kecuali legacy) | `service_orders.request_id` (nullable untuk legacy) | Validasi aplikasi (wajib untuk data baru) |
| I05 | Inventory tidak boleh negatif | `SUM(inventory_movements.qty) >= 0` per `inventory_item` | Check constraint / validasi sebelum insert movement |
| I06 | Compensation harus mempunyai Resolution | `compensations.resolution IS NOT NULL` sebelum `status='paid'` | Validasi |
| I07 | Attachment harus mempunyai Owner | `attachments.attachable_type + attachable_id NOT NULL` | FK polymorphic constraint |
| I08 | Provider Connection hanya milik Tenant | `provider_credentials.tenant_id NOT NULL` | FK + tenant scope |
| I09 | Tidak boleh dua shift terbuka di cabang yang sama | `COUNT(cash_shifts WHERE branch_id=X AND closed_at IS NULL) <= 1` | Unique partial index / validasi |
| I10 | `request_id` immutable setelah fork | `service_orders.request_id` — tidak boleh UPDATE setelah IS NOT NULL | Aplikasi — tolak update |
| I11 | Stok keluar hanya saat transaksi sukses | `inventory_movements` dengan `type='out'` hanya saat `sales_orders.status='success'` atau `service_orders` parts digunakan | Aplikasi |
| I12 | Void harus rollback stok & kas | `sales_orders.status='void'` → wajib ada `inventory_movements` reversal | Aplikasi |
| I13 | Claim hanya dalam periode policy | `warranty_claims.claim_date BETWEEN warranty.start_date AND warranty.end_date` | Validasi |
| I14 | Replacement wajib memengaruhi inventory | `replacements` → wajib menghasilkan `inventory_movements` (StockIn) | Aplikasi |
| I15 | Minimal satu user dengan role Owner aktif | `COUNT(users JOIN user_role JOIN roles WHERE role='owner' AND users.deleted_at IS NULL) >= 1` | Validasi sebelum suspend/delete owner |
| I16 | Email unik per tenant | `users.email UNIQUE(tenant_id, email)` | Unique constraint |
| I17 | IMEI/serial unik per tenant | `devices.imei UNIQUE(tenant_id, imei)` (jika diisi) | Unique constraint |
| I18 | Customer telepon unik per tenant (deteksi, bukan tolak) | — | Validasi — peringatan, bukan tolak |
| I19 | `request_id` harus valid (ada di `requests`) | FK `service_orders.request_id → requests.id` | FK constraint (jika NOT NULL) |
| I20 | Tidak boleh hard delete transaksional | Semua tabel L3/L4/L5: `deleted_at` soft delete | Aplikasi — tidak ada `DELETE` query |

---

## 2. Invariant Bisnis (Target)

| # | Invariant | Detail |
|---|---|---|
| I21 | Policy versioning — versi lama tetap berlaku untuk data historis | `policies.valid_from`/`valid_to`; kompensasi menyimpan `policy_version` |
| I22 | Kompensasi mengikuti policy | `compensations.policy_id` wajib; nilai dihitung dari policy rules |
| I23 | Delegation memiliki masa berlaku & audit | `delegations.expires_at` + `audit_logs` (target) |

---

## 3. Verifikasi

Konsisten dengan `docs/data-architecture/17_DataIntegrity.md` (Sprint 6.2A), `docs/domain/Aggregate.md` (Sprint 6.1).
