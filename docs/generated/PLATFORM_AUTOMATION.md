# Platform Automation

> 15 platform admin automation rules.

---

## 📋 Rule Catalog

| # | Rule | Trigger | Action |
|---|------|---------|--------|
| 1 | Subscription Expiry | DATE_REACHED | Push notification |
| 2 | Trial Ending | DATE_REACHED | Send email |
| 3 | Backup Reminder | SCHEDULED | Push notification |
| 4 | Backup Failure | RECORD_UPDATED | Push notification |
| 5 | Platform Alert | RECORD_UPDATED | Push notification |
| 6 | CPU Alert | RECORD_UPDATED | Push notification |
| 7 | Storage Alert | RECORD_UPDATED | Push notification |
| 8 | Database Alert | RECORD_UPDATED | Push notification |
| 9 | Queue Failure | RECORD_UPDATED | Push notification |
| 10 | Tenant Suspension | RECORD_UPDATED | Send email |
| 11 | License Expiry | DATE_REACHED | Push notification |
| 12 | Security Alert | RECORD_CREATED | Push notification |
| 13 | Compliance Reminder | SCHEDULED | Create task |
| 14 | Billing Reminder | DATE_REACHED | Send email |
| 15 | System Maintenance | SCHEDULED | Maintenance mode + tasks + activity log |

---

*Platform Automation — Sprint 30.0*
