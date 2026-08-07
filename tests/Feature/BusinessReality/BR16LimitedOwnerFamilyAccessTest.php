<?php

namespace Tests\Feature\BusinessReality;

use App\Models\Tenant\Sale;
use App\Models\Tenant\Service;
use Illuminate\Support\Facades\Queue;

/**
 * BR-016 — OWNER FAMILY / RESTRICTED OPERATIONAL ACCESS.
 *
 * A restricted operational user (owner's family member) is modeled using an
 * EXISTING operational role (CS) + explicit permissions — NOT a custom
 * role, and definitely not the owner role. They can perform permitted CS
 * work (intake, cashier when granted sales.create) but CANNOT read finance
 * (P&L), manage users, modify subscription, or leave their branch scope.
 * STEP 18 test list (10 tests).
 */
class BR16LimitedOwnerFamilyAccessTest extends BRDelegationTestCase
{
    // 14. Restricted operational user can perform permitted CS work.
    public function test_restricted_operational_user_can_perform_permitted_cs_work(): void
    {
        $this->actingAs($this->csA);

        $response = $this->post(route('services.store'), $this->intakePayload($this->branchA));

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();
        $this->assertDatabaseCount('services', 1);
    }

    // 15. Can cashier only if explicitly permitted.
    public function test_can_cashier_only_if_explicitly_permitted(): void
    {
        Queue::fake();

        $service = $this->makeReadyService($this->branchA);
        $sale = $this->makeDraftSale($service);

        // A user without sales.create cannot record payment (not permitted).
        $this->actingAs($this->techA);
        $this->post(route('sales.pay-draft', ['sale' => $sale->id]), [
            'payment_method' => 'cash',
            'paid_amount' => (float) $sale->total,
        ])->assertStatus(403);

        // The CS role explicitly carries sales.create → the restricted
        // operational user CAN cashier.
        $this->actingAs($this->csA);
        $this->post(route('sales.pay-draft', ['sale' => $sale->id]), [
            'payment_method' => 'cash',
            'paid_amount' => (float) $sale->total,
        ])->assertStatus(302);

        $this->assertEquals(Sale::STATUS_PAID, $sale->fresh()->status);
    }

    // 16. Cannot see monthly P&L.
    public function test_restricted_user_cannot_see_monthly_pnl(): void
    {
        $this->actingAs($this->csA);
        $this->get(route('reports.finance'))->assertStatus(403);
    }

    // 17. Cannot call P&L endpoint directly.
    public function test_restricted_user_cannot_call_pnl_endpoint_directly(): void
    {
        $service = $this->makeReadyService($this->branchA);

        $this->actingAs($this->csA);
        $this->get(route('services.profit', ['service' => $service->id]))->assertStatus(403);
    }

    // 18. Cannot see owner-sensitive dashboard payload.
    public function test_restricted_user_cannot_see_owner_sensitive_dashboard_payload(): void
    {
        $this->actingAs($this->csA);

        $this->get(route('dashboard.owner'))->assertStatus(403);
        $this->get(route('dashboard.owner-kpi'))->assertStatus(403);
    }

    // 19. Cannot manage users.
    public function test_restricted_user_cannot_manage_users(): void
    {
        $this->actingAs($this->csA);

        $this->post(route('users.store'), [
            'name' => 'User Baru',
            'email' => 'baru@test.com',
            'password' => 'secret123',
            'role' => 'cs',
            'branch_id' => $this->branchA->id,
        ])->assertStatus(403);

        $this->assertDatabaseCount('users', 4); // owner, manager, csA, techA only
    }

    // 20. Cannot modify subscription.
    public function test_restricted_user_cannot_modify_subscription(): void
    {
        // The restricted operational user has no subscription capability at all.
        $this->assertFalse($this->csA->canViaPermission('subscription.manage'));
        $this->assertFalse($this->csA->canViaPermission('subscription.view'));

        // And they cannot grant themselves one (no delegation.grant).
        $this->actingAs($this->csA);
        $this->post(route('delegations.store'), [
            'user_id' => $this->csA->id,
            'permission' => 'subscription.manage',
        ])->assertStatus(403);
    }

    // 21. Cannot access unauthorized branch.
    public function test_restricted_user_cannot_access_unauthorized_branch(): void
    {
        // csA primary branch is A; Branch B is outside their scope.
        $this->actingAs($this->csA);

        $response = $this->post(route('services.store'), $this->intakePayload($this->branchB));
        $response->assertStatus(403);
        $this->assertDatabaseCount('services', 0);
    }

    // 22. Owner retains full access.
    public function test_owner_retains_full_access(): void
    {
        // Owner can see the P&L report.
        $this->actingAs($this->owner);
        $this->get(route('reports.finance'))->assertOk();

        // Owner can perform CS work.
        $this->post(route('services.store'), $this->intakePayload($this->branchA))
            ->assertSessionHasNoErrors();

        // Owner can grant delegations.
        $this->post(route('delegations.store'), [
            'user_id' => $this->techA->id,
            'permission' => 'service.pickup',
            'branch_id' => $this->branchA->id,
        ])->assertStatus(302);

        $this->assertDatabaseCount('delegations', 1);
    }

    // 23. Granting operational permission does not grant finance permission.
    public function test_granting_operational_permission_does_not_grant_finance(): void
    {
        // Grant only an OPERATIONAL capability (service.create / intake).
        $this->grant($this->owner, $this->techA, 'service.create', $this->branchA->id);

        // The grantee can now intake...
        $this->assertTrue($this->techA->canViaPermissionInBranch('service.create', $this->branchA->id));

        // ...but still has NO finance capability.
        $this->assertFalse($this->techA->canViaPermission('finance.view'));

        $this->actingAs($this->techA);
        $this->get(route('reports.finance'))->assertStatus(403);
        $this->get(route('dashboard.owner-kpi'))->assertStatus(403);
    }
}

