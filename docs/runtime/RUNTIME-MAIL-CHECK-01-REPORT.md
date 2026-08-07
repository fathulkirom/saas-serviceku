# RUNTIME-MAIL-CHECK-01 — Verify Central Admin Resend Deployment

> Phase: RUNTIME-MAIL-CHECK-01 — Find why the Resend provider UI was not visible on live Central Admin
> Date: 2026-08-08
> Mode: Audit local → GitHub → live server → identify exact mismatch → fix only verified deployment issue → verify → report → STOP
> Input: Owner reported live Central Admin → Pengaturan has **no Provider option** for Transactional Mail (Resend). Resend domain already verified externally.

---

## Summary

**Root cause: the live server was running a stale deployment.** The Resend (PILOT-MAIL-04R) and platform-sync (PLATFORM-SYNC-01) code was committed and pushed to GitHub, but was **never deployed** to the live server. The server source lacked the Resend backend files, `Settings.vue` lacked the Resend section, and the Vite build was from **Aug 6** (before the Resend work). This is a **deployment problem, not a code problem.**

**Fix performed:** ran the canonical, safe `./deploy.sh` (rsync current source + local `vite build` → server; recreate `serviceku-app` container; `migrate --force`; `tenants:migrate --force`; idempotent seeds; `optimize:clear`). Verified live after deploy.

---

## 1. Local Git HEAD

`1fb3ead15f73f0d2e36aaf1db0e238d356d8d2cc` (`main`)

## 2. Origin/main HEAD

`1fb3ead15f73f0d2e36aaf1db0e238d356d8d2cc` — **identical** (local synced with GitHub).

Relevant commits present locally/remotely:
- `32bb577` feat: finalize ServiceKU pilot-ready platform synchronization (contains Resend UI + backend)
- `1fb3ead` docs: add GIT-SYNC-01 checkpoint report

## 3. Live Server HEAD

**N/A — the live server directory is NOT a git repository.** `deploy.sh` rsyncs code and explicitly excludes `.git`. The server's state is whatever was last rsynced, so git hashes do not apply.

## 4. Active Server / Project Directory

- Host: deployment LAN server per `deploy.sh` (`kirom@192.168.1.33`)
- Project dir: `/home/kirom/serviceku`
- Runtime: **Docker** — `serviceku-app` container (`serversideup/php:8.4-fpm-nginx`, PHP 8.4.23), mounts `/var/www/html` = host project dir; app on port **8081**, origin of the Cloudflare Tunnel for `kirom.serviceku.my.id`
- Web root / Vite build path: `public/build/`
- Server not a git repo (rsync-deployed)

## 5. Provider Field Source-Code Status

**PRESENT (confirmed by inspecting actual current code — not relying on prior reports).**
`resources/js/Pages/Admin/Settings.vue` renders the full "Transactional Mail (Resend)" section:
- Provider `<select>` (`mail_resend_provider`) with options `off` ("Nonaktif fallback env mailer") and `resend` ("Resend API")
- Resend API Key (password input, masked; blank = retain)
- From Email, From Name, Reply-To
- Config status badge (`resendStatus.configured`)
- Save action (main form) + "Kirim Email Tes" action
- Reads `props.settings.mail_resend`

## 6. Backend Route Status

Present (local + now server):
- `GET admin/settings` → `SystemSettingsController@index`
- `POST admin/settings` → `admin.settings.update` → `SystemSettingsController@update`
- `POST admin/settings/feature-flags` → `admin.settings.feature-flags`
- `POST admin/settings/test-mail` → `admin.settings.test-mail` → `SystemSettingsController@testMail`

No duplicate routes added.

## 7. Inertia Prop Status

`SystemSettingsController@index` passes `settings.mail_resend => TransactionalMailService::status()` (masked — never the raw key). `Settings.vue` binds `form.mail_resend_provider`, `form.mail_resend_api_key`, `form.mail_resend_from_address`, `form.mail_resend_from_name`, `form.mail_resend_reply_to` from `props.settings.mail_resend`. Key names consistent end-to-end (`mail_resend_*`). **Complete.**

## 8. Live Vite Build Status

- **Before deploy (stale):** `public/build/manifest.json` dated **Agu 6 17:52**; built JS had **no** Resend/Transactional-Mail strings → **STALE FRONTEND BUILD** (the live site could not render the section even if source were current).
- **After deploy (current):** manifest dated **Agu 7 18:22**; built JS `Settings-CfxVaLNd.js` contains `mail_resend_provider` / Transactional Mail strings. Live HTTP serves this exact chunk (HTTP 200, 18,125 bytes).

