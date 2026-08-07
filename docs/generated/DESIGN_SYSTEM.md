# ServiceKU Enterprise Design System v1.0

> **Sprint 8.0A — Frontend Foundation**
> Dibangun di atas Tailwind CSS + Vue 3 + Inertia.js
> Backward-compatible dengan seluruh kode existing.

---

## 📐 Design Principles

1. **Consistency First** — Setiap komponen memiliki API yang seragam (props, slots, events)
2. **CSS Variables** — Semua warna/spacing/typography menggunakan CSS custom properties
3. **Dark Mode Ready** — Semua komponen support light/dark via `--sk-*` variables
4. **Accessible** — ARIA labels, keyboard navigation, focus management
5. **Performant** — Lazy-load ready, tree-shakeable, minimal bundle impact
6. **Backward Compatible** — Tidak merusak komponen existing (K-* prefix)

---

## 🎨 Color System

### CSS Variables (prefix `--sk-`)

```css
/* Primary */
--sk-primary-50 → --sk-primary-950

/* Neutral / Surface */
--sk-neutral-0 → --sk-neutral-950

/* Semantic */
--sk-success-50 → --sk-success-900
--sk-warning-50 → --sk-warning-900
--sk-danger-50 → --sk-danger-900
--sk-info-50 → --sk-info-900
```

### Existing Variables (tetap digunakan)

```css
--primary, --primary-hover, --primary-soft
--success, --warning, --danger, --info
--bg-app, --bg-card, --bg-hover, --bg-input
--text-primary, --text-secondary, --text-muted
--border-color, --border-light
```

### Dark Mode

Tambahkan class `.dark` ke `<html>` untuk mengaktifkan dark mode. Semua komponen Enterprise otomatis merespons.

---

## 📝 Typography System

### Utility Classes

| Class | Size | Weight | Usage |
|-------|------|--------|-------|
| `sk-heading-1` | 48px | 800 | Hero / Page title |
| `sk-heading-2` | 36px | 700 | Section heading |
| `sk-heading-3` | 30px | 700 | Sub-section heading |
| `sk-heading-4` | 24px | 600 | Card title |
| `sk-heading-5` | 18px | 600 | Small heading |
| `sk-heading-6` | 16px | 600 | Minor heading |
| `sk-subtitle` | 16px | 400 | Section description |
| `sk-body` | 14px | 400 | Paragraph text |
| `sk-body-sm` | 13px | 400 | Secondary text |
| `sk-caption` | 11px | 500 | Small caption |
| `sk-label` | 13px | 500 | Form label |
| `sk-helper` | 12px | 400 | Helper text |
| `sk-error` | 12px | 500 | Error message |
| `sk-success` | 12px | 500 | Success message |
| `sk-code` | 13px | 400 | Inline code |
| `sk-text-gradient` | — | — | Gradient text effect |

### Vue Components

```vue
<SkHeading level="1" gradient>Dashboard</SkHeading>
<SkText variant="subtitle">Deskripsi halaman</SkText>
<SkText variant="error">Field wajib diisi</SkText>
```

---

## 📏 Spacing System

Grid 4px base. Semua spacing menggunakan `--sk-space-*` tokens:

| Token | Value | Usage |
|-------|-------|-------|
| `--sk-space-1` | 4px | Micro gap |
| `--sk-space-2` | 8px | Tight gap |
| `--sk-space-3` | 12px | Compact gap |
| `--sk-space-4` | 16px | Standard gap |
| `--sk-space-6` | 24px | Section gap |
| `--sk-space-8` | 32px | Large gap |
| `--sk-space-12` | 48px | XL gap |
| `--sk-space-16` | 64px | 2XL gap |

---

## 🧩 Component Library

### Cards

| Component | Use Case |
|-----------|----------|
| `SkCard` | General purpose card |
| `SkMetricCard` | KPI / metric display |
| `SkWidgetCard` | Dashboard widget container |

```vue
<!-- Basic Card -->
<SkCard title="Ringkasan" subtitle="Hari ini" icon="📊" accent="primary">
  <p>Content here</p>
  <template #action><SkButton size="sm">Lihat</SkButton></template>
</SkCard>

<!-- Metric Card -->
<SkMetricCard
  label="Pendapatan Hari Ini"
  :value="15000000"
  format="currency"
  trend="+12.5"
  color="success"
/>

<!-- Widget Card -->
<SkWidgetCard title="Aktivitas Terbaru" collapsible :loading="loading">
  <p>Widget content</p>
</SkWidgetCard>
```

### Table

`SkDataTable` — full-featured data table:

