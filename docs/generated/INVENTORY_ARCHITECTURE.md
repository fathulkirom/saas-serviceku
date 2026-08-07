# Inventory & Warehouse Module Architecture

> **Sprint 17.0** — Second ERP module, fully Enterprise Platform.

---

## 🏗️ Architecture

```
Inventory Module
├── Data Engine (Sprint 12) → Inventory List (11 columns, 4 filters, 5 bulk actions)
├── Workspace Engine (Sprint 10) → Inventory Workspace (10 tabs)
├── Form Engine (Sprint 11) → Product Create/Edit (18 fields, 3 sections)
├── Automation Engine (Sprint 13) → 3 rules (low stock, goods received, dead stock)
├── Reporting Engine (Sprint 14) → 5 reports (stock value, fast moving, mutation, supplier, margin)
├── Dashboard Engine (Sprint 8) → 2 widgets (stock alert, inventory value)
└── Design System (Sprint 8) → All UI components
```

---

## 🔌 Engine Wiring

| Feature | Engine | Definition |
|---------|--------|------------|
| Inventory List | Data Engine | `InventoryDefinitions::dataTable()` |
| Product Create | Form Engine | `InventoryDefinitions::createForm()` |
| Product Workspace | Workspace Engine | `InventoryDefinitions::workspace()` |
| Stock Alerts | Automation Engine | `InventoryDefinitions::automations()` |
| Reports | Reporting Engine | `InventoryDefinitions::reports()` |
| Dashboard | Dashboard Engine | `InventoryValueWidget + StockWidget` |

---

## 📊 Workspace Tabs (10)

| Tab | Content |
|-----|---------|
| Overview | Stock cards, product info, pricing |
| Stock Movement | Movement timeline |
| Purchase History | Purchase orders |
| Sales History | Sales transactions |
| Service Usage | Parts used in services |
| Transfer | Inter-warehouse transfers |
| Supplier | Supplier info |
| Price History | Price changes |
| Serial/IMEI | Serial number tracking |
| Documents | Attachments |

---

## 📈 Dashboard Widgets

| Widget | Role | Feature Gate |
|--------|------|-------------|
| Stock Alert (Low Stock) | Owner, Admin, Manager, HeadStore | products → full/read_only |
| Inventory Value | Owner, Admin, Manager, HeadStore | products → full/read_only |

---

## 🤖 Automation Rules

| Rule | Trigger | Action |
|------|---------|--------|
| Low Stock Alert | STOCK_LOW | Push notification + activity |
| Goods Received | RECORD_UPDATED | Timeline entry |
| Dead Stock Detect | SCHEDULE (weekly) | Activity log |

---

*Inventory Architecture — Sprint 17.0*
