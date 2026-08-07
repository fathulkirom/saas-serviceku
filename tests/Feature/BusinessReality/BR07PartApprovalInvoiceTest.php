<?php

namespace Tests\Feature\BusinessReality;

use App\Models\Tenant\Service;
use App\Models\Tenant\Sale;
use App\Models\Tenant\ServiceRequiredPart;
use App\Models\Tenant\ServicePartUsage;
use App\Models\Tenant\ServiceSparepart;
use App\Models\Tenant\InventoryMutation;

/**
 * BR-007 — PART REQUEST → APPROVAL → RESERVATION → INVOICE → STOCK → USAGE.
 *
 * Canonical rule:
 *   request (no stock impact)
 *   → approve (reserve, no stock impact)
 *   → CS confirm/consume (reservation consumed, physical stock reduced ONCE,
 *     usage + invoice record + inventory mutation created)
 *
 * Repair finish MUST NOT itself consume physical inventory.
 */
class BR07PartApprovalInvoiceTest extends BRPartLifecycleTestCase
{
    public function test_technician_request_does_not_reduce_physical_stock()
    {
        $service = $this->makeService();
        $stockBefore = $this->product->fresh()->stock_quantity;

        $this->actingAs($this->technician);
        $response = $this->requestPart($service);
        $response->assertSessionHas('success');

        $part = ServiceRequiredPart::where('service_id', $service->id)->first();
        $this->assertNotNull($part);
        $this->assertEquals(ServiceRequiredPart::STATUS_REQUESTED, $part->fresh()->status);
        $this->assertEquals($stockBefore, $this->product->fresh()->stock_quantity, 'Request MUST NOT change physical stock.');
        $this->assertEquals(0, $this->product->fresh()->reserved_quantity, 'Request MUST NOT create reservation.');
        $this->assertEquals(0, InventoryMutation::where('product_id', $this->product->id)->count(), 'Request MUST NOT create inventory mutation.');
    }

    public function test_approval_does_not_reduce_physical_stock()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $stockBefore = $this->product->fresh()->stock_quantity;

        $this->actingAs($this->admin);
        $response = $this->approvePart($part);
        $response->assertSessionHas('success');

