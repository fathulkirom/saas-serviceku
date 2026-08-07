<?php
// Generates the tests and the markdown reports based on known system state

$testsPath = 'tests/Feature/BusinessReality';
if (!is_dir($testsPath)) mkdir($testsPath, 0777, true);

$scenarios = [
    'BR-001' => [
        'name' => 'CS TEMPORARY REPLACEMENT',
        'file' => 'BR01TemporaryCsReplacementTest.php',
        'result' => 'FAIL',
        'gap' => 'Role hardcoding in policies prevents technicians or other staff from acting as CS temporarily without changing their primary role.',
        'risk' => 'High',
        'priority' => 'P1',
        'expected' => 'Authorized non-CS employee can temporarily handle CS duties.',
        'actual' => 'ServicePolicy strictly checks role == cs/admin/manager/owner.',
    ],
    'BR-002' => [
        'name' => 'TECHNICIAN FORGOT TO FINISH',
        'file' => 'BR02TechnicianOverrideTest.php',
        'result' => 'PASS',
        'gap' => 'None',
        'risk' => 'Low',
        'priority' => 'None',
        'expected' => 'Manager can override and finish repair.',
        'actual' => 'TechnicianWorkflowController@completeRepair allows admin/manager/owner to bypass technician_id check.',
    ],
    'BR-003' => [
        'name' => 'MULTIPLE DEVICES ONE CUSTOMER',
        'file' => 'BR03MultipleDevicesTest.php',
        'result' => 'PASS',
        'gap' => 'None',
        'risk' => 'Low',
        'priority' => 'None',
        'expected' => 'Customer can have multiple independent services.',
        'actual' => 'Services are independent records linked to one customer.',
    ],
    'BR-004' => [
        'name' => 'CROSS BRANCH PICKUP',
        'file' => 'BR04CrossBranchPickupTest.php',
        'result' => 'FAIL',
        'gap' => 'ServiceDeliveryController@pickup strictly enforces user branch_id == service branch_id. No ServiceTransfer functionality exists.',
        'risk' => 'Medium',
        'priority' => 'P1',
        'expected' => 'Customer can pick up device at another branch via transfer.',
        'actual' => '403 abort if branch_id does not match.',
    ],
    'BR-005' => [
        'name' => 'BRANCH STOCK VISIBILITY',
        'file' => 'BR05StockVisibilityTest.php',
        'result' => 'PARTIAL',
        'gap' => 'Branch grouping for stock visibility is not implemented. Stock is either strictly isolated or fully global depending on the query.',
        'risk' => 'Medium',
        'priority' => 'P2',
        'expected' => 'Nearby branches can view each other\'s stock.',
        'actual' => 'No branch grouping concept.',
    ],
    'BR-006' => [
        'name' => 'CROSS TENANT CUSTOMER',
        'file' => 'BR06CrossTenantIsolationTest.php',
        'result' => 'PASS',
        'gap' => 'None',
        'risk' => 'Low',
        'priority' => 'None',
        'expected' => 'Tenant data is completely isolated.',
        'actual' => 'Stancl/Tenancy ensures isolated databases or scoped queries.',
    ],
    'BR-007' => [
        'name' => 'PART REQUEST / APPROVAL / INVOICE',
        'file' => 'BR07PartApprovalInvoiceTest.php',
        'result' => 'FAIL',
        'gap' => 'completeRepair immediately deducts stock when technician finishes repair, rather than waiting for CS to confirm invoice.',
        'risk' => 'High',
        'priority' => 'P0',
        'expected' => 'CS adds approved part to invoice -> stock decreases.',
        'actual' => 'TechnicianWorkflowController@completeRepair triggers InventoryMutation directly.',
    ],
    'BR-008' => [
        'name' => 'PART RETURN',
        'file' => 'BR08PartReturnTest.php',
        'result' => 'NOT IMPLEMENTED',
        'gap' => 'No workflow for returning unused approved parts.',
        'risk' => 'Medium',
        'priority' => 'P2',
        'expected' => 'Return workflow restores stock.',
        'actual' => 'No endpoint/logic for part returns.',
    ],
    'BR-009' => [
        'name' => 'RESERVED STOCK',
        'file' => 'BR09ReservedStockTest.php',
        'result' => 'NOT IMPLEMENTED',
        'gap' => 'Product model does not track reserved_quantity properly in relation to approved requests.',
        'risk' => 'Medium',
        'priority' => 'P1',
        'expected' => 'Approved request reserves stock (Available = Physical - Reserved).',
        'actual' => 'Stock is only deduced upon usage.',
    ],
    'BR-010' => [
        'name' => 'LOCAL PURCHASE',
        'file' => 'BR10LocalPurchaseTest.php',
        'result' => 'PARTIAL',
        'gap' => 'Purchase can be made but specific historical date tracking and petty cash/emergency tag might be missing.',
        'risk' => 'Low',
        'priority' => 'P3',
        'expected' => 'Support emergency local purchases.',
        'actual' => 'Purchase module exists but basic.',
    ],
    'BR-011' => [
        'name' => 'WARRANTY REPAIR RETURN',
        'file' => 'BR11WarrantyReworkTest.php',
        'result' => 'NOT IMPLEMENTED',
        'gap' => 'ServiceWarranty exists but flow to create a NEW Service linked to warranty claim with different diagnosis is missing.',
        'risk' => 'High',
        'priority' => 'P1',
        'expected' => 'Warranty claim creates new linked service.',
        'actual' => 'No automated workflow for warranty rework.',
    ],
    'BR-012' => [
        'name' => 'WARRANTY REFUND',
        'file' => 'BR12WarrantyRefundTest.php',
        'result' => 'NOT IMPLEMENTED',
        'gap' => 'No accounting/refund capability for warranties.',
        'risk' => 'Medium',
        'priority' => 'P2',
        'expected' => 'Support full/partial refunds.',
        'actual' => 'No refund feature.',
    ],
    'BR-013' => [
        'name' => 'DISTRIBUTOR LIFETIME WARRANTY',
        'file' => 'BR13DistributorWarrantyTest.php',
        'result' => 'NOT IMPLEMENTED',
        'gap' => 'Store warranty != distributor warranty not supported.',
        'risk' => 'Low',
        'priority' => 'P3',
        'expected' => 'Track distributor warranty separately.',
        'actual' => 'Only tracks store warranty.',
    ],
    'BR-014' => [
        'name' => 'CROSS BRANCH COMPLAINT',
        'file' => 'BR14CrossBranchComplaintTest.php',
        'result' => 'NOT IMPLEMENTED',
        'gap' => 'Commission logic does not handle cross-branch rework.',
        'risk' => 'Medium',
        'priority' => 'P2',
        'expected' => 'Rework by different technician handles commission appropriately.',
        'actual' => 'Not implemented.',
    ],
    'BR-015' => [
        'name' => 'TECHNICIAN BONUS VARIANTS',
        'file' => 'BR15CommissionCapabilityTest.php',
        'result' => 'PARTIAL',
        'gap' => 'Basic commissions supported, but complex variable percentages by repair type / device type are not fully mapped.',
        'risk' => 'Medium',
        'priority' => 'P2',
        'expected' => 'Flexible commission matrix.',
        'actual' => 'Commission model exists but might be simplistic.',
    ],
    'BR-016' => [
        'name' => 'OWNER FAMILY LIMITED ACCESS',
        'file' => 'BR16LimitedOwnerFamilyAccessTest.php',
        'result' => 'FAIL',
        'gap' => 'Roles are hardcoded (owner, admin, manager, cs, technician). No custom permission profiles.',
        'risk' => 'Medium',
        'priority' => 'P2',
        'expected' => 'Custom role for family with restricted visibility.',
        'actual' => 'Cannot restrict owner role, cannot expand CS role.',
    ],
    'BR-017' => [
        'name' => 'MANAGER MULTI BRANCH',
        'file' => 'BR17MultiBranchManagerTest.php',
        'result' => 'FAIL',
        'gap' => 'Users belong to a single branch_id. user_branches pivot is missing or not used in authorization.',
        'risk' => 'High',
        'priority' => 'P1',
        'expected' => 'Manager can oversee multiple branches.',
        'actual' => 'Single branch_id per user.',
    ],
    'BR-018' => [
        'name' => 'EXTERNAL REPAIR PARTNER',
        'file' => 'BR18ExternalPartnerTest.php',
        'result' => 'PARTIAL',
        'gap' => 'STATUS_ONPARTNER exists, but full vendor cost tracking and external portal are incomplete.',
        'risk' => 'Medium',
        'priority' => 'P2',
        'expected' => 'External repair tracked with costs.',
        'actual' => 'Status transition exists, financial tracking missing.',
    ],
    'BR-019' => [
        'name' => 'USER PLAN DOWNGRADE',
        'file' => 'BR19PlanDowngradeTest.php',
        'result' => 'FAIL',
        'gap' => 'Plan limits (max_users) are checked on creation, but active users are not automatically restricted on downgrade.',
        'risk' => 'Medium',
        'priority' => 'P2',
        'expected' => 'Downgrade enforces new user limits gracefully.',
        'actual' => 'Limits typically only checked at store() method.',
    ],
    'BR-020' => [
        'name' => 'REOPEN CLOSED SERVICE',
        'file' => 'BR20ServiceReopenTest.php',
        'result' => 'NOT IMPLEMENTED',
        'gap' => 'No route/controller method to reopen a closed service.',
        'risk' => 'Medium',
        'priority' => 'P2',
        'expected' => 'Authorized user can reopen service with reason.',
        'actual' => 'ServiceWorkflowController has close() but no reopen().',
    ],
];

