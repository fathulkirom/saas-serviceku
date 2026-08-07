# Technician Dashboard — Sprint 36B

> Real-time technician productivity dashboard with 12 widgets.

---

## 📊 Dashboard Widgets

| # | Widget | Metric | Role |
|---|--------|--------|------|
| 1 | Assigned Today | Jobs assigned today | technician |
| 2 | Waiting Diagnosis | Jobs needing diagnosis | technician |
| 3 | Waiting Sparepart | Jobs waiting for parts | technician |
| 4 | In Progress | Jobs being worked on | technician |
| 5 | Ready QC | Jobs completed, awaiting QC | technician |
| 6 | Completed Today | Jobs finished today | technician |
| 7 | Average Repair Time | Avg effective minutes per job | technician, manager |
| 8 | Productivity Score | Composite KPI score (0-100) | technician, manager |
| 9 | SLA Achievement | % jobs completed within SLA | technician, manager |
| 10 | Parts Used Today | Parts consumed today | technician, inventory |
| 11 | Warranty Repairs | Active warranty repair count | technician |
| 12 | Rework Rate | % jobs requiring rework | technician, manager |

---

## 🔄 Real-Time Updates

All widgets auto-refresh via:
- WebSocket push on status changes
- 30-second polling fallback
- Manual refresh shortcut (`Ctrl+R`)

---

## 📈 Drill-Down

Every metric widget is clickable and navigates to the relevant tab:
- Click "Waiting Diagnosis" → Diagnosis tab
- Click "In Progress" → Today's Jobs tab
- Click "Ready QC" → Quality Check tab

---

*Technician Dashboard — Sprint 36B*
