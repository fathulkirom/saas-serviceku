# PILOT-UAT-RUNBOOK — SERVICEKU v1.0 (Human Store UAT)

**Sifat**: Runbook manual untuk manusia nyata di toko pilot. **Bukan** pengganti PHPUnit — test otomatis membuktikan kode, UAT manusia membuktikan usability.

**Alur masuk (diverifikasi PILOT-UAT-02/PROVISION-03)**:
1. Landing: `https://serviceku.my.id`
2. Tenant entry: `https://serviceku.my.id/masuk` (cari toko)
3. → redirect ke `toko-kirom.serviceku.my.id/login` (login tenant = email/password)
4. Registrasi toko baru: `https://serviceku.my.id/register`

> ⚠️ `/admin/login` = **Central Management / Super Admin** (platform), bukan login toko.
> **Pilot tenant**: `toko-kirom` — paket **Pro** (Basic `users=read_only` memblokir pembuatan user; Trial memblokir sales/payment).

**Format setiap test**:

| Field | Isi |
|---|---|
| Actor | siapa yang menjalankan (CS/Teknisi/Manager/Kasir/Owner) |
| URL/Page | halaman yang dituju |
| Action | langkah konkret |
| Expected | hasil yang diharapkan |
| Actual | **diisi saat uji** (JANGAN diisi otomatis) |
| PASS/FAIL | **diisi saat uji** |
| Bug ID | bila gagal, beri ID (mis. UAT-001) |
| Notes | catatan tambahan |

---

## A. PRE-FLIGHT

| # | Actor | URL/Page | Action | Expected | Actual | PASS/FAIL | Bug ID | Notes |
|---|---|---|---|---|---|---|---|---|
| A1 | Owner | serviceku.my.id | Buka landing | Halaman landing muncul, HTTPS aman, CSS/JS termuat | | | | |
| A2 | Owner | serviceku.my.id/masuk | Cari toko pilot (nama/email/no) | Redirect ke `toko-kirom.serviceku.my.id/login` | | | | |
| A3 | Owner | toko-kirom.serviceku.my.id/login | Login owner (email+password) | Masuk ke Dashboard | | | | |
| A4 | Owner | serviceku.my.id/register | Buka halaman registrasi | Form pilih paket muncul (Trial/Basic/Pro/Enterprise) | | | | (Tidak wajib submit) |
| A5 | Owner | Dashboard | Cek paket tenant | **Basic/Pro**, bukan Trial | | | | Trial = sales read_only |
| A6 | Owner | Sistem → Pengguna | Buat user CS, Teknisi, Manager, Kasir | Role selector menampilkan role resmi; legacy diberi label (legacy) | | | | |

## B. CS FLOW

| # | Actor | URL/Page | Action | Expected | Actual | PASS/FAIL | Bug ID | Notes |
|---|---|---|---|---|---|---|---|---|
| B1 | CS | toko-kirom.serviceku.my.id/login | Login CS | Menu: Dashboard, Customer, Service (tanpa Finance/Users) | | | | |
| B2 | CS | Customers | Cari/lihat pelanggan | List + search bekerja | | | | |
| B3 | CS | Service → Buat Tiket | Intake: pilih customer (atau quick-add), keluhan, tipe unit | Nomor tracking 8 karakter muncul | | | | |
| B4 | CS | Detail servis → Cetak | Cetak tanda terima | PDF berisi nomor servis/customer/unit/keluhan | | | | |
| B5 | CS | Detail servis → Assign | Tugaskan teknisi | Status → diterima; teknisi tercatat | | | | |
| B6 | CS | Detail servis → Sparepart | Konfirmasi pemakaian part (setelah approve) | Stock berkurang; part masuk invoice | | | | |

## C. TECHNICIAN FLOW

| # | Actor | URL/Page | Action | Expected | Actual | PASS/FAIL | Bug ID | Notes |
|---|---|---|---|---|---|---|---|---|
| C1 | Teknisi | toko-kirom.serviceku.my.id/login | Login teknisi | Dashboard menampilkan servis yang ditugaskan | | | | |
| C2 | Teknisi | Detail servis → Diagnosa | Isi diagnosis (findings + solusi) | Tersimpan; status berubah | | | | |
| C3 | Teknisi | Detail servis → Mulai | Mulai perbaikan | Status → dikerjakan | | | | |
| C4 | Teknisi | Detail servis → Sparepart | Request part (pilih produk + qty) | Status part = diminta; stock fisik TIDAK berubah | | | | |
| C5 | Teknisi | Detail servis → Selesai | Selesaikan perbaikan | Status → selesai; stock tidak double | | | | |

## D. MANAGER FLOW

| # | Actor | URL/Page | Action | Expected | Actual | PASS/FAIL | Bug ID | Notes |
|---|---|---|---|---|---|---|---|---|
| D1 | Manager | toko-kirom.serviceku.my.id/login | Login manager | Menu: Dashboard, Customer, Service, Finance, Inventory, Reports | | | | |
| D2 | Manager | Detail servis → Sparepart | Approve part request | Part ter-reservasi (stock fisik tetap) | | | | |
| D3 | Manager | Detail servis → QC | Lakukan QC (checklist + LULUS/GAGAL) | LULUS → siap diambil; GAGAL → kembali repair | | | | |
| D4 | Manager | Detail servis → Garansi | Setujui/tolak klaim garansi | Approve → rework dibuat; tolak → wajib alasan | | | | |

