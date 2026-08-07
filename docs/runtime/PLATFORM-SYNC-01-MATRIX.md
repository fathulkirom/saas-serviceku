# PLATFORM-SYNC-01 — Platform Sync Matrix

> Phase: PLATFORM-SYNC-01 — Central Admin + Plan + Register + Landing final sync
> Date: 2026-08-07
> Mode: Audit existing implementation → define canonical platform data → synchronize surfaces → fix verified mismatches → test → report → STOP
> Purpose: Before the SAFE GIT CHECKPOINT, make all platform surfaces tell the SAME product truth.

## Source of Truth

| Surface | Data source | Canonical? |
|---|---|---|
| Plan model (`app/Models/Plan.php`) | central `plans` table | ✅ YES — canonical |
| `PlanSeeder` | seed values for trial/basic/pro/enterprise | ✅ YES (seed-time canonical) |
| Landing (`routes/web.php` home + `welcome.blade.php`) | DB `plans` (with a hardcoded fallback) | ⚠️ Mostly — stale hardcoded fallback exists |
| Register (`RegisteredTenantController::create` + `Register.vue`) | DB `plans` | ✅ YES |
| Central Admin Paket (`PlanController` + `Plans.vue`) | DB `plans` | ⚠️ Edit round-trip drops some features |
| Tenant provisioning (`RegisteredTenantController::verifyOtp`) | `plan_id` from registration data | ✅ YES |
| Plan enforcement (`CheckPlanFeature` + `FeatureEngine`) | `tenant.plan.features` | ✅ YES |
| Central settings (`SystemSetting` + `SystemSettingsController`) | central `system_settings` | ✅ YES |
| Payment settings (`PaymentGatewayService`) | central `system_settings` (group `payment`) | ⚠️ Bank fields not pre-filled |
| Voucher (`VoucherController` + `Voucher` model) | central `vouchers` | ⚠️ `extra_months` dropped |
| Monitoring (`MonitoringController` + `Monitoring.vue`) | computed + `TenantStat` | ⚠️ 3 undefined props |
| Admin Dashboard (`SuperAdminController` + `Dashboard.vue`) | DB counts + `TenantStat` | ⚠️ `server_time` missing |
| Tenant lookup (`TenantLookupController`) | central `tenants` | ⚠️ reserved slugs not excluded |

**Canonical decision:** The `Plan` model / central `plans` table is the single canonical source. All surfaces already read it; the mismatches are (a) one plan row that contradicts its own numeric limit (Basic `users`), (b) UI round-trips that drop or invent data, (c) stale hardcoded fallbacks, and (d) missing enforcement. **No new plan engine. No new feature definitions.** A minimal canonical formatter (`PlanPresenter`) will be introduced only to avoid repeated inline shaping, and existing inline shaping already matches it.

## Plan Catalog — Per-Plan Truth

### TRIAL (`trial`)
- Price 0 · trial_days 14 · max_users 1 · max_branches 1
- services/customers/products = full · sales/reports/settings/monitoring = read_only · users/multi_branch/transfer_stock/expenses/purchases/deposits/checklist/indents = none
- Landing: shown · Register: shown (Gratis, badge "Trial 14 Hari") · Admin: shown
- Backend: enforced via `check.plan.feature:*`
- **Classification: SYNCED** (intentionally restricted for evaluation — correct)

