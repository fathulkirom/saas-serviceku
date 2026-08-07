<?php

namespace Tests\Feature\Pilot;

use Tests\TestCase;
use App\Models\Tenant\Service;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Product;
use App\Models\Tenant\ServiceRequiredPart;
use App\Models\Tenant\ServiceSparepart;
use App\Models\Tenant\ServiceDelivery;
use App\Models\Tenant\ServiceWarranty;

/**
 * PILOT-READY-01 — Step 18: Definitive executable pilot test.
 *
 * Proves a real service shop can run the PRIMARY daily journey through the
 * application's own HTTP routes (no mocks, no tinker, no seeder dependency):
 *
 *   fresh tenant → branch → users → customer → device → service intake →
 *   technician → diagnosis/repair → part request/approve/consume → finish →
 *   fees (complete) → QC → invoice → payment → pickup → warranty visible.
 *
 * Also guards PILOT-READY-01 P1-1 (Enterprise Workspace section prop contract)
 * so the service detail page can never silently regress to empty tabs.
 */
class PilotStoreOperationalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->grantFullPlanAccess();
    }

    // ═══════════════════════════════════════════════════════════════
    // STEP 5 — Fresh tenant provisions minimum operational data
    //          through the REAL application routes (zero developer
    //          intervention / zero demo seed).
    // ═══════════════════════════════════════════════════════════════
    public function test_fresh_tenant_provisions_master_data_via_real_routes(): void
    {
        $branch = $this->createBranch(['name' => 'Cabang Utama']);
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $this->actingAs($owner);

        // 1. Branch — branches.store (multi_branch granted by full plan)
        $this->post(route('branches.store'), ['name' => 'Cabang Pilot', 'address' => 'Jl. Pilot 1'])
            ->assertSessionHas('success');
        $this->assertDatabaseHas('branches', ['name' => 'Cabang Pilot']);

        // 2. Users — users.store (CS, technician, cashier)
        $this->post(route('users.store'), [
            'name' => 'CS Pilot', 'email' => 'cs_pilot@test.com',
            'password' => 'password123', 'role' => 'cs', 'branch_id' => $branch->id,
        ])->assertSessionHas('success');
        $this->post(route('users.store'), [
            'name' => 'Teknisi Pilot', 'email' => 'tech_pilot@test.com',
            'password' => 'password123', 'role' => 'technician', 'branch_id' => $branch->id,
        ])->assertSessionHas('success');
        $this->assertDatabaseHas('users', ['email' => 'cs_pilot@test.com']);
        $this->assertDatabaseHas('users', ['email' => 'tech_pilot@test.com']);

        // 3. Customer — customers.ajax-store (quick-add path used at intake)
        $this->post(route('customers.ajax-store'), [
            'name' => 'Pelanggan Pilot', 'phone' => '081299887766',
        ])->assertJson(['success' => true]);
        $this->assertDatabaseHas('customers', ['name' => 'Pelanggan Pilot']);

        // 4. Product — products.store (sparepart inventory path)
        $this->post(route('products.store'), [
            'name' => 'LCD Pilot', 'cost_price' => 50000,
            'selling_price' => 100000, 'stock_quantity' => 10,
        ])->assertSessionHas('success');
        $this->assertDatabaseHas('products', ['name' => 'LCD Pilot', 'stock_quantity' => 10]);
    }

    // ═══════════════════════════════════════════════════════════════
    // STEP 18 — Primary daily operation, end-to-end through real routes.
    // ═══════════════════════════════════════════════════════════════
    public function test_primary_service_operation_journey_via_real_routes(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id, 'email' => 'owner@test.com']);
        $cs = $this->createTenantUser(['role' => 'cs', 'branch_id' => $branch->id, 'email' => 'cs@test.com']);
        $tech = $this->createTenantUser(['role' => 'technician', 'branch_id' => $branch->id, 'email' => 'tech@test.com']);
        $cashier = $this->createTenantUser(['role' => 'cashier', 'branch_id' => $branch->id, 'email' => 'cashier@test.com']);

        $product = Product::create([
            'name' => 'LCD Screen', 'branch_id' => $branch->id,
            'stock_quantity' => 10, 'cost_price' => 50000, 'selling_price' => 100000,
        ]);

        // ── CS: customer + service intake ──
        $this->actingAs($cs);
        $customerResp = $this->post(route('customers.ajax-store'), [
            'name' => 'John Doe', 'phone' => '081234567890',
        ])->assertJson(['success' => true]);
        $customerId = $customerResp->json('customer.id');

        $this->post(route('services.store'), [
            'customer_id' => $customerId,
            'problem_description' => 'Layar retak dan tidak menyala',
            'tipe_unit' => 'iPhone 13 Pro',
            'imei_sn' => '356789012345678',
            'condition_note' => 'Baret ringan di body',
        ])->assertSessionHas('success');

        $service = Service::where('customer_id', $customerId)->latest()->first();
        $this->assertNotNull($service);
        $this->assertSame(Service::STATUS_MENUNGGU_ALOKASI, $service->status);
        $this->assertNotNull($service->device_id, 'Intake must persist a device row (IMEI/SN).');

        // ── Owner: assign technician ──
        $this->actingAs($owner);
        $this->post("/services/{$service->id}/assign", ['technician_id' => $tech->id])
            ->assertSessionHas('success');
        $this->assertSame(Service::STATUS_DITERIMA, $service->fresh()->status);

        // ── Technician: start repair ──
        $this->actingAs($tech);
        $this->post("/services/{$service->id}/repair/start")->assertSessionHas('success');
        $this->assertSame(Service::STATUS_DIKERJAKAN, $service->fresh()->status);

        // ── Technician: request part (no stock impact) ──
        $this->post("/services/{$service->id}/parts/request", [
            'product_id' => $product->id, 'part_name' => 'LCD Screen',
            'qty' => 1, 'notes' => 'Ganti layar',
        ])->assertSessionHas('success');
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();
        $this->assertNotNull($part);
        $this->assertSame(10, $product->fresh()->stock_quantity);

        // ── Owner: approve → reserves ──
        $this->actingAs($owner);
        $this->post("/service-parts/{$part->id}/approve")->assertSessionHas('success');
        $this->assertSame(10, $product->fresh()->stock_quantity);
        $this->assertSame(1, $product->fresh()->reserved_quantity);

        // ── CS: confirm/consume → stock reduced exactly once, billable row ──
        $this->actingAs($cs);
        $this->post("/service-parts/{$part->id}/use", ['selling_price' => 100000, 'discount' => 0])
            ->assertSessionHas('success');
        $this->assertSame(9, $product->fresh()->stock_quantity);
        $this->assertSame(1, ServiceSparepart::where('service_id', $service->id)->count());

        // ── Technician: finish repair → selesai ──
        $this->actingAs($tech);
        $this->post("/services/{$service->id}/repair/complete", ['repair_notes' => 'Ganti layar selesai'])
            ->assertSessionHas('success');
        $this->assertSame(Service::STATUS_SELESAI, $service->fresh()->status);
        $this->assertSame(9, $product->fresh()->stock_quantity, 'Finish must not change stock.');

        // ── Owner: set fees (complete) → total = part + labor ──
        $this->actingAs($owner);
        $this->post("/services/{$service->id}/complete", ['service_charge' => 150000])
            ->assertRedirect(route('services.show', $service->id));
        $this->assertSame(250000.0, (float) $service->fresh()->total_cost);

        // ── Owner: QC pass → siap_diambil ──
        $this->post("/services/{$service->id}/qc", [
            'checks' => [['item' => 'Layar', 'result' => 'pass']],
            'qc_decision' => 'pass',
        ])->assertSessionHas('success');
        $this->assertSame(Service::STATUS_SIAP_DIAMBIL, $service->fresh()->status);

        // ── Owner: mark ready (pickup queue) ──
        $this->post("/services/{$service->id}/ready-pickup")->assertSessionHas('success');
        $this->assertNotNull(ServiceDelivery::where('service_id', $service->id)->first()?->ready_at);

        // ── Cashier: draft invoice from service ──
        $this->actingAs($cashier);
        $this->post("/sales/draft-from-service/{$service->id}")->assertSessionHas('success');
        $sale = Sale::where('service_id', $service->id)->first();
        $this->assertNotNull($sale);
        $this->assertSame(Sale::STATUS_DRAFT, $sale->status);
        $this->assertSame(250000.0, (float) $sale->total);

        // ── Cashier: payment → paid ──
        $this->post("/sales/{$sale->id}/pay-draft", ['paid_amount' => 250000, 'payment_method' => 'cash'])
            ->assertSessionHas('success');
        $this->assertSame(Sale::STATUS_PAID, $sale->fresh()->status);
        $this->assertSame('paid', $service->fresh()->payment_status);

        // ── Cashier: pickup → diambil + warranty auto-created ──
        $this->post("/services/{$service->id}/pickup", [
            'received_by' => 'John Doe', 'receiver_phone' => '081234567890', 'receiver_relation' => 'self',
        ])->assertSessionHas('success');
        $this->assertSame(Service::STATUS_DIAMBIL, $service->fresh()->status);
        $warranty = ServiceWarranty::where('service_id', $service->id)->first();
        $this->assertNotNull($warranty, 'Pickup must auto-create the store warranty.');
        $this->assertSame(30, (int) $warranty->duration_days);
        $this->assertTrue($warranty->isActive());
    }

    // ═══════════════════════════════════════════════════════════════
    // PILOT-READY-01 P1-1 — the service detail (Enterprise Workspace)
    // must expose the rich section props so tabs render real content.
    // ═══════════════════════════════════════════════════════════════
    public function test_service_workspace_page_exposes_section_prop_contract(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer(['branch_id' => $branch->id]);
        $product = Product::create([
            'name' => 'Baterai', 'branch_id' => $branch->id,
            'stock_quantity' => 5, 'selling_price' => 75000,
        ]);
        $service = $this->createService([
            'branch_id' => $branch->id, 'customer_id' => $customer->id,
            'created_by' => $owner->id, 'technician_id' => $owner->id,
            'status' => Service::STATUS_DIKERJAKAN, 'service_charge' => 50000, 'total_cost' => 50000,
        ]);

        $this->actingAs($owner);
        $response = $this->get(route('services.show', $service));
        $response->assertOk();

        $data = $response->viewData('page')['props']['workspaceConfig']['data'] ?? [];

        // Rich `service` object (nested warranty/claims/can_refund/upstream).
        $this->assertSame($service->id, $data['service']['id'] ?? null);
        $this->assertSame('dikerjakan', $data['service']['status'] ?? null);
        $this->assertArrayHasKey('warranty', $data['service']);
        $this->assertArrayHasKey('warranty_claims', $data['service']);
        $this->assertArrayHasKey('can_refund', $data['service']);
        $this->assertArrayHasKey('upstream_warranty', $data['service']);

        // Per-section props consumed by Overview / Sparepart / Foto / Diagnosa / QC / Garansi.
        $this->assertSame($service->id, $data['serviceId'] ?? null);
        $this->assertArrayHasKey('customerSummary', $data);
        $this->assertArrayHasKey('previousServices', $data);
        $this->assertArrayHasKey('relatedServices', $data);
        $this->assertArrayHasKey('availableProducts', $data);
        $this->assertArrayHasKey('spareparts', $data);
        $this->assertArrayHasKey('photos', $data);
        $this->assertArrayHasKey('diagnosis', $data);
        $this->assertArrayHasKey('qcChecks', $data);
        $this->assertArrayHasKey('sale', $data);
        $this->assertSame(50000.0, (float) ($data['serviceCharge'] ?? 0));
        $this->assertSame(50000.0, (float) ($data['totalCost'] ?? 0));
        $this->assertTrue($data['canQC']);
        $this->assertTrue($data['canManageParts']);
        $this->assertTrue($data['canRequestPart']);
    }
}
