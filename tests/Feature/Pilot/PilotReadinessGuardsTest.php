<?php

namespace Tests\Feature\Pilot;

use Tests\TestCase;
use App\Models\Tenant\Service;
use App\Models\Tenant\Sale;

/**
 * PILOT-READY-01 — Regression guard for the P0/P1 fixes in this phase:
 *   - P0 security guards (/keuangan, /pengaturan, /sistem)
 *   - P1 cashier dashboard prop contract (readyServices / cashRegisterOpen)
 *   - global search endpoint returns 200 (no unknown-column 500)
 */
class PilotReadinessGuardsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->grantFullPlanAccess();
    }

    // ── P0: /keuangan must reject roles without finance authority ──
    public function test_keuangan_rejects_technician_but_allows_owner(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $tech = $this->createTenantUser(['role' => 'technician', 'branch_id' => $branch->id, 'email' => 'tech@test.com']);

        $this->actingAs($tech);
        $this->get(route('keuangan.index'))->assertForbidden();

        $this->actingAs($owner);
        $this->get(route('keuangan.index'))->assertOk();
    }

    // ── P0: /pengaturan (revenue/users/branches) must be owner/admin only ──
    public function test_pengaturan_rejects_technician_but_allows_owner(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $tech = $this->createTenantUser(['role' => 'technician', 'branch_id' => $branch->id, 'email' => 'tech@test.com']);

        $this->actingAs($tech);
        $this->get(route('pengaturan.index'))->assertForbidden();

        $this->actingAs($owner);
        $this->get(route('pengaturan.index'))->assertOk();
    }

    // ── P0: /sistem (all users + role assignment) must be owner/admin only ──
    public function test_sistem_rejects_technician_but_allows_owner(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $tech = $this->createTenantUser(['role' => 'technician', 'branch_id' => $branch->id, 'email' => 'tech@test.com']);

        $this->actingAs($tech);
        $this->get(route('sistem.index'))->assertForbidden();

        $this->actingAs($owner);
        $this->get(route('sistem.index'))->assertOk();
    }

    // ── P1: CashierDashboard must receive readyServices + cashRegisterOpen ──
    public function test_cashier_dashboard_passes_ready_services_props(): void
    {
        $branch = $this->createBranch();
        $cashier = $this->createTenantUser(['role' => 'cashier', 'branch_id' => $branch->id]);

        // A service ready for pickup so the list is non-empty.
        $customer = $this->createCustomer(['branch_id' => $branch->id]);
        $service = $this->createService([
            'branch_id' => $branch->id, 'customer_id' => $customer->id,
            'created_by' => $cashier->id, 'status' => Service::STATUS_SIAP_DIAMBIL,
        ]);

        $this->actingAs($cashier);
        $response = $this->get(route('dashboard'));
        $response->assertOk();

        $props = $response->viewData('page')['props'] ?? [];
        $this->assertArrayHasKey('readyServices', $props, 'CashierDashboard must receive readyServices.');
        $this->assertArrayHasKey('cashRegisterOpen', $props, 'CashierDashboard must receive cashRegisterOpen.');
        $rows = collect($props['readyServices'] ?? []);
        $this->assertTrue($rows->contains('id', $service->id), 'Ready service must appear in the cashier list.');
    }

    // ── Global search (active SearchController) returns 200, no 500 ──
    public function test_global_search_returns_200_with_results(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer(['branch_id' => $branch->id, 'name' => 'Budi Santoso']);
        $this->createService([
            'branch_id' => $branch->id, 'customer_id' => $customer->id,
            'created_by' => $owner->id, 'tipe_unit' => 'iPhone 13',
        ]);

        $this->actingAs($owner);
        $response = $this->getJson(route('search') . '?q=budi');
        $response->assertOk();
        $results = $response->json('results');
        $this->assertIsArray($results);
        $types = array_column($results, 'type');
        $this->assertContains('customer', $types);
    }

    // ── BR-020: reopen is owner/admin/manager only + safely unlocks ──
    public function test_reopen_requires_authority_and_unlocks_service(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $tech = $this->createTenantUser(['role' => 'technician', 'branch_id' => $branch->id, 'email' => 'tech@test.com']);
        $customer = $this->createCustomer(['branch_id' => $branch->id]);
        $service = $this->createService([
            'branch_id' => $branch->id, 'customer_id' => $customer->id,
            'created_by' => $owner->id, 'status' => Service::STATUS_CLOSE,
        ]);
        $service->lock($owner->id);
        $this->assertTrue($service->fresh()->isLocked());

        // Technician cannot approve a reopen.
        $this->actingAs($tech);
        $this->post(route('services.reopen', $service), ['reason' => 'salah tutup'])
            ->assertForbidden();

        // Owner requests + approves reopen → service unlocked, status intact.
        $this->actingAs($owner);
        $this->post(route('services.reopen', $service), ['reason' => 'salah tutup'])
            ->assertSessionHas('success');
        $reopen = \App\Models\Tenant\ServiceReopen::where('service_id', $service->id)->first();
        $this->assertNotNull($reopen);

        $this->post(route('service-reopens.approve', $reopen))
            ->assertSessionHas('success');
        $service->refresh();
        $this->assertFalse($service->isLocked(), 'Approve must unlock the service.');
        $this->assertSame(Service::STATUS_CLOSE, $service->status, 'Reopen must not mutate status/financial state.');
        $this->assertSame('approved', $reopen->fresh()->status);
    }
}