### BASIC (`basic`)
- Price 99000 · trial_days 0 · max_users 3 · max_branches 1
- services/customers/products/sales/reports/settings/monitoring/expenses/purchases/deposits/checklist/indents/cash_register/master_data = full
- **users = read_only ← CONTRADICTS max_users = 3**
- Landing advertises "Maks. 3 karyawan" but backend rejects `users.store` (POST blocked by `check.plan.feature:users` read_only)
- **Classification: MISMATCH → FIX** (STEP 3: `users = full`, `max_users = 3` enforced; owner included in the count — consistent with Trial's max_users=1 = the single owner)

### PRO (`pro`)
- Price 199000 · trial_days 0 · max_users 10 · max_branches 5 · users = full · multi_branch/transfer_stock = full
- Landing: shown · Register: shown · Admin: shown · Backend: enforced
- **Classification: SYNCED**

### ENTERPRISE (`enterprise`)
- Price 499000 · trial_days 0 · max_users 999 · max_branches 999 · users = full · multi_branch/transfer_stock = full
- Landing: shown · Register: shown (CTA "Konsultasi Paket") · Admin: shown · Backend: enforced
- **Classification: SYNCED**

## Verified Mismatches / Findings (all confirmed in code)

| # | Surface | Finding | Severity | Action |
|---|---|---|---|---|
| M1 | PlanSeeder | Basic `users = read_only` contradicts `max_users = 3`; landing promises employees the backend denies | P0 consistency | Fix → `users = true`; enforce max_users (STEP 3/9) |
| M2 | User creation | **No `max_users` enforcement** anywhere; user #N+1 silently allowed | P0 consistency | Enforce in `UserManagementController::store` (STEP 9) |
| M3 | Landing route | Hardcoded fallback plan array (stale constants, contradicts seeder e.g. trial_days 14 on paid plans, users none on Basic) | P1 stale truth | Remove fallback; landing consumes DB only (STEP 2/6) |
| M4 | Landing pricing copy | "per bulan, trial tersedia" + CTA "Mulai Trial" shown for Basic/Pro (trial_days=0) | P2 content | Show trial copy/CTA only when `trial_days > 0` (STEP 6) |
| M5 | Admin Paket edit | `Plans.vue` KNOWN_FEATURES omits `cash_register`/`master_data` → saving a plan DROPS those feature flags | P1 data loss | Add them to KNOWN_FEATURES (STEP 5) |
| M6 | Voucher | `extra_months` accepted by UI but absent from store/update validation → dropped | P1 data loss | Add validation + persist (STEP 16) |
| M7 | PaymentSettings | `getConfig()` omits bank fields → form pre-fills stale defaults; saving overwrites real bank config; secrets not protected on blank update | P1 | Pre-fill bank fields; preserve secrets on blank/masked (STEP 13) |
| M8 | Admin Dashboard | `systemHealth` lacks `server_time` → blank on Dashboard.vue | P3 | Add `server_time` (STEP 12) |
| M9 | Monitoring | `health.system_alerts`, `storageHealth.mysql_data_size`, `backupHealth.file_count` undefined | P3 | Wire real values or hide unsupported (STEP 17) |
| M10 | Tenant lookup | reserved slugs (`kirom`, `admin`, …) not excluded from tenant search result | P2 | Exclude reserved slugs (STEP 21) |
| M11 | Admin authZ | central `users` has no role/is_admin guard — any central user = platform admin | P2 latent | DOCUMENT as hardening-required; no schema change in this phase (STEP 18) |
| M12 | Provisioning | `verifyOtp` has no rollback — a mid-provisioning failure leaves a partial tenant | P2 | Add guarded cleanup on failure (STEP 8) |

## Already SYNCHRONIZED (no action)

- Register page plans: DB-driven, selected `plan_id` provisions the exact plan (no frontend spoofing) ✅
- Tenant plan assignment at provisioning: `plan_id` from verified registration data ✅
- `CheckPlanFeature` + `FeatureEngine` resolve plan levels (flat + nested formats both handled) ✅
- Role display: `Sistem/Index.vue` lists official roles first; `head_store`/`courier` labeled "(legacy)" ✅ (PILOT-UAT-02)
- Central Admin menu: 10 items, all routes exist, all platform functions ✅
- Landing CTAs: all real (`/register`, `/masuk`, anchors); no `href="#"`; no admin login advertised ✅
- Resend placement: Admin → Pengaturan → Transactional Mail (Resend) ✅ (PILOT-MAIL-04R)
- Platform settings: `Pengaturan` is canonical; registration/maintenance toggles wired (to be confirmed by test) ✅
- Tenant page list/detail: shows name/slug/domain/plan/status/business type/user count/branch count/dates ✅
