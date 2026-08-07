<?php

namespace Tests\Feature\BusinessReality;

use App\Models\Tenant\BranchVisibility;
use App\Models\Tenant\ServiceRequiredPart;
use App\Models\Tenant\InventoryMutation;
use App\Services\BranchAccessService;

/**
 * BR-005 — BRANCH STOCK VISIBILITY.
 *
 * Configured branches may READ each other's stock (branch_visibility pivot).
 * This is READ VISIBILITY ONLY — it never grants mutation, transfer, service,
 * or financial authority. Removing the relationship removes visibility.
 */
class BR05StockVisibilityTest extends BRBranchOperationsTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Config: Branch A may READ Branch B stock. (D stays isolated.)
        BranchVisibility::create(['branch_id' => $this->branchA->id, 'visible_branch_id' => $this->branchB->id]);
    }

    public function test_branch_a_sees_configured_b_stock()
    {
        $this->makeProduct($this->branchA, 'LCD');
        $this->makeProduct($this->branchB, 'LCD');
        $this->makeProduct($this->branchD, 'LCD');

        $visibleIds = BranchAccessService::visibleBranchIds($this->csA);
        $this->assertContains($this->branchA->id, $visibleIds);
        $this->assertContains($this->branchB->id, $visibleIds, 'Configured visible branch B must be readable.');

        $productIds = BranchAccessService::stockVisibilityScope(\App\Models\Tenant\Product::query(), $this->csA)
            ->pluck('branch_id')
            ->map(fn($id) => (int) $id)
            ->all();
        $this->assertContains($this->branchB->id, $productIds, 'A must see B products.');
    }

    public function test_branch_a_does_not_see_unrelated_d_stock()
    {
        $this->makeProduct($this->branchD, 'LCD');

        $visibleIds = BranchAccessService::visibleBranchIds($this->csA);
        $this->assertNotContains($this->branchD->id, $visibleIds, 'D is not in the visibility group.');

        $productIds = BranchAccessService::stockVisibilityScope(\App\Models\Tenant\Product::query(), $this->csA)
            ->pluck('branch_id')->all();
        $this->assertNotContains($this->branchD->id, $productIds);
    }

    public function test_visible_b_stock_is_labeled_b()
    {
        $productB = $this->makeProduct($this->branchB, 'LCD');

        $visible = BranchAccessService::stockVisibilityScope(\App\Models\Tenant\Product::query(), $this->csA)
            ->with('branch')
            ->where('id', $productB->id)
            ->first();

        $this->assertNotNull($visible, 'B product visible to A.');
        $this->assertEquals($this->branchB->id, $visible->branch_id);
        $this->assertEquals('Cabang B', $visible->branch->name, 'Frontend can label the owning branch.');
    }

    public function test_read_visibility_does_not_allow_stock_mutation()
    {
        $productB = $this->makeProduct($this->branchB, 'LCD');

        // B is visible (read) but NOT accessible (canAccess) for A's CS.
        $this->assertContains($this->branchB->id, BranchAccessService::visibleBranchIds($this->csA));
        $this->assertFalse(
            BranchAccessService::canAccess($this->csA, $this->branchB->id),
            'Read visibility MUST NOT grant mutation/access authority.'
        );
        $this->assertNotContains($this->branchB->id, BranchAccessService::accessibleBranchIds($this->csA));
    }

    public function test_branch_a_service_cannot_reserve_visible_remote_b_stock()
    {
        $productB = $this->makeProduct($this->branchB, 'LCD');
        $serviceA = $this->makeService($this->branchA, \App\Models\Tenant\Service::STATUS_DIKERJAKAN);

        // BR-FIX-01 safety: a service in A cannot reserve/consume B's visible stock.
        $this->actingAs($this->manager);
        $response = $this->post(route('service-parts.request', $serviceA), [
            'product_id' => $productB->id,
            'part_name' => $productB->name,
            'qty' => 1,
        ]);
        $response->assertStatus(422); // product not available in A's branch

        $this->assertEquals(0, ServiceRequiredPart::where('service_id', $serviceA->id)->count(), 'No request created.');
        $this->assertEquals(10, $productB->fresh()->stock_quantity, 'B stock untouched.');
        $this->assertEquals(0, InventoryMutation::where('product_id', $productB->id)->count(), 'No mutation on B stock.');
    }

    public function test_removing_visibility_relationship_removes_stock_visibility()
    {
        $this->makeProduct($this->branchB, 'LCD');
        $this->assertContains($this->branchB->id, BranchAccessService::visibleBranchIds($this->csA));

        // Remove the relationship (data-driven — no hardcoded branch ids).
        BranchVisibility::where('branch_id', $this->branchA->id)->where('visible_branch_id', $this->branchB->id)->delete();

        $this->assertNotContains(
            $this->branchB->id,
            BranchAccessService::visibleBranchIds($this->csA),
            'Removing the visibility relationship must remove stock visibility.'
        );
    }
}
