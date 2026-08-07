<?php

namespace Tests\Feature\BusinessReality;

use App\Models\Tenant\Sale;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceDiagnosis;
use App\Models\Tenant\ServicePartUsage;
use App\Models\Tenant\ServiceQcCheck;
use App\Models\Tenant\ServiceRequiredPart;
use App\Models\Tenant\ServiceWarrantyClaim;
use App\Models\Tenant\Request;
use App\Models\Tenant\WorkOrder;

/**
 * BR-011 — WARRANTY REPAIR RETURN (rework).
 *
 * Canonical sequence (BR-FIX-04.1):
 * OPEN (submitted, no rework) → REVIEW → APPROVE (creates rework exactly once)
 * → REPAIR → QC FAIL (claim stays open, rework returns to repair) / QC PASS
 * (claim resolved at ready-pickup). Original Service stays immutable.
 *
 * Covers STEP 24 (16) + STEP 16 additions/corrections.
 */
class BR11WarrantyReworkTest extends BRWarrantyTestCase
{
    private function claimFor(Service $original): ServiceWarrantyClaim
    {
        return ServiceWarrantyClaim::where('service_id', $original->id)->firstOrFail();
    }

    // ── STEP 24 ────────────────────────────────────────────────────────────

    // 1. Completed paid closed Service creates/has valid warranty where applicable.
    public function test_completed_paid_closed_service_has_valid_warranty(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);

