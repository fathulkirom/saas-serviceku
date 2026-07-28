<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant\Sale;
use App\Models\Tenant\SaleItem;
use App\Models\Tenant\Product;

class TenantSaleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_can_create_sale()
    {
        $sale = $this->createSale();

        $this->assertNotNull($sale);
        $this->assertEquals(Sale::STATUS_DRAFT, $sale->status);
        $this->assertEquals(100000, $sale->total);
    }

    public function test_can_create_sale_with_items()
    {
        $customer = $this->createCustomer();

        $branch = \App\Models\Tenant\Branch::create(['name' => 'Cabang', 'is_active' => true]);
        $product = Product::create([
            'name' => 'Test Product',
            'branch_id' => $branch->id,
            'selling_price' => 25000,
            'stock_quantity' => 10,
        ]);

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
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
            'product_id' => $product->id,
            'product_name' => $product->name,
            'item_type' => 'sparepart',
            'quantity' => 2,
            'unit_price' => 25000,
            'subtotal' => 50000,
        ]);

        $sale->load('items');
        $this->assertCount(1, $sale->items);
        $this->assertEquals(50000, $sale->items->first()->subtotal);
    }

    public function test_sale_can_be_voided()
    {
        $sale = $this->createSale();

        $sale->update(['status' => Sale::STATUS_CANCEL]);

        $this->assertEquals(Sale::STATUS_CANCEL, $sale->fresh()->status);
    }

    public function test_sale_tracks_payment()
    {
        $sale = $this->createSale();
        $total = $sale->total;

        $sale->update([
            'status' => Sale::STATUS_PAID,
            'payment_method' => 'cash',
            'paid_amount' => $total,
            'change' => 0,
        ]);

        $fresh = $sale->fresh();
        $this->assertEquals(Sale::STATUS_PAID, $fresh->status);
        $this->assertEquals($total, $fresh->paid_amount);
    }
}
