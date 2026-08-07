<?php

namespace Tests\Feature\BusinessReality;

use App\Models\Tenant\Service;
use App\Models\Tenant\Sale;
use App\Models\Tenant\SaleItem;
use App\Models\Tenant\ServiceRequiredPart;
use App\Models\Tenant\ServicePartUsage;
use App\Models\Tenant\ServiceSparepart;
use App\Models\Tenant\ServicePartReturn;
use App\Models\Tenant\InventoryMutation;

/**
 * BR-008 — PART RETURN.
 *
 * Canonical rule — two distinct cases:
 *   A. APPROVED/RESERVED BUT NEVER CONSUMED → release reservation only.
 *      Physical stock never changed → no restore, no mutation.
 *   B. ALREADY CONSUMED/ISSUED BUT RETURNED UNUSED → restore physical stock,
 *      reversal InventoryMutation, reverse usage, remove/adjust billable item
 *      if invoice is not finalized. Finalized/paid invoices are NOT silently
 *      modified — operation is blocked.
 */
class BR08PartReturnTest extends BRPartLifecycleTestCase
{
    public function test_reserved_only_cancellation_leaves_physical_stock_unchanged()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 3);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);
        $this->assertEquals(3, $this->product->fresh()->reserved_quantity);

        $this->cancelPart($part->fresh());

        $product = $this->product->fresh();
        $this->assertEquals(10, $product->stock_quantity, 'Reserved-only cancellation leaves physical stock unchanged.');
        $this->assertEquals(0, $product->reserved_quantity, 'Reservation released.');
        $this->assertEquals(0, InventoryMutation::where('product_id', $this->product->id)->count(), 'No physical mutation.');
    }

    public function test_reserved_only_return_releases_reservation_without_restoring_stock()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 2);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);
        $this->assertEquals(2, $this->product->fresh()->reserved_quantity);

        // Return flow for a reserved-but-never-consumed part.
        $this->actingAs($this->cs);
        $this->requestReturn($service, $this->product->id, 2, $part->id);
        $return = ServicePartReturn::where('service_id', $service->id)->first();
        $this->assertNotNull($return);

        $this->processReturn($return);

        $product = $this->product->fresh();
        $this->assertEquals(10, $product->stock_quantity, 'Physical stock never changed → no restore.');
        $this->assertEquals(0, $product->reserved_quantity, 'Reservation released.');
        $this->assertEquals(0, InventoryMutation::where('product_id', $this->product->id)->count(), 'No reversal mutation for reserved-only.');
        $this->assertEquals('processed', $return->fresh()->status);
    }

    public function test_consumed_unused_part_return_restores_stock_once()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 1);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);

        $this->actingAs($this->cs);
        $this->consumePart($part);
        $this->assertEquals(9, $this->product->fresh()->stock_quantity);

        // Return the consumed-but-unused part.
        $this->requestReturn($service, $this->product->id, 1, $part->id);
        $return = ServicePartReturn::where('service_id', $service->id)->first();
        $this->processReturn($return);

        $this->assertEquals(10, $this->product->fresh()->stock_quantity, 'Return restores physical stock once.');
        $this->assertEquals(ServiceRequiredPart::STATUS_RETURNED, $part->fresh()->status);
        $this->assertEquals('processed', $return->fresh()->status);
    }

    public function test_return_creates_reversal_mutation()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 1);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);

        $this->actingAs($this->cs);
        $this->consumePart($part);

        $this->requestReturn($service, $this->product->id, 1, $part->id);
        $return = ServicePartReturn::where('service_id', $service->id)->first();
        $this->processReturn($return);

        $mutations = InventoryMutation::where('product_id', $this->product->id)->get();
        $this->assertCount(2, $mutations, 'One deduction + one reversal.');
        $this->assertContains('keluar', $mutations->pluck('type')->all());
        // Reversal restores stock → recorded as 'masuk' (incoming) with a
        // service_part_return reference (inventory_mutations.type enum is
        // limited to masuk|keluar|transfer).
        $this->assertContains('masuk', $mutations->pluck('type')->all(), 'Return creates a reversal mutation.');
        $reversal = $mutations->firstWhere('reference_type', 'service_part_return');
        $this->assertNotNull($reversal, 'Reversal is identifiable via reference_type.');
        $this->assertEquals(1, $reversal->quantity);
        $this->assertEquals(10, $reversal->after_stock);
    }

    public function test_repeated_return_does_not_double_restore()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 1);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);

        $this->actingAs($this->cs);
        $this->consumePart($part);
        $this->assertEquals(9, $this->product->fresh()->stock_quantity);

        $this->requestReturn($service, $this->product->id, 1, $part->id);
        $return = ServicePartReturn::where('service_id', $service->id)->first();
        $this->processReturn($return);
        $this->assertEquals(10, $this->product->fresh()->stock_quantity);

        // Repeated processing must not restore again.
        $this->processReturn($return->fresh());
        $this->assertEquals(10, $this->product->fresh()->stock_quantity, 'Repeated return MUST NOT double-restore.');
        $this->assertEquals(1, InventoryMutation::where('product_id', $this->product->id)->where('type', 'masuk')->where('reference_type', 'service_part_return')->count(), 'Only one reversal mutation.');
    }

    public function test_invoice_draft_reflects_legitimate_return()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 1);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);

        $this->actingAs($this->cs);
        $this->consumePart($part, sellingPrice: 100000);

        $this->actingAs($this->owner);
        $this->completeRepair($service);
        $this->draftFromService($service);

        $sale = Sale::where('service_id', $service->id)->first();
        $this->assertNotNull($sale);
        $this->assertEquals(1, $sale->items()->where('item_type', 'sparepart')->count(), 'Part billed before return.');

        // Legitimate return before invoice finalization.
        $this->actingAs($this->cs);
        $this->requestReturn($service, $this->product->id, 1, $part->id);
        $return = ServicePartReturn::where('service_id', $service->id)->first();
        $this->processReturn($return);

        $this->assertEquals(0, ServiceSparepart::where('service_id', $service->id)->count(), 'Billable sparepart removed after return.');
        $this->assertEquals(0, $sale->fresh()->items()->where('item_type', 'sparepart')->count(), 'Draft invoice no longer bills returned part.');
        $this->assertEquals(150000, (float) $sale->fresh()->total, 'Draft invoice adjusted to labor only.');
        $this->assertEquals(10, $this->product->fresh()->stock_quantity, 'Stock restored.');
    }

    public function test_finalized_paid_invoice_is_not_silently_modified()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 1);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);

        $this->actingAs($this->cs);
        $this->consumePart($part, sellingPrice: 100000);
        $this->assertEquals(9, $this->product->fresh()->stock_quantity);

        // Simulate a finalized (PAID) invoice for this service.
        Sale::create([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'sale_type' => Sale::SALE_TYPE_SERVIS,
            'status' => Sale::STATUS_PAID,
            'service_id' => $service->id,
            'subtotal' => 250000,
            'discount' => 0,
            'total' => 250000,
            'payment_method' => 'Cash',
            'paid_amount' => 250000,
            'change' => 0,
        ]);

        $this->actingAs($this->cs);
        $this->requestReturn($service, $this->product->id, 1, $part->id);
        $return = ServicePartReturn::where('service_id', $service->id)->first();
        $response = $this->processReturn($return);
        $response->assertSessionHas('error'); // Return against paid invoice must be blocked.

        $this->assertEquals(9, $this->product->fresh()->stock_quantity, 'Stock MUST NOT be restored for finalized invoice.');
        $this->assertEquals(1, ServiceSparepart::where('service_id', $service->id)->count(), 'Billable record untouched.');
        $this->assertEquals(ServiceRequiredPart::STATUS_USED, $part->fresh()->status, 'Part stays used.');
        $this->assertEquals(0, InventoryMutation::where('product_id', $this->product->id)->where('type', 'masuk')->where('reference_type', 'service_part_return')->count(), 'No reversal mutation for blocked return.');
        $this->assertEquals(1, InventoryMutation::where('product_id', $this->product->id)->where('type', 'keluar')->count(), 'Only the original deduction mutation.');
    }
}
