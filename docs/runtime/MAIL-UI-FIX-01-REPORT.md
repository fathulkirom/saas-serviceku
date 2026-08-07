# MAIL-UI-FIX-01 — Separate Test Recipient from Reply-To

> Phase: MAIL-UI-FIX-01 — Central Admin → Pengaturan → Transactional Mail (Resend)
> Date: 2026-08-08
> Mode: TRACE → FIX MINIMALLY → TEST → DEPLOY → STOP
> Input: Owner reported the "Kirim Email Tes" recipient value also appearing in "Reply-To" — the two must be independent.

---

## Root Cause

**Two distinct issues — both verified in source:**

1. **Frontend coupling (the reported bug).** In `resources/js/Pages/Admin/Settings.vue`, the Resend **"Kirim Email Tes"** input and the SMTP **"Test Email"** input both used the **same `testEmail` ref** (`v-model="testEmail"`). The Reply-To field was correctly bound to `form.mail_resend_reply_to`, but the temporary test-recipient value was **not given its own dedicated state** — it shared one binding across two test inputs, so the value the owner typed in the Resend test field also populated the other test field. The temporary test recipient was never structurally isolated from the persistent settings state.

2. **Latent backend bug (discovered by the new tests).** `ResendTransactionalMail::deliver()` and `sendRawTest()` called `$pending->replyTo(...)` on a `PendingMail` (and `PendingMailFake` in tests) — **`PendingMail` has no `replyTo()` method**, so this would throw `BadMethodCallException` whenever a Reply-To was configured. It was never caught because all prior tests used a blank Reply-To, so that code path had never executed. Reply-To is correctly applied via **`Mailable::replyTo()`** (the proper Laravel API), not on the pending message.

## Expected vs Actual

| Concept | Expected | Actual (before) | After fix |
|---|---|---|---|
| `mail_resend_reply_to` | persistent platform setting | bound to `form.mail_resend_reply_to` | unchanged — only persistent setting |
| Test recipient | temporary UI value | shared `testEmail` ref (coupled with SMTP test field) | dedicated `testEmailRecipient` ref |
| Test request payload | `{ email: testEmailRecipient }` | `{ email: testEmail }` | `{ email: testEmailRecipient }` |
| Test recipient persistence | never saved | never saved (but shared binding) | never saved; fully isolated |
| Reply-To application | mailable reply-to header | `$pending->replyTo()` → would throw | `$mail->replyTo()` (Mailable) |

## Files Changed

- `resources/js/Pages/Admin/Settings.vue` — added `const testEmailRecipient = ref('')` + `sendResendTestEmail()`; the Resend test input/button now use `testEmailRecipient`/`sendResendTestEmail`; Reply-To remains bound only to `form.mail_resend_reply_to`; helper text clarifies "Email Tujuan Tes" is recipient-only.
- `app/Services/Mail/ResendTransactionalMail.php` — set reply-to on the **Mailable** (`Mailable::replyTo()`) instead of `PendingMail` in both `deliver()` and `sendRawTest()`.
- `tests/Feature/Pilot/PilotMailSettingsTest.php` — 4 new tests (below).

## Test Result

`php artisan test tests/Feature/Pilot/PilotMailSettingsTest.php` → **15 passed / 38 assertions** (was 11). New coverage:
1. `test_reply_to_persists_correctly` — Reply-To saves to its own `system_settings` key
2. `test_blank_reply_to_is_allowed` — blank Reply-To is valid (optional)
3. `test_test_mail_does_not_modify_stored_reply_to` — test mail to `user@example.com` goes to that address and leaves stored Reply-To untouched
4. `test_test_mail_with_blank_reply_to_leaves_it_blank` — blank stays blank

Regression: `php artisan test tests/Feature/Pilot/PlatformSyncTest.php` → **15 passed / 108 assertions**.

## Build Result

`npm run build` → **PASS** (~26 s). New Settings chunk: `Settings-DqGYO0oW.js`.

## Deploy Result

Committed `701bcce` (`fix: separate test email recipient from Reply-To in mail settings`), pushed to `origin/main` (`a34887a..701bcce`). Deployed via canonical **`./deploy.sh`** (exit 0) — rsync source + `vite build` assets, preserved `.env`, recreated `serviceku-app`, `migrate --force`, `optimize:clear`.

## Live Result (verified server + public)

- Server source: `Settings.vue` contains `testEmailRecipient` (×5, host + container); `ResendTransactionalMail` uses `Mailable::replyTo` (×3)
- Server build: `public/build/assets/Settings-DqGYO0oW.js` present (18,246 B); server manifest maps `Admin/Settings.vue → Settings-DqGYO0oW.js`
- Live: `https://kirom.serviceku.my.id/build/assets/Settings-DqGYO0oW.js` → **HTTP 200 (18,246 B)**
- Container `serviceku-app` healthy; caches cleared

**Limitation:** authenticated Central Admin → Pengaturan click-through could not be automated (no admin credentials). Owner must visually confirm: type an address in "Email Tujuan Tes" → **Reply-To must remain unchanged (blank or its saved value)**; after clicking "Kirim Test", Reply-To must still be unchanged.

## Final Verdict

**B — TEST RECIPIENT AND REPLY-TO FULLY SEPARATED.**

- Reply-To is a persistent setting (`mail_resend_reply_to`), bound only to its own field.
- The test recipient is a dedicated temporary value, never persisted, never written to Reply-To/from.
- Backend validates the recipient independently (`required|email`); Reply-To stays optional.
- Tests + build + deploy + live asset verification all pass.

No real Resend API key was configured, no production test email was sent during this phase.

---

## STOP

After B: **STOP.** Report returned to owner. Do not configure real Resend credentials automatically.
**Next owner action:** open Central Admin → Pengaturan → Transactional Mail (Resend), verify the separated fields, then configure the real Resend API key manually.
