<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\SaleStoreController;
use App\Models\Tenant\RequestIdempotency;
use App\Models\Tenant\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TenantSaleStoreIdempotencyTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        Queue::fake();
    }

    public function test_paid_sale_with_same_idempotency_key_is_created_once(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);

        $payload = [
            'sale_type' => Sale::SALE_TYPE_LANGSUNG,
            'paid_amount' => 150000,
            'payment_method' => 'cash',
            'idempotency_key' => 'sale-store-kunci-001',
            'items' => [
                [
                    'item_type' => 'jasa',
                    'description' => 'Pasang tempered glass',
                    'quantity' => 1,
                    'price' => 150000,
                ],
            ],
        ];

        $this->actingAs($owner);

        $firstResponse = app(SaleStoreController::class)->store(Request::create('/sales', 'POST', $payload));
        $secondResponse = app(SaleStoreController::class)->store(Request::create('/sales', 'POST', $payload));

        $this->assertEquals(302, $firstResponse->getStatusCode());
        $this->assertEquals(302, $secondResponse->getStatusCode());

        $this->assertEquals(1, Sale::count());

        $sale = Sale::first();
        $this->assertNotNull($sale);
        $this->assertEquals(Sale::STATUS_PAID, $sale->status);

        $this->assertDatabaseHas('request_idempotencies', [
            'key' => 'sale-store-kunci-001',
            'action' => 'sale.store',
            'user_id' => $owner->id,
            'resource_type' => 'sale',
            'resource_id' => (string) $sale->id,
        ]);

        $this->assertEquals(1, RequestIdempotency::count());
    }
}
