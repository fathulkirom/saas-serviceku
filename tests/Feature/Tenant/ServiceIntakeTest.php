<?php

namespace Tests\Feature\Tenant;

use App\Models\Tenant\Customer;
use App\Models\Tenant\Device;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceIntakeSnapshot;
use App\Models\Tenant\User;
use App\Models\Tenant\Branch;
use App\Models\Tenant\ChecklistTemplate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceIntakeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_can_create_service_intake_end_to_end()
    {
        Storage::fake('public');
        
        $branch = Branch::create(['name' => 'Main Branch']);
        $user = $this->createTenantUser([
            'role' => 'cs',
            'branch_id' => $branch->id,
            'active' => true
        ]);
        
        $customer = Customer::create([
            'name' => 'John Doe',
            'phone' => '081234567890',
            'branch_id' => $branch->id
        ]);

        $response = $this->actingAs($user)->post(route('services.store'), [
            'customer_id' => $customer->id,
            'problem_description' => 'Layar retak',
            'tipe_unit' => 'iPhone 13',
            'imei_sn' => 'IMEI1234567890',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $this->assertDatabaseHas('services', [
            'customer_id' => $customer->id,
            'problem_description' => 'Layar retak',
            'branch_id' => $branch->id,
        ]);

        $service = Service::first();

        $this->assertDatabaseHas('devices', [
            'id' => $service->device_id,
            'customer_id' => $customer->id,
            'model' => 'iPhone 13',
            'imei' => 'IMEI1234567890',
        ]);

        $this->assertDatabaseHas('service_intake_snapshots', [
            'service_id' => $service->id,
            'device_id' => $service->device_id,
            'customer_complaint' => 'Layar retak',
        ]);
    }

    public function test_branch_isolation_prevents_creating_service_for_other_branch_customer()
    {
        $branch1 = Branch::create(['name' => 'Branch A']);
        $branch2 = Branch::create(['name' => 'Branch B']);
        
        $user = $this->createTenantUser([
            'role' => 'cs',
            'branch_id' => $branch1->id,
            'active' => true
        ]);
        
        $customer = Customer::create([
            'name' => 'Jane Doe',
            'phone' => '08987654321',
            'branch_id' => $branch2->id
        ]);

        $response = $this->actingAs($user)->post(route('services.store'), [
            'customer_id' => $customer->id,
            'problem_description' => 'Mati total',
            'tipe_unit' => 'Samsung S21',
        ]);

        $response->assertStatus(403);
    }
}
