# ServiceKU — Target Users (Persona)

> Persona pengguna berdasarkan **role nyata** di sistem (`docs/Naming.md`): `owner`, `manager`, `head_store`, `cs`, `technician`, `cashier`, `courier`, `custom`, plus peran stok/gudang dan **super admin** (platform).
> Dokumen ini menjadi acuan keputusan UX, fitur, dan copy di Sprint 6+.

---

## Persona 1 — Owner (Pemilik Toko)

**Profil:** Pemilik usaha servis HP/laptop & retail sparepart, 1–5 cabang. Tidak selalu hadir di kasir; lebih sering memantau dari jauh. Usia 30–55.

- **Pain Point:**
  - Tidak tahu kondisi real omzet & stok setiap cabang tanpa bertanya ke staf.
  - Servis lama tidak selesai karena tidak ada yang memantau.
  - Laporan manual memakan waktu & rawan salah.
  - Sulit mempercayakan operasional saat tidak di toko.
- **Goals:**
  - Melihat omzet, servis aktif, stok menipis, dan kinerja tim dalam satu dashboard.
  - Menjaga setiap servis selesai tepat waktu & tercatat.
  - Mengontrol biaya, pengeluaran, dan setoran.
  - Membandingkan performa antar cabang.
- **Daily Activity:**
  - Buka dashboard (omzet hari ini, servis hari ini, low stock).
  - Periksa servis yang berjalan & yang sudah siap diambil.
  - Cek laporan penjualan/keuangan; pantau pengeluaran.
  - Kelola user/cabang, atur pengaturan toko & paket (billing).
- **Computer Skill:** Menengah — nyaman dengan HP & laptop, butuh UI yang jelas, tidak butuh menu rumit.

---

## Persona 2 — Manager / Head Store (Pengawas Toko)

**Profil:** Mengawasi operasional harian satu atau beberapa toko; menjembatani owner dan tim. Bisa juga merangkap CS senior.

- **Pain Point:**
  - Harus memastikan alur servis berjalan sesuai standar.
  - Sulit melacak siapa yang mengerjakan servis apa.
  - Rekonsiliasi penjualan & kas manual rawan selisih.
- **Goals:**
  - Memantau antrean servis & beban teknisi.
  - Menjaga stok cukup & pembelian tepat waktu.
  - Memastikan setoran dan kas tutup sesuai.
- **Daily Activity:**
  - Pantau dashboard & status servis; alokasikan ulang jika macet.
  - Setujui/monitor pengeluaran, pembelian, dan stok.
  - Cek laporan shift/absensi; tangani komplain pelanggan.
- **Computer Skill:** Menengah–tinggi — nyaman dengan sistem, sering di dashboard & laporan.

---

## Persona 3 — CS (Customer Service / Front Desk)

**Profil:** Ujung tombak toko; menerima servis & melayani pelanggan di konter. Sering terburu-buru saat toko ramai.

- **Pain Point:**
  - Mencatat servis manual lambat & sering salah (IMEI, kelengkapan, kerusakan).
  - Pelanggan bertanya status servis berkali-kali.
  - Lupa mencatat checklist/kondisi perangkat saat masuk.
- **Goals:**
  - Membuat tiket servis cepat & lengkap (unit, masalah, kelengkapan, checklist masuk).
  - Memberi informasi status ke pelanggan dengan akurat.
  - Membuat tanda terima & nota saat servis selesai.
- **Daily Activity:**
  - Buka dashboard CS; buat servis baru dari pelanggan.
  - Isi checklist masuk; lampirkan foto perangkat.
  - Update status & hubungi pelanggan (via WhatsApp) saat siap diambil.
- **Computer Skill:** Dasar–menengah — cepat belajar form, butuh alur sederhana & minim klik.

---

## Persona 4 — Teknisi (Teknisi Servis)

**Profil:** Mengerjakan perbaikan HP/laptop di workshop. Fokus ke pekerjaan teknis, bukan administrasi.

- **Pain Point:**
  - Tidak suka mencatat manual; lupa update status servis.
  - Butuh sparepart yang stoknya tidak jelas.
  - Komunikasi antar tim (CS/teknisi) sering terputus.
- **Goals:**
  - Melihat daftar servis yang ditugaskan ke dirinya.
  - Meng-update status cepat (terima → kerjakan → selesai).
  - Mencatat sparepart terpakai & klaim garansi dengan benar.
