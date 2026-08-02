# ServiceKU — Spacing

> Standar spacing/geometri. Sumber: token spacing di `themes.css` + utility Tailwind + pola nyata di halaman & komponen `K*`.

---

## 1. Token Spacing (`themes.css`)

```
--space-1: 0.25rem;  --space-2: 0.5rem;   --space-3: 0.75rem;  --space-4: 1rem;
--space-5: 1.25rem;  --space-6: 1.5rem;   --space-8: 2rem;     --space-10: 2.5rem;
--space-12: 3rem;    --space-16: 4rem;
```

> Utama: pakai utility Tailwind (`p-4`, `gap-3`, `space-y-5`, dsb.) yang nilainya sama dengan token. Token dipakai langsung di beberapa CSS komponen.

---

## 2. Pola Spacing yang Konsisten

### Gap (jarak antar elemen sejajar)
| Konteks | Gap |
|---|---|
| Tombol aksi dalam satu baris | `gap-2` (0.5rem) |
| Icon + teks dalam tombol | `gap-1.5` |
| Header halaman | `gap-3` |
| Konten dalam kartu | `space-y-3`/`space-y-4` |

### Section (jarak antar blok besar)
- Konten halaman tenant: wrapper `max-w-7xl mx-auto py-5 sm:py-6 px-3 sm:px-6 lg:px-8`.
- Antara section dalam halaman: `space-y-5` pada container (`<div class="space-y-5">`).
- Halaman detail servis: `max-w-5xl mx-auto space-y-5`.

### Kartu
- Padding kartu standar: `p-5` (1.25rem).
- `KCard`: prop `padding` → `none`/`sm` (p-4)/`md` (p-6)/`lg` (p-8).
- Header section di kartu: `mb-4` (atau `mb-3`) setelah judul.

### Tombol
- Aksi (action bar): `px-3 py-1.5 rounded-lg text-xs` (default `KButton` action).
- Upload: `px-4 py-2 rounded-lg text-xs` (`KButton size="md"`).
- Footer modal: `px-4 py-2 rounded-xl text-sm` (`modal-*` variant, `flex-1`).
- `.btn` app.css: `padding: 0.5rem 1.25rem` (`--space-2`/`--btn-padding-x-md`).

### Input
- Standar `KInput`: `px-3 py-2 text-sm` (md) → `rounded-xl border w-full`.
- `sm`: `px-2 py-1.5 text-xs`; `lg`: `px-3 py-2.5 text-sm`.
- `.input` app.css: `height: var(--input-height-md)` (2.5rem), `padding: 0 var(--input-padding-x)`.
- Label form: `mb-1`/`mb-1.5`; error text: `mt-1.5`.

### Modal / Drawer / Toast
- Panel modal: `p-5 w-full mx-3` (+ `max-w-sm`/`max-w-lg`).
- Footer modal: `mt-5` + `flex gap-2`.
- Drawer: width default `448px` (`--sidebar-width` keluarga), padding konten `px-5 py-4`.
- Toast: `top-4 right-4`, `max-width: var(--toast-max-width)` (24rem).

---

## 3. Layout Metrics

```
--layout-header-height: 56px;   --layout-sidebar-width: 240px;
--layout-content-max-width: 1440px;  --sidebar-width: 16rem; --sidebar-collapsed: 4rem;
```

- Header: `h-14 lg:h-16`; konten header `px-3 sm:px-6 lg:px-8`.
- Sidebar modern `w-60`, pro `w-64`, elegant `w-72`, slim `w-64`/`w-[64px]`.
- `mainContentStyle` menggeser konten sesuai lebar sidebar di desktop.

---

## 4. Responsive (Breakpoints)

- Gunakan prefix Tailwind: `sm:`, `md:`, `lg:`, `xl:`.
- Grid kartu: `grid-cols-1 md:grid-cols-2` (dua kolom di md+).
- Foto: `grid-cols-4 gap-3`.
- Padding halaman: `px-3 sm:px-6 lg:px-8`.
- Header: mobile `h-14`, desktop `h-16`.

---

## 5. Aturan

1. Pakai skala spacing yang ada (1/1.5/2/2.5/3/4/5/6/8/10/12/16) — jangan nilai acak.
2. Jarak antar section: `space-y-5` pada container (bukan `mt-5` per elemen), agar aman saat elemen kondisional disembunyikan.
3. Padding/radius tombol & input: ikuti varian `K*` (jangan override tanpa alasan).
4. Ukuran ikon dalam tombol aksi: `w-3.5 h-3.5`; ikon umum: `w-4 h-4`/`w-5 h-5`.
5. Jangan menambah `mt-*` di elemen yang sudah mendapat spacing dari `space-y-*`.
