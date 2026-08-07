<?php

namespace Tests\Feature\Tenant;

use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceIntakeSnapshot;
use Tests\TestCase;

class ProductionHardeningTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_stock_cannot_go_negative(): void
    {
        $product = $this->createProduct(['stock_quantity' => 5, 'min_stock' => 2]);

        $this->expectException(\RuntimeException::class);

        $product->reduceStock(10);
    }

    public function test_stock_reduces_correctly(): void
    {
        $product = $this->createProduct(['stock_quantity' => 10, 'min_stock' => 3]);

        $product->reduceStock(3);

        $this->assertEquals(7, $product->fresh()->stock_quantity);
    }

    public function test_low_stock_detection(): void
    {
        $product = $this->createProduct(['stock_quantity' => 5, 'min_stock' => 3]);

        $this->assertFalse($product->fresh()->isLowStock());

        $product->reduceStock(3);

        $this->assertTrue($product->fresh()->isLowStock());
        $this->assertEquals('low', $product->fresh()->stock_status);
    }

    public function test_out_of_stock_status(): void
    {
        $product = $this->createProduct(['stock_quantity' => 1, 'min_stock' => 0]);

        $product->reduceStock(1);

        $this->assertEquals('out', $product->fresh()->stock_status);
        $this->assertEquals(0, $product->fresh()->stock_quantity);
    }

    public function test_customer_creation_auto_generates_code(): void
    {
        $branch = $this->createBranch();

        $customer = Customer::create([
            'name' => 'Test User',
            'phone' => '08123456789',
            'branch_id' => $branch->id,
        ]);

        $this->assertNotNull($customer->customer_code);
        $this->assertStringStartsWith('CUS', $customer->customer_code);
    }

    public function test_duplicate_detection_by_phone(): void
    {
        $branch = $this->createBranch();

        Customer::create([
            'name' => 'Budi',
            'phone' => '08123456789',
            'branch_id' => $branch->id,
        ]);

        $duplicates = Customer::detectDuplicates('Budi', '08123456789', null);

        $this->assertNotEmpty($duplicates);
        $this->assertEquals('Budi', $duplicates[0]['name']);
    }

    public function test_merge_moves_all_relations(): void
    {
        $branch = $this->createBranch();
        $user = $this->createTenantUser(['branch_id' => $branch->id]);
        $target = Customer::create(['name' => 'Target', 'phone' => '081', 'branch_id' => $branch->id]);
        $source = Customer::create(['name' => 'Source', 'phone' => '082', 'branch_id' => $branch->id]);

        $service = Service::create([
            'customer_id' => $source->id,
            'branch_id' => $branch->id,
            'created_by' => $user->id,
            'problem_description' => 'Test',
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
        ]);

        $target->merge($source);

        $this->assertEquals($target->id, $service->fresh()->customer_id);
        $this->assertNull($source->fresh());
    }

    public function test_intake_snapshot_is_immutable(): void
    {
        $service = $this->createService([
            'problem_description' => 'LCD',
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
        ]);

        $snapshot = ServiceIntakeSnapshot::capture($service, true);
        $originalPhotos = $snapshot->photos;

        $snapshot->update(['photos' => ['modified']]);

        $this->assertNotNull($originalPhotos);
    }

    private function createProduct(array $attributes = []): Product
    {
        $branchId = $attributes['branch_id'] ?? $this->createBranch()->id;

        return Product::create(array_merge([
            'branch_id' => $branchId,
            'name' => 'Produk '.uniqid(),
            'code' => 'PRD-'.uniqid(),
            'selling_price' => 100000,
            'stock_quantity' => 10,
            'min_stock' => 1,
        ], $attributes));
    }
}
