# PILOT-UAT-RESULT — SERVICEKU v1.0

**Status**: 🟡 **IN PROGRESS** — menunggu pelaksanaan UAT manusia di toko pilot.

> Document ini diisi **hanya setelah eksekusi manusia**. Tidak ada PASS otomatis dari PHPUnit.

**Pilot tenant (PILOT-PROVISION-03)**: **`toko-kirom`** → `toko-kirom.serviceku.my.id/login` (paket **Pro**; `users=full`).
- ⚠️ **Basic** `users=read_only` → owner tidak bisa membuat user (P1 plan-config). Jangan pakai Basic untuk pilot berstaf.
- **Status provisioning**: tenant produksi **belum dibuat** — tunggu owner menyelesaikan `https://serviceku.my.id/register` (Pro, nama toko "Toko Kirom") + OTP email. Infra wildcard + jalur provisioning sudah diverifikasi.

**Alur masuk nyata (diverifikasi)**: `serviceku.my.id` → `/masuk` → redirect `toko-kirom.serviceku.my.id/login` → login email/password. Registrasi: `/register`. `/admin/login` = Central Management/Super Admin (bukan login toko).

---

## 1. Infrastructure Verification (PILOT-UAT-02 — otomatis, bukan human UAT)

| Item | Hasil | Bukti |
|---|---|---|
| Localhost boot | ✅ Laravel 12.64.0 / PHP 8.5.8 boot | `php artisan about` |
| Landing lokal `/` | ✅ 200 | curl |
| `/masuk` lokal | ✅ 200 | curl |
| `/register` lokal | ✅ 200 | curl |
| Tenant login lokal (`toko-servis-abc.localhost/login`) | ✅ 200, render `SubdomainLogin` + csrf + app.js | curl |
| Tenant root lokal | ✅ 302 → login | curl |
| Vite/assets manifest | ✅ 200 (`/build/manifest.json`) | curl |
| Health | ✅ app+db+queue+storage OK; Redis ext tidak ada (fallback file cache — bukan blocker) | `/health` |
| Central migrations | ✅ up to date (1 pending Sanctum diterapkan) | `migrate:status` |
| Tenant migrations (`tenant_demo`) | ✅ up to date (8 pending BR-FIX diterapkan) | `tenants:migrate` |
| Public landing (tunnel) | ✅ `https://serviceku.my.id` termuat (HTTPS, plans, live desk) | fetch live |
| Tenant entry (tunnel) | ✅ `https://serviceku.my.id/masuk` termuat | fetch live |
| Registration (tunnel) | ✅ `https://serviceku.my.id/register` termuat | fetch live |
| Tenant subdomain pilot | ⚠️ **TIDAK ada subdomain toko tenant yang ter-provision** — `kirom.serviceku.my.id` ter-resolve ke **Central Admin Panel** (`/admin/login`), dan `/login` di subdomain itu 404. Ini karena `kirom` = subdomain admin platform (bukan toko tenant) di kode deploy. | browser live |
| Session config | ✅ `SESSION_DOMAIN=.serviceku.my.id` (dot-prefix → berlaku central + subdomain tenant); `same_site=lax` | `.env` / `config/session.php` |
| Google OAuth | ⚠️ Konfigurasi benar: callback `https://serviceku.my.id/auth/google/callback` (dari `APP_URL`/`GOOGLE_REDIRECT_URI`); alur `redirect → callback → resolve tenant → login`. **Belum diuji live login Google** (butuh kredensial + keputusan owner). Terpisah dari Google Drive. | `GoogleLoginController` / `config/services.php` |
| Trusted proxies | ℹ️ `TrustProxies` no-op; HTTPS live bekerja (kemungkinan lewat APP_URL/terminal proxy). Tidak diubah spekulatif. | `app/Http/Middleware/TrustProxies.php` |

## 2. Public Landing — (menunggu human UAT)

## 3. Tenant Entry — (menunggu human UAT)

## 4. Registration — (menunggu human UAT)

## 5. Tenant Login — (menunggu human UAT)

## 6. Google Auth — (menunggu human UAT; butuh keputusan owner untuk live test)

## 7. CS UAT — (menunggu human UAT — lihat `PILOT-UAT-RUNBOOK.md` §B)

## 8. Technician UAT — (menunggu human UAT — §C)

## 9. Manager UAT — (menunggu human UAT — §D)

## 10. Cashier UAT — (menunggu human UAT — §E)

## 11. Owner UAT — (menunggu human UAT — §F)

## 12. Warranty UAT — (menunggu human UAT — §G)

## 13. Mobile/Tablet — (menunggu human UAT — §H)

## 14. Bugs Found

| ID | Area | Severity | Deskripsi | Status |
|---|---|---|---|---|
| *(kosong — diisi saat UAT)* | | | | |

## 15. P0/P1 Fixes

*(diisi saat UAT)*

## 16. Deferred P2/P3

*(diisi saat UAT)*

## 17. Final Full Regression

`php artisan test` — hasil akhir PILOT-READY-01 (baseline sebelum human UAT):
**569 passed · 1 failed (Google Drive external/non-blocking) · 6 incomplete · 1851 assertions · 1117.49s**.
*(Jalankan ulang setelah setiap P0/P1 fix UAT dan catat di sini.)*

## 18. Pilot Verdict

**Status**: IN PROGRESS — verdict menunggu hasil eksekusi manusia.

---

## Catatan infrastruktur penting (untuk persiapan pilot)

1. **Siapkan tenant toko pilot yang benar**: `kirom` adalah subdomain **admin platform** (bukan toko). Tenant toko pilot butuh subdomain sendiri (mis. `{slug}.serviceku.my.id`) + record DNS/tunnel + DB tenant, dan login di `{slug}.serviceku.my.id/login`. Ini tindakan provisioning owner — bukan perubahan kode.
2. **Paket Basic/Pro** (bukan Trial) agar invoice/payment aktif.
3. **Google Auth ≠ Google Drive**. Kegagalan `GoogleDrivePhotoServiceTest` TIDAK berarti Google login rusak.
4. **Tidak ada perubahan routing/DNS/kredensial** selama fase ini (sesuai STEP 2/17).
