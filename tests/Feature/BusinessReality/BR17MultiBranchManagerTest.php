<?php

namespace Tests\Feature\BusinessReality;

use App\Models\Tenant\Service;
use App\Models\Tenant\Customer;
use Illuminate\Support\Facades\Gate;

/**
 * BR-017 — MANAGER MULTI BRANCH.
 *
 * A manager may oversee SEVERAL selected branches (primary home branch +
 * explicitly assigned additional branches via user_branches pivot), WITHOUT
 * being granted tenant-global access. Branches outside the assignment remain
 * inaccessible. Permissions still gate actions even inside an accessible branch.
 */
class BR17MultiBranchManagerTest extends BRBranchOperationsTestCase
{
    public function test_manager_can_access_primary_branch()
    {
        $serviceA = $this->makeService($this->branchA);

        $this->assertTrue(
            Gate::forUser($this->manager)->allows('view', $serviceA),
            'Manager must access their primary branch A.'
        );
        $this->assertTrue(
            \App\Services\BranchAccessService::canAccess($this->manager, $this->branchA->id)
        );
    }

    public function test_manager_can_access_assigned_branch()
    {
        $serviceB = $this->makeService($this->branchB);

        $this->assertTrue(
            Gate::forUser($this->manager)->allows('view', $serviceB),
            'Manager must access explicitly assigned branch B.'
        );
    }

    public function test_manager_cannot_access_unassigned_branch()
    {
        $serviceC = $this->makeService($this->branchC);

        $this->assertFalse(
            Gate::forUser($this->manager)->allows('view', $serviceC),
            'Manager must NOT access unassigned branch C.'
        );
        $this->assertFalse(
            \App\Services\BranchAccessService::canAccess($this->manager, $this->branchC->id)
        );
    }

    public function test_removing_branch_assignment_revokes_access()
    {
        $serviceB = $this->makeService($this->branchB);
        $this->assertTrue(Gate::forUser($this->manager)->allows('view', $serviceB));

        // Owner removes the additional B assignment through the user-management API.
        $this->actingAs($this->owner);
        $this->put("/users/{$this->manager->id}", [
            'name' => $this->manager->name,
            'email' => $this->manager->email,
            'role' => 'manager',
            'branch_id' => $this->branchA->id,
            'additional_branches' => [],
        ])->assertSessionHas('success');

        $manager = $this->manager->fresh();
        $this->assertFalse(
            Gate::forUser($manager)->allows('view', $serviceB),
            'Removing the assignment must revoke access immediately (cache invalidated).'
        );
        $this->assertEmpty($manager->branches()->pluck('branches.id'), 'Pivot must be empty.');
    }

    public function test_manager_cannot_access_branch_in_another_tenant()
    {
        $tenantA = $this->setUpTenant();
        $this->grantFullPlanAccess();
        $branchA = \App\Models\Tenant\Branch::create(['name' => 'Branch A']);
        $managerA = $this->createTenantUser(['role' => 'manager', 'branch_id' => $branchA->id]);

        // Tenant B — separate database (1 DB per tenant).
        $this->setUpTenant();
        $this->grantFullPlanAccess();
        $branchB = \App\Models\Tenant\Branch::create(['name' => 'Branch B']);

        // Back in Tenant A, acting as manager A.
        tenancy()->initialize($tenantA);
        $this->actingAs($managerA);

        // Tenant B's branch does not even exist in Tenant A's database
        // (1 DB per tenant → absolute isolation).
        $this->assertFalse(\App\Models\Tenant\Branch::where('name', 'Branch B')->exists());
        $this->assertCount(1, \App\Services\BranchAccessService::accessibleBranchIds($managerA), 'Manager A sees only its own tenant branches.');
    }

    public function test_permissions_still_limit_actions_even_when_branch_accessible()
    {
        $serviceA = $this->makeService($this->branchA);

        // Branch A is accessible, but deleting a service requires
        // service.delete / owner / admin — a manager lacks it.
        $this->assertTrue(Gate::forUser($this->manager)->allows('update', $serviceA));
        $this->assertFalse(
            Gate::forUser($this->manager)->allows('delete', $serviceA),
            'Branch access must NOT bypass the delete permission.'
        );
    }

    public function test_customer_lookup_follows_manager_branch_scope()
    {
        $customerA = $this->makeCustomer($this->branchA);
        $customerB = $this->makeCustomer($this->branchB);
        $customerC = $this->makeCustomer($this->branchC);

        $this->assertTrue(Gate::forUser($this->manager)->allows('view', $customerA));
        $this->assertTrue(Gate::forUser($this->manager)->allows('view', $customerB));
        $this->assertFalse(Gate::forUser($this->manager)->allows('view', $customerC));
    }
}
