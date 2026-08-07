# ServiceKU UI Component Guide

> Panduan lengkap penggunaan komponen Enterprise Design System.
> Semua komponen tersedia via `@/Enterprise`.

---

## 📦 Installation

Tidak perlu instalasi tambahan. Komponen sudah otomatis tersedia setelah Sprint 8.0A.

### Import

```js
// Import semua (convenience)
import { SkCard, SkDataTable, SkModal } from '@/Enterprise'

// Import individual (smaller bundle)
import SkDataTable from '@/Enterprise/Components/Table/DataTable.vue'
```

---

## 🧩 Components API Reference

### SkHeading

Typography heading dengan level 1-6.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `level` | Number | `1` | Heading level (1-6) |
| `gradient` | Boolean | `false` | Gradient text effect |
| `extraClass` | String | `''` | Additional CSS classes |

```vue
<SkHeading level="1">Page Title</SkHeading>
<SkHeading level="2" gradient>Premium Heading</SkHeading>
<SkHeading level="3">Section Title</SkHeading>
```

---

### SkText

Typography text dengan berbagai variant.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `variant` | String | `'body'` | body, body-sm, body-xs, subtitle, subtitle-sm, caption, helper, error, success, label, label-sm |
| `muted` | Boolean | `false` | Apply muted style |
| `extraClass` | String | `''` | Additional CSS classes |

```vue
<SkText variant="subtitle">Deskripsi halaman ini.</SkText>
<SkText variant="error">Field email wajib diisi.</SkText>
<SkText variant="caption">Diperbarui 5 menit yang lalu</SkText>
```

---

### SkCard

General purpose card dengan header, content, footer.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | String | `''` | Card title |
| `subtitle` | String | `''` | Card subtitle |
| `icon` | String | `''` | Icon HTML/SVG |
| `variant` | String | `'default'` | default, glass, danger |
| `size` | String | `'md'` | sm, md, lg |
| `accent` | String | `''` | primary, success, warning, danger, info |
| `hover` | Boolean | `false` | Enable hover animation |
| `extraClass` | String | `''` | Additional CSS classes |

| Slot | Description |
|------|-------------|
| `default` | Card body content |
| `header` | Custom header (replaces default) |
| `icon` | Custom icon |
| `action` | Right-side action button |
| `footer` | Bottom footer section |

---

### SkMetricCard

KPI / metric display dengan trend indicator.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `label` | String | *required* | Metric label |
| `value` | Number/String | `0` | Metric value |
| `format` | String | `'number'` | number, currency, decimal, percent |
| `trend` | Number/String | — | Trend percentage (+/-) |
| `subtext` | String | `''` | Subtitle text |
| `icon` | String | `''` | Icon (emoji or SVG) |
| `color` | String | `'primary'` | primary, success, warning, danger, info |
| `progress` | Number | — | Progress bar value (0-100) |
| `progressLabel` | String | `''` | Progress bar label |
| `clickable` | Boolean | `false` | Enable click behavior |

| Event | Payload | Description |
|-------|---------|-------------|
| `click` | — | Emitted when card is clicked |

| Slot | Description |
|------|-------------|
| `value` | Custom formatted value |
| `subtext` | Custom subtext |
| `icon` | Custom icon rendering |
| `sparkline` | Sparkline/chart area |
| `action` | Right-side action |

---

### SkWidgetCard

Dashboard widget container.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | String | *required* | Widget title |
| `icon` | String | `''` | Icon HTML |
| `variant` | String | `'default'` | Card variant |
| `loading` | Boolean | `false` | Show loading overlay |
| `collapsible` | Boolean | `false` | Enable collapse/expand |
| `collapsed` | Boolean | `false` | Initially collapsed |
| `clickable` | Boolean | `false` | Enable click |
| `extraClass` | String | `''` | Additional CSS classes |

| Event | Payload | Description |
|-------|---------|-------------|
| `click` | — | Emitted on card click |

| Slot | Description |
|------|-------------|
| `default` | Widget content |
| `action` | Header action buttons |

---

### SkDataTable

Full-featured enterprise data table.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `columns` | Array | *required* | Column definitions `[{ key, label, sortable, align, width, format, bold }]` |
| `rows` | Array | `[]` | Data rows |
| `rowKey` | String | `'id'` | Unique key field |
| `searchable` | Boolean | `false` | Show search bar |
| `searchPlaceholder` | String | `'Cari...'` | Search placeholder |
| `selectable` | Boolean | `false` | Enable row selection |
| `exportable` | Boolean | `false` | Show export button |
| `resizable` | Boolean | `false` | Enable column resize |
| `sortable` | Boolean | `true` | Enable column sorting |
| `striped` | Boolean | `false` | Alternating row colors |
| `hoverable` | Boolean | `true` | Row hover effect |
| `compact` | Boolean | `false` | Compact row height |
| `loading` | Boolean | `false` | Loading state |
| `skeletonCount` | Number | `5` | Skeleton row count |
| `showToolbar` | Boolean | `true` | Show toolbar |
| `showColumnToggle` | Boolean | `false` | Show column visibility toggle |
| `showPagination` | Boolean | `true` | Show pagination |
| `pageSize` | Number | `10` | Rows per page |
| `pageSizeOptions` | Array | `[10,25,50,100]` | Page size options |
| `emptyTitle` | String | `'Belum ada data'` | Empty state title |
| `emptyDescription` | String | — | Empty state description |

