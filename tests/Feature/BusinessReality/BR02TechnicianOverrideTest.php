<?php

namespace Tests\Feature\BusinessReality;

use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Device;
use App\Models\Tenant\Service;
use App\Models\Tenant\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BR-002 — TECHNICIAN FORGOT TO FINISH (manager/admin/owner override).
 *
 * An authorized manager/admin/owner can safely complete a forgotten
 * technician repair WITHOUT impersonating the technician: the original
 * technician_id stays assigned, the actual overriding actor is audited, the
 * reason (repair_notes) is recorded, no duplicate side effect, and QC remains
 * mandatory.
 *
 * Override path under test: TechnicianWorkflowController@completeRepair
 * (POST services.repair.complete) — explicitly allows owner/admin/manager.
 */
class BR02TechnicianOverrideTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branchA;
    protected Branch $branchB;

    protected User $owner;
    protected User $manager;   // branch A (+ access B)
    protected User $managerB;  // branch B, NO access A
    protected User $techA;
    protected User $techB;

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

        $this->managerB = $this->createTenantUser(['name' => 'Manager B', 'role' => 'manager', 'branch_id' => $this->branchB->id, 'active' => true]);
        $this->techA = $this->createTenantUser(['name' => 'Tech A', 'role' => 'technician', 'branch_id' => $this->branchA->id, 'active' => true]);
        $this->techB = $this->createTenantUser(['name' => 'Tech B', 'role' => 'technician', 'branch_id' => $this->branchA->id, 'active' => true]);
    }

    /** A service currently in Dikerjakan, assigned to Tech A. */
    private function makeServiceInWork(Branch $branch): Service
    {
        $customer = Customer::create(['name' => 'Customer', 'phone' => '08123456789', 'branch_id' => $branch->id]);
        $device = Device::create(['customer_id' => $customer->id, 'brand' => 'Samsung', 'model' => 'S23', 'status' => 'active']);

        return Service::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'device_id' => $device->id,
            'created_by' => $this->owner->id,
            'technician_id' => $this->techA->id,
            'status' => Service::STATUS_DIKERJAKAN,
            'service_charge' => 100000,
            'total_cost' => 100000,
            'problem_description' => 'Perbaikan',
        ]);
    }

    // 1. Assigned technician can finish own work.
    public function test_assigned_technician_can_finish_own_work(): void
    {
        $service = $this->makeServiceInWork($this->branchA);

        $this->actingAs($this->techA);
        $this->post(route('services.repair.complete', $service), ['repair_notes' => 'Selesai'])
            ->assertSessionHasNoErrors();

        $this->assertSame(Service::STATUS_SELESAI, $service->fresh()->status);
    }

    // 2. Different normal technician cannot finish it.
    public function test_different_normal_technician_cannot_finish_it(): void
    {
        $service = $this->makeServiceInWork($this->branchA);

        $this->actingAs($this->techB);
        $this->post(route('services.repair.complete', $service), ['repair_notes' => 'Coba override'])
            ->assertStatus(403);

        $this->assertSame(Service::STATUS_DIKERJAKAN, $service->fresh()->status);
    }

    // 3. Authorized manager can finish/override.
    public function test_authorized_manager_can_finish_override(): void
    {
        $service = $this->makeServiceInWork($this->branchA);

        $this->actingAs($this->manager);
        $this->post(route('services.repair.complete', $service), ['repair_notes' => 'Teknisi lupa finish — manager override'])
            ->assertSessionHasNoErrors();

        $this->assertSame(Service::STATUS_SELESAI, $service->fresh()->status);
    }

    // 4. Original technician_id remains unchanged.
    public function test_original_technician_id_remains_unchanged(): void
    {
        $service = $this->makeServiceInWork($this->branchA);

        $this->actingAs($this->manager);
        $this->post(route('services.repair.complete', $service), ['repair_notes' => 'override']);

        $this->assertSame($this->techA->id, $service->fresh()->technician_id);
    }

    // 5. Actual manager actor appears in audit.
    public function test_actual_manager_actor_appears_in_audit(): void
    {
        $service = $this->makeServiceInWork($this->branchA);

        $this->actingAs($this->manager);
        $this->post(route('services.repair.complete', $service), ['repair_notes' => 'override oleh manager']);

        // The overriding actor is recorded (activity user_id = manager, name in description).
        $log = ActivityLog::where('action', 'repair_completed')
            ->where('subject_id', $service->id)
            ->latest('id')->first();
        $this->assertNotNull($log);
        $this->assertSame($this->manager->id, $log->user_id);
        $this->assertStringContainsString($this->manager->name, $log->description ?? '');

        // Reason (repair_notes) recorded with the actual actor.
        $note = ActivityLog::where('action', 'repair_note')->where('subject_id', $service->id)->latest('id')->first();
        $this->assertNotNull($note);
        $this->assertSame($this->manager->id, $note->user_id);
        $this->assertStringContainsString('override oleh manager', $note->description ?? '');
    }

    // 6. Override does not mark manager as repair technician.
    public function test_override_does_not_mark_manager_as_repair_technician(): void
    {
        $service = $this->makeServiceInWork($this->branchA);

        $this->actingAs($this->manager);
        $this->post(route('services.repair.complete', $service), ['repair_notes' => 'override']);

        $this->assertSame($this->techA->id, $service->fresh()->technician_id);
        $this->assertNotSame($this->manager->id, $service->fresh()->technician_id);
    }

    // 7. QC is still required after manager completion.
    public function test_qc_still_required_after_manager_completion(): void
    {
        $service = $this->makeServiceInWork($this->branchA);

        $this->actingAs($this->manager);
        $this->post(route('services.repair.complete', $service), ['repair_notes' => 'override']);

        $this->assertSame(Service::STATUS_SELESAI, $service->fresh()->status);
        $this->assertSame(0, \App\Models\Tenant\ServiceQcCheck::where('service_id', $service->id)->count());

        // Close is blocked without QC (canonical precondition).
        $this->actingAs($this->owner);
        $this->post(route('services.close', $service))->assertSessionHas('error');
    }

    // 8. Repeated completion does not duplicate side effects.
    public function test_repeated_completion_does_not_duplicate_side_effects(): void
    {
        $service = $this->makeServiceInWork($this->branchA);

        $this->actingAs($this->manager);
        $this->post(route('services.repair.complete', $service), ['repair_notes' => 'pertama']);
        $this->assertSame(Service::STATUS_SELESAI, $service->fresh()->status);

        // Second completion → 409 (status no longer dikerjakan), no duplicate.
        $this->post(route('services.repair.complete', $service), ['repair_notes' => 'kedua'])
            ->assertStatus(409);

        $this->assertSame(Service::STATUS_SELESAI, $service->fresh()->status);
        $this->assertSame(1, ActivityLog::where('action', 'repair_completed')->where('subject_id', $service->id)->count());
        $this->assertSame(0, \App\Models\Tenant\Commission::where('service_id', $service->id)->count(), 'Repair completion alone must NOT create commission');
    }

    // 9. Unauthorized branch manager cannot override.
    public function test_unauthorized_branch_manager_cannot_override(): void
    {
        $service = $this->makeServiceInWork($this->branchA); // branch A

        // managerB has NO access to branch A.
        $this->actingAs($this->managerB);
        $this->post(route('services.repair.complete', $service), ['repair_notes' => 'override'])
            ->assertStatus(403);

        $this->assertSame(Service::STATUS_DIKERJAKAN, $service->fresh()->status);
    }

    // 10. Tenant isolation applies (service not visible/finishable from another tenant).
    public function test_tenant_isolation_applies(): void
    {
        $tenantA = $this->setUpTenant();
        $this->grantFullPlanAccess();
        $branchA = Branch::create(['name' => 'A']);
        $techA = $this->createTenantUser(['role' => 'technician', 'branch_id' => $branchA->id]);
        $serviceA = $this->makeServiceInWork($branchA);

        // Tenant B — separate DB.
        $this->setUpTenant();
        $this->grantFullPlanAccess();

        // Tenant A's service does not exist in Tenant B.
        $this->assertFalse(Service::whereKey($serviceA->id)->exists());

        // Back in Tenant A, service still there and intact.
        tenancy()->initialize($tenantA);
        $this->assertTrue(Service::whereKey($serviceA->id)->exists());
    }
}

