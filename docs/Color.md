# ServiceKU — Color

> Palet warna resmi. Sumber utama: CSS variables di `resources/css/themes.css` + scale Tailwind di `tailwind.config.js`. Gunakan token semantik — jangan hardcode warna bila token tersedia.

---

## 1. Primary (Brand — Blue)

| Token | Value | Penggunaan |
|---|---|---|
| `--primary` | `#2563EB` | Aksi utama, link aktif, aksen |
| `--primary-hover` | `#1D4ED8` | Hover primary |
| `--primary-soft` | `#DBEAFE` | Latar lembut primary (badge, pill) |
| `--primary-soft-border` | `#BFDBFE` | Border lembut primary |

> Branding tenant bisa meng-override `--primary*` saat runtime (lihat `docs/Theme.md` §5).

---

## 2. Semantic (Status)

| Makna | Solid | Hover | Soft bg | Soft border | Text |
|---|---|---|---|---|---|
| Success | `--success #22C55E` | `#16A34A` | `#DCFCE7` | `#BBF7D0` | `#15803D` |
| Warning | `--warning #F59E0B` | `#D97706` | `#FEF3C7` | `#FDE68A` | `#92400E` |
| Danger | `--danger #EF4444` | `#DC2626` | `#FEE2E2` | `#FECACA` | `#B91C1C` |
| Info | `--info #0EA5E9` | `#0284C7` | `#E0F2FE` | `#BAE6FD` | `#0369A1` |

Pola pemakaian: solid untuk tombol/indikator aktif; `-soft`/`-soft-border`/`-text` untuk badge/pill status.

---

## 3. Background

| Token | Light | Dark |
|---|---|---|
| `--bg-app` | `#F8FAFC` | `#0F172A` |
| `--bg-card` | `#FFFFFF` | `#1E293B` |
| `--bg-hover` | `#F1F5F9` | `rgba(255,255,255,0.05)` |
| `--bg-input` | `#FFFFFF` | `#1E293B` |
| `--bg-sidebar` | `#0F172A` | `#0F172A` |
| `--bg-sidebar-hover` | `rgba(255,255,255,0.06)` | — |
| `--bg-sidebar-active` | `rgba(37,99,235,0.15)` | — |
| `--bg-header` | `#FFFFFF` | `#0F172A` |
| `--bg-surface` / `--bg-elevated` | `#FFFFFF` | `#1E293B` |
| `--bg-surface-hover` | `#F8FAFC` | `#334155` |
| `--bg-tertiary` | `#F1F5F9` | `#1E293B` |

---

## 4. Text

| Token | Light | Dark |
|---|---|---|
| `--text-primary` | `#0F172A` | `#F1F5F9` |
| `--text-secondary` | `#475569` | `#94A3B8` |
| `--text-muted` | `#94A3B8` | `#64748B` |
| `--text-inverse` | `#FFFFFF` | — |
| `--text-sidebar` | `#94A3B8` | `#94A3B8` |
| `--text-sidebar-active` | `#FFFFFF` | — |

---

## 5. Border

| Token | Light | Dark |
|---|---|---|
| `--border-color` | `#E5E7EB` | `#334155` |
| `--border-light` | `#F1F5F9` | `#1E293B` |
| `--border-focus` | `rgba(37,99,235,0.4)` | — |

---

## 6. Scale Tailwind (tailwind.config.js)

Tailwind menambahkan scale khusus selain default:

- **`dark`** (50–950): zinc/slate-ish untuk konten dark (contoh `text-dark-900` ≈ `#0F172A`).
- **`success`**: `50 #dcfce7`, `100 #bbf7d0`, `500 #22c55e`, `600 #16a34a`, `700 #15803d`.
- **`warning`**: `50 #fef3c7`, `100 #fde68a`, `500 #f59e0b`, `600 #d97706`, `700 #92400e`.
- Font: `Plus Jakarta Sans` (default sans), lihat `docs/Typography.md`.
- Shadow kustom: `soft`, `soft-lg`, `premium`, `inner-soft` (lihat `docs/Theme.md`).

---

## 7. Warna Aksen Grup Menu (Sidebar) — `layoutHelpers.js`

`groupColors` dipakai sidebar/topbar:

| Grup | Accent (hex) | light |
|---|---|---|
| Utama / Operasional | `#7c3aed` (violet) | `var(--primary-soft)` |
| Transaksi / Keuangan | `#10b981` (emerald) | `rgba(16,185,129,0.12)` |
| Manajemen / Sistem & Laporan | `#3b82f6` (blue) | `rgba(59,130,246,0.12)` |

---

## 8. Indigo (indigo-600 `#4f46e5`) — dipakai luas

Banyak aksi memakai `bg-indigo-600 text-white` / `text-indigo-600` (tombol, link, avatar). Ini bagian dari scale default Tailwind. **Catatan**: `#2563eb` (`--primary`) dan `#4f46e5` (indigo-600) berbeda — pertahankan keduanya sesuai penggunaannya saat ini (mis. tombol "Checklist Keluar" memakai `#2563eb`).

---

## 9. Warna Status Servis (modul Services) — `useServiceStatus.js`

- `statusDot(status)` & `statusStyle(status)` memetakan status servis ke warna token/soft:
  - `menunggu_alokasi` → warning (`rgba(243,156,18,0.12)` / `#b87c0e`)
  - `diterima`/`dikerjakan` → info
  - `menunggu_konfirmasi_*` → danger
  - `indent`/`onpartner` → primary
  - `selesai`/`diambil` → success
  - `cancel`/`void` → danger
  - `close` → muted

---

## 10. Aturan

1. Gunakan token semantik (`--success` dll.) untuk status, bukan warna ad-hoc.
2. Gunakan `--primary` untuk brand; `indigo-600` tetap boleh untuk aksi indigo yang sudah ada.
3. Dark mode: pastikan kontras terjaga (teks terang di bg gelap).
4. Untuk warna yang tidak punya token (mis. `#2563eb` spesifik), pertahankan nilai literal yang sudah dipakai.
