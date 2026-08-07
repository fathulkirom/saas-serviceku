# ServiceKU Frontend Architecture

> Arsitektur frontend ServiceKU — struktur, pola, dan konvensi.

---

## 🏛️ Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                    BROWSER (Vue 3 SPA)                      │
├─────────────────────────────────────────────────────────────┤
│  Inertia.js (Server-Side Routing + Client-Side Rendering)   │
├─────────────────────────────────────────────────────────────┤
│  Vue 3 App                                                  │
│  ├── Pages/           (Inertia page components)             │
│  ├── Layouts/         (Page layouts)                        │
│  ├── Components/      (K-* shared components)               │
│  ├── Enterprise/      (NEW: Design System)                  │
│  │   ├── Components/  (Sk-* enterprise components)          │
│  │   ├── Composables/ (useShortcut, useTheme, etc.)         │
│  │   └── Theme/       (CSS tokens, typography, animations)  │
│  ├── Composables/     (useToast, useFormatter, etc.)        │
│  └── Utils/           (statusMaps)                          │
├─────────────────────────────────────────────────────────────┤
│  Laravel Backend (API, Routing, Auth, Tenancy)              │
└─────────────────────────────────────────────────────────────┘
```

---

## 📂 Directory Structure

```
resources/
├── css/
│   ├── app.css              # Main stylesheet + Tailwind
│   └── themes.css            # Core theme CSS variables
├── js/
│   ├── app.js                # Vue + Inertia bootstrap
│   ├── lib/
│   │   └── utils.js          # cn() utility (clsx + tailwind-merge)
│   ├── Components/           # Shared K-* components
│   │   ├── KButton.vue       # Reusable button
│   │   ├── KInput.vue        # Reusable input/select/textarea
│   │   ├── KTable.vue        # Basic table
│   │   ├── KCard.vue         # Basic card
│   │   ├── KDialog.vue       # Modal shell
│   │   ├── KDrawer.vue       # Drawer (alias → Drawer.vue)
│   │   ├── Drawer.vue        # Drawer base
│   │   ├── Toast.vue         # Toast notifications
│   │   ├── EmptyState.vue    # Basic empty state
│   │   ├── StatCard.vue      # Stat card
│   │   ├── Pagination.vue    # Pagination
│   │   ├── Skeleton.vue      # Loading skeleton
│   │   └── ui/               # UI primitives (button, card, input, label)
│   ├── Enterprise/           # ⭐ ENTERPRISE DESIGN SYSTEM
│   │   ├── index.js          # Barrel export
│   │   ├── Theme/
│   │   │   ├── tokens.css     # CSS design tokens
│   │   │   ├── typography.css # Typography classes
│   │   │   └── animations.css # Animations & transitions
│   │   ├── Components/
│   │   │   ├── Typography/   # Heading, Text
│   │   │   ├── Cards/        # Card, MetricCard, WidgetCard
│   │   │   ├── Table/        # DataTable
│   │   │   ├── Form/         # FloatingInput, Autocomplete, Switch, Currency, FileUpload
│   │   │   ├── Overlay/      # Drawer, Modal
│   │   │   ├── Feedback/     # Loading
│   │   │   ├── Empty/        # EmptyState (5 variants)
│   │   │   ├── Navigation/   # Breadcrumb, Favorites
│   │   │   └── Dashboard/    # WidgetGrid
│   │   └── Composables/
│   │       ├── useShortcut.js
│   │       ├── useBreakpoint.js
│   │       └── useTheme.js
│   ├── Composables/          # App-level composables
│   │   ├── useToast.js
│   │   ├── useFormatter.js
│   │   ├── useServiceStatus.js
│   │   ├── useProductivity.js
│   │   └── layoutHelpers.js
│   ├── Layouts/
│   │   ├── AuthenticatedLayout.vue  # Main app shell
│   │   ├── GuestLayout.vue          # Auth pages shell
│   │   └── Themes/
│   │       ├── LayoutNew.vue        # Main layout with sidebar
│   │       ├── Sidebar.vue          # Navigation sidebar
│   │       ├── HeaderBar.vue        # Top header bar
│   │       └── GlobalSearch.vue     # Ctrl+K search
│   ├── Pages/                # Inertia page components
│   │   ├── Dashboard.vue
│   │   ├── Services/
│   │   ├── Customers/
│   │   ├── Keuangan/
│   │   ├── Inventaris/
│   │   └── ...
│   └── Utils/
│       └── statusMaps.js
└── views/                    # Blade templates (entry points)
    ├── app.blade.php         # Main layout
    └── ...
