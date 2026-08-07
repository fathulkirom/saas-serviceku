<?php

namespace Tests\Feature\BusinessReality;

use App\Models\Tenant\Delegation;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Service;
use Illuminate\Support\Facades\Queue;

/**
 * BR-001 — CS TEMPORARY REPLACEMENT (via delegation).
 *
 * An authorized employee (e.g. a technician) can temporarily handle CS duties
 * through the EXISTING Role + Permission + Branch Scope + Delegation
 * architecture — WITHOUT changing their role. STEP 17 test list (13 tests).
 */
class BR01TemporaryCsReplacementTest extends BRDelegationTestCase
{
    // 1. Technician without delegation cannot create CS intake.
    public function test_technician_without_delegation_cannot_create_cs_intake(): void
    {
        $this->actingAs($this->techA);

        $response = $this->post(route('services.store'), $this->intakePayload($this->branchA));

        $response->assertStatus(403);
        $this->assertDatabaseCount('services', 0);
    }

    // 2. Technician remains role=technician after delegation.
    public function test_technician_remains_technician_after_delegation(): void
    {
        $this->grant($this->owner, $this->techA, 'service.create', $this->branchA->id);

        $this->assertEquals('technician', $this->techA->fresh()->role);
        $this->assertFalse($this->techA->isCs());
        $this->assertTrue($this->techA->hasActiveDelegation('service.create', $this->branchA->id));
    }

    // 3. Delegated service.intake permits intake.
    public function test_delegated_service_intake_permits_intake(): void
    {
        $this->grant($this->owner, $this->techA, 'service.create', $this->branchA->id);

        $this->actingAs($this->techA);
        $response = $this->post(route('services.store'), $this->intakePayload($this->branchA));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseCount('services', 1);
    }

    // 4. Delegation applies only to allowed branch.
    public function test_delegation_applies_only_to_allowed_branch(): void
    {
        // Intake is always stamped to the actor's PRIMARY branch (techA → A),
        // so a delegation must cover that branch to be usable for intake.

        // Delegated service.create at Branch B ONLY — does NOT cover primary A.
        $this->grant($this->owner, $this->techA, 'service.create', $this->branchB->id);

        $this->actingAs($this->techA);
        $response = $this->post(route('services.store'), $this->intakePayload($this->branchA));
        $response->assertStatus(403);
        $this->assertDatabaseCount('services', 0);

        // Now delegate at the technician's primary branch (A) → intake works.
        $this->grant($this->owner, $this->techA, 'service.create', $this->branchA->id);

        $response = $this->post(route('services.store'), $this->intakePayload($this->branchA));
        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseCount('services', 1);
    }

    // 5. Delegation does not grant payment automatically.
    public function test_delegation_does_not_grant_payment_automatically(): void
    {
        $service = $this->makeReadyService($this->branchA);
        $sale = $this->makeDraftSale($service);

        // Grant ONLY service.create (intake), NOT sales.create.
        $this->grant($this->owner, $this->techA, 'service.create', $this->branchA->id);

        $this->actingAs($this->techA);
        $response = $this->post(route('sales.pay-draft', ['sale' => $sale->id]), [
            'payment_method' => 'cash',
            'paid_amount' => (float) $sale->total,
        ]);

        $response->assertStatus(403);
        $this->assertEquals(Sale::STATUS_DRAFT, $sale->fresh()->status);
    }

    // 6. Separate payment permission can grant payment if policy supports it.
    public function test_separate_payment_permission_can_grant_payment(): void
    {
        Queue::fake();

        $service = $this->makeReadyService($this->branchA);
        $sale = $this->makeDraftSale($service);

        $this->grant($this->owner, $this->techA, 'sales.create', $this->branchA->id);

        $this->actingAs($this->techA);
        $response = $this->post(route('sales.pay-draft', ['sale' => $sale->id]), [
            'payment_method' => 'cash',
            'paid_amount' => (float) $sale->total,
        ]);

        $response->assertStatus(302);
        $this->assertEquals(Sale::STATUS_PAID, $sale->fresh()->status);
    }

