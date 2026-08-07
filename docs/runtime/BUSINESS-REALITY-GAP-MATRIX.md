# SERVICEKU BUSINESS REALITY UAT

## Executive Result

**PARTIAL — PILOT POSSIBLE AFTER P0/P1 FIXES**

While the happy path works, real-world operational scenarios reveal significant gaps in branch scope handling, stock mutation timing, and warranty workflows.

## Scenario Matrix

| ID | Scenario | Result | Technical Gap | Business Risk | Priority |
|---|---|---|---|---|---|
| BR-001 | CS TEMPORARY REPLACEMENT | FAIL | Role hardcoding in policies prevents technicians or other staff from acting as CS temporarily without changing their primary role. | High | P1 |
| BR-002 | TECHNICIAN FORGOT TO FINISH | PASS | None | Low | None |
| BR-003 | MULTIPLE DEVICES ONE CUSTOMER | PASS | None | Low | None |
| BR-004 | CROSS BRANCH PICKUP | FAIL | ServiceDeliveryController@pickup strictly enforces user branch_id == service branch_id. No ServiceTransfer functionality exists. | Medium | P1 |
| BR-005 | BRANCH STOCK VISIBILITY | PARTIAL | Branch grouping for stock visibility is not implemented. Stock is either strictly isolated or fully global depending on the query. | Medium | P2 |
| BR-006 | CROSS TENANT CUSTOMER | PASS | None | Low | None |
| BR-007 | PART REQUEST / APPROVAL / INVOICE | FAIL | completeRepair immediately deducts stock when technician finishes repair, rather than waiting for CS to confirm invoice. | High | P0 |
| BR-008 | PART RETURN | NOT IMPLEMENTED | No workflow for returning unused approved parts. | Medium | P2 |
| BR-009 | RESERVED STOCK | NOT IMPLEMENTED | Product model does not track reserved_quantity properly in relation to approved requests. | Medium | P1 |
| BR-010 | LOCAL PURCHASE | PARTIAL | Purchase can be made but specific historical date tracking and petty cash/emergency tag might be missing. | Low | P3 |
| BR-011 | WARRANTY REPAIR RETURN | NOT IMPLEMENTED | ServiceWarranty exists but flow to create a NEW Service linked to warranty claim with different diagnosis is missing. | High | P1 |
| BR-012 | WARRANTY REFUND | NOT IMPLEMENTED | No accounting/refund capability for warranties. | Medium | P2 |
| BR-013 | DISTRIBUTOR LIFETIME WARRANTY | NOT IMPLEMENTED | Store warranty != distributor warranty not supported. | Low | P3 |
| BR-014 | CROSS BRANCH COMPLAINT | NOT IMPLEMENTED | Commission logic does not handle cross-branch rework. | Medium | P2 |
| BR-015 | TECHNICIAN BONUS VARIANTS | PARTIAL | Basic commissions supported, but complex variable percentages by repair type / device type are not fully mapped. | Medium | P2 |
| BR-016 | OWNER FAMILY LIMITED ACCESS | FAIL | Roles are hardcoded (owner, admin, manager, cs, technician). No custom permission profiles. | Medium | P2 |
| BR-017 | MANAGER MULTI BRANCH | FAIL | Users belong to a single branch_id. user_branches pivot is missing or not used in authorization. | High | P1 |
| BR-018 | EXTERNAL REPAIR PARTNER | PARTIAL | STATUS_ONPARTNER exists, but full vendor cost tracking and external portal are incomplete. | Medium | P2 |
| BR-019 | USER PLAN DOWNGRADE | FAIL | Plan limits (max_users) are checked on creation, but active users are not automatically restricted on downgrade. | Medium | P2 |
| BR-020 | REOPEN CLOSED SERVICE | NOT IMPLEMENTED | No route/controller method to reopen a closed service. | Medium | P2 |

## Detailed Results

