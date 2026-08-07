# Repair Timer — Sprint 36B

> Accurate work time tracking with start/pause/resume/finish.

---

## ⏱️ Timer Features

| Action | Description | Backend |
|--------|-------------|---------|
| Start | Begin repair, timer starts counting | `WorkOrder::markInProgress()` → sets `started_at` |
| Pause | Pause timer (e.g., waiting parts) | `WorkOrder::pause()` → records `paused_at` |
| Resume | Resume timer, calculates paused duration | `WorkOrder::resume()` → `total_paused_minutes += diff` |
| Finish | Complete repair, calculate effective time | `WorkOrder::finish()` → `actual_minutes = total - paused` |

---

## 📊 Time Metrics Tracked

| Metric | Calculation |
|--------|-------------|
| Effective Working Time | `actual_minutes` (total elapsed - paused time) |
| Waiting Time | `total_paused_minutes` |
| Idle Time | Time between assignment and first start |
| Total Duration | `completed_at - created_at` |

---

## 📈 Reporting Integration

All timer data flows into:
- Technician KPI (avg repair time, productivity, utilization)
- SLA monitoring (on-time completion %)
- Revenue per technician
- Operational efficiency reports

---

*Repair Timer — Sprint 36B*
