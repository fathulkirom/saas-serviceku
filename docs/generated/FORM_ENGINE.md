# ServiceKU Enterprise Form Engine

> **Sprint 11.0** — Universal form framework untuk seluruh modul ERP.
> **Status:** ✅ Production Ready

---

## 🎯 What is Form Engine?

Form Engine adalah **meta-framework** yang mengabstraksi pembuatan form untuk seluruh modul ServiceKU. Define → Register → Render. Tidak ada HTML form manual.

```
Service Form ────┐
Customer Form ───┤
Product Form ────┼──→ Form Engine ──→ Rendered UI
Supplier Form ───┤
Finance Form ────┘
```

---

## 🏗️ Architecture

```
┌──────────────────────────────────────────────────────────┐
│                    BACKEND (PHP)                          │
├──────────────────────────────────────────────────────────┤
│  FormDefinition    → Schema (fields + sections + actions)│
│  FormField         → 40+ field types                     │
│  FormSection       → Section grouping (collapse, cols)   │
│  FormAction        → Action buttons (save, delete, etc.) │
│  FormRegistry      → Central registry                    │
│  FormPresenter     → User context + Inertia props        │
└──────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────┐
│                   FRONTEND (Vue 3)                        │
├──────────────────────────────────────────────────────────┤
│  FormRegistry.js  → Field type → Component mapping       │
│  useForm()        → Values, validation, dirty, history   │
│  FormRenderer     → Layout shell (toolbar + body + foot) │
│  FormSection      → Section renderer                     │
│  FormField        → Dynamic field dispatcher             │
│  Field Components → 25+ field types registered           │
└──────────────────────────────────────────────────────────┘
```

---

## 🎨 Supported Field Types (40+ registered)

| Category | Types |
|----------|-------|
| **Basic** | text, number, email, password, phone, url |
| **Long Text** | textarea, markdown, code, json |
| **Selection** | select, checkbox, radio, switch, autocomplete, multi-select, tags |
| **Numeric** | currency, percentage |
| **DateTime** | date, time, datetime |
| **Media** | photo, gallery, file, pdf, signature |
| **Advanced** | color, otp, barcode, map, floating |
| **Relation** | customer, supplier, technician, product (via autocomplete + asyncUrl) |

---

## 🔌 How to Create a Form

### Step 1: Define (Backend)
```php
// app/Enterprise/Form/Definitions/ProductCreateForm.php
class ProductCreateForm
{
    public static function define(): FormDefinition
    {
        return (new FormDefinition(id: 'product.create', title: 'Produk Baru'))
            ->addSection(new FormSection(id: 'basic', label: 'Info Dasar', cols: 2))
            ->addFields([
                new FormField('name', type: 'text', label: 'Nama Produk', required: true, section: 'basic', cols: 12),
                new FormField('price', type: 'currency', label: 'Harga', required: true, section: 'basic', cols: 6),
                new FormField('stock', type: 'number', label: 'Stok Awal', section: 'basic', cols: 6),
                new FormField('category', type: 'select', label: 'Kategori', section: 'basic', cols: 6),
            ])
            ->addAction(new FormAction('save', 'Simpan', variant: 'primary'));
    }
}
```

### Step 2: Register (Backend)
```php
// In AppServiceProvider
$registry->register(ProductCreateForm::define());
```

### Step 3: Render (Frontend)
```vue
<FormRenderer :formSchema="formSchema" @action="handleAction" />
```

The formSchema comes from the backend via Inertia:
```php
// In controller
$form = (new FormPresenter($registry))->build('product.create');
return Inertia::render('Product/Create', ['formSchema' => $form['schema']]);
```

---

## 📝 Form State Features

| Feature | Description |
|---------|-------------|
| **Dirty Tracking** | Auto-detects unsaved changes |
| **Undo/Redo** | Ctrl+Z / Ctrl+Y history stack (50 entries) |
| **Autosave** | Configurable interval, saves when dirty |
| **Validation** | Realtime + on-blur validation |
| **Optimistic Save** | UI updates immediately, rolls back on error |
| **Field Visibility** | Conditional fields (show/hide based on other values) |
| **Role Gates** | Fields/sections/actions hidden by role |
| **Feature Gates** | Fields hidden based on FeatureEngine |

---

## 📱 Responsive

- **Desktop**: Multi-column grid per section
- **Tablet**: Sections stack, smaller columns
- **Mobile**: Single column, toolbar collapses

---

## 🔧 Adding Custom Field Types

```js
import { fieldRegistry } from '@/Enterprise/Form/FormRegistry.js';

fieldRegistry.register('qrcode', MyQRCodeComponent, {
  size: 256,
  format: 'png',
});
```

---

*ServiceKU Enterprise Form Engine — Sprint 11.0*