    // 7. Expired delegation is denied.
    public function test_expired_delegation_is_denied(): void
    {
        $this->grant(
            $this->owner,
            $this->techA,
            'service.create',
            $this->branchA->id,
            now()->subDay()->toDateTimeString()
        );

        $this->assertFalse($this->techA->hasActiveDelegation('service.create', $this->branchA->id));

        $this->actingAs($this->techA);
        $response = $this->post(route('services.store'), $this->intakePayload($this->branchA));
        $response->assertStatus(403);
        $this->assertDatabaseCount('services', 0);
    }

    // 8. Revoked delegation is denied.
    public function test_revoked_delegation_is_denied(): void
    {
        $delegation = $this->grant($this->owner, $this->techA, 'service.create', $this->branchA->id);

        $this->assertTrue($this->techA->hasActiveDelegation('service.create', $this->branchA->id));

        $this->actingAs($this->owner);
        $this->post(route('delegations.revoke', ['delegation' => $delegation->id]));
        $delegation->refresh();

        $this->assertNotNull($delegation->revoked_at);
        $this->assertFalse($this->techA->hasActiveDelegation('service.create', $this->branchA->id));

        $this->actingAs($this->techA);
        $response = $this->post(route('services.store'), $this->intakePayload($this->branchA));
        $response->assertStatus(403);
        $this->assertDatabaseCount('services', 0);
    }

    // 9. Unauthorized user cannot create delegation.
    public function test_unauthorized_user_cannot_create_delegation(): void
    {
        $this->actingAs($this->csA);

        $response = $this->post(route('delegations.store'), [
            'user_id' => $this->techA->id,
            'permission' => 'service.create',
            'branch_id' => $this->branchA->id,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('delegations', 0);
    }

    // 10. Delegated user cannot delegate privileges onward unless explicitly allowed.
    public function test_delegated_user_cannot_delegate_onward(): void
    {
        $this->grant($this->owner, $this->techA, 'service.create', $this->branchA->id);

        // Even though techA now has an active delegation, they lack delegation.grant.
        $this->actingAs($this->techA);
        $response = $this->post(route('delegations.store'), [
            'user_id' => $this->csA->id,
            'permission' => 'service.pickup',
            'branch_id' => $this->branchA->id,
        ]);

        $response->assertStatus(403);
        $this->assertDatabaseCount('delegations', 1); // only the original grant
    }

    // 11. Audit records grant.
    public function test_audit_records_grant(): void
    {
        $this->actingAs($this->owner);

        $this->post(route('delegations.store'), [
            'user_id' => $this->techA->id,
            'permission' => 'service.create',
            'branch_id' => $this->branchA->id,
            'reason' => 'CS cuti satu hari',
        ]);

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'delegation_granted',
            'subject_type' => \App\Models\Tenant\User::class,
            'subject_id' => $this->techA->id,
            'user_id' => $this->owner->id,
        ]);
    }

    // 12. Audit records revoke.
    public function test_audit_records_revoke(): void
    {
        $this->actingAs($this->owner);

        $this->post(route('delegations.store'), [
            'user_id' => $this->techA->id,
            'permission' => 'service.create',
            'branch_id' => $this->branchA->id,
        ]);

        $delegation = Delegation::first();

        $this->post(route('delegations.revoke', ['delegation' => $delegation->id]));

        $this->assertDatabaseHas('activity_logs', [
            'action' => 'delegation_revoked',
            'subject_id' => $this->techA->id,
        ]);
    }

    // 13. Service created under delegation records actual substitute actor.
    public function test_service_created_under_delegation_records_actual_substitute_actor(): void
    {
        $this->grant($this->owner, $this->techA, 'service.create', $this->branchA->id);

        $this->actingAs($this->techA);
        $this->post(route('services.store'), $this->intakePayload($this->branchA));

        $service = Service::first();
        $this->assertNotNull($service);
        $this->assertEquals($this->techA->id, $service->created_by);
        $this->assertNotEquals($this->csA->id, $service->created_by);
    }
}