### BR-001 — CS TEMPORARY REPLACEMENT
- **Expected**: Authorized non-CS employee can temporarily handle CS duties.
- **Actual**: ServicePolicy strictly checks role == cs/admin/manager/owner.
- **Test**: `tests/Feature/BusinessReality/BR01TemporaryCsReplacementTest.php`
- **Bug/Gap**: Role hardcoding in policies prevents technicians or other staff from acting as CS temporarily without changing their primary role.
- **Severity**: P1

### BR-002 — TECHNICIAN FORGOT TO FINISH
- **Expected**: Manager can override and finish repair.
- **Actual**: TechnicianWorkflowController@completeRepair allows admin/manager/owner to bypass technician_id check.
- **Test**: `tests/Feature/BusinessReality/BR02TechnicianOverrideTest.php`
- **Bug/Gap**: None
- **Severity**: None

### BR-003 — MULTIPLE DEVICES ONE CUSTOMER
- **Expected**: Customer can have multiple independent services.
- **Actual**: Services are independent records linked to one customer.
- **Test**: `tests/Feature/BusinessReality/BR03MultipleDevicesTest.php`
- **Bug/Gap**: None
- **Severity**: None

### BR-004 — CROSS BRANCH PICKUP
- **Expected**: Customer can pick up device at another branch via transfer.
- **Actual**: 403 abort if branch_id does not match.
- **Test**: `tests/Feature/BusinessReality/BR04CrossBranchPickupTest.php`
- **Bug/Gap**: ServiceDeliveryController@pickup strictly enforces user branch_id == service branch_id. No ServiceTransfer functionality exists.
- **Severity**: P1

### BR-005 — BRANCH STOCK VISIBILITY
- **Expected**: Nearby branches can view each other's stock.
- **Actual**: No branch grouping concept.
- **Test**: `tests/Feature/BusinessReality/BR05StockVisibilityTest.php`
- **Bug/Gap**: Branch grouping for stock visibility is not implemented. Stock is either strictly isolated or fully global depending on the query.
- **Severity**: P2

### BR-006 — CROSS TENANT CUSTOMER
- **Expected**: Tenant data is completely isolated.
- **Actual**: Stancl/Tenancy ensures isolated databases or scoped queries.
- **Test**: `tests/Feature/BusinessReality/BR06CrossTenantIsolationTest.php`
- **Bug/Gap**: None
- **Severity**: None

### BR-007 — PART REQUEST / APPROVAL / INVOICE
- **Expected**: CS adds approved part to invoice -> stock decreases.
- **Actual**: TechnicianWorkflowController@completeRepair triggers InventoryMutation directly.
- **Test**: `tests/Feature/BusinessReality/BR07PartApprovalInvoiceTest.php`
- **Bug/Gap**: completeRepair immediately deducts stock when technician finishes repair, rather than waiting for CS to confirm invoice.
- **Severity**: P0

### BR-008 — PART RETURN
- **Expected**: Return workflow restores stock.
- **Actual**: No endpoint/logic for part returns.
- **Test**: `tests/Feature/BusinessReality/BR08PartReturnTest.php`
- **Bug/Gap**: No workflow for returning unused approved parts.
- **Severity**: P2

### BR-009 — RESERVED STOCK
- **Expected**: Approved request reserves stock (Available = Physical - Reserved).
- **Actual**: Stock is only deduced upon usage.
- **Test**: `tests/Feature/BusinessReality/BR09ReservedStockTest.php`
- **Bug/Gap**: Product model does not track reserved_quantity properly in relation to approved requests.
- **Severity**: P1

### BR-010 — LOCAL PURCHASE
- **Expected**: Support emergency local purchases.
- **Actual**: Purchase module exists but basic.
- **Test**: `tests/Feature/BusinessReality/BR10LocalPurchaseTest.php`
- **Bug/Gap**: Purchase can be made but specific historical date tracking and petty cash/emergency tag might be missing.
- **Severity**: P3

