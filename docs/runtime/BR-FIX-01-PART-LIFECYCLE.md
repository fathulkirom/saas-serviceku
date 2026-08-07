# BR-FIX-01 — PART REQUEST → RESERVATION → INVOICE → STOCK → RETURN

**ServiceKU v1.0 · Business Reality Fix · Priority P0 Inventory & Financial Integrity**

Scope: **BR-007** (Part Request/Approval/Invoice) · **BR-009** (Reserved Stock) · **BR-008** (Part Return)

MODE: VERIFY WITH EXECUTABLE TESTS → FIX → REGRESSION TEST

---

## 1. Previous Behavior (Before Fix)

- **Physical stock** lives on `products.stock_quantity` (per `branch_id`, nullable = global).
- **No reservation model.** A `service_required_parts.reserved_qty` column existed but was never written. `Product` had no `reserved` / `available` concept.
- `TechnicianWorkflowController::completeRepair()` **deducted physical stock directly** when the technician pressed *Finish* (via `ServiceRequiredPart::use()` AND via a direct `reduceStock()` fallback), created `ServiceSparepart` and `InventoryMutation` records. → **BR-007 FAIL (P0)**: the commercial/inventory consumption event was bound to repair completion instead of CS invoice confirmation.
- `ServiceRequiredPart::approve()` only flipped the status — **no reservation, no availability check, no idempotency** → **BR-009 NOT IMPLEMENTED (P1)**.
- `ServiceSparepart` (the invoice source-of-truth read by `SaleStoreController::draftFromService()`) was created **only inside `completeRepair()`**, so nothing was billable until the technician finished the repair.
- Returns were minimal / broken: `ServicePartController::processReturn()` created an `InventoryMutation` with `type='return'` and **omitted `branch_id`** (NOT NULL constraint violation), and did not distinguish reserved-only from consumed returns → **BR-008 NOT IMPLEMENTED (P2)**.
- `inventory_mutations.type` is an enum limited to `masuk | keluar | transfer` — any `type='return'` write was a latent CHECK-constraint failure.

## 2. Canonical Behavior (After Fix)

```
TECHNICIAN REQUEST
  → ADMIN/WAREHOUSE APPROVE        (reservation created; PHYSICAL stock unchanged)
  → CS CONFIRMS / ADDS TO INVOICE  (reservation consumed)
  → PHYSICAL STOCK REDUCED (ONCE)
  → SERVICE PART USAGE CREATED
  → SERVICE SPAREPART CREATED      (invoice source-of-truth)
  → INVENTORY MUTATION CREATED     (exactly one deduction)

Cancelled before consumption → RELEASE RESERVATION only (no stock change).
Consumed but legitimately returned → RESTORE STOCK + REVERSAL MUTATION +
  remove/adjust billable item if invoice is not finalized. Finalized/PAID
  invoices are never silently modified (blocked).
```

## 3. Existing Models Reused (no new inventory subsystem)

| Model | Reused as |
|---|---|
| `Product` | physical stock + derived `reserved_quantity` / `available_quantity` |
| `ServiceRequiredPart` | authoritative reservation record (status `approved`/`reserved` + `reserved_qty`) |
| `ServicePartUsage` | canonical consumption audit record |
| `ServiceSparepart` | invoice source-of-truth (read by `draftFromService`) |
| `InventoryMutation` | physical movements (`keluar` consume, `masuk` reversal) |
| `ServicePartReturn` | return request/processing (extended with `service_required_part_id`) |
| Existing events (`StockReserved`, `StockReleased`, `PartAddedToInvoice`, `PartUsed`, `StockReturned`, …) | audit/event platform |

Reservation is **derived** from authoritative `ServiceRequiredPart` rows in `approved`/`reserved` state — no mutable duplicated total added on `Product`.

## 4. Schema Changes

Additive only (rollback-safe, tenant migration):

- `service_part_returns.service_required_part_id` (nullable FK → `service_required_parts`, `nullOnDelete`)
  → `database/migrations/tenant/2026_08_07_000002_add_service_required_part_id_to_service_part_returns.php`
- No table drop, no column drop, no destructive rewrite, no enum rebuild.

## 5. Request Flow

- `ServicePartController::request()` (`POST /services/{service}/parts/request`).
- Authorized: assigned technician or owner/admin/manager.
- Branch-scoped product validation (product must belong to service branch or be global).
- Creates `ServiceRequiredPart` status `requested`.
- **No physical stock change, no reservation, no mutation** (verified by test).

## 6. Approval Flow

