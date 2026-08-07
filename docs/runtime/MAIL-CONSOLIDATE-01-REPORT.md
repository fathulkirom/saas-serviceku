# MAIL-CONSOLIDATE-01 — One Canonical Transactional Mail UI

> Phase: MAIL-CONSOLIDATE-01 — Remove SMTP/Resend UI confusion without breaking existing mail
> Date: 2026-08-08
> Mode: AUDIT → DEFINE CANONICAL PROVIDER → CONSOLIDATE UI → PRESERVE BACKWARD COMPATIBILITY → TEST → DEPLOY → STOP
> Locked decision: canonical platform transactional provider = **Resend HTTP API**

---

## 1. Existing Mail Callers

| Caller | Purpose | Path (before) | Pilot-critical |
|---|---|---|---|
| `RegisteredTenantController@sendOtp` | Registration OTP | `TransactionalMailService::sendOtp` | ✅ Yes |
| `TransactionalMailService::deliver` | OTP/transactional delivery | Resend if configured → **else default mailer (SMTP)** | ✅ Yes |
| `TransactionalMailService::sendTest` | Central Admin test mail | Resend if configured → **else `MailConfigService::test` (SMTP)** | ✅ Yes |
| `RegisteredTenantController@verifyOtp` | Welcome email | `Mail::to()->send(WelcomeMail)` (default mailer) | ⚠️ non-blocking (try/catch) |
| `RegisteredTenantController@resendOtp` | OTP resend | `Mail::to()->send(OtpMail)` (default mailer) | ⚠️ |
| `Jobs/SendInvoiceEmail` | Invoice email (tenant) | `Mail::send([], ...)` (default mailer) | ❌ tenant feature |
| `app/Services/ProviderAdapter` | Provider raw mail | `Mail::raw` (default mailer) | ❌ |
| `User` / `ResetPasswordController` | Verify/2FA/Reset notifications | `->notify(...)` (default mailer) | ❌ |
| `TenantOtp` model | Tenant-level OTP | `Mail::html` (default mailer) | ❌ |
| `MailConfigService::apply` | Apply DB SMTP config to default mailer | AppServiceProvider boot + settings update | legacy |

## 2. Legacy SMTP Dependencies

- **Legacy SMTP** (DB `mail_*` settings via `MailConfigService::apply`) was the default-mailer config used by **non-pilot-critical** paths: welcome email, OTP resend, invoice email, tenant notifications, provider raw mail.
- **Pilot-critical transactional mail (OTP + Central Admin test mail)** already routed through `TransactionalMailService` — but with a **silent fallback to the legacy SMTP/default mailer** when Resend wasn't configured.
- **Conclusion:** No pilot-critical runtime depends on legacy SMTP as its intended path. The fallback was the ambiguity to remove. Legacy SMTP remains as backward-compat for the non-transactional `Mail::` paths.

## 3. Canonical Mail Path (after)

```
Controller/domain action
→ TransactionalMailService (sendOtp / sendTest / deliver)
→ ResendTransactionalMail (HTTP API transport)
→ Resend API
```

- OTP continues through `TransactionalMailService::sendOtp` → `deliver` → `ResendTransactionalMail`.
- Central Admin "Kirim Email Tes" → `TransactionalMailService::sendTest` → `ResendTransactionalMail::sendRawTest` — the **same** canonical path as OTP. It no longer silently tests SMTP.
- **No silent SMTP fallback.** Provider=off or unconfigured Resend → honest failure (`false`).

## 4. UI Before

Two competing normal sections in Central Admin → Pengaturan:
1. **Email (SMTP)** — Mail Driver, Host, Port, Encryption, Username, Password, From Address, From Name, Test Email (was configured for `smtp.resend.com` — "Resend via SMTP")
2. **Transactional Mail (Resend)** — Provider, API Key, From Email, From Name, Reply-To, Test Email

This exposed "Resend via SMTP" AND "Resend via HTTP API" simultaneously.

## 5. UI After

- **One canonical normal section: "Email Transaksional (Resend)"** — Provider (Resend/Off), Status badge, Resend API Key, From Email, From Name, Reply-To (optional), Email Tujuan Tes, [Simpan], [Kirim Email Tes].
- **Legacy SMTP collapsed** under a clearly-labeled `<details>`: **"🔧 Legacy SMTP / Advanced — tidak digunakan untuk OTP Resend"** (kept for backward compatibility only; still editable if the owner truly needs it for non-transactional mail).
- Provider options limited to **Resend / Off** — SMTP is **not** a normal provider.
- No duplicate From Email / From Name across two normal sections.

