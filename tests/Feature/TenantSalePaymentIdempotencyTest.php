<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\SalePaymentController;
use App\Models\Tenant\RequestIdempotency;
use App\Models\Tenant\Sale;
use App\Models\Tenant\SaleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TenantSalePaymentIdempotencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        Queue::fake();
    }

    public function test_pay_draft_with_same_idempotency_key_is_processed_once(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'sale_type' => Sale::SALE_TYPE_LANGSUNG,
            'status' => Sale::STATUS_DRAFT,
            'subtotal' => 100000,
            'total' => 100000,
            'payment_method' => 'draft',
            'paid_amount' => 0,
            'change' => 0,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'item_type' => 'jasa',
            'description' => 'Biaya jasa',
            'quantity' => 1,
            'price' => 100000,
            'subtotal' => 100000,
        ]);

        $payload = [
            'paid_amount' => 100000,
            'payment_method' => 'cash',
            'idempotency_key' => 'pay-draft-001',
        ];

        $this->actingAs($owner);

        $firstResponse = app(SalePaymentController::class)->payDraft(
            Request::create('/sales/' . $sale->id . '/pay-draft', 'POST', $payload),
            $sale
        );

        $sale->refresh();

        $secondResponse = app(SalePaymentController::class)->payDraft(
            Request::create('/sales/' . $sale->id . '/pay-draft', 'POST', $payload),
            $sale
        );

        $this->assertEquals(302, $firstResponse->getStatusCode());
        $this->assertEquals(302, $secondResponse->getStatusCode());

        $sale->refresh();
        $this->assertEquals(Sale::STATUS_PAID, $sale->status);

        $this->assertDatabaseHas('request_idempotencies', [
            'key' => 'pay-draft-001',
            'action' => 'sale.pay_draft',
            'user_id' => $owner->id,
            'resource_type' => 'sale',
            'resource_id' => (string) $sale->id,
        ]);

        $this->assertEquals(1, RequestIdempotency::where('action', 'sale.pay_draft')->count());
    }

    public function test_void_with_same_idempotency_key_is_processed_once(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'sale_type' => Sale::SALE_TYPE_LANGSUNG,
            'status' => Sale::STATUS_PAID,
            'subtotal' => 50000,
            'total' => 50000,
            'payment_method' => 'cash',
            'paid_amount' => 50000,
            'change' => 0,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'item_type' => 'jasa',
            'description' => 'Biaya jasa',
            'quantity' => 1,
            'price' => 50000,
            'subtotal' => 50000,
        ]);

        $this->actingAs($owner);

        $this->app->instance('request', Request::create('/sales/' . $sale->id . '/void', 'POST', [
            'idempotency_key' => 'void-sale-001',
        ]));
        $firstResponse = app(SalePaymentController::class)->void($sale);

        $sale->refresh();

        $this->app->instance('request', Request::create('/sales/' . $sale->id . '/void', 'POST', [
            'idempotency_key' => 'void-sale-001',
        ]));
        $secondResponse = app(SalePaymentController::class)->void($sale);

        $this->assertEquals(302, $firstResponse->getStatusCode());
        $this->assertEquals(302, $secondResponse->getStatusCode());

        $sale->refresh();
        $this->assertEquals(Sale::STATUS_CANCEL, $sale->status);

        $this->assertDatabaseHas('request_idempotencies', [
            'key' => 'void-sale-001',
            'action' => 'sale.void',
            'user_id' => $owner->id,
            'resource_type' => 'sale',
            'resource_id' => (string) $sale->id,
        ]);

        $this->assertEquals(1, RequestIdempotency::where('action', 'sale.void')->count());
    }
}
