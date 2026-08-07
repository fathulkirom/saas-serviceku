# PILOT-PROVISION-03 — REAL STORE TENANT PREPARATION

**Tanggal**: 2026-08-07
**Mode**: VERIFY EXISTING INFRA → PROVISION ONE REAL PILOT TENANT → SMOKE TEST → STOP

---

## 1. Existing Wildcard DNS/Tunnel

**Verdict: A — wildcard sudah mendukung tenant baru.**

- Tunnel Cloudflare berjalan (systemd `cloudflared-serviceku` → origin `localhost:8081` di server produksi remote `/home/kirom/serviceku`; kredensial `~/.cloudflared/`).
- **Verifikasi live**: subdomain acak `https://provisiontest-abc123.serviceku.my.id/login` **berhasil resolve via HTTPS dengan TLS valid** (bukan error DNS/cert) — merender halaman cari toko. Artinya wildcard `*.serviceku.my.id` (DNS + sertifikat + ingress tunnel) sudah aktif.
- **Tidak perlu membuat hostname/ingress tunnel baru** untuk tenant pilot.
- `RUNBOOK.md` sudah menyatakan `*.serviceku.my.id` resolve ke tunnel aktif — kini **terbukti di runtime**.

> Catatan: origin live adalah **server remote** (bukan mesin lokal ini). Kredensial DB/tunnel lokal tidak sama dengan produksi.

## 2. Pilot Tenant Selected

- Slug terpilih: **`toko-kirom`** → subdomain `toko-kirom.serviceku.my.id`.
- Nama toko yang menghasilkan slug tersebut: **"Toko Kirom"** (auto-slug dari nama di registrasi).
- **`kirom` TIDAK dipakai** — di-reserved sebagai subdomain Central Management/Super Admin (`web.php` memblokir slug `kirom` + reserve list `['admin','kirom','www','api','mail','ftp','dev','staging','test','demo','app','web','blog','shop','help','support']`).
- Tidak ada tenant toko yang cocok untuk di-reuse (hanya `toko-servis-abc` demo lokal; di produksi belum ada subdomain toko tenant).

## 3. Registration / Provisioning Result

- **Jalur kanonik**: `https://serviceku.my.id/register` — **terverifikasi fungsional** (form paket + data toko + OTP email).
- **Kode provisioning (`RegisteredTenantController::verifyOtp`) terbukti lengkap & benar**:
  - `Tenant::create` (auto-create tenant DB) → `tenancy()->initialize` → `app('migrator')->run(database_path('migrations/tenant'))` → create branch "Cabang Utama" + owner user (role `owner`) + `tenant_settings` → auto-slug → create domain `{slug}.{baseDomain}` → auto-login owner → redirect `{slug}.{domain}/login`.
  - Tidak ada seed/demo data.
- **Pemblokir satu-satunya (bukan bug kode)**: langkah **OTP email** di `sendOtp` — kode dikirim ke email pendaftar (Brevo). Otomasi agen tidak dapat menerima email OTP, dan sesuai aturan keamanan tidak boleh melewati/bypass OTP.
- **Aksi owner (2 menit) untuk mencapai verdict B**:
  1. Buka `https://serviceku.my.id/register`
  2. Pilih paket **Pro** (lihat §7), tipe bisnis `full_service`
  3. Nama toko **"Toko Kirom"**, nama owner, email owner, password
  4. Masukkan OTP dari email → tenant `toko-kirom` ter-provision otomatis → redirect ke `toko-kirom.serviceku.my.id/login`

## 4. Tenant Domain

- `toko-kirom.serviceku.my.id` — dicakup wildcard (verifikasi §1). Domain row dibuat otomatis oleh `verifyOtp` (`{slug}.serviceku.my.id`).
- Central domain: `serviceku.my.id` (config `tenancy.central_domains.0`).

## 5. Tenant Database

- Model: DB per tenant (stancl), nama DB = tenant id (mis. `tenant_demo` lokal; produksi `tenant_<id>`).
- Schema tenant **terverifikasi lengkap** (demo tenant lokal `tenant_demo` setelah migrasi): tabel `delegations`, `user_branches`, `branch_visibility`, `sale_refunds`, kolom warranty/rework/refund/supplier, dsb.
- Tidak ada dependensi demo seed; tidak ada data demo besar.

## 6. Migrations

- Central: ✅ up to date (1 pending Sanctum diterapkan fase ini).
- Tenant: ✅ up to date (8 pending BR-FIX diterapkan ke `tenant_demo` fase ini; `tenants:migrate`).
- **Tanpa `migrate:fresh`**; hanya migrasi aditif.

## 7. Plan

- **WAJIB Pro** (atau Enterprise). Perbandingan fitur:
  - Trial: `users=none`, `sales=read_only`, `max_users=1` → tidak layak.
  - **Basic**: `sales=full` tetapi **`users=read_only`** → owner **tidak bisa membuat user** (POST `users.store` diblokir 403 oleh `CheckPlanFeature`), bertentangan dengan iklan "Maks. 3 karyawan" → **tidak layak untuk pilot berstaf**.
  - **Pro**: `users=full`, `sales=full`, `max_users=10` ✅
  - Enterprise: `users=full`, `sales=full` ✅
