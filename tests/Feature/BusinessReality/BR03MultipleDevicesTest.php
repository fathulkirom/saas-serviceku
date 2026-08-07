<?php

namespace Tests\Feature\BusinessReality;

use App\Models\Tenant\Branch;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Device;
use App\Models\Tenant\Product;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceDiagnosis;
use App\Models\Tenant\ServiceSparepart;
use App\Models\Tenant\ServiceWarranty;
use App\Models\Tenant\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BR-003 — MULTIPLE DEVICES, ONE CUSTOMER.
 *
 * ONE customer identity may operate MULTIPLE independent device/service
 * lifecycles (technician, diagnosis, status, parts, invoice, warranty each
 * independent) with zero data contamination.
 */
class BR03MultipleDevicesTest extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;
    protected User $owner;
    protected User $techA;
    protected User $techB;
    protected User $techC;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->grantFullPlanAccess();

        $this->branch = Branch::create(['name' => 'Cabang Utama', 'is_active' => true]);
        $this->owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $this->branch->id]);
        $this->techA = $this->createTenantUser(['name' => 'Tech A', 'role' => 'technician', 'branch_id' => $this->branch->id]);
        $this->techB = $this->createTenantUser(['name' => 'Tech B', 'role' => 'technician', 'branch_id' => $this->branch->id]);
        $this->techC = $this->createTenantUser(['name' => 'Tech C', 'role' => 'technician', 'branch_id' => $this->branch->id]);
    }

    private function makeProduct(string $name): Product
    {
        return Product::create(['name' => $name, 'branch_id' => $this->branch->id, 'stock_quantity' => 10, 'cost_price' => 5000, 'selling_price' => 15000]);
    }

    /**
     * One customer + three devices + three independent services (per technician).
     * Returns [$customer, $devices, $services].
     */
    private function seedCustomerWithThree(): array
    {
        $customer = Customer::create(['name' => 'Andi', 'phone' => '08123456789', 'branch_id' => $this->branch->id]);

        $devices = [];
        $services = [];
        foreach (['A', 'B', 'C'] as $i) {
            $device = Device::create([
                'customer_id' => $customer->id, 'brand' => 'Samsung', 'model' => "Model {$i}",
                'type' => 'smartphone', 'imei' => 'IMEI' . $i, 'status' => 'active',
            ]);
            $devices[] = $device;

            $service = Service::create([
                'branch_id' => $this->branch->id,
                'customer_id' => $customer->id,
                'device_id' => $device->id,
                'created_by' => $this->owner->id,
                'technician_id' => $this->{'tech' . $i}->id, // techA / techB / techC
                'status' => Service::STATUS_DIKERJAKAN,
                'service_charge' => 100000,
                'total_cost' => 100000,
                'warranty_days' => 30,
                'warranty_expired_at' => now()->addDays(30),
                'problem_description' => "Servis perangkat {$i}",
            ]);
            ServiceWarranty::createFromService($service, 30);
            $services[] = $service;
        }

        return [$customer, $devices, $services];
    }

    // 1. One Customer can own 3 Devices.
    public function test_one_customer_can_own_three_devices(): void
    {
        [$customer, $devices] = $this->seedCustomerWithThree();

        $this->assertCount(3, $customer->fresh()->devices);
        $this->assertCount(3, Device::where('customer_id', $customer->id)->get());
    }

    // 2. Each Device creates independent Service.
    public function test_each_device_creates_independent_service(): void
    {
        [$customer, $devices, $services] = $this->seedCustomerWithThree();

        foreach ($devices as $i => $device) {
            $this->assertSame($device->id, $services[$i]->device_id);
            $this->assertSame($customer->id, $services[$i]->customer_id);
        }
        $this->assertCount(3, Service::where('customer_id', $customer->id)->get());
    }

    // 3. Customer row is not duplicated.
    public function test_customer_row_is_not_duplicated(): void
    {
        [$customer] = $this->seedCustomerWithThree();

        $this->assertSame(1, Customer::where('phone', '08123456789')->count());
        $this->assertSame($customer->id, Customer::where('phone', '08123456789')->first()->id);
    }

    // 4. Service A technician != Service B technician is supported.
    public function test_different_technicians_supported(): void
    {
        [$customer, $devices, $services] = $this->seedCustomerWithThree();

        $this->assertSame($this->techA->id, $services[0]->technician_id);
        $this->assertSame($this->techB->id, $services[1]->technician_id);
        $this->assertSame($this->techC->id, $services[2]->technician_id);
        $this->assertNotSame($services[0]->technician_id, $services[1]->technician_id);
    }

    // 5. Status changes on Service A do not change B/C.
    public function test_status_changes_on_a_do_not_change_b_c(): void
    {
        [, , $services] = $this->seedCustomerWithThree();

        $services[0]->update(['status' => Service::STATUS_SELESAI]);

        $this->assertSame(Service::STATUS_SELESAI, $services[0]->fresh()->status);
        $this->assertSame(Service::STATUS_DIKERJAKAN, $services[1]->fresh()->status);
        $this->assertSame(Service::STATUS_DIKERJAKAN, $services[2]->fresh()->status);
    }

    // 6. Diagnosis on A does not appear on B/C.
    public function test_diagnosis_on_a_does_not_appear_on_b_c(): void
    {
        [, , $services] = $this->seedCustomerWithThree();

        ServiceDiagnosis::create(['service_id' => $services[0]->id, 'findings' => 'Fault A', 'solution' => 'Sol A', 'diagnosed_by' => $this->techA->id]);

        $this->assertSame('Fault A', $services[0]->fresh()->diagnosis?->findings);
        $this->assertNull($services[1]->fresh()->diagnosis);
        $this->assertNull($services[2]->fresh()->diagnosis);
    }

    // 7. Parts on A do not affect B/C service records.
    public function test_parts_on_a_do_not_affect_b_c(): void
    {
        [$customer, $devices, $services] = $this->seedCustomerWithThree();
        $product = $this->makeProduct('IC Charger');

        ServiceSparepart::create(['service_id' => $services[0]->id, 'product_id' => $product->id, 'quantity' => 1, 'unit_price' => 15000, 'subtotal' => 15000]);

        $this->assertSame(1, ServiceSparepart::where('service_id', $services[0]->id)->count());
        $this->assertSame(0, ServiceSparepart::where('service_id', $services[1]->id)->count());
        $this->assertSame(0, ServiceSparepart::where('service_id', $services[2]->id)->count());
    }

    // 8. Invoice A is independent from B/C.
    public function test_invoice_a_independent_from_b_c(): void
    {
        [$customer, , $services] = $this->seedCustomerWithThree();

        $saleA = Sale::create(['branch_id' => $this->branch->id, 'customer_id' => $customer->id, 'service_id' => $services[0]->id, 'sale_type' => Sale::SALE_TYPE_SERVIS, 'status' => Sale::STATUS_PAID, 'subtotal' => 100000, 'discount' => 0, 'total' => 100000, 'payment_method' => 'cash', 'paid_amount' => 100000, 'change' => 0]);

        $this->assertSame($services[0]->id, $saleA->service_id);
        $this->assertNull($services[1]->fresh()->sale);
        $this->assertNull($services[2]->fresh()->sale);
        $this->assertSame(1, Sale::where('service_id', $services[0]->id)->count());
    }

    // 9. Warranty A is independent from B/C.
    public function test_warranty_a_independent_from_b_c(): void
    {
        [, , $services] = $this->seedCustomerWithThree();

        $this->assertNotNull($services[0]->warranty);
        $this->assertNotNull($services[1]->warranty);
        $this->assertNotNull($services[2]->warranty);
        $this->assertNotSame($services[0]->warranty->id, $services[1]->warranty->id);

        $services[1]->warranty->update(['status' => 'void']);
        $this->assertSame('active', $services[0]->warranty->fresh()->status);
        $this->assertSame('void', $services[1]->warranty->fresh()->status);
        $this->assertSame('active', $services[2]->warranty->fresh()->status);
    }

    // 10. Customer history returns all 3 services correctly.
    public function test_customer_history_returns_all_three_services(): void
    {
        [$customer, , $services] = $this->seedCustomerWithThree();

        $ids = $customer->fresh()->services->pluck('id')->sort()->values();
        $expected = collect($services)->pluck('id')->sort()->values();
        $this->assertEquals($expected, $ids);
    }

    // 11. Device history remains separate.
    public function test_device_history_remains_separate(): void
    {
        [$customer, $devices, $services] = $this->seedCustomerWithThree();

        foreach ($devices as $i => $device) {
            $this->assertSame(1, Service::where('device_id', $device->id)->count());
            $this->assertSame($services[$i]->id, Service::where('device_id', $device->id)->first()->id);
        }
        $this->assertCount(3, $customer->fresh()->devices);
    }

    // 12. Closing one service does not close the others.
    public function test_closing_one_service_does_not_close_others(): void
    {
        [, , $services] = $this->seedCustomerWithThree();

        $services[0]->update(['status' => Service::STATUS_CLOSE]);

        $this->assertSame(Service::STATUS_CLOSE, $services[0]->fresh()->status);
        $this->assertSame(Service::STATUS_DIKERJAKAN, $services[1]->fresh()->status);
        $this->assertSame(Service::STATUS_DIKERJAKAN, $services[2]->fresh()->status);
    }
}

