# ServiceKU — Design Principles

> 14 prinsip desain produk ServiceKU. Setiap prinsip sudah **selaras dengan kondisi UI nyata** (`docs/Frontend.md`, `docs/Component.md`, `docs/Theme.md`, `docs/Accessibility.md`, `docs/product/*`). Gunakan sebagai kerangka pengambilan keputusan di Sprint 6+.

---

## P1 — Information First (Data Didahulukan)

Tampilan ada untuk **menyajikan data**, bukan untuk dekorasi. Setiap layar menjawab: "informasi apa yang pengguna butuhkan sekarang?" Tabel rapi, angka jelas (`font-bold`, `font-mono` untuk ID), status berwarna semantik. Elemen dekoratif tidak boleh mengalahkan data.

---

## P2 — One Primary Action (Satu Aksi Utama)

Setiap layar/modal punya **satu aksi utama yang dominan** (tombol primer), dan aksi sekunder tampil lebih tenang (sekunder/teks). Contoh: modal simpan → "Simpan" primer + "Batal" sekunder; halaman aksi servis → tombol status yang relevan dengan kondisi saat itu (hanya tampil jika `can*` benar).

---

## P3 — Minimal Color (Warna Minimal)

Warna hanya untuk **makna**, bukan hiasan:
- Brand/aksi → `--primary` (biru) atau `indigo-600`.
- Status → semantik (`--success/warning/danger/info`).
- Netral → slate/zinc (dark scale).
Jangan menambah warna acak; gunakan token (`docs/Color.md`).

---

## P4 — Consistent Components (Komponen Konsisten)

Semua elemen interaktif wajib memakai komponen `K*` (`docs/Component.md`). Tidak ada `<button>/<input>/<select>/<textarea>` mentah di halaman. Konsistensi = ukuran, radius, shadow, dan perilaku sama di seluruh produk.

---

## P5 — Readable Tables (Tabel yang Terbaca)

Data adalah jantung produk; tabel harus mudah dipindai:
- Header tebal, uppercase, kecil (`text-xs font-bold`).
- Baris `hover` ringan, divider tipis.
- Angka di-align kanan; teks di-align kiri.
- Kosong → empty state yang jelas (bukan tabel kosong membingungkan).
- Gunakan `KTable` untuk pola standar (`docs/Component.md` §14).

---

## P6 — No Visual Noise (Tanpa Kebisingan Visual)

Hapus elemen yang tidak menambah informasi: border berlebihan, warna aksen berlapis, animasi berlebihan, teks panjang di tombol. Gunakan whitespace (`space-y-5`, `p-5`) dan hierarki tipografi untuk memisahkan, bukan garis tebal.

---

## P7 — Fast Interaction (Interaksi Cepat)

Target pengguna CS & kasir yang sibuk: minim klik, shortcut (Cmd/Ctrl+K search), aksi satu langkah, loading ringan (skeleton), dan feedback instan (toast). Tidak ada langkah berbelit untuk tugas harian (`docs/product/Interaction.md`).

---

## P8 — Business Focus (Fokus pada Bisnis)

Setiap fitur harus terhubung ke operasional bisnis nyata: servis, stok, penjualan, keuangan, cabang. Hindari fitur "menarik" yang tidak membantu. Bahasa & istilah mengikuti domain (`docs/Naming.md`): Servis, Pelanggan, Produk, Penjualan, Kas, Inventaris, Indent, dll.

---

## P9 — Clear Status (Status Selalu Jelas)

Produk yang terpercaya selalu memberi tahu posisi saat ini: status servis (`Menunggu Alokasi`, `On Progress`, `Siap Diambil`, `Selesai`), status pembayaran (`Lunas`/`Belum Bayar`), status langganan. Jangan biarkan pengguna menebak.

---

## P10 — Safety Before Destruction (Aman Sebelum Menghancurkan)

Aksi destruktif (hapus, void, cancel, reset) **wajib konfirmasi** lewat `KDialog` yang menjelaskan konsekuensi. Aksi yang tidak bisa dibatalkan harus diperjelas ("Tindakan ini tidak dapat dibatalkan."). Izin per role membatasi siapa yang bisa melakukannya (`docs/Backend.md` §13).

---

## P11 — Accessibility (Aksesibilitas)

Semua interaksi bisa diakses: label form, focus ring terlihat, kontras token, keyboard (Esc, Cmd+K), elemen native (`K*`). Detail: `docs/Accessibility.md`. Aksesibilitas bukan fitur bonus — bagian dari standar.

---

## P12 — Responsive (Responsif)

Produk dipakai di desktop (admin/owner), namun CS & kasir bisa memakai tablet/laptop. Layout harus adaptif: sidebar mobile, grid `grid-cols-1 md:grid-cols-2`, konten `max-w-7xl` dengan padding responsif. `docs/Spacing.md` §4.

---

## P13 — Progressive Disclosure (Ungkap Bertahap)

Jangan tumpahkan semua kontrol sekaligus. Tampilkan fitur sesuai: paket/plan (feature gate), peran (role), dan status servis (hanya aksi yang relevan). Sembunyikan yang tidak bisa dipakai — jangan tampilkan tombol lalu nonaktifkan tanpa konteks.

---

## P14 — Trustworthy Data (Data Terpercaya)

Setiap angka punya konteks & sumber: laporan konsisten dengan transaksi; total = jasa + sparepart; stok = mutasi terakumulasi. Jangan menampilkan angka yang bisa menyesatkan. Format id-ID konsisten (`Rp 1.234.567`).

---

## Cara Memakai Prinsip Ini

- Saat merancang layar baru: mulai dari **P1 (informasi)** dan **P2 (satu aksi utama)**, lalu **P3 (warna minimal)** + **P4 (komponen konsisten)**.
- Saat ada konflik: prioritaskan **kejelasan & kepercayaan** (P9, P14, P10) di atas estetika.
- Validasi terhadap persona (`docs/product/TargetUsers.md`) — apakah layar ini membantu CS/Kasir cepat, Owner paham, Teknisi tidak pusing.
