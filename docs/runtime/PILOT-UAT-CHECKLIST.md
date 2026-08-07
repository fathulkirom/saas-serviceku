# PILOT-UAT-CHECKLIST — SERVICEKU v1.0 (Human Store UAT)

**Cara pakai**: Centang setiap butir **saat diverifikasi oleh manusia nyata di toko pilot**. Jangan menandai PASS otomatis — setiap kotak diisi hanya setelah diuji di lingkungan pilot (desktop/tablet/hp).

> **Prasyarat pilot (PILOT-PROVISION-03)**: tenant berjalan pada paket **Pro** (`users=full`).
> - ⚠️ **Basic** punya `users=read_only` → owner **tidak bisa membuat user** CS/teknisi/manager/kasir (POST diblokir 403), bertentangan dengan iklan "Maks. 3 karyawan". **Jangan pakai Basic untuk pilot yang perlu staf.**
> - Trial memblokir invoice/payment (`sales=read_only`).
>
> **Pilot tenant**: **`toko-kirom`** (subdomain `toko-kirom.serviceku.my.id`, dari nama toko "Toko Kirom" — `kirom` sendiri di-reserved sebagai subdomain admin platform).
>
> **Alur masuk nyata (diverifikasi)**:
> 1. Landing → **https://serviceku.my.id**
> 2. **Masuk** → **https://serviceku.my.id/masuk** (cari toko: nama/email/no. telepon)
> 3. Redirect ke subdomain tenant → **`toko-kirom.serviceku.my.id/login`**
> 4. Login tenant = **email + password** (form `SubdomainLogin`), **bukan** `/admin/login`.
>
> ⚠️ **`/admin/login`** (`kirom.serviceku.my.id/admin/login`) = **Central Management / Super Admin** (platform-level) — **bukan** login toko tenant.
>
> Daftar toko baru: **https://serviceku.my.id/register**.

---

## CS (Customer Service)

- [ ] CS dapat login ke aplikasi (email/password)
- [ ] Menu yang muncul: Dashboard, Customer, Service — **tidak** muncul Finance/Users/Master Data
- [ ] CS dapat mencari pelanggan (global search Ctrl+K dan halaman Customer)
- [ ] CS dapat membuat pelanggan baru cepat saat intake (tanpa keluar form)
- [ ] **Intake servis**: pilih pelanggan, isi keluhan (min. 5 karakter), tipe unit (free-text), IMEI/SN opsional
- [ ] Setelah submit, muncul nomor tracking (8 karakter otomatis)
- [ ] **Cetak tanda terima**: tombol Cetak di halaman servis membuka PDF tanda terima yang berisi nomor servis, data pelanggan, unit, dan keluhan
- [ ] CS dapat menugaskan teknisi (Assign di toolbar)
- [ ] CS dapat melihat progress servis di halaman detail (tab Overview/Timeline)
- [ ] CS dapat mengonfirmasi pemakaian part (setelah disetujui) dan stock berkurang
- [ ] CS dapat memproses pickup/serah terima (dengan nama + no HP penerima)
- [ ] Error yang muncul dapat dimengerti (bukan halaman putih / 500)

## Technician (Teknisi)

- [ ] Teknisi dapat login
- [ ] Menu yang muncul: Dashboard, Service — **tidak** muncul Finance/Users/Master Data
- [ ] Dashboard Teknisi menampilkan servis yang ditugaskan ke dirinya (bukan data orang lain)
- [ ] Teknisi dapat membuka detail servis yang ditugaskan
- [ ] **Diagnosa**: tab Diagnosa dapat menyimpan hasil pemeriksaan (findings + solusi) untuk servis miliknya
- [ ] **Mulai perbaikan** (toolbar Mulai Servis) berhasil
- [ ] **Request part**: tab Sparepart, pilih produk, qty, submit — status menjadi “Diminta” dan stock fisik TIDAK berubah
- [ ] **Selesaikan perbaikan** (toolbar Selesai) → status “Selesai”, part tidak mengurangi stock ganda
- [ ] Teknisi TIDAK dapat meng-QC servisnya sendiri (403)
- [ ] Teknisi TIDAK dapat menyelesaikan servis yang bukan miliknya (403) — kecuali override berwenang