```vue
<SkDataTable
  :columns="columns"
  :rows="items"
  searchable
  selectable
  exportable
  @row-click="handleClick"
  @export="exportData"
>
  <template #cell-status="{ value }">
    <SkBadge :variant="value">{{ value }}</SkBadge>
  </template>
  <template #bulk-actions="{ selected }">
    <SkButton variant="danger" size="xs">Hapus {{ selected.length }}</SkButton>
  </template>
</SkDataTable>
```

**Features:** Sticky header, column resize, column hide/show, sorting, search, pagination, bulk select, loading skeleton, responsive, empty state.

### Form

| Component | Use Case |
|-----------|----------|
| `SkFloatingInput` | Input with animated floating label |
| `SkAutocomplete` | Search/select with dropdown |
| `SkSwitch` | Toggle switch |
| `SkCurrencyInput` | Currency (Rp) input |
| `SkFileUpload` | Drag & drop file upload |

```vue
<SkFloatingInput v-model="name" label="Nama Lengkap" required />
<SkAutocomplete v-model="customer" :options="customers" optionLabel="name" />
<SkSwitch v-model="enabled" label="Notifikasi Email" />
<SkCurrencyInput v-model="price" />
<SkFileUpload v-model="files" accept=".pdf,.docx" />
```

### Overlay

| Component | Use Case |
|-----------|----------|
| `SkDrawer` | Slide panel (left/right/bottom/fullscreen) |
| `SkModal` | Dialog (default/danger/wizard, sm/md/lg/xl/fullscreen) |

```vue
<SkDrawer v-model:open="show" title="Detail" position="right" width="600px">
  <p>Content</p>
  <template #footer><SkButton @click="show=false">Tutup</SkButton></template>
</SkDrawer>

<SkModal v-model:open="confirm" title="Hapus Data?" variant="danger">
  <p>Data yang dihapus tidak bisa dikembalikan.</p>
  <template #footer>
    <SkButton variant="secondary" @click="confirm=false">Batal</SkButton>
    <SkButton variant="danger" @click="doDelete">Hapus</SkButton>
  </template>
</SkModal>
```

### Feedback

| Component | Use Case |
|-----------|----------|
| `SkLoading` | Spinner, skeleton, overlay, progress bar |

```vue
<SkLoading variant="spinner" size="lg" text="Memuat..." />
<SkLoading variant="skeleton" type="table" :count="5" />
<SkLoading variant="overlay" text="Menyimpan..." />
<SkLoading variant="progress" :percent="65" label="Upload" />
```

### Empty States

```vue
<SkEmptyState variant="empty" title="Belum ada data" />
<SkEmptyState variant="search" title="Tidak ditemukan" />
<SkEmptyState variant="error" title="Gagal memuat" actionLabel="Coba Lagi" @action="retry" />
<SkEmptyState variant="offline" title="Tidak terhubung" />
<SkEmptyState variant="lock" title="Akses terbatas" />
```

### Navigation

```vue
<SkBreadcrumb :items="[
  { label: 'Dashboard', url: '/' },
  { label: 'Servis', url: '/services' },
  { label: 'Detail' },
]" />

<SkFavorites :items="favorites" active="services" removable
  @select="navigate" @remove="unfavorite" />
```

### Dashboard

```vue
<SkWidgetGrid :widgets="dashboardWidgets" :cols="4">
  <template #widget-revenue="{ widget }">
    <SkMetricCard :label="widget.title" :value="widget.value" format="currency" />
  </template>
</SkWidgetGrid>
```

---

## 🎬 Animation System

### CSS Animation Classes

```css
sk-animate-fade-in
sk-animate-slide-up
sk-animate-slide-down
sk-animate-slide-left
sk-animate-slide-right
sk-animate-scale-in
sk-animate-bounce-in
sk-animate-spin
sk-animate-pulse-soft
```

### Transition Names (Vue `<Transition>`)

```
sk-page       — Page transitions
sk-drawer     — Drawer slide
sk-modal      — Modal scale + fade
sk-dropdown   — Dropdown expand
sk-accordion  — Accordion collapse
sk-toast      — Toast slide in
```

Stagger animation: tambahkan class `sk-stagger` ke parent, children akan animasi sequential.

---

## ⌨️ Keyboard Shortcuts

```js
import { useShortcut, SHORTCUTS } from '@/Enterprise'

// Global search: Ctrl+K
useShortcut('k', openSearch, { ctrl: true })

// Save: Ctrl+S
useShortcut('s', saveData, { ctrl: true, preventDefault: true })

// Close: Escape
useShortcut('Escape', closeModal)
```

### Built-in Shortcuts

| Shortcut | Action |
|----------|--------|
| `Ctrl+K` | Global search |
| `Ctrl+S` | Save |
| `Ctrl+P` | Print |
| `Ctrl+N` | New item |
| `Esc` | Close modal/drawer |
| `→` | Next page |
| `←` | Previous page |
| `/` | Shortcut help |

