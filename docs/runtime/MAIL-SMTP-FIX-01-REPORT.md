# MAIL-SMTP-FIX-01 — Preserve Legacy SMTP When Provider Is Resend/Off

> Date: 2026-08-08
> Owner report (informal): "kok smtp belum jalan ya saat aku pakai provider resend" (why doesn't SMTP work when I use the Resend provider?)

---

## Diagnosis (verified from live server state)

Live `system_settings` (masked):
- `mail_resend_provider = NULL` → provider is effectively **off** (Resend was never persisted)
- `mail_resend_api_key = (empty)` → no Resend API key configured
- `mail_driver = 'log'` ← **caused by the MAIL-UNIFY-01 save handler**
- `mail_host = 'smtp.resend.com'` (owner's legacy SMTP config still stored)
- `mail_resend_last_test_result = 'failed'`

Two facts:
1. **Provider=Resend only drives transactional mail (OTP + "Kirim Test")** and it uses **Resend API only** — that is the owner's own decision (MAIL-UNIFY-01: "Provider=Resend → SMTP must never run"). Because the provider is actually off + no Resend key, transactional mail honestly fails. This is correct/by-design, not a bug.
2. **Regression introduced by MAIL-UNIFY-01:** the save handler forced `mail_driver='log'` whenever the transactional provider ≠ smtp. That silently disabled the **legacy default-mailer SMTP** used by non-transactional `Mail::` paths (welcome email, invoice email, tenant notifications). This was unintended and is what "SMTP tidak jalan" referred to.

## Fix

`app/Http/Controllers/Admin/SystemSettingsController.php`:
- `mail_driver` is now set to `smtp` **only when** the transactional provider is explicitly `smtp`.
- For `resend`/`off`, `mail_driver` is left **untouched** → legacy SMTP for non-transactional mail is preserved.

Live data corrected: `mail_driver` restored to `smtp` (matches the stored `smtp.resend.com` config) so welcome/invoice/notification emails work again via the legacy SMTP path, independently of the transactional provider.

## Verification

- `PilotMailSettingsTest` → **27/27** (new: `test_provider_resend_preserves_legacy_smtp_driver`)
- `PlatformSyncTest` → **15/15**
- `npm run build` → PASS (no UI change)
- Commit `cb62736`, pushed; deployed via `./deploy.sh` (exit 0); container healthy; live `mail_driver=smtp`, `provider=off`, `host=smtp.resend.com`.

## Owner guidance

- **To use Resend for transactional mail:** open Pengaturan → Email Transaksional → Provider = **Resend API** → isi **Resend API Key** (asli) + From Email/Name → **Simpan**. Setelah itu OTP & "Kirim Test" akan lewat Resend.
- **Legacy SMTP** (welcome/invoice/notifikasi) kini tetap jalan via `smtp.resend.com` terlepas dari provider transaksional.
- Jika ingin transaksional lewat **SMTP**, pilih Provider = **SMTP** (field SMTP muncul otomatis).

## Verdict

Root cause identified (provider off + no Resend key → honest failure by design) + regression fixed (legacy SMTP no longer disabled by the provider switch).
