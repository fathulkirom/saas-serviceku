# ServiceKU Enterprise Data Engine

> **Sprint 12.0** — Universal data table/list engine for ALL ERP modules.
> **Status:** ✅ Production Ready

---

## 🎯 What is Data Engine?

Data Engine adalah **meta-framework** untuk tabel/list data di seluruh modul ServiceKU. Define → Register → Render. Tidak ada lagi DataTable manual.

---

## 🏗️ Architecture

```
┌──────────────────────────────────────────────────────────┐
│                    BACKEND (PHP)                          │
├──────────────────────────────────────────────────────────┤
│  DataDefinition      → Schema (columns + filters + bulk) │
│  ColumnDefinition    → 30+ column types                  │
│  FilterDefinition    → 12 filter types                   │
│  BulkAction          → Bulk action buttons               │
│  TableRegistry       → Central registry                  │
│  DataPresenter       → Inertia props + user context      │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│                   FRONTEND (Vue 3)                        │
├──────────────────────────────────────────────────────────┤
│  DataRegistry.js    → Column renderer mapping            │
│  useDataTable()     → Columns, filters, sort, selection  │
│  DataTable.vue      → Full-featured table renderer       │
└──────────────────────────────────────────────────────────┘
```

---

## 📊 Column Types (30+)

| Category | Types |
|----------|-------|
| **Basic** | text, number, currency |
| **Status** | badge, status, boolean |
| **DateTime** | date, datetime, relative_time |
| **Media** | avatar, photo, gallery |
| **Advanced** | tags, progress, rating, qrcode, barcode |
| **Relation** | relation, user, branch, customer, technician, product |
| **Special** | actions, slot |

---

## 🔍 Filter Types

text, number, select, date, date_range, status, multi_select, toggle, range, quick_filter, saved_filter

---

## 📦 Reference Implementations

| Definition | Columns | Filters | Bulk Actions |
|------------|:-------:|:-------:|:------------:|
| `ServiceListDefinition` | 8 | 3 | 4 |
| `CustomerListDefinition` | 8 | 2 | 2 |

---

## 🔌 How to Create a Table (3 Steps)

```php
// 1. Define
DataDefinition(id: 'product.index', title: 'Daftar Produk')
  ->addColumns([new ColumnDefinition('name', 'Nama', type: 'text', sortable: true)])
  ->addFilter(new FilterDefinition('category', 'Kategori', type: 'select'))
  ->addBulkAction(new BulkAction('delete', 'Hapus', variant: 'danger'));

// 2. Register
$registry->register($def);

// 3. Render
<EnterpriseDataTable :tableProps="tableProps" />
```

---

## 🎯 Features

| Feature | Status |
|---------|:------:|
| Column resize | ✅ |
| Column reorder | ✅ |
| Column hide/show | ✅ |
| Column pin left/right | ✅ |
| Multi-sort | ✅ |
| Global search + per-column | ✅ |
| 12 filter types | ✅ |
| Bulk actions (with confirm) | ✅ |
| Export (CSV) | ✅ |
| Row selection (single/multi/all) | ✅ |
| Pagination (offset) | ✅ |
| Aggregates (sum/avg/count) in footer | ✅ |
| Saved views (localStorage) | ✅ |
| Compact/Comfortable density | ✅ |
| Keyboard shortcuts | ✅ |
| Loading state | ✅ |
| Empty state | ✅ |
| Role/Permission/Feature gates per column | ✅ |
| Responsive (scroll horizontal) | ✅ |

---

*ServiceKU Enterprise Data Engine — Sprint 12.0*