- `ServicePartController::approveRequest()` (`POST /service-parts/{part}/approve`) → `ServiceRequiredPart::approve()`.
- Authorized via `ServiceRequiredPartPolicy@approve` (owner/admin/manager — the authorized warehouse function; **no new official role**).
- Runs in a DB transaction with `lockForUpdate()` on the product row.
- Validates the request is `requested` (idempotent if already approved/reserved).
- Checks `available = physical − reserved(other requests)`; throws if the requested qty exceeds available.
- Sets status `approved`, `reserved_qty = qty`. Physical stock unchanged. No mutation.

## 7. Reservation Flow

- `Product::reserved_quantity` = Σ qty of `ServiceRequiredPart` in `approved`/`reserved` state for that product.
- `Product::available_quantity = max(0, stock_quantity − reserved_quantity)`.
- Reservation/release is **not** a physical mutation (no `InventoryMutation`).
- Concurrency: all reservation mutations (approve / consume / cancel / release / return) acquire the product row lock inside a transaction, preventing double reservation, over-reservation and negative available stock. Repeated approval is idempotent.

## 8. Consumption Flow

- `ServicePartController::usePart()` (`POST /service-parts/{part}/use`) → `ServiceRequiredPart::use()` — **the single place physical stock is reduced for a service part**.
- Authorized via `ServiceRequiredPartPolicy@consume` (owner/admin/manager/cs/cashier).
- Transactional (product row locked):
  1. Validate status `approved`/`reserved` (reservation exists); reject `used` (idempotency).
  2. Validate quantity > 0 and physical stock sufficient.
  3. Consume reservation (`reserved_qty = 0`, status `used`).
  4. `Product::reduceStock()` — **exactly once**.
  5. Create `ServicePartUsage`.
  6. Create `ServiceSparepart` (becomes billable on the invoice).
  7. Create exactly one `InventoryMutation` (`type='keluar'`, `reference_type='service_part_usage'`).
  8. Dispatch `PartUsed` + `PartAddedToInvoice` events.

## 9. Invoice Flow

- `SaleStoreController::draftFromService()` reads `$service->spareparts()` + `service_charge` — unchanged logic, now fed by the **canonical** consumed parts only.
- It cannot invoice: requested-but-unapproved, approved-but-unconsumed, cancelled, or returned parts (they never get a `ServiceSparepart` row).
- `saleItemAffectsStock()` still skips deduction for service-linked sales (stock already deducted at CS confirmation) — no double deduction, labor + parts totals verified by test (`250000 = 150000 labor + 100000 part`).

## 10. Return Flow

- `ServicePartController::requestReturn()` (`POST /services/{service}/parts/return-request`) records a `ServicePartReturn` linked to the originating `ServiceRequiredPart` (optional `service_required_part_id`).
- `ServicePartController::processReturn()` (`POST /service-part-returns/{return}/process`), authorized (owner/admin/manager/cs/cashier), distinguishes:

  - **Case A — reserved but never consumed** (`approved`/`reserved`): `releaseReservation()` → reservation released, physical stock unchanged, no mutation.
  - **Case B — consumed but returned unused** (`used`): `returnToStock()` → restore stock once, one reversal `InventoryMutation` (`type='masuk'`, `reference_type='service_part_return'`), remove the billable `ServiceSparepart`, zero the usage quantity, adjust a **DRAFT** sale (remove the part line, recompute totals).
  - **Idempotent**: already-`processed` returns are no-ops (no double restore).
  - **Finalized/PAID invoice**: operation is **blocked** (financial reversal is a documented P2 dependency) — nothing is silently mutated.

## 11. Inventory Mutation Integrity

- Consume service part → **one** `keluar` mutation.
- Return consumed part → **one** `masuk` reversal (identified by `reference_type='service_part_return'`).
- Reservation / release / request / approval / rejection → **no mutation**.
- Never double-log physical deduction; reversal uses the schema-valid enum value `masuk` (the column enum is `masuk|keluar|transfer`).

## 12. Authorization

- New `ServiceRequiredPartPolicy` registered in `AuthServiceProvider`; enforced in `ServicePartController`.
- Technician → request part, see state, cancel own request.
- Admin/Manager → approve / reject (authorized warehouse function).
- CS / Kasir → confirm billable usage (consume), process returns.
- Owner → all of the above (global).
- No new official role created; existing ServiceKU role architecture respected.

## 13. Concurrency Protection

- All reservation/consumption/return mutations run inside `DB::transaction()` with `Product::query()->lockForUpdate()`.
- Two concurrent approvals against limited stock: the second is rejected when available < requested (`physical=1`, service A reserves 1, service B blocked).
- Idempotency: repeated approval (no double reserve), repeated consume (rejected once `used`), repeated return (no double restore).