| Event | Payload | Description |
|-------|---------|-------------|
| `row-click` | `row` | Row clicked |
| `export` | — | Export button clicked |
| `update:pageSize` | `size` | Page size changed |
| `update:selected` | `ids[]` | Selection changed |
| `update:sort` | `{key, dir}` | Sort changed |

| Slot | Description |
|------|-------------|
| `cell-{key}` | Custom cell render for column `key` |
| `bulk-actions` | Bulk action buttons (receives `selected` prop) |

**Column Definition:**
```js
columns: [
  { key: 'id', label: 'ID', width: '80px', sortable: true },
  { key: 'name', label: 'Nama', bold: true },
  { key: 'total', label: 'Total', format: 'currency', align: 'right' },
  { key: 'date', label: 'Tanggal', format: 'datetime', align: 'center' },
]
```

---

### SkFloatingInput

Input dengan animasi floating label.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | String/Number | `''` | v-model value |
| `label` | String | *required* | Input label |
| `type` | String | `'text'` | Input type |
| `disabled` | Boolean | `false` | Disabled |
| `readonly` | Boolean | `false` | Readonly |
| `required` | Boolean | `false` | Show red asterisk |
| `error` | String | `''` | Error message |
| `helper` | String | `''` | Helper text |
| `size` | String | `'md'` | sm, md, lg |
| `wrapperClass` | String | `''` | Wrapper CSS class |

| Event | Payload | Description |
|-------|---------|-------------|
| `update:modelValue` | `value` | Value changed |

---

### SkAutocomplete

Input dengan dropdown suggestion.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | any | `''` | Selected value |
| `options` | Array | `[]` | Source options |
| `optionLabel` | String | `'label'` | Label field |
| `optionKey` | String | `'id'` | Key field |
| `placeholder` | String | `'Cari...'` | Placeholder |
| `disabled` | Boolean | `false` | Disabled |
| `clearable` | Boolean | `true` | Show clear button |
| `loading` | Boolean | `false` | Loading state |
| `error` | String | `''` | Error message |
| `searchFn` | Function | `null` | Async search function |
| `maxHeight` | String | `'240px'` | Dropdown max height |

| Event | Payload | Description |
|-------|---------|-------------|
| `update:modelValue` | `value` | Value changed |
| `select` | `option` | Option selected |

---

### SkSwitch

Toggle switch component.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | Boolean | `false` | Toggle state |
| `label` | String | `''` | Switch label |
| `description` | String | `''` | Description text |
| `disabled` | Boolean | `false` | Disabled |
| `size` | String | `'md'` | sm, md, lg |
| `color` | String | `'primary'` | primary, success, danger, warning |
| `showCheck` | Boolean | `false` | Show check/cross icon |
| `wrapperClass` | String | `''` | Wrapper CSS class |

---

### SkCurrencyInput

Input mata uang Rupiah.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | Number/String | `0` | Numeric value |
| `prefix` | String | `'Rp'` | Currency prefix |
| `placeholder` | String | `'0'` | Placeholder |
| `disabled` | Boolean | `false` | Disabled |
| `error` | String | `''` | Error message |
| `helper` | String | `''` | Helper text |

---

### SkFileUpload

Drag and drop file upload.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `modelValue` | File/Array | `[]` | Selected file(s) |
| `label` | String | `'Seret & lepas...'` | Upload label |
| `hint` | String | `'atau klik...'` | Upload hint |
| `accept` | String | `''` | Accepted file types |
| `multiple` | Boolean | `true` | Allow multiple files |
| `maxSize` | Number | `10` | Max size in MB |
| `uploading` | Boolean | `false` | Upload in progress |
| `uploadLabel` | String | `'Mengunggah...'` | Uploading text |
| `clearable` | Boolean | `true` | Show clear button |
| `error` | String | `''` | Error message |
| `preview` | Boolean | `false` | Show image preview |

---

### SkDrawer

