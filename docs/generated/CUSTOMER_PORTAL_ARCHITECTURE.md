# Customer Portal & Technician Portal Architecture

> **Sprint 31.0** — Sixteenth ERP module. Customer-facing & technician-facing web portals.

---

## 🏗️ Architecture

```
Portal Layer (on top of ALL 15 existing modules)
├── Customer Portal         → Workspace Engine (14 tabs)
├── Technician Portal       → Workspace Engine (15 tabs)
├── Appointments            → Data Engine (8 cols, 1 filter, 2 bulk actions)
├── Support Tickets         → Data Engine (8 cols, 2 filters, 1 bulk action)
├── Technician Jobs         → Data Engine (9 cols, 2 filters, 2 bulk actions)
├── Appointment Form        → Form Engine (7 fields)
├── Automation Engine       → 12 rules (appointment, status, warranty, invoice, payment, pickup, feedback, technician, QC, no-show, follow-up)
├── Reporting Engine        → 12 reports (satisfaction, productivity, warranty, appointment, activity, tracking, usage, response, ticket, feedback, growth, leaderboard)
└── Zero new database       → All data from existing ServiceKU models
```

---

## 👤 Customer Portal (14 tabs)

| Tab | Content |
|-----|---------|
| Overview | Active services, ready pickup, warranty, points, spending, appointments, invoices |
| My Services | All service history |
| Service Tracking | Real-time status tracking with timeline |
| Invoices | Invoice list + download |
| Payments | Payment history + pay now |
| Warranty | Active warranties + claim |
| My Devices | Registered devices |
| Purchase History | Purchase records |
| Appointments | Book + manage appointments |
| Support Tickets | Create + track tickets |
| Messages | Chat with CS/technician |
| Notifications | Portal inbox |
| Downloads | Invoice, warranty, reports |
| My Profile | Personal info, devices, addresses |

---

## 🔧 Technician Portal (15 tabs)

| Tab | Content |
|-----|---------|
| Overview | Today's jobs, completed, waiting parts, avg time, performance |
| Today's Jobs | Priority-sorted job list |
| Assigned Jobs | All assigned jobs |
| Job Detail | Full service detail |
| Diagnosis | Diagnostic notes + findings |
| Repair Checklist | Step-by-step checklist |
| Photos | Before/after/damage/progress |
| Parts Used | Spare parts consumed |
| Work Timer | Start/pause/resume timer |
| Quality Check | QC checklist |
| Customer Signature | Digital signature capture |
| Notes | Internal notes |
| History | Completed jobs history |
| Notifications | Tech notifications |
| My Profile | Tech profile + performance |

---

## 🔗 Key Design Principles

| Principle | Implementation |
|-----------|---------------|
| Zero New Database | All data from existing Service, CRM, Finance, HRM, Inventory models |
| Definition Driven | `PortalDefinitions::customerPortal()` + `::technicianPortal()` |
| Registry Driven | `workspaceRegistry.register('customer_portal', ...)` |
| Zero Hardcode | All UI from engines |
| Realtime | Workspace Timeline for service tracking |

---

*Portal Architecture — Sprint 31.0*
