# Service Notification Flow — Sprint 36A

> All service event notifications routed through Notification Center.

---

## 🔔 Notification Events

| Event | Trigger | Channel | Recipient |
|-------|---------|---------|-----------|
| Service Created | New service intake | WA + Internal | Customer + CS |
| Status Changed | Any status transition | Internal | CS, Technician, Manager |
| Estimation Ready | Quotation generated | WA + Internal | Customer |
| Customer Approval Needed | Awaiting confirmation | WA | Customer |
| Technician Assigned | Assignment | Internal | Technician |
| Parts Needed (Indent) | Indent status | Internal | Purchasing, Manager |
| Repair Started | dikerjakan status | Internal | CS |
| QC Passed | QC complete | Internal | CS, Manager |
| Ready for Pickup | siap_diambil status | WA + Internal | Customer + CS |
| Payment Received | Payment success | WA + Internal | Customer + Cashier |
| Warranty Expiring (3 days) | Schedule check | WA + Internal | Customer |
| Warranty Claim Created | Claim filed | Internal | Technician, Manager |
| Service Overdue | SLA breach | Internal | Manager, CS |
| Canceled | Service canceled | WA + Internal | Customer + CS |

---

## 📱 Channel Details

| Channel | Template | Fallback |
|---------|----------|----------|
| WhatsApp | `service_status_update` | Internal notification |
| Internal | `notification.service.*` | — |
| Email | `service.status_changed` | — |

---

## ⏱️ Timing

- Instant: Status change, assignment, payment
- Scheduled: Warranty reminder (daily check)
- Delayed: Overdue check (every 4 hours)

---

*Service Notification Flow — Sprint 36A*
