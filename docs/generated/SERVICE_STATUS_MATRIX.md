# Service Status Matrix — Sprint 36A

> Complete 14-status lifecycle with ALL allowed transitions documented.

---

## 📊 Status Matrix

| # | Status Key | Label | Phase | Icon | Terminal? |
|---|-----------|-------|-------|------|-----------|
| 1 | `menunggu_alokasi` | Pending | Intake | 📥 | No |
| 2 | `diterima` | Diterima | Intake | ✅ | No |
| 3 | `diagnosa` | Diagnosa | Diagnosis | 🔍 | No |
| 4 | `menunggu_konfirmasi_pelanggan` | Konfirmasi Customer | Approval | 📞 | No |
| 5 | `menunggu_konfirmasi_internal` | Konfirmasi Internal | Approval | 🏢 | No |
| 6 | `indent` | Indent Part | Parts | 📦 | No |
| 7 | `onpartner` | Di Partner | External | 🤝 | No |
| 8 | `dikerjakan` | Dikerjakan | Repair | 🔧 | No |
| 9 | `selesai` | QC / Selesai | QC | 🔍 | No |
| 10 | `siap_diambil` | Siap Diambil | Pickup | 📦 | No |
| 11 | `diambil` | Diambil | Pickup | 🤝 | No |
| 12 | `close` | Closed | Closed | 🔒 | ✅ |
| 13 | `cancel` | Cancel | Terminal | ❌ | ✅ |
| 14 | `void` | Void | Terminal | 🚫 | ✅ |

---

## 🔄 Allowed Transitions

| From | → To |
|------|------|
| `menunggu_alokasi` | `diterima`, `cancel` |
| `diterima` | `diagnosa`, `dikerjakan`, `menunggu_alokasi`, `cancel` |
| `diagnosa` | `menunggu_konfirmasi_pelanggan`, `dikerjakan`, `indent`, `cancel` |
| `dikerjakan` | `menunggu_konfirmasi_pelanggan`, `menunggu_konfirmasi_internal`, `indent`, `onpartner`, `selesai`, `cancel` |
| `menunggu_konfirmasi_pelanggan` | `dikerjakan`, `cancel` |
| `menunggu_konfirmasi_internal` | `dikerjakan`, `cancel` |
| `indent` | `dikerjakan`, `cancel` |
| `onpartner` | `dikerjakan`, `selesai`, `cancel` |
| `selesai` | `siap_diambil`, `dikerjakan` (QC fail), `close` |
| `siap_diambil` | `close`, `diambil` |
| `diambil` | `close` |
| `close` | (terminal) |
| `cancel` | (terminal) |
| `void` | (terminal) |

---

## 🚫 Invalid Transitions (Now Blocked)

| Attempt | Blocked By |
|---------|-----------|
| `close` before payment | `ServiceWorkflowValidator` — `REQUIRE_PAYMENT_BEFORE_CLOSE` |
| `siap_diambil` before QC | `ServiceWorkflowValidator` — `REQUIRE_QC_BEFORE_READY` |
| `dikerjakan` without diagnosis | `ServiceWorkflowValidator` — `REQUIRE_DIAGNOSIS_BEFORE_REPAIR` |
| `diagnosa` without checklist | `ServiceWorkflowValidator` — `REQUIRE_CHECKLIST_BEFORE_DIAGNOSIS` |
| `diagnosa` without intake photo | `ServiceWorkflowValidator` — `REQUIRE_INTAKE_PHOTO_BEFORE_DIAGNOSIS` |
| Any non-matrix transition | `ServiceWorkflowValidator` — `ALLOWED_TRANSITIONS` |

---

*Service Status Matrix — Sprint 36A*
