<?php

namespace Tests\Feature;

use App\Models\Tenant\Service;
use Tests\TestCase;

class TenantServiceUpdateGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_owner_cannot_change_service_status_via_generic_update_endpoint(): void
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
            'problem_description' => 'Masalah awal',
        ]);

        $this->actingAs($owner);

        $response = $this->from(route('services.edit', $service))->put(route('services.update', $service), [
            'problem_description' => 'Masalah diubah',
            'status' => Service::STATUS_SELESAI,
        ]);

        $response->assertRedirect(route('services.edit', $service));
        $response->assertSessionHasErrors(['status']);

        $service->refresh();
        $this->assertSame(Service::STATUS_MENUNGGU_ALOKASI, $service->status);
        $this->assertSame('Masalah awal', $service->problem_description);
    }

    public function test_owner_can_update_service_fields_without_changing_status(): void
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
            'problem_description' => 'Masalah awal',
            'condition_note' => 'Catatan lama',
            'tipe_unit' => 'iPhone 12',
        ]);

        $this->actingAs($owner);

        $response = $this->put(route('services.update', $service), [
            'problem_description' => 'Masalah revisi',
            'condition_note' => 'Catatan revisi',
            'tipe_unit' => 'iPhone 13',
        ]);

        $response->assertSessionHas('success');

        $service->refresh();
        $this->assertSame(Service::STATUS_DIKERJAKAN, $service->status);
        $this->assertSame('Masalah revisi', $service->problem_description);
        $this->assertSame('Catatan revisi', $service->condition_note);
        $this->assertSame('iPhone 13', $service->tipe_unit);
    }
}