        $this->assertTrue((bool) $service->fresh()->isWarrantyValid());
        $warranty = $service->warranty;
        $this->assertNotNull($warranty);
        $this->assertSame('active', $warranty->status);
        $this->assertTrue($warranty->isActive());
    }

    // 2. Valid active warranty accepts claim (opens as submitted).
    public function test_valid_active_warranty_accepts_claim(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);

        $response = $this->openClaim($service, $this->csA);
        $response->assertRedirect();

        $claim = $this->claimFor($service);
        $this->assertSame('submitted', $claim->status);
        $this->assertDatabaseCount('service_warranty_claims', 1);
    }

    // 3. Expired store warranty rejects normal store claim.
    public function test_expired_store_warranty_rejects_claim(): void
    {
        $service = $this->makeWarrantyService($this->branchA, expired: true);
        $this->assertFalse((bool) $service->fresh()->isWarrantyValid());

        $beforeServices = Service::count();
        $response = $this->openClaim($service, $this->csA);
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertSame($beforeServices, Service::count());
        $this->assertDatabaseCount('service_warranty_claims', 0);
    }

    // 4. Claim preserves original Service unchanged.
    public function test_claim_preserves_original_service_unchanged(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        ServiceDiagnosis::create([
            'service_id' => $service->id, 'customer_complaint' => 'Charging IC',
            'findings' => 'Fault A', 'cause' => 'IC rusak', 'solution' => 'Ganti IC', 'diagnosed_by' => $this->techA->id,
        ]);

        $snapshot = [
            'status' => $service->status,
            'payment_status' => $service->payment_status,
            'service_charge' => $service->service_charge,
            'total_cost' => $service->total_cost,
            'technician_id' => $service->technician_id,
            'branch_id' => $service->branch_id,
            'created_by' => $service->created_by,
        ];

        $claim = $this->openClaim($service, $this->csA);
        $this->approveClaim($this->claimFor($service), $this->owner);
        $this->driveReworkToQcPass(Service::where('is_warranty_claim', true)->first(), $this->owner);

        $original = $service->fresh();
        foreach ($snapshot as $k => $v) {
            $this->assertEquals($v, $original->{$k}, "Original {$k} must remain unchanged");
        }
        $this->assertSame('Fault A', $original->diagnosis?->findings);
    }

    // 5. Approval creates/links NEW rework Service (open itself creates none).
    public function test_approval_creates_and_links_new_rework_service(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);

        $this->openClaim($service, $this->csA);
        $claim = $this->claimFor($service);
        $this->assertNull($claim->rework_service_id);
        $this->assertSame(0, Service::where('is_warranty_claim', true)->count());

        $this->approveClaim($claim, $this->owner);

        $claim->refresh();
        $rework = Service::where('is_warranty_claim', true)->first();
        $this->assertNotNull($rework);
        $this->assertSame($rework->id, $claim->rework_service_id);
        $this->assertSame($service->id, $rework->parent_service_id);
        $this->assertNotSame($service->id, $rework->id);
        $this->assertTrue((bool) $rework->is_warranty_claim);
    }

    // 6. Rework uses same customer/device.
    public function test_rework_uses_same_customer_and_device(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        $this->assertNotNull($service->device_id);

        $this->openClaim($service, $this->csA);
        $this->approveClaim($this->claimFor($service), $this->owner);

        $rework = Service::where('is_warranty_claim', true)->first();
        $this->assertSame($service->customer_id, $rework->customer_id);
        $this->assertSame($service->device_id, $rework->device_id);
    }

    // 7. Rework has independent status/workorder.
    public function test_rework_has_independent_status_and_workorder(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);

        $this->openClaim($service, $this->csA);
        $this->approveClaim($this->claimFor($service), $this->owner);
        $rework = Service::where('is_warranty_claim', true)->first();

        $this->assertSame(Service::STATUS_MENUNGGU_ALOKASI, $rework->status);
        $this->assertNotSame($service->status, $rework->status);

        $req = Request::create([
            'request_number' => 'RQ-' . uniqid(),
            'customer_id' => $rework->customer_id,
            'branch_id' => $rework->branch_id,
            'type' => 'service',
            'source' => 'internal',
            'status' => 'created',
            'created_by' => $this->owner->id,
        ]);
        $wo = WorkOrder::create([
            'request_id' => $req->id,
            'service_id' => $rework->id,
            'device_id' => $rework->device_id,
            'technician_id' => $this->techA->id,
            'title' => 'Rework ' . $rework->id,
            'category' => 'repair',
            'status' => 'assigned',
        ]);
        $this->assertDatabaseHas('work_orders', ['id' => $wo->id, 'service_id' => $rework->id]);
    }

    // 8. New diagnosis does not overwrite original diagnosis.
    public function test_new_diagnosis_does_not_overwrite_original(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        ServiceDiagnosis::create([
            'service_id' => $service->id, 'customer_complaint' => 'Charging IC',
            'findings' => 'Fault A', 'cause' => 'IC rusak', 'solution' => 'Ganti IC', 'diagnosed_by' => $this->techA->id,
        ]);

        $this->openClaim($service, $this->csA);
        $this->approveClaim($this->claimFor($service), $this->owner);
        $rework = Service::where('is_warranty_claim', true)->first();

        ServiceDiagnosis::create([
            'service_id' => $rework->id, 'customer_complaint' => 'Mati total',
            'findings' => 'Fault B', 'cause' => 'CPU/motherboard', 'solution' => 'Replace board', 'diagnosed_by' => $this->techA->id,
        ]);

        $this->assertSame('Fault A', $service->fresh()->diagnosis?->findings);
        $this->assertSame('Fault B', $rework->fresh()->diagnosis?->findings);
    }

    // 9. Warranty rework does not duplicate original Sale revenue.
    public function test_rework_does_not_duplicate_original_sale_revenue(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        $salesBefore = Sale::count();

        $this->openClaim($service, $this->csA);
        $this->approveClaim($this->claimFor($service), $this->owner);
        $rework = Service::where('is_warranty_claim', true)->first();

        $this->assertSame($salesBefore, Sale::count());
        $this->assertEquals(0, (float) $rework->service_charge);
        $this->assertEquals(0, (float) $rework->total_cost);
    }

    // 10. Warranty rework does not duplicate original Payment.
    public function test_rework_does_not_duplicate_original_payment(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        $originalSale = $service->sale;
        $this->assertNotNull($originalSale);
        $this->assertSame(Sale::STATUS_PAID, $originalSale->status);
        $paidAmountBefore = $originalSale->paid_amount;

        $this->openClaim($service, $this->csA);
        $this->approveClaim($this->claimFor($service), $this->owner);

        $this->assertSame($paidAmountBefore, $originalSale->fresh()->paid_amount);
        $this->assertSame(Sale::STATUS_PAID, $originalSale->fresh()->status);
        $this->assertSame(1, Sale::where('service_id', $service->id)->count());
    }

    // 11. Warranty rework part usage follows BR-FIX-01 lifecycle.
    public function test_rework_part_usage_follows_br_fix01_lifecycle(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        $product = $this->makeProduct($this->branchA, 'IC Power');
        $this->openClaim($service, $this->csA);
        $this->approveClaim($this->claimFor($service), $this->owner);
        $rework = Service::where('is_warranty_claim', true)->first();

        $this->actingAs($this->techA);
        $this->post(route('service-parts.request', $rework), [
            'product_id' => $product->id, 'part_name' => 'IC Power', 'qty' => 1,
        ]);

        $part = ServiceRequiredPart::where('service_id', $rework->id)->first();
        $this->assertSame('requested', $part->status);

        $this->actingAs($this->owner);
        $this->post(route('service-parts.approve', $part));
        $this->assertSame('approved', $part->fresh()->status);

        $this->actingAs($this->csA);
        $this->post(route('service-parts.use', $part), ['selling_price' => 25000]);
        $part->refresh();
        $this->assertSame('used', $part->status);
        $this->assertSame(9, $product->fresh()->stock_quantity);

        $this->assertDatabaseHas('service_part_usages', ['service_id' => $rework->id, 'product_id' => $product->id]);
        $this->assertDatabaseHas('inventory_mutations', [
            'product_id' => $product->id, 'type' => 'keluar', 'reference_type' => 'service_part_usage',
        ]);
        $this->assertSame(0, ServicePartUsage::where('service_id', $service->id)->count());
    }

    // 12. Warranty rework requires QC (no warranty shortcut).
    public function test_rework_requires_qc(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        $this->openClaim($service, $this->csA);
        $this->approveClaim($this->claimFor($service), $this->owner);
        $rework = Service::where('is_warranty_claim', true)->first();

        $this->actingAs($this->owner);
        $this->post(route('services.assign-technician', $rework), ['technician_id' => $this->techA->id]);
        $this->post(route('services.finish', $rework));
        $this->assertSame(Service::STATUS_SELESAI, $rework->fresh()->status);

        $this->post(route('services.qc.store', $rework), [
            'checks' => [['item' => 'Fungsi', 'result' => 'pass', 'notes' => 'ok']],
            'qc_decision' => 'pass', 'qc_notes' => 'QC rework lulus',
        ]);

        $this->assertDatabaseHas('service_qc_checks', ['service_id' => $rework->id, 'result' => 'pass']);
        $this->assertSame(Service::STATUS_SIAP_DIAMBIL, $rework->fresh()->status);
    }

    // 13. Claim resolves against linked rework AT QC PASS (not repair finish).
    public function test_claim_resolves_at_qc_pass_not_repair_finish(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        $this->openClaim($service, $this->csA);
        $claim = $this->claimFor($service);
        $this->approveClaim($claim, $this->owner);
        $rework = Service::where('is_warranty_claim', true)->first();

        // Repair finish does NOT resolve the claim.
        $this->actingAs($this->owner);
        $this->post(route('services.assign-technician', $rework), ['technician_id' => $this->techA->id]);
        $this->post(route('services.finish', $rework));

        $claim->refresh();
        $this->assertSame('approved', $claim->status);
        $this->assertNull($claim->completed_at);
        $this->assertNull($claim->resolved_by);

        // QC PASS resolves it.
        $this->post(route('services.qc.store', $rework), [
            'checks' => [['item' => 'Fungsi', 'result' => 'pass', 'notes' => 'ok']],
            'qc_decision' => 'pass', 'qc_notes' => 'ok',
        ]);

        $claim->refresh();
        $this->assertSame('completed', $claim->status);
        $this->assertNotNull($claim->completed_at);
        $this->assertSame($this->owner->id, $claim->resolved_by);
    }

    // 14. Duplicate claim/rework creation is blocked safely.
    public function test_duplicate_claim_creation_is_blocked(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);

        $this->openClaim($service, $this->csA);
        $this->assertSame(1, ServiceWarrantyClaim::where('service_id', $service->id)->count());

        $response = $this->openClaim($service, $this->csA);
        $response->assertSessionHas('error');
        $this->assertSame(1, ServiceWarrantyClaim::where('service_id', $service->id)->count());
        $this->assertSame(0, Service::where('is_warranty_claim', true)->count());
    }

    // 15. Unauthorized branch cannot open claim.
    public function test_unauthorized_branch_cannot_open_claim(): void
    {
        $serviceB = $this->makeClosedPaidService($this->branchB);
        $before = Service::count();

        $this->openClaim($serviceB, $this->csA)->assertSessionHas('error');

        $this->assertSame($before, Service::count());
        $this->assertDatabaseCount('service_warranty_claims', 0);
    }

    // 16. Legitimately authorized branch can handle claim.
    public function test_legitimately_authorized_branch_can_handle_claim(): void
    {
        $serviceB = $this->makeClosedPaidService($this->branchB);

        $this->openClaim($serviceB, $this->manager, 'Komplain di cabang B', (int) $this->branchB->id)
            ->assertSessionHasNoErrors();
        $claim = $this->claimFor($serviceB);
        $this->assertSame((int) $this->branchB->id, (int) $claim->branch_id);

        $this->approveClaim($claim, $this->owner);
        $rework = Service::where('is_warranty_claim', true)->first();
        $this->assertNotNull($rework);
        $this->assertSame((int) $this->branchB->id, (int) $rework->branch_id);
    }

    // ── STEP 16 additions/corrections ──────────────────────────────────────

    // 17. Open claim creates submitted/checking claim.
    public function test_open_claim_creates_submitted_claim(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        $this->openClaim($service, $this->csA);
        $this->assertSame('submitted', $this->claimFor($service)->status);
    }

    // 18. Open claim does not set approved_by.
    public function test_open_claim_does_not_set_approved_by(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        $this->openClaim($service, $this->csA);
        $claim = $this->claimFor($service);
        $this->assertNull($claim->approved_by);
        $this->assertNull($claim->checked_by);
    }

    // 19. Open claim creates no rework.
    public function test_open_claim_creates_no_rework(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        $this->openClaim($service, $this->csA);
        $this->assertNull($this->claimFor($service)->rework_service_id);
        $this->assertSame(0, Service::where('is_warranty_claim', true)->count());
    }

    // 20. Unauthorized opener cannot approve.
    public function test_unauthorized_opener_cannot_approve(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        $this->openClaim($service, $this->csA);
        $claim = $this->claimFor($service);

        $this->approveClaim($claim, $this->csA)->assertStatus(403);
        $this->approveClaim($claim, $this->techA)->assertStatus(403);

        $this->assertSame('submitted', $claim->fresh()->status);
        $this->assertSame(0, Service::where('is_warranty_claim', true)->count());
    }

    // 21. Authorized approval creates exactly one rework.
    public function test_authorized_approval_creates_exactly_one_rework(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        $this->openClaim($service, $this->csA);
        $claim = $this->claimFor($service);

        $this->approveClaim($claim, $this->owner);
        $this->assertSame('approved', $claim->fresh()->status);
        $this->assertSame(1, Service::where('is_warranty_claim', true)->count());
        $this->assertNotNull($claim->fresh()->rework_service_id);
    }

    // 22. Rejection creates no rework.
    public function test_rejection_creates_no_rework(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        $this->openClaim($service, $this->csA);
        $claim = $this->claimFor($service);

        $this->rejectClaim($claim, $this->owner, 'Bukan cacat garansi');

        $claim->refresh();
        $this->assertSame('rejected', $claim->status);
        $this->assertNull($claim->rework_service_id);
        $this->assertSame(0, Service::where('is_warranty_claim', true)->count());
        $this->assertSame(Service::STATUS_CLOSE, $service->fresh()->status);
    }

    // 23. Repeated approval creates no duplicate rework.
    public function test_repeated_approval_creates_no_duplicate_rework(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        $this->openClaim($service, $this->csA);
        $claim = $this->claimFor($service);

        $this->approveClaim($claim, $this->owner);
        $this->approveClaim($claim->fresh(), $this->owner);

        $this->assertSame(1, Service::where('is_warranty_claim', true)->count());
        $this->assertSame(1, ServiceWarrantyClaim::where('service_id', $service->id)->count());
    }

    // 24. Repair finish does not complete claim.
    public function test_repair_finish_does_not_complete_claim(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        $this->openClaim($service, $this->csA);
        $claim = $this->claimFor($service);
        $this->approveClaim($claim, $this->owner);
        $rework = Service::where('is_warranty_claim', true)->first();

        $this->actingAs($this->owner);
        $this->post(route('services.assign-technician', $rework), ['technician_id' => $this->techA->id]);
        $this->post(route('services.finish', $rework));

        $claim->refresh();
        $this->assertSame('approved', $claim->status);
        $this->assertNull($claim->completed_at);
        $this->assertNull($claim->resolved_by);
    }

    // 25. QC fail keeps claim open.
    public function test_qc_fail_keeps_claim_open(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        $this->openClaim($service, $this->csA);
        $claim = $this->claimFor($service);
        $this->approveClaim($claim, $this->owner);
        $rework = Service::where('is_warranty_claim', true)->first();

        $this->actingAs($this->owner);
        $this->post(route('services.assign-technician', $rework), ['technician_id' => $this->techA->id]);
        $this->post(route('services.finish', $rework));
        $this->post(route('services.qc.store', $rework), [
            'checks' => [['item' => 'Fungsi', 'result' => 'fail', 'notes' => 'rusak']],
            'qc_decision' => 'fail', 'qc_notes' => 'QC gagal',
        ]);

        $claim->refresh();
        $this->assertSame('approved', $claim->status);
        $this->assertNull($claim->completed_at);
        $this->assertNull($claim->resolved_by);
    }

    // 26. QC fail returns rework to repair.
    public function test_qc_fail_returns_rework_to_repair(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        $this->openClaim($service, $this->csA);
        $this->approveClaim($this->claimFor($service), $this->owner);
        $rework = Service::where('is_warranty_claim', true)->first();

        $this->actingAs($this->owner);
        $this->post(route('services.assign-technician', $rework), ['technician_id' => $this->techA->id]);
        $this->post(route('services.finish', $rework));
        $this->post(route('services.qc.store', $rework), [
            'checks' => [['item' => 'Fungsi', 'result' => 'fail', 'notes' => 'rusak']],
            'qc_decision' => 'fail', 'qc_notes' => 'QC gagal',
        ]);

        $this->assertSame(Service::STATUS_DIKERJAKAN, $rework->fresh()->status);
        // No duplicate rework, no new revenue, no commission.
        $this->assertSame(1, Service::where('is_warranty_claim', true)->count());
        $this->assertSame(0, \App\Models\Tenant\Commission::where('service_id', $rework->id)->count());
    }

    // 27. Subsequent QC pass resolves at canonical point.
    public function test_subsequent_qc_pass_resolves_at_canonical_point(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        $this->openClaim($service, $this->csA);
        $claim = $this->claimFor($service);
        $this->approveClaim($claim, $this->owner);
        $rework = Service::where('is_warranty_claim', true)->first();

        $this->actingAs($this->owner);
        $this->post(route('services.assign-technician', $rework), ['technician_id' => $this->techA->id]);
        $this->post(route('services.finish', $rework));
        $this->post(route('services.qc.store', $rework), [
            'checks' => [['item' => 'Fungsi', 'result' => 'fail', 'notes' => 'rusak']],
            'qc_decision' => 'fail', 'qc_notes' => 'QC gagal',
        ]);
        $this->assertSame(Service::STATUS_DIKERJAKAN, $rework->fresh()->status);

        // QC is decided once per day (existing idempotency) — re-QC happens on
        // a later day after re-repair.
        \Illuminate\Support\Carbon::setTestNow(now()->addDay());

        try {
            $this->post(route('services.finish', $rework->fresh()));
            $this->post(route('services.qc.store', $rework->fresh()), [
                'checks' => [['item' => 'Fungsi', 'result' => 'pass', 'notes' => 'ok']],
                'qc_decision' => 'pass', 'qc_notes' => 'QC lulus setelah perbaikan ulang',
            ]);
        } finally {
            \Illuminate\Support\Carbon::setTestNow();
        }

        $claim->refresh();
        $this->assertSame('completed', $claim->status);
        $this->assertNotNull($claim->completed_at);
        $this->assertSame(Service::STATUS_SIAP_DIAMBIL, $rework->fresh()->status);
        $this->assertSame(1, Service::where('is_warranty_claim', true)->count());
    }

    // 28. Original service remains unchanged throughout the full cycle.
    public function test_original_service_remains_unchanged_throughout(): void
    {
        $service = $this->makeClosedPaidService($this->branchA);
        ServiceDiagnosis::create([
            'service_id' => $service->id, 'customer_complaint' => 'Charging IC',
            'findings' => 'Fault A', 'cause' => 'IC rusak', 'solution' => 'Ganti IC', 'diagnosed_by' => $this->techA->id,
        ]);
        $snapshot = [
            'status' => $service->status,
            'payment_status' => $service->payment_status,
            'service_charge' => $service->service_charge,
            'total_cost' => $service->total_cost,
            'technician_id' => $service->technician_id,
            'branch_id' => $service->branch_id,
            'created_by' => $service->created_by,
        ];
        $originalSalePaid = $service->sale?->paid_amount;

        $this->openClaim($service, $this->csA);
        $this->approveClaim($this->claimFor($service), $this->owner);
        $rework = Service::where('is_warranty_claim', true)->first();
        $this->driveReworkToQcPass($rework, $this->owner);

        $original = $service->fresh();
        foreach ($snapshot as $k => $v) {
            $this->assertEquals($v, $original->{$k}, "Original {$k} must remain unchanged");
        }
        $this->assertSame('Fault A', $original->diagnosis?->findings);
        $this->assertEquals($originalSalePaid, $original->sale?->paid_amount);
        $this->assertSame(0, ServicePartUsage::where('service_id', $service->id)->count());
        // Original commission preserved (not deleted, not duplicated).
        $this->assertSame(1, \App\Models\Tenant\Commission::where('service_id', $service->id)->count());
    }
}

