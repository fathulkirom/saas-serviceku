# 01 — Table Catalog

> **Sprint 6.2D · Table Blueprint Only.** Master catalog seluruh tabel ServiceKU — 52 tabel dalam 5 layer + 15 kelompok fungsional.
> **Tidak ada SQL, tidak ada migration.**

---

## 1. Master Catalog (52 Tabel)

### L1 — Platform (Central DB — 6 tabel)
| # | Tabel | Kelompok | Aggregate Root? | Wajib? |
|---|---|---|---|---|
| P01 | `tenants` | Platform | ✅ | ✅ |
| P02 | `plans` | Platform | ✅ | ✅ |
| P03 | `plan_features` | Platform | ❌ (Child of Plan) | ✅ |
| P04 | `vouchers` | Platform | ✅ | ❌ |
| P05 | `platform_payments` | Platform | ❌ (Child of Tenant) | ❌ |
| P06 | `super_admins` | Platform | ✅ | ✅ |

### L2 — Tenant Configuration (Tenant DB — 10 tabel)
| # | Tabel | Kelompok | Aggregate Root? | Wajib? |
|---|---|---|---|---|
| C01 | `branches` | Configuration | ✅ | ✅ |
| C02 | `users` | Security | ✅ | ✅ |
| C03 | `roles` | Security | ✅ | ✅ |
| C04 | `permissions` | Security | ✅ | ✅ |
| C05 | `role_permission` | Security (Pivot) | ❌ | ✅ |
| C06 | `user_role` | Security (Pivot) | ❌ | ✅ (target) |
| C07 | `positions` | Configuration | ✅ | ❌ (target) |
| C08 | `policies` | Configuration | ✅ | ❌ (target) |
| C09 | `tenant_settings` | Configuration | ❌ (Child of Tenant) | ✅ |
| C10 | `provider_credentials` | Configuration | ❌ (Child of Tenant) | ❌ |

### L3 — Master Data (Tenant DB — 5 tabel)
| # | Tabel | Kelompok | Aggregate Root? | Wajib? |
|---|---|---|---|---|
| M01 | `customers` | Master | ✅ | ✅ |
| M02 | `devices` | Master | ✅ | ✅ |
| M03 | `suppliers` | Master | ✅ | ❌ |
| M04 | `service_partners` | Master | ✅ | ❌ |
| M05 | `products` | Master | ✅ | ❌ |

### L4 — Transactional (Tenant DB — 17 tabel)
| # | Tabel | Kelompok | Aggregate Root? | Wajib? |
|---|---|---|---|---|
| T01 | `requests` | Transaction | ✅ | ✅ |
| T02 | `request_devices` | Transaction (Pivot) | ❌ | ✅ |
| T03 | `request_history` | Transaction (Audit) | ❌ | ✅ |
| T04 | `service_orders` | Transaction / Workflow | ✅ | ✅ (setelah fork) |
| T05 | `work_orders` | Transaction / Workflow | ❌ (Child of Service) | ❌ (target) |
| T06 | `checklists` | Transaction / Workflow | ❌ (Child of Service) | ❌ |
| T07 | `technician_assignments` | Transaction / Workflow | ❌ (Child of Service) | ❌ |
| T08 | `sales_orders` | Transaction | ✅ | ❌ |
| T09 | `sale_items` | Transaction | ❌ (Child of Sales) | ✅ (jika sales) |
| T10 | `purchase_orders` | Transaction | ✅ | ❌ |
| T11 | `purchase_items` | Transaction | ❌ (Child of Purchase) | ✅ (jika purchase) |
| T12 | `cash_shifts` | Transaction | ✅ | ❌ |
| T13 | `deposits` | Transaction | ❌ (Child of CashShift) | ❌ |
| T14 | `expenses` | Transaction | ✅ | ❌ |
| T15 | `inventory_items` | Inventory | ✅ | ✅ |
| T16 | `inventory_movements` | Inventory | ❌ (Child of Item) | ✅ |
| T17 | `customer_visits` | Master (Legacy) | ✅ | ❌ (legacy) |

