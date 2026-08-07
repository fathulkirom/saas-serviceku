# Inventory Automation & Reporting

> Sprint 17.0 — Automation rules + Report definitions.

---

## 🤖 Automation Rules (3)

| ID | Name | Trigger | Actions |
|----|------|---------|---------|
| `inventory.low_stock` | Stok Menipis | STOCK_LOW | Push notification + Activity |
| `inventory.goods_received` | Barang Diterima | RECORD_UPDATED | Timeline entry |
| `inventory.dead_stock` | Dead Stock | SCHEDULE (weekly) | Activity log |

---

## 📊 Report Definitions (5)

| ID | Title | Type | Chart | Metrics |
|----|-------|------|:-----:|---------|
| `inventory.stock_value` | Nilai Stok | summary | bar | Total value, item count |
| `inventory.fast_moving` | Fast Moving | summary | bar | Sold count, revenue |
| `inventory.mutation` | Mutasi Stok | summary | line | Qty in, qty out |
| `inventory.supplier_analysis` | Analisis Supplier | summary | bar | Total purchased |
| `inventory.margin` | Analisis Margin | summary | kpi | Revenue, cost, margin |

---

## 🔌 Wiring

```php
// Auto-registered in AppServiceProvider
AutomationRegistry ← InventoryDefinitions::automations()
ReportRegistry     ← InventoryDefinitions::reports()
```

All reports respect:
- Role gates (e.g., `permissions: ['manage_finance']`)
- Feature gates (e.g., `features: ['products']`)
- Business type gates

---

*Inventory Automation & Reporting — Sprint 17.0*
