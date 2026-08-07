<?php

namespace Tests\Feature;

use App\Models\Tenant\Product;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceQuotation;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * Phase 2F — Correct Stock Semantics & Obtain Valid Test Evidence
 * 
 * ALL tests use real HTTP routes (actingAs + post/get), not controller calls.
 * Tests cover:
 * 1. Branch-scoped product validation (cross-branch rejection)
 * 2. Backend pricing authority (frontend price manipulation ignored)
 * 3. Stock NOT deducted at quotation (no reservation system)
 * 4. Customer Portal auth separation (customer ownership)
 * 5. Internal role gate
 * 6. Event dispatch verification
 * 7. HTTP idempotency (repeated requests)
 */
class TenantTechnicianWorkflowPhase2FTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 1: Branch-scoped product — cross-branch product REJECTED
    // ═══════════════════════════════════════════════════════════════
    public function test_create_quotation_rejects_product_from_different_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Branch A']);
        $branchB = $this->createBranch(['name' => 'Branch B']);

        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branchA->id]);
        $customer = $this->createCustomer();

        // Service is at Branch A
        $service = $this->createService([
            'branch_id' => $branchA->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_DIKERJAKAN,
        ]);

        // Product belongs to Branch B (different branch)
        $product = Product::create([
            'branch_id' => $branchB->id,
            'name' => 'Branch B Only Item',
            'selling_price' => 100000,
            'stock_quantity' => 10,
        ]);

        $this->actingAs($owner);

        $response = $this->post(route('services.quotation.create', $service), [
            'items' => [['product_id' => $product->id, 'qty' => 1]],
            'labor_cost' => 0,
        ]);

        // Must reject — product belongs to different branch
        $response->assertStatus(422);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 2: Global product (branch_id=null) ACCEPTED for any branch
    // ═══════════════════════════════════════════════════════════════
    public function test_create_quotation_accepts_global_product_for_any_branch(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_DIKERJAKAN,
        ]);

        // Global product — no branch restriction
        $product = Product::create([
            'branch_id' => null,
            'name' => 'Universal Charger',
            'selling_price' => 50000,
            'stock_quantity' => 20,
        ]);

        $this->actingAs($owner);

        $response = $this->post(route('services.quotation.create', $service), [
            'items' => [['product_id' => $product->id, 'qty' => 2]],
            'labor_cost' => 0,
        ]);

        // Should succeed — global product accessible by any branch
        $response->assertStatus(302); // back() redirect on success
        $this->assertDatabaseHas('service_quotations', ['service_id' => $service->id, 'status' => 'sent']);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 3: Stock NOT deducted at quotation (no reservation)
    // ═══════════════════════════════════════════════════════════════
    public function test_create_quotation_does_not_deduct_stock(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_DIKERJAKAN,
        ]);

        $product = Product::create([
            'branch_id' => $branch->id,
            'name' => 'LCD Cable',
            'selling_price' => 30000,
            'stock_quantity' => 10,
        ]);

        $stockBefore = $product->stock_quantity;

        $this->actingAs($owner);

        $this->post(route('services.quotation.create', $service), [
            'items' => [['product_id' => $product->id, 'qty' => 3]],
            'labor_cost' => 50000,
        ]);

        // Stock must remain UNCHANGED — quotation does NOT reserve
        $product->refresh();
        $this->assertEquals($stockBefore, $product->stock_quantity,
            'Stock MUST NOT be deducted at quotation stage (no reservation system).');

        // But quotation items should record stock_before for audit
        $quotation = ServiceQuotation::where('service_id', $service->id)->first();
        $this->assertNotNull($quotation, 'Quotation should have been created');
        $items = $quotation->items;
        $this->assertEquals(10, $items[0]['stock_before']);
        $this->assertArrayHasKey('stock_checked_at', $items[0]);
        $this->assertArrayHasKey('_disclaimer', $items[0]);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 4: Backend pricing authority — frontend price IGNORED
    // ═══════════════════════════════════════════════════════════════
    public function test_create_quotation_ignores_frontend_price_manipulation(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_DIKERJAKAN,
        ]);

        // Backend price: Rp 100.000
        $product = Product::create([
            'branch_id' => $branch->id,
            'name' => 'Battery Pack',
            'selling_price' => 100000,
            'stock_quantity' => 10,
        ]);

        $this->actingAs($owner);

        // Frontend sends manipulated prices — backend MUST ignore them
        $response = $this->post(route('services.quotation.create', $service), [
            'items' => [
                [
                    'product_id' => $product->id,
                    'qty' => 2,
                    'price' => 5000,       // ← MANIPULATED (real price is 100000)
                    'line_total' => 10000, // ← MANIPULATED
                ],
            ],
            'labor_cost' => 50000,
            'total_cost' => 60000, // ← MANIPULATED (should be 2×100000 + 50000 = 250000)
        ]);

        $quotation = ServiceQuotation::where('service_id', $service->id)->first();
        $this->assertNotNull($quotation);
        // Real total: 2 × 100000 + 50000 = 250000
        $this->assertEquals(250000, (float) $quotation->total_cost,
            'Backend must use selling_price from Inventory, not frontend price.');

        $items = $quotation->items;
        $this->assertEquals(100000, $items[0]['price'], 'Item price must come from Product model.');
        $this->assertEquals(200000, $items[0]['line_total'], 'Line total must be qty × backend selling_price.');
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 5: Customer Portal approve — owner customer ALLOWED
    // ═══════════════════════════════════════════════════════════════
    public function test_approve_quotation_customer_portal_allows_owner_customer(): void
    {
        Event::fake([\App\Events\Entity\CustomerApprovedRepair::class]);

        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);

        $customerEmail = 'realcustomer@test.com';
        $customerPhone = '08123456789';
        $customer = $this->createCustomer([
            'name' => 'Real Customer',
            'email' => $customerEmail,
            'phone' => $customerPhone,
        ]);

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_KONFIRMASI_PELANGGAN,
        ]);

        $quotation = ServiceQuotation::create([
            'service_id' => $service->id,
            'total_cost' => 200000,
            'items' => json_encode([]),
            'status' => 'sent',
            'created_by' => $owner->id,
        ]);

        // The real customer logs in (matched by email/phone in CustomerPortalController)
        $customerUser = $this->createTenantUser([
            'name' => 'Real Customer',
            'email' => $customerEmail,
            'phone' => $customerPhone,
            'role' => 'cs', // Role doesn't matter for customer_portal method — identity matters
        ]);

        $this->actingAs($customerUser);

        $response = $this->post(route('quotations.approve', $quotation), [
            'method' => 'customer_portal',
        ]);

        $response->assertStatus(302); // back() redirect

        $quotation->refresh();
        $this->assertEquals('approved', $quotation->status);

        Event::assertDispatched(\App\Events\Entity\CustomerApprovedRepair::class);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 6: Customer Portal approve — WRONG customer REJECTED
    // ═══════════════════════════════════════════════════════════════
    public function test_approve_quotation_customer_portal_rejects_wrong_customer(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);

        $customer1 = $this->createCustomer([
            'name' => 'Alice', 'email' => 'alice@test.com', 'phone' => '0811111111',
        ]);
        $customer2 = $this->createCustomer([
            'name' => 'Bob', 'email' => 'bob@test.com', 'phone' => '0822222222',
        ]);

        // Service belongs to Alice
        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer1->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_KONFIRMASI_PELANGGAN,
        ]);

        $quotation = ServiceQuotation::create([
            'service_id' => $service->id,
            'total_cost' => 100000,
            'items' => json_encode([]),
            'status' => 'sent',
            'created_by' => $owner->id,
        ]);

        // Bob logs in — he should NOT be able to approve Alice's quotation
        $bobUser = $this->createTenantUser([
            'name' => 'Bob', 'email' => 'bob@test.com', 'phone' => '0822222222',
            'role' => 'cs',
        ]);

        $this->actingAs($bobUser);

        // The abort(403) renders error view which crashes in test — but auth logic still fires.
        // Verify by checking quotation status unchanged (approval was blocked).
        try {
            $this->withoutExceptionHandling();
            $this->post(route('quotations.approve', $quotation), [
                'method' => 'customer_portal',
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            // Expected: 403 from abort()
            $this->assertEquals(403, $e->getStatusCode());
        }

        $quotation->refresh();
        $this->assertEquals('sent', $quotation->status, 'Status must remain sent — wrong customer rejected.');
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 7: Internal approve — technician WITHOUT proper role REJECTED
    // ═══════════════════════════════════════════════════════════════
    public function test_approve_quotation_internal_rejects_unauthorized_technician(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_KONFIRMASI_PELANGGAN,
        ]);

        $quotation = ServiceQuotation::create([
            'service_id' => $service->id,
            'total_cost' => 100000,
            'items' => json_encode([]),
            'status' => 'sent',
            'created_by' => $owner->id,
        ]);

        // Technician tries internal (cs) approval — should be REJECTED
        $tech = $this->createTenantUser(['role' => 'technician', 'email' => 'tech@test.com']);

        $this->actingAs($tech);

        try {
            $this->withoutExceptionHandling();
            $this->post(route('quotations.approve', $quotation), [
                'method' => 'cs',
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException|\Illuminate\Auth\Access\AuthorizationException $e) {
            $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 403;
            $this->assertContains($statusCode, [403, 0], 'Expected authorization failure');
        }

        $quotation->refresh();
        $this->assertEquals('sent', $quotation->status);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 8: Technician cannot bypass auth via method=customer_portal
    // ═══════════════════════════════════════════════════════════════
    public function test_technician_cannot_bypass_auth_with_customer_portal_method(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);

        $customer = $this->createCustomer([
            'name' => 'Eve', 'email' => 'eve@test.com', 'phone' => '0833333333',
        ]);

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_KONFIRMASI_PELANGGAN,
        ]);

        $quotation = ServiceQuotation::create([
            'service_id' => $service->id,
            'total_cost' => 100000,
            'items' => json_encode([]),
            'status' => 'sent',
            'created_by' => $owner->id,
        ]);

        // A random user with different email/phone tries customer_portal method
        $attacker = $this->createTenantUser([
            'name' => 'Attacker',
            'email' => 'attacker@test.com',
            'phone' => '0899999999',
            'role' => 'technician',
        ]);

        $this->actingAs($attacker);

        try {
            $this->withoutExceptionHandling();
            $this->post(route('quotations.approve', $quotation), [
                'method' => 'customer_portal', // Trying to bypass with customer_portal
            ]);
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }

        $quotation->refresh();
        $this->assertEquals('sent', $quotation->status);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 9: Reject quotation dispatches QuotationRejected event
    // ═══════════════════════════════════════════════════════════════
    public function test_reject_quotation_dispatches_quotation_rejected_event(): void
    {
        Event::fake([\App\Events\Entity\QuotationRejected::class]);

        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_KONFIRMASI_PELANGGAN,
        ]);

        $quotation = ServiceQuotation::create([
            'service_id' => $service->id,
            'total_cost' => 100000,
            'items' => json_encode([]),
            'status' => 'sent',
            'created_by' => $owner->id,
        ]);

        $this->actingAs($owner);

        $response = $this->post(route('quotations.reject', $quotation), [
            'method' => 'cs',
            'reason' => 'Customer tidak setuju dengan harga.',
        ]);

        $response->assertStatus(302);

        $quotation->refresh();
        $this->assertEquals('rejected', $quotation->status);

        Event::assertDispatched(\App\Events\Entity\QuotationRejected::class, function ($event) use ($quotation) {
            return $event->quotation->id === $quotation->id;
        });
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 10: Duplicate approve/reject idempotent (repeated HTTP requests)
    // ═══════════════════════════════════════════════════════════════
    public function test_duplicate_approve_is_idempotent(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_KONFIRMASI_PELANGGAN,
        ]);

        $quotation = ServiceQuotation::create([
            'service_id' => $service->id,
            'total_cost' => 100000,
            'items' => json_encode([]),
            'status' => 'sent',
            'created_by' => $owner->id,
        ]);

        $this->actingAs($owner);

        // First approval — OK
        $r1 = $this->post(route('quotations.approve', $quotation), ['method' => 'cs']);
        $r1->assertStatus(302);

        $quotation->refresh();
        $this->assertEquals('approved', $quotation->status);

        // Second approval — idempotent 409
        $r2 = $this->post(route('quotations.approve', $quotation), ['method' => 'cs']);
        $r2->assertStatus(409);

        // Status unchanged
        $quotation->refresh();
        $this->assertEquals('approved', $quotation->status);
    }

    // ═══════════════════════════════════════════════════════════════
    // TEST 11: Quotation items contain disclaimer (stock not reserved)
    // ═══════════════════════════════════════════════════════════════
    public function test_quotation_items_contain_stock_disclaimer(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_DIKERJAKAN,
        ]);

        $product = Product::create([
            'branch_id' => $branch->id,
            'name' => 'LCD Panel',
            'selling_price' => 200000,
            'stock_quantity' => 5,
        ]);

        $this->actingAs($owner);

        $this->post(route('services.quotation.create', $service), [
            'items' => [['product_id' => $product->id, 'qty' => 1]],
            'labor_cost' => 0,
        ]);

        $quotation = ServiceQuotation::where('service_id', $service->id)->first();
        $this->assertNotNull($quotation, 'Quotation should have been created');
        $items = $quotation->items;
        $this->assertIsArray($items);
        $this->assertArrayHasKey('_disclaimer', $items[0]);
        $this->assertStringContainsString('Stok diperiksa', $items[0]['_disclaimer']);
        $this->assertArrayHasKey('stock_checked_at', $items[0]);

        // Notes also contain disclaimer
        $this->assertStringContainsString('Stok diperiksa', $quotation->notes);
    }
}
