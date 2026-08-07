# ServiceKU Enterprise Reporting & Analytics Engine

> **Sprint 14.0** — Universal BI/Reporting framework for ALL ERP modules.
> **Status:** ✅ Production Ready

---

## 🎯 What is Reporting Engine?

Enterprise BI engine — laporan, chart, dan KPI dashboard. Define → Register → Render. Semua modul pakai engine yang sama.

---

## 🏗️ Architecture

```
ReportDefinition → MetricDefinition + DimensionDefinition + ReportFilter
        ↓
  ReportRegistry → Query + Aggregation + Chart formatting
        ↓
  ReportPresenter → Inertia props
        ↓
  ReportViewer → KPIGrid + ChartViewer + Data Table
```

---

## 📊 Chart Types

| Chart | Description |
|-------|-------------|
| **bar** | Bar chart with hover tooltips |
| **line** | Line/area chart |
| **pie** | Pie chart with legend |
| **donut** | Donut with center total |
| **kpi** | KPI metric cards grid |
| **table** | Data table only |

All charts use **pure SVG/CSS** — no heavy chart library needed.

---

## 📦 Reference Reports (5)

| ID | Title | Type | Chart |
|----|-------|------|:-----:|
| `service.daily` | Ringkasan Servis Harian | summary | bar |
| `service.status` | Status Servis | summary | pie |
| `sales.daily` | Penjualan Harian | summary | line |
| `inventory.low_stock` | Stok Menipis | summary | table |
| `finance.pl` | Laba Rugi Ringkas | summary | kpi |

---

## 🔌 How to Create a Report (3 Steps)

```php
// 1. Define
(new ReportDefinition('my.report', 'My Report', modelClass: MyModel::class, chartType: 'bar'))
    ->addMetric(new MetricDefinition('total', 'Total', 'sum', 'amount', format: 'currency'))
    ->addDimension(new DimensionDefinition('date', 'Date', 'created_at', type: 'date'))
    ->addFilter(new ReportFilter('status', 'Status', 'select', options: [...]));

// 2. Register
$registry->register($report);

// 3. Render
<ReportViewer />
```

---

## 🎨 Components

| Component | Description |
|-----------|-------------|
| `ReportViewer` | Full report page — header, filters, chart, table, footer |
| `KPIGrid` | Responsive KPI metric cards (1-4 columns) |
| `ChartViewer` | SVG chart renderer (bar, line, pie, donut) |
| `ReportRegistry` | Frontend report registry |

---

*ServiceKU Enterprise Reporting Engine — Sprint 14.0*