## Manager

- [ ] Manager dapat login (dibuat oleh owner di Sistem → Pengguna)
- [ ] Menu: Dashboard, Customer, Service, Finance, Inventory, Reports — **tidak** ada leak ke CS/Teknisi
- [ ] Manager melihat progress semua servis (list + kanban + detail)
- [ ] **Setujui/Tolak part request** (tab Sparepart) — approve mereservasi stock, reject meminta alasan
- [ ] **QC**: tab QC (hanya owner/admin/manager) — isi checklist, pilih LULUS/GAGAL; LULUS → “Siap Diambil”
- [ ] **Garansi**: tab Garansi — lihat klaim, **Setujui/Tolak** klaim (tolak wajib alasan), approve membuat rework
- [ ] Refund garansi: tombol Refund hanya untuk yang berwenang; tercatat sebagai event finansial (Expense) terpisah
- [ ] Akses langsung `/keuangan`, `/pengaturan`, `/sistem` tidak memunculkan 500; data dibatasi per peran
- [ ] Dashboard owner/manager menampilkan angka nyata (bukan dummy)

## Kasir (Cashier)

- [ ] Kasir dapat login
- [ ] Menu: Dashboard, Customer, Service, Finance (data keuangan dibatasi transaksi hari ini yang lunas)
- [ ] Dashboard Kasir menampilkan **“Servis Siap Diambil”** (bukan skeleton abadi)
- [ ] Kasir dapat membuka detail servis (Service) — halaman tidak 403
- [ ] **Buat invoice**: toolbar Buat Invoice → draft dengan total dari backend (jasa + part)
- [ ] **Terima pembayaran**: toolbar Bayar → jumlah, metode → status lunas, receipt dapat dicetak
- [ ] Pembayaran ganda ditolak (tidak bisa bayar dua kali)
- [ ] **Serah terima**: toolbar Serahkan → unit diambil, garansi aktif (30 hari default)
- [ ] Kasir dapat membuka shift (Kas) jika digunakan

## Owner

- [ ] Owner dapat login
- [ ] Menu: Dashboard, Customer, Service, Finance, Inventory, Reports, Pengaturan
- [ ] Owner dapat membuat pengguna (CS/teknisi/kasir/manager) di Sistem
- [ ] Owner dapat membuat produk/sparepart di Inventaris
- [ ] Owner dapat mengatur data master (kategori/brand) di Pengaturan
- [ ] Owner dapat melihat seluruh laporan keuangan
- [ ] Owner dapat melakukan override repair / QC / pickup bila diperlukan
- [ ] Owner dapat membuka/menutup servis dan mengelola garansi

---

## Lintas Peran — Alur Harian Utama (uji sebagai satu toko nyata)

- [ ] Pelanggan datang → CS menerima + cetak tanda terima
- [ ] Teknisi melihat job yang ditugaskan
- [ ] Teknisi diagnosa → (bila perlu estimasi) → mulai perbaikan
- [ ] Teknisi minta part → manager/owner setujui (reservasi) → CS konfirmasi (stock turun) → part masuk invoice
- [ ] Teknisi selesai → owner/manager QC LULUS → siap diambil
- [ ] Kasir buat invoice → terima bayaran → cetak receipt
- [ ] Pelanggan ambil unit → garansi aktif tercatat
- [ ] (Bila ada komplain) CS buka klaim garansi → manager setujui → rework → QC → selesai
- [ ] Error/tindakan ganda (klik 2x, finish 2x, approve 2x, refund 2x) tidak merusak data

## Catatan untuk Pilot

- [ ] Catat semua tempat yang membutuhkan intervensi developer (harusnya NOL untuk alur utama)
- [ ] Catat masalah mobile/tablet yang menghalangi operasi (bukan sekadar estetika)
- [ ] Verifikasi tanggal garansi & nomor invoice yang tercetak
