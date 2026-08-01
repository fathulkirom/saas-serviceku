# 🚀 ServiceKU — Runbook Setup Pra-Produksi (Langkah Manual)

> Panduan langkah-demi-langkah untuk menyelesaikan sisa setup yang **butuh aksi manual** di server
> (`kirom@192.168.1.33`). Semua yang bisa diotomasi dari repo sudah selesai (Issue #43).
>
> ⚠️ **Penting**: Perintah `sudo` / rahasia (password, token) harus diketik **langsung di terminal**,
> bukan melalui chat/AI.

---

## 📋 Ringkasan Status

| Item | Status | Butuh aksi manual? |
|------|--------|--------------------|
| Backup DB (Blocker #1) | 🟡 Script siap | ✅ **Ya** — Langkah 1 |
| Slow query log | ✅ FIXED | ❌ |
| Monitoring (Sentry) | 🟡 Config siap | ✅ **Ya** — Langkah 2 |
| Queue worker | ✅ FIXED | ❌ |
| OPcache / performa | ✅ FIXED | ❌ |

---

## 🔧 Langkah 1 — Backup Database (Blocker #1) ✅ RESOLVED

> Backup **sudah berfungsi** ke `/home/kirom/serviceku/storage/backups` + cron 03:00 terpasang.
>
> ⚠️ **Catatan HDD**: `/mnt/hdd` (exfat) **tidak bisa** dibuat writable untuk kirom —
> driver exfat sistem ini mengabaikan opsi `uid/gid/fmask/dmask` (dibuktikan dengan mount
> manual eksplisit; tetap root-owned). Jadi backup disimpan di `storage/backups` (disk sistem).
> Opsi HDD: jalankan backup sebagai root (butuh sudoers NOPASSWD khusus).

### 1.1 (selesai) Permission storage
```bash
sudo chown -R kirom:www-data /home/kirom/serviceku/storage
sudo chmod -R g+w /home/kirom/serviceku/storage
```

### 1.2 Tes backup dari HOST
```bash
cd /home/kirom/serviceku && ./backup.sh --auto
```

**Hasil yang diharapkan** (semua ✅):
```
[OK] Semua prasyarat terpenuhi
[OK]  Master database selesai: master_<timestamp>.sql.gz
[OK]  N database tenant selesai
[OK]  Storage selesai: storage_<timestamp>.tar.gz
[OK]  File .env di-backup
[OK] Backup database selesai!
```

**Verifikasi file backup**:
```bash
ls -lh /home/kirom/serviceku/storage/backups/databases/ | tail -5
```

> Jika error lain, catat pesannya dan kirim ke saya.

### 1.3 Pasang cron host (backup otomatis tiap 03:00)

```bash
crontab -e
```

Tambahkan baris berikut, lalu simpan:
- **nano**: tempel baris → `Ctrl+O` → `Enter` → `Ctrl+X`
- **vi/vim**: `i` → tempel baris → `Esc` → `:wq` → `Enter`

```
0 3 * * * /home/kirom/serviceku/backup.sh --auto >> /home/kirom/serviceku-backup.log 2>&1
```

> ⚠️ **Jangan pakai `/var/log/serviceku-backup.log`** — `/var/log` butuh root,
> kirom tidak bisa tulis (permission denied). Pakai path di HOME kirom.

Verifikasi cron terpasang:
```bash
crontab -l
```

> Catatan: jadwal `backup:run` di dalam container (via scheduler Laravel) hanya mem-backup
> storage/.env (tanpa DB). Backup DB yang benar = cron host di atas.

---

## 🔧 Langkah 2 — Monitoring Error (Sentry)

> Package `sentry/sentry-laravel` sudah terinstall & terkonfigurasi. Yang kurang hanya **DSN**.

### 2.1 Dapatkan DSN

1. Login ke [sentry.io](https://sentry.io) (atau self-hosted Sentry)
2. Buat project baru → pilih **Laravel**
3. Salin **DSN** (format: `https://xxxxx@o1.ingest.sentry.io/xxxx`)

### 2.2 Isi DSN di server

```bash
cd /home/kirom/serviceku
# Edit .env (nano .env), tambah / ganti baris berikut:
#   SENTRY_DSN=https://xxxxx@o1.ingest.sentry.io/xxxx
#   SENTRY_ENVIRONMENT=production
#   SENTRY_SAMPLE_RATE=1.0
```

Lalu bersihkan cache config:

```bash
docker exec serviceku-app php artisan config:clear
```

### 2.3 Verifikasi

- Error baru akan otomatis masuk dashboard Sentry (bootstrap/app.php sudah mereport).
- Cek log container tidak ada error baru terkait Sentry.

---

## 🔧 Langkah 3 — Verifikasi Akhir

```bash
# 1. Situs sehat
curl -s -o /dev/null -w "%{http_code}\n" https://serviceku.my.id        # 200

# 2. MySQL sehat + slow query ON
docker inspect serviceku-mysql --format "{{.State.Health.Status}}"       # healthy
docker exec serviceku-mysql mysql -uroot -pserviceku_root_pass -N \
  -e "SHOW VARIABLES LIKE 'slow_query_log';"                             # ON

# 3. Queue worker jalan
docker ps --filter name=serviceku-queue --format "{{.Names}} {{.Status}}"

# 4. OPcache ON
docker exec serviceku-app php -i | grep "opcache.enable =>"              # On
```

---

## 📎 Referensi

- Issue pelacakan: **#43** (checklist pra-produksi) — semua item repo sudah ✅
- `backup.sh` — path & env override siap server (commit `e222675`)
- `.env` template produksi (Redis, log rotation, Sentry) — commit `bf96836`
- API contract test otomatis (Newman + Schemathesis) — job `api-contract-tests`
  di `.github/workflows/pre-prod-serviceku.yml`

---

### ✅ Setelah selesai semua langkah, kabari saya
Saya akan verifikasi backup berhasil & menutup Blocker #1 di Issue #43.

---

## ✅ Checklist Go/No-Go (Tahap Uji Coba)

Tujuan: pastikan fondasi operasional stabil dulu sebelum lanjut perbaikan fitur aplikasi.

### A. Setup Wajib (harus hijau semua)

1. **Subdomain & Tunnel**
- `serviceku.my.id`, `admin.serviceku.my.id`, dan `*.serviceku.my.id` resolve ke tunnel aktif.
- `cloudflared` auto-start setelah reboot.
- Landing, admin, dan tenant login bisa diakses dari domain publik.

2. **SMTP Brevo**
- Driver mail aktif di `smtp` (bukan `log`).
- Host/port/enkripsi/user/password valid.
- Sender domain dan from address sudah terverifikasi.
- Test email berhasil masuk inbox (cek spam juga).

3. **Queue Worker**
- `QUEUE_CONNECTION=database` (atau redis jika dipakai).
- Worker jalan terus dengan process manager (systemd/supervisor).
- Setelah restart server, job tetap diproses.

4. **Backup Harian**
- Backup otomatis berjalan sesuai jadwal.
- File backup terbentuk dan bisa di-restore uji sampel.

### B. Verifikasi Harian (10-15 menit)

1. Cek akses `serviceku.my.id`, `admin.serviceku.my.id`, dan 1 subdomain tenant aktif.
2. Kirim 1 email test dari halaman pengaturan sistem.
3. Cek failed jobs = 0 atau ada rencana retry/penanganan.
4. Jalankan 1 transaksi sampling (draft -> bayar -> cek stok/mutasi).
5. Cek file backup terbaru dibuat pada jadwal yang benar.

### C. Keputusan Go/No-Go

- **GO (boleh lanjut perbaikan aplikasi)**: semua poin A hijau + verifikasi B aman selama 2 hari berturut-turut.
- **NO-GO (fokus infra dulu)**: ada 1 saja poin A merah, atau email/queue/subdomain gagal pasca-restart.

### D. Prioritas Saat No-Go

1. Pulihkan tunnel/subdomain dulu.
2. Pulihkan queue worker agar job tidak menumpuk.
3. Pulihkan SMTP agar notifikasi penting terkirim.
4. Baru lanjut investigasi bug aplikasi.

### E. Lembar Cek Siap Pakai

Gunakan lembar cek operasional harian ini untuk tim non-teknis:
- `CHECKLIST-OPERASIONAL-HARIAN.md`

### F. Cek Otomatis (Disarankan)

Untuk validasi cepat sebelum operasional harian, jalankan:

```bash
./scripts/ops-health-check.sh
```

Interpretasi hasil:
- `Status: GO` -> operasional boleh lanjut.
- `Status: GO DENGAN CATATAN` -> lanjut dengan pengawasan, tindak lanjuti warning.
- `Status: NO-GO` -> hentikan perubahan penting, selesaikan item FAIL terlebih dahulu.

### G. Apply Template Env (Tanpa Secret)

Gunakan template berikut sesuai environment server:
- `.env.production`
- `.env.staging`

Langkah apply di server:

```bash
cp .env.production .env
php artisan optimize:clear
php artisan config:cache
./scripts/ops-health-check.sh
```

Catatan penting:
- Isi secret SMTP Brevo (`MAIL_USERNAME`, `MAIL_PASSWORD`) langsung di server.
- Untuk uji akses tenant otomatis, isi `TENANT_SMOKE_DOMAIN` dengan 1 subdomain tenant aktif.
- Aplikasi ini juga bisa override mail config dari tabel system settings. Pastikan setting mail di Admin -> Settings juga `smtp`.

### H. Cek Harian Jarak Jauh (Dari Laptop)

Jalankan satu perintah ini dari root project untuk cek server produksi:

```bash
./scripts/ops-health-check-remote.sh
```

Opsional target lain:

```bash
./scripts/ops-health-check-remote.sh user@host
```

Script akan otomatis:
1. Cek status container app dan queue.
2. Menjalankan `scripts/ops-health-check.sh` di container aplikasi.
3. Menampilkan ringkasan failed jobs.

---

## 🔖 Standar Update Versi (GitHub + Server)

Tujuan: setiap perubahan rilis selalu punya versi yang konsisten di kode, Git tag, dan tampilan aplikasi.

### 1) Bump versi lokal

```bash
bash scripts/release-version.sh 1.0.1
```

Atau via composer:

```bash
composer run release:version -- 1.0.1
```

Perintah ini otomatis update:
- `VERSION`
- `.env.example` (`APP_VERSION=...`)

### 2) Commit & push

```bash
git add VERSION .env.example
git commit -m "chore(release): bump version to v1.0.1"
git push origin main
```

### 3) Rilis via GitHub Actions (manual)

Gunakan workflow:
- `.github/workflows/release-version.yml`

Input:
- `version` (format `MAJOR.MINOR.PATCH`, contoh `1.0.1`)

Workflow akan:
- validasi format versi
- update `VERSION` + `.env.example`
- commit
- buat tag `vX.Y.Z`
- buat GitHub Release

### 4) Sinkron server

Set di environment server:

```bash
APP_VERSION=1.0.1
```

Lalu jalankan:

```bash
php artisan optimize:clear
php artisan config:cache
```

Catatan: aplikasi juga punya fallback ke file `VERSION` jika `APP_VERSION` belum di-set.