## 6. Legacy Compatibility Decision

- `MailConfigService` (`apply()` + `test()`) is **retained** (not deleted) for the legacy `Mail::`/notification paths (welcome, invoice, tenant notifications).
- `MailConfigService::apply()` still runs at boot and on settings save — legacy SMTP config remains functional for those paths.
- The SMTP section is **hidden/collapsed** in the normal UI, explicitly labeled legacy and NOT used for OTP Resend.
- Proven executable: `test_legacy_mail_config_service_still_functional`.

## 7. Test-Mail Path

- Provider=**Resend** → `TransactionalMailService::sendTest($to)` → `ResendTransactionalMail::sendRawTest` → Resend API. **Never** legacy SMTP.
- Provider=**Off** → honest error: *"Provider transaksional nonaktif (Off). Aktifkan Resend..."*
- Provider=**Resend but unconfigured** (no key/from) → honest error: *"Resend belum dikonfigurasi. Lengkapi Resend API Key dan From Email..."*
- Reply-To stays persistent; Email Tujuan Tes stays a temporary recipient only (MAIL-UI-FIX-01 preserved).

## 8. OTP Path

- `RegisteredTenantController@sendOtp` → `TransactionalMailService::sendOtp` → `deliver` → **Resend HTTP API** (provider=resend).
- Provider=off / unconfigured → **honest failure** (no tenant provisioned, error returned). No silent SMTP fallback.
- `resendApiKey()` keeps the DB-setting → `RESEND_KEY` env fallback for the key only (intentional), never arbitrary SMTP.

## 9. Tests

`php artisan test tests/Feature/Pilot/PilotMailSettingsTest.php` → **19 passed / 45 assertions**. New (MAIL-CONSOLIDATE-01):
- `test_provider_off_test_mail_fails_honestly` — Off does NOT fall back to SMTP
- `test_resend_unconfigured_test_mail_fails_honestly` — resend without key fails honestly
- `test_otp_with_provider_off_fails_honestly` — OTP does NOT fall back to SMTP
- `test_legacy_mail_config_service_still_functional` — legacy backend retained

Existing coverage retained: OTP via abstraction, Resend test path, encrypted/masked key, reply-to ↔ test-recipient separation, blank reply-to, key retention.

`php artisan test tests/Feature/Pilot/PlatformSyncTest.php` → **15 passed / 108 assertions** (registration test updated to canonical Resend path).

Broader mail/registration regression (Pilot dir, MailContent, TenantOtp, FeatureFlag, RegistrationVerification) → **73 passed / 312 assertions**.

## 10. Build

`npm run build` → **PASS**. New Settings chunk: `Settings-DAQyA3cy.js`.

## 11. Deployment

Committed `3da7a6c` (`feat: consolidate transactional mail on Resend HTTP API`), pushed to `origin/main` (`001e91d..3da7a6c`). Deployed via canonical **`./deploy.sh`** (exit 0) — rsync source + build, preserved `.env`, recreated `serviceku-app`, `migrate --force`, `optimize:clear`.

## 12. Live Verification

- Server source (host + container): "Email Transaksional (Resend)", "Legacy SMTP / Advanced", `testEmailRecipient` present; `TransactionalMailService` uses `PROVIDER_RESEND` (no SMTP fallback).
- Server manifest: Aug 7 19:18 (current); `Admin/Settings.vue → assets/Settings-DAQyA3cy.js`.
- Live: `https://kirom.serviceku.my.id/build/assets/Settings-DAQyA3cy.js` → **HTTP 200 (18,452 B)**; live manifest maps `Admin/Settings.vue → Settings-DAQyA3cy.js`.
- **Limitation:** authenticated Central Admin → Pengaturan click-through could not be automated (no admin credentials). Owner must visually confirm one canonical section.

## 13. Final Verdict

**B — ONE CANONICAL TRANSACTIONAL MAIL UI.**

- Normal Central Admin view shows a single **Email Transaksional (Resend)** configuration.
- Legacy SMTP collapsed/labeled legacy (backward-compat retained, not used for OTP).
- OTP and test mail go through Resend HTTP API only; no silent SMTP fallback; honest errors when off/unconfigured.
- Reply-To (persistent) and test recipient (temporary) remain fully separate.
- Tests + build + deploy + live asset verification all pass.

No real Resend API key was configured, no production test email was sent during this phase.

---

## STOP

After B: **STOP.** Report returned to owner. Do not configure real Resend credentials automatically.
**Next owner action:** open Central Admin → Pengaturan → Email Transaksional (Resend), confirm the single canonical section, then configure the real Resend API key manually.