Slide panel dari kiri/kanan/bawah.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `open` | Boolean | `false` | Show/hide (pakai `v-model:open`) |
| `position` | String | `'right'` | left, right, bottom |
| `title` | String | `''` | Drawer title |
| `subtitle` | String | `''` | Drawer subtitle |
| `icon` | String | `''` | Header icon |
| `width` | String | `'448px'` | Panel width |
| `fullscreen` | Boolean | `false` | Full screen mode |
| `hideHeader` | Boolean | `false` | Hide header |
| `closeOnBackdrop` | Boolean | `true` | Close on backdrop click |
| `closeOnEscape` | Boolean | `true` | Close on Escape key |

| Event | Payload | Description |
|-------|---------|-------------|
| `close` | — | Drawer closed |

| Slot | Description |
|------|-------------|
| `default` | Drawer content |
| `footer` | Bottom footer |

---

### SkModal

Modal dialog dengan berbagai variant.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `open` | Boolean | `false` | Show/hide (pakai `v-model:open`) |
| `title` | String | `''` | Modal title |
| `subtitle` | String | `''` | Modal subtitle |
| `variant` | String | `'default'` | default, danger, wizard |
| `size` | String | `'md'` | sm, md, lg, xl, fullscreen |
| `hideHeader` | Boolean | `false` | Hide header |
| `hideClose` | Boolean | `false` | Hide close button |
| `scrollable` | Boolean | `false` | Scrollable content |
| `closeOnBackdrop` | Boolean | `true` | Close on backdrop click |
| `closeOnEscape` | Boolean | `true` | Close on Escape key |

| Event | Payload | Description |
|-------|---------|-------------|
| `close` | — | Modal closed |

| Slot | Description |
|------|-------------|
| `default` | Modal content |
| `footer` | Action buttons |

---

### SkLoading

Various loading indicators.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `variant` | String | `'inline'` | spinner, skeleton, overlay, progress, inline |
| `size` | String | `'md'` | sm, md, lg (spinner only) |
| `text` | String | `''` | Loading text |
| `type` | String | `'text'` | text, card, table, stat, circle (skeleton) |
| `count` | Number | `3` | Skeleton item count |
| `percent` | Number | `0` | Progress percentage |
| `label` | String | `''` | Progress label |
| `showLabel` | Boolean | `true` | Show progress label |
| `color` | String | `'var(--primary)'` | Progress bar color |
| `animated` | Boolean | `false` | Pulse animation on progress |

---

### SkEmptyState

Various empty/error states.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `variant` | String | `'empty'` | empty, search, error, offline, lock |
| `title` | String | auto | State title |
| `description` | String | auto | State description |
| `actionLabel` | String | `''` | Action button text |
| `actionUrl` | String | `''` | Action link URL |
| `extraClass` | String | `''` | Additional CSS classes |

| Event | Payload | Description |
|-------|---------|-------------|
| `action` | — | Action button clicked |

| Slot | Description |
|------|-------------|
| `default` | Additional content |
| `action` | Custom action button |

---

### SkBreadcrumb

Breadcrumb navigation.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `items` | Array | *required* | `[{ label, url? }]` |
| `homeIcon` | Boolean | `true` | Show home icon |
| `extraClass` | String | `''` | Additional CSS classes |

---

### SkFavorites

Sidebar favorites list.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `items` | Array | `[]` | `[{ key, label, icon? }]` |
| `active` | String | `''` | Active item key |
| `removable` | Boolean | `false` | Show remove button |
| `extraClass` | String | `''` | Additional CSS classes |

| Event | Payload | Description |
|-------|---------|-------------|
| `select` | `item` | Item selected |
| `remove` | `item` | Remove button clicked |

---

### SkWidgetGrid

Dashboard widget grid layout.

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `widgets` | Array | `[]` | Widget items |
| `cols` | Number | `4` | Columns (desktop) |
| `colsLaptop` | Number | `3` | Columns (laptop) |
| `colsTablet` | Number | `2` | Columns (tablet) |
| `colsMobile` | Number | `1` | Columns (mobile) |
| `gap` | String | `'1.5rem'` | Grid gap |
| `extraClass` | String | `''` | Additional CSS classes |

| Slot | Description |
|------|-------------|
| `widget-{id}` | Custom widget render per widget |

---

## 🔧 Composables

### useShortcut

```js
import { useShortcut } from '@/Enterprise'

// Ctrl+K → open global search
useShortcut('k', openSearch, { ctrl: true })

// Escape → close
useShortcut('Escape', closeAll)

// Ctrl+S → save
useShortcut('s', save, { ctrl: true, preventDefault: true })
```

### useBreakpoint

```js
import { useBreakpoint } from '@/Enterprise'

const { width, breakpoint, isMobile, isTablet, isDesktop, isTouch } = useBreakpoint()
```

### useTheme

```js
import { useTheme } from '@/Enterprise'

const { theme, isDark, setTheme, toggle } = useTheme()
toggle() // Switch dark/light
```

---

*ServiceKU UI Component Guide — Sprint 8.0A*
