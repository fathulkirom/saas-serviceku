# ServiceKU — Visual Language

> Gaya visual resmi ServiceKU. **Bukan meniru** Linear/Stripe/Vercel/GitHub/Tailwind UI — dokumen ini menjelaskan **elemen yang menjadi inspirasi** dan bagaimana ServiceKU menyesuaikannya dengan domain & identitasnya.
> Semua nilai warna/tipografi/spacing mengikuti `docs/Theme.md`, `docs/Color.md`, `docs/Typography.md`, `docs/Spacing.md`, `docs/Animation.md`.

---

## 1. Pernyataan Gaya Visual

> **"Bersih, tenang, dan tegas — seperti dashboard keuangan bengkel modern."**

ServiceKU tampil sebagai **SaaS utilitarian yang rapi**: latar netral (slate/zinc), kartu putih (atau gelap di dark mode), aksen biru profesional (`--primary`), status berwarna semantik, dan banyak whitespace. Tidak ada gradien ramai, bayangan berat, atau ornamen berlebihan.

---

## 2. Elemen Inspirasi (bukan tiruan)

### Linear — fokus & micro-interaction
**Inspirasi:** hierarki fokus yang sangat jelas; setiap layar punya satu hal utama; transisi halus; penggunaan ruang untuk memandu mata.
**Penyesuaian ServiceKU:** fokus pada *tabel & alur servis* (bukan aplikasi desain). Transisi lembut (fade/slide pendek, `docs/Animation.md`) tanpa efek berlebihan.

### Stripe — ketenangan & konsistensi
**Inspirasi:** palet tenang, konsistensi komponen, whitespace yang menenangkan, pesan yang jelas.
**Penyesuaian ServiceKU:** palet netral + biru tenang; komponen `K*` konsisten di seluruh produk; ruang antar section (`space-y-5`) membuat layar padat-data tetap terasa lega.

### Vercel — efisiensi visual & tipografi
**Inspirasi:** tipografi rapat & jelas, konten didahulukan, kehadiran visual yang efisien.
**Penyesuaian ServiceKU:** Plus Jakarta Sans untuk keterbacaan; angka/ID dengan `font-mono`; tidak ada elemen dekoratif yang bersaing dengan data.

### GitHub — utilitarian & tabel padat
**Inspirasi:** penyajian informasi padat yang terbaca (tabel, badge status, daftar), ikon tipis yang fungsional.
**Penyesuaian ServiceKU:** tabel dengan header tebal & baris hover; badge status semantik; ikon SVG garis tipis (`stroke-width` 1.5–2) via `Icons.js`.

### Tailwind UI — konsistensi komponen
**Inspirasi:** komponen yang seragam dan dapat dirakit.
**Penyesuaian ServiceKU:** seluruh primitif distandarkan ke `K*` (`docs/Component.md`) — tombol, input, modal, drawer, badge, dsb. punya ukuran/radius/shadow yang sama.

---

## 3. Fondasi Visual

| Aspek | Keputusan | Referensi |
|---|---|---|
| Warna utama | Biru `--primary #2563EB` + `indigo-600` untuk aksi | `docs/Color.md` |
| Netral | Slate/zinc (dark scale) | `docs/Color.md` §6 |
| Latar | `--bg-app #F8FAFC`, kartu `--bg-card #FFFFFF` | `docs/Theme.md` |
| Kartu | `rounded-xl`/`rounded-2xl`, border tipis, `shadow-sm` | `docs/Theme.md` §3 |
| Radius | `--radius-md 0.75rem` (input/btn), `--radius-lg 1rem` (kartu) | `docs/Theme.md` |
| Shadow | `soft`, `soft-lg`, `premium` (Tailwind) + `--shadow-*` | `docs/Theme.md` |
| Tipografi | Plus Jakarta Sans (body), `font-mono` (angka/ID) | `docs/Typography.md` |
| Spacing | Skala 1–16; section `space-y-5`; kartu `p-5` | `docs/Spacing.md` |
| Ikon | SVG garis tipis (inline, `Icons.js`) | `docs/Frontend.md` §1 |
| Dark mode | `.dark` via `ThemeSwitcher` (localStorage) | `docs/Theme.md` §4 |

---

## 4. Komponen Visual (pola)

### Kartu / Section
```
Kartu konten: rounded-xl/2xl, border var(--border-color), background var(--bg-card),
              padding p-5, shadow-sm (soft).
Header section: judul text-sm font-bold + mb-3/4.
```

### Tombol
```
Primer: var(--primary)/indigo-600, text-white, rounded-lg/xl, font-bold.
Sekunder: border + bg-card, text-secondary.
Danger: var(--danger). Success: var(--success). (KButton variants)
Aksi di detail servis: pill kecil (px-3 py-1.5 rounded-lg text-xs font-bold).
```

### Status / Badge
```
Pill: rounded-full, soft background + text warna semantik (badge-success, badge-warning...).
Status servis: dot + label + soft bg (statusStyle) — docs/Color.md §9.
```

### Tabel
```
Header: text-xs font-bold uppercase, bg tipis, border-b.
Row: hover:bg-hover, divide-y tipis. Angka kanan, teks kiri.
Empty: EmptyState / KTable empty props.
```

### Sidebar & Header
```
Sidebar: gelap (var(--bg-sidebar) #0F172A), icon group beraksen warna, item aktif
        bg-sidebar-active + text-sidebar-active.
Header: bg-header (putih/dark), sticky, backdrop-blur, border-b.
Topbar role pill: primary-soft.
```

---

## 5. Densitas (Information Density)

- **Data padat** untuk daftar/laporan: tabel ketat, row `py-2/3`, teks `text-sm`.
- **Data longgar** untuk input/form penting: kartu `p-5`, field `mb-4`, label `text-xs`.
- Jangan membuat semuanya rapat atau semuanya longgar — ikuti peran (`docs/product/TargetUsers.md`): CS/Kasir butuh cepat, Owner butuh terbaca.

---

## 6. Ikon

- Gunakan ikon SVG garis tipis dari `Components/Icons.js` (`getIcon(id)`), bukan emoji untuk elemen fungsional.
- Emoji diperbolehkan sebagai **indikator emosional ringan** di judul section (mis. "📱 Data Perangkat", "💰 Rincian Biaya") — pola yang sudah ada di source.
- Ukuran: tombol aksi `w-3.5 h-3.5`; umum `w-4 h-4`/`w-5 h-5`; empty state besar `w-12 h-12`.

---

## 7. Aturan Visual

1. **Satu bahasa visual** — selalu token + komponen `K*`; tidak ada sistem paralel.
2. **Kartu & status** konsisten (radius, border, padding, warna).
3. **Jangan meniru** Linear/Stripe/Vercel secara harfiah — ambil prinsip (fokus, tenang, efisien, padat, konsisten), sesuaikan dengan identitas "ERP servis" yang profesional.
4. **Dark mode** harus tetap terlihat bersih (latar `#0F172A`/`#1E293B`, text `#F1F5F9`).
5. Validasi visual: screenshot halaman sebelum & sesudah perubahan harus tetap konsisten dengan gaya ini (`docs/` adalah sumber kebenaran).
