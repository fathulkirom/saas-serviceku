# CRM & Customer Management Architecture

> **Sprint 19.0** — Fourth ERP module, fully Enterprise Platform.

---

## 🏗️ Architecture

```
CRM Module
├── Data Engine       → Customer List (12 cols, 4 filters, 4 bulk actions)
├── Workspace Engine  → Customer Workspace (13 tabs)
├── Form Engine       → Customer Create (23 fields, 4 sections)
├── Automation Engine → 5 rules (new customer, birthday, no visit, VIP upgrade, reactivated)
├── Reporting Engine  → 5 reports (growth, segmentation, top customers, LTV, inactive)
├── Dashboard Engine  → New Customers widget (existing)
└── Design System     → All UI components
```

---

## 📊 Customer Workspace (13 tabs)

| Tab | Content |
|-----|---------|
| Overview | Customer 360° — service count, spending, LTV, last visit, devices, member card, favorites |
| Profile | Full customer detail |
| Timeline | Combined timeline (service, sales, purchase, payment, warranty, communication) |
| Service History | All services |
| Purchase History | All purchases |
| Invoices | All invoices |
| Payments | Payment history |
| Devices | Device management (IMEI, serial, warranty) |
| Warranty | Active warranties |
| Communication | WA, SMS, Email history |
| Notes | Internal notes, pinned, mentions |
| Documents | Attachments |
| Activity | Activity log |

---

## 🎯 Customer 360° Metrics

| Metric | Source |
|--------|--------|
| Total Service | Service count |
| Total Spending | Sum of purchases |
| Lifetime Value | Revenue - cost |
| Last Visit | Most recent service/purchase date |
| Device Count | Registered devices |
| Favorite Technician | Most assigned tech |
| Favorite Product | Most purchased product |
| Avg Ticket | Average transaction value |
| Member Level | Regular / Silver / Gold / Platinum |

---

## 🤖 Automation Rules (5)

| Rule | Trigger | Action |
|------|---------|--------|
| New Customer Welcome | CUSTOMER_CREATED | Create follow-up task (1h delay) |
| Birthday | DATE_REACHED | Send WhatsApp greeting |
| No Visit 30 Days | DATE_REACHED | Push notification |
| VIP Upgrade | RECORD_UPDATED | WhatsApp + Timeline |
| Customer Reactivated | RECORD_UPDATED | Activity log |

---

## 📈 Dashboard Widgets

| Widget | Role | Feature |
|--------|------|---------|
| New Customers Today | CS, Owner, Admin, Manager | customers → full/read_only |

---

*CRM Architecture — Sprint 19.0*
