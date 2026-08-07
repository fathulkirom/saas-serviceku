<?php

namespace Tests\Feature\BusinessReality;

use Tests\TestCase;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Device;
use App\Models\Tenant\Product;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceWarranty;
use App\Models\Tenant\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Shared setup for BR-011 (Warranty Repair Return / Rework),
 * BR-012 (Warranty Refund) and BR-013 (Distributor/Supplier Warranty).
 *
 * Fixtures:
 *   - Branch A (owner, CS, technician primary), Branch B.
 *   - manager: branches A + B (legit cross-branch handling).
 *   - owner/csA/techA at branch A.
 */
abstract class BRWarrantyTestCase extends TestCase
{
    use RefreshDatabase;

    protected Branch $branchA;
    protected Branch $branchB;

    protected User $owner;
    protected User $manager;
    protected User $csA;
    protected User $techA;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->grantFullPlanAccess();

        $this->branchA = Branch::create(['name' => 'Cabang A', 'is_active' => true]);
        $this->branchB = Branch::create(['name' => 'Cabang B', 'is_active' => true]);

        $this->owner = $this->createTenantUser(['name' => 'Owner', 'role' => 'owner', 'branch_id' => $this->branchA->id, 'active' => true]);
        $this->manager = $this->createTenantUser(['name' => 'Manager', 'role' => 'manager', 'branch_id' => $this->branchA->id, 'active' => true]);
        $this->manager->branches()->sync([$this->branchB->id]);
        $this->manager->clearBranchAccessCache();

        $this->csA = $this->createTenantUser(['name' => 'CS A', 'role' => 'cs', 'branch_id' => $this->branchA->id, 'active' => true]);
        $this->techA = $this->createTenantUser(['name' => 'Tech A', 'role' => 'technician', 'branch_id' => $this->branchA->id, 'active' => true]);
    }

    protected function makeCustomer(Branch $branch): Customer
    {
        return Customer::create([
            'name' => 'Customer ' . $branch->name,
            'phone' => '08' . random_int(100000000, 999999999),
            'branch_id' => $branch->id,
        ]);
    }

    protected function makeDevice(Customer $customer, string $brand = 'Samsung', string $model = 'S23'): Device
    {
        return Device::create([
            'customer_id' => $customer->id,
            'brand' => $brand,
            'model' => $model,
            'type' => 'smartphone',
            'imei' => 'IMEI' . random_int(1000000000, 9999999999),
            'status' => 'active',
        ]);
    }

    /**
     * A completed, paid, closed original service with an active store warranty
     * and a paid Sale (the historical, financially intact record).
     */
    protected function makeClosedPaidService(Branch $branch, int $warrantyDays = 30, float $charge = 100000, array $extra = []): Service
    {
        $customer = $this->makeCustomer($branch);
        $device = $this->makeDevice($customer);

        $service = Service::create(array_merge([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'device_id' => $device->id,
            'created_by' => $this->owner->id,
            'technician_id' => $this->techA->id,
            'status' => Service::STATUS_CLOSE,
            'payment_status' => 'paid',
            'service_charge' => $charge,
            'total_cost' => $charge,
            'warranty_days' => $warrantyDays,
            'warranty_expired_at' => now()->addDays($warrantyDays),
            'problem_description' => 'Servis awal',
            'selesai_at' => now(),
        ], $extra));

        ServiceWarranty::createFromService($service, $warrantyDays);

        Sale::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'sale_type' => Sale::SALE_TYPE_SERVIS,
            'status' => Sale::STATUS_PAID,
            'subtotal' => $charge,
            'discount' => 0,
            'total' => $charge,
            'payment_method' => 'cash',
            'paid_amount' => $charge,
            'change' => 0,
        ]);

        // Historical technician commission earned on the ORIGINAL service.
        \App\Models\Tenant\Commission::create([
            'service_id' => $service->id,
            'technician_id' => $this->techA->id,
            'amount' => round($charge * 0.1),
            'percentage' => 10,
            'status' => 'pending',
        ]);

        return $service;
    }

    /** A service with an active warranty window but NOT closed/paid (still claimable). */
    protected function makeWarrantyService(Branch $branch, bool $expired = false): Service
    {
        $customer = $this->makeCustomer($branch);
        $device = $this->makeDevice($customer);

        $service = Service::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'device_id' => $device->id,
            'created_by' => $this->owner->id,
            'technician_id' => $this->techA->id,
            'status' => Service::STATUS_SIAP_DIAMBIL,
            'payment_status' => 'paid',
            'service_charge' => 100000,
            'total_cost' => 100000,
            'warranty_days' => 30,
            'warranty_expired_at' => $expired ? now()->subDay() : now()->addDays(30),
            'problem_description' => 'Servis dengan garansi',
        ]);

        ServiceWarranty::createFromService($service, $expired ? 0 : 30);

        if ($expired) {
            ServiceWarranty::where('service_id', $service->id)->update([
                'end_date' => now()->subDay()->toDateString(),
                'status' => 'expired',
            ]);
        }

        return $service;
    }

    protected function makeProduct(Branch $branch, string $name = 'IC Charger', int $qty = 10): Product
    {
        return Product::create([
            'name' => $name . ' ' . $branch->name,
            'branch_id' => $branch->id,
            'stock_quantity' => $qty,
            'cost_price' => 10000,
            'selling_price' => 25000,
        ]);
    }

    /** Open a claim through the canonical route as $actor. */
    protected function openClaim(Service $original, User $actor, string $problem = 'Rusak lagi', ?int $branchId = null)
    {
        return $this->actingAs($actor)->post(route('services.warranty-claim', $original), array_filter([
            'problem_description' => $problem,
            'branch_id' => $branchId,
        ], fn($v) => $v !== null));
    }

    /** Approve an open claim (authorized review → creates rework). */
    protected function approveClaim(\App\Models\Tenant\ServiceWarrantyClaim $claim, User $actor, string $note = 'Disetujui')
    {
        return $this->actingAs($actor)->post(route('warranty-claims.decide', $claim), [
            'decision' => 'approve', 'note' => $note,
        ]);
    }

    /** Reject an open claim (no rework). */
    protected function rejectClaim(\App\Models\Tenant\ServiceWarrantyClaim $claim, User $actor, string $reason)
    {
        return $this->actingAs($actor)->post(route('warranty-claims.decide', $claim), [
            'decision' => 'reject', 'note' => $reason,
        ]);
    }

    /** Drive a warranty rework to QC-PASS (repair → finish → QC pass). */
    protected function driveReworkToQcPass(\App\Models\Tenant\Service $rework, User $actor): void
    {
        $this->actingAs($actor);
        $this->post(route('services.assign-technician', $rework), ['technician_id' => $rework->technician_id ?? $this->techA->id]);
        $this->post(route('services.finish', $rework));
        $this->post(route('services.qc.store', $rework), [
            'checks' => [['item' => 'Fungsi', 'result' => 'pass', 'notes' => 'ok']],
            'qc_decision' => 'pass',
            'qc_notes' => 'QC rework lulus',
        ]);
    }
}
