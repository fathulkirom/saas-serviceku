# HRM & Employee Management Architecture

> **Sprint 21.0** — Sixth ERP module, fully integrated Enterprise Platform.

---

## 🏗️ Architecture

```
HRM Module
├── Employee Master        → Data Engine (12 cols, 4 filters, 4 bulk actions)
├── Employee Workspace     → Workspace Engine (14 tabs)
├── Employee Form          → Form Engine (35+ fields, 9 sections)
├── Attendance             → Data Engine (11 cols, 4 filters, 2 bulk actions)
├── Leave Management       → Data Engine (10 cols, 3 filters, 3 bulk actions)
├── Payroll                → Data Engine (11 cols, 4 filters, 4 bulk actions)
├── Performance            → Data Engine (10 cols, 3 filters, 1 bulk action)
├── Training               → Data Engine (10 cols, 3 filters, 3 bulk actions)
├── Recruitment            → Data Engine (10 cols, 3 filters, 3 bulk actions)
├── Employee Assets        → Data Engine (9 cols, 3 filters, 2 bulk actions)
├── Organization           → Data Engine (8 cols, 2 filters, 1 bulk action)
├── Automation Engine      → 12 rules (employee lifecycle, attendance, payroll, training, review)
├── Reporting Engine       → 13 reports (attendance, leave, payroll, performance, training, recruitment, turnover, growth, salary, org)
└── Dashboard Engine       → 3 widgets (EmployeeCount, AttendanceToday, PayrollPending)
```

---

## 👤 Employee Workspace (14 tabs)

| Tab | Content |
|-----|---------|
| Overview | HR KPI dashboard — employees, attendance, leave, payroll, birthdays, departments |
| Profile | Full employee profile |
| Attendance | Clock in/out records with GPS, photo, status |
| Schedule | Shift calendar (morning, afternoon, night, flexible) |
| Leave | Leave balance + request history |
| Payroll | Salary slips + payroll history |
| Performance | KPI, targets, evaluations, grades |
| Training | Programs, attendance, certificates |
| Recruitment | Pipeline, interviews, offers |
| Assets | Assigned assets (laptop, phone, tools) |
| Documents | Contracts, certificates, ID |
| Timeline | Full HR timeline |
| Activity Log | Audit trail |
| Notes | Internal HR notes |

---

## 🔗 Integration Points

| Module | HRM Integration |
|--------|----------------|
| Finance | Payroll → Auto Journal (Salary Expense) |
| Finance | BPJS/Tax → Tax Engine |
| CRM | Employee → Customer Service assignments |
| Service | Technician → Service assignments + Performance |
| Inventory | Employee → Asset assignment tracking |
| Purchasing | Employee → Purchase approver chain |

---

## 🔐 Role Matrix

| Role | HRM Access |
|------|-----------|
| Owner | Full — all tabs, reports, payroll |
| HRD | Full — all operational HR |
| Manager | Team attendance, leave approval, performance |
| Finance | Payroll tab only |
| Admin | Read-only employee list |
| Supervisor | Team attendance + performance |
| Technician | Own profile, attendance, leave |
| CS / Cashier / Warehouse / Purchasing | Own profile, attendance, leave |

---

*HRM Architecture — Sprint 21.0*
