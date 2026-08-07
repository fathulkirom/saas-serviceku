<?php

namespace Tests\Feature\BusinessReality;

use Tests\TestCase;
use App\Models\Tenant\Delegation;
use App\Models\Tenant\User;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Service;
use App\Models\Tenant\Sale;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Shared setup for BR-001 (CS Temporary Replacement via delegation) and
 * BR-016 (Owner Family / Restricted Operational Access) tests.
 *
 * Fixtures:
 *   - Branch A (owner, manager, CS, technician primary)
 *   - Branch B (manager additional; CS operates here too)
 *   - owner: full access (finance.view, delegation.grant/revoke, …)
 *   - manager: delegation.grant/revoke + branches A & B
 *   - csA: operational role (service.create, service.pickup, sales.create)
 *   - techA: technician (NO service.create / sales.create by default)
 */
abstract class BRDelegationTestCase extends TestCase
{
    use RefreshDatabase;

    protected Branch $branchA;
    protected Branch $branchB;

    protected User $owner;
    protected User $manager;
    protected User $csA;
    protected User $techA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->grantFullPlanAccess();

        $this->branchA = Branch::create(['name' => 'Cabang A', 'is_active' => true]);
        $this->branchB = Branch::create(['name' => 'Cabang B', 'is_active' => true]);

        $this->owner = $this->createTenantUser(['name' => 'Owner', 'role' => 'owner', 'branch_id' => $this->branchA->id, 'active' => true]);
        $this->manager = $this->createTenantUser(['name' => 'Manager', 'role' => 'manager', 'branch_id' => $this->branchA->id, 'active' => true]);
        $this->manager->branches()->sync([$this->branchB->id]);
        $this->manager->clearBranchAccessCache();

        $this->csA = $this->createTenantUser(['name' => 'CS A', 'role' => 'cs', 'branch_id' => $this->branchA->id, 'active' => true]);
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

    /** Payload for POST /services (CS intake) for a customer in $branch. */
    protected function intakePayload(Branch $branch): array
    {
        $customer = $this->makeCustomer($branch);

        return [
            'customer_id' => $customer->id,
            'problem_description' => 'Perbaikan di ' . $branch->name,
            'tipe_unit' => 'Unit Test',
        ];
    }

    /** Directly persist a delegation (granted by $granter). */
    protected function grant(User $granter, User $grantee, string $permission, ?int $branchId = null, ?string $expiresAt = null, ?string $startsAt = null): Delegation
    {
        $grantee->clearPermissionCache();

        return Delegation::create([
            'user_id' => $grantee->id,
            'permission' => $permission,
            'branch_id' => $branchId,
            'granted_by' => $granter->id,
            'starts_at' => $startsAt,
            'expires_at' => $expiresAt,
            'reason' => 'BR-FIX-03 test',
        ]);
    }

    protected function makeReadyService(Branch $branch): Service
    {
        $customer = $this->makeCustomer($branch);

        return Service::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $this->owner->id,
            'technician_id' => $this->techA->id,
            'status' => Service::STATUS_SIAP_DIAMBIL,
            'service_charge' => 100000,
            'total_cost' => 100000,
            'problem_description' => 'Siap diambil',
        ]);
    }

    protected function makeDraftSale(Service $service): Sale
    {
        return Sale::create([
            'branch_id' => $service->branch_id,
            'customer_id' => $service->customer_id,
            'sale_type' => Sale::SALE_TYPE_SERVIS,
            'status' => Sale::STATUS_DRAFT,
            'service_id' => $service->id,
            'subtotal' => (float) $service->total_cost,
            'discount' => 0,
            'total' => (float) $service->total_cost,
            'payment_method' => 'draft',
            'paid_amount' => 0,
            'change' => 0,
        ]);
    }
}
