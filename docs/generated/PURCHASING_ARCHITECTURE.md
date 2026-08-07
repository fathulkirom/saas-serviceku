# Purchasing & Procurement Module Architecture

> **Sprint 18.0** — Third ERP module, fully Enterprise Platform.

---

## 🏗️ Architecture

```
Purchasing Module
├── Data Engine       → Purchase Order List (11 cols, 4 filters, 4 bulk actions)
├── Workspace Engine  → Purchase Workspace (12 tabs) + Supplier Workspace (9 tabs)
├── Form Engine       → PO Create (18 fields, 4 sections)
├── Automation Engine → 5 rules (PO created, approved, goods received, invoice due, completed)
├── Reporting Engine  → 5 reports (summary, by supplier, outstanding, lead time, performance)
├── Dashboard Engine  → Purchase widget
└── Design System     → All UI components
```

---

## 📊 Workspaces (2)

| Workspace | Tabs |
|-----------|------|
| **Purchasing** | Overview, Workflow, Items, Supplier, Quotation, Approval, Goods Receipt, Invoice, Payment, Timeline, Documents, Activity (12) |
| **Supplier** | Overview, Contacts, Purchase History, Outstanding PO, Invoices, Payments, Performance, Documents, Timeline (9) |

---

## 🔄 Procurement Workflow

```
PR → RFQ → Quotation → Supplier Selection → PO → Approval → Goods Receipt → Invoice → Payment → Completed
```

---

## 🤖 Automation Rules (5)

| Rule | Trigger | Actions |
|------|---------|---------|
| PO Created | RECORD_CREATED | Notification: "Approval required" |
| PO Approved | STATUS_CHANGED | Email to supplier + Timeline |
| Goods Received | RECORD_UPDATED | Timeline + Update inventory stock |
| Invoice Due | DATE_REACHED | Notification reminder |
| Purchase Completed | STATUS_CHANGED | Timeline + Activity log |

---

## 📈 Dashboard Widget

| Widget | Role | Feature |
|--------|------|---------|
| Purchase Today | Owner, Admin, Manager | purchases → full/read_only |

---

*Purchasing Architecture — Sprint 18.0*
