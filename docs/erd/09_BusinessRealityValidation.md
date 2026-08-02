# 09 — Business Reality Validation

> **Sprint 6.2C · Conceptual Blueprint.** Uji ERD terhadap seluruh Business Reality. ERD harus lolos semua.

---

## 1. Uji Business Reality

| # | Business Reality | ERD mendukung? | Entity yang terlibat | Mekanisme |
|---|---|---|---|---|
| 1 | **Walk In** | ✅ | requests (type=walk_in) → service_orders / sales_orders | Request 1 device → 1 ServiceOrder. `request_devices` pivot = 1 baris. |
| 2 | **Pickup** | ✅ | requests (type=pickup, pickup_branch_id) → request_devices → service_orders | `pickup_branch_id` berbeda dari `branch_id`. PickupTask/DeliveryTask = future entity. |
| 3 | **Home Service** | ✅ | requests (type=home_service) → service_orders | Request dengan `scheduled_at` + teknisi datang ke alamat. |
| 4 | **Courier** | ✅ | requests (type=courier) → request_devices → service_orders | Seperti Pickup + `in_transit` status. |
| 5 | **Multi Device** (BR-019) | ✅ | requests → request_devices (N baris) → N service_orders | Pivot `request_devices` memungkinkan 1 Request → N Device → N ServiceOrder. |
| 6 | **1 Device** | ✅ | requests → request_devices (1 baris) → 1 service_order | Toko kecil: 1 Request → 1 Device → 1 ServiceOrder. UI menyederhanakan. |
| 7 | **External Technician** (BR-009) | ✅ | service_partners (capability=technician) + service_orders | `technician_assignments` bisa assign ke partner. |
| 8 | **Multi Cabang** (BR-001) | ✅ | branches + inventory_items (per branch) + requests (pickup_branch_id) | Stok & kas per cabang; pickup lintas cabang. |
| 9 | **Repeat Repair** | ✅ | devices.id → request_devices → requests → service_orders | Satu device → banyak Request → banyak ServiceOrder sepanjang hidupnya. |
| 10 | **Warranty** (BR-012) | ✅ | warranties → warranty_claims | Service selesai → warranty. Claim dengan ResolutionType. |
| 11 | **Refund** | ✅ | sales_orders (status=refunded) + inventory_movements (reversal) | Sales refund → rollback stok & kas. |
| 12 | **Upgrade Sparepart** (BR-017) | ✅ | products (grade/variant) + service_orders part usage | Part upgrade option + policy surcharge. |
| 13 | **Corporate** | ✅ | requests (type=corporate) → request_devices (batch) → N service_orders | 1 Request → 20 device → 20 ServiceOrder. |
| 14 | **Marketplace** (P2) | ✅ | requests (type=marketplace, source=marketplace) → sales_orders | Interface siap; implementasi P2. |
| 15 | **WhatsApp** | ✅ | requests (type=whatsapp, source=whatsapp_bot) → service_orders | Sprint 6.2B §04. |
| 16 | **Google Drive** | ✅ | provider_credentials (type=storage, provider=google_drive) + attachments | Sprint 6.2B §03. |
| 17 | **Companion Mode** | ✅ | Tidak memerlukan entity khusus. Attachments & scanning via browser HP. | Sprint 6.2B §17. |
| 18 | **Progressive Complexity** | ✅ | Semua relasi 1:N dirancang untuk enterprise; UI toko kecil menyederhanakan tampilan. | Token kecil lihat 1:1; Enterprise lihat 1:N. |

---

## 2. ERD Quality Test (per Relationship)

| Relationship | Business Reality | Scalability | Performance | Future Expansion | Config over Code | Grow Without Migration |
|---|---|---|---|---|---|---|
| Customer 1:N Device | ✅ BR-019 | ✅ Indeks customer_id | ✅ | ✅ Device transfer | N/A | ✅ Additive |
| Device N:M Request | ✅ BR-019, repeat | ✅ Pivot + indeks | ✅ | ✅ IoT, corporate | N/A | ✅ Pivot additive |
| Request 1:N ServiceOrder | ✅ BR-019, corporate | ✅ Indeks request_id | ✅ | ✅ Batch processing | N/A | ✅ |
| ServiceOrder 1:N WorkOrder | ✅ BR-018 | ✅ Indeks service_order_id | ✅ | ✅ Multi-teknisi | N/A | ✅ Additive |
| ServiceOrder 1:N Attachment | ✅ Foto servis | ✅ Polymorphic indeks | ✅ | ✅ Voice note, video | Storage provider | ✅ |
| User N:M Role | ✅ BR-003, 004 | ✅ Pivot indeks | ✅ | ✅ Role kustom | Permission engine | ✅ Target pivot |
| InventoryItem 1:N Movement | ✅ Data Is Sacred | ✅ Indeks item_id+date | ✅ Aggregate view | ✅ | N/A | ✅ Append-only |

---

## 3. Hasil

**18/18 Business Reality LOLOS. 7/7 Relationship Quality Test LOLOS.**

---

## 4. Verifikasi

Konsisten dengan `docs/domain-validation/BusinessRealityValidation.md` (Sprint 6.1A), `docs/request-engine/06_RequestValidation.md` (Sprint 6.1D).
