# ServiceKU — Animation

> Standar animasi & transisi. Sumber: keyframes/animations di `tailwind.config.js`, transisi di `themes.css`, dan transition CSS di `app.css`/komponen.

---

## 1. Animation Tailwind (`tailwind.config.js`)

Didefinisikan di `theme.extend.animation` (dipakai sebagai utility `animate-*`):

| Utility | Keyframes | Penggunaan umum |
|---|---|---|
| `animate-fade-in` | `fadeIn` (opacity 0→1, 0.6s) | Kemunculan elemen |
| `animate-slide-up` | `slideUp` (opacity 0 + translateY(20px) → normal, 0.6s) | Hero / section masuk |
| `animate-slide-down` | `slideDown` (translateY(-10px), 0.3s) | Dropdown/panel |
| `animate-scale-in` | `scaleIn` (scale 0.95→1, 0.3s) | Modal search (`GlobalSearch`), panel |
| `animate-spin-slow` | spin 3s linear | Loading circular |
| `animate-pulse-soft` | `pulseSoft` (opacity 1↔0.8, 2s) | Indikator status |

---

## 2. Transisi Durasi (tokens di `themes.css`)

```
--transition-fast: 150ms ease;   --transition-normal: 250ms ease;   --transition-slow: 350ms ease;
```

Utility Tailwind `transition-all`, `transition-colors`, `transition-opacity` banyak dipakai dengan `duration-150`/`duration-200`/`duration-300`.

---

## 3. Page Transition (LayoutNew)

Transisi pergantian halaman di `LayoutNew.vue`:

```css
.page-enter-active, .page-leave-active { transition: opacity 0.15s ease, transform 0.15s ease; }
.page-enter-from { opacity: 0; transform: translateY(6px); }
.page-leave-to   { opacity: 0; transform: translateY(-6px); }
```

Dipakai dengan `<Transition name="page" mode="out-in">` keyed oleh `$page.url`.

---

## 4. Theme Transition

```css
.theme-transition, .theme-transition * {
  transition: background-color var(--transition-normal),
              color var(--transition-normal),
              border-color var(--transition-normal),
              box-shadow var(--transition-normal);
}
```
`ThemeSwitcher` menambahkan class `theme-transition` ke `<html>` sesaat saat toggle dark/light.

---

## 5. Drawer Transition (`Drawer.vue`)

- Fade overlay: `opacity 0.2s ease`.
- Panel slide: `transform 0.3s cubic-bezier(0.16, 1, 0.3, 1)` (masuk), `0.2s ease` (keluar), `translateX(100%)`.

---

## 6. Skeleton / Shimmer

- `app.css`: `.shimmer` (gradient sweep `shimmer 1.5s infinite`).
- `.skeleton` + `@keyframes skeleton-shimmer` (gradient bergeser `1.8s ease-in-out infinite`).
- Komponen `Skeleton.vue` dengan props `type` (mis. `table`) & `count`.

---

## 7. Hover / Micro-interaction

- `.hover-lift` (app.css): `translateY(-2px)` + shadow pada hover.
- `.card--hover`/`.card--interactive`: shadow + `translateY(-1px)`.
- Tombol `KButton` varian action: `hover:shadow-sm`; `transition-all`.
- Sidebar item: `transition-all duration-150`; aktif: background `--bg-sidebar-active`.
- Kartu interaktif (mis. foto, baris tabel): `group-hover:*` (contoh overlay foto `group-hover:bg-black/10`).

---

## 8. Shadow Tokens (untuk elevasi)

```
--shadow-xs..xl, --shadow-card, --shadow-glow (lihat docs/Theme.md §3)
```

---

## 9. Aturan

1. Gunakan animasi/transisi yang sudah ada; jangan menambah keyframe baru tanpa kebutuhan.
2. Hormati `prefers-reduced-motion` bila menambah animasi besar (disarankan).
3. Jangan menganimasikan layout secara berlebihan; animasi utama = fade/slide pendek (< 0.4s).
4. Transisi state (hover/focus/disabled) pakai `transition-all`/`transition-colors` + durasi singkat.
5. Halaman baru masuk via page transition bawaan LayoutNew — jangan buat mekanisme sendiri.
