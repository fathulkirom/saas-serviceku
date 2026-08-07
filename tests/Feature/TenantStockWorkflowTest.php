<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\SalePaymentController;
use App\Http\Controllers\Tenant\ServiceDocumentController;
use App\Models\Tenant\InventoryMutation;
use App\Models\Tenant\Product;
use App\Models\Tenant\Sale;
use App\Models\Tenant\SaleItem;
use App\Models\Tenant\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class TenantStockWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        Queue::fake();
    }

    public function test_service_complete_reduces_stock_and_creates_mutation(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_SELESAI,
        ]);

        $product = Product::create([
            'branch_id' => $branch->id,
            'name' => 'IC Charger',
            'selling_price' => 100000,
            'stock_quantity' => 10,
            'min_stock' => 1,
        ]);

        $this->actingAs($owner);

        $request = Request::create('/services/'.$service->id.'/complete', 'POST', [
            'spareparts' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'service_charge' => 50000,
        ]);

        $response = app(ServiceDocumentController::class)->complete($request, $service);

        $this->assertEquals(302, $response->getStatusCode());

        $product->refresh();
        $this->assertEquals(8, $product->stock_quantity);

        $this->assertDatabaseHas('service_spareparts', [
            'service_id' => $service->id,
            'product_id' => $product->id,
            'quantity' => 2,
        ]);

        $this->assertDatabaseHas('inventory_mutations', [
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'type' => 'keluar',
            'reference_type' => 'service',
            'reference_id' => (string) $service->id,
        ]);
    }

    public function test_service_complete_second_time_rolls_back_previous_spareparts_before_reapply(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $service = $this->createService([
            'branch_id' => $branch->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_SELESAI,
        ]);

        $product = Product::create([
            'branch_id' => $branch->id,
            'name' => 'LCD OEM',
            'selling_price' => 200000,
            'stock_quantity' => 10,
            'min_stock' => 1,
        ]);

        $this->actingAs($owner);

        $firstRequest = Request::create('/services/'.$service->id.'/complete', 'POST', [
            'spareparts' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
            'service_charge' => 30000,
        ]);
        app(ServiceDocumentController::class)->complete($firstRequest, $service);

        $secondRequest = Request::create('/services/'.$service->id.'/complete', 'POST', [
            'spareparts' => [
                ['product_id' => $product->id, 'quantity' => 3],
            ],
            'service_charge' => 30000,
        ]);
        app(ServiceDocumentController::class)->complete($secondRequest, $service);

        $product->refresh();
        // stok: 10 -2 +2 -3 = 7
        $this->assertEquals(7, $product->stock_quantity);

        $this->assertEquals(1, $service->spareparts()->count());
        $this->assertDatabaseHas('service_spareparts', [
            'service_id' => $service->id,
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $this->assertEquals(
            3,
            InventoryMutation::where('product_id', $product->id)
                ->where('reference_id', (string) $service->id)
                ->count()
        );

        $this->assertDatabaseHas('inventory_mutations', [
            'product_id' => $product->id,
            'type' => 'masuk',
            'reference_type' => 'service_adjustment',
            'reference_id' => (string) $service->id,
        ]);
    }

    public function test_void_sale_restores_stock_and_writes_valid_mutation_fields(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);

        $product = Product::create([
            'branch_id' => $branch->id,
            'name' => 'Baterai Original',
            'selling_price' => 250000,
            'stock_quantity' => 3,
            'min_stock' => 1,
        ]);

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'sale_type' => Sale::SALE_TYPE_LANGSUNG,
            'status' => Sale::STATUS_PAID,
            'subtotal' => 500000,
            'total' => 500000,
            'payment_method' => 'cash',
            'paid_amount' => 500000,
            'change' => 0,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'item_type' => 'sparepart',
            'description' => 'Baterai Original',
            'quantity' => 2,
            'price' => 250000,
            'subtotal' => 500000,
        ]);

        $this->actingAs($owner);

        $response = app(SalePaymentController::class)->void($sale);

        $this->assertEquals(302, $response->getStatusCode());

        $sale->refresh();
        $product->refresh();

        $this->assertEquals(Sale::STATUS_CANCEL, $sale->status);
        $this->assertEquals(5, $product->stock_quantity);

        $this->assertDatabaseHas('inventory_mutations', [
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'type' => 'masuk',
            'reference_type' => 'void_sale',
            'reference_id' => (string) $sale->id,
        ]);
    }

    public function test_pay_draft_for_direct_sale_reduces_stock_and_creates_sale_mutation(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);

        $product = Product::create([
            'branch_id' => $branch->id,
            'name' => 'Backdoor Case',
            'selling_price' => 150000,
            'stock_quantity' => 5,
            'min_stock' => 1,
        ]);

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'sale_type' => Sale::SALE_TYPE_LANGSUNG,
            'status' => Sale::STATUS_DRAFT,
            'subtotal' => 300000,
            'total' => 300000,
            'payment_method' => 'draft',
            'paid_amount' => 0,
            'change' => 0,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'item_type' => 'sparepart',
            'description' => 'Backdoor Case',
            'quantity' => 2,
            'price' => 150000,
            'subtotal' => 300000,
        ]);

        $this->actingAs($owner);
        $request = Request::create('/sales/'.$sale->id.'/pay-draft', 'POST', [
            'paid_amount' => 300000,
            'payment_method' => 'cash',
        ]);

        $response = app(SalePaymentController::class)->payDraft($request, $sale);

        $this->assertEquals(302, $response->getStatusCode());

        $sale->refresh();
        $product->refresh();

        $this->assertEquals(Sale::STATUS_PAID, $sale->status);
        $this->assertEquals(3, $product->stock_quantity);

        $this->assertDatabaseHas('inventory_mutations', [
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'type' => 'keluar',
            'reference_type' => 'sale',
            'reference_id' => (string) $sale->id,
        ]);
    }

    public function test_pay_draft_for_service_linked_sale_does_not_deduct_stock_again(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);

        $service = $this->createService([
            'branch_id' => $branch->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_SELESAI,
        ]);

        $product = Product::create([
            'branch_id' => $branch->id,
            'name' => 'Flex Cable',
            'selling_price' => 120000,
            'stock_quantity' => 5,
            'min_stock' => 1,
        ]);

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'sale_type' => Sale::SALE_TYPE_SERVIS,
            'status' => Sale::STATUS_DRAFT,
            'service_id' => $service->id,
            'subtotal' => 240000,
            'total' => 240000,
            'payment_method' => 'draft',
            'paid_amount' => 0,
            'change' => 0,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'item_type' => 'sparepart',
            'description' => 'Flex Cable',
            'quantity' => 2,
            'price' => 120000,
            'subtotal' => 240000,
        ]);

        $this->actingAs($owner);
        $request = Request::create('/sales/'.$sale->id.'/pay-draft', 'POST', [
            'paid_amount' => 240000,
            'payment_method' => 'cash',
        ]);

        $response = app(SalePaymentController::class)->payDraft($request, $sale);

        $this->assertEquals(302, $response->getStatusCode());

        $sale->refresh();
        $product->refresh();

        $this->assertEquals(Sale::STATUS_PAID, $sale->status);
        $this->assertEquals(5, $product->stock_quantity);

        $this->assertDatabaseMissing('inventory_mutations', [
            'product_id' => $product->id,
            'type' => 'keluar',
            'reference_type' => 'sale',
            'reference_id' => (string) $sale->id,
        ]);
    }

    public function test_void_service_linked_sale_does_not_restore_stock_again(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);

        $service = $this->createService([
            'branch_id' => $branch->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_SELESAI,
        ]);

        $product = Product::create([
            'branch_id' => $branch->id,
            'name' => 'Konektor Charger',
            'selling_price' => 110000,
            'stock_quantity' => 5,
            'min_stock' => 1,
        ]);

        $sale = Sale::create([
            'branch_id' => $branch->id,
            'sale_type' => Sale::SALE_TYPE_SERVIS,
            'status' => Sale::STATUS_PAID,
            'service_id' => $service->id,
            'subtotal' => 220000,
            'total' => 220000,
            'payment_method' => 'cash',
            'paid_amount' => 220000,
            'change' => 0,
        ]);

        SaleItem::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'item_type' => 'sparepart',
            'description' => 'Konektor Charger',
            'quantity' => 2,
            'price' => 110000,
            'subtotal' => 220000,
        ]);

        $this->actingAs($owner);
        $response = app(SalePaymentController::class)->void($sale);

        $this->assertEquals(302, $response->getStatusCode());

        $sale->refresh();
        $product->refresh();

        $this->assertEquals(Sale::STATUS_CANCEL, $sale->status);
        $this->assertEquals(5, $product->stock_quantity);

        $this->assertDatabaseMissing('inventory_mutations', [
            'product_id' => $product->id,
            'type' => 'masuk',
            'reference_type' => 'void_sale',
            'reference_id' => (string) $sale->id,
        ]);
    }
}
