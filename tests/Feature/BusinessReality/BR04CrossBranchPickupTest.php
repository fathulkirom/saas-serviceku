<?php

namespace Tests\Feature\BusinessReality;

use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceDelivery;
use App\Models\Tenant\ServiceTransfer;
use App\Models\Tenant\ActivityLog;

/**
 * BR-004 — CROSS BRANCH PICKUP.
 *
 * A service entered at Branch A can legitimately move to custody at Branch B
 * (via ServiceTransfer: requested → sent → received) and be PICKED UP at B.
 * Origin ownership (service.branch_id) is NEVER rewritten. Unauthorized
 * branches cannot receive or pick up. Every transition is audited.
 */
class BR04CrossBranchPickupTest extends BRBranchOperationsTestCase
{
    /**
     * Helper: run the full custody chain A → B and return the transfer.
     */
    protected function transferToB(Service $service): ServiceTransfer
    {
        // Manager (primary A, also assigned B) requests + sends the transfer.
        $this->actingAs($this->manager);
        $this->post(route('service-transfers.store'), [
            'service_id' => $service->id,
            'to_branch_id' => $this->branchB->id,
            'note' => 'Ambil di cabang B',
        ])->assertSessionHas('success');

        $transfer = ServiceTransfer::where('service_id', $service->id)->first();
        $this->assertNotNull($transfer);
        $this->post(route('service-transfers.send', $transfer))->assertSessionHas('success');

        return $transfer->fresh();
    }

    public function test_service_can_be_transferred_to_branch_b_and_origin_preserved()
    {
        $service = $this->makeService($this->branchA);
        $transfer = $this->transferToB($service);

        // Origin ownership preserved.
        $this->assertEquals($this->branchA->id, $service->fresh()->branch_id, 'Origin branch MUST remain A.');
        $this->assertEquals($this->branchA->id, $transfer->from_branch_id);
        $this->assertEquals($this->branchB->id, $transfer->to_branch_id);
        $this->assertEquals(ServiceTransfer::STATUS_SENT, $transfer->status);
    }

    public function test_custody_becomes_b_after_receive()
    {
        $service = $this->makeService($this->branchA);
        $transfer = $this->transferToB($service);

        // Authorized CS at B receives custody.
        $this->actingAs($this->csB);
        $this->post(route('service-transfers.receive', $transfer))->assertSessionHas('success');

        $this->assertEquals(ServiceTransfer::STATUS_RECEIVED, $transfer->fresh()->status);
        $this->assertEquals($this->branchB->id, $service->fresh()->currentCustodyBranchId(), 'Custody MUST be B.');
        $this->assertEquals($this->branchA->id, $service->fresh()->branch_id, 'Origin still A.');
    }

    public function test_unauthorized_branch_cannot_receive()
    {
        $service = $this->makeService($this->branchA);
        $transfer = $this->transferToB($service);

        // CS at C (not B) cannot receive the transfer destined for B.
        $this->actingAs($this->csC);
        $response = $this->post(route('service-transfers.receive', $transfer));
        $response->assertStatus(403);
        $this->assertEquals(ServiceTransfer::STATUS_SENT, $transfer->fresh()->status, 'Transfer must NOT be received.');
    }

    public function test_authorized_b_can_receive()
    {
        $service = $this->makeService($this->branchA);
        $transfer = $this->transferToB($service);

        $this->actingAs($this->csB);
        $this->post(route('service-transfers.receive', $transfer))->assertSessionHas('success');

        $this->assertEquals(ServiceTransfer::STATUS_RECEIVED, $transfer->fresh()->status);
    }

