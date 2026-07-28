<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant\Service;
use App\Models\Tenant\User;

class TenantServiceWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_can_create_service()
    {
        $user = $this->createTenantUser(['role' => 'cs']);

        $service = $this->createService(['created_by' => $user->id]);

        $this->assertNotNull($service);
        $this->assertEquals(Service::STATUS_MENUNGGU_ALOKASI, $service->status);
        $this->assertEquals('Test problem description', $service->problem_description);
    }

    public function test_service_can_be_accepted_by_technician()
    {
        $owner = $this->createTenantUser(['role' => 'owner']);
        $technician = $this->createTenantUser([
            'name' => 'Teknisi',
            'email' => 'teknik@test.com',
            'role' => 'technician',
        ]);

        $service = $this->createService(['created_by' => $owner->id]);

        $service->update([
            'technician_id' => $technician->id,
            'status' => Service::STATUS_DITERIMA,
        ]);

        $this->assertEquals(Service::STATUS_DITERIMA, $service->fresh()->status);
        $this->assertEquals($technician->id, $service->fresh()->technician_id);
    }

    public function test_full_service_workflow()
    {
        $owner = $this->createTenantUser(['role' => 'owner']);
        $technician = $this->createTenantUser([
            'name' => 'Teknisi',
            'email' => 'teknik2@test.com',
            'role' => 'technician',
        ]);

        $service = $this->createService(['created_by' => $owner->id]);

        // 1. Assign technician
        $service->update(['technician_id' => $technician->id, 'status' => Service::STATUS_DITERIMA]);
        $this->assertEquals(Service::STATUS_DITERIMA, $service->fresh()->status);

        // 2. Start working
        $service->update(['status' => Service::STATUS_DIKERJAKAN]);
        $this->assertEquals(Service::STATUS_DIKERJAKAN, $service->fresh()->status);

        // 3. Complete service
        $service->update(['status' => Service::STATUS_SELESAI]);
        $this->assertEquals(Service::STATUS_SELESAI, $service->fresh()->status);
    }

    public function test_service_can_be_cancelled()
    {
        $owner = $this->createTenantUser(['role' => 'owner']);
        $service = $this->createService(['created_by' => $owner->id]);

        $service->update(['status' => Service::STATUS_CANCEL]);

        $this->assertEquals(Service::STATUS_CANCEL, $service->fresh()->status);
    }

    public function test_can_set_service_charge()
    {
        $owner = $this->createTenantUser(['role' => 'owner']);
        $service = $this->createService(['created_by' => $owner->id]);

        $service->update([
            'service_charge' => 50000,
            'total_cost' => 50000,
        ]);

        $fresh = $service->fresh();
        $this->assertEquals(50000, $fresh->service_charge);
        $this->assertEquals(50000, $fresh->total_cost);
    }

    public function test_service_tracking_code_is_generated()
    {
        $user = $this->createTenantUser();
        $service = $this->createService(['created_by' => $user->id]);

        $this->assertNotNull($service->tracking_code);
        $this->assertEquals(8, strlen($service->tracking_code));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]+$/', $service->tracking_code);
    }
}