## 9. Migration Status

- Before deploy: `2026_08_08_000001_sync_basic_plan_users_full` **absent** (not in server `migrate:status`).
- After deploy: migration **Ran**; server Basic plan `features.users = 1` (full), `max_users = 3` preserved.

## 10. Cache Status

- `deploy.sh` runs `php artisan optimize:clear` at the end; after deploy `bootstrap/cache` holds only fresh `packages.php` / `services.php` (Agu 7 18:23) — **no stale `config.php` / `routes-v7.php`**.
- App container was recreated (fresh PHP-FPM, clean opcache). No Cloudflare HTML/JS purge performed (not needed — new hashed assets are cache-busted by filename).

## 11. Exact Root Cause

**DEPLOY-01 (stale source) + DEPLOY-02 (stale Vite build).** The server was running a pre-Resend snapshot:
- `app/Services/TransactionalMailService.php` and `app/Services/Mail/ResendTransactionalMail.php` absent (host + container)
- `Settings.vue` had 0 Resend markers
- Vite build from Aug 6 with no Resend UI

The Resend UI/backend were committed and pushed, but the live deployment was never refreshed. Not: missing-in-source (C), wrong directory (D), or prop/route mismatch (G/H).

## 12. Fix Performed

Ran the canonical safe deploy script **`./deploy.sh`** (exit 0):
1. Local `vite build` (current source, incl. Resend UI)
2. rsync to server — excludes `.env`, `.env.*`, `.git`, `storage`, `bootstrap/cache/*`, `public/build` (then re-copies build separately)
3. Composer install (`--no-dev --optimize-autoloader`)
4. `.env` preserved (not overwritten), `APP_KEY` preserved
5. Recreated `serviceku-app` container (mounts host dir)
6. `php artisan migrate --force` (applied the Basic-plan rollout migration) + `tenants:migrate --force`
7. `db:seed PlanSeeder` (idempotent; Basic `users=full`) + `SystemSettingSeeder`
8. `php artisan optimize:clear`

No destructive commands (`migrate:fresh`/`db:wipe`/`git reset`/`force push`) were used.

## 13. Tests / Build Performed

- **No application code changed** (deployed already-committed code only) → no new commit/push required.
- Build: `vite build` via `deploy.sh` succeeded.
- Previously validated suites remain the source-of-truth for code correctness: `PilotMailSettingsTest` (11/11) and `PlatformSyncTest` (15/15) pass; full regression 593 passed (only external GoogleDrive credential failure).

## 14. Live Verification

Server-side (post-deploy, all confirmed):
- `Settings.vue` Resend markers: 6 (was 0)
- `TransactionalMailService.php` present in container (`MAIL_OK`)
- `SystemSettingsController` references `mail_resend` (×15)
- `public/build/manifest.json` current; built `Settings-CfxVaLNd.js` contains Resend UI
- Migration `sync_basic_plan_users_full` Ran; Basic `users=full`
- Container healthy; caches cleared

Public HTTP (no credentials):
- `https://kirom.serviceku.my.id/build/manifest.json` → 200 (contains Settings entry + app.js)
- `https://kirom.serviceku.my.id/admin/login` → 200
- `https://kirom.serviceku.my.id/build/assets/Settings-CfxVaLNd.js` → 200 (18,125 B)

**Limitation:** authenticated Central Admin → Pengaturan click-through could **not** be performed automatically (no admin credentials available). Owner must visually confirm the controls.

## 15. Remaining Owner Action

1. Log in to `https://kirom.serviceku.my.id/admin/login`
2. Open **Pengaturan** and confirm the **"Transactional Mail (Resend)"** section shows: Provider (**Resend / Off**), API Key, From Email, From Name, Reply-To, Save, **Kirim Email Tes**
3. If visible → manually enter the real Resend API key and click **Kirim Email Tes** (real email test is owner-configured, NOT part of this phase)

## 16. Final Verdict

**B — RESEND UI DEPLOYED AND READY FOR OWNER CONFIGURATION.**

- Current source + backend + build confirmed deployed on the live server (source, route, Inertia prop, Vite build, migration, Basic plan, caches all verified).
- Live public assets serve the current build.
- Authenticated visual confirmation pending the owner logging in (no credentials were available for automated click-through).

No real Resend API key was configured, no test email was sent, no pilot tenant was registered during this audit.

---

## STOP

After B: **STOP.** Return report to owner. Do not configure real Resend credentials automatically.
**Next owner action:** open Central Admin → Pengaturan and manually configure Resend.
