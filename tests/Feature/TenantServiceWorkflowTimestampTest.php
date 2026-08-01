<?php

namespace Tests\Feature;

use App\Models\Tenant\Service;
use Tests\TestCase;

class TenantServiceWorkflowTimestampTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_assign_technician_sets_dikerjakan_at_when_moving_into_dikerjakan(): void
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

        $technician = $this->createTenantUser([
            'role' => 'technician',
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
            'dikerjakan_at' => null,
        ]);

        $this->actingAs($cs);

        $response = $this->post(route('services.assign-technician', $service), [
            'technician_id' => $technician->id,
        ]);

        $response->assertSessionHas('success');

        $service->refresh();
        $this->assertSame(Service::STATUS_DIKERJAKAN, $service->status);
        $this->assertSame($technician->id, $service->technician_id);
        $this->assertNotNull($service->dikerjakan_at);
    }

    public function test_resume_from_indent_sets_dikerjakan_at_when_missing(): void
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
            'status' => Service::STATUS_INDENT,
            'dikerjakan_at' => null,
        ]);

        $this->actingAs($owner);

        $response = $this->post(route('services.resume-from-indent', $service));

        $response->assertSessionHas('success');

        $service->refresh();
        $this->assertSame(Service::STATUS_DIKERJAKAN, $service->status);
        $this->assertNotNull($service->dikerjakan_at);
    }

    public function test_complete_partner_sets_selesai_at_when_moving_into_selesai(): void
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
            'status' => Service::STATUS_ONPARTNER,
            'selesai_at' => null,
        ]);

        $this->actingAs($owner);

        $response = $this->post(route('services.complete-partner', $service));

        $response->assertSessionHas('success');

        $service->refresh();
        $this->assertSame(Service::STATUS_SELESAI, $service->status);
        $this->assertNotNull($service->selesai_at);
    }
}
