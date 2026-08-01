<?php

namespace Tests\Feature;

use App\Models\Tenant\Service;
use Tests\TestCase;

class TenantCsWorkflowGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_cs_cannot_create_service_for_customer_from_other_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $cs = $this->createTenantUser([
            'role' => 'cs',
            'branch_id' => $branchA->id,
        ]);

        $customerOtherBranch = $this->createCustomer([
            'branch_id' => $branchB->id,
            'name' => 'Customer B',
        ]);

        $this->actingAs($cs);

        $response = $this->post(route('services.store'), [
            'customer_id' => $customerOtherBranch->id,
            'problem_description' => 'Layar mati total',
            'tipe_unit' => 'iPhone 12',
        ]);

        $response
            ->assertSessionHasErrors(['customer_id']);

        $this->assertDatabaseCount('services', 0);
    }

    public function test_cs_cannot_assign_technician_from_other_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $owner = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branchA->id,
        ]);

        $cs = $this->createTenantUser([
            'role' => 'cs',
            'branch_id' => $branchA->id,
        ]);

        $service = $this->createService([
            'branch_id' => $branchA->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
        ]);

        $techOtherBranch = $this->createTenantUser([
            'role' => 'technician',
            'branch_id' => $branchB->id,
        ]);

        $this->actingAs($cs);

        $response = $this->post(route('services.assign-technician', $service), [
            'technician_id' => $techOtherBranch->id,
        ]);

        $response->assertSessionHasErrors(['technician_id']);

        $service->refresh();
        $this->assertNull($service->technician_id);
        $this->assertSame(Service::STATUS_MENUNGGU_ALOKASI, $service->status);
    }

    public function test_cs_can_assign_active_technician_in_same_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);

        $owner = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branchA->id,
        ]);

        $cs = $this->createTenantUser([
            'role' => 'cs',
            'branch_id' => $branchA->id,
        ]);

        $service = $this->createService([
            'branch_id' => $branchA->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
        ]);

        $technician = $this->createTenantUser([
            'role' => 'technician',
            'branch_id' => $branchA->id,
            'active' => true,
        ]);

        $this->actingAs($cs);

        $response = $this->post(route('services.assign-technician', $service), [
            'technician_id' => $technician->id,
        ]);

        $response->assertSessionHas('success');

        $service->refresh();
        $this->assertSame($technician->id, $service->technician_id);
        $this->assertSame(Service::STATUS_DIKERJAKAN, $service->status);
    }

    public function test_cs_cannot_assign_global_technician_without_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);

        $owner = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branchA->id,
        ]);

        $cs = $this->createTenantUser([
            'role' => 'cs',
            'branch_id' => $branchA->id,
        ]);

        $globalTechnician = $this->createTenantUser([
            'role' => 'technician',
            'branch_id' => null,
            'active' => true,
            'name' => 'Teknisi Global',
        ]);

        $customer = $this->createCustomer([
            'branch_id' => $branchA->id,
        ]);

        $service = $this->createService([
            'branch_id' => $branchA->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
        ]);

        $this->actingAs($cs);

        $response = $this->post(route('services.assign-technician', $service), [
            'technician_id' => $globalTechnician->id,
        ]);

        $response->assertSessionHasErrors(['technician_id']);

        $service->refresh();
        $this->assertNull($service->technician_id);
        $this->assertSame(Service::STATUS_MENUNGGU_ALOKASI, $service->status);
    }
}
