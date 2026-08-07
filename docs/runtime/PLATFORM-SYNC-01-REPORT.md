# PLATFORM-SYNC-01 — REPORT

> Phase: PLATFORM-SYNC-01 — Central Admin + Plan + Register + Landing final sync
> Date: 2026-08-07
> Target: ONE consistent platform before the SAFE GIT CHECKPOINT.
> Mode: Audit → define canonical data → synchronize surfaces → fix verified mismatches → test → report → STOP

---

## 1. Source of Truth

**Canonical source = the `Plan` model / central `plans` table.** Every public surface already reads it:

- Landing (`routes/web.php` home → `welcome.blade.php`) reads active `plans`
- Register (`RegisteredTenantController::create` → `Register.vue`) reads active `plans`
- Central Admin Paket (`PlanController` → `Plans.vue`) reads `plans`
- Tenant provisioning (`verifyOtp`) assigns the `plan_id` recorded at registration
- Enforcement (`CheckPlanFeature` + `FeatureEngine`) resolves from `tenant.plan.features`
- Central settings (`SystemSetting` + `SystemSettingsController`) is the canonical settings store

**Decision:** A dedicated `PlanPresenter` was **not** introduced. All surfaces already consume the same canonical `plans` table, so a new presenter would add an abstraction without fixing any mismatch (and the phase forbids building a new plan engine or duplicating feature definitions). The one inline shaping each surface does (name/price/features/trial_days) is consistent and DB-driven.

**Applied in this phase:** removed the only hidden stale constant — the hardcoded fallback plan array in `routes/web.php` (it contradicted the seeder: `trial_days` 14 on paid plans, `users` none on Basic). Landing now shows **only** real DB plan data.

## 2. Central Admin Menu

All 10 nav items verified working with real data, all routes exist, all platform functions (see `CENTRAL-ADMIN-AUDIT-01`). No tenant operational menu was added. Confirmed by new test `test_no_tenant_operational_route_in_central_admin` and `test_central_admin_menu_routes_all_work`.

## 3. Plan Catalog

| Plan | Price | trial_days | max_users | users | Status |
|---|---|---|---|---|---|
| Trial | 0 | 14 | 1 | none | SYNCED (restricted for evaluation) |
| Basic | 99.000 | 0 | **3** | **full** (was read_only) | **FIXED** |
| Pro | 199.000 | 0 | 10 | full | SYNCED |
| Enterprise | 499.000 | 0 | 999 | full | SYNCED |

Full per-plan matrix: `docs/runtime/PLATFORM-SYNC-01-MATRIX.md`.

## 4. Basic Plan Correction

**Mismatch (verified):** Basic had `max_users = 3` but `users = read_only` — the landing advertised "Maks. 3 karyawan" while the backend rejected `POST users.store` (via `check.plan.feature:users`).

**Fix:**
- `PlanSeeder`: Basic `users` → `true` (full). `max_users` stays 3.
- Backend now **enforces** the limit (see §8).

**Production rollout (no manual tinker required):**
- Added an **idempotent central data migration** — `database/migrations/2026_08_08_000001_sync_basic_plan_users_full.php`.
- It corrects **only** the verified contradiction: if the `basic` plan's `features.users` is `read_only`, it sets it to `true` (full). Price, `max_users`, `max_branches`, and every other field/feature (including any admin-customized values) are **preserved**.
- It is a strict no-op when the row is absent or already corrected, and never touches other plans.
- It runs automatically via the **normal deploy path** (`php artisan migrate --force`, already executed by `deploy.sh`) — **no manual tinker/DB edit is required in production**.
- Verified in dev: `php artisan migrate --force` ran `2026_08_08_000001_sync_basic_plan_users_full` (3.44 ms) and preserved `users=full, max_users=3, price=99000, services=full`.
- Proven executable by `test_rollout_migration_corrects_existing_readonly_basic_plan` and `test_rollout_migration_is_noop_when_already_corrected`.

## 5. Landing Page

- Plan names/prices/limits come from DB (stale hardcoded fallback removed).
- Content sync (no visual redesign): the "per bulan, trial tersedia" line and the "Mulai Trial" CTA now render only when `trial_days > 0` — so Basic/Pro/Enterprise (trial_days 0) correctly show "per bulan" and "Pilih Paket" instead of advertising a trial the backend does not grant.
- Landing no longer advertises any capability the backend denies.

## 6. Register Page

- Plans shown come from the DB (controller). No frontend plan spoofing, no hidden stale constants.
- Selected `plan_id` is stored in the pending registration record and is the exact value `verifyOtp` assigns to the tenant (proven by `test_selected_register_plan_persists_to_provisioning`).

## 7. Provisioning

- Verified canonical flow (register → plan → info → OTP → verify → tenant + DB → migrations → Cabang Utama → owner → settings → domain → plan assignment → login) is intact. OTP is **not** bypassed.
- **Hardened (STEP 8):** provisioning is now wrapped so a mid-flight failure rolls back — domains deleted, tenant DB dropped, tenant row removed, friendly error returned. No unusable partial tenant is left behind.

