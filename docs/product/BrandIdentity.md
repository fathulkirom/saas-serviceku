# ServiceKU — Brand Identity

> Identitas produk resmi **ServiceKU** — SaaS ERP untuk bisnis servis HP, servis laptop, sparepart, dan retail, multi-cabang & multi-tenant.
> Dokumen ini adalah acuan merek untuk seluruh sprint berikutnya (Sprint 6+). Semua keputusan desain/penulisan harus selaras dengan `docs/` (Architecture, Frontend, Component, Theme, Color, Typography, Naming, dll).

---

## 1. Visi Produk

Menjadi **platform manajemen operasional paling tepercaya untuk bisnis servis elektronik & retail di Indonesia** — satu tempat untuk mengelola seluruh siklus hidup sebuah toko servis: dari tiket servis masuk, perbaikan, sparepart, penjualan, stok, keuangan, hingga laporan dan pertumbuhan multi-cabang.

**Visi singkat:** *"Satu platform, seluruh operasional toko servis Anda — dari tiket masuk hingga garansi."*

---

## 2. Misi Produk

1. **Merapikan proses servis** — menyediakan alur kerja servis yang jelas dan konsisten (masuk → alokasi → dikerjakan → konfirmasi → selesai → garansi) sehingga tidak ada tiket yang hilang atau terlupakan.
2. **Menghubungkan data** — menyatukan servis, pelanggan, produk/sparepart, penjualan, dan keuangan dalam satu sumber data (satu database per toko).
3. **Memberi kendali pemilik** — dashboard & laporan yang mudah dipahami untuk memantau omzet, stok, kinerja tim, dan cabang.
4. **Mengurangi beban admin** — otomatisasi catatan, notifikasi, dan dokumen (tanda terima, nota, klaim garansi).
5. **Mendukung pertumbuhan** — multi-cabang, transfer stok, dan perencanaan stok (forecast) untuk toko yang berkembang.

---

## 3. Positioning

**Untuk pemilik dan tim toko servis HP/laptop & retail sparepart**, ServiceKU adalah **sistem ERP SaaS multi-tenant** yang menggantikan catatan manual/berceceran dengan satu alur kerja digital — dari tiket servis, stok sparepart, POS, hingga laporan multi-cabang — **berbeda dari aplikasi kasir/point-of-sale biasa** karena ServiceKU mengelola siklus servis end-to-end beserta operasional tokonya.

**Pernyataan positioning:**
> Untuk pemilik bisnis servis elektronik (HP & laptop) dan retail sparepart yang ingin semua proses tokonya rapi dan terpantau, ServiceKU adalah platform manajemen operasional yang menyatukan servis, stok, penjualan, keuangan, dan cabang dalam satu sistem — karena saat servis berjalan, stok habis, dan laporan harus akurat, catatan terpisah tidak lagi cukup.

---

## 4. Value Proposition

Nilai yang diberikan kepada pengguna (per peran):

| Peran | Nilai Utama |
|---|---|
| Owner | Melihat seluruh bisnis (omzet, servis aktif, stok menipis, laporan) dalam satu dashboard; kendali penuh & multi-cabang |
| CS | Mencatat servis masuk cepat & rapi, status jelas, komunikasi pelanggan terjaga (via WA) |
| Teknisi | Daftar pekerjaan yang jelas, update status mudah, sparepart & checklist terstruktur |
| Kasir | POS & penjualan cepat, setor harian, tutup kas |
| Gudang/Stok | Stok akurat, pembelian, mutasi, peringatan stok menipis, transfer antar cabang |
| Super Admin | Mengelola tenant, plan, dan platform dalam satu panel |

**Jaminan nilai:** *Semua tercatat, semua terpantau, semua terkendali — dari satu platform.*

---

## 5. Brand Promise

> **"Kelola servis dan toko Anda dengan tenang — setiap servis tercatat, setiap rupiah terpantau."**

