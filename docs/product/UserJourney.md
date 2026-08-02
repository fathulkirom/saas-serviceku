# ServiceKU — User Journey

> Perjalanan pengguna (user journey) untuk 5 peran utama. Alur mengikuti **route & fitur nyata** (`docs/Backend.md`, `docs/Frontend.md`): dari login hingga logout, termasuk titik kepuasan (happy path), titik frustasi potensial, dan momen kunci produk.
> Persona per peran: `docs/product/TargetUsers.md`.

---

## Konteks Umum

- **Login tenant**: akses via subdomain toko (mis. `toko.serviceku.my.id`) → halaman `/login` → autentikasi (password; opsional 2FA & email verify).
- **Login super admin**: `/admin/login` (panel platform).
- **Dashboard role-based**: halaman `/dashboard` menampilkan varian sesuai role (`DashboardController`).
- **Keluar**: menu user → "Keluar" (POST `/logout`).
- **Status servis** (acuan): `menunggu_alokasi → diterima → dikerjakan → menunggu_konfirmasi_pelanggan → siap_diambil → selesai` (+ `indent`, `onpartner`, `cancel`, `void`, `diambil`).

---

## Journey 1 — Owner (Pemilik Toko)

**Tujuan:** memantau & mengendalikan seluruh operasional (omzet, servis, stok, cabang, tim).

1. **Login** — buka subdomain toko → `/login` → masukkan email & password → (2FA bila aktif) → masuk ke **Dashboard Owner**.
2. **Dashboard** — lihat omzet hari ini, servis hari ini, stok menipis, dan servis aktif; pantau status servis (sidebar breakdown).
3. **Pantau servis** — buka daftar servis (`/services`) → buka detail servis (`/services/{id}`) untuk lihat progress, biaya, sparepart, timeline.
4. **Kontrol stok** — cek inventaris (`/inventaris`), peringatan stok menipis, forecast; buka pembelian (`/purchases`) bila perlu restock.
5. **Cek keuangan** — buka `/keuangan` (penjualan & pengeluaran), `/kas` (setoran), laporan (`/reports`) untuk penjualan/finance/produktivitas.
6. **Kelola tim & cabang** — `/users` (tambah/edit user & menu access), `/sistem` (cabang, shift), `/pengaturan` (profil toko, tema, WA).
7. **Langkganan** — `/billing` (paket, voucher) dan `/profile` (profil toko, statistik bisnis).
8. **Logout** — menu user → "Keluar".

**Momen kunci:** dashboard ringkas (omzet + servis + stok) → Owner tahu kondisi bisnis dalam 10 detik. **Risiko:** jika dashboard lambat/bias, Owner kehilangan kepercayaan (P1 Information First).

---

## Journey 2 — CS (Customer Service / Front Desk)

**Tujuan:** menerima & melacak servis pelanggan dengan cepat dan akurat.

1. **Login** — `/login` → **Dashboard CS** (ringkas: tugas harian CS).
2. **Terima servis baru** — pelanggan datang → buka `/services/create` → isi data pelanggan (nama, HP), unit (tipe, IMEI), deskripsi masalah, kelengkapan → **Simpan** → tiket servis dibuat (nomor unik).
3. **Checklist masuk** — buka detail servis → "Isi Checklist Masuk" (pilih template, centang item kondisi) → lampirkan **foto perangkat** (upload).
4. **Update status** — saat teknisi mulai/menyelesaikan, CS ikut memantau; saat servis selesai, CS melakukan **"Konfirmasi Pelanggan"** / buat nota (`/keuangan` tab penjualan) atau `Complete Servis`.
5. **Informasi ke pelanggan** — cek status via detail servis; gunakan WhatsApp (WA gateway) untuk memberi kabar siap diambil.
6. **Cetak dokumen** — "Cetak Tanda Terima" saat menerima; cetak nota saat selesai.
7. **Logout** — menu user → "Keluar".

**Momen kunci:** pembuatan tiket 1 layar + checklist + foto → data servis lengkap sejak awal. **Risiko:** form terlalu panjang memperlambat antrean (P7 Fast Interaction) — buat field wajib seminimal mungkin.

---

## Journey 3 — Teknisi (Teknisi Servis)

**Tujuan:** mengerjakan perbaikan dengan daftar pekerjaan jelas dan update status cepat.