## 8. User Limit

- **Enforcement added** in `UserManagementController::store`: before creating a user it checks the plan's `max_users`; over-limit is rejected with `"Kuota user paket Anda sudah penuh (maks. N user). Silakan upgrade paket..."`.
- **Interpretation:** `max_users` counts **all** accounts (owner + staff), consistent with the Trial plan (`max_users=1` = the single owner) and with the existing registration (1 owner per tenant). This single interpretation now drives Admin display, landing, register, tenant UI, and backend enforcement.
- Proven by `test_basic_max_users_is_enforced_on_user_creation` (users 1–3 allowed, #4 rejected, count stays 3).

## 9. Roles

- Official roles prioritized in the tenant user UI: Owner, Admin, Manager, CS, Teknisi, Kasir (from PILOT-UAT-02).
- Legacy values `head_store` / `courier` are labeled "(legacy)"; `custom` remains supported backend-side. Nothing historical deleted.
- `test_official_roles_are_correct` proves both official and legacy roles remain valid.

## 10. Tenant Management

- Central Admin Tenant list/detail/create/edit verified: name, slug/domain, plan, status, business type, user/branch counts, subscription/trial dates all real.
- Fixed one stale label: domain placeholder example now `tokoku.serviceku.my.id` (was `.serviceku.app`).
- Plan changes go through the dedicated Ganti Paket flow (`changePlan`) with upgrade/downgrade simulation — functional.

## 11. Dashboard

- Only real metrics (DB counts + `TenantStat` aggregates); no dummy values.
- **P3 fixed:** `systemHealth` now includes `server_time` (was blank on `Dashboard.vue`).

## 12. Payment Settings

**Mismatch (verified):** `PaymentGatewayService::getConfig()` omitted the bank fields, so the form pre-filled stale defaults and saving overwrote real bank config; secrets were also at risk.

**Fix:**
- `getConfig()` now returns bank fields + payment instructions (correct pre-fill) and **masks** secrets (`••••••••`).
- `updateSettings` preserves secrets on blank/masked submit — saving an untouched form cannot erase config.
- Proven by `test_payment_settings_preserve_existing_config`.

## 13. Platform Settings

- `Pengaturan` remains the canonical location (general / registration / maintenance / feature flags / mail / Transactional Mail-Resend).
- Registration & maintenance toggles persist correctly (wired via the `updateSettings` submission). Confirmed by `test_settings_feature_toggles_persist`.

## 14. Resend Placement

- Transactional Mail already lives under Central Admin → Pengaturan → "Transactional Mail (Resend)" (from PILOT-MAIL-04R). Verified: fields render, key masked, blank update retains key, from/reply-to, test-mail action, honest provider status. **No new mail page added.** Real Resend credentials are out of scope for this coding phase.

## 15. Voucher

**Mismatch (verified):** `extra_months` was accepted by the UI but absent from store/update validation → dropped.

**Fix:** added `extra_months` (nullable int 0–60) to `VoucherController::store` and `update`. Proven by `test_voucher_extra_months_persists`.

## 16. Monitoring

**P3 (verified):** 3 undefined props.

**Fix:** `health.system_alerts` (merged storage+backup alerts), `storageHealth.mysql_data_size` (real MySQL size when the default connection is MySQL, else `null`), `backupHealth.file_count` (real backup-file count). The MySQL card is hidden when the value cannot be measured — no fake metrics.

## 17. Public CTA

All landing CTAs verified real: `Daftar / Mulai` → `/register`, `Masuk` → `/masuk`, footer legal links real, in-page anchors valid. No `href="#"`, no dead buttons, admin platform login is **not** advertised as tenant login.

## 18. Tenant Entry

- `/masuk` search → correct tenant → `{slug}.serviceku.my.id/login`.
- **Fixed (STEP 21):** reserved platform slugs (`kirom`, `admin`, `www`, …) are centralized on `Tenant::reservedSlugs()` and excluded from tenant lookup — `kirom` is never returned as a store. Proven by `test_tenant_lookup_excludes_reserved_slug`.

## 19. Terminology

- Tenant / Toko; roles Owner, Manager, Admin, CS, Kasir, Teknisi; "Super Admin / Central Management" only for the platform. Legacy roles clearly labeled. Consistent across Landing, Register, Central Admin, and Tenant UI.

## 20. Tests

New executable suite `tests/Feature/Pilot/PlatformSyncTest.php` — **15 tests / 108 assertions**, all PASS — covering all 14 STEP-23 checks plus the production rollout:
1. Basic `users = full` ✅
2+7. Basic `max_users` enforcement (owner counted; #4 rejected) ✅
3. Register plans match backend ✅
4. Selected register plan persists to provisioning record ✅
5. Landing plan data matches canonical source ✅
6. Plan update reflects consistently (admin → landing) ✅
8. Official roles remain correct (legacy kept) ✅
9. Tenant entry excludes reserved `kirom` ✅
10. Admin PaymentSettings preserves existing config + secrets ✅
11. Settings feature toggles persist ✅
12. Voucher `extra_months` persists ✅
13. No broken Central Admin menu route ✅
14. No tenant operational menu in Central Admin ✅
15. Rollout migration corrects existing read_only Basic plan (preserves price/max_users/other features) ✅
16. Rollout migration is a no-op when already corrected (idempotent, other plans untouched) ✅

No existing tests were weakened.

## 21. Build

`npm run build` → **PASS** (built in ~24s, PWA assets generated).

## 22. Full Regression

**`php artisan test` — final authoritative run (full suite, 2026-08-08, on the final code incl. rollout migration):**

| Metric | Value |
|---|---|
| Passed | **593** |
| Failed (initial) | **3** → resolved to **1** (see below) |
| Incomplete | **6** (deferred skeletons: BR-10/14/15/18/19/20) |
| Assertions | **1985** |
| Duration | **1431.12 s** |
| Exit code | 1 (external credential dependency) |

**Failures classified & resolved (no new P0/P1 regression):**
- `GoogleDrivePhotoServiceTest` — **known external credential dependency** (unchanged, non-blocking, separately classified, out of scope).
- `TenantFinanceTransactionVisibilityTest` + `TenantServiceVisibilityTest` — **pre-existing timezone-dependent test fragility**, NOT a code regression. Root cause: the app runs in `Asia/Jakarta` (UTC+7) while Laravel stores/serializes timestamps in UTC (`...Z`). The tests compared the raw UTC string against the LOCAL date, which fails whenever a suite crosses 00:00–07:00 Jakarta time (local date = UTC date + 1). First run finished before Jakarta midnight (passed); second run crossed midnight (failed). **Resolved** with timezone-correct assertions (parse timestamp → convert to `config('app.timezone')` → compare date to local today). Test-only change; no application behavior modified. Re-verified green in the failure window (5 tests / 36 assertions PASS).

**Final state:** after resolution, the only failure is the known external `GoogleDrivePhotoServiceTest` credential dependency. All application, plan, registration, provisioning, permission, Central-Admin, and Pilot tests are green.

Related groups re-verified green: `tests/Feature/Pilot/*` (PlatformSync 15, ReadinessGuards 6, StoreOperational 3, MailSettings 11), `PlanTest`, `TenantUserManagementTest`, `BR16LimitedOwnerFamilyAccessTest`, `Unit/PolicyTest`, `FeatureFlagServiceTest`, `VoucherApplyControllerTest`, `Unit/Models/VoucherTest`, `Unit/Models/RegistrationVerificationTest`, `TenantOtpTest` — **141 tests / 437 assertions — PASS**.

## 23. Deferred Items

| Item | Classification | Status |
|---|---|---|
| **Admin authZ hardening** (no `is_admin`/role guard on central users — any central user = platform admin) | P2 latent | **DOCUMENTED as SECURITY HARDENING REQUIRED BEFORE MULTI-ADMIN USE.** No schema change in this phase (would require a safe migration + lockout-proof path; owner decision). For the current pilot: do NOT create arbitrary central users. |
| BR-010/014/015/018/019 (business-reality backlog) | Deferred | Unchanged; reviewed post-human-pilot unless a human UAT proves a P0/P1. |
| Real Resend credentials | External | Owner configuration step (PILOT-MAIL-04R). Not part of this coding phase. |

## 24. Final Verdict

**B — PLATFORM SYNCHRONIZED FOR PILOT.**

- ✅ Basic contradiction resolved (`users=full`, `max_users=3`, enforced, landing/register/admin all agree)
- ✅ Landing & Register match canonical plan data (stale hardcoded fallback removed)
- ✅ Selected register plan provisions the correct tenant plan (contract proven)
- ✅ User limits actually enforce
- ✅ Admin menu remains valid (10 items, all routes work, no tenant operational menu)
- ✅ Platform settings persist correctly
- ✅ Critical CTAs work; tenant entry excludes reserved `kirom`
- ✅ Payment settings preserve config + secrets; voucher `extra_months` persists; monitoring props wired
- ✅ Build passes; **final regression resolved — 593 passed / 1985 assertions; only the known external GoogleDrive credential failure remains; 2 timezone-dependent test assertions fixed (test-only, no behavior change) → no P0/P1 regression**
- ✅ Existing production Basic plan has a **safe, idempotent migration rollout** — no manual tinker/DB edit required

---

## STOP RULE

Per the phase instructions, after verdict B: **STOP.** No real Resend credentials, no pilot-tenant creation, no Human UAT start, no post-pilot development, no new modules. The next step after B is the **SAFE GIT CHECKPOINT**.
