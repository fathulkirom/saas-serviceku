# Asset Automation

> 12 IFTTT automation rules for Asset Management lifecycle.

---

## 📋 Rule Catalog

| # | Rule | Trigger | Action |
|---|------|---------|--------|
| 1 | Asset Created | RECORD_CREATED | Activity log |
| 2 | Maintenance Due | DATE_REACHED | Create task + push notification |
| 3 | Maintenance Overdue | DATE_REACHED | Push notification (maintenance, manager, owner) |
| 4 | Warranty Expiring | DATE_REACHED (30d before) | Create task + push notification |
| 5 | Insurance Expiring | DATE_REACHED (30d before) | Create task + push notification |
| 6 | Calibration Due | DATE_REACHED | Create task |
| 7 | Inspection Due | DATE_REACHED | Create task |
| 8 | Asset Assigned | RECORD_UPDATED | Push notification to employee |
| 9 | Asset Returned | RECORD_UPDATED | Activity log |
| 10 | Depreciation Posted | RECORD_UPDATED | Auto journal (Finance) |
| 11 | Asset Disposed | RECORD_UPDATED | Activity log |
| 12 | Vehicle Tax Due | DATE_REACHED | Create task + push notification |

---

*Asset Automation — Sprint 22.0*