Janji ini berarti:
- **Tidak ada tiket hilang** — setiap servis punya nomor & status yang jelas.
- **Tidak ada stok tak terduga** — peringatan stok menipis dan mutasi tercatat.
- **Tidak ada keuangan abu-abu** — penjualan, kas, dan setoran terkait satu sama lain.
- **Anda bisa mempercayakan operasional** — data tersimpan per toko, aman & terisolasi (multi-tenant).

---

## 6. Brand Keywords

**Terpercaya · Rapi · Cepat · Profesional · Modern · Andal · Sederhana · Menyeluruh**

| Kata Kunci | Makna dalam Produk |
|---|---|
| Terpercaya | Data akurat, pencatatan transaksional, izin per peran |
| Rapi | Struktur menu, status servis yang jelas, tabel yang terbaca |
| Cepat | Aksi satu langkah, shortcut (Cmd+K search), POS ringkas |
| Profesional | Bahasa Indonesia formal, komponen konsisten, tampilan bersih |
| Modern | Design token, dark mode, tampilan SaaS kontemporer |
| Andal | Alur kerja yang tidak kehilangan data (idempotensi, transaksi DB) |
| Sederhana | Satu aksi utama per layar, tanpa noise visual |
| Menyeluruh | Servis, stok, POS, keuangan, HR, laporan dalam satu sistem |

---

## 7. Tone

Suara komunikasi ServiceKU adalah: **profesional, kalem, jelas, dan membantu.**

- **Profesional** — tidak bergaya "startup gimmick"; tegas dan formal namun tidak kaku.
- **Kalem (calm)** — tidak dramatis; menyampaikan status dengan tenang (mis. error memandu solusi, bukan panik).
- **Jelas (clear)** — kalimat pendek, langsung pada inti, tanpa jargon teknis berlebihan.
- **Membantu (helpful)** — setiap pesan berorientasi solusi ("Fitur ini belum aktif pada paket Anda.").

---

## 8. Voice

Ciri-ciri suara merek dalam penulisan (detail: `docs/product/CopyWriting.md`):

1. **Bahasa Indonesia profesional** untuk seluruh UI & komunikasi produk.
2. **Kalimat pendek dan imperatif** untuk aksi ("Simpan", "Batalkan", "Selesaikan Pekerjaan").
3. **State yang jujur dan menenangkan** — sukses ("Berhasil disimpan."), error (jelaskan + solusi), konfirmasi (jelas konsekuensinya).
4. **Konsisten dengan status produk** — istilah status mengikuti `docs/Naming.md` (mis. "On Progress", "Menunggu Alokasi", "Selesai").
5. **Tidak berlebihan** — hindari emoji berlebihan di teks formal; gunakan ikon dengan bijak.

---

## 9. Batasan (Do / Don't)

**Do:**
- Selalu jelaskan dampak tindakan sebelum tindakan destruktif.
- Gunakan istilah yang konsisten dengan modul (Servis, Pelanggan, Produk, Penjualan, Kas, Inventaris).
- Sebutkan konteks (nomor servis/nota) saat relevan ("Servis #123").

**Don't:**
- Jangan memakai bahasa yang mengintimidasi atau menyalahkan pengguna.
- Jangan mencampur bahasa Inggris ke dalam kalimat UI (kecuali istilah produk: "Service", "Tracking").
- Jangan menjanjikan fitur yang tidak ada di paket/plan saat ini.

---

## 10. Kesesuaian dengan Dokumentasi Teknis

- Identitas ini **selaras** dengan `docs/Architecture.md` (produk = SaaS multi-tenant + per-tenant DB, business type & plan).
- Warna/tipografi merek mengikuti `docs/Color.md` & `docs/Typography.md` (primary `#2563EB`, Plus Jakarta Sans, id-ID).
- Komponen mengikuti `docs/Component.md` (K*); interaksi & copy mengikuti `docs/product/Interaction.md` & `CopyWriting.md`.
- Tidak ada pernyataan merek yang bertentangan dengan source code (fitur, status, role, plan).
