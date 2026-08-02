# 06 — Request Validation

> **Sprint 6.1D · Architecture Freeze · Blueprint Only.**
> Validasi Request terhadap **20 Business Reality** + **checklist operasional** + **prinsip**.
> Tujuan: membuktikan Request sebagai Core Entry Point mampu menangani seluruh kebutuhan bisnis.

---

## 1. Validasi 20 Business Reality (BR-001..020)

| BR | Kasus | Request menangani? | Mekanisme |
|---|---|---|---|
| 001 | Multi Branch Pickup | ✅ | type=pickup, pickup_branch ≠ service_branch; PickupTask→DeviceTransfer→DeliveryTask |
| 002 | Talangan Sparepart | ✅ | Request→ServiceOrder→PartCostBearing (customer/supplier/toko); Finance reconciliation |
| 003 | Owner Family | ✅ | Multi-user owner; siapa pun bisa buat/kelola Request |
| 004 | Manager Multi Function | ✅ | Request di-assign ke Manager; permission union |
| 005 | Cluster Branch Stock | ✅ | Tidak terpengaruh — Request tidak bergantung scope stok; Inventory tetap per branch/cluster |
| 006 | Tech Specialization | ✅ | Request→assign→ServiceOrder; matching Skill dengan Device type |
| 007 | Financial Ownership | ✅ | Request→ServiceOrder/SalesOrder; permission (void/refund/confirm) tidak berubah |
| 008 | Hybrid Store | ✅ | type=walk_in bisa fork ke ServiceOrder ATAU SalesOrder ATAU keduanya; tidak terikat business type |
| 009 | External Technician | ✅ | Request→assign→ServiceOrder→ServicePartner(capability=technician) |
| 010 | Service Partner | ✅ | Request→ServiceOrder→onpartner; tidak berubah |
| 011 | No SPOF (Take Over/Override/Delegation) | ✅ | Request reassign + Delegation (temporary grant) + Override (owner/admin force) + audit |
| 012 | Warranty Resolution | ✅ | type=warranty_claim→WarrantyClaim→ResolutionType(re-service/replacement/refund/reject) |
| 013 | Supplier Warranty | ✅ | Claim→SupplierClaim→Replacement→Inventory; Request hanya titik masuk claim |
| 014 | Lifetime Cost | ✅ | Request adalah titik awal cost chain; `request_id` di ServiceOrder→PartUsage→Finance memungkinkan rollup |
| 015 | Human Error Policy | ✅ | Request cancel + reversal (melalui Workflow Engine) + CorrectionRecord + policy human_error |
| 016 | Compensation Policy | ✅ | Request→ServiceOrder→Compensation mengikuti Policy; tidak berubah |
| 017 | Part Upgrade | ✅ | Request→ServiceOrder→part upgrade option + policy surcharge |
| 018 | Progressive Work Order | ✅ | Request→ServiceOrder→WorkOrder 0..n progresif |
| 019 | Multi Device Visit | ✅ | Request 1→Device N→ServiceOrder N (parallel fork) |
| 020 | Walk In Retail | ✅ | type=walk_in, fork ke SalesOrder (POS); customer opsional |

**Hasil: 20/20 ✅ — seluruh Business Reality dapat dimulai dari Request.**

---

## 2. Validasi Checklist Operasional

| Item | Ditangani Request? | Mekanisme |
|---|---|---|
| Walk In | ✅ | type=walk_in, source=customer/cs, channel=store |
| Pickup | ✅ | type=pickup, PickupTask + DeliveryTask |
| Home Service | ✅ | type=home_service, teknisi ke lokasi |
| Multi Device | ✅ | 1 Request→N Device→N ServiceOrder |
| Corporate | ✅ | type=corporate, batch N device |
| Marketplace | ✅ | type=marketplace, source=marketplace |
| API | ✅ | type=api, source=api_client |
| WhatsApp | ✅ | type=whatsapp, source=customer/whatsapp_bot |
| Booking | ✅ | type=booking, scheduled_at wajib |
| Garansi | ✅ | type=warranty_claim |
| Upgrade Sparepart | ✅ | Request→ServiceOrder→grade/variant option |
| Refund | ✅ | Request cancel→SalesOrder refund/void (reversal via Workflow) |
| Multi Branch | ✅ | pickup_branch ≠ service_branch; PickupTask+DeliveryTask |
| External Partner | ✅ | Request→ServiceOrder→ServicePartner(ext tech) |
| Delegation | ✅ | Request reassign/override/delegation |
| Progressive Complexity | ✅ | Channel menambah status lifecycle secara bertahap; tidak dipaksa |
| Grow Without Migration | ✅ | Channel/type baru = data registry, bukan tabel baru |

**Hasil: 17/17 ✅.**

---

## 3. Validasi Prinsip

| Prinsip | Status | Bukti |
|---|---|---|
| Configuration over Code | ✅ | Type, source, channel, lifecycle = data; perilaku = policy |
| Simple by Default | ✅ | Walk-in = 5 status minimal, tanpa PickupTask/DeliveryTask |
| Progressive Complexity | ✅ | Pickup/Corporate/Marketplace menambah status hanya bila dibutuhkan |
| Business Driven | ✅ | Mencerminkan realita: semua masuk sebagai "permintaan" |
| No Single Point Of Failure | ✅ | Multi-creator + Delegation + Override |
| Tenant Data Isolation | ✅ | Request scope tenant |
| Policy over Hardcode | ✅ | Validasi channel, SLA, approval = policy |
| Module over Business Type | ✅ | Request tidak terikat business type; hybrid = kombinasi type |
| Permission over Role | ✅ | `request.create/assign/cancel` = permission |
| Data is Sacred | ✅ | RequestHistory append-only; origin trace immutable |
| Grow Without Migration | ✅ | Channel baru = value baru, bukan migrasi skema |

**Hasil: 11/11 ✅.**

---

## 4. Perbandingan: Sebelum vs Sesudah ADR-001

| Aspek | Sebelum (Sprint 6.1) | Sesudah (Sprint 6.1D) |
|---|---|---|
| Entry point | CustomerVisit (walk-in saja) + langsung ServiceOrder (lainnya) | **Request** — satu funnel semua channel |
| Pickup / Home Service | Tidak ada wadah (gap) | type=pickup, type=home_service |
| Corporate batch | Tidak ada wadah | type=corporate, batch device |
| Marketplace | Tidak ada wadah | type=marketplace |
| API | Tidak ada wadah | type=api |
| WhatsApp | Langsung ServiceOrder | type=whatsapp→Request→ServiceOrder |
| Multi-device visit | Kardinalitas Visit→ServiceOrder 0..n (ADJ-01) | Request 1→Device N→ServiceOrder N |
| Origin trace | tersebar (service, sales, warranty sendiri) | `request_id` di semua domain turunan |
| Channel baru | Butuh entitas baru | Tambah value di registry |

---

## 5. Tidak Ada Regresi

Request **tidak mengubah**:
- 14 status ServiceOrder
- 5 status Payment
- 4 status Subscription
- 9 role tenant
- 5 business type
- Business Reality Chain invariant
- Tenant isolation (1 DB per tenant)
- Inventory, Finance, Compensation, Policy models
- Guard "minimal satu owner"
- Data Is Sacred (tidak hapus fisik)

---

## 6. Verifikasi Akhir

| Metrik | Nilai |
|---|---|
| BR ditangani | 20/20 |
| Checklist ditangani | 17/17 |
| Prinsip dipenuhi | 11/11 |
| Regresi | 0 |
