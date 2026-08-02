# 06 — ERD Consistency · 07 — Table Consistency · 08 — Provider Consistency

> **Sprint 6.2E · Architecture Freeze Review v1.0.** Dokumen gabungan.

---

## Part A — ERD Consistency (06)

### Entity Count Trace
| Layer | ERD (6.2C) | Table (6.2D) | Match? |
|---|---|---|---|
| L1 Platform | 6 | 6 | ✅ |
| L2 Config | 10 | 10 | ✅ |
| L3 Master | 5 + 1 legacy | 6 | ✅ |
| L4 Transactional | 17 | 17 | ✅ |
| L5 Post-Sale + Aggregate | 14 | 14 | ✅ |
| **Total** | **52** | **52** | ✅ |

### Cardinality Verification
| ERD Cardinality | Table FK | Type | Konsisten? |
|---|---|---|---|
| Tenant 1:N Branch | `branches.tenant_id` | FK composite | ✅ |
| Customer 1:N Device | `devices.customer_id` | FK | ✅ |
| Device N:M Request | `request_devices` | Pivot | ✅ |
| Request 1:N ServiceOrder | `service_orders.request_id` | FK (nullable) | ✅ |
| ServiceOrder 1:1 Warranty | `warranties.service_order_id` UNIQUE | FK unique | ✅ |
| User N:M Role | `user_role` | Pivot (target) | ✅ |

---

## Part B — Table Consistency (07)

### FK Integrity
| FK | Parent exists? | ON DELETE | Konsisten? |
|---|---|---|---|
| `service_orders.request_id` → `requests.id` | ✅ | SET NULL | ✅ |
| `sales_orders.request_id` → `requests.id` | ✅ | SET NULL | ✅ |
| `request_devices.request_id` → `requests.id` | ✅ | CASCADE | ✅ |
| `request_devices.device_id` → `devices.id` | ✅ | RESTRICT | ✅ |
| `work_orders.service_order_id` → `service_orders.id` | ✅ | CASCADE | ✅ |
| `warranties.service_order_id` → `service_orders.id` | ✅ | RESTRICT | ✅ |
| `inventory_movements.inventory_item_id` → `inventory_items.id` | ✅ | RESTRICT | ✅ |

### Constraint Completeness
| Tipe Constraint | Jumlah (6.2D) | Cakupan |
|---|---|---|
| UNIQUE | 21 | ✅ Mencegah duplikasi |
| NOT NULL (wajib) | Sesuai spesifikasi | ✅ Kolom kritis |
| CHECK | 5 | ✅ Validasi dasar |

---

## Part C — Provider Consistency (08)

### Provider Pattern → Table
| Provider Type (6.2B) | Entity di ERD? | Table? | Konsisten? |
|---|---|---|---|
| Storage (9 provider) | ❌ (infrastructure) | `provider_credentials` | ✅ |
| Messaging (5 provider) | ❌ | `provider_credentials` | ✅ |
| Payment (6 provider) | ❌ | `provider_credentials` | ✅ |
| Printing (5 provider) | ❌ | `provider_credentials` | ✅ |
| Scanning (5 provider) | ❌ | `provider_credentials` | ✅ |
| AI (5 provider) | ❌ | `provider_credentials` | ✅ |
| Location (2 provider) | ❌ | `provider_credentials` | ✅ |
| Notification (5 provider) | ❌ | `provider_credentials` | ✅ |
| Backup (4 provider) | ❌ | `provider_credentials` | ✅ |
| Marketplace (4 provider) | ❌ | `provider_credentials` + `requests(type=marketplace)` | ✅ |

### Vendor Independence
| Provider | Hardcode di domain? | Swap tanpa kode? | Verdict |
|---|---|---|---|
| Storage | ❌ (StorageInterface) | ✅ Ganti S3→R2 via Settings | ✅ |
| Messaging | ❌ (MessagingInterface) | ✅ Ganti WA Web→Cloud API via Settings | ✅ |
| Payment | ❌ (PaymentInterface) | ✅ Ganti Midtrans→Xendit via Settings | ✅ |
| AI | ❌ (AIInterface) | ✅ Ganti OpenAI→DeepSeek via Settings | ✅ |

**Tidak ada tabel vendor di ERD.** Provider = infrastructure (code). `provider_credentials` cukup.

---

## Verdict

ERD ↔ Table: 52/52 konsisten. FK: 31 konsisten. Provider: 10 tipe, semua vendor-independent. ✅
