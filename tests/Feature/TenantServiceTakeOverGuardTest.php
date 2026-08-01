<?php

namespace Tests\Feature;

use App\Models\Tenant\Service;
use Tests\TestCase;

class TenantServiceTakeOverGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_cs_cannot_take_over_service_even_in_same_branch(): void
    {
        $branch = $this->createBranch(['name' => 'Cabang A']);

        $owner = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branch->id,
        ]);

        $cs = $this->createTenantUser([
            'role' => 'cs',
            'branch_id' => $branch->id,
        ]);

        $customer = $this->createCustomer([
            'branch_id' => $branch->id,
        ]);

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
            'technician_id' => null,
        ]);

        $this->actingAs($cs);

        $response = $this->post(route('services.take-over', $service));

        $response->assertSessionHasErrors(['technician_id']);

        $service->refresh();
        $this->assertSame(Service::STATUS_MENUNGGU_ALOKASI, $service->status);
        $this->assertNull($service->technician_id);
    }

    public function test_owner_can_take_over_service_and_become_assigned_technician(): void
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
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
            'technician_id' => null,
        ]);

        $this->actingAs($owner);

        $response = $this->post(route('services.take-over', $service));

        $response->assertSessionHas('success');

        $service->refresh();
        $this->assertSame(Service::STATUS_DIKERJAKAN, $service->status);
        $this->assertSame($owner->id, $service->technician_id);
    }
}
