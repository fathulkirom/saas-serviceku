# MAIL-UNIFY-01 — One Mail Settings Form With Provider Switch

> Phase: MAIL-UNIFY-01 — True single mail configuration UI
> Date: 2026-08-08
> Mode: AUDIT → ONE PROVIDER MODEL → CONSOLIDATE UI → PRESERVE BACKWARD COMPATIBILITY → TEST → DEPLOY → STOP
> Owner decision: ONLY ONE visible mail configuration section: **EMAIL TRANSAKSIONAL** with Provider **Resend API / SMTP / Off**; fields change dynamically per provider.

---

## 1. Previous Architecture

Central Admin → Pengaturan had **two** mail areas:
1. **Email Transaksional (Resend)** — Provider (resend/off), API key, From, Reply-To, test recipient.
2. **Legacy SMTP / Advanced** (collapsed `<details>`) — separate SMTP config + its own test email.

Although SMTP was collapsed, two independent mail configuration areas + two test-mail inputs still existed.

## 2. Provider Model

One canonical provider setting, reused: `mail_resend_provider` (central `system_settings`, group `mail_resend`).
Allowed values extended: **`resend` | `smtp` | `off`** (validation: `nullable|in:off,resend,smtp`).

- `resend` → Resend HTTP API
- `smtp` → SMTP (reuses `MailConfigService` + legacy `mail_*` keys)
- `off` → disabled, honest failure

The legacy `mail_driver` is kept in sync automatically (provider=smtp → `mail_driver=smtp` so `MailConfigService::apply()` configures SMTP; otherwise `log`). No second provider setting introduced.

## 3. Resend Path

Provider=resend → `TransactionalMailService::deliver/sendTest` → **`ResendTransactionalMail`** → Resend HTTP API. OTP and test mail use the same path. The SMTP path never runs under provider=resend.

## 4. SMTP Path

Provider=smtp → `TransactionalMailService::deliverViaSmtp` → `MailConfigService::apply()` + default mailer `Mail::to()->send(...)`; `sendTest` → `MailConfigService::test()`. Reuses the existing SMTP backend (`MailConfigService`). Resend never runs under provider=smtp. Legacy SMTP values automatically populate the SMTP fields when provider=smtp.

## 5. Off Behavior

Provider=off → `deliver()` and `sendTest()` return `false` (honest failure). No provider runs. UI shows "Email transaksional dinonaktifkan." Test mail shows a clear error ("Provider transaksional nonaktif (Off)…").

## 6. UI Before / After

**Before:** two sections (Resend + collapsed Legacy SMTP), two test-mail inputs, duplicate From fields across the page.

**After (ONE section):**

```
EMAIL TRANSAKSIONAL
Provider: [ Resend API ▼ ]   (Resend API / SMTP / Off)
Status:  Configured / Not configured · Last test result

if RESEND:  Resend API Key · From Email · From Name · Reply-To (optional)
if SMTP:    SMTP Host · Port · Encryption · Username · Password · From Address · From Name
if OFF:     "Email transaksional dinonaktifkan."

Email Tujuan Tes  [ Kirim Test ]
[ Simpan Pengaturan ]
```

- No "Legacy SMTP / Advanced" block remains.
- No duplicate From Address / From Name.
- ONE test recipient + ONE test button + ONE save button.
- Provider switch is the single source of truth for which fields/status appear.

## 7. Secret Preservation

- **Resend API key:** encrypted at rest; masked on frontend; blank update preserves; never logged/serialized.
- **SMTP password:** now **masked from the frontend** (`mail_password` replaced with `••••••••` in the page props); the form stays empty with a "Tersimpan — kosongkan untuk mempertahankan" placeholder; blank/masked update **preserves** the stored password (previously it could be overwritten).
- **Switching provider never erases inactive credentials:** Resend key survives a switch to SMTP; SMTP password survives a switch back to Resend (proven by tests).

## 8. OTP Routing

`RegisteredTenantController@sendOtp` → `TransactionalMailService::sendOtp` → `deliver` → routes by the **selected** provider (resend/smtp/off). No silent cross-provider fallback; off/unconfigured → honest failure (no tenant provisioned).

## 9. Test-Mail Routing

One `admin.settings.test-mail` endpoint → `TransactionalMailService::sendTest` → routes by the selected provider (resend→Resend HTTP API, smtp→SMTP, off→honest error). The temporary recipient never alters Reply-To/From/credentials.

## 10. Tests

`php artisan test tests/Feature/Pilot/PilotMailSettingsTest.php` → **26 passed / 67 assertions**. New (MAIL-UNIFY-01):
- `test_provider_smtp_test_mail_uses_smtp` — SMTP path, Resend SystemTestMail NOT sent
- `test_provider_smtp_otp_uses_smtp` — OTP via SMTP delivers the real OtpMail
- `test_provider_off_sends_nothing` — neither provider runs
- `test_switching_provider_does_not_erase_resend_key`
- `test_switching_provider_does_not_erase_smtp_password`
- `test_smtp_password_is_masked_on_frontend`
- `test_settings_ui_has_single_provider_driven_mail_section` — one section, no legacy block, provider resend/smtp/off, single test recipient/handler

`php artisan test tests/Feature/Pilot/PlatformSyncTest.php` → **15 passed / 108 assertions**.
Broader mail/registration group → **80 passed / 334 assertions**. No regression.

## 11. Build

`npm run build` → **PASS**. New Settings chunk: `Settings-CCZrAPC-.js`.

## 12. Deployment

Committed `8b69d6e` (`feat: unify mail settings into one provider-driven form`), pushed to `origin/main` (`ef55e6e..8b69d6e`). Deployed via canonical **`./deploy.sh`** (exit 0) — rsync source + build, preserved `.env`, recreated `serviceku-app`, `migrate --force`, `optimize:clear`.

## 13. Live Verification

- Server source (host + container): single "Email Transaksional" section; **no** "Legacy SMTP / Advanced" / "Email (SMTP)"; provider options `resend / smtp / off`.
- Server manifest: Aug 7 19:40 (current); `Admin/Settings.vue → assets/Settings-CCZrAPC-.js`.
- Live: `https://kirom.serviceku.my.id/build/assets/Settings-CCZrAPC-.js` → **HTTP 200 (17,042 B)**; live manifest maps `Admin/Settings.vue → Settings-CCZrAPC-.js`.
- **Limitation:** authenticated Central Admin → Pengaturan click-through not automated (no admin credentials). Owner must visually confirm the single provider-driven section.

## 14. Final Verdict

**B — SINGLE PROVIDER-DRIVEN MAIL SETTINGS UI.**

- ONE visible "Email Transaksional" section with a single Provider switch (Resend API / SMTP / Off); fields render dynamically per provider.
- No second/legacy SMTP section, no duplicate From/test/save, one status.
- OTP and test mail follow the SAME selected provider; no cross-provider fallback.
- Secrets (Resend key + SMTP password) masked and preserved across provider switches.
- Tests + build + deploy + live asset verification all pass.

No real Resend key was configured, no production test email was sent during this phase.

---

## STOP

After B: **STOP.** Report returned to owner. Do not configure real Resend credentials automatically.
**Next owner action:** open Central Admin → Pengaturan → Email Transaksional, confirm the single provider switch, then configure the real provider credentials manually.
