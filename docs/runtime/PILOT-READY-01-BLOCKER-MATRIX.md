# PILOT-READY-01 — PILOT BLOCKER MATRIX

**Tanggal**: 2026-08-07
**Scope**: Operational usability of the PRIMARY daily service journey (intake → assign → diagnosis → part → repair → QC → invoice → payment → pickup → warranty).
**Severity**: P0 = data/financial/security integrity · P1 = cannot complete daily flow · P2 = workaround exists · P3 = polish/post-pilot.

| # | Area | Runtime Status | Severity | Pilot Blocker? | Fix Required |
|---|------|----------------|----------|----------------|--------------|
| 1 | **Service detail page (Enterprise Workspace)** — tab sections received no props → Overview empty, Sparepart/Foto/Diagnosa posted to `/services/undefined/...` | ✅ FIXED — `ServiceController@show` now builds the rich payload (`ServiceWorkspaceService::build`) and the engine spreads `data` as props + wires `@refresh` | P1 | No (was YES) | Backend rich `dataContext` + engine `v-bind="data"` + `@refresh` |
| 2 | **QC step** — no reachable UI to pass/fail QC (mandatory gate to `siap_diambil`) | ✅ FIXED — `qc` tab registered (backend definition + `service.js`), `QC.vue` renders with real `service`/`qcChecks`/`canQC` props | P1 | No (was YES) | Added `qc` tab to `ServiceWorkspace.php` + registered `QC.vue` |
| 3 | **Part flow (request → approve/reserve → use/consume)** — form hidden on live page | ✅ FIXED — `Spareparts.vue` now receives `service`, `spareparts`, `availableProducts`, `canManageParts`, `canConsumeParts`, `canRequestPart` | P1 | No (was YES) | Fixed by prop-contract fix (see #1) |
| 4 | **Diagnosis entry** — posted to `/services/undefined/diagnosis` | ✅ FIXED — `serviceId` now passed | P1 | No (was YES) | Fixed by #1 |
| 5 | **Warranty claim approve/reject** — claim could be opened but never decided (no rework) | ✅ FIXED — `Warranty.vue` now has Setujui/Tolak per claim → `warranty-claims.decide` (note required on reject) | P1 | No (was YES) | Added decide buttons + `decideClaim()` in `Warranty.vue` |
| 6 | **Cashier pickup** — UI offers pickup to cashier but backend denied (`service.pickup` missing) | ✅ FIXED — `service.view` + `service.pickup` granted to cashier (legacy map + seeder) | P1 | No (was YES) | Permission grant |
| 7 | **Cashier dashboard** — “Servis Siap Diambil” perpetual skeleton (prop mismatch) | ✅ FIXED — controller passes `readyServices` + `cashRegisterOpen` | P1 | No (was YES) | Controller alias |
| 8 | **Global search (Ctrl+K / header)** — every query 500 (`Builder::map()` on products) | ✅ FIXED — `SearchController@search` added `->get()` before `->map()`; also hardened dead `UniversalSearchController` (`invoice_number` column does not exist) | P1 | No (was YES) | `->get()` + removed `invoice_number` query |
| 9 | **`services.show` for a fresh tenant** — workspace feature gate denied because `FeatureEngine::getAllFeatureKeys()` returned `[]` (modules not seeded) | ✅ FIXED — feature engine falls back to active-plan feature keys when `modules` is empty | P1 | No (was YES) | `FeatureEngine::getAllFeatureKeys()` fallback |
| 10 | **`/keuangan` security** — technician/courier could read full sales/expenses/purchases | ✅ FIXED — 403 unless `canManageFinance()` or cs/cashier (restricted today-only view) | P0 | No (was YES) | Guard in `FinanceController@index` |
| 11 | **`/pengaturan` security** — any user could read revenue/users/branches | ✅ FIXED — 403 unless owner/admin | P0 | No (was YES) | Guard in `SettingController@index` |
| 12 | **`/sistem` security** — any user on users-enabled plan could view/manage all users | ✅ FIXED — 403 unless owner/admin | P0 | No (was YES) | Guard in `SystemController@index` |
| 13 | **Warranty duration default** — services without explicit `warranty_days` (0) got a 0-day instantly-expired warranty | ✅ FIXED — pickup defaults to 30 days when `warranty_days <= 0` | P1 | No (was YES) | `ServiceDeliveryController::pickup` guard |
| 13b | **BR-020 Reopen** — closed service could not be corrected from UI; also `Service::lock()` never persisted (`is_locked` missing from fillable) | ✅ FIXED — “Minta Reopen” toolbar action + “Setujui” in Approval Center (owner/admin/manager, reason required); unlock-only semantics (no financial mutation); lock fields added to fillable+cast | P1 | No (was YES) | Minimal reopen UI + `Service` fillable fix |
| 14 | **Service intake receipt print** | ✅ PASS — `services.print-receipt` renders real `pdfs/service-receipt.blade.php` PDF (service no, customer/device, complaint, status) | — | No | none |
| 15 | **Invoice + payment** | ✅ PASS — draft-from-service → pay-draft (backend totals, idempotent, paid state, receipt `sales.print` PDF) | — | No | none |
| 16 | **Inventory operation** | ✅ PASS — request/approve/use reduces stock exactly once; return/cancel routes exist; verified E2E | — | No | none |
| 17 | **Warranty visibility + refund** | ✅ PASS — store warranty auto-created at pickup; claims + rework + refund (real Expense cash-out) in workspace | — | No | none |
| 18 | **Technician dashboard** | ✅ PASS — top-level `TechnicianDashboard.vue` (live) shows real assigned services + stats. (Nested `Technician/Dashboard.vue` is unlinked dead code w/ unresolved component — P3) | P3 | No | none (dead code) |
| 19 | **Master data for fresh tenant** | ✅ PASS — branch/users/customer/product/checklist self-served via real routes; no demo-seed dependency | — | No | none |
| 20 | **Plan tier (Trial)** | ⚠️ Trial = `sales: read_only` → invoice/payment blocked. Pilot MUST run on Basic/Pro | P2 | No (workaround = use Basic/Pro plan) | none (deliberate plan gate) |
| 21 | **Jasa (labor) master UI** | ⚠️ `master-services.index` redirects back to Pengaturan (circular) — no working page; labor billed via free-text/quotation | P3 | No | none (post-pilot) |
| 22 | **Invoice numbering on service sales** | ⚠️ service sales referenced by id (`invoice-{sale.id}.pdf`); no sequential `invoice_number` column | P3 | No | none (post-pilot) |
| 23 | **Mobile/tablet** | ✅ functional responsive layout on daily screens (responsive grid classes); no functional responsive blocker found | P2 | No | none |
| 24 | **Error recovery / idempotency** | ✅ PASS — repeat complete (409), duplicate QC (409), duplicate payment safe, pickup idempotent, approve-once, no double stock | — | No | none (verified by tests) |
| 25 | **Data safety** | ✅ PASS — per-tenant SQLite, migrations (no migrate:fresh in deploy), `backup.sh`, down-migrations for rollback, `CACHE_STORE=array`/Redis loss non-destructive to source of truth | — | No | none |
| 26 | **Google Drive photos** | ℹ️ Optional integration — photo upload degrades gracefully when not connected; not required by the primary journey | — | No | NON-BLOCKING EXTERNAL INTEGRATION |

---

## Summary

- **P0 resolved**: 3/3 (finance, pengaturan, sistem security guards).
- **P1 resolved**: 10/10 (workspace contract, QC UI, part/diagnosis UI, warranty decide, cashier pickup+permission, cashier dashboard, global search, fresh-tenant workspace access, warranty duration default).
- **P2/P3 (deferred)**: plan-tier gating (operational choice), jasa master UI, invoice numbering, dead-code technician dashboard, mobile polish.
- **Pilot blockers remaining after this phase**: **0** (P0/P1).
