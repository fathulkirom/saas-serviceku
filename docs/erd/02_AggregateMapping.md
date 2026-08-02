# 02 — Aggregate Mapping

> **Sprint 6.2C · Conceptual Blueprint.** Pemetaan Aggregate Root ke tabel. Setiap Aggregate Root adalah pintu masuk untuk modifikasi data; child tabel hanya dimodifikasi melalui root.
> Dari Sprint 6.1 `docs/domain/Aggregate.md`.

---

## 1. Pemetaan Aggregate → Tabel

| Aggregate Root | Tabel root | Child tables | Invariant (dari Sprint 6.1) |
|---|---|---|---|
| **Tenant** | `tenants` | `tenant_settings`, `module_activations`, `provider_credentials` | 1 DB per tenant; business type resmi |
| **Plan** | `plans` | `plan_features` | Plan mendefinisikan batas & fitur |
| **Branch** | `branches` | — | Stok & kas milik cabang |
| **User** | `users` | `user_role` (pivot), `positions` (pivot) | Minimal 1 role; email unik per tenant |
| **Role** | `roles` | `role_permission` (pivot) | Role resmi 7 tidak bisa dihapus |
| **Permission** | `permissions` | — | Registry platform; tenant membaca |
| **Policy** | `policies` | — | Versioning; versi lama tetap berlaku |
| **Customer** | `customers` | — | Telepon unik per tenant (deteksi duplikat) |
| **Device** | `devices` | — | IMEI/serial unik per tenant; tidak hapus berriwayat |
| **Supplier** | `suppliers` | — | — |
| **ServicePartner** | `service_partners` | — | — |
| **Product** | `products` | — | — |
| **Request** | `requests` | `request_history`, `attachments` (polymorphic) | `request_id` immutable di turunan |
| **ServiceOrder** | `service_orders` | `work_orders`, `checklists`, `technician_assignments`, `attachments` | Transisi status valid; void owner/admin |
| **WorkOrder** | `work_orders` | — | Tidak ada WO tanpa induk ServiceOrder |
| **SalesOrder** | `sales_orders` | `sale_items` | Stok keluar hanya saat success |
| **PurchaseOrder** | `purchase_orders` | `purchase_items` | Terima tanpa PO dilarang |
| **CashShift** | `cash_shifts` | `deposits` | Tidak boleh 2 shift terbuka di branch sama |
| **Expense** | `expenses` | — | — |
| **InventoryItem** | `inventory_items` | `inventory_movements` | Qty = sum(movements); tidak negatif |
| **Warranty** | `warranties` | `warranty_claims` | Hanya dari Service selesai; klaim dalam periode policy |
| **Claim** | `warranty_claims` | `suplier_claims`, `replacements` | Resolution wajib sebelum resolved |
| **Compensation** | `compensations` | — | Wajib mengikuti policy |
| **Subscription** | `subscriptions` | `subscription_history` | Status trial/active/expired/suspended |
| **Dashboard** | `dashboard_widgets` | — | — |
| **Report** | `report_snapshots` | — | — |
| **Notification** | `notifications` | — | — |
| **Audit** | `audit_logs` | — | Append-only, immutable |
| **History** | `history_logs` | — | Append-only |

---

## 2. Aturan Modifikasi (dari Sprint 6.1 §4)

| Aggregate | Siapa boleh memodifikasi lewat root |
|---|---|
| Tenant | Super Admin (platform); Owner (pengaturan tenant) |
| Branch | Owner; Admin/Manager (ops. cabang) |
| User | Owner (`manage_users`); User (profil sendiri) |
| Role | Owner (target); Platform (seed) |
| Customer | Owner/Admin/Manager/CS (`manage_customers`) |
| Device | CS/Admin/Manager/Owner |
| Request | CS/Admin/Owner/System/API (buat); CS/Admin/Owner (assign/cancel) |
| ServiceOrder | CS/Admin/Manager/Owner/Teknisi (`work_on_services`); void: Owner/Admin |
| SalesOrder | Kasir/Owner/Admin/Manager; void: Owner/Admin |
| PurchaseOrder | Owner/Admin/Manager |
| CashShift | Kasir/Owner/Admin/Manager; konfirmasi setoran: Owner/Admin |
| Warranty/Claim | Owner/Admin/Manager/CS |
| Compensation | Owner (`manage_finance`) |
| Policy | Owner |
| Subscription | Owner (bayar); Super Admin (override) |

---

## 3. Verifikasi

Konsisten dengan `docs/domain/Aggregate.md` (Sprint 6.1), `docs/domain/Ownership.md` (Sprint 6.1), `docs/data-architecture/02_DataOwnership.md` (Sprint 6.2A).
