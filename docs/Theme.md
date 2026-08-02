# ServiceKU — Theme

> Sistem tema berbasis **CSS Variables (design tokens)**. Definisi di `resources/css/themes.css` (di-import oleh `resources/css/app.css`). Semua komponen wajib memakai token ini — jangan hardcode warna/spacing/radius bila token tersedia.

---

## 1. Di mana Token Didefinisikan

- `resources/css/themes.css` — semua token `:root` + override dark mode (`.dark`) + utility alias.
- `resources/css/app.css` — `@tailwind base/components/utilities` + kelas komponen (`.card`, `.btn-*`, `.input`, `.badge-*`, `.table-wrap`, `.modal-overlay`, `.shimmer`, `.glass`, dsb.).

Urutan import di `app.css`:
```css
@import url('...Plus Jakarta Sans...');
@import './themes.css';
@tailwind base;
@tailwind components;
@tailwind utilities;
```

---

## 2. Cara Memakai Token

- **Inline style / binding**: `:style="{ background: 'var(--bg-card)' }"` atau `style="border-color: var(--border-color);"`.
- **Tailwind arbitrary**: `class="bg-[var(--bg-card)]"`, `text-[var(--text-primary)]` — dipakai bila utility belum ada.
- **Kelas CSS komponen** (app.css): `.card`, `.btn-primary`, `.input`, `.badge-success`, dsb. — memakai token di dalamnya.
- **Tailwind config** memetakan scale khusus (`dark`, `success`, `warning`) — lihat `docs/Color.md`.

> Tailwind **tidak** punya mapping `primary` → token secara langsung; warna primer dipakai lewat `var(--primary)` atau kelas `.btn-primary`.

---

## 3. Token Utama

### Primary
```
--primary: #2563EB;          --primary-hover: #1D4ED8;
--primary-soft: #DBEAFE;     --primary-soft-border: #BFDBFE;
```

### Background
```
--bg-app, --bg-card, --bg-hover, --bg-input,
--bg-sidebar, --bg-sidebar-hover, --bg-sidebar-active,
--bg-header, --bg-elevated, --bg-surface, --bg-surface-hover, --bg-tertiary
```

### Text
```
--text-primary, --text-secondary, --text-muted, --text-inverse,
--text-sidebar, --text-sidebar-active
```

### Border
```
--border-color, --border-light, --border-focus
```

### Semantic (dipakai untuk status sukses/warning/danger/info)
```
--success(-hover/-soft/-soft-border/-text)
--warning(...)
--danger(...)
--info(...)
```

### Shadow
```
--shadow-xs, --shadow-sm, --shadow-md, --shadow-lg, --shadow-xl,
--shadow-card, --shadow-glow
```

### Radius
```
--radius-sm: 0.5rem; --radius-md: 0.75rem; --radius-lg: 1rem; --radius-xl: 1.25rem;
```

### Transisi
```
--transition-fast: 150ms ease; --transition-normal: 250ms ease; --transition-slow: 350ms ease;
```

### Layout
```
--layout-header-height: 56px; --layout-sidebar-width: 240px;
--layout-mobile-header: 56px; --layout-content-max-width: 1440px;
```

### Tipografi & Spacing — lihat `docs/Typography.md` & `docs/Spacing.md`.

### Komponen sizing token
```
--btn-height-{sm,md,lg}, --btn-padding-x-{sm,md,lg},
--card-padding-{sm,md,lg}, --input-height-{sm,md,lg}, --input-padding-x,
--table-cell-padding-{x,y}, --table-header-height,
--sidebar-width, --sidebar-collapsed, --modal-max-width,
--dropdown-min-width, --dropdown-max-height, --toast-max-width, --toast-gap
```

---

## 4. Dark Mode

- Aktif saat elemen `<html>` punya class `.dark` (`document.documentElement.classList.toggle('dark', ...)`).
- Override di `.dark` di `themes.css`: background (app/card/hover/input/sidebar/header/surface/tertiary), text (primary/secondary/muted/sidebar), border, shadow.
- `ThemeSwitcher.vue` mengelola toggle + menyimpan `localStorage['theme']` (`dark`/`light`), fallback `prefers-color-scheme`.
- **Dark mode bersifat client-side** (tidak sinkron lintas device) — berbeda dengan `ui_preferences` yang disimpan ke server.

---

## 5. Branding Per-Tenant (Primary Color)

- `HandleInertiaRequests` meng-share `tenant.primary_color` (dari `TenantSetting`, default `#4F46E5`, cache 5 menit).
- `AuthenticatedLayout` menerapkan ke CSS vars pada mount:
  - `--primary` ← warna tenant
  - `--primary-hover` ← `darkenHex(color, 12)`
  - `--primary-soft` ← `rgba(color, 0.1)`
  - `--primary-soft-border` ← `rgba(color, 0.2)`
- Utility `hexToRgba`/`darkenHex` ada di `AuthenticatedLayout.vue`.

---

## 6. Utility Alias (themes.css)

```css
.border-dark-100 { border-color: var(--border-light) !important; }
.border-dark-200 { border-color: var(--border-color) !important; }
.shadow-sm/.shadow-md/.shadow-lg → var(--shadow-*);
.hover\:bg-dark-50:hover → var(--bg-hover);
.hover\:text-dark-700:hover → var(--text-primary);
.theme-transition, .theme-transition * → transition bg/color/border/shadow var(--transition-normal);
```

> Kelas `text-dark-500..900`, `border-dark-100/200`, `bg-premium-*`, `.btn-premium-*`, `.input-premium`, `.badge--*`, `.card--*` juga ada sebagai kompatibilitas (legacy) — tetap berfungsi, tetapi **standar baru memakai token/varian `K*`**.

---

## 7. Kelas Komponen di `app.css`

- `.card` / `.card-glass` / `.card--hover` / `.card--interactive`
- `.btn`, `.btn-primary`, `.btn-secondary`, `.btn-danger`, `.btn-success`, `.btn-sm`, `.btn-xs`, `.btn-ghost`
- `.input`, `.input-error`, `.input-premium`
- `.badge`, `.badge-{success,warning,danger,info}`, `.badge--*`, `.badge--dot`
- `.table-wrap` (thead/tbody styling), `.table`
- `.stat-card`, `.stat-icon`, `.stat-value`, `.stat-label`
- `.section`, `.section-title`
- `.modal-overlay`, `.modal-content`
- `.shimmer`, `.skeleton` (skeleton-shimmer)
- `.glass`, `.gradient-text`, `.gradient-border`, `.hover-lift`, `.ring-accent`
- `.page-header`

---

## 8. Aturan

1. Selalu pakai token (`var(--*)`) untuk warna/radius/shadow/spacing yang punya token.
2. Untuk komponen interaktif, pakai varian `K*` (memakai token di dalamnya).
3. Jangan mendefinisikan warna ad-hoc bila sudah ada token semantik (`--success`, `--danger`, dst.).
4. Dark mode harus tetap berfungsi — jangan hardcode warna terang di dalam komponen tanpa token.