- **Trial tidak diubah.**

## 8. Owner Login

- Belum dapat diuji di produksi (tenant belum dibuat). Alur login terverifikasi: `LoginController::create` → `Auth/SubdomainLogin` (email/password) → `store` → dashboard.
- Terbukti berfungsi lokal (`toko-servis-abc.localhost/login` → 200 + render `SubdomainLogin`).

## 9. Tenant Entry Redirect

- `https://serviceku.my.id/masuk` ✅ live. `TenantLookupController` → redirect ke `{scheme}://{slug}.{centralDomain}/login`.
- Setelah tenant `toko-kirom` dibuat, pencarian "Toko Kirom" → `https://toko-kirom.serviceku.my.id/login`.

## 10. Cloudflare HTTPS

- HTTPS + TLS untuk `*.serviceku.my.id` ✅ (terverifikasi subdomain acak).
- Central landing/masuk/register ✅ live.
- Tidak ada mixed-content/redirect-loop yang teramati.

## 11. Session / CSRF

- `SESSION_DOMAIN=.serviceku.my.id` (dot-prefix → central + subdomain tenant), `same_site=lax`, CSRF aktif. ✅

## 12. Google Auth Status

- Konfigurasi benar (callback `https://serviceku.my.id/auth/google/callback`, terpisah dari Google Drive).
- **Belum diuji live** (butuh keputusan owner + akun Google). Diklasifikasikan terpisah dari `GoogleDrivePhotoServiceTest` (external, non-blocking).

## 13. Role Selector

- ✅ Selesai fase sebelumnya: role resmi (Owner/Admin/Manager/CS/Teknisi/Kasir) tampil lebih dulu; `head_store`/`courier` diberi label **(legacy)**; `custom` tetap di backend. Tidak ada user historis dihapus.

## 14. Minimum Pilot Data (setelah login owner)

Menggunakan UI (bukan seeder), buat hanya:
- Branch: "Cabang Utama" (auto di provisioning) — sudah ada.
- User: 1 CS, 1 teknisi, 1 manager, 1 kasir (via Sistem → Pengguna; **Pro** memungkinkan).
- Produk/sparepart sampel: 2–3 (via Inventaris).
- Checklist: 1 jika dipakai (opsional).
- Payment method: cash/transfer sudah didukung (hardcoded + fallback).

## 15. Smoke Test

- **Produksi**: belum bisa — tenant belum dibuat (butuh langkah OTP owner, §3).
- **Lokal (bukti app operational)**: `toko-servis-abc` boot → login 200 → `PilotStoreOperationalTest` full journey PASS (9/9) → full suite 569 PASS. App siap; tinggal tenant produksi.

## 16. Backup

- **Mekanisme produksi**: `backup.sh` (Docker, di server remote) → `storage/backups/databases/{master,tenant_*}_<ts>.sql.gz`, retention 30 hari, cron 03:00.
- **Restore**: `./backup.sh --restore FILE` (atau `gunzip < file.sql.gz | docker exec -i serviceku-mysql mysql ...`).
- **Dibuat fase ini (demo lokal)**: `storage/backups/databases/master_2026-08-07_22-23-00.sql.gz` + `tenant_demo_2026-08-07_22-23-00.sql.gz` (gzip valid).
- **Wajib**: setelah tenant `toko-kirom` dibuat, owner menjalankan `backup.sh` di produksi SEBELUM UAT manusia (backup central + tenant baru). Kredensial tidak disimpan di repo.

## 17. Human UAT Readiness

- Infra (wildcard/HTTPS/session) ✅.
- Jalur provisioning kanonik ✅ (terverifikasi kode + form live).
- Plan Pro ✅ (Basic tidak layak untuk staf — temuan P1 plan-config).
- Role selector ✅.
- Smoke E2E lokal ✅.
- **Satu langkah tersisa**: owner menyelesaikan registrasi `toko-kirom` (email OTP) → tenant otomatis live → B tercapai.

---

## FINAL VERDICT

**A — NOT READY — PROVISIONING BLOCKER REMAINS** *(blocker = langkah OTP owner, bukan bug kode)*

- Tidak ada P0/P1 **code** blocker yang tersisa.
- Tenant toko produksi **belum dibuat**; satu-satunya hal yang mencegah verdict B adalah langkah manusia wajib: menyelesaikan `https://serviceku.my.id/register` (paket **Pro**, nama toko "Toko Kirom" → slug `toko-kirom`) + memasukkan OTP email.
- Setelah langkah tersebut (2 menit, oleh owner), verifikasi ulang: `toko-kirom.serviceku.my.id/login` login owner → dashboard → buat data minimal (§14) → mulai Human UAT. **Verdict B** tercapai saat subdomain tenant bekerja eksternal.

**Dilarang saat fase ini**: menambah fitur, mengubah routing/kirom, recreate tunnel, regenerate OAuth, migrate:fresh, hapus data tenant, dan manual DB/domain rows (flow registrasi tidak rusak).