```

---

## 🔄 Data Flow

```
Laravel Controller
    ↓ (Inertia response)
Vue Page Component
    ↓ (props + slots)
Layout Component (AuthenticatedLayout)
    ↓
Enterprise Components (SkCard, SkDataTable, etc.)
    ↓
K-* Primitives (KButton, KInput, etc.)
    ↓
Browser DOM
```

---

## 🧩 Component Hierarchy

### Layout Tree
```
GuestLayout / AuthenticatedLayout
    └── LayoutNew
        ├── Sidebar
        │   ├── Navigation Menu
        │   ├── SkFavorites (NEW)
        │   └── Branch Switcher
        ├── HeaderBar
        │   ├── Mobile Menu Toggle
        │   ├── SkBreadcrumb (NEW)
        │   ├── GlobalSearch (Ctrl+K)
        │   ├── Notifications
        │   ├── Theme Switcher
        │   └── User Menu
        ├── <Page Content>
        │   ├── SkHeading (NEW)
        │   ├── SkCard / SkMetricCard (NEW)
        │   ├── SkDataTable (NEW)
        │   └── SkModal / SkDrawer (NEW)
        └── Toast
```

---

## 🎨 Styling Strategy

### Layer 1: Tailwind Utility Classes
Digunakan untuk layout, spacing, dan styling cepat.

### Layer 2: CSS Custom Properties
Semua warna, spacing, typography melalui CSS variables.
Definisi di `themes.css` (core) + `tokens.css` (enterprise).

### Layer 3: Component Classes
Utility classes seperti `.card`, `.btn`, `.sk-heading-1`.
Definisi di `app.css` + `typography.css`.

### Layer 4: Vue Component Styles
Scoped styles untuk komponen spesifik.

**Aturan:** Jangan hardcode warna. Selalu gunakan `var(--*)`.

---

## 🚀 Performance Strategy

### Code Splitting
Semua halaman di-split otomatis oleh Inertia + Vite:
```js
// app.js
resolve: (name) => resolvePageComponent(
    `./Pages/${name}.vue`,
    import.meta.glob('./Pages/**/*.vue')
)
```

### Lazy Loading (Recommended)
Untuk komponen berat, gunakan `defineAsyncComponent`:
```js
const SkDataTable = defineAsyncComponent(() =>
    import('@/Enterprise/Components/Table/DataTable.vue')
)
```

### Keep-Alive
Gunakan `<KeepAlive>` untuk halaman yang sering diakses:
```vue
<KeepAlive :include="['Dashboard']">
    <component :is="$page.component" />
</KeepAlive>
```

### Memoization
Gunakan `v-memo` untuk list besar:
```vue
<tr v-for="row in rows" :key="row.id" v-memo="[row.updated_at]">
```

---

## 🔌 Integration Points

### Inertia.js
Semua navigasi menggunakan `<Link>` atau `router.visit()`:
```js
import { Link, router } from '@inertiajs/vue3'
```

### Ziggy Routes
```js
route('services.show', { id: 1 })
```

### Toast Notifications
```js
import { useToast } from '@/Composables/useToast.js'
const toast = useToast()
toast.success('Data berhasil disimpan')
```

### Formatter
```js
import { useFormatter } from '@/Composables/useFormatter.js'
const { formatNumber, formatCurrency, formatDate } = useFormatter()
```

---

## 📋 Component Naming Convention

| Prefix | Type | Example |
|--------|------|---------|
| `K` | K-* shared primitives | `KButton`, `KInput`, `KTable` |
| `Sk` | Enterprise Design System | `SkCard`, `SkDataTable`, `SkModal` |
| No prefix | Legacy/custom | `Drawer`, `StatCard`, `EmptyState` |
| PascalCase | Pages | `Dashboard`, `Services/Index` |

---

## 🔒 Backward Compatibility Guarantee

1. **K-* components tetap digunakan** — tidak dihapus
2. **CSS variables existing tidak diubah** — hanya ditambah
3. **Import path existing tidak berubah**
4. **Semua halaman existing tetap berfungsi** — tidak perlu migrasi
5. **Enterprise components adalah ADDITIVE** — melengkapi, bukan mengganti

---

*ServiceKU Frontend Architecture — Sprint 8.0A*
