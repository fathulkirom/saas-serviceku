# ✅ Checklist Operasional Harian (Uji Coba)

Tujuan: memastikan sistem tetap aman dipakai harian selama tahap uji coba.

## Jalankan Cek Otomatis Dulu

```bash
./scripts/ops-health-check.sh
```

- [ ] Simpan hasil ringkasan `PASS/WARN/FAIL`
- [ ] Jika hasil `NO-GO`, hentikan dulu aktivitas berisiko dan eskalasi

## Informasi Shift

- Tanggal:
- Nama petugas:
- Jam cek:
- Catatan singkat:

## A. Akses Domain

- [ ] Buka https://serviceku.my.id -> halaman tampil normal
- [ ] Buka https://admin.serviceku.my.id -> halaman tampil normal
- [ ] Buka 1 subdomain tenant aktif (contoh: https://namatoko.serviceku.my.id/login)
- [ ] Login tenant berhasil

Jika gagal:
- [ ] Catat error
- [ ] Eskalasi ke teknis (cek tunnel/DNS)

## B. Email (SMTP Brevo)

- [ ] Kirim 1 test email dari halaman admin settings
- [ ] Email diterima di inbox
- [ ] Jika tidak ada di inbox, cek folder spam
- [ ] Catat waktu kirim dan email tujuan

Jika gagal:
- [ ] Jangan kirim massal dulu
- [ ] Eskalasi ke teknis (cek SMTP auth/port/queue)

## C. Queue & Job

- [ ] Cek status worker: berjalan
- [ ] Cek failed jobs: 0
- [ ] Uji 1 proses yang memicu job (misal invoice/notifikasi)

Jika gagal:
- [ ] Catat job yang gagal
- [ ] Eskalasi ke teknis untuk restart worker dan retry job

## D. Transaksi Sampling

- [ ] Buat 1 transaksi draft
- [ ] Bayarkan draft tersebut
- [ ] Verifikasi status jadi lunas
- [ ] Verifikasi stok/mutasi berubah sesuai
- [ ] (Opsional) uji void lalu verifikasi rollback stok

Jika gagal:
- [ ] Hentikan perubahan data massal
- [ ] Eskalasi ke teknis

## E. Backup

- [ ] File backup terbaru terbentuk sesuai jadwal
- [ ] Ukuran file backup masuk akal (tidak 0 KB)

Jika gagal:
- [ ] Catat waktu terakhir backup sukses
- [ ] Eskalasi ke teknis

## F. Go / No-Go Hari Ini

- [ ] GO: semua poin A-E aman
- [ ] NO-GO: ada poin kritikal gagal (domain, email, queue, backup)

Keputusan hari ini:
- Status:
- Alasan:
- PIC tindak lanjut:

## G. Kontak Eskalasi

- PIC teknis:
- Cadangan PIC:
- Kanal komunikasi:

## H. Ringkasan Insiden (Jika Ada)

- Jam kejadian:
- Gejala:
- Dampak:
- Tindakan sementara:
- Status akhir:
