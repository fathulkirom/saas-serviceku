<?php

namespace Tests\Feature\Tenant;

use Tests\TestCase;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceTransfer;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * BR-FIX-02 (STEP 17) — Tenant isolation for branch operations.
 *
 * Tenant isolation is ABSOLUTE (1 DB per tenant): a manager of Tenant A cannot
 * see/access a Branch, Customer, Product (stock), Service, transfer, or pickup
 * belonging to Tenant B.
 */
class TenantIsolationBranchTest extends TestCase
{
    use RefreshDatabase;

    public function test_cross_tenant_branch_customer_stock_and_service_are_isolated(): void
    {
        // ===== Tenant A =====
        $tenantA = $this->setUpTenant();
        $this->grantFullPlanAccess();
        $branchA = Branch::create(['name' => 'Branch A']);
        $managerA = $this->createTenantUser(['role' => 'manager', 'branch_id' => $branchA->id]);
        $customerA = Customer::create(['name' => 'Cust Tenant A', 'branch_id' => $branchA->id]);
        Product::create(['name' => 'Part Tenant A', 'branch_id' => $branchA->id, 'stock_quantity' => 5]);
        $serviceA = Service::create([
            'branch_id' => $branchA->id,
            'customer_id' => $customerA->id,
            'created_by' => $managerA->id,
            'status' => Service::STATUS_DIKERJAKAN,
            'problem_description' => 'SERVICE_TENANT_A',
        ]);

        // ===== Tenant B (separate database) =====
        $tenantB = $this->setUpTenant();
        $this->grantFullPlanAccess();
        $branchB = Branch::create(['name' => 'Branch B']);
        $customerB = Customer::create(['name' => 'Cust Tenant B', 'branch_id' => $branchB->id]);
        Product::create(['name' => 'Part Tenant B', 'branch_id' => $branchB->id, 'stock_quantity' => 7]);
        Service::create([
            'branch_id' => $branchB->id,
            'customer_id' => $customerB->id,
            'created_by' => 1,
            'status' => Service::STATUS_DIKERJAKAN,
            'problem_description' => 'SERVICE_TENANT_B',
        ]);

        // ===== Back to Tenant A, acting as manager A =====
        tenancy()->initialize($tenantA);
        $this->actingAs($managerA);

        // Tenant A cannot see ANY Tenant B record.
        $this->assertFalse(Branch::where('name', 'Branch B')->exists(), 'Tenant B branch invisible in Tenant A.');
        $this->assertFalse(Customer::where('name', 'Cust Tenant B')->exists(), 'Tenant B customer invisible.');
        $this->assertFalse(Product::where('name', 'Part Tenant B')->exists(), 'Tenant B stock invisible.');
        $this->assertFalse(Service::where('problem_description', 'SERVICE_TENANT_B')->exists(), 'Tenant B service invisible.');
        $this->assertTrue(Service::where('problem_description', 'SERVICE_TENANT_A')->exists(), 'Tenant A service intact.');
    }

    public function test_cross_tenant_transfer_is_rejected(): void
    {
        $tenantA = $this->setUpTenant();
        $this->grantFullPlanAccess();
        $branchA = Branch::create(['name' => 'Branch A']);
        $managerA = $this->createTenantUser(['role' => 'manager', 'branch_id' => $branchA->id]);
        $customerA = Customer::create(['name' => 'Cust A', 'branch_id' => $branchA->id]);
        $serviceA = Service::create([
            'branch_id' => $branchA->id,
            'customer_id' => $customerA->id,
            'created_by' => $managerA->id,
            'status' => Service::STATUS_SELESAI,
            'problem_description' => 'A',
        ]);

        // Tenant B branch exists only in Tenant B's DB.
        $this->setUpTenant();
        $this->grantFullPlanAccess();
        $branchB = Branch::create(['name' => 'Branch B']);

        tenancy()->initialize($tenantA);
        $this->actingAs($managerA);

        // Attempting to transfer to Tenant B's branch is impossible: that branch
        // is not a valid destination in Tenant A (no cross-DB record exists).
        $this->post(route('service-transfers.store'), [
            'service_id' => $serviceA->id,
            'to_branch_id' => (int) $branchB->id,
        ]);

        $this->assertEquals(0, ServiceTransfer::count(), 'No cross-tenant transfer record may be created.');
        $this->assertEquals($branchA->id, $serviceA->fresh()->branch_id, 'Origin untouched.');
    }

    public function test_cross_tenant_pickup_is_impossible(): void
    {
        $tenantA = $this->setUpTenant();
        $this->grantFullPlanAccess();
        $branchA = Branch::create(['name' => 'Branch A']);
        $managerA = $this->createTenantUser(['role' => 'manager', 'branch_id' => $branchA->id]);

        // Tenant B creates a service that does not exist in Tenant A.
        $this->setUpTenant();
        $this->grantFullPlanAccess();
        $branchB = Branch::create(['name' => 'Branch B']);
        $customerB = Customer::create(['name' => 'Cust B', 'branch_id' => $branchB->id]);
        $serviceB = Service::create([
            'branch_id' => $branchB->id,
            'customer_id' => $customerB->id,
            'created_by' => 1,
            'status' => Service::STATUS_SELESAI,
            'problem_description' => 'SERVICE_B',
        ]);

        tenancy()->initialize($tenantA);
        $this->actingAs($managerA);

        // Tenant B's service is not present in Tenant A — pickup is impossible.
        $this->assertFalse(Service::where('problem_description', 'SERVICE_B')->exists());
        $this->post(route('services.pickup', $serviceB->id))->assertStatus(404);
    }
}
