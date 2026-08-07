# Project, Task & Job Management Architecture

> **Sprint 23.0** — Eighth ERP module, fully integrated Enterprise Platform.

---

## 🏗️ Architecture

```
Project Management Module
├── Project Master         → Data Engine (13 cols, 5 filters, 3 bulk actions)
├── Project Workspace      → Workspace Engine (14 tabs)
├── Project Form           → Form Engine (28+ fields, 9 sections)
├── Task Management        → Data Engine (12 cols, 5 filters, 3 bulk actions)
├── Job Management         → Data Engine (10 cols, 4 filters, 4 bulk actions)
├── Milestones             → Data Engine (8 cols, 2 filters, 2 bulk actions)
├── Risk Register          → Data Engine (9 cols, 2 filters, 2 bulk actions)
├── Issue Management       → Data Engine (9 cols, 3 filters, 4 bulk actions)
├── Timesheets             → Data Engine (10 cols, 4 filters, 3 bulk actions)
├── Automation Engine      → 14 rules (project, task, job, milestone, budget, risk, issue, timesheet)
├── Reporting Engine       → 13 reports (summary, progress, task, tech, cost, budget, resource, timesheet, profitability, risk, issue, milestone, portfolio)
└── Dashboard Engine       → 3 widgets (ActiveProjects, TasksDue, OpenIssues)
```

---

## 📁 Project Workspace (14 tabs)

| Tab | Content |
|-----|---------|
| Overview | KPI — active projects, budget, tasks due/overdue, open issues, progress bars, budget vs actual |
| Planning | Objective, scope, deliverables, methodology |
| Tasks | Task list with Kanban status |
| Kanban | Drag & drop board (Backlog→Todo→In Progress→Review→Testing→Done) |
| Gantt | Timeline with dependencies, milestones, critical path |
| Timeline | Activity timeline |
| Milestones | Milestone list with deadlines, progress, status |
| Resources | Employee, equipment, vehicle, tool allocation |
| Files | Documents, drawings, contracts |
| Budget | Budget planning vs actual |
| Cost | Cost breakdown (labor, material, external, overhead) |
| Risks | Risk register with probability × impact |
| Issues | Issue log with status tracking |
| Activity Log | Full audit trail |

---

## 🔗 Cross-Module Integration

| Module | Integration |
|--------|-------------|
| Service | Job assignment → Service work order |
| Inventory | Material request → Stock reservation |
| Purchasing | Purchase request → Vendor |
| CRM | Client project → Customer portal |
| Finance | Budget, cost, profit, invoice → Journal |
| HRM | Employee allocation, attendance, overtime → Payroll |
| Asset | Equipment, vehicle, tool allocation |

---

## 🔐 Role Matrix

| Role | Project Access |
|------|---------------|
| Owner | Full — all projects, reports, budget |
| Manager | Full — all operational |
| Project Manager | Own projects + team tasks |
| Supervisor | Team tasks + jobs |
| Technician | Assigned tasks + jobs |
| Employee | Own tasks + timesheets |
| HR | Resource allocation view |
| Finance | Budget/cost reports |
| Admin | Read-only project list |
| Customer Portal | Client project view only |

---

*Project Architecture — Sprint 23.0*
