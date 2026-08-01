<?php

namespace Tests\Feature;

use App\Models\Tenant\ChecklistTemplate;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceChecklist;
use Tests\TestCase;

class TenantServiceBranchIsolationEndpointsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_owner_cannot_save_checklist_for_service_from_other_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerBranchA = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branchA->id,
        ]);

        $ownerBranchB = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branchB->id,
        ]);

        $customerBranchB = $this->createCustomer([
            'branch_id' => $branchB->id,
        ]);

        $service = $this->createService([
            'branch_id' => $branchB->id,
            'customer_id' => $customerBranchB->id,
            'created_by' => $ownerBranchB->id,
        ]);

        $template = ChecklistTemplate::create([
            'name' => 'Template Masuk',
            'type' => 'masuk',
            'is_active' => true,
        ]);

        $beforeCount = ServiceChecklist::count();

        $this->actingAs($ownerBranchA);

        $response = $this->post(route('services.checklists.store', $service), [
            'checklist_template_id' => $template->id,
            'type' => 'masuk',
            'checked_items' => ['lcd', 'battery'],
            'notes' => 'Checklist lintas cabang',
        ]);

        $response->assertRedirect();
        $this->assertSame($beforeCount, ServiceChecklist::count());
    }

    public function test_owner_cannot_complete_service_from_other_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerBranchA = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branchA->id,
        ]);

        $ownerBranchB = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branchB->id,
        ]);

        $customerBranchB = $this->createCustomer([
            'branch_id' => $branchB->id,
        ]);

        $service = $this->createService([
            'branch_id' => $branchB->id,
            'customer_id' => $customerBranchB->id,
            'created_by' => $ownerBranchB->id,
            'service_charge' => 10000,
            'total_cost' => 10000,
            'status' => Service::STATUS_DIKERJAKAN,
        ]);

        $this->actingAs($ownerBranchA);

        $response = $this->post(route('services.complete', $service), [
            'service_charge' => 25000,
        ]);

        $response->assertRedirect();

        $fresh = $service->fresh();
        $this->assertSame('10000.00', (string) $fresh->service_charge);
        $this->assertSame('10000.00', (string) $fresh->total_cost);
    }
}
