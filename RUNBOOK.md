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

## 🔧 Langkah 1 — Backup Database (Blocker #1)

> Kenapa manual: backup DB memakai `docker exec mysqldump` yang hanya tersedia **di HOST**,
> bukan di dalam container app (tidak ada docker CLI / mysqldump di sana).

### 1.1 Perbaiki permission direktori backup (sekali saja)

SSH ke server, lalu jalankan:

```bash
sudo chown -R kirom:www-data /mnt/hdd/Backup/ServiceKU /home/kirom/serviceku/storage
sudo chmod -R g+w /mnt/hdd/Backup/ServiceKU /home/kirom/serviceku/storage
```

> Ketik password sudo saat diminta. Pastikan tidak ada error.

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
ls -lh /mnt/hdd/Backup/ServiceKU/databases/ | tail -5
```

> Jika gagal `mkdir(): Permission denied` → ulangi Langkah 1.1.
> Jika error lain, catat pesannya dan kirim ke saya.

### 1.3 Pasang cron host (backup otomatis tiap 03:00)

```bash
crontab -e
```

Tambahkan baris berikut, lalu simpan:
- **nano**: tempel baris → `Ctrl+O` → `Enter` → `Ctrl+X`
- **vi/vim**: `i` → tempel baris → `Esc` → `:wq` → `Enter`

```
0 3 * * * /home/kirom/serviceku/backup.sh --auto >> /var/log/serviceku-backup.log 2>&1
```

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
