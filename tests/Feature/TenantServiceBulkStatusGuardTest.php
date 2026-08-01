<?php

namespace Tests\Feature;

use App\Models\Tenant\Service;
use Tests\TestCase;

class TenantServiceBulkStatusGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_owner_bulk_accept_assigns_current_user_and_skips_other_branch_services(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerA = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branchA->id,
        ]);

        $ownerB = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branchB->id,
        ]);

        $customerA = $this->createCustomer(['branch_id' => $branchA->id]);
        $customerB = $this->createCustomer(['branch_id' => $branchB->id]);

        $serviceA = $this->createService([
            'branch_id' => $branchA->id,
            'customer_id' => $customerA->id,
            'created_by' => $ownerA->id,
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
            'technician_id' => null,
        ]);

        $serviceB = $this->createService([
            'branch_id' => $branchB->id,
            'customer_id' => $customerB->id,
            'created_by' => $ownerB->id,
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
            'technician_id' => null,
        ]);

        $this->actingAs($ownerA);

        $response = $this->post(route('services.bulk-status'), [
            'ids' => [$serviceA->id, $serviceB->id],
            'status' => Service::STATUS_DITERIMA,
        ]);

        $response->assertSessionHas('success');

        $serviceA->refresh();
        $serviceB->refresh();

        $this->assertSame(Service::STATUS_DITERIMA, $serviceA->status);
        $this->assertSame($ownerA->id, $serviceA->technician_id);
        $this->assertSame(Service::STATUS_MENUNGGU_ALOKASI, $serviceB->status);
        $this->assertNull($serviceB->technician_id);
    }

    public function test_cs_cannot_bulk_accept_services(): void
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

        $customer = $this->createCustomer(['branch_id' => $branch->id]);

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
        ]);

        $this->actingAs($cs);

        $response = $this->post(route('services.bulk-status'), [
            'ids' => [$service->id],
            'status' => Service::STATUS_DITERIMA,
        ]);

        $response->assertSessionHas('error');

        $service->refresh();
        $this->assertSame(Service::STATUS_MENUNGGU_ALOKASI, $service->status);
        $this->assertNull($service->technician_id);
    }

    public function test_bulk_update_rejects_unsupported_statuses(): void
    {
        $branch = $this->createBranch(['name' => 'Cabang A']);

        $owner = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branch->id,
        ]);

        $customer = $this->createCustomer(['branch_id' => $branch->id]);

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
        ]);

        $this->actingAs($owner);

        $response = $this->post(route('services.bulk-status'), [
            'ids' => [$service->id],
            'status' => Service::STATUS_SELESAI,
        ]);

        $response->assertSessionHas('error');

        $service->refresh();
        $this->assertSame(Service::STATUS_MENUNGGU_ALOKASI, $service->status);
    }
}
