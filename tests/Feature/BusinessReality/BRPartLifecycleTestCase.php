<?php

namespace Tests\Feature\BusinessReality;

use Tests\TestCase;
use App\Models\Tenant\Service;
use App\Models\Tenant\User;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use App\Models\Tenant\ServiceRequiredPart;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Shared setup for BR-007 / BR-009 / BR-008 part lifecycle tests.
 *
 * Canonical ServiceKU flow (BR-FIX-01):
 *   TECHNICIAN REQUEST → ADMIN/WAREHOUSE APPROVE → STOCK RESERVED (physical NOT reduced)
 *   → CS CONFIRMS / ADDS PART TO INVOICE → RESERVATION CONSUMED → PHYSICAL STOCK REDUCED
 *   → SERVICE PART USAGE → INVENTORY MUTATION → visible to invoice.
 *
 * If cancelled before consumption → release reservation only.
 * If consumed and legitimately returned → restore stock + reversal mutation.
 */
abstract class BRPartLifecycleTestCase extends TestCase
{
    use RefreshDatabase;

    protected Branch $branch;
    protected User $owner;
    protected User $admin;
    protected User $manager;
    protected User $technician;
    protected User $cs;
    protected Customer $customer;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->grantFullPlanAccess();

        $this->branch = Branch::create(['name' => 'Main Branch', 'is_active' => true]);

        $this->owner = $this->createTenantUser(['name' => 'Owner', 'role' => 'owner', 'branch_id' => $this->branch->id, 'active' => true]);
        $this->admin = $this->createTenantUser(['name' => 'Admin', 'role' => 'admin', 'branch_id' => $this->branch->id, 'active' => true]);
        $this->manager = $this->createTenantUser(['name' => 'Manager', 'role' => 'manager', 'branch_id' => $this->branch->id, 'active' => true]);
        $this->technician = $this->createTenantUser(['name' => 'Teknisi', 'role' => 'technician', 'branch_id' => $this->branch->id, 'active' => true]);
        $this->cs = $this->createTenantUser(['name' => 'CS', 'role' => 'cs', 'branch_id' => $this->branch->id, 'active' => true]);

        $this->customer = Customer::create([
            'name' => 'John Doe',
            'phone' => '081234567890',
            'branch_id' => $this->branch->id,
        ]);

        $this->product = Product::create([
            'name' => 'LCD Screen',
            'branch_id' => $this->branch->id,
            'stock_quantity' => 10,
            'cost_price' => 50000,
            'selling_price' => 100000,
            'min_stock' => 1,
        ]);
    }

    protected function makeService(array $extra = []): Service
    {
        return Service::create(array_merge([
            'branch_id' => $this->branch->id,
            'customer_id' => $this->customer->id,
            'created_by' => $this->owner->id,
            'technician_id' => $this->technician->id,
            'status' => Service::STATUS_DIKERJAKAN,
            'service_charge' => 150000,
            'problem_description' => 'Layar rusak',
        ], $extra));
    }

    /**
     * Technician requests a part (canonical entry point).
     */
    protected function requestPart(Service $service, ?int $productId = null, int $qty = 1, string $partName = 'LCD Screen')
    {
        return $this->post("/services/{$service->id}/parts/request", [
            'product_id' => $productId ?? $this->product->id,
            'part_name' => $partName,
            'qty' => $qty,
            'notes' => 'Perlu ganti',
        ]);
    }

    /**
     * Admin/authorized approves a part request → reservation.
     */
    protected function approvePart(ServiceRequiredPart $part)
    {
        return $this->post("/service-parts/{$part->id}/approve");
    }

    /**
     * CS confirms/consumes an approved part → adds to invoice, deducts stock.
     */
    protected function consumePart(ServiceRequiredPart $part, float $sellingPrice = 100000, float $discount = 0)
    {
        return $this->post("/service-parts/{$part->id}/use", [
            'selling_price' => $sellingPrice,
            'discount' => $discount,
        ]);
    }

    /**
     * Tech/admin cancels a part request.
     */
    protected function cancelPart(ServiceRequiredPart $part, string $reason = 'Tidak jadi dipakai')
    {
        return $this->post("/service-parts/{$part->id}/cancel", ['reason' => $reason]);
    }

    /**
     * Admin rejects a part request (approval path negative outcome).
     */
    protected function rejectPart(ServiceRequiredPart $part, string $reason = 'Ditolak admin')
    {
        return $this->post("/service-parts/{$part->id}/reject", ['reason' => $reason]);
    }

    /**
     * Request a return for a part (consumed or reserved-only).
     */
    protected function requestReturn(Service $service, int $productId, int $qty, ?int $serviceRequiredPartId = null, string $reason = 'Tidak terpakai')
    {
        return $this->post("/services/{$service->id}/parts/return-request", [
            'product_id' => $productId,
            'quantity' => $qty,
            'reason' => $reason,
            'service_required_part_id' => $serviceRequiredPartId,
        ]);
    }

    /**
     * Process a return request.
     */
    protected function processReturn($return)
    {
        return $this->post("/service-part-returns/{$return->id}/process");
    }

    /**
     * Complete repair (work completion only — MUST NOT consume inventory).
     */
    protected function completeRepair(Service $service, array $partsUsed = [])
    {
        return $this->post("/services/{$service->id}/repair/complete", [
            'repair_notes' => 'Selesai dikerjakan',
            'parts_used' => $partsUsed,
        ]);
    }

    /**
     * Create the service draft sale (reads consumed/billable parts only).
     */
    protected function draftFromService(Service $service)
    {
        return $this->post("/sales/draft-from-service/{$service->id}");
    }
}