### BR-011 — WARRANTY REPAIR RETURN
- **Expected**: Warranty claim creates new linked service.
- **Actual**: No automated workflow for warranty rework.
- **Test**: `tests/Feature/BusinessReality/BR11WarrantyReworkTest.php`
- **Bug/Gap**: ServiceWarranty exists but flow to create a NEW Service linked to warranty claim with different diagnosis is missing.
- **Severity**: P1

### BR-012 — WARRANTY REFUND
- **Expected**: Support full/partial refunds.
- **Actual**: No refund feature.
- **Test**: `tests/Feature/BusinessReality/BR12WarrantyRefundTest.php`
- **Bug/Gap**: No accounting/refund capability for warranties.
- **Severity**: P2

### BR-013 — DISTRIBUTOR LIFETIME WARRANTY
- **Expected**: Track distributor warranty separately.
- **Actual**: Only tracks store warranty.
- **Test**: `tests/Feature/BusinessReality/BR13DistributorWarrantyTest.php`
- **Bug/Gap**: Store warranty != distributor warranty not supported.
- **Severity**: P3

### BR-014 — CROSS BRANCH COMPLAINT
- **Expected**: Rework by different technician handles commission appropriately.
- **Actual**: Not implemented.
- **Test**: `tests/Feature/BusinessReality/BR14CrossBranchComplaintTest.php`
- **Bug/Gap**: Commission logic does not handle cross-branch rework.
- **Severity**: P2

### BR-015 — TECHNICIAN BONUS VARIANTS
- **Expected**: Flexible commission matrix.
- **Actual**: Commission model exists but might be simplistic.
- **Test**: `tests/Feature/BusinessReality/BR15CommissionCapabilityTest.php`
- **Bug/Gap**: Basic commissions supported, but complex variable percentages by repair type / device type are not fully mapped.
- **Severity**: P2

### BR-016 — OWNER FAMILY LIMITED ACCESS
- **Expected**: Custom role for family with restricted visibility.
- **Actual**: Cannot restrict owner role, cannot expand CS role.
- **Test**: `tests/Feature/BusinessReality/BR16LimitedOwnerFamilyAccessTest.php`
- **Bug/Gap**: Roles are hardcoded (owner, admin, manager, cs, technician). No custom permission profiles.
- **Severity**: P2

### BR-017 — MANAGER MULTI BRANCH
- **Expected**: Manager can oversee multiple branches.
- **Actual**: Single branch_id per user.
- **Test**: `tests/Feature/BusinessReality/BR17MultiBranchManagerTest.php`
- **Bug/Gap**: Users belong to a single branch_id. user_branches pivot is missing or not used in authorization.
- **Severity**: P1

### BR-018 — EXTERNAL REPAIR PARTNER
- **Expected**: External repair tracked with costs.
- **Actual**: Status transition exists, financial tracking missing.
- **Test**: `tests/Feature/BusinessReality/BR18ExternalPartnerTest.php`
- **Bug/Gap**: STATUS_ONPARTNER exists, but full vendor cost tracking and external portal are incomplete.
- **Severity**: P2

### BR-019 — USER PLAN DOWNGRADE
- **Expected**: Downgrade enforces new user limits gracefully.
- **Actual**: Limits typically only checked at store() method.
- **Test**: `tests/Feature/BusinessReality/BR19PlanDowngradeTest.php`
- **Bug/Gap**: Plan limits (max_users) are checked on creation, but active users are not automatically restricted on downgrade.
- **Severity**: P2

### BR-020 — REOPEN CLOSED SERVICE
- **Expected**: Authorized user can reopen service with reason.
- **Actual**: ServiceWorkflowController has close() but no reopen().
- **Test**: `tests/Feature/BusinessReality/BR20ServiceReopenTest.php`
- **Bug/Gap**: No route/controller method to reopen a closed service.
- **Severity**: P2

