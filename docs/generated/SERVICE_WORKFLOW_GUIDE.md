# Service Workflow Guide — ServiceKU v1.0

> Complete workflow validation, automation triggers, and notification mapping.

---

## 🔄 Production Workflow (9 Status)

```
MASUK (CS creates service)
  │  auto: tracking_code generated
  │  auto: customer_notified (WA: "Servis diterima")
  ▼
ASSIGNED / DITERIMA (CS assigns technician)
  │  auto: technician_notified (Internal: "Servis baru")
  │  require: checklist completed, 2+ intake photos
  ▼
DIAGNOSA (Technician diagnoses)
  │  auto: quotation generated
  │  require: diagnosis saved with estimated cost + time
  ▼
WAITING APPROVAL (Customer approves/rejects)
  │  auto: customer_notified (WA: "Estimasi siap")
  │  customer action: approve / reject / revise
  ▼
DIKERJAKAN (Technician repairs)
  │  auto: timer starts
  │  require: approval received
  ▼
QC / SELESAI (QC check)
  │  require: 22-point QC checklist completed
  │  if PASS → siap_diambil
  │  if FAIL → back to dikerjakan
  ▼
READY PICKUP / SIAP DIAMBIL
  │  auto: customer_notified (WA: "Servis siap diambil")
  │  auto: invoice generated
  ▼
PAID / DIAMBIL (Kasir processes payment)
  │  require: payment received, signature captured, handover photo
  ▼
CLOSED
  │  auto: warranty activated (30d jasa, 90d sparepart)
  │  auto: feedback survey scheduled (7 days)
```

---

## 🚫 Blocked Transitions

| Attempt | Blocked By |
|---------|-----------|
| Close without payment | `ServiceWorkflowValidator` |
| Ready without QC | `ServiceWorkflowValidator` |
| Repair without diagnosis | `ServiceWorkflowValidator` |
| Diagnosis without checklist | Warning (soft block) |
| Cancel from terminal status | `ALLOWED_TRANSITIONS` matrix |

---

## 🔔 Notification Mapping

| Event | Channel | Recipient | Timing |
|-------|---------|-----------|--------|
| Service Created | WA | Customer | Instant |
| Technician Assigned | Internal | Technician | Instant |
| Diagnosis Complete | WA | Customer | Instant |
| Approval Needed | WA | Customer | Instant |
| Parts Arrived | WA | Customer | Instant |
| Repair Complete | Internal | QC | Instant |
| Ready Pickup | WA + Email | Customer | Instant |
| Payment Received | WA + Email | Customer | Instant |
| Warranty Active | WA + Email | Customer | Instant |
| Warranty Expiring | WA | Customer | 3 days before |
| Feedback Request | WA | Customer | 7 days after |

---

*Service Workflow Guide — ServiceKU v1.0*