    public function test_authorized_b_can_pickup_cross_branch()
    {
        $service = $this->makeService($this->branchA, Service::STATUS_SELESAI);

        // Mark ready at origin A (manager).
        $this->actingAs($this->manager);
        $this->post(route('services.ready-pickup', $service))->assertSessionHas('success');

        // Transfer A → B and receive at B.
        $transfer = $this->transferToB($service);
        $this->actingAs($this->csB);
        $this->post(route('service-transfers.receive', $transfer))->assertSessionHas('success');

        // CS B picks up (custody B, origin A).
        $this->post(route('services.pickup', $service), [
            'received_by' => 'John Doe',
            'receiver_phone' => '081234567890',
        ])->assertSessionHas('success');

        $this->assertEquals(Service::STATUS_DIAMBIL, $service->fresh()->status);
        $delivery = ServiceDelivery::where('service_id', $service->id)->first();
        $this->assertNotNull($delivery->picked_up_at);
        $this->assertEquals($this->branchB->id, $delivery->pickup_branch_id, 'Pickup recorded at custody branch B.');
        $this->assertEquals($this->branchA->id, $service->fresh()->branch_id, 'Origin remains A.');
    }

    public function test_unauthorized_branch_cannot_pickup()
    {
        $service = $this->makeService($this->branchA, Service::STATUS_SELESAI);
        $this->actingAs($this->manager);
        $this->post(route('services.ready-pickup', $service))->assertSessionHas('success');

        $transfer = $this->transferToB($service);
        $this->actingAs($this->csB);
        $this->post(route('service-transfers.receive', $transfer))->assertSessionHas('success');

        // CS at C cannot pick up (custody is B).
        $this->actingAs($this->csC);
        $response = $this->post(route('services.pickup', $service), [
            'received_by' => 'John',
            'receiver_phone' => '08123',
        ]);
        $response->assertStatus(403);
        $this->assertNull(ServiceDelivery::where('service_id', $service->id)->first()?->picked_up_at, 'Must NOT be picked up.');
    }

    public function test_timeline_contains_transfer_receipt_and_pickup()
    {
        $service = $this->makeService($this->branchA, Service::STATUS_SELESAI);
        $this->actingAs($this->manager);
        $this->post(route('services.ready-pickup', $service))->assertSessionHas('success');

        $transfer = $this->transferToB($service);
        $this->actingAs($this->csB);
        $this->post(route('service-transfers.receive', $transfer))->assertSessionHas('success');
        $this->post(route('services.pickup', $service), [
            'received_by' => 'John',
            'receiver_phone' => '08123',
        ])->assertSessionHas('success');

        $actions = ActivityLog::where('subject_type', Service::class)
            ->where('subject_id', $service->id)
            ->pluck('action')
            ->all();

        $this->assertContains('service_transfer_requested', $actions);
        $this->assertContains('service_transfer_sent', $actions);
        $this->assertContains('service_transfer_received', $actions);
        $this->assertContains('pickup', $actions);
    }

    public function test_repeated_receive_and_pickup_do_not_create_duplicate_side_effects()
    {
        $service = $this->makeService($this->branchA, Service::STATUS_SELESAI);
        $this->actingAs($this->manager);
        $this->post(route('services.ready-pickup', $service))->assertSessionHas('success');

        $transfer = $this->transferToB($service);
        $this->actingAs($this->csB);

        // Receive twice — idempotent, no duplicate audit.
        $this->post(route('service-transfers.receive', $transfer))->assertSessionHas('success');
        $this->post(route('service-transfers.receive', $transfer->fresh()))->assertStatus(302);

        $receivedAudits = ActivityLog::where('action', 'service_transfer_received')->count();
        $this->assertEquals(1, $receivedAudits, 'Receive must be audited exactly once.');

        // Pickup twice — second is blocked by idempotency.
        $this->post(route('services.pickup', $service), ['received_by' => 'John', 'receiver_phone' => '08123'])->assertSessionHas('success');
        $this->post(route('services.pickup', $service), ['received_by' => 'Jane', 'receiver_phone' => '08999'])->assertSessionHas('error');

        $pickups = ServiceDelivery::where('service_id', $service->id)->whereNotNull('picked_up_at')->count();
        $this->assertEquals(1, $pickups, 'Pickup must complete exactly once.');
    }
}
