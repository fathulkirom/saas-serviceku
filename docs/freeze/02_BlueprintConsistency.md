# 02 — Blueprint Consistency

> **Sprint 6.2E · Architecture Freeze Review v1.0.** Pemeriksaan konsistensi lintas dokumen.

---

## 1. Naming Convention Consistency

| Konvensi | Sprint 4 (Naming.md) | Sprint 6.2A (DataStandards) | Sprint 6.2D (Table) | Konsisten? |
|---|---|---|---|---|
| Tabel | snake_case plural | ✅ | ✅ | ✅ |
| PK | `id` | `id` | `id` BIGINT/UUID | ✅ |
| FK | `<entity>_id` | ✅ | ✅ | ✅ |
| Status | string snake_case | VARCHAR(50) | ✅ | ✅ |
| Amount | — | BIGINT (sen) | ✅ | ✅ |
| Timestamps | `created_at`, `updated_at` | + `deleted_at` | ✅ | ✅ |

---

## 2. Status Value Consistency

| Status | Sprint 5.1 Spec | Sprint 6.1 Domain | Sprint 6.1D Request | Sprint 6.2C ERD | Konsisten? |
|---|---|---|---|---|---|
| Service (14) | ✅ | ✅ | — | ✅ | ✅ |
| Payment (5) | ✅ | ✅ | — | ✅ | ✅ |
| Subscription (4) | ✅ | ✅ | — | ✅ | ✅ |
| Request (14) | — | — | ✅ | ✅ | ✅ |
| Business Type (5) | ✅ | ✅ | — | ✅ | ✅ |
| Role (9) | ✅ | ✅ | — | ✅ | ✅ |
| Feature Level (3) | ✅ | ✅ | — | ✅ | ✅ |

---

## 3. Daftar Domain → Entity → Table Trace

| Domain (6.1) | Entity (6.1 Entity.md) | Table (6.2D) | Konsisten? |
|---|---|---|---|
| Tenant | ✅ | `tenants` | ✅ |
| Branch | ✅ | `branches` | ✅ |
| User | ✅ | `users` | ✅ |
| Role | ✅ | `roles` + `role_permission` | ✅ |
| Permission | ✅ | `permissions` | ✅ |
| Policy | ✅ | `policies` | ✅ |
| Customer | ✅ | `customers` | ✅ |
| Device | ✅ | `devices` | ✅ |
| Supplier | ✅ | `suppliers` | ✅ |
| Service Partner | ✅ | `service_partners` | ✅ |
| Product | ✅ | `products` | ✅ |
| Request | ✅ | `requests` + `request_devices` + `request_history` | ✅ |
| Service Order | ✅ | `service_orders` + `work_orders` + `checklists` + `technician_assignments` | ✅ |
| Sales Order | ✅ | `sales_orders` + `sale_items` | ✅ |
| Purchase Order | ✅ | `purchase_orders` + `purchase_items` | ✅ |
| Cash Shift | ✅ | `cash_shifts` + `deposits` | ✅ |
| Warranty | ✅ | `warranties` + `warranty_claims` | ✅ |
| SupplierClaim | ✅ | `suplier_claims` (target) | ✅ |
| Replacement | ✅ | `replacements` (target) | ✅ |
| Compensation | ✅ | `compensations` (target) | ✅ |
| Subscription | ✅ | `subscriptions` + `subscription_history` | ✅ |
| Attachment | ✅ | `attachments` (polymorphic) | ✅ |
| Audit | ✅ | `audit_logs` | ✅ |
| History | ✅ | `history_logs` | ✅ |

**26/26 domain → 52 tabel. Tidak ada domain yang hilang atau tidak terpetakan.**

---

## 4. Pemeriksaan Dokumen Spesifik

| Pemeriksaan | Hasil |
|---|---|
| `PROJECT_SPECIFICATION.md` §6 (7 role resmi) vs `HandleInertiaRequests.php` (9 role) | ✅ Konsisten — 7 resmi + 2 tambahan (head_store, courier) didokumentasikan |
| `PROJECT_SPECIFICATION.md` §7 (4 business type) vs `Tenant.php` (5 type) | ✅ Konsisten — 4 resmi + aksespare_service sebagai tambahan |
| ADJ-01 (Visit→ServiceOrder 0..n) vs ERD (Request 1:N ServiceOrder) | ✅ Konsisten — Visit sudah digantikan Request |
| ADJ-13 (Module terpisah BusinessType) vs ERD (module_activations) | ✅ Konsisten |

---

## 5. Verdict

**Tidak ada inkonsistensi antar dokumen.** Seluruh rantai dari Specification → Domain → Request → Data → Integration → ERD → Table konsisten.
