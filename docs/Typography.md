# ServiceKU — Typography

> Standar tipografi. Sumber: `tailwind.config.js` (font family) + token tipografi di `themes.css` + pola nyata di halaman.

---

## 1. Font Family

| Penggunaan | Font | Definisi |
|---|---|---|
| Sans (default body) | **Plus Jakarta Sans** | `tailwind.config.js`: `fontFamily.sans = ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans]`; di-import via Google Fonts di `app.css` |
| Token `--font-sans` | Inter | Token di `themes.css` (referensi; body utama memakai Plus Jakarta Sans) |
| Mono | JetBrains Mono / Fira Code | Token `--font-mono`; di markup pakai `font-mono` (mis. kode tiket `#SR1001`, ID transaksi) |

Cara memakai: `class="font-sans"` (default) / `font-mono` untuk angka/kode teknis.

---

## 2. Ukuran (Sizes)

### Token (`themes.css`)
```
--text-xs: 0.6875rem;   --text-sm: 0.8125rem;   --text-base: 0.875rem;
--text-lg: 1rem;        --text-xl: 1.125rem;    --text-2xl: 1.5rem;
--text-3xl: 1.875rem;   --text-4xl: 2.25rem;
```

### Utility Tailwind (pola nyata di halaman)
| Kelas | Pemakaian umum |
|---|---|
| `text-xs` (0.75rem) | Label kecil, badge, tombol aksi, metadata tabel, clock |
| `text-sm` (0.875rem) | Body, tombol form, teks kartu |
| `text-base` (1rem) | Paragraf/body besar, total |
| `text-lg` / `text-xl` | Judul section (`text-xl font-bold` di header halaman) |
| `text-2xl`+ | Hero/landing, angka statistik besar |

> Catatan: token `--text-*` lebih kecil dari utility Tailwind (contoh `--text-sm: 0.8125rem`). Utility `text-sm` (0.875rem) lebih sering dipakai. Pertahankan pilihan yang ada per konteks.

---

## 3. Berat (Weights)

```
--font-normal: 400; --font-medium: 500; --font-semibold: 600; --font-bold: 700;
```

Pola nyata:
- Judul halaman/section: `font-bold` (teks gelap `text-zinc-900` / `var(--text-primary)`).
- Label form: `font-semibold` (`text-xs`/`text-sm`).
- Tombol: `font-bold` (aksi) / `font-semibold` (footer modal secondary).
- Metadata: `font-medium` / `font-normal`.
- Angka penting (total, ID): `font-bold` + `font-mono` untuk ID.

---

## 4. Line Height (Leading)

```
--leading-tight: 1.25; --leading-normal: 1.5; --leading-relaxed: 1.625;
```

Tailwind `leading-*` dipakai sesuai kebutuhan; teks panjang (deskripsi masalah) memakai `whitespace-pre-wrap`.

---

## 5. Pola Heading (Konvensi)

| Level | Pola kelas |
|---|---|
| Header halaman (slot `#header`) | `text-xl font-bold text-zinc-900` + `text-xs text-zinc-500` subjudul |
| Judul section/kartu | `text-sm font-bold mb-4 text-zinc-900` (atau `mb-3`) |
| Judul modal | `text-base font-bold mb-4 text-zinc-900` |
| Label form | `block text-xs font-semibold mb-1 text-zinc-500` |

---

## 6. Warna Teks

- Teks utama: `text-zinc-900` atau `var(--text-primary)` / `text-dark-900`.
- Teks sekunder: `text-zinc-500` / `text-zinc-600` atau `var(--text-secondary)`.
- Muted: `text-zinc-400` / `text-muted` / `var(--text-muted)`.
- Aksen/link: `text-indigo-600` atau `var(--primary)`.
- Di sidebar: `var(--text-sidebar)` (normal) / `var(--text-sidebar-active)` (aktif).

---

## 7. Angka & Format (id-ID)

- Mata uang: `formatNumber`/`formatCurrency` dari `useFormatter.js` → `Rp 1.234.567`.
- Tanggal: `formatDate` (default bulan pendek) dari `useFormatter.js`; modul Services memakai `formatDate` bulan panjang (`useServiceStatus.js`).
- Inisial nama: `getInitials(name)` → 2 huruf pertama.
- ID kode teknis (tiket/nota): `font-mono text-xs font-bold`.

---

## 8. Aturan

1. Body default `font-sans` (Plus Jakarta Sans) — jangan ganti.
2. Gunakan `font-mono` untuk angka/ID teknis.
3. Konsisten: judul `font-bold`, label form `font-semibold`, body `font-normal`.
4. Jangan memuat font baru tanpa kebutuhan jelas; tambah ke `tailwind.config.js` + import CSS.
5. Perhatikan kontras warna teks (dark mode) — pakai token `--text-*`.