---

## 📱 Responsive

Semua komponen responsive by default. Gunakan `useBreakpoint()`:

```js
const { isMobile, isTablet, isDesktop, breakpoint } = useBreakpoint()
```

---

## 🎯 Accessibility

- Semua komponen memiliki `role`, `aria-label` yang sesuai
- Focus trap di modal/drawer
- Keyboard navigation (Tab, Escape, Arrow keys)
- Screen reader friendly labels
- Skip-to-content link tersedia
- Color contrast ratio WCAG AA compliant

---

## ⚡ Performance

- Semua komponen tree-shakeable via barrel export
- Lazy-load ready dengan dynamic import:
  ```js
  const SkDataTable = defineAsyncComponent(() => import('@/Enterprise/Components/Table/DataTable.vue'))
  ```
- CSS custom properties = zero runtime cost
- Tidak ada dependency tambahan (hanya Tailwind + Vue)

---

## 📂 File Structure

```
resources/js/Enterprise/
├── index.js                    # Barrel export
├── Theme/
│   ├── tokens.css              # CSS custom properties
│   ├── typography.css           # Typography utility classes
│   └── animations.css           # Animation keyframes & transitions
├── Components/
│   ├── Typography/
│   │   ├── Heading.vue
│   │   └── Text.vue
│   ├── Cards/
│   │   ├── Card.vue
│   │   ├── MetricCard.vue
│   │   └── WidgetCard.vue
│   ├── Table/
│   │   └── DataTable.vue
│   ├── Form/
│   │   ├── FloatingInput.vue
│   │   ├── Autocomplete.vue
│   │   ├── Switch.vue
│   │   ├── CurrencyInput.vue
│   │   └── FileUpload.vue
│   ├── Overlay/
│   │   ├── Drawer.vue
│   │   └── Modal.vue
│   ├── Feedback/
│   │   └── Loading.vue
│   ├── Empty/
│   │   └── EmptyState.vue
│   ├── Navigation/
│   │   ├── Breadcrumb.vue
│   │   └── Favorites.vue
│   └── Dashboard/
│       └── WidgetGrid.vue
└── Composables/
    ├── useShortcut.js
    ├── useBreakpoint.js
    └── useTheme.js
```

---

## 🔗 Integration

### Import tunggal
```js
import { SkCard, SkDataTable, SkModal, useShortcut } from '@/Enterprise'
```

### Import per komponen (smaller bundle)
```js
import SkDataTable from '@/Enterprise/Components/Table/DataTable.vue'
```

### CSS
Sudah di-import otomatis via `app.css`:
```css
@import '../js/Enterprise/Theme/tokens.css';
@import '../js/Enterprise/Theme/typography.css';
@import '../js/Enterprise/Theme/animations.css';
```

---

## 🔄 Backward Compatibility

Enterprise Design System **tidak menggantikan** komponen existing:
- `KButton`, `KInput`, `KTable`, `KCard`, dll → **tetap berfungsi**
- `Drawer.vue`, `Toast.vue`, `EmptyState.vue`, `StatCard.vue` → **tetap digunakan**
- `themes.css` → **tidak diubah**
- `app.css` → **hanya menambah 3 import baru**

Semua halaman existing tetap berfungsi tanpa perubahan.

---

## 📊 Status

| Area | Status | Komponen |
|------|--------|----------|
| Typography | ✅ Complete | Heading, Text, utility classes |
| Color System | ✅ Complete | CSS variables, dark mode |
| Spacing | ✅ Complete | CSS tokens |
| Cards | ✅ Complete | Card, MetricCard, WidgetCard |
| Table | ✅ Complete | DataTable (full features) |
| Form | ✅ Complete | FloatingInput, Autocomplete, Switch, CurrencyInput, FileUpload |
| Overlay | ✅ Complete | Drawer, Modal (all variants) |
| Feedback | ✅ Complete | Loading (spinner/skeleton/overlay/progress) |
| Empty States | ✅ Complete | 5 variant states |
| Navigation | ✅ Complete | Breadcrumb, Favorites |
| Dashboard | ✅ Complete | WidgetGrid |
| Shortcuts | ✅ Complete | useShortcut composable |
| Responsive | ✅ Complete | useBreakpoint composable |
| Theme | ✅ Complete | useTheme composable |
| Animation | ✅ Complete | CSS animations + Vue transitions |
| Accessibility | ✅ Complete | ARIA, keyboard, focus |
| Documentation | ✅ Complete | DESIGN_SYSTEM.md |

---

*ServiceKU Enterprise Design System v1.0 — Sprint 8.0A*