- **Daily Activity:**
  - Buka dashboard teknisi (servis milik saya); kerjakan perbaikan.
  - Update status & isi checklist/sparepart.
  - Request sparepart (indent) bila stok habis.
- **Computer Skill:** Dasar–menengah — lebih nyaman dengan HP; UI harus sederhana, tombol besar & jelas.

---

## Persona 5 — Gudang / Stok (Admin Stok & Pembelian)

**Profil:** Mengelola inventaris produk & sparepart: penerimaan barang, stok, mutasi, dan transfer antar cabang.

- **Pain Point:**
  - Stok tidak akurat karena catatan manual.
  - Tidak tahu kapan harus restock (low stock).
  - Barang masuk/keluar tidak tercatat konsisten.
- **Goals:**
  - Stok selalu akurat & terkini.
  - Mendapat peringatan stok menipis & forecast kebutuhan.
  - Mencatat pembelian, retur, dan transfer stok antar cabang.
- **Daily Activity:**
  - Cek dashboard inventaris & peringatan stok menipis.
  - Catat pembelian/penerimaan barang; lakukan mutasi/adjustment.
  - Proses transfer stok antar cabang.
- **Computer Skill:** Menengah — sering memakai tabel & form; butuh tabel yang terbaca & filter cepat.

---

## Persona 6 — Kasir (Cashier)

**Profil:** Menangani POS/penjualan retail & pembayaran servis di kasir. Transaksi harus cepat dan akurat.

- **Pain Point:**
  - Antrean pelanggan saat kasir lambat.
  - Selisih kas antara penjualan, setoran, dan uang di laci.
  - Pembayaran parsial/draft yang membingungkan.
- **Goals:**
  - Transaksi (penjualan/pembayaran servis) cepat & benar.
  - Buka/tutup kas sesuai shift; setoran harian tercatat.
  - Print nota/struk dengan mudah.
- **Daily Activity:**
  - Buka dashboard kasir; buka kas register.
  - Proses penjualan (POS) & pembayaran nota servis.
  - Setor harian & tutup kas di akhir shift.
- **Computer Skill:** Dasar–menengah — butuh alur POS yang ringkas, sedikit klik, tombol besar.

---

## Persona 7 — Super Admin (Admin Platform)

**Profil:** Pengelola platform ServiceKU (tim internal/CS platform) — bukan pengguna toko. Bertanggung jawab atas tenant, plan, billing, dan kesehatan sistem.

- **Pain Point:**
  - Banyak tenant dengan status/plan berbeda (trial, expired, suspend).
  - Perlu memantau kesehatan server, backup, dan log.
  - Pembayaran/langganan tenant perlu dikelola & dikonfirmasi.
- **Goals:**
  - Mengelola tenant (buat, suspend, activate, extend trial, login-as).
  - Mengelola plan, voucher/promo, dan pembayaran.
  - Memantau sistem (monitoring, log, backup) & mengatur settings global.
- **Daily Activity:**
  - Buka dashboard admin; pantau daftar tenant & status subscription.
  - Kelola plan/voucher; konfirmasi pembayaran.
  - Jalankan/monitor backup & log; ubah feature flags & pengaturan.
- **Computer Skill:** Tinggi — nyaman dengan panel admin, tabel besar, dan aksi teknis.

---

## Peran Tambahan (courier / custom)

- **Courier** — kurir untuk pickup & delivery; akses terbatas (dashboard minimal).
- **Custom** — role kustom dengan `custom_permissions`; kebutuhan mengikuti konfigurasi owner.

---

## Prinsip Desain Berdasarkan Persona

1. **Alur role-based** — setiap persona punya dashboard & menu sesuai perannya (lihat `docs/Frontend.md` — dashboard variants per role).
2. **Kecepatan untuk CS & Kasir** — minim klik, tombol besar, form ringkas.
3. **Kejelasan untuk Owner & Manager** — dashboard & laporan padat, angka tegas.
4. **Kesederhanaan untuk Teknisi** — tombol status yang jelas, minim administrasi.
5. **Kontrol untuk Super Admin** — tabel besar, filter, aksi eksplisit + konfirmasi.
6. **Bahasa Indonesia** — seluruh persona berbahasa Indonesia; copy harus profesional & jelas (`docs/product/CopyWriting.md`).