### L5 — Post-Sale, Aggregate, Log, Archive (Tenant DB — 14 tabel)
| # | Tabel | Kelompok | Aggregate Root? | Wajib? |
|---|---|---|---|---|
| A01 | `warranties` | Transaction | ✅ | ❌ |
| A02 | `warranty_claims` | Transaction | ❌ (Child of Warranty) | ❌ |
| A03 | `suplier_claims` | Transaction | ❌ (Child of Claim) | ❌ (target) |
| A04 | `replacements` | Transaction | ❌ (Child of SuplierClaim) | ❌ (target) |
| A05 | `compensations` | Finance | ✅ | ❌ (target) |
| A06 | `finance_transactions` | Finance | ✅ | ✅ |
| A07 | `attachments` | Attachment | ❌ (Polymorphic) | ❌ |
| A08 | `notifications` | Notification | ✅ | ❌ |
| A09 | `audit_logs` | Audit | N/A (Append-only) | ✅ |
| A10 | `history_logs` | History | N/A (Append-only) | ❌ |
| A11 | `dashboard_widgets` | Analytics | ❌ (Child of User) | ❌ |
| A12 | `report_snapshots` | Analytics | ✅ | ❌ |
| A13 | `subscriptions` | Platform (Billing) | ✅ | ✅ |
| A14 | `subscription_history` | Platform (Billing) | ❌ (Child of Subscription) | ✅ |

---

## 2. Klasifikasi per Kelompok Fungsional

| Kelompok | Jumlah | Tabel |
|---|---|---|
| **Platform** | 6 | tenants, plans, plan_features, vouchers, platform_payments, super_admins |
| **Security** | 4 + 2 pivot | users, roles, permissions, role_permission, user_role (+ positions) |
| **Configuration** | 5 | branches, policies, positions, tenant_settings, provider_credentials |
| **Master** | 5 + 1 legacy | customers, devices, suppliers, service_partners, products (+ customer_visits) |
| **Transaction** | 9 | requests, service_orders, sales_orders, purchase_orders, cash_shifts, expenses, warranties, warranty_claims, suplier_claims |
| **Finance** | 2 | compensations, finance_transactions |
| **Inventory** | 2 | inventory_items, inventory_movements |
| **Workflow** | 4 | work_orders, checklists, technician_assignments, sale_items, purchase_items |
| **Attachment** | 1 | attachments |
| **Notification** | 1 | notifications |
| **Audit** | 2 | audit_logs, history_logs, request_history |
| **Analytics** | 2 | dashboard_widgets, report_snapshots |
| **Billing** | 2 | subscriptions, subscription_history |
| **Pivot** | 3 | request_devices, role_permission, user_role |
| **Child/Detail** | 8 | sale_items, purchase_items, deposits, replacements, plan_features, request_history, work_orders, checklists |
| **Legacy** | 1 | customer_visits |

---

## 3. Aturan Umum (detail di dokumen 08–18)

| Aturan | Ketentuan |
|---|---|
| **PK** | `id` — BIGINT UNSIGNED auto-increment (tenant DB), UUID (central DB opsional) |
| **FK** | `<entity>_id` — BIGINT UNSIGNED, nullable untuk backward compatibility |
| **Amount** | `<name>_amount` — BIGINT (sen). Rp 50.000 = 5000000 |
| **Status** | `status` — VARCHAR(50), string enum (14 service, 5 payment, dsb.) |
| **Timestamps** | `created_at`, `updated_at`, `deleted_at` — TIMESTAMP |
| **Soft Delete** | `deleted_at` TIMESTAMP NULL — semua tabel L3/L4/L5 |
| **Audit** | `audit_logs` — append-only untuk semua perubahan transaksional |
| **History** | `history_logs` — versioning untuk policy/harga; `request_history` untuk request |

---

## 4. Verifikasi

Konsisten dengan `docs/erd/01_ERDConcept.md` (Sprint 6.2C), `docs/data-architecture/` (Sprint 6.2A), `docs/domain/` (Sprint 6.1). 52 tabel terpetakan lengkap.
