# Workflow Test Matrix — Sprint 36E (RC1)

> Complete service workflow validation for all 14 statuses.

---

## 📊 Status Transition Matrix

| From ↓ / To → | menunggu | diterima | diagnosa | konfirmasi | internal | indent | onpartner | dikerjakan | selesai | siap | diambil | close | cancel | void |
|---------------|:--------:|:--------:|:--------:|:----------:|:--------:|:------:|:---------:|:----------:|:-------:|:----:|:-------:|:-----:|:------:|:----:|
| menunggu_alokasi | — | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |
| diterima | ✅ | — | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |
| diagnosa | ❌ | ❌ | — | ✅ | ❌ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |
| konfirmasi_pelanggan | ❌ | ❌ | ❌ | — | ❌ | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |
| konfirmasi_internal | ❌ | ❌ | ❌ | ❌ | — | ❌ | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |
| indent | ❌ | ❌ | ❌ | ❌ | ❌ | — | ❌ | ✅ | ❌ | ❌ | ❌ | ❌ | ✅ | ❌ |
| onpartner | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | — | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ |
| dikerjakan | ❌ | ❌ | ❌ | ✅ | ✅ | ✅ | ✅ | — | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ |
| selesai | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ | — | ✅ | ❌ | ✅ | ❌ | ❌ |
| siap_diambil | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | — | ✅ | ✅ | ❌ | ❌ |
| diambil | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | — | ✅ | ❌ | ❌ |
| close | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | — | ❌ | ❌ |
| cancel | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | — | ❌ |
| void | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | — |

✅ = Allowed transition | ❌ = Blocked transition

---

## 🚫 Invalid Transition Tests

| Test | From → To | Expected | Status |
|------|-----------|----------|--------|
| Close without payment | `selesai` → `close` | ❌ Blocked | ✅ |
| Ready without QC | `dikerjakan` → `siap_diambil` | ❌ Blocked | ✅ |
| Repair without diagnosis | `diterima` → `dikerjakan` | ✅ Allowed (simple repair) | ✅ |
| Diagnose without checklist | `diterima` → `diagnosa` | ⚠️ Warning (checklist recommended) | ✅ |
| Cancel from terminal | `close` → `cancel` | ❌ Blocked | ✅ |
| Reopen cancelled | `cancel` → any | ❌ Blocked | ✅ |

---

## 🔄 Automation Triggers

| Trigger | From → To | Automation | Status |
|---------|-----------|------------|--------|
| Service Created | — → `menunggu_alokasi` | Notify CS + Customer | ✅ |
| Technician Assigned | `menunggu_alokasi` → `diterima` | Notify Technician | ✅ |
| Diagnosis Complete | `diagnosa` → `konfirmasi` | Send Estimation | ✅ |
| Approved | `konfirmasi` → `dikerjakan` | Notify Technician | ✅ |
| Indent | `diagnosa` → `indent` | Notify Purchasing | ✅ |
| Repair Complete | `dikerjakan` → `selesai` | Trigger QC | ✅ |
| QC Passed | `selesai` → `siap_diambil` | Notify Customer | ✅ |
| Payment Done | `siap_diambil` → `diambil` | Generate Invoice | ✅ |
| Handover | `diambil` → `close` | Activate Warranty | ✅ |
| Cancelled | any → `cancel` | Notify Customer + CS | ✅ |

---

## 📋 Notification Tests

| Event | Channel | Recipient | Status |
|-------|---------|-----------|--------|
| Service Received | WA + Internal | Customer + CS | ✅ |
| Estimation Ready | WA | Customer | ✅ |
| Approval Needed | WA | Customer | ✅ |
| Parts Arrived | WA | Customer | ✅ |
| Ready Pickup | WA + Email | Customer | ✅ |
| Payment Received | WA + Email | Customer + Cashier | ✅ |
| Warranty Active | WA + Email | Customer | ✅ |
| Warranty Expiring | WA | Customer | ✅ |
| Feedback Request | WA | Customer | ✅ |

---

*Workflow Test Matrix — Sprint 36E*
