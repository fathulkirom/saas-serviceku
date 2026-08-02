# ServiceKU — Interaction Rules

> Aturan interaksi UI ServiceKU — selaras dengan komponen nyata (`docs/Component.md`), animasi (`docs/Animation.md`), aksesibilitas (`docs/Accessibility.md`), dan prinsip desain (`docs/product/DesignPrinciples.md`).

---

## 1. Hover

| Elemen | Perilaku |
|---|---|
| Tombol `KButton` (aksi) | `hover:shadow-sm` (elevasi halus), warna hover (`--primary-hover` untuk primer) |
| Baris tabel | `hover:bg-zinc-50/50` / `var(--bg-hover)` |
| Link | perubahan warna (`text-indigo-600 → text-indigo-700`) atau underline |
| Kartu interaktif | `.card--hover`/`.hover-lift`: shadow + translateY(-1/-2px) halus |
| Sidebar item | `bg-hover` saat non-aktif; aksen group saat aktif |
| Ikon aksi (icon-only) | `hover:bg-zinc-50` + warna lebih pekat |

- Durasi hover: `transition-all`/`transition-colors` singkat (150–200ms).
- Jangan gunakan hover sebagai satu-satunya cara mengungkap informasi penting (touch device).

---

## 2. Focus

- Semua elemen interaktif punya **focus ring terlihat**:
  - Input: `border-color: var(--primary)` + `box-shadow: 0 0 0 3px var(--primary-soft)` (kelas `.input` / pola `focus:ring-2`).
  - Tombol `.btn`: `focus:ring-2 focus:ring-offset-2`.
- Keyboard dapat mengakses semua aksi (Tab / Enter / Esc).
- Jangan menghapus outline tanpa pengganti fokus (`docs/Accessibility.md` §3).

---

## 3. Loading

| Konteks | Perilaku |
|---|---|
| Muat data (tabel/daftar) | `Skeleton` (type + count) — bukan spinner besar |
| Aksi tombol | Tombol `:disabled` + teks "Memproses..." / "Menyimpan..." / "Mengupload..." (pola `KButton` action) |
| Loading umum | `KLoading` spinner kecil (role=status) |
| Navigasi Inertia | Progress bar bawaan (`app.js` progress.color `#4B5563`) |

- Saat loading, **disable tombol** untuk mencegah klik ganda (idempotensi dijaga server-side juga).
- Jangan memindahkan layout saat loading — skeleton menempati area yang sama.

---

## 4. Empty State

- Gunakan `EmptyState` atau prop empty `KTable` (`emptyTitle`, `emptyDescription`, opsional `emptyActionLabel`).
- Format: ikon besar lembut + judul singkat + deskripsi + (opsional) CTA.
- Bahasa: "Belum ada data penjualan." / "Tidak ada data tiket servis." + ajakan.
- Empty state harus memberi jalan keluar (CTA "Buat", "Tambah") bila relevan.

---

## 5. Success (Sukses)

- **Feedback utama:** toast hijau di kanan atas (`.success`), durasi ±5 detik, auto-dismiss.
- **Feedback sekunder:** flash `success` dari server (redirect back), atau perubahan state yang terlihat (badge "Lunas", status berubah).
- Copy: "Berhasil disimpan." / "Servis berhasil diselesaikan." (lihat `CopyWriting.md`).
- Jangan menampilkan dialog sukses berlebihan untuk aksi yang sudah jelas terlihat hasilnya.

---

## 6. Error (Error)

- **Input:** border `--danger` + pesan error merah di bawah field (`text-red-500 text-xs mt-1`).
- **Aksi/global:** toast merah + (bila server) pesan flash `error`.
- Copy error harus **jelas + solusi** ("Fitur tidak tersedia pada paket Anda. Silakan upgrade.").
- Jangan menampilkan error teknis mentah (stack trace) ke pengguna.

---

## 7. Warning (Peringatan)

- Toast kuning (`warning`) untuk peringatan ringan (mis. trial hampir habis, stok menipis, demo mode).
- Badge/pill warning untuk status yang perlu perhatian (`--warning-soft`).
- Gunakan hemat — peringatan yang terlalu sering kehilangan makna.

---

## 8. Confirmation (Konfirmasi)

- Aksi **destruktif/tidak bisa dibatalkan** (hapus, void, cancel, reset, batalkan servis) wajib konfirmasi via **`KDialog`**:
  - Judul pertanyaan ("Batalkan Servis?"), deskripsi konsekuensi ("Servis #123 akan dibatalkan. Tindakan ini tidak dapat dibatalkan.").
  - Tombol: primer (bahaya) "Ya, ..." + sekunder "Tidak"/"Batal".
- Konfirmasi untuk aksi yang hanya memerlukan catatan singkat boleh pakai modal dengan input (mis. alasan cancel) — tetap `KDialog`.
- Jangan konfirmasi untuk aksi yang mudah dibatalkan/berisiko rendah (mis. buka modal, buka menu).

---

## 9. Toast

- Posisi: kanan atas, `max-width var(--toast-max-width)` (24rem), stack maksimal 5, `pointer-events-none` container.
- Tipe: `success` (hijau), `error` (merah), `warning` (kuning), `info` (biru).
- Durasi: 5 detik auto-dismiss; progress bar menunjukkan sisa waktu.
- Gunakan via `useToast()` (`docs/Frontend.md` §7) — jangan buat toast sendiri.

---

## 10. Animation

- **Transisi halaman:** bawaan LayoutNew (`page` fade/slide 0.15s, `mode="out-in"`).
- **Panel/modal:** `animate-scale-in` (0.3s) untuk modal; drawer slide (0.3s).
- **Elemen:** `animate-fade-in`, `animate-slide-up`, `animate-slide-down` sesuai konteks.
- **Loading:** `animate-spin` (KLoading), skeleton shimmer.
- **Theme switch:** `.theme-transition` (bg/color/border/shadow 250ms).
- Durasi maksimal ~0.4s; hormati `prefers-reduced-motion` untuk animasi besar.
- Detail: `docs/Animation.md`.

---

## Ringkasan Aturan

| State | Pola |
|---|---|
| Hover | shadow/color halus, 150–200ms |
| Focus | ring primary yang terlihat |
| Loading | skeleton + tombol disabled "Memproses..." |
| Empty | EmptyState/empty KTable + CTA |
| Success | toast hijau / perubahan state |
| Error | border danger + pesan + solusi |
| Warning | toast kuning / badge warning |
| Confirm | KDialog (konsekuensi jelas) |
| Toast | kanan atas, 5s, via useToast |
| Animation | fade/slide pendek, page transition bawaan |
