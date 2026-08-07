<?php

namespace Tests\Feature\BusinessReality;

use Tests\TestCase;
use App\Models\Tenant\User;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use App\Models\Tenant\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Shared setup for BR-017 (Manager Multi-Branch), BR-004 (Cross-Branch Pickup)
 * and BR-005 (Branch Stock Visibility) tests.
 *
 * Branches: A (manager primary), B (manager additional), C (unassigned), D
 * (isolated — never in the visibility group).
 */
abstract class BRBranchOperationsTestCase extends TestCase
{
    use RefreshDatabase;

    protected Branch $branchA;
    protected Branch $branchB;
    protected Branch $branchC;
    protected Branch $branchD;

    protected User $owner;
    protected User $manager; // primary A + additional B
    protected User $csA;
    protected User $csB;
    protected User $csC;
    protected User $techA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->grantFullPlanAccess();

        $this->branchA = Branch::create(['name' => 'Cabang A', 'is_active' => true]);
        $this->branchB = Branch::create(['name' => 'Cabang B', 'is_active' => true]);
        $this->branchC = Branch::create(['name' => 'Cabang C', 'is_active' => true]);
        $this->branchD = Branch::create(['name' => 'Cabang D', 'is_active' => true]);

        $this->owner = $this->createTenantUser(['name' => 'Owner', 'role' => 'owner', 'active' => true]);
        $this->manager = $this->createTenantUser(['name' => 'Manager', 'role' => 'manager', 'branch_id' => $this->branchA->id, 'active' => true]);
        $this->manager->branches()->sync([$this->branchB->id]);
        $this->manager->clearBranchAccessCache();

        $this->csA = $this->createTenantUser(['name' => 'CS A', 'role' => 'cs', 'branch_id' => $this->branchA->id, 'active' => true]);
        $this->csB = $this->createTenantUser(['name' => 'CS B', 'role' => 'cs', 'branch_id' => $this->branchB->id, 'active' => true]);
        $this->csC = $this->createTenantUser(['name' => 'CS C', 'role' => 'cs', 'branch_id' => $this->branchC->id, 'active' => true]);
        $this->techA = $this->createTenantUser(['name' => 'Tech A', 'role' => 'technician', 'branch_id' => $this->branchA->id, 'active' => true]);
    }

    protected function makeCustomer(Branch $branch): Customer
    {
        return Customer::create([
            'name' => 'Customer ' . $branch->name,
            'phone' => '08' . random_int(100000000, 999999999),
            'branch_id' => $branch->id,
        ]);
    }

    protected function makeService(Branch $branch, string $status = Service::STATUS_SIAP_DIAMBIL, array $extra = []): Service
    {
        $customer = $this->makeCustomer($branch);

        return Service::create(array_merge([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $this->owner->id,
            'technician_id' => $this->techA->id,
            'status' => $status,
            'service_charge' => 100000,
            'problem_description' => 'Perbaikan di ' . $branch->name,
        ], $extra));
    }

    protected function makeProduct(Branch $branch, string $name = 'Komponen'): Product
    {
        return Product::create([
            'name' => $name . ' ' . $branch->name,
            'branch_id' => $branch->id,
            'stock_quantity' => 10,
            'cost_price' => 10000,
            'selling_price' => 25000,
        ]);
    }
}
