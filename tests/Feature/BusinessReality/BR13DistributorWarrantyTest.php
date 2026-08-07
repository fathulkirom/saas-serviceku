<?php

namespace Tests\Feature\BusinessReality;

use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceSparepart;
use App\Models\Tenant\ServiceWarranty;
use App\Models\Tenant\Supplier;
use App\Services\WarrantyService;
use Illuminate\Support\Facades\Schema;

/**
 * BR-013 — DISTRIBUTOR / SUPPLIER WARRANTY.
 *
 * Store warranty and upstream supplier/distributor warranty are DISTINCT and
 * truthful. Store expiry must not falsely mark upstream warranty expired, and
 * a lifetime upstream warranty is represented explicitly (no magic far-future
 * date). STEP 26 test matrix (7 tests).
 */
class BR13DistributorWarrantyTest extends BRWarrantyTestCase
{
    private function makeSupplier(string $name = 'PT Distributor Utama'): Supplier
    {
        return Supplier::create(['name' => $name]);
    }

    /**
     * Service with an installed part carrying an upstream supplier warranty.
     * $storeExpired controls the STORE warranty window.
     */
    private function makeServiceWithPart(bool $storeExpired, bool $lifetime = false, ?int $supplierDays = null, ?int $supplierId = null): Service
    {
        $supplier = $supplierId ? Supplier::find($supplierId) : $this->makeSupplier();
        $product = $this->makeProduct($this->branchA, 'Mainboard');
        $service = $this->makeClosedPaidService($this->branchA, 30);
        $service->update(['warranty_expired_at' => $storeExpired ? now()->subDay() : now()->addDays(30)]);

        if ($storeExpired) {
            ServiceWarranty::where('service_id', $service->id)->update([
                'end_date' => now()->subDay()->toDateString(),
                'status' => 'expired',
            ]);
        }

        ServiceSparepart::create([
            'service_id' => $service->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 25000,
            'subtotal' => 25000,
            'supplier_id' => $supplier->id,
            'supplier_warranty_days' => $lifetime ? null : $supplierDays,
            'supplier_warranty_lifetime' => $lifetime,
        ]);

        return $service;
    }

    // 27. Store warranty and supplier warranty are distinguishable.
    public function test_store_and_supplier_warranty_distinguishable(): void
    {
        $service = $this->makeServiceWithPart(storeExpired: false, supplierDays: 365);
        $part = $service->spareparts()->first();

        // Store warranty = ServiceWarranty row; upstream = part supplier fields.
        $store = WarrantyService::storeWarrantyFor($service);
        $this->assertNotNull($store);
        $this->assertSame('service', $store->warranty_type);

        $this->assertTrue((bool) $part->supplier_warranty_days);
        $this->assertNotNull($part->supplier_id);

        // Distinct query surfaces.
        $upstream = WarrantyService::upstreamWarrantyFor($service);
        $this->assertCount(1, $upstream);
        $this->assertSame('duration', $upstream[0]['warranty_type']);
    }

    // 28. Store expiry does not mark upstream warranty expired automatically.
    public function test_store_expiry_does_not_mark_upstream_expired(): void
    {
        $service = $this->makeServiceWithPart(storeExpired: true, supplierDays: 365);
        $part = $service->spareparts()->first();

        // Store warranty expired...
        $this->assertFalse(WarrantyService::isEligibleForStoreWarranty($service));
        // ...but upstream still active.
        $this->assertTrue(WarrantyService::isUpstreamWarrantyActive($part));
    }

    // 29. Active upstream warranty is queryable after store warranty expiry.
    public function test_active_upstream_queryable_after_store_expiry(): void
    {
        $service = $this->makeServiceWithPart(storeExpired: true, supplierDays: 365);

        $upstream = WarrantyService::upstreamWarrantyFor($service);
        $this->assertCount(1, $upstream);
        $this->assertSame('active', $upstream[0]['status']);
        $this->assertSame('duration', $upstream[0]['warranty_type']);
    }

    // 30. Lifetime upstream warranty represented correctly.
    public function test_lifetime_upstream_warranty_represented(): void
    {
        $service = $this->makeServiceWithPart(storeExpired: true, lifetime: true);
        $part = $service->spareparts()->first();

        $this->assertTrue((bool) $part->supplier_warranty_lifetime);
        $this->assertTrue(WarrantyService::isUpstreamWarrantyActive($part));

        $upstream = WarrantyService::upstreamWarrantyFor($service);
        $this->assertSame('lifetime', $upstream[0]['warranty_type']);
        $this->assertSame('active', $upstream[0]['status']);
    }

    // 31. Upstream warranty links back to installed part/supplier where data exists.
    public function test_upstream_links_to_part_and_supplier(): void
    {
        $supplier = $this->makeSupplier('PT Garansi Distributor');
        $service = $this->makeServiceWithPart(storeExpired: true, supplierDays: 365, supplierId: $supplier->id);
        $part = $service->spareparts()->first();

        $upstream = WarrantyService::upstreamWarrantyFor($service);
        $this->assertSame($part->id, $upstream[0]['part_id']);
        $this->assertSame($supplier->id, $upstream[0]['supplier_id']);
        $this->assertSame('PT Garansi Distributor', $upstream[0]['supplier_name']);
    }

    // 32. Customer-facing store warranty remains truthful.
    public function test_store_warranty_remains_truthful(): void
    {
        $service = $this->makeServiceWithPart(storeExpired: true, supplierDays: 365);

        // Even with active upstream, the STORE warranty is genuinely expired.
        $this->assertFalse(WarrantyService::isEligibleForStoreWarranty($service));
        $store = WarrantyService::storeWarrantyFor($service);
        $this->assertNotNull($store);
        $this->assertNotSame('active', $store->status);
    }

    // 33. No cross-tenant supplier warranty leakage.
    public function test_no_cross_tenant_supplier_warranty_leakage(): void
    {
        // Upstream warranty data lives only on tenant service_spareparts rows.
        $this->assertTrue(Schema::connection('tenant')->hasColumn('service_spareparts', 'supplier_warranty_days'));
        $this->assertTrue(Schema::connection('tenant')->hasColumn('service_spareparts', 'supplier_warranty_lifetime'));
        // Central has no service_spareparts (per-tenant).
        $this->assertFalse(Schema::connection('central')->hasTable('service_spareparts'));
    }
}

