<?php

namespace Tests\Feature\BusinessReality;

use App\Models\Tenant\ServiceRequiredPart;
use App\Models\Tenant\InventoryMutation;

/**
 * BR-009 — RESERVED STOCK.
 *
 * Canonical rule:
 *   available_stock = physical_stock - reserved_stock
 *
 * Approval creates a reservation: physical stock unchanged, reserved increases,
 * available decreases. Reservation is derived from authoritative approved
 * ServiceRequiredPart records. Reservation/release is NOT a physical mutation.
 */
class BR09ReservedStockTest extends BRPartLifecycleTestCase
{
    public function test_reservation_cannot_exceed_available_stock()
    {
        // Physical stock = 1.
        $this->product->update(['stock_quantity' => 1]);
        $service = $this->makeService();

        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 1);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $response = $this->approvePart($part);
        $response->assertSessionHas('success');

        $this->assertEquals(1, $this->product->fresh()->reserved_quantity);
        $this->assertEquals(0, $this->product->fresh()->available_quantity);
        $this->assertEquals(1, $this->product->fresh()->stock_quantity);
    }

    public function test_second_reservation_blocked_when_no_available_quantity()
    {
        // Physical stock = 1. Two services both try to reserve it.
        $this->product->update(['stock_quantity' => 1]);
        $serviceA = $this->makeService();
        $serviceB = $this->makeService();

        $this->actingAs($this->technician);
        $this->requestPart($serviceA, qty: 1);
        $partA = ServiceRequiredPart::where('service_id', $serviceA->id)->first();
        $this->requestPart($serviceB, qty: 1);
        $partB = ServiceRequiredPart::where('service_id', $serviceB->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($partA); // success — reserves the only unit

        $responseB = $this->approvePart($partB); // must be blocked
        $responseB->assertSessionHas('error');

        $this->assertEquals(1, $this->product->fresh()->reserved_quantity, 'Only one reservation.');
        $this->assertEquals(0, $this->product->fresh()->available_quantity);
        $this->assertEquals(1, $this->product->fresh()->stock_quantity);
        $this->assertEquals(ServiceRequiredPart::STATUS_REQUESTED, $partB->fresh()->status, 'Second part stays requested.');
    }

    public function test_approval_is_idempotent()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 2);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);
        $this->approvePart($part->fresh()); // repeated approval

        $this->assertEquals(2, $this->product->fresh()->reserved_quantity, 'Repeated approval must NOT double-reserve.');
        $this->assertEquals(2, (int) $part->fresh()->reserved_qty);
        $this->assertEquals(10, $this->product->fresh()->stock_quantity, 'Physical stock untouched by approval.');
    }

    public function test_cancellation_releases_reservation()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 3);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $this->approvePart($part);
        $this->assertEquals(3, $this->product->fresh()->reserved_quantity);
        $this->assertEquals(7, $this->product->fresh()->available_quantity);

        $this->actingAs($this->admin);
        $this->cancelPart($part);

        $product = $this->product->fresh();
        $this->assertEquals(ServiceRequiredPart::STATUS_CANCELLED, $part->fresh()->status);
        $this->assertEquals(0, $product->reserved_quantity, 'Cancellation MUST release reservation.');
        $this->assertEquals(10, $product->available_quantity, 'Available stock increases back to physical.');
        $this->assertEquals(10, $product->stock_quantity, 'Physical stock unchanged.');
        $this->assertEquals(0, InventoryMutation::where('product_id', $this->product->id)->count(), 'Release is NOT a physical mutation.');
    }

    public function test_rejected_request_never_reserves_stock()
    {
        $service = $this->makeService();
        $this->actingAs($this->technician);
        $this->requestPart($service, qty: 2);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();

        $this->actingAs($this->admin);
        $response = $this->rejectPart($part);
        $response->assertSessionHas('success');

        $this->assertEquals(ServiceRequiredPart::STATUS_REJECTED, $part->fresh()->status);
        $this->assertEquals(0, $this->product->fresh()->reserved_quantity, 'Rejected request MUST NOT reserve stock.');
        $this->assertEquals(10, $this->product->fresh()->available_quantity);
        $this->assertEquals(10, $this->product->fresh()->stock_quantity);
        $this->assertEquals(0, InventoryMutation::where('product_id', $this->product->id)->count());
    }
}
