# Technician Workspace — Sprint 36B

> Production-grade technician experience for HP & Laptop repair operations.

---

## 🔧 Workspace Overview

The Technician Portal is a dedicated workspace optimized for the repair workflow. **15 tabs** cover the complete technician journey.

| # | Tab | Purpose |
|---|-----|---------|
| 1 | Overview | Today's jobs, stats, notifications at a glance |
| 2 | Today's Jobs | All jobs assigned for today with priority |
| 3 | Assigned Jobs | Full list of all assigned jobs |
| 4 | Job Detail | Complete service detail with device info |
| 5 | Diagnosis | Diagnosis form with AI-assisted templates |
| 6 | Repair Checklist | Step-by-step repair checklist |
| 7 | Photos | Photo documentation by category |
| 8 | Parts Used | Sparepart usage with scan/search |
| 9 | Work Timer | Start/pause/resume/finish timer |
| 10 | Quality Check | 22-item QC checklist |
| 11 | Customer Signature | Digital signature capture |
| 12 | Notes | Internal notes, tips, warnings |
| 13 | History | Complete timeline of all actions |
| 14 | Notifications | Real-time notification feed |
| 15 | My Profile | Technician profile and KPI |

---

## ⚡ Job Management Actions

| Action | Method | Description |
|--------|--------|-------------|
| Start Job | `POST /services/{id}/repair/start` | Accept and begin work, starts timer |
| Pause Job | `POST /work-orders/{id}/pause` | Pause timer, record reason |
| Resume Job | `POST /work-orders/{id}/resume` | Resume timer, calculate paused duration |
| Finish Job | `POST /services/{id}/repair/complete` | Complete repair, submit for QC |
| Upload Photo | Custom event | Trigger photo upload component |
| Add Diagnosis | `POST /services/{id}/diagnosis` | Save diagnosis findings |
| Request Parts | `POST /services/{id}/parts` | Request sparepart from inventory |
| Request Approval | `POST /services/{id}/quotation` | Create quotation for approval |
| Get Signature | Custom event | Trigger signature pad |
| Escalate | `POST /services/{id}/workspace/transition` | Escalate to internal confirmation |

---

## 🔗 Integration Points

- **Inventory**: Sparepart usage auto-deducts stock
- **Workflow Center**: All status transitions validated
- **Notification Center**: Real-time alerts for assignments/QC
- **GRC Center**: Compliance and audit trail
- **AI Intelligence Layer**: Knowledge assist and diagnosis suggestions
- **Reporting Engine**: Technician productivity reports

---

*Technician Workspace — Sprint 36B*
