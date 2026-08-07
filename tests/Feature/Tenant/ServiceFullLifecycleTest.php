<?php

namespace Tests\Feature\Tenant;

use Tests\TestCase;
use App\Models\Tenant\Service;
use App\Models\Tenant\User;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use App\Models\Tenant\Sale;
use App\Models\Tenant\SaleItem;
use App\Models\Tenant\ServiceDelivery;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ServiceFullLifecycleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        tenant()->update(['plan_id' => 3]); // Upgrade plan to allow sales
    }

    public function test_service_full_lifecycle()
    {
        // 1. Setup Data
        $branch = Branch::create(['name' => 'Main Branch']);
        
        $owner = $this->createTenantUser([
            'name' => 'Owner',
            'role' => 'owner',
            'branch_id' => $branch->id,
            'active' => true
        ]);

        $technician = $this->createTenantUser([
            'name' => 'Technician',
            'role' => 'technician',
            'branch_id' => $branch->id,
            'active' => true
        ]);

        $cs = $this->createTenantUser([
            'name' => 'CS',
            'role' => 'cs',
            'branch_id' => $branch->id,
            'active' => true
        ]);
        
        $customer = Customer::create([
            'name' => 'John Doe',
            'phone' => '081234567890',
            'branch_id' => $branch->id
        ]);
        
        $product = Product::create([
            'name' => 'LCD Screen',
            'branch_id' => $branch->id,
            'stock_quantity' => 10,
            'cost_price' => 50000,
            'selling_price' => 100000,
        ]);

        $this->actingAs($owner);

        // 2. Service Intake (Mocking creation as it's tested elsewhere)
        $service = Service::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'device_id' => null, // Just to bypass if it exists
            'created_by' => $owner->id,
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
            'service_charge' => 150000,
            'problem_description' => 'Layar rusak',
        ]);

        // 3. Technician Assignment
        $response = $this->post("/services/{$service->id}/assign", [
            'technician_id' => $technician->id,
        ]);
        $response->assertSessionHas('success');
        $this->assertEquals(Service::STATUS_DITERIMA, $service->fresh()->status);

        // 4. Technician Starts Repair
        $this->actingAs($technician);
        $response = $this->post("/services/{$service->id}/repair/start");
        $response->assertSessionHas('success');
        $this->assertEquals(Service::STATUS_DIKERJAKAN, $service->fresh()->status);

        // 5. BR-FIX-01 CANONICAL PART LIFECYCLE:
        //    request → approve (reserve) → CS confirm/consume → repair finish.
        //    Repair finish itself MUST NOT consume physical inventory.

        // 5a. Technician requests the part (no stock impact)
        $this->actingAs($technician);
        $response = $this->post("/services/{$service->id}/parts/request", [
            'product_id' => $product->id,
            'part_name' => 'LCD Screen',
            'qty' => 1,
            'notes' => 'Ganti layar',
        ]);
        $response->assertSessionHas('success');
        $part = \App\Models\Tenant\ServiceRequiredPart::where('service_id', $service->id)->first();
        $this->assertNotNull($part, 'Part request must be recorded.');
        $this->assertEquals(10, $product->fresh()->stock_quantity, 'Request must not deduct stock');

        // 5b. Admin approves → reservation (physical stock unchanged)
        $this->actingAs($owner);
        $response = $this->post("/service-parts/{$part->id}/approve");
        $response->assertSessionHas('success');
        $this->assertEquals(10, $product->fresh()->stock_quantity, 'Approval must not deduct stock');
        $this->assertEquals(1, $product->fresh()->reserved_quantity, 'Approval reserves stock');

        // 5c. CS confirms/consumes → physical stock reduced EXACTLY ONCE, billable record created
        $this->actingAs($cs);
        $response = $this->post("/service-parts/{$part->id}/use", [
            'selling_price' => 100000,
            'discount' => 0,
        ]);
        $response->assertSessionHas('success');
        $this->assertEquals(9, $product->fresh()->stock_quantity, 'CS confirmation reduces stock once');
        $this->assertEquals(1, \App\Models\Tenant\ServiceSparepart::where('service_id', $service->id)->count(), 'Billable sparepart created');

        // 5d. Technician completes repair — WORK COMPLETION ONLY (no stock impact)
        $this->actingAs($technician);
        $response = $this->post("/services/{$service->id}/repair/complete", [
            'repair_notes' => 'Ganti layar',
        ]);
        $response->assertSessionHas('success');
        $this->assertEquals(Service::STATUS_SELESAI, $service->fresh()->status);
        $this->assertEquals(9, $product->fresh()->stock_quantity, 'Repair finish must NOT change stock');

        // 6. QC Pass (done by owner/manager)
        $this->actingAs($owner);
        $response = $this->post("/services/{$service->id}/qc", [
            'checks' => [['item' => 'Layar', 'result' => 'pass']],
            'qc_decision' => 'pass',
        ]);
        $response->assertSessionHas('success');

        // Note: QC pass does not change status directly in completeRepair if it's separate?
        // Wait, QC pass transition is not defined in `TechnicianWorkflowController@storeQcCheck`. Let's assume it doesn't change it to `SIAP_DIAMBIL` automatically, it's a separate step?
        // Ah, the user flow says "Ready Pickup".
        
        // 7. Ready for Pickup
        $response = $this->post("/services/{$service->id}/ready-pickup");
        $response->assertSessionHas('success');
        $this->assertEquals(Service::STATUS_SIAP_DIAMBIL, $service->fresh()->status);

        // 8. Create Invoice
        $response = $this->post("/sales/draft-from-service/{$service->id}");
        $response->assertSessionHas('success');
        
        $sale = Sale::where('service_id', $service->id)->first();
        $this->assertNotNull($sale);
        $this->assertEquals(Sale::STATUS_DRAFT, $sale->status);
        $this->assertEquals(250000, $sale->total); // 150000 service + 100000 part

        // 9. Payment
        $response = $this->post("/sales/{$sale->id}/pay-draft", [
            'paid_amount' => 250000,
            'payment_method' => 'Cash',
        ]);
        $response->assertSessionHas('success');
        $this->assertEquals(Sale::STATUS_PAID, $sale->fresh()->status);
        $this->assertEquals('paid', $service->fresh()->payment_status);

        // 10. Handover / Pickup
        $response = $this->post("/services/{$service->id}/pickup", [
            'received_by' => 'John Doe',
            'receiver_phone' => '08123456789',
        ]);
        $response->assertSessionHas('success');
        $this->assertEquals(Service::STATUS_DIAMBIL, $service->fresh()->status);
        
        $delivery = ServiceDelivery::where('service_id', $service->id)->first();
        $this->assertNotNull($delivery->picked_up_at);

        // 11. Close
        $response = $this->post("/services/{$service->id}/close");
        $response->assertSessionHas('success');
        $this->assertEquals(Service::STATUS_CLOSE, $service->fresh()->status);
    }
}