## E. CASHIER FLOW

| # | Actor | URL/Page | Action | Expected | Actual | PASS/FAIL | Bug ID | Notes |
|---|---|---|---|---|---|---|---|---|
| E1 | Kasir | toko-kirom.serviceku.my.id/login | Login kasir | Dashboard kasir menampilkan “Servis Siap Diambil” | | | | |
| E2 | Kasir | Detail servis → Buat Invoice | Buat draft invoice dari servis | Total dari backend (jasa + part) | | | | |
| E3 | Kasir | Detail servis → Bayar | Terima pembayaran (jumlah + metode) | Status lunas; receipt dapat dicetak | | | | |
| E4 | Kasir | Detail servis → Serahkan | Pickup (nama + no HP penerima) | Unit diambil; garansi aktif (30 hari) | | | | |
| E5 | Kasir | Detail servis → Bayar (2x) | Coba bayar lagi setelah lunas | Ditolak (tidak bisa double payment) | | | | |

## F. OWNER FLOW

| # | Actor | URL/Page | Action | Expected | Actual | PASS/FAIL | Bug ID | Notes |
|---|---|---|---|---|---|---|---|---|
| F1 | Owner | toko-kirom.serviceku.my.id/login | Login owner | Semua menu (Dashboard, Customer, Service, Finance, Inventory, Reports, Pengaturan) | | | | |
| F2 | Owner | Sistem → Pengguna | Buat user (CS/Teknisi/Manager/Kasir) | User aktif; role benar | | | | |
| F3 | Owner | Inventaris | Buat produk/sparepart | Produk muncul; stock tercatat | | | | |
| F4 | Owner | Detail servis → Minta Reopen | Reopen servis tertutup (alasan wajib) | Approval masuk ke Approval Center | | | | |
| F5 | Owner | Approval Center | Setujui reopen | Servis ter-unlock | | | | |
| F6 | Owner | Pengaturan | Cek setting toko | Bisa diakses (hanya owner/admin) | | | | |

## G. WARRANTY FLOW

| # | Actor | URL/Page | Action | Expected | Actual | PASS/FAIL | Bug ID | Notes |
|---|---|---|---|---|---|---|---|---|
| G1 | CS | Detail servis (selesai, dalam garansi) | Buka klaim garansi (keluhan) | Klaim status submitted | | | | |
| G2 | Manager | Detail servis → Garansi | Setujui klaim | Rework service dibuat (status aktif) | | | | |
| G3 | Teknisi | Rework service | Perbaiki rework | Rework selesai | | | | |
| G4 | Manager | Rework → QC | QC LULUS | Klaim resolve (completed) | | | | |
| G5 | Owner | Detail servis → Garansi | (Opsional) Refund dengan data uji terkontrol | Refund tercatat sebagai event finansial (Expense); nota asli utuh | | | | Jangan real-money |

## H. MOBILE/TABLET CHECK

| # | Actor | Device | URL/Page | Action | Expected | Actual | PASS/FAIL | Bug ID | Notes |
|---|---|---|---|---|---|---|---|---|---|
| H1 | CS | Tablet/HP | Intake | Isi form intake di layar kecil | Form dapat diisi; tidak terpotong | | | | |
| H2 | Teknisi | Tablet/HP | Detail servis | Diagnosa + part di mobile | Tombol berfungsi | | | | |
| H3 | Manager | Tablet/HP | QC | QC LULUS di mobile | Berhasil | | | | |
| H4 | Kasir | Tablet/HP | Payment + pickup | Bayar & serah terima di mobile | Berhasil | | | | |
| H5 | Owner | Tablet/HP | Dashboard | Angka dashboard terbaca | Tidak rusak layout kritis | | | | |

> Kosmetik (visual) = P3, bukan blocker. Hanya kegagalan fungsional yang menjadi blocker pilot.

## I. END-OF-DAY CHECK

| # | Actor | URL/Page | Action | Expected | Actual | PASS/FAIL | Bug ID | Notes |
|---|---|---|---|---|---|---|---|---|
| I1 | Kasir/Owner | Keuangan | Cek transaksi hari ini | Total sales + expense + pembayaran konsisten | | | | |
| I2 | Owner | Inventaris | Cek stock | Stock berkurang sesuai part terpakai | | | | |
| I3 | Owner | Reports | Laporan hari ini | Data akurat; tidak ada angka dummy | | | | |
| I4 | Owner | Dashboard | Ringkasan harian | Servis masuk/selesai/ambil sesuai catatan fisik | | | | |

---

## Klasifikasi Bug selama UAT

- **P0**: data hilang, uang salah, stock salah, keamanan/tenant bocor.
- **P1**: operasi harian tidak bisa lanjut.
- **P2**: ada workaround.
- **P3**: visual/polish.

**Selama pilot**: perbaiki **P0/P1** segera; catat **P2/P3** untuk update post-pilot.
