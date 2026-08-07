# PILOT-MAIL-04R — CENTRAL MAIL SETTINGS + RESEND OTP

**Tanggal**: 2026-08-07
**Mode**: AUDIT EXISTING MAIL ARCHITECTURE → ADD MINIMAL CENTRAL MAIL CONFIG → RESEND OTP → REAL DELIVERY TEST → STOP

---

## 1. Existing Mail Architecture

- **Mailer default**: `config/mail.php` → `default => env('MAIL_MAILER', 'log')`; mailer `resend` sudah didefinisikan (transport `resend`).
- **`config/services.php`**: `resend => ['key' => env('RESEND_KEY')]` sudah ada.
- **`MailConfigService`** (`app/Services/MailConfigService.php`): menerapkan konfigurasi SMTP dari DB (`SystemSetting`) di `AppServiceProvider::boot()` + method `test()`.
- **OTP lama**: `RegisteredTenantController::sendOtp()` memakai `Mail::to(...)->send(new OtpMail(...))` langsung (belum lewat abstraksi).
- **`OtpMail`** mailable + blade `emails/otp` (kode 6 digit, 15 menit).
- **Brevo**: disebut sebagai legacy (SMTP) di beberapa dokumen; tidak dijadikan fallback otomatis untuk OTP.
- **Provider terpisah**: `GoogleDrivePhotoService` (Drive) — **tidak terkait** mail.

## 2. Central Settings Architecture Reused

- **Tabel** `system_settings` (key/value/group) pada koneksi `central` + model **`SystemSetting`** (`getValue`/`setValue`/`getGroup`).
- **Central Admin** sudah ada: prefix `/admin` (`web.php`) + middleware `admin.auth` (`AdminAuthenticate`, guard `web` terhadap tabel `users` central = **platform admin only**).
- **`SystemSettingsController`** (Admin) + halaman **`Admin/Settings.vue`** (grup `general`, `registration`, `maintenance`, `mail`).
- **Tidak dibuat framework settings kedua** — semua ditambahkan ke arsitektur yang ada (grup `mail_resend`).

## 3. Provider

- **Resend API** (melalui transport `resend` bawaan Laravel — berbasis HTTP API, tanpa IP publik statis).
- Field: provider (`resend`/`off`), API key, from address, from name, reply-to.
- UI: Central Admin → Pengaturan → **Transactional Mail (Resend)** — bagian baru di `Admin/Settings.vue`.
- Tidak ada fitur marketing/campaign.

## 4. Secret Storage (keamanan)

- API key disimpan **terenkripsi at rest** via `encrypt()` (AES-256, APP_KEY) — konsisten dengan pola `TwoFactorController` (recovery codes).
- `index()` mengirim **masked** (`status()`) — `has_api_key` + `masked_api_key`, **tidak pernah** key mentah.
- Key tidak di-log, tidak di URL, tidak diserialisasi ke Inertia/frontend.
- Otorisasi: hanya platform admin (`admin.auth` + tabel users central). Tenant CS/Owner/Manager/Admin tidak bisa akses.
- Edit: **kosongkan API key = pertahankan secret lama**; isi = replace (enkripsi).

## 5. ENV Fallback

- Urutan: 1) setting platform (`mail_resend_api_key`) → 2) env (`RESEND_KEY` → `config('services.resend.key')`) → 3) tidak tersedia → gagal jujur (tidak ada provisioning).
- Tidak perlu konfigurasi DB untuk boot (fallback aman di `AppServiceProvider`).

## 6. Resend Integration

- **`TransactionalMailService`** (`app/Services/TransactionalMailService.php`) — abstraksi mail transaksional tunggal: `resendApiKey()`, `isResendConfigured()`, `status()`, `sendOtp()`, `deliver()`, `sendTest()`.
- **`ResendTransactionalMail`** (`app/Services/Mail/ResendTransactionalMail.php`) — provider Resend: set `mail.default=resend`, `services.resend.key`, `mail.from` + reply-to saat runtime, lalu `Mail::mailer('resend')->send(...)`.
- **`SystemTestMail`** mailable + blade `emails/system-test`.
- OTP memanggil abstraksi, bukan Resend langsung di controller.

## 7. Domain Verification

