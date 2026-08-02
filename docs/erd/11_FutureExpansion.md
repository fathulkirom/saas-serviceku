# 11 — Future Expansion (ERD Readiness)

> **Sprint 6.2C · Conceptual Blueprint.** Kemampuan ERD menampung perluasan masa depan tanpa perubahan struktur.

---

## 1. Perluasan yang Sudah Siap

| Perluasan | ERD readiness | Mekanisme |
|---|---|---|
| **Multi-role** (user_role pivot) | ✅ | Pivot `user_role` sudah didesain (target). |
| **Delegation** (temporary grant) | ✅ | Tabel `delegations` (future) — additive. |
| **Gudang / StockCluster** (BR-005, P2) | ✅ | `inventory_items` siap scope branch ATAU cluster. Tambah `stock_clusters` + FK opsional. |
| **Marketplace** (Tokopedia, Shopee) | ✅ | `requests(type=marketplace)` + `sales_orders`. Tidak perlu tabel baru. |
| **AI auto-classify** | ✅ | `requests` menerima hasil klasifikasi AI; tidak perlu struktur baru. |
| **Customer Portal** | ✅ | Customer melihat `requests` + `service_orders` via proyeksi. |
| **Public API** | ✅ | `requests(type=api, source=api_client)`. |
| **Mobile App** | ✅ | Companion Mode; tidak perlu entity baru. |
| **IoT / Smart Device** | ✅ | `requests(type=api, source=system)`. |
| **Subscription Service** | ✅ | Auto-generate `requests` berkala. |
| **Double-entry accounting** | ✅ | `finance_transactions` bisa diperluas ke debit/credit. |
| **HR / Payroll** | ✅ | `positions` + `compensations` siap diperluas. |

---

## 2. Perluasan yang Butuh Entity Baru (Additive — Future ADR)

| Perluasan | Entity baru | Prioritas |
|---|---|---|
| **StockCluster / Gudang** | `stock_clusters`, `branch_cluster` pivot | P2 |
| **Delegation** | `delegations` (granter, grantee, permission, expires_at) | P1 |
| **CorrectionRecord** (Human Error) | `correction_records` (reversal audit) | P1 |
| **PickupTask / DeliveryTask** | `pickup_tasks`, `delivery_tasks` | P1 |
| **Queue System** | `queues`, `queue_entries` | P2 |
| **Contract / Subscription Service** | `contracts`, `contract_items` | P2 |
| **E-Sign** | `signatures` (polymorphic) | P2 |
| **Blockchain verification** | `blockchain_records` | P3 |
| **Franchise / Tenant hierarchy** | `tenant_parent` (self-referencing) | P3 |

---

## 3. Aturan

1. **Semua perluasan = additive** — tabel baru, kolom baru (nullable), pivot baru. Tidak mengubah struktur existing.
2. **Entity baru harus mengikuti konvensi** (`18_DataStandards.md`).
3. **FK baru selalu nullable** — backward compatible dengan data existing.
4. **Setiap entity baru = ADR baru** — tidak bisa ditambahkan tanpa persetujuan.

---

## 4. Verifikasi

Konsisten dengan `docs/domain/FutureExpansion.md` (Sprint 6.1), `docs/integration/18_FutureProvider.md` (Sprint 6.2B), `docs/architecture-engine/FutureRoadmap.md` (Sprint 5.2).
