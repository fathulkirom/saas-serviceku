<?php

namespace Tests\Feature;

use App\Models\Tenant\Sale;
use App\Models\Tenant\Service;
use Tests\TestCase;

class TenantSaleBranchGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_finance_only_lists_sales_from_active_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerA = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branchA->id]);
        $customerA = $this->createCustomer(['branch_id' => $branchA->id]);
        $customerB = $this->createCustomer(['branch_id' => $branchB->id]);

        $saleA = $this->createSale(['branch_id' => $branchA->id, 'customer_id' => $customerA->id]);
        $saleB = $this->createSale(['branch_id' => $branchB->id, 'customer_id' => $customerB->id]);

        $this->actingAs($ownerA);

        $response = $this->get(route('keuangan.index'));
        $response->assertOk();
        $page = $response->viewData('page');

        $rows = collect($page['props']['sales']['data'] ?? [])->map(fn ($row) => (array) $row)->values()->all();
        $ids = array_map(fn ($row) => $row['id'] ?? null, $rows);

        $this->assertContains($saleA->id, $ids);
        $this->assertNotContains($saleB->id, $ids);
    }

    public function test_cannot_draft_from_service_of_other_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerA = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branchA->id]);
        $ownerB = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branchB->id]);
        $customerB = $this->createCustomer(['branch_id' => $branchB->id]);

        $service = $this->createService([
            'branch_id' => $branchB->id,
            'customer_id' => $customerB->id,
            'created_by' => $ownerB->id,
            'status' => Service::STATUS_SELESAI,
            'service_charge' => 50000,
            'total_cost' => 50000,
        ]);

        $beforeCount = Sale::count();

        $this->actingAs($ownerA);

        $response = $this->post(route('sales.draft-from-service', $service));

        $response->assertSessionHas('error');
        $this->assertSame($beforeCount, Sale::count());
    }
}
