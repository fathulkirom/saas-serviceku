# Project Deprecation Strategy

> Plan for future Project module iterations & migration.

---

## ❌ Deprecated in Sprint 23

| Component | Reason | Replacement |
|-----------|--------|-------------|
| Manual project tracking | Replaced by Data Engine | `ProjectDefinitions::projectTable()` |
| Spreadsheet task list | Replaced by Task Engine | `ProjectDefinitions::taskTable()` |
| Paper job assignment | Replaced by Job Engine | `ProjectDefinitions::jobTable()` |
| Ad-hoc timesheet | Replaced by Timesheet Engine | `ProjectDefinitions::timesheetTable()` |
| Excel risk register | Replaced by Risk Engine | `ProjectDefinitions::riskTable()` |

---

## 🔮 Future Enhancements (Sprint 24+)

| Feature | Priority | Notes |
|---------|----------|-------|
| Full Drag & Drop Kanban | P2 | Interactive board |
| Interactive Gantt Chart | P2 | Edit tasks on Gantt |
| Resource Leveling | P3 | Auto-balance allocation |
| Earned Value Management (EVM) | P3 | CPI, SPI metrics |
| Agile/Scrum Support | P3 | Sprints, velocity, burndown |
| Client Portal (Project View) | P3 | Customer sees their project progress |
| Project Template Library | P2 | Reusable project templates |
| Time Tracking Timer | P2 | Start/stop timer in UI |

---

*Project Deprecation — Sprint 23.0*
