# Asset Management & Maintenance Architecture

> **Sprint 22.0** — Seventh ERP module, fully integrated Enterprise Platform.

---

## 🏗️ Architecture

```
EAM/CMMS Module
├── Fixed Asset Register   → Data Engine (13 cols, 5 filters, 4 bulk actions)
├── Asset Workspace        → Workspace Engine (14 tabs)
├── Asset Form             → Form Engine (32+ fields, 11 sections)
├── Maintenance Schedule   → Data Engine (10 cols, 4 filters, 4 bulk actions)
├── Asset Movement         → Data Engine (10 cols, 2 filters, 1 bulk action)
├── Warranty Management    → Data Engine (10 cols, 3 filters, 3 bulk actions)
├── Insurance Management   → Data Engine (11 cols, 2 filters, 3 bulk actions)
├── Vehicle Management     → Data Engine (10 cols, 2 filters, 1 bulk action)
├── Tool Management        → Data Engine (9 cols, 2 filters, 3 bulk actions)
├── Automation Engine      → 12 rules (maintenance, warranty, insurance, calibration, depreciation)
├── Reporting Engine       → 12 reports (register, value, depreciation, maintenance cost, schedule, utilization, warranty, insurance, vehicle, tool, movement, disposed)
└── Dashboard Engine       → 3 widgets (TotalAssets, MaintenanceDue, DepreciationMTD)
```

---

## 🏗️ Asset Workspace (14 tabs)

| Tab | Content |
|-----|---------|
| Overview | Asset KPI — total assets, total value, maintenance due, overdue, depreciation, categories |
| Profile | Full asset details |
| Maintenance | Scheduled maintenance, checklist, technician, cost |
| Maintenance History | Complete maintenance log |
| Depreciation | Depreciation schedule + book value tracking |
| Movement | Purchase, transfer, assignment, return, disposal history |
| Assignment | Current custodian, department, branch |
| Warranty | Warranty status, claims, expiry |
| Insurance | Policy, coverage, premium, claims, renewal |
| Calibration | Tool/equipment calibration schedule |
| Inspection | Inspection records |
| Documents | Manuals, invoices, photos |
| Timeline | Full asset timeline |
| Activity Log | Audit trail |

---

## 🔗 Integration Points

| Module | Integration |
|--------|-------------|
| Finance | Depreciation → Auto Journal (Depreciation Expense + Accumulated Depreciation) |
| HRM | Assignment → Employee custodian |
| Inventory | Tool → Inventory item link |
| Purchasing | Asset acquisition → Purchase order link |
| Service | Workshop equipment → Service tracking |

---

## 🔐 Role Matrix

| Role | Asset Access |
|------|-------------|
| Owner | Full — all tabs, reports, disposal |
| Manager | Full — all operational |
| Maintenance | Maintenance schedule + history + calibration |
| Warehouse | Tool management + assignment |
| HRD | Employee asset assignment |
| Finance | Depreciation + value reports |
| Admin | Read-only asset list |
| Technician | Maintenance records |
| Supervisor | Team asset assignment view |

---

*Asset Architecture — Sprint 22.0*
