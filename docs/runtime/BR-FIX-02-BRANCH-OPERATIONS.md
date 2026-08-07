# BR-FIX-02 — BRANCH ACCESS, MULTI-BRANCH MANAGER, CROSS-BRANCH PICKUP & STOCK VISIBILITY

**ServiceKU v1.0 · Business Reality Fix · Priority P1 Core Multi-Branch Operation**

Scope: Customer Branch Guard · **BR-017** (Manager Multi Branch) · **BR-004** (Cross Branch Pickup) · **BR-005** (Branch Stock Visibility)

MODE: AUDIT → EXECUTABLE TESTS → MINIMAL ARCHITECTURE FIX → REGRESSION

---

## 1. Previous Branch Architecture

- `users.branch_id` = single, nullable, one-branch-per-user column (the **primary/home** branch). **No** many-to-many user↔branch relation existed.
- `Branch` model: `name, address, phone, is_active` only — no groups/regions/visibility.
- `BranchAccessService` had two methods: `scope()` (dead code, never called) and `canAccess()` — **owner = all, everyone else = strict `user.branch_id === record.branch_id`**. No caching, no multi-branch support.
- Policies (`ServicePolicy`, `CustomerPolicy`, `DevicePolicy`, `WorkOrderPolicy`, `ServiceRequiredPartPolicy`) delegated to `BranchAccessService::canAccess` → single-branch only. `ProductPolicy`, `SalePolicy`, `InventoryMutationPolicy`, `WorkOrderPolicy::delete`, `ServiceRequiredPartPolicy::returnPart` had **no** branch check.
- ~32 controllers used hand-written `(int)/(string)` `branch_id ===` comparisons (e.g. `ServiceController`, `CustomerController`, `ProductController`, `UserManagementController`, `SaleStoreController`, `ServiceDeliveryController`).
- Inventory: `InventarisController` scoped to own branch; `InventoryIntelligenceController::dashboard` was **fully global** (any tenant user saw every branch's stock).
- `service_transfers` table existed but was **never written** — the deprecated `ServiceTransferController::store` **rewrote `service.branch_id`** to the destination (forbidden by BR-FIX-02) and reset status/technician.
- `ServiceDelivery` had no pickup/destination branch; `ServiceDeliveryController::pickup` strictly required `user.branch_id === service.branch_id`.
- `requests.pickup_branch_id` existed on the `requests` table but was never consulted by the delivery flow.
- Permissions (`Permission`/`Role`) are tenant-global; no branch dimension.

## 2. Existing Cross-Branch Bugs (confirmed by audit)

1. **Cross-branch customer binding** — `TenantCsWorkflowGuardTest::test_cs_cannot_create_service_for_customer_from_other_branch` failed: `StoreServiceRequest` had no branch guard on `customer_id`; the policy layer did reject with 403 (no service created), but the test expected a validation error and the app's guard was inconsistent with the canonical rule.
2. **Cross-branch device takeover** — `ServiceIntakeController` reassigned a device (matched by IMEI/SN) to a new customer without verifying access to the device's original customer (a cross-branch side effect).
3. **Single-branch users** — no way to give a manager multiple branches without tenant-global access.
4. **User-management authorization broken** — `AuthServiceProvider` is **not registered** in `bootstrap/providers.php`, so its explicit `$policies` map was never applied; Laravel auto-discovery maps most models by basename but **`User` → `App\Policies\UserPolicy` (missing)** — user create/update/delete always 403.
5. **Transfer rewrote origin** — `ServiceTransferController::store` set `service.branch_id = destination`, destroying origin history.
6. **Pickup strictly origin-bound** — no way to pick up at a different custody branch.
7. **Global inventory leak** — `InventoryIntelligenceController::dashboard` returned every branch's stock to any user.

## 3. Schema Reused / Added (all tenant, additive, rollback-safe)

| Change | Table | Purpose |
|---|---|---|
| Added | `user_branches` | many-to-many **additional** branch access (primary stays on `users.branch_id`) |
| Added | `branch_visibility` | READ-only stock visibility (branch_id may read visible_branch_id) |
| Added cols | `service_transfers` | `status (requested/sent/received/cancelled)`, `requested_by`, `processed_by`, `sent_at`, `received_at` |
| Added col | `service_deliveries` | `pickup_branch_id` (nullable) |

Migrations: `2026_08_07_000003_create_user_branches_table.php`, `2026_08_07_000004_create_branch_visibility_table.php`, `2026_08_07_000005_add_transfer_custody_and_pickup_branch.php`.

Reused without change: `users.branch_id` (primary/home), `branches`, `service_transfers` base columns, `service_deliveries`, `ActivityLog`, `requests.pickup_branch_id` (schema-level precedent).

## 4. BranchAccessService Changes

Now the **sole** branch-scope resolver (centralized):

- `accessibleBranchIds(User)` — owner = all tenant branches; others = primary + explicit `user_branches` pivot. Cached on the User (`user:{id}:branch_access`, 300s), invalidated on assignment changes.
- `canAccess(User, ?int $branchId)` — owner always true; null = global record; else `in_array(accessibleBranchIds)`.
- `canAccessBranch(User, $branch)`, `canAccessRecord(User, $record)` — convenience wrappers.
- `scope(Builder, User, string $branchColumn)` — now functional (uses accessibleBranchIds).
- `visibleBranchIds(User)` + `stockVisibilityScope(Builder, User)` — **BR-005** READ-only stock visibility (own + `branch_visibility` config). This never grants mutation/transfer/financial authority.

## 5. Policy Changes

- `ServicePolicy`, `CustomerPolicy`, `DevicePolicy`, `WorkOrderPolicy` (view/update), `ServiceRequiredPartPolicy` (request/approve/cancel/consume) already delegate to `BranchAccessService::canAccess` → they now inherit **multi-branch** behavior automatically (no per-policy edit needed).
- `ServiceRequiredPartPolicy::returnPart` — added the missing branch-scope (`inScope`) check.
- **Root-cause fix**: registered `Gate::policy(User::class, TenantUserPolicy::class)` in `AppServiceProvider::boot()` so user-management authorization works (the absent `AuthServiceProvider` registration was the latent bug).
- No permission was loosened — branch access only means "this branch is in scope"; the operation still requires the relevant permission/policy (verified by test: manager with branch access still cannot delete a service).

## 6. Customer Binding Guard

- `ServiceIntakeController::store` authorizes `view` on the customer → `CustomerPolicy` → `BranchAccessService::canAccess` → a branch-scoped CS **cannot bind an unauthorized customer** (canonical 403, no service, no device, no timeline/audit side effect). Owner/global users remain allowed.
- Added a **device guard**: if an IMEI/SN match returns a device owned by a different customer, access to that customer is authorized before reassignment (blocks cross-branch device takeover).
- `TenantCsWorkflowGuardTest` updated to assert the canonical rejection (403) and no side effects; added tests: CS may create with own-branch customer; owner may use any tenant-branch customer.

## 7. Manager Multi-Branch Flow (BR-017)

- Manager has **primary** branch (`users.branch_id`, backward compatible) + **zero or more additional** branches (explicit `user_branches` pivot).
- A manager assigned A+B can view/update services and customers in A and B, cannot access C, and is never given tenant-global access.
- Removing an assignment revokes access immediately (pivot sync + cache invalidation).

## 8. User Branch Assignment

- `UserManagementController::store`/`update` accept `additional_branches` (array), sync the pivot, invalidate the branch-access cache, and write audit (`manager_branch_assigned` / `manager_branch_removed`).
- Non-owner actors may only assign branches within their own access scope (403 otherwise).
- Fixed the resource-route model binding: controller parameters renamed `$userManagement` → `$user` to match the `{user}` route segment (the previous name mismatch silently operated on a phantom user).
- UI (`Sistem/Index.vue`): users table shows **Primary (blue) + Additional (indigo)** badges; the user form adds a **"Akses Cabang Tambahan"** checkbox list. Owner/Admin (with `users` plan feature) manage assignments per the existing permission design — no new role, no new framework.

## 9. Cross-Branch Transfer (BR-004)

- `ServiceTransferController` rewritten (was deprecated, rewrote origin):
  - `store` — request transfer from **current custody branch** to a destination (validates origin & destination are in the actor's scope; same-branch rejected; origin preserved).
  - `send` — requested → sent (origin branch only).
  - `receive` — sent → received (destination branch only; **idempotent**), custody moves.
  - `cancel` — open transfer cancelled (origin branch only).
- `Service::currentCustodyBranchId()` = last received transfer's destination, else origin — **origin (`service.branch_id`) is NEVER rewritten**.
- Routes added: `service-transfers.send` / `.receive` / `.cancel`.

## 10. Cross-Branch Pickup (BR-004)

- `ServiceDeliveryController::pickup` is now **custody-aware**: it authorizes against the current custody branch (origin if no transfer; destination after receive), records `pickup_branch_id` on the delivery, and keeps the existing idempotency (`picked_up_at`).
- An unauthorized branch cannot receive or pick up (403). Repeated receive/pickup create no duplicate side effects (idempotency + audit-once).
- Timeline: `service_transfer_requested` → `service_transfer_sent` → `service_transfer_received` → `pickup`.

## 11. Stock Visibility Architecture (BR-005)

- `branch_visibility` pivot configures **READ** visibility per branch (data-driven — no hardcoded branch IDs).
- `BranchAccessService::visibleBranchIds()` / `stockVisibilityScope()` centralize the readable stock scope.
- `InventarisController` product list and `InventoryIntelligenceController::dashboard` now scope to visible branches (no longer global; own-branch still shown with the owning branch labeled).
- Mutations/movements remain own-branch (mutation authority ≠ read visibility).

## 12. Stock Mutation Safety

- BR-FIX-01 part reservation/consumption still validates the **stock source branch**: a service in A cannot request/reserve/consume B's product even when B's stock is **visible** (request returns 422; no `ServiceRequiredPart`, no mutation, B stock untouched).
- Consuming remote stock requires an existing transfer / explicit stock-movement mechanism (out of BR-FIX-02 scope — documented).

## 13. Tenant Isolation

- 1 DB per tenant ⇒ cross-tenant access is **absolute**: `TenantIsolationBranchTest` proves a manager of Tenant A cannot see a Branch/Customer/Product/Service of Tenant B, cannot create a transfer to Tenant B's branch, and cannot pick up Tenant B's service (404 / record absent).
- `BR17` also verifies the manager's `accessibleBranchIds` only contains its own tenant branches.

## 14. Audit / Timeline

Reused `ActivityLog` (append-only, no new audit engine):
- `manager_branch_assigned` / `manager_branch_removed` (user management).
- `service_transfer_requested` / `service_transfer_sent` / `service_transfer_received` / `service_transfer_cancelled` (transfer).
- `pickup` now records `pickup_branch_id` context.

## 15. UI Wiring

- `Sistem/Index.vue` — Primary + Additional branch badges in the users table; multi-branch checkbox list in the user form.
- `SystemController::index` loads `users` with `branch` + `branches`.
- Stock list (`Inventaris/Index`) shows the owning branch via `with('branch')`.

## 16. Tests Before Fix

- `BR17MultiBranchManagerTest`, `BR04CrossBranchPickupTest`, `BR05StockVisibilityTest` were **skeletons** (`markTestIncomplete`).
- `TenantCsWorkflowGuardTest` test 1 failed (cross-branch customer guard response mismatch).
- Full-suite failures carried forward: `GoogleDrivePhotoServiceTest` (credential-dependent, per BR-FIX-02 STEP 22 left as-is) and `TenantCsWorkflowGuardTest` customer-guard assertion (now fixed).

## 17. Tests After Fix

| File | Result |
|---|---|
| `BR17MultiBranchManagerTest` | **7/7 PASS** |
| `BR04CrossBranchPickupTest` | **8/8 PASS** |
| `BR05StockVisibilityTest` | **6/6 PASS** |
| `TenantCsWorkflowGuardTest` | **6/6 PASS** (customer guard fixed + extended) |
| `TenantIsolationBranchTest` | **3/3 PASS** |
| **BR-FIX-02 subtotal** | **30 PASS** |

All acceptance scenarios execute (no `markTestIncomplete` / `markTestSkipped` / placeholder).

## 18. BR-FIX-01 Regression

| File | Result |
|---|---|
| `BR07PartApprovalInvoiceTest` | **11/11 PASS** |
| `BR08PartReturnTest` | **7/7 PASS** |
| `BR09ReservedStockTest` | **5/5 PASS** |
| `ServiceFullLifecycleTest` | **PASS** |

BR-FIX-01 canonical part lifecycle is unaffected by BR-FIX-02 (part reservation still requires the product to belong to the service's own branch).

## 19. Full Suite

**`446 passed / 1 failed / 14 incomplete` — 1356 assertions — 575.16s**

The single failure is `GoogleDrivePhotoServiceTest::test_constructor_with_unexpired_token_initializes_client` — the known **credential-dependent** test (no Google OAuth credentials in the test environment); per BR-FIX-02 STEP 22 it is intentionally left as a separately-classified, pre-existing failure and no credentials are added.

The 14 incomplete are the remaining pre-existing Business Reality skeleton tests (BR01–03, BR06, BR10–16, BR18–20) — outside BR-FIX-02 scope.

vs. BR-FIX-01 baseline (419 passed / 2 failed / 17 incomplete): **+27 passed**, the cross-branch customer-guard failure is **fixed**, and BR-017/BR-004/BR-05 are no longer skeletons.

## 20. Remaining Risks

1. **`GoogleDrivePhotoServiceTest`** remains a separate credential-dependent failure (BR-FIX-02 STEP 22 — intentionally untouched; no credentials added).
2. `ProductPolicy`/`SalePolicy`/`InventoryMutationPolicy` remain role-based (branch enforcement for those actions lives in controllers / query scoping). A future audit may centralize them.
3. Cross-branch **stock transfer** (consuming visible remote stock) is not in scope — visibility is read-only; an authorized transfer/movement flow is a separate feature.
4. The user-management UI is limited to the `Sistem` page; other "consolidated" user pages were not modified.
5. Controllers still contain legacy hand-written `branch_id` helpers (`ServiceController`, `CustomerController`, `ProductController`, etc.); they were not all migrated to `BranchAccessService` to avoid regression risk — the object-level policies are the authoritative gate, and `BranchAccessService` now resolves multi-branch correctly.

---

## Verdicts

### BR-017 — **PASS**
A manager can have explicit access to multiple selected branches (primary + `user_branches` pivot) **without** tenant-global access; unassigned branches remain inaccessible; removing an assignment revokes access immediately; permissions still gate actions within accessible branches.

### BR-004 — **PASS**
A service can legitimately move from origin A to custody/pickup B via the transfer workflow with origin (`service.branch_id`) preserved, full audit, unauthorized branches blocked, and no duplicate side effects.

### BR-005 — **PASS**
Configured branches can READ remote stock (via `branch_visibility`, data-driven, no hardcoded IDs) without gaining permission to consume/mutate it; BR-FIX-01 reservation still refuses to consume visible remote stock; removing the relationship removes visibility.

### Customer Branch Guard — **PASS**
All active service-creation paths reject binding an unauthorized customer record (403 via `CustomerPolicy`/`BranchAccessService`, no side effects), while owner/global users remain allowed; device reassignment is also guarded.

---

## 22. Final Acceptance Verification

| Check | Result |
|---|---|
| BR-017 (Manager Multi-Branch) | **PASS** — 7/7 |
| BR-004 (Cross-Branch Pickup) | **PASS** — 8/8 |
| BR-005 (Stock Visibility) | **PASS** — 6/6 |
| Customer Branch Guard | **PASS** — 6/6 (TenantCsWorkflowGuardTest) |
| Tenant Isolation | **PASS** — 3/3 |
| BR-FIX-01 regression (BR07/08/09 + lifecycle) | **PASS** — 23/23 + lifecycle |
| Legacy regression (ServicePolicy, ServiceIntake, TenantIsolation, E2E, QC, parts) | **PASS** |
| Full PHP suite | **446 passed / 1 failed / 14 incomplete** (1356 assertions, 575.16s) — only failure is the pre-existing Google Drive credential test (out of scope) |
| Frontend build (`npm run build`) | **PASS** — 22.71s, no warnings (`Sistem/Index.vue` + BR-FIX-01 components compile) |

**STOP — BR-FIX-02 complete.** Warranty, permissions/delegation, commission, external partner, and Master Data are not started.

## Final Verdict — **BR-FIX-02 VERIFIED AND REGRESSION-SAFE**

- **BR-017 — PASS**: manager with explicit multi-branch access (primary + pivot) without tenant-global access; removal revokes immediately; permissions still gate actions.
- **BR-004 — PASS**: cross-branch custody transfer + pickup with origin preserved, full audit, unauthorized branches blocked, no duplicate side effects.
- **BR-005 — PASS**: configured branches read remote stock (data-driven) without mutation authority; BR-FIX-01 reservation refuses to consume visible remote stock.
- **Customer Branch Guard — PASS**: all active service-creation paths reject unauthorized customer binding (403, no side effects); owner/global allowed; device takeover guarded.
- **BR-FIX-01 regression**: none (BR07/08/09 + lifecycle + phase3 + E2E all pass).
- The only remaining full-suite failure (`GoogleDrivePhotoServiceTest`) is pre-existing and explicitly excluded from BR-FIX-02 scope.
