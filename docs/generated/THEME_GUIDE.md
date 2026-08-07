# ServiceKU Theme Guide

> Panduan styling, theming, dan kustomisasi visual ServiceKU.

---

## 🎨 Design Tokens

ServiceKU menggunakan **CSS Custom Properties** (CSS variables) untuk semua nilai desain. Ada dua lapis:

### Layer 1: Core Theme (`themes.css`)
Variabel yang sudah ada sebelum Sprint 8.0A. Tetap menjadi fondasi.

```css
--primary, --primary-hover, --primary-soft
--success, --warning, --danger, --info
--bg-app, --bg-card, --bg-hover, --bg-input
--text-primary, --text-secondary, --text-muted
--border-color, --border-light
--shadow-sm, --shadow-md, --shadow-lg
--radius-sm, --radius-md, --radius-lg
```

### Layer 2: Enterprise Tokens (`tokens.css`)
Variabel baru dengan prefix `--sk-`. Extended palette + design tokens.

```css
/* Primary Scale (50-950) */
--sk-primary-50 → --sk-primary-950

/* Neutral Scale */
--sk-neutral-0 → --sk-neutral-950

/* Semantic Scales */
--sk-success-50 → --sk-success-900
--sk-warning-50 → --sk-warning-900
--sk-danger-50 → --sk-danger-900
--sk-info-50 → --sk-info-900

/* Typography */
--sk-text-caption → --sk-text-5xl
--sk-font-light → --sk-font-extrabold
--sk-leading-none → --sk-leading-loose

/* Spacing (4px grid) */
--sk-space-1 (4px) → --sk-space-24 (96px)

/* Layout */
--sk-sidebar-width, --sk-header-height, --sk-content-max-width

/* Z-Index */
--sk-z-dropdown → --sk-z-toast

/* Animation */
--sk-duration-75 → --sk-duration-1000
--sk-ease-linear → --sk-ease-bounce
```

---

## 🌓 Dark Mode

### Aktivasi

Dark mode diaktifkan dengan menambahkan class `.dark` ke `<html>`:

```html
<html class="dark">
```

Atau via JavaScript:

```js
// Toggle
document.documentElement.classList.toggle('dark')

// Set specific
document.documentElement.classList.add('dark')
document.documentElement.classList.remove('dark')
```

### Via Composable

```js
import { useTheme } from '@/Enterprise'
const { isDark, toggle, setTheme } = useTheme()

toggle()              // Switch light ↔ dark
setTheme('dark')      // Force dark
setTheme('light')     // Force light
setTheme('system')    // Follow OS preference
```

### Dark Mode CSS

Semua variabel di-override di `.dark`:

```css
.dark {
    --bg-app: #0F172A;
    --bg-card: #1E293B;
    --bg-hover: rgba(255,255,255,0.05);
    --text-primary: #F1F5F9;
    --text-secondary: #94A3B8;
    --border-color: #334155;
    /* ... all overrides ... */
}
```

### Komponen Enterprise

Semua komponen Enterprise otomatis mengikuti tema. Tidak perlu konfigurasi tambahan.

---

## 🖋️ Typography

### Font

**Primary:** Plus Jakarta Sans (Google Fonts)
**Mono:** JetBrains Mono (system fallback)

### Type Scale

| Token | Size | Line Height | Weight |
|-------|------|-------------|--------|
| `--sk-text-caption` | 11px | 1.5 | 500 |
| `--sk-text-xs` | 12px | 1.5 | 400 |
| `--sk-text-sm` | 13px | 1.5 | 400/500 |
| `--sk-text-base` | 14px | 1.625 | 400 |
| `--sk-text-lg` | 16px | 1.5 | 400/600 |
| `--sk-text-xl` | 18px | 1.5 | 600 |
| `--sk-text-2xl` | 24px | 1.375 | 600 |
| `--sk-text-3xl` | 30px | 1.375 | 700 |
| `--sk-text-4xl` | 36px | 1.25 | 700 |
| `--sk-text-5xl` | 48px | 1.25 | 800 |

