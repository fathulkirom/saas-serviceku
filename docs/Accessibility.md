# ServiceKU — Accessibility

> Standar aksesibilitas berdasarkan praktik yang **sudah ada** di source code. Dokumen ini merekam kondisi saat ini dan aturan agar tidak melanggar praktik tersebut. (Bukan klaim kepatuhan penuh WCAG.)

---

## 1. Semantic & Struktur

- Gunakan elemen/semantik yang tepat:
  - `h1`/`h2`/`h3` untuk judul (pola: header halaman `text-xl font-bold`, judul kartu `text-sm font-bold`).
  - `label` untuk input (pola: `<label class="block text-xs font-semibold mb-1 ...">`).
  - `table`/`th`/`td` untuk data tabular (heading `th scope="col"`).
  - `button`/`a` untuk aksi/navigasi (bukan `<div @click>`).
- Semua elemen interaktif kini dibuat lewat komponen `K*` yang merender elemen native (`KButton`→`<button>`, `KInput`→`<input>`, dst.) — semantik tetap terjaga.

---

## 2. Label Form

- Setiap field wajib punya `<label>` (pola `block text-xs font-semibold mb-1`).
- Hubungkan label–input secara visual & semantik:
  - Id yang cocok: `<label for="id">` ↔ `<input id="id">` (contoh: `wa_active`, `branch_active`, `ceklis_active`).
  - Atau bungkus input di dalam `<label>` (contoh checkbox/toggle).
- `placeholder` bersifat pelengkap, bukan pengganti label.

---

## 3. Focus

- Input memakai focus ring:
  - `.input:focus` → `border-color: var(--primary)` + `box-shadow: 0 0 0 3px var(--primary-soft)`.
  - Pola Tailwind: `focus:ring-2 focus:ring-indigo-100 focus:border-indigo-500`.
  - `.ring-accent:focus` → `box-shadow: 0 0 0 3px rgba(37,99,235,0.15)`.
- Tombol `KButton`: `focus:outline-none focus:ring-2 focus:ring-offset-2` (pada varian `.btn`).
- Jangan menghapus outline/focus ring tanpa pengganti yang terlihat.

---

## 4. Toggle / Switch (checkbox & radio)

- Toggle switch memakai checkbox/radio tersembunyi `class="sr-only peer"` + elemen visual `peer-checked:*` (Tailwind peer):
  ```vue
  <KCheckbox v-model="flag" class="sr-only peer" />
  <div class="w-11 h-6 ... peer-checked:bg-indigo-600 ..."></div>
  ```
- Pastikan urutan DOM: input `sr-only peer` tepat sebelum elemen visual di dalam parent yang sama.

---

## 5. Keyboard

| Perilaku | Implementasi |
|---|---|
| Esc menutup Drawer | `@keydown.escape` di `Drawer.vue` |
| Esc menutup Global Search | `@keydown.escape="closeSearch"` di `GlobalSearch.vue` |
| Cmd/Ctrl+K membuka search | `keydown` listener di `LayoutNew.vue` (dicegah `preventDefault`) |
| Navigasi hasil search | panah atas/bawah (`searchIndex++/--`) + Enter (`navigateSelected`) |
| Toggle sidebar | Tombol floating + `@click` |

---

## 6. ARIA & Roles

- `KLoading` → `role="status"`.
- `Toast` container → `pointer-events-none` (dekoratif) + item `aria-live` tidak wajib saat ini (notifikasi via toast; disarankan tambah `role="status"`/`aria-live` bila diubah).
- Dropdown panel → `role="menu"` di `Dropdown.vue`; item `DropdownLink` role menu-item implisit.
- `Modal/Dialog` (`KDialog`) → overlay + panel; **disarankan**: fokus trap & `role="dialog"`/`aria-modal` bila di-refactor (belum diterapkan — jangan klaim sebaliknya).

---

## 7. Ikon & Teks

- Ikon dekoratif (SVG `aria-hidden` implisit karena tidak `role="img"`) — untuk ikon yang berdiri sendiri sebagai tombol, **pastikan ada teks/`aria-label`/`title`**.
  - Contoh sudah benar: tombol floating sidebar punya `title="Tampilkan Sidebar"`; tombol close drawer adalah icon-only (perlu `aria-label` — catatan perbaikan).
- Tombol dengan teks (umum) sudah memadai.
- Kontras: warna teks memakai token `--text-*` (lihat `docs/Color.md`) — pastikan kontras terjaga di dark mode.

---

## 8. Teks & Bahasa

- UI memakai **Bahasa Indonesia** (label, tombol, pesan, empty state) — konsisten.
- `lang="id-ID"` di PWA manifest & dokumen.
- Teks panjang (deskripsi masalah) memakai `whitespace-pre-wrap` agar line-break user dipertahankan.
- Empty state selalu menyertakan keterangan teks (via `EmptyState`/`KTable` empty props).

---

## 9. Media & Motion

- Gambar: `alt`/`@error` fallback pada logo; foto produk/servis memakai `alt` deskriptif bila ada.
- Animasi tidak memicu mual berlebihan (fade/slide pendek); `prefers-reduced-motion` disarankan untuk animasi besar berikutnya (lihat `docs/Animation.md`).

---

## 10. Aturan (Checklist untuk Kontributor)

1. Setiap field punya `<label>` (untuk/:id atau pembungkus).
2. Setiap tombol ikon-only punya `aria-label`/`title`/teks tersembunyi.
3. Fokus ring terlihat pada semua elemen interaktif.
4. Gunakan elemen native (`K*`) — jangan `<div>` untuk tombol/input.
5. Kontras teks memakai token; cek mode gelap.
6. Dialog/Drawer: sediakan cara tutup (Esc / tombol close) — sudah ada untuk Drawer & Search; untuk modal pastikan tombol Batal/Tutup ada.
7. Jangan menambahkan interaksi yang hanya bisa diakses mouse.