## 14. UI Wiring

- `ServiceWorkspace/sections/Spareparts.vue` (existing Enterprise design system):
  - Clear 5-state distinction: **Diminta / Disetujui-Reserved / Dipakai (Masuk Invoice) / Dikembalikan / Ditolak-Dibatalkan**.
  - Stock info per part (physical / reserved / available).
  - Actions call **real backend endpoints**: approve, reject, cancel, and CS **confirm (consume)**; a technician **Request Part** form (branch-scoped product list with available qty).
- `useServiceWorkspace.js` adds `canConsumeParts` (owner/admin/manager/cs/cashier).
- `Repair.vue`: removing the misleading "Sparepart Dipakai" selection from Finish — finishing a repair no longer consumes stock (info banner added).
- No browser-only state as source of truth; no new frontend framework.

## 15. Tests Before Fix (baseline — executable, expected to fail)

| File | Result | Failures |
|---|---|---|
| `BR07PartApprovalInvoiceTest` | 5 failed / 6 passed | approval reservation, available=physical−reserved, consumption reservation, part not on invoice, repeated consume not guarded, repair finish deducts stock |
| `BR09ReservedStockTest` | 5 failed | reservation > available, second reservation, approval idempotency, cancellation release, rejected request (no reject route) |
| `BR08PartReturnTest` | 7 failed | reserved-only release, consumed restore, reversal mutation (also `branch_id` NOT NULL bug), repeated return, invoice draft, paid-invoice block |

Total before fix: **17 failed** (all three skeletons converted from `markTestIncomplete` into executable feature tests).

## 16. Tests After Fix

| File | Result |
|---|---|
| `BR07PartApprovalInvoiceTest` | **11/11 PASS** |
| `BR09ReservedStockTest` | **5/5 PASS** |
| `BR08PartReturnTest` | **7/7 PASS** |
| **Subtotal (BR scope)** | **23 PASS / 110 assertions** |

**Full PHP suite (final):** `419 passed / 2 failed / 17 incomplete` — 1247 assertions — **588.87s**

The 2 failures are **pre-existing and outside BR-FIX-01 scope** (see §18, §22). The 17 incomplete are the other pre-existing Business Reality skeleton tests (BR01–BR06, BR10–BR20) — explicitly excluded by scope (BR-007/008/009 only).

## 17. Full Lifecycle Regression

- `tests/Feature/Tenant/ServiceFullLifecycleTest.php` — updated to the **canonical sequence** (request → approve → CS confirm/consume → repair finish → QC → invoice → payment → pickup → close). **PASS** (stock deducted exactly once, invoice total correct).
- `TenantRepairQcPhase3Test` — TEST 4/5/11 updated to canonical (approval rejects over-reservation; part usage via request→approve→consume; branch isolation at request time). **PASS**.
- `TenantRepairNotesPhotosPartsPhase3CTest` — repair-evidence test updated to canonical. **PASS**.
- `TenantE2EPhase5ProductionQATest` — 3 test-fixture/expectation defects fixed against canonical behavior; file now **PASS (7/7)**.
- Final targeted run (8 files): **54 PASS / 308 assertions**.

## 18. Remaining Risks

1. **Pre-existing failures outside BR-FIX-01 scope** (verified, not regressions — BR-FIX-01 did not touch these code paths):
   - `GoogleDrivePhotoServiceTest::test_constructor_with_unexpired_token_initializes_client` — asserts the Google Drive client is initialized, but the test environment has **no Google OAuth credentials** (`services.google.client_id/secret`), so `client` stays `null`. Environment-dependent test defect (needs real credentials / mock).
   - `TenantCsWorkflowGuardTest::test_cs_cannot_create_service_for_customer_from_other_branch` — `StoreServiceRequest` has no cross-branch `customer_id` guard, so the service is created and no `customer_id` validation error is returned. Real pre-existing app gap (cross-branch customer guard at service intake) — **explicitly out of BR-FIX-01 scope** (cross-branch/permissions are excluded).
2. **Refund/adjustment for finalized invoices** (BR-008 case B on a PAID sale) is intentionally **blocked**; a refund/adjustment policy is a documented P2 dependency.
3. `inventory_mutations.type` enum (`masuk|keluar|transfer`) constrains mutation semantics; reversals are encoded via `reference_type='service_part_return'` rather than a new enum value (kept migration-safe).
4. Legacy `ServicePartReturn` rows without `service_required_part_id` fall back to a generic cancel/restore path and rely on the processed-flag idempotency.
5. `InventoryIntelligenceController::approvePart/returnPart` are unrouted duplicates aligned to canonical behavior but remain unused by the UI.

