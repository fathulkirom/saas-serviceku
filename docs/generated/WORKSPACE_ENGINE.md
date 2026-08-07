# ServiceKU Enterprise Workspace Engine

> **Sprint 10.0** — Universal workspace framework untuk seluruh modul ERP.
> **Status:** ✅ Production Ready

---

## 🎯 What is Workspace Engine?

Workspace Engine adalah **meta-framework** yang mengabstraksi pola workspace untuk seluruh modul ServiceKU. Alih-alih membangun `ServiceWorkspace`, `InventoryWorkspace`, `POSWorkspace` secara terpisah, semua modul menggunakan engine yang sama.

```
Service Workspace ───┐
Inventory Workspace ─┤
POS Workspace ───────┼──→ Workspace Engine ──→ User Interface
Finance Workspace ───┤
CRM Workspace ───────┘
```

---

## 🏗️ Architecture

### Backend (Laravel)
```
WorkspaceRegistry          → Central registry, semua workspace didaftarkan di sini
  └── WorkspaceDefinition  → Definisi workspace (tabs, actions, sidebar, shortcuts)
       └── ServiceWorkspace → Implementasi pertama
       └── InventoryWorkspace → (Future)

WorkspaceService           → Resolve workspace + data context + permissions
WorkspaceController        → Thin Inertia render + action execution
```

### Frontend (Vue 3)
```
FrontendWorkspaceRegistry  → Registry komponen UI per module
useWorkspace()             → Universal composable
  ├── switchTab()
  ├── executeAction()
  ├── refresh()
  └── toggleInspector()

Layout Shell:
  WorkspaceHeader    → Breadcrumb, status, search, favorite, refresh
  WorkspaceToolbar   → Dynamic action buttons from registry
  WorkspaceTabs      → Tab navigation from registry
  WorkspaceSidebar   → Modular sidebar widgets from registry
  WorkspaceInspector → Right panel: properties, metadata, relations
  WorkspaceFooter    → Timestamp + refresh
  WorkspaceTimeline  → Universal timeline component
```

---

## 🔌 How to Add a New Workspace Module

### Step 1: Backend Definition
```php
// app/Workspace/Definitions/InventoryWorkspace.php
class InventoryWorkspace extends WorkspaceDefinition
{
    public function __construct()
    {
        parent::__construct(
            id: 'inventory',
            title: 'Inventory Workspace',
            icon: '📦',
            tabs: [
                ['id' => 'overview', 'label' => 'Overview', 'icon' => '📋'],
                ['id' => 'movement', 'label' => 'Movement', 'icon' => '🔄'],
                ['id' => 'stock', 'label' => 'Stock', 'icon' => '📊'],
            ],
            actions: [
                ['id' => 'add_stock', 'label' => 'Tambah Stok', 'roles' => ['owner', 'admin']],
                ['id' => 'transfer', 'label' => 'Transfer', 'roles' => ['owner', 'admin', 'manager']],
            ],
            features: ['products'],
        );
    }
}
```

### Step 2: Register in AppServiceProvider
```php
// Add to the registerAll() call:
new InventoryWorkspace(),
```

### Step 3: Frontend UI Registration
```js
// resources/js/Enterprise/Workspace/registrations/inventory.js
import { workspaceRegistry } from '@/Enterprise/Workspace/WorkspaceRegistry.js';
import InventoryOverview from '@/Pages/Inventory/sections/Overview.vue';
import InventoryMovement from '@/Pages/Inventory/sections/Movement.vue';

workspaceRegistry.register('inventory', {
  tabs: {
    overview: InventoryOverview,
    movement: InventoryMovement,
    stock: InventoryStock,
  },
  actionHandlers: {
    add_stock(data, payload) { /* ... */ },
  },
});
```

### Step 4: Controller Data Context
```php
// In WorkspaceController::resolveDataContext()
'inventory' => $this->resolveInventoryContext($id),
```

### DONE. Workspace langsung muncul di UI.

---

## 🎨 Components

| Component | Description | Registry-Driven? |
|-----------|-------------|:----------------:|
| `WorkspaceHeader` | Breadcrumb, status badge, search, favorite, refresh | Partially |
| `WorkspaceToolbar` | Dynamic action buttons | ✅ Fully |
| `WorkspaceTabs` | Tab navigation | ✅ Fully |
| `WorkspaceSidebar` | Modular sidebar widgets | ✅ Fully |
| `WorkspaceInspector` | Right panel: properties, metadata, relations | ✅ Fully |
| `WorkspaceFooter` | Timestamp + refresh | No |
| `WorkspaceTimeline` | Universal timeline | No |

---

## ⌨️ Keyboard Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl+K` | Quick search |
| `Ctrl+R` | Refresh workspace |
| `Ctrl+E` | Edit record |
| `Ctrl+I` | Toggle inspector |
| `Esc` | Close inspector/panel |

Shortcuts dapat dikustomisasi per workspace melalui `shortcuts: []` di definition.

---

## 🔒 Permission Model

Setiap workspace dicek melalui 4 gate:

1. **Role** — `roles: ['owner', 'admin']` 
2. **Permission** — `permissions: ['manage_products']`
3. **Feature** — `features: ['products']` (via FeatureEngine)
4. **Business Type** — `denyBusinessTypes: ['retail_only']`

Action dan tab juga bisa di-gate per role:

```php
['id' => 'diagnosis', 'label' => 'Diagnosa', 'roles' => ['owner', 'technician']]
```

---

## 📱 Responsive

- **Desktop**: Header + Toolbar + Tabs + Content + Sidebar + Inspector
- **Tablet**: Sidebar collapsible, Inspector becomes drawer
- **Mobile**: Sidebar = drawer, toolbar scroll horizontal, tabs swipe

---

## 🚀 Performance

- **Lazy tabs**: Hanya render tab yang aktif
- **Partial reload**: `router.reload({ only: ['workspaceConfig'] })`
- **Optimistic actions**: UI update dulu, rollback jika gagal
- **Intersection Observer**: Timeline lazy-load entries

---

*ServiceKU Enterprise Workspace Engine — Sprint 10.0*