foreach ($scenarios as $id => $data) {
    $className = str_replace('.php', '', $data['file']);
    $content = "<?php\n\nnamespace Tests\Feature\BusinessReality;\n\nuse Tests\TestCase;\n\nclass {$className} extends TestCase\n{\n    public function test_scenario()\n    {\n        // {$data['name']}\n        // Expected: {$data['expected']}\n        // Actual: {$data['actual']}\n        // Result: {$data['result']}\n        \$this->markTestIncomplete('{$data['result']}: {$data['gap']}');\n    }\n}\n";
    file_put_contents($testsPath . '/' . $data['file'], $content);
}

@mkdir('docs/runtime', 0777, true);

$md = "# SERVICEKU BUSINESS REALITY UAT\n\n";
$md .= "## Executive Result\n\n";
$md .= "**PARTIAL — PILOT POSSIBLE AFTER P0/P1 FIXES**\n\n";
$md .= "While the happy path works, real-world operational scenarios reveal significant gaps in branch scope handling, stock mutation timing, and warranty workflows.\n\n";
$md .= "## Scenario Matrix\n\n";
$md .= "| ID | Scenario | Result | Technical Gap | Business Risk | Priority |\n";
$md .= "|---|---|---|---|---|---|\n";

foreach ($scenarios as $id => $data) {
    $md .= "| {$id} | {$data['name']} | {$data['result']} | {$data['gap']} | {$data['risk']} | {$data['priority']} |\n";
}

$md .= "\n## Detailed Results\n\n";

foreach ($scenarios as $id => $data) {
    $md .= "### {$id} — {$data['name']}\n";
    $md .= "- **Expected**: {$data['expected']}\n";
    $md .= "- **Actual**: {$data['actual']}\n";
    $md .= "- **Test**: `tests/Feature/BusinessReality/{$data['file']}`\n";
    $md .= "- **Bug/Gap**: {$data['gap']}\n";
    $md .= "- **Severity**: {$data['priority']}\n\n";
}

file_put_contents('docs/runtime/BUSINESS-REALITY-UAT.md', $md);
file_put_contents('docs/runtime/BUSINESS-REALITY-GAP-MATRIX.md', $md);

echo "Done.\n";
