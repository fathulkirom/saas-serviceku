# Portal Automation

> 12 automation rules for customer & technician portal.

---

## 📋 Rule Catalog

| # | Rule | Trigger | Action |
|---|------|---------|--------|
| 1 | Appointment Reminder | DATE_REACHED | Send WA reminder |
| 2 | Service Status Changed | RECORD_UPDATED | Send WA + push notification |
| 3 | Warranty Expiring | DATE_REACHED | Send WA alert |
| 4 | Invoice Created | RECORD_CREATED | Send WA notification |
| 5 | Payment Reminder | DATE_REACHED | Send WA reminder |
| 6 | Ready Pickup | RECORD_UPDATED | Send WA notification |
| 7 | Customer Feedback | RECORD_UPDATED | Send rating request (24h delay) |
| 8 | Technician Assigned | RECORD_UPDATED | Push notification to technician |
| 9 | Technician Completed | RECORD_UPDATED | Activity log |
| 10 | QC Failed | RECORD_UPDATED | Push notification to technician |
| 11 | Customer No Show | DATE_REACHED | Send WA rebooking link |
| 12 | Follow Up After Service | DATE_REACHED | Send WA follow-up (7d delay) |

---

*Portal Automation — Sprint 31.0*
