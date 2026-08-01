<?php

namespace Tests\Feature;

use App\Models\Tenant\Service;
use Tests\TestCase;

class TenantServiceCompleteGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_owner_cannot_complete_service_costs_before_service_status_is_selesai(): void
    {
        $branch = $this->createBranch(['name' => 'Cabang A']);

        $owner = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branch->id,
        ]);

        $customer = $this->createCustomer([
            'branch_id' => $branch->id,
        ]);

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_DIKERJAKAN,
            'service_charge' => 10000,
            'total_cost' => 10000,
        ]);

        $this->actingAs($owner);

        $response = $this->post(route('services.complete', $service), [
            'service_charge' => 25000,
        ]);

        $response->assertSessionHas('error');

        $service->refresh();
        $this->assertSame(Service::STATUS_DIKERJAKAN, $service->status);
        $this->assertSame('10000.00', (string) $service->service_charge);
        $this->assertSame('10000.00', (string) $service->total_cost);
    }

    public function test_owner_can_complete_service_costs_when_status_is_selesai(): void
    {
        $branch = $this->createBranch(['name' => 'Cabang A']);

        $owner = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branch->id,
        ]);

        $customer = $this->createCustomer([
            'branch_id' => $branch->id,
        ]);

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_SELESAI,
            'service_charge' => 0,
            'total_cost' => 0,
        ]);

        $this->actingAs($owner);

        $response = $this->post(route('services.complete', $service), [
            'service_charge' => 25000,
        ]);

        $response->assertSessionHas('success');

        $service->refresh();
        $this->assertSame(Service::STATUS_SELESAI, $service->status);
        $this->assertSame('25000.00', (string) $service->service_charge);
        $this->assertSame('25000.00', (string) $service->total_cost);
    }
}