- **Belum diverifikasi** — tidak ada akun/domain Resend yang dikonfigurasi (tidak ada kredensial).
- Rekomendasi sender: `noreply@serviceku.my.id` — hanya dipakai setelah Resend melaporkan domain terverifikasi.
- **Klasifikasi: EXTERNAL CONFIG BLOCKER** (bukan bug kode).
- DNS yang perlu ditambahkan owner di Cloudflare (dari dashboard Resend → Domains):
  1. **SPF** TXT: nilai dari Resend (biasanya `v=spf1 include:amazonses.com ~all` atau milik Resend).
  2. **DKIM** TXT (3 record): token dari Resend (`resend._domainkey`, `resend2._domainkey`, `resend3._domainkey`).
  3. Tidak otomatis mengubah DNS (sesuai aturan).

## 8. Test Mail

- Tombol **"Kirim Email Tes"** di Central Admin → `POST admin.settings.test-mail` → `TransactionalMailService::sendTest()`:
  - provider `resend` aktif → `ResendTransactionalMail::sendRawTest` (SystemTestMail).
  - tidak → fallback `MailConfigService::test`.
- **Hasil jujur**: sukses hanya jika pengiriman benar-benar berhasil; gagal → flash error + `mail_resend_last_test_result=failed`. Bukan "berhasil karena masuk antrean".

## 9. OTP Integration

- Alur kanonik: `/register` → `sendOtp()` → `TransactionalMailService::sendOtp()` → (Resend bila terkonfigurasi / env fallback) → simpan pending (`RegistrationVerification`) → owner masukkan OTP → `verifyOtp()` → **tenant baru dibuat**.
- **Tenant TIDAK dibuat sebelum OTP diverifikasi** (teruji).

## 10. OTP Security

- OTP 6 digit, `random_int`, **expired 15 menit**.
- **Salah/expired/reused ditolak** (`verifyOtp`: `verified_at` null + `expires_at >= now`; reuse → null).
- **Resend invalidates OTP lama** (`generateOtp` menghapus record email yang sama).
- **Rate limiting**: `throttle:register` (send/resend), `throttle:otp`.
- OTP tidak di-log, tidak dikembalikan ke browser, tidak di URL.
- (Teruji di `RegistrationVerificationTest` + `PilotMailSettingsTest`.)

## 11. Automated Tests

`tests/Feature/Pilot/PilotMailSettingsTest.php` — **11/11 PASS (28 assertions)**:
1. hanya platform admin bisa lihat mail settings ✅
2. tenant owner/guest tidak bisa lihat ✅
3. API key tersimpan terenkripsi ✅
4. frontend tidak menerima key penuh ✅
5. blank key mempertahankan key lama ✅
6. test email memanggil Resend (mock) ✅
7. failure provider ditampilkan jujur ✅
8. OTP lewat abstraksi mail transaksional ✅
9. penerima benar menerima request OTP ✅
10. kegagalan mail tidak membuat tenant ✅
11. OTP diverifikasi sekali (reused ditolak) ✅

> Mock membuktikan jalur kode, **bukan** pengiriman nyata. Real delivery = uji owner (STEP 14).

## 12. Real Delivery

- **Belum dilakukan** — tidak ada Resend API key/domain terkonfigurasi.
- Setelah owner mengonfigurasi (Central Admin → Mail Settings → Save → Kirim Email Tes), lakukan uji `/register` nyata sampai OTP tiba di inbox.
- **Jangan menandai PASS dari mock.**

## 13. Remaining Owner Actions

1. Tambah `serviceku.my.id` di Resend (dashboard Resend).
2. Tambah record DNS (SPF + DKIM) di Cloudflare.
3. Tunggu hingga domain **verified** di Resend.
4. Generate **sending-only** Resend API key.
5. Buka **Central Admin** (`kirom.serviceku.my.id/admin/login`) → Pengaturan → **Transactional Mail (Resend)**.
6. Paste API key, set sender (`noreply@serviceku.my.id`), nama, reply-to.
7. **Simpan** → **Kirim Email Tes**.
8. Uji `/register` sampai OTP nyata tiba.

> Tidak ada kredensial yang diminta untuk di-commit ke repo.

## 14. Pilot Registration Status

- Registrasi pilot (`toko-kirom`) **masih tertahan** di langkah OTP — mail transaksional belum terkonfigurasi (Resend external).
- Setelah Resend aktif + OTP terkirim nyata, registrasi bisa diselesaikan.

---

## FINAL VERDICT

**A — MAIL SETTINGS READY BUT RESEND EXTERNAL CONFIG REQUIRED**

- Kode central mail settings selesai + teruji (11/11), build PASS, tanpa regresi (33 test relevan PASS).
- **Blocker**: akun/domain/API key **Resend** belum dikonfigurasi (external config), dan belum ada real delivery test.
- Menunggu owner menyelesaikan §13 (Resend → DNS → API key → Save → Test) → lalu uji OTP nyata → verdict C.
