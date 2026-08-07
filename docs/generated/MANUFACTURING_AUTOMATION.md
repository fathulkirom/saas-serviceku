# Manufacturing Automation

> 15 IFTTT automation rules for Manufacturing lifecycle.

---

## 📋 Rule Catalog

| # | Rule | Trigger | Action |
|---|------|---------|--------|
| 1 | Production Created | RECORD_CREATED | Push notification |
| 2 | Production Approved | RECORD_UPDATED | Reserve materials + notify |
| 3 | Production Started | RECORD_UPDATED | Activity log |
| 4 | Production Paused | RECORD_UPDATED | Push notification |
| 5 | Production Completed | RECORD_UPDATED | Update inventory + auto journal + activity log |
| 6 | Production Delayed | DATE_REACHED | Push notification |
| 7 | Material Shortage | RECORD_UPDATED | Create purchase suggestion + notify |
| 8 | QC Failed | RECORD_UPDATED | Create CAPA task + notify |
| 9 | QC Passed | RECORD_UPDATED | Activity log |
| 10 | Machine Down | RECORD_UPDATED | Create repair task + notify |
| 11 | Maintenance Due | DATE_REACHED | Create maintenance task |
| 12 | Production Closed | RECORD_UPDATED | Activity log |
| 13 | BOM Changed | RECORD_UPDATED | Push notification |
| 14 | Routing Updated | RECORD_UPDATED | Push notification |
| 15 | MRP Generated | RECORD_CREATED | Create review task |

---

*Manufacturing Automation — Sprint 25.0*