        $this->assertEquals($stockBefore, $this->product->fresh()->stock_quantity, 'Approval MUST NOT change physical stock.');
        $this->assertEquals(0, InventoryMutation::where('product_id', $this->product->id)->count(), 'Approval MUST NOT create physical mutation.');
    }

    public function test_approval_creates_reservation()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 3);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);

        $this->assertEquals(ServiceRequiredPart::STATUS_APPROVED, $part->fresh()->status);
        $this->assertEquals(3, (int) $part->fresh()->reserved_qty, 'Approval MUST set reserved_qty.');
        $this->assertEquals(3, $this->product->fresh()->reserved_quantity, 'Approval MUST create reservation on product.');
        $this->assertEquals(10, $this->product->fresh()->stock_quantity, 'Approval MUST NOT reduce physical stock.');
    }

    public function test_available_stock_equals_physical_minus_reserved()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 3);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);

        $product = $this->product->fresh();
        $this->assertEquals(10, $product->stock_quantity, 'Physical stock unchanged.');
        $this->assertEquals(3, $product->reserved_quantity, 'Reserved = 3.');
        $this->assertEquals(7, $product->available_quantity, 'Available = physical - reserved.');
    }

    public function test_cs_confirmation_consumes_reservation()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 3);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);

        $this->actingAs($this->cs);
        $response = $this->consumePart($part);
        $response->assertSessionHas('success');

        $part = $part->fresh();
        $this->assertEquals(ServiceRequiredPart::STATUS_USED, $part->status, 'Consumed part MUST be marked used.');
        $this->assertEquals(0, (int) $part->reserved_qty, 'Consumption MUST consume reservation.');
        $this->assertEquals(0, $this->product->fresh()->reserved_quantity, 'No reservation remains.');
        $this->assertEquals(7, $this->product->fresh()->stock_quantity, 'Physical stock reduced once (10-3).');
    }

    public function test_cs_confirmation_reduces_stock_exactly_once()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 2);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);

        $this->actingAs($this->cs);
        $this->consumePart($part);

        $this->assertEquals(8, $this->product->fresh()->stock_quantity, 'Stock reduced exactly once (10-2).');
        $this->assertEquals(1, ServicePartUsage::where('service_id', $service->id)->count(), 'Usage created exactly once.');
    }

    public function test_inventory_mutation_created_exactly_once()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 1);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);

        $this->actingAs($this->cs);
        $this->consumePart($part);

        $mutations = InventoryMutation::where('product_id', $this->product->id)
            ->where('type', 'keluar')
            ->get();
        $this->assertCount(1, $mutations, 'Exactly one physical deduction mutation.');
        $this->assertEquals(1, $mutations->first()->quantity);
        $this->assertEquals(9, $mutations->first()->after_stock);
    }

    public function test_consumed_part_appears_on_invoice()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 1);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);

        $this->actingAs($this->cs);
        $this->consumePart($part, sellingPrice: 100000);

        // Repair finish (work completion) must not change the consumed state.
        $this->actingAs($this->owner);
        $this->completeRepair($service);
        $this->assertEquals(Service::STATUS_SELESAI, $service->fresh()->status);

        $this->draftFromService($service);

        $sale = Sale::where('service_id', $service->id)->first();
        $this->assertNotNull($sale, 'Draft sale must be created.');

        $partItems = $sale->items()->where('item_type', 'sparepart')->get();
        $this->assertCount(1, $partItems, 'Consumed part MUST appear on invoice.');
        $this->assertEquals($this->product->id, $partItems->first()->product_id);
        $this->assertEquals(1, $partItems->first()->quantity);
        $this->assertEquals(100000, (float) $partItems->first()->price);

        $this->assertEquals(250000, (float) $sale->total, 'Invoice total = labor + consumed part.');
    }

    public function test_approved_but_unconsumed_part_does_not_appear_on_invoice()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 1);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);

        // NOTE: No CS consumption — part stays approved/reserved.

        $this->actingAs($this->owner);
        $this->completeRepair($service);
        $this->draftFromService($service);

        $sale = Sale::where('service_id', $service->id)->first();
        $this->assertNotNull($sale, 'Draft sale must be created (labor only).');

        $partItems = $sale->items()->where('item_type', 'sparepart')->get();
        $this->assertCount(0, $partItems, 'Approved but unconsumed part MUST NOT appear on invoice.');
        $this->assertEquals(150000, (float) $sale->total, 'Invoice only contains labor.');
        $this->assertEquals(0, ServiceSparepart::where('service_id', $service->id)->count(), 'No billable sparepart record.');
    }

    public function test_repeated_confirmation_does_not_double_deduct()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 2);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);

        $this->actingAs($this->cs);
        $this->consumePart($part);

        // Second confirmation of the same part must be rejected.
        $response = $this->consumePart($part);
        $response->assertSessionHas('error');

        $this->assertEquals(8, $this->product->fresh()->stock_quantity, 'Stock deducted exactly once.');
        $this->assertEquals(1, ServicePartUsage::where('service_id', $service->id)->count(), 'Usage created exactly once.');
        $this->assertEquals(1, ServiceSparepart::where('service_id', $service->id)->count(), 'Billable sparepart created exactly once.');
    }

    public function test_repair_finish_does_not_deduct_stock()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 1);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);

        $this->actingAs($this->owner);
        $response = $this->completeRepair($service, [
            ['product_id' => $this->product->id, 'qty' => 1],
        ]);
        $response->assertSessionHas('success');

        $this->assertEquals(Service::STATUS_SELESAI, $service->fresh()->status, 'Repair work completion still works.');
        $this->assertEquals(10, $this->product->fresh()->stock_quantity, 'Repair finish MUST NOT deduct physical stock.');
        $this->assertEquals(0, ServicePartUsage::where('service_id', $service->id)->count(), 'No usage from repair finish.');
        $this->assertEquals(0, ServiceSparepart::where('service_id', $service->id)->count(), 'No billable part from repair finish.');
        $this->assertEquals(0, InventoryMutation::where('product_id', $this->product->id)->count(), 'No mutation from repair finish.');
        $this->assertEquals(ServiceRequiredPart::STATUS_APPROVED, $part->fresh()->status, 'Part stays approved/reserved until CS confirms.');
    }
}
