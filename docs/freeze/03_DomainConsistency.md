# 03 — Domain Consistency · 04 — Request Consistency · 05 — Data Consistency

> **Sprint 6.2E · Architecture Freeze Review v1.0.** Pemeriksaan konsistensi internal Domain, Request, dan Data. Dokumen gabungan.

---

## Part A — Domain Consistency (03)

### Aggregate Root → Table Mapping
| Aggregate Root (6.1) | Table (6.2D) | Child tables | Invariant preserved? |
|---|---|---|---|
| Tenant | `tenants` | `tenant_settings`, `module_activations` | ✅ 1 DB per tenant |
| Branch | `branches` | — | ✅ |
| User | `users` | `user_role` | ✅ Minimal 1 owner |
| Role | `roles` | `role_permission` | ✅ |
| Policy | `policies` | — | ✅ Versioning |
| Customer | `customers` | — | ✅ Soft delete |
| Device | `devices` | — | ✅ IMEI unique |
| Request | `requests` | `request_devices`, `request_history` | ✅ `request_id` immutable |
| ServiceOrder | `service_orders` | `work_orders`, `checklists` | ✅ 14 status |
| SalesOrder | `sales_orders` | `sale_items` | ✅ Stok keluar saat success |
| PurchaseOrder | `purchase_orders` | `purchase_items` | ✅ |
| CashShift | `cash_shifts` | `deposits` | ✅ 1 shift terbuka |
| InventoryItem | `inventory_items` | `inventory_movements` | ✅ No negative stock |
| Warranty | `warranties` | `warranty_claims` | ✅ Claim dalam periode |
| Compensation | `compensations` | — | ✅ Mengikuti policy |
| Subscription | `subscriptions` | `subscription_history` | ✅ |

**16/16 Aggregate Root → Table mapping konsisten. Tidak ada invariant yang dilanggar.**

### Domain Relationships → ERD FK
| Domain Relationship (6.1) | ERD FK (6.2C) | Table FK (6.2D) | Konsisten? |
|---|---|---|---|
| Customer 1:N Device | `devices.customer_id` | ✅ | ✅ |
| Device N:M Request | `request_devices` pivot | ✅ | ✅ |
| Request 1:N ServiceOrder | `service_orders.request_id` | ✅ | ✅ |
| ServiceOrder 1:N WorkOrder | `work_orders.service_order_id` | ✅ | ✅ |
| ServiceOrder 1:1 Warranty | `warranties.service_order_id` UNIQUE | ✅ | ✅ |
| Warranty 1:N Claim | `warranty_claims.warranty_id` | ✅ | ✅ |
| Branch 1:N InventoryItem | `inventory_items.branch_id` | ✅ | ✅ |
| InventoryItem 1:N Movement | `inventory_movements.inventory_item_id` | ✅ | ✅ |

---

## Part B — Request Consistency (04)

### ADR-001 Trace
| Dokumen | ADR-001 diterapkan? |
|---|---|
| `docs/request-engine/` (6.1D) | ✅ Definisi Request + lifecycle + channel |
| `docs/domain/CoreDomain.md` (6.1) | ✅ Request sebagai domain level atas |
| `docs/domain-validation/` (6.1A) | ✅ 20/20 BR dimulai dari Request |
| `docs/data-architecture/01_DataArchitecture.md` (6.2A) | ✅ Origin trace `request_id` Layer 4 |
| `docs/erd/06_RequestFlow.md` (6.2C) | ✅ Fork points + cascade |
| `docs/database/03_TransactionTables.md` (6.2D) | ✅ `requests` tabel + `request_id` FK |

### Request Lifecycle → Table
| Request status (6.1D) | Table status values | Konsisten? |
|---|---|---|
| draft, created, scheduled, waiting_pickup, picked_up, in_transit, received, assigned, processing, completed, delivered, closed, cancelled, archived | VARCHAR(50) — enum string | ✅ |

---

## Part C — Data Consistency (05)

### Data Classification vs ERD
| Klasifikasi (6.2A) | Contoh tabel | Aturan diterapkan? |
|---|---|---|
| L4 Sensitive | `provider_credentials` | ✅ Encrypted (AES-256) |
| L3 PII | `customers`, `devices` | ✅ Soft delete; audit akses |
| L2 Financial | `sales_orders`, `compensations` | ✅ Immutable setelah final; BIGINT |
| L1 Operational | `requests`, `service_orders` | ✅ Soft delete |
| L0 Public | — | — |

### Data Lifecycle vs Table
| Tabel | Retensi (6.2A) | Arsip (6.2D) | Konsisten? |
|---|---|---|---|
| `requests` | 7 tahun | 1 tahun → arsip | ✅ |
| `service_orders` | 7 tahun | 1 tahun → arsip | ✅ |
| `sales_orders` | 7 tahun | 3 tahun → arsip | ✅ |
| `audit_logs` | 7 tahun | 1 tahun → arsip | ✅ |
| `customers` | 7 tahun | Anonymize | ✅ |

### Data Standards vs Table
| Standar (6.2A) | Table (6.2D) | Konsisten? |
|---|---|---|
| Tabel = snake_case plural | ✅ Semua tabel | ✅ |
| PK = `id` | ✅ | ✅ |
| FK = `<entity>_id` | ✅ | ✅ |
| Amount = BIGINT (sen) | ✅ | ✅ |
| Status = VARCHAR | ✅ | ✅ |
| Timestamps = created/updated/deleted_at | ✅ | ✅ |

---

## Verdict

**Domain → Request → Data → ERD → Table — 100% konsisten.** Tidak ada broken chain. Tidak ada kontradiksi.