---

## 📏 Spacing

Grid 4px base. Semua spacing menggunakan tokens, bukan magic numbers.

```css
/* ❌ JANGAN */
<div class="mt-7 mb-[13px] px-[22px]">

/* ✅ GUNAKAN */
<div style="margin-top: var(--sk-space-6); margin-bottom: var(--sk-space-3); padding: 0 var(--sk-space-4);">
```

### Tabel Konversi

| Token | px | Tailwind eq. |
|-------|----|--------------|
| `--sk-space-1` | 4px | `p-1`, `gap-1` |
| `--sk-space-2` | 8px | `p-2`, `gap-2` |
| `--sk-space-3` | 12px | `p-3` |
| `--sk-space-4` | 16px | `p-4`, `gap-4` |
| `--sk-space-6` | 24px | `p-6`, `gap-6` |
| `--sk-space-8` | 32px | `p-8`, `gap-8` |
| `--sk-space-12` | 48px | — |
| `--sk-space-16` | 64px | — |

---

## 🎬 Animations

### Duration Tokens

| Token | Value | Usage |
|-------|-------|-------|
| `--sk-duration-75` | 75ms | Micro-interactions |
| `--sk-duration-150` | 150ms | Button hover, focus |
| `--sk-duration-200` | 200ms | Modal/Drawer exit |
| `--sk-duration-300` | 300ms | Modal/Drawer enter, page transition |
| `--sk-duration-500` | 500ms | Complex animations |

### Easing Curves

| Token | Curve | Feel |
|-------|-------|------|
| `--sk-ease-out` | `cubic-bezier(0,0,0.2,1)` | Smooth deceleration |
| `--sk-ease-in` | `cubic-bezier(0.4,0,1,1)` | Smooth acceleration |
| `--sk-ease-in-out` | `cubic-bezier(0.4,0,0.2,1)` | Symmetric |
| `--sk-ease-spring` | `cubic-bezier(0.16,1,0.3,1)` | Bouncy overshoot |
| `--sk-ease-bounce` | `cubic-bezier(0.68,-0.55,0.265,1.55)` | Strong bounce |

---

## 🏗️ Tenant Branding

ServiceKU mendukung kustomisasi warna per tenant:

```js
// AuthenticatedLayout.vue — applyTenantTheme()
const primaryColor = page.props.tenant?.primary_color || '#2563eb'

function applyTenantTheme(color) {
  const root = document.documentElement
  root.style.setProperty('--primary', color)
  root.style.setProperty('--primary-hover', darkenHex(color, 10))
  root.style.setProperty('--primary-soft', hexToRgba(color, 0.1))
}
```

---

## 📐 Layout Tokens

| Token | Value | Description |
|-------|-------|-------------|
| `--sk-sidebar-width` | `16rem` (256px) | Expanded sidebar |
| `--sk-sidebar-collapsed` | `4rem` (64px) | Collapsed sidebar |
| `--sk-header-height` | `3.5rem` (56px) | Top header |
| `--sk-content-max-width` | `1440px` | Content area |
| `--sk-content-padding` | `2rem` (32px) | Content padding |

---

## 🎯 Best Practices

### 1. Gunakan CSS Variables, bukan hardcode
```css
/* ❌ */
color: #475569;
background: #FFFFFF;

/* ✅ */
color: var(--text-secondary);
background: var(--bg-card);
```

### 2. Gunakan spacing tokens
```css
/* ❌ */
padding: 13px 22px;

/* ✅ */
padding: var(--sk-space-3) var(--sk-space-5);
```

### 3. Gunakan transition tokens
```css
/* ❌ */
transition: all 0.25s ease;

/* ✅ */
transition: all var(--sk-duration-300) var(--sk-ease-out);
```

### 4. Dark mode testing
Selalu test komponen baru di dark mode:
```html
<html class="dark">
```

---

*ServiceKU Theme Guide — Sprint 8.0A*
