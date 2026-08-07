<?php

namespace Tests\Feature;

use App\Models\Tenant\Service;
use Tests\TestCase;

class TenantWarrantyClaimWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_can_create_warranty_claim_and_set_created_by_current_user(): void
    {
        $branch = $this->createBranch(['name' => 'Cabang A']);

        $owner = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branch->id,
        ]);

        $cs = $this->createTenantUser([
            'role' => 'cs',
            'branch_id' => $branch->id,
        ]);

        $customer = $this->createCustomer([
            'branch_id' => $branch->id,
        ]);

        $parentService = $this->createService([
            'branch_id' => $cs->branch_id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'tipe_unit' => 'Samsung S23',
            'imei_sn' => 'IMEI-001',
        ]);

        $parentService->update([
            'warranty_expired_at' => now()->addDays(7),
        ]);

        $this->assertSame((int) $cs->branch_id, (int) $parentService->branch_id);
        $this->assertTrue((bool) $parentService->fresh()->isWarrantyValid());

        // BR-FIX-04.1: OPEN records the complaint only (submitted, no rework).
        $this->actingAs($cs);

        $response = $this->post(route('services.warranty-claim', $parentService), [
            'problem_description' => 'Klaim layar berkedip',
        ]);

        $response->assertRedirect();

        $claim = \App\Models\Tenant\ServiceWarrantyClaim::where('service_id', $parentService->id)->first();
        $this->assertNotNull($claim);
        $this->assertSame('submitted', $claim->status);
        $this->assertNull($claim->approved_by);
        $this->assertNull($claim->rework_service_id);
        $this->assertSame(0, Service::where('parent_service_id', $parentService->id)->count());

        // AUTHORIZED APPROVAL creates the rework (created_by = approver).
        $this->actingAs($owner);
        $this->post(route('warranty-claims.decide', $claim), [
            'decision' => 'approve', 'note' => 'Disetujui',
        ]);

        $claimService = Service::query()
            ->where('parent_service_id', $parentService->id)
            ->latest('id')
            ->first();

        $this->assertNotNull($claimService);
        $this->assertSame($owner->id, $claimService->created_by);
        $this->assertTrue((bool) $claimService->is_warranty_claim);
        $this->assertSame($parentService->customer_id, $claimService->customer_id);
        $this->assertSame('Klaim layar berkedip', $claimService->problem_description);
        $this->assertSame($claimService->id, $claim->fresh()->rework_service_id);
    }

    public function test_cannot_create_warranty_claim_when_warranty_expired(): void
    {
        $branch = $this->createBranch(['name' => 'Cabang A']);

        $owner = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branch->id,
        ]);

        $cs = $this->createTenantUser([
            'role' => 'cs',
            'branch_id' => $branch->id,
        ]);

        $customer = $this->createCustomer([
            'branch_id' => $branch->id,
        ]);

        $parentService = $this->createService([
            'branch_id' => $cs->branch_id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
        ]);

        $parentService->update([
            'warranty_expired_at' => now()->subDay(),
        ]);

        $this->assertSame((int) $cs->branch_id, (int) $parentService->branch_id);
        $this->assertFalse((bool) $parentService->fresh()->isWarrantyValid());

        $beforeCount = Service::count();

        $this->actingAs($cs);

        $response = $this->post(route('services.warranty-claim', $parentService), [
            'problem_description' => 'Klaim garansi',
        ]);

        $response->assertRedirect();
        $this->assertSame($beforeCount, Service::count());
    }

    public function test_cannot_create_warranty_claim_for_service_from_other_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerBranchB = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branchB->id,
        ]);

        $csBranchA = $this->createTenantUser([
            'role' => 'cs',
            'branch_id' => $branchA->id,
        ]);

        $customerBranchB = $this->createCustomer([
            'branch_id' => $branchB->id,
        ]);

        $parentService = $this->createService([
            'branch_id' => $branchB->id,
            'customer_id' => $customerBranchB->id,
            'created_by' => $ownerBranchB->id,
            'warranty_expired_at' => now()->addDays(3),
        ]);

        $beforeCount = Service::count();

        $this->actingAs($csBranchA);

        $response = $this->post(route('services.warranty-claim', $parentService), [
            'problem_description' => 'Klaim lintas cabang',
        ]);

        $response->assertRedirect();
        $this->assertSame($beforeCount, Service::count());

        $crossBranchClaim = Service::query()
            ->where('parent_service_id', $parentService->id)
            ->first();

        $this->assertNull($crossBranchClaim);
    }
}
