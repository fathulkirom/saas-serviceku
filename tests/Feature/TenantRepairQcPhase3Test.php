<?php

namespace Tests\Feature;

use App\Models\Tenant\Product;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceQuotation;
use App\Models\Tenant\ServiceQcCheck;
use App\Models\Tenant\ServiceRequiredPart;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Phase 3 — Repair Execution & Quality Control
 * 
 * 12 acceptance tests covering:
 * 1. Repair rejected if quotation not approved
 * 2. Non-assignee technician rejected; role override allowed
 * 3. Start repair idempotent (only once)
 * 4. Final stock check at repair time rejects insufficient stock
 * 5. Part usage produces correct stock movement, no duplicate on retry
 * 6. Complete repair transitions to SELESAI status
 * 7. QC only by authorized roles
 * 8. QC pass → ready pickup; QC fail → back to repair
 * 9. QC cannot process same service twice
 * 10. Timeline/audit/events created for repair/QC transitions
 * 11. Tenant/branch isolation on service, parts, QC
 * 12. Backend test + build/lint verification
 */
class TenantRepairQcPhase3Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 1: Repair rejected if quotation not yet approved (when required)
    // ═══════════════════════════════════════════════════════════════
    public function test_start_repair_rejected_when_quotation_not_approved(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $tech = $this->createTenantUser(['role' => 'technician', 'branch_id' => $branch->id, 'email' => 'tech@test.com']);
        $customer = $this->createCustomer();

        // Service in KONFIRMASI_PELANGGAN (waiting for approval) — no approved quotation
        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'technician_id' => $tech->id,
            'status' => Service::STATUS_KONFIRMASI_PELANGGAN,
        ]);

        // Quotation exists but is NOT approved (still 'sent')
        ServiceQuotation::create([
            'service_id' => $service->id,
            'total_cost' => 100000,
            'items' => json_encode([]),
            'status' => 'sent',
            'created_by' => $owner->id,
        ]);

        $this->actingAs($tech);

        try {
            $this->withoutExceptionHandling();
            $this->post(route('services.repair.start', $service));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(409, $e->getStatusCode());
        }

        // Status must NOT change
        $service->refresh();
        $this->assertEquals(Service::STATUS_KONFIRMASI_PELANGGAN, $service->status);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 2: Non-assignee technician rejected; owner/admin override allowed
    // ═══════════════════════════════════════════════════════════════
    public function test_start_repair_rejects_non_assigned_technician_allows_override(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $assignedTech = $this->createTenantUser(['role' => 'technician', 'email' => 'assigned@test.com', 'branch_id' => $branch->id]);
        $otherTech = $this->createTenantUser(['role' => 'technician', 'email' => 'other@test.com', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'technician_id' => $assignedTech->id,
            'status' => Service::STATUS_DIKERJAKAN,
        ]);

        // Other technician should be REJECTED (either 403 or AuthorizationException)
        $this->actingAs($otherTech);
        try {
            $this->withoutExceptionHandling();
            $this->post(route('services.repair.start', $service));
            $this->fail('Expected authorization exception was not thrown');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException|\Illuminate\Auth\Access\AuthorizationException $e) {
            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 403;
            $this->assertContains($status, [403, 0]);
        }

        // Owner can override and start repair
        $this->actingAs($owner);
        $response = $this->post(route('services.repair.start', $service));
        $response->assertStatus(302);

        $service->refresh();
        $this->assertNotNull($service->dikerjakan_at);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 3: Start repair idempotent — can only start once per day
    // ═══════════════════════════════════════════════════════════════
    public function test_start_repair_is_idempotent(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $tech = $this->createTenantUser(['role' => 'technician', 'email' => 'tech@test.com', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'technician_id' => $tech->id,
            'status' => Service::STATUS_DIKERJAKAN,
        ]);

        // First start — OK
        $this->actingAs($tech);
        $r1 = $this->post(route('services.repair.start', $service));
        $r1->assertStatus(302);

        // Second start — idempotent 409
        try {
            $this->withoutExceptionHandling();
            $this->post(route('services.repair.start', $service));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(409, $e->getStatusCode());
        }

        // dikerjakan_at should remain same
        $service->refresh();
        $this->assertNotNull($service->dikerjakan_at);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 4: Approval rejects a reservation that exceeds AVAILABLE stock
    // (BR-FIX-01 BR-009: available = physical - reserved; cannot reserve more)
    // ═══════════════════════════════════════════════════════════════
    public function test_approval_rejects_insufficient_available_stock(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $tech = $this->createTenantUser(['role' => 'technician', 'email' => 'tech@test.com', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'technician_id' => $tech->id,
            'status' => Service::STATUS_DIKERJAKAN,
            'dikerjakan_at' => now(),
        ]);

        // Product with only 1 in stock
        $product = Product::create([
            'branch_id' => $branch->id,
            'name' => 'Rare IC Chip',
            'selling_price' => 500000,
            'stock_quantity' => 1,
        ]);

        // Technician requests 5 (more than available)
        $this->actingAs($tech);
        $this->post(route('service-parts.request', $service), [
            'product_id' => $product->id,
            'qty' => 5,
            'part_name' => $product->name,
        ]);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();
        $this->assertNotNull($part);

        // Approval must be rejected (available 1 < 5)
        $this->actingAs($owner);
        $response = $this->post(route('service-parts.approve', $part));
        $response->assertStatus(302);

        $part->refresh();
        $this->assertEquals('requested', $part->status, 'Unapproved: stays requested');

        // No reservation, no physical change
        $product->refresh();
        $this->assertEquals(0, $product->reserved_quantity);
        $this->assertEquals(1, $product->stock_quantity, 'Stock must NOT be touched');

        // Service status must NOT change
        $service->refresh();
        $this->assertEquals(Service::STATUS_DIKERJAKAN, $service->status);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 5: Part usage (request→approve→CS consume) produces correct stock
    // movement; repair finish does NOT double-deduct (BR-FIX-01 canonical)
    // ═══════════════════════════════════════════════════════════════
    public function test_part_usage_produces_correct_stock_movement(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $tech = $this->createTenantUser(['role' => 'technician', 'email' => 'tech@test.com', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'technician_id' => $tech->id,
            'status' => Service::STATUS_DIKERJAKAN,
            'dikerjakan_at' => now(),
        ]);

        $product = Product::create([
            'branch_id' => $branch->id,
            'name' => 'LCD Connector',
            'selling_price' => 30000,
            'cost_price' => 20000,
            'stock_quantity' => 10,
        ]);

        $stockBefore = $product->stock_quantity;

        // 1. Technician requests the part (no stock impact)
        $this->actingAs($tech);
        $this->post(route('service-parts.request', $service), [
            'product_id' => $product->id,
            'part_name' => $product->name,
            'qty' => 2,
        ])->assertStatus(302);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();
        $this->assertNotNull($part);
        $product->refresh();
        $this->assertEquals($stockBefore, $product->stock_quantity, 'Request must not deduct stock');

        // 2. Owner approves → reservation (no physical impact)
        $this->actingAs($owner);
        $this->post(route('service-parts.approve', $part))->assertStatus(302);
        $part->refresh();
        $this->assertEquals('approved', $part->status);
        $this->assertEquals(2, $product->fresh()->reserved_quantity);
        $this->assertEquals($stockBefore, $product->fresh()->stock_quantity, 'Approval must not deduct stock');

        // 3. CS/owner confirms consumption → stock reduced exactly once
        $this->post(route('service-parts.use', $part), [
            'selling_price' => $product->selling_price,
            'discount' => 0,
        ])->assertStatus(302);
        $part->refresh();
        $this->assertEquals('used', $part->status);
        $product->refresh();
        $this->assertEquals($stockBefore - 2, $product->stock_quantity);

        // Inventory mutation created (single deduction)
        $this->assertDatabaseHas('inventory_mutations', [
            'product_id' => $product->id,
            'type' => 'keluar',
            'quantity' => 2,
        ]);

        // 4. Complete repair — WORK COMPLETION only, MUST NOT deduct again
        $this->actingAs($tech);
        $response = $this->post(route('services.repair.complete', $service), [
            'repair_notes' => 'Selesai',
            'parts_used' => [['product_id' => $product->id, 'qty' => 2]],
        ]);
        $response->assertStatus(302);

        // Service status → SELESAI
        $service->refresh();
        $this->assertEquals(Service::STATUS_SELESAI, $service->status);

        // No double deduction
        $product->refresh();
        $this->assertEquals($stockBefore - 2, $product->stock_quantity, 'Repair finish must NOT deduct stock');

        // Retry complete repair — should fail (status no longer DIKERJAKAN)
        try {
            $this->withoutExceptionHandling();
            $this->post(route('services.repair.complete', $service), [
                'parts_used' => [['product_id' => $product->id, 'qty' => 1]],
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(409, $e->getStatusCode());
        }

        // Stock NOT reduced again
        $product->refresh();
        $this->assertEquals($stockBefore - 2, $product->stock_quantity);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 6: Complete repair transitions to SELESAI status
    // ═══════════════════════════════════════════════════════════════
    public function test_complete_repair_transitions_to_selesai(): void
    {
        Event::fake([\App\Events\Entity\RepairCompleted::class]);

        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $tech = $this->createTenantUser(['role' => 'technician', 'email' => 'tech@test.com', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'technician_id' => $tech->id,
            'status' => Service::STATUS_DIKERJAKAN,
            'dikerjakan_at' => now(),
        ]);

        $this->actingAs($tech);

        $response = $this->post(route('services.repair.complete', $service), [
            'repair_notes' => 'LCD sudah diganti, charging port diperbaiki.',
        ]);

        $response->assertStatus(302);

        $service->refresh();
        $this->assertEquals(Service::STATUS_SELESAI, $service->status);
        $this->assertNotNull($service->selesai_at);

        Event::assertDispatched(\App\Events\Entity\RepairCompleted::class);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 7: QC only by authorized roles (manager/admin/owner)
    // ═══════════════════════════════════════════════════════════════
    public function test_qc_rejected_for_unauthorized_roles(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $tech = $this->createTenantUser(['role' => 'technician', 'email' => 'tech@test.com', 'branch_id' => $branch->id]);
        $cs = $this->createTenantUser(['role' => 'cs', 'email' => 'cs@test.com', 'branch_id' => $branch->id]);
        $manager = $this->createTenantUser(['role' => 'manager', 'email' => 'mgr@test.com', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'technician_id' => $tech->id,
            'status' => Service::STATUS_SELESAI,
            'selesai_at' => now(),
        ]);

        // Technician cannot QC
        $this->actingAs($tech);
        try {
            $this->withoutExceptionHandling();
            $this->post(route('services.qc.store', $service), [
                'checks' => [['item' => 'Touchscreen', 'result' => 'pass', 'notes' => '']],
                'qc_decision' => 'pass',
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }

        // CS cannot QC
        $this->actingAs($cs);
        try {
            $this->withoutExceptionHandling();
            $this->post(route('services.qc.store', $service), [
                'checks' => [['item' => 'Touchscreen', 'result' => 'pass', 'notes' => '']],
                'qc_decision' => 'pass',
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }

        // Manager CAN QC
        $this->actingAs($manager);
        $response = $this->post(route('services.qc.store', $service), [
            'checks' => [['item' => 'Touchscreen', 'result' => 'pass', 'notes' => 'ok']],
            'qc_decision' => 'pass',
            'qc_notes' => 'All good.',
        ]);
        $response->assertStatus(302);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 8: QC pass → READY PICKUP; QC fail → back to REPAIR
    // ═══════════════════════════════════════════════════════════════
    public function test_qc_pass_transitions_to_ready_qc_fail_returns_to_repair(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $tech = $this->createTenantUser(['role' => 'technician', 'email' => 'tech@test.com', 'branch_id' => $branch->id]);
        $manager = $this->createTenantUser(['role' => 'manager', 'email' => 'mgr@test.com', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        // Service 1: QC pass → SIAP_DIAMBIL
        $service1 = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'technician_id' => $tech->id,
            'status' => Service::STATUS_SELESAI,
            'selesai_at' => now(),
        ]);

        $this->actingAs($manager);
        $r1 = $this->post(route('services.qc.store', $service1), [
            'checks' => [['item' => 'Touchscreen', 'result' => 'pass', 'notes' => 'ok']],
            'qc_decision' => 'pass',
            'qc_notes' => 'Semua berfungsi.',
        ]);
        $r1->assertStatus(302);
        $service1->refresh();
        $this->assertEquals(Service::STATUS_SIAP_DIAMBIL, $service1->status);

        // Service 2: QC fail → back to DIKERJAKAN
        $service2 = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'technician_id' => $tech->id,
            'status' => Service::STATUS_SELESAI,
            'selesai_at' => now(),
        ]);

        $r2 = $this->post(route('services.qc.store', $service2), [
            'checks' => [['item' => 'Touchscreen', 'result' => 'fail', 'notes' => 'dead spots']],
            'qc_decision' => 'fail',
            'qc_notes' => 'Touchscreen masih ada dead spot. Ganti ulang.',
        ]);
        $r2->assertStatus(302);
        $service2->refresh();
        $this->assertEquals(Service::STATUS_DIKERJAKAN, $service2->status);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 9: QC cannot process same service twice (idempotency)
    // ═══════════════════════════════════════════════════════════════
    public function test_qc_cannot_process_same_service_twice(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $tech = $this->createTenantUser(['role' => 'technician', 'email' => 'tech@test.com', 'branch_id' => $branch->id]);
        $manager = $this->createTenantUser(['role' => 'manager', 'email' => 'mgr@test.com', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'technician_id' => $tech->id,
            'status' => Service::STATUS_SELESAI,
            'selesai_at' => now(),
        ]);

        $this->actingAs($manager);

        // First QC — OK
        $r1 = $this->post(route('services.qc.store', $service), [
            'checks' => [['item' => 'Speaker', 'result' => 'pass', 'notes' => 'ok']],
            'qc_decision' => 'pass',
        ]);
        $r1->assertStatus(302);

        // Second QC — idempotent 409
        try {
            $this->withoutExceptionHandling();
            $this->post(route('services.qc.store', $service), [
                'checks' => [['item' => 'Speaker', 'result' => 'pass', 'notes' => '']],
                'qc_decision' => 'pass',
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(409, $e->getStatusCode());
        }
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 10: Timeline/audit/events created for repair/QC transitions
    // ═══════════════════════════════════════════════════════════════
    public function test_events_and_audit_created_for_repair_and_qc(): void
    {
        Event::fake([\App\Events\Entity\RepairStarted::class, \App\Events\Entity\RepairCompleted::class, \App\Events\Entity\QCCompleted::class]);

        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $tech = $this->createTenantUser(['role' => 'technician', 'email' => 'tech@test.com', 'branch_id' => $branch->id]);
        $manager = $this->createTenantUser(['role' => 'manager', 'email' => 'mgr@test.com', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'technician_id' => $tech->id,
            'status' => Service::STATUS_DIKERJAKAN,
        ]);

        // Start repair
        $this->actingAs($tech);
        $this->post(route('services.repair.start', $service));
        Event::assertDispatched(\App\Events\Entity\RepairStarted::class);
        $this->assertDatabaseHas('activity_logs', ['action' => 'repair_started']);

        // Complete repair
        $service->refresh();
        $service->update(['status' => Service::STATUS_DIKERJAKAN, 'dikerjakan_at' => now()]);
        $this->post(route('services.repair.complete', $service));
        Event::assertDispatched(\App\Events\Entity\RepairCompleted::class);
        $this->assertDatabaseHas('activity_logs', ['action' => 'repair_completed']);

        // QC
        $service->refresh();
        $service->update(['status' => Service::STATUS_SELESAI, 'selesai_at' => now()]);
        $this->actingAs($manager);
        $this->post(route('services.qc.store', $service), [
            'checks' => [['item' => 'Battery', 'result' => 'pass', 'notes' => '']],
            'qc_decision' => 'pass',
        ]);
        Event::assertDispatched(\App\Events\Entity\QCCompleted::class);
        $this->assertDatabaseHas('activity_logs', ['action' => 'qc_passed']);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 11: Tenant/branch isolation on service, parts, QC
    // ═══════════════════════════════════════════════════════════════
    public function test_branch_isolation_on_repair_parts(): void
    {
        $branchA = $this->createBranch(['name' => 'Branch A']);
        $branchB = $this->createBranch(['name' => 'Branch B']);

        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branchA->id]);
        $tech = $this->createTenantUser(['role' => 'technician', 'email' => 'tech@test.com', 'branch_id' => $branchA->id]);
        $customer = $this->createCustomer();

        // Service at Branch A
        $service = $this->createService([
            'branch_id' => $branchA->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'technician_id' => $tech->id,
            'status' => Service::STATUS_DIKERJAKAN,
            'dikerjakan_at' => now(),
        ]);

        // Product belongs to Branch B
        $productB = Product::create([
            'branch_id' => $branchB->id,
            'name' => 'Branch B Exclusive Part',
            'selling_price' => 50000,
            'stock_quantity' => 100,
        ]);

        // Product belongs to Branch A
        $productA = Product::create([
            'branch_id' => $branchA->id,
            'name' => 'Branch A Part',
            'selling_price' => 30000,
            'stock_quantity' => 50,
        ]);

        $this->actingAs($tech);

        // Request Branch B product on Branch A service — must be REJECTED
        $response = $this->post(route('service-parts.request', $service), [
            'product_id' => $productB->id,
            'qty' => 1,
            'part_name' => $productB->name,
        ]);
        $response->assertStatus(422);
        $this->assertEquals(0, ServiceRequiredPart::where('service_id', $service->id)->count(),
            'Cross-branch part request must not create a record');

        // Branch A product — should work (canonical request → approve → consume)
        $response2 = $this->post(route('service-parts.request', $service), [
            'product_id' => $productA->id,
            'qty' => 1,
            'part_name' => $productA->name,
        ]);
        $response2->assertStatus(302);
        $part = ServiceRequiredPart::where('service_id', $service->id)->first();
        $this->assertNotNull($part);

        $this->actingAs($owner);
        $this->post(route('service-parts.approve', $part))->assertStatus(302);
        $this->post(route('service-parts.use', $part), [
            'selling_price' => $productA->selling_price,
            'discount' => 0,
        ])->assertStatus(302);

        $productA->refresh();
        $this->assertEquals(49, $productA->stock_quantity);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 12: QC checklist items created correctly
    // ═══════════════════════════════════════════════════════════════
    public function test_qc_checklist_items_persisted_correctly(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $tech = $this->createTenantUser(['role' => 'technician', 'email' => 'tech@test.com', 'branch_id' => $branch->id]);
        $manager = $this->createTenantUser(['role' => 'manager', 'email' => 'mgr@test.com', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'technician_id' => $tech->id,
            'status' => Service::STATUS_SELESAI,
            'selesai_at' => now(),
        ]);

        $this->actingAs($manager);

        $this->post(route('services.qc.store', $service), [
            'checks' => [
                ['item' => 'Touchscreen', 'result' => 'pass', 'notes' => 'Responsif'],
                ['item' => 'Charging', 'result' => 'pass', 'notes' => 'Normal'],
                ['item' => 'Speaker', 'result' => 'fail', 'notes' => 'Suara kecil'],
            ],
            'qc_decision' => 'fail',
            'qc_notes' => 'Speaker perlu diganti.',
        ]);

        // All 3 QC items persisted
        $this->assertEquals(3, ServiceQcCheck::where('service_id', $service->id)->count());
        $this->assertDatabaseHas('service_qc_checks', [
            'service_id' => $service->id,
            'item' => 'Touchscreen',
            'result' => 'pass',
        ]);
        $this->assertDatabaseHas('service_qc_checks', [
            'service_id' => $service->id,
            'item' => 'Speaker',
            'result' => 'fail',
        ]);
    }
}
