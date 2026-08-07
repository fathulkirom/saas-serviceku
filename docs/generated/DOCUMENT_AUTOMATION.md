# Document Automation

> 15 IFTTT automation rules for Document Management lifecycle.

---

## 📋 Rule Catalog

| # | Rule | Trigger | Action |
|---|------|---------|--------|
| 1 | Document Uploaded | RECORD_CREATED | Activity log |
| 2 | Document Updated | RECORD_UPDATED | Create version + activity log |
| 3 | Version Published | RECORD_UPDATED | Push notification |
| 4 | Approval Requested | RECORD_UPDATED | Push notification + create task |
| 5 | Approval Completed | RECORD_UPDATED | Activity log |
| 6 | Document Expired | DATE_REACHED | Create review task + push notification |
| 7 | Review Due | DATE_REACHED | Create review task |
| 8 | Knowledge Published | RECORD_UPDATED | Create announcement |
| 9 | Knowledge Updated | RECORD_UPDATED | Activity log |
| 10 | Announcement Published | RECORD_CREATED | Push notification to all |
| 11 | Comment Mentioned | RECORD_CREATED | Push notification |
| 12 | OCR Completed | RECORD_UPDATED | Activity log |
| 13 | Retention Triggered | DATE_REACHED | Archive document + activity log |
| 14 | Archive Triggered | DATE_REACHED | Archive document + activity log |
| 15 | Document Restored | RECORD_UPDATED | Activity log |

---

*Document Automation — Sprint 27.0*
