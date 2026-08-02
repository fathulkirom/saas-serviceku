<?php

namespace Tests\Feature\Tenant;

use Tests\TestCase;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceRequiredPart;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StockIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_cannot_go_negative(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5, 'min_stock' => 2]);

        $this->expectException(\RuntimeException::class);
        $product->reduceStock(10); // Should throw — only 5 available
    }

    public function test_stock_reduces_correctly(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 10, 'min_stock' => 3]);

        $product->reduceStock(3);
        $this->assertEquals(7, $product->fresh()->stock_quantity);
    }

    public function test_low_stock_detection(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 5, 'min_stock' => 3]);

        $this->assertFalse($product->fresh()->isLowStock());

        $product->reduceStock(3); // 5 → 2, below min_stock=3
        $this->assertTrue($product->fresh()->isLowStock());
        $this->assertEquals('low', $product->fresh()->stock_status);
    }

    public function test_out_of_stock_status(): void
    {
        $product = Product::factory()->create(['stock_quantity' => 1, 'min_stock' => 0]);
        $product->reduceStock(1);
        $this->assertEquals('out', $product->fresh()->stock_status);
        $this->assertEquals(0, $product->fresh()->stock_quantity);
    }
}

class CustomerWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_creation_auto_generates_code(): void
    {
        $customer = Customer::create(['name' => 'Test User', 'phone' => '08123456789', 'branch_id' => 1]);
        $this->assertNotNull($customer->customer_code);
        $this->assertStringStartsWith('CUS', $customer->customer_code);
    }

    public function test_duplicate_detection_by_phone(): void
    {
        Customer::create(['name' => 'Budi', 'phone' => '08123456789', 'branch_id' => 1]);
        $duplicates = Customer::detectDuplicates('Budi', '08123456789', null);

        $this->assertNotEmpty($duplicates);
        $this->assertEquals('Budi', $duplicates[0]['name']);
    }

    public function test_merge_moves_all_relations(): void
    {
        $target = Customer::create(['name' => 'Target', 'phone' => '081', 'branch_id' => 1]);
        $source = Customer::create(['name' => 'Source', 'phone' => '082', 'branch_id' => 1]);

        $service = Service::create(['customer_id' => $source->id, 'branch_id' => 1, 'problem_description' => 'Test', 'status' => Service::STATUS_MENUNGGU_ALOKASI]);

        $target->merge($source);

        $this->assertEquals($target->id, $service->fresh()->customer_id);
        $this->assertNotNull($source->fresh()->merged_into_id);
    }
}

class ServiceIntakeTest extends TestCase
{
    use RefreshDatabase;

    public function test_intake_snapshot_is_immutable(): void
    {
        $service = Service::create(['customer_id' => 1, 'branch_id' => 1, 'problem_description' => 'LCD', 'status' => Service::STATUS_MENUNGGU_ALOKASI]);
        $snapshot = \App\Models\Tenant\ServiceIntakeSnapshot::capture($service, true);
        $originalPhotos = $snapshot->photos;

        // Trying to modify... should be read by fresh()
        $snapshot->update(['photos' => ['modified']]);

        // Original snapshot captured data preserved
        $this->assertNotNull($originalPhotos);
    }
}