1. **Login** — `/login` → **Dashboard Teknisi** (daftar servis yang ditugaskan kepadanya: "assigned to me", waiting, in progress, completed today).
2. **Lihat pekerjaan** — buka daftar servis → hanya yang relevan untuknya; buka detail (`/services/{id}`) untuk masalah, checklist, sparepart, foto.
3. **Mulai kerja** — **"Terima Pekerjaan"** (diterima) → **"Mulai Pekerjaan"** (dikerjakan).
4. **Kerjakan** — perbarui status saat selesai: **"Selesaikan Pekerjaan"** (siap konfirmasi); isi **checklist keluar** & catat **sparepart terpakai**.
5. **Sparepart kurang** — gunakan **"Indent Sparepart"** (waiting parts) → saat sparepart datang, "Lanjutkan dari Indent".
6. **Kasus khusus** — "Kirim ke Partner" (onpartner) bila perlu; klaim garansi saat selesai & pelanggan datang.
7. **Logout** — menu user → "Keluar".

**Momen kunci:** dashboard "servis milik saya" + tombol status besar → teknisi tidak perlu admin. **Risiko:** banyak klik per servis — pastikan tombol status hanya muncul saat relevan (P13 Progressive Disclosure).

---

## Journey 4 — Kasir (Cashier)

**Tujuan:** memproses penjualan & pembayaran dengan cepat dan akurat, kas selalu balance.

1. **Login** — `/login` → **Dashboard Kasir**.
2. **Buka kas** — buka `/cash-registers` → "Buka Kas" (shift dimulai, saldo awal).
3. **Transaksi penjualan** — buka POS/`/sales/create` → pilih produk/keranjang → diskon → **Simpan** (pembayaran tunai/transfer; print nota).
4. **Bayar servis** — saat servis selesai & pelanggan bayar, buat nota dari servis / terima pembayaran (lunas).
5. **Setor harian** — buat **setoran harian** (`/kas`) di akhir shift agar saldo kas cocok.
6. **Tutup kas** — `/cash-registers` → "Tutup Kas" (shift selesai, saldo akhir tercatat).
7. **Logout** — menu user → "Keluar".

**Momen kunci:** POS ringkas + buka/tutup kas + setoran → kasir selesai tepat waktu tanpa selisih. **Risiko:** jika alur draft/nota rumit, transaksi lambat — pertahankan alur lurus (P7).

---

## Journey 5 — Super Admin (Admin Platform)

**Tujuan:** mengelola tenant, plan, pembayaran, dan kesehatan platform.

1. **Login** — buka `/admin/login` → autentikasi admin → **Dashboard Super Admin**.
2. **Pantau tenant** — buka `/admin/tenants` (daftar, status, plan, trial) → detail tenant (`/admin/tenants/{id}`): statistik, langganan, aksi (suspend/activate/extend trial/change plan).
3. **Kelola tenant baru** — buat tenant (`/admin/tenants/create`) → tentukan plan/trial; atau tangani registrasi mandiri (OTP).
4. **Kelola plan & promo** — `/admin/plans` (harga, fitur, business types, promo) & `/admin/vouchers` (kode promo).
5. **Billing** — `/admin/payments` (invoice, konfirmasi pembayaran), `/admin/payment-settings` (gateway).
6. **Monitoring & ops** — `/admin/monitoring`, `/admin/logs` (log, clear), `/admin/backup` (jalankan backup, upload ke Drive), `/admin/settings` (feature flags, mail, pengaturan global).
7. **Login-as tenant** — dari detail tenant, "Login Sebagai" untuk troubleshooting.
8. **Logout** — menu admin → "Keluar".

**Momen kunci:** tabel tenant + aksi eksplisit (suspend/activate/extend) → super admin mengelola banyak tenant efisien. **Risiko:** aksi berbahaya (suspend/delete) wajib konfirmasi (P10) — hindari kesalahan klik.

---

## Momen Emosional Lintas Peran (Emotional Journey)

| Tahap | Perasaan | Kebutuhan |
|---|---|---|
| Login | Fokus, sedikit tergesa | Cepat, aman (2FA opsional), tidak lupa kata sandi (reset mudah) |
| Kerja harian | Sibuk, padat | Alur cepat, status jelas, tidak ada data hilang |
| Selesai transaksi/servis | Lega | Konfirmasi sukses singkat, dokumen siap (nota/tanda terima) |
| Error / stok habis | Cemas | Pesan jelas + solusi, tidak menyalahkan |
| Laporan (owner) | Butuh kepastian | Angka akurat & konsisten (P14) |
| Logout | Selesai | Mudah & aman (session ditutup) |

---

## Prinsip yang Menjamin Journey Lancar

1. **Login satu pintu per domain** — tidak bingung antara panel tenant & admin.
2. **Dashboard sesuai peran** — setiap persona langsung melihat yang relevan (`docs/product/TargetUsers.md`).
3. **Status servis sebagai benang merah** — semua peran membaca status yang sama (`docs/Naming.md`).
4. **Feedback pada setiap langkah** — toast sukses/error, konfirmasi destruktif (`docs/product/Interaction.md`).
5. **Bahasa Indonesia yang jelas** — copy memandu di setiap titik (`docs/product/CopyWriting.md`).