## 19. BR-007 Verdict — **PASS**

Technician Request → Approval → Reservation → CS Confirmation → Stock Deduction (once) → Usage → Invoice works exactly once and in this order. Technician finishing a repair no longer consumes physical inventory.

## 20. BR-009 Verdict — **PASS**

Approval creates a reservation derived from authoritative records: physical stock unchanged, reserved increases, available = physical − reserved. Over-reservation and negative available are prevented; cancellation/rejection never reserve.

## 21. BR-008 Verdict — **PASS**

Reserved-only cancellation/return releases the reservation with no physical change; consumed-part return restores stock once with a single reversal mutation and adjusts a non-finalized draft invoice; finalized/PAID invoices are never silently modified (blocked).

## 22. Final Acceptance Verification

| Check | Result | Evidence |
|---|---|---|
| Targeted BR tests (BR07/BR09/BR08) | **PASS** | 23/23 (110 assertions) |
| Full Lifecycle (`ServiceFullLifecycleTest`) | **PASS** | canonical sequence, stock deducted once, invoice total correct |
| Legacy regression tests (phase3 repair/QC/parts) | **PASS** | `TenantRepairQcPhase3Test` + `TenantRepairNotesPhotosPartsPhase3CTest` updated to canonical, all pass |
| E2E Production QA (`TenantE2EPhase5ProductionQATest`) | **PASS** | 7/7 after fixing 3 test-fixture/expectation defects (branch-scoped fixtures; QC by manager; close accepts `diambil`) |
| Full PHP suite | **419 passed / 2 failed / 17 incomplete** (1247 assertions, 588.87s) | 2 failures pre-existing & out of scope; 17 incomplete are pre-existing BR skeletons |
| Frontend build (`npm run build`) | **PASS** | `✓ built in 24.93s`, no Vue warnings/errors; `Spareparts.vue`, `Repair.vue`, `ServiceWorkspace/Index.vue`, `useServiceWorkspace.js` compile |
| Migration verification | **PASS** | 3/3: column+FK (nullOnDelete via PRAGMA), existing-rows nullable-safe, `up→down→up` roundtrip |

No `markTestIncomplete` / `markTestSkipped` / placeholder assertions remain in `BR07PartApprovalInvoiceTest`, `BR09ReservedStockTest`, or `BR08PartReturnTest` — all acceptance scenarios execute.

**Full-suite failure breakdown (both pre-existing, evidence-based):**

| Test | Cause | Classification |
|---|---|---|
| `GoogleDrivePhotoServiceTest::test_constructor_with_unexpired_token_initializes_client` | asserts the Google Drive `client` is initialized, but the test environment has **no Google OAuth credentials** (`services.google.client_id/secret`), so `client` stays `null` | **Pre-existing test defect** (environment-dependent; BR-FIX-01 did not touch `GoogleDrivePhotoService`/config) |
| `TenantCsWorkflowGuardTest::test_cs_cannot_create_service_for_customer_from_other_branch` | `StoreServiceRequest` has **no cross-branch `customer_id` guard**, so the service is created and no `customer_id` validation error is returned | **Pre-existing real application gap** (cross-branch customer guard at service intake; explicitly **out of BR-FIX-01 scope** — cross-branch/permissions excluded) |

## 23. Final Verdict — **C. PASS — BR-FIX-01 VERIFIED AND REGRESSION-SAFE**

BR-FIX-01 introduced **no regression**. Every BR-FIX-01 acceptance criterion is verified:

- **BR-007** — Technician Request → Approval → Reservation → CS Confirmation → Stock Deduction (once) → Usage → Invoice works exactly once and in order; repair finish no longer consumes inventory. **PASS**
- **BR-009** — Reserved stock reduces AVAILABLE without reducing PHYSICAL; derived from authoritative records; over-reservation prevented. **PASS**
- **BR-008** — Cancellation/return correctly reverses reservation or stock without duplicate mutation; paid invoices never silently modified. **PASS**
- Targeted BR tests **23/23**, Full Lifecycle **PASS**, legacy phase3 **PASS**, E2E Production QA **7/7 PASS**, Migration **PASS**, Frontend build **PASS**.
- Remaining full-suite failures are pre-existing and outside scope (Google Drive credential-dependent test defect; missing cross-branch customer guard app gap).

**STOP — no further Business Reality work.** BR-004, BR-017, warranty, permissions, and Master Data are not started.
