# Customer Notification Flow — Sprint 36C

> All customer-facing notifications routed through Notification Center.

---

## 🔔 Notification Events

| Event | Trigger | Channel | Timing |
|-------|---------|---------|--------|
| Booking Confirmed | Booking created | WA + Email | Instant |
| Service Received | Status: `diterima` | WA | Instant |
| Diagnosis Update | Diagnosis saved | WA | Instant |
| Estimation Ready | Quotation created | WA + Email | Instant |
| Approval Needed | Status: `menunggu_konfirmasi` | WA | Instant |
| Parts Arrived | Status: `indent` → `dikerjakan` | WA | Instant |
| Repair In Progress | Status: `dikerjakan` | WA | Instant |
| QC Complete | Status: `selesai` → `siap_diambil` | WA | Instant |
| Ready for Pickup | Status: `siap_diambil` | WA + Email | Instant |
| Payment Received | Payment success | WA + Email | Instant |
| Warranty Active | Status: `close` | WA + Email | Instant |
| Warranty Expiring | 3 days before expiry | WA | Scheduled |
| Maintenance Reminder | 90 days after service | WA | Scheduled |
| Feedback Request | 7 days after service | WA | Scheduled |
| Promo Offer | Periodic | WA + Email | Scheduled |

---

## 📱 Channel Priority

1. WhatsApp (primary — highest open rate)
2. Email (secondary — for documents)
3. Push Notification (in-portal)
4. SMS (fallback — for non-WA users)

---

*Customer Notification Flow — Sprint 36C*
