<?php

namespace Tests\Feature\BusinessReality;

use App\Models\Tenant\Branch;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Device;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceWarranty;
use App\Models\Tenant\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * BR-006 — CROSS-TENANT CUSTOMER ISOLATION.
 *
 * The same real-world person may visit Tenant A and later Tenant B (same
 * phone / name / IMEI) — this is VALID — but Tenant B must NEVER see Tenant A
 * customer history. ServiceKU uses 1 database per tenant (stancl/tenancy);
 * these tests prove isolation with REAL tenant initialization, not mocks.
 */
class BR06CrossTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Seed the ACTIVE tenant with a customer/device/service/warranty/sale.
     * Each tenant uses a unique marker (name + imei) so cross-tenant "resolve
     * by id" assertions are unambiguous (both tenants share auto-increment ids).
     */
    private function seedActiveTenant(string $marker, string $sharedPhone = '08123456789'): array
    {
        $branch = Branch::create(['name' => 'Cabang ' . $marker]);
        $customer = Customer::create(['name' => 'John-' . $marker, 'phone' => $sharedPhone, 'branch_id' => $branch->id]);
        $device = Device::create([
            'customer_id' => $customer->id, 'brand' => 'Samsung', 'model' => 'S23',
            'type' => 'smartphone', 'imei' => 'IMEI-' . $marker, 'status' => 'active',
        ]);
        $service = Service::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'device_id' => $device->id,
            'created_by' => $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id])->id,
            'status' => Service::STATUS_SELESAI,
            'payment_status' => 'paid',
            'service_charge' => 100000,
            'total_cost' => 100000,
            'warranty_days' => 30,
            'warranty_expired_at' => now()->addDays(30),
            'problem_description' => 'Servis ' . $marker,
        ]);
        ServiceWarranty::createFromService($service, 30);
        Sale::create([
            'branch_id' => $branch->id, 'customer_id' => $customer->id, 'service_id' => $service->id,
            'sale_type' => Sale::SALE_TYPE_SERVIS, 'status' => Sale::STATUS_PAID,
            'subtotal' => 100000, 'discount' => 0, 'total' => 100000,
            'payment_method' => 'cash', 'paid_amount' => 100000, 'change' => 0,
        ]);

        return [$branch, $customer, $device, $service];
    }

    // 1. Tenant A creates Customer phone 081xxx.
    // 2. Tenant B can independently create same phone.
    public function test_same_person_can_exist_independently_in_two_tenants(): void
    {
        $tenantA = $this->setUpTenant();
        $this->grantFullPlanAccess();
        [, $customerA] = $this->seedActiveTenant('TENANTA');

        $tenantB = $this->setUpTenant();
        $this->grantFullPlanAccess();
        [, $customerB] = $this->seedActiveTenant('TENANTB');

        // Both exist as SEPARATE rows in separate tenant DBs (ids may coincide;
        // isolation is proven via unique markers).
        $this->assertSame('08123456789', $customerB->phone);
        $this->assertSame(1, Customer::where('phone', '08123456789')->count());
        $this->assertSame('John-TENANTB', Customer::where('phone', '08123456789')->first()->name);

        tenancy()->initialize($tenantA);
        $this->assertSame('John-TENANTA', Customer::where('phone', '08123456789')->first()->name);
    }

    // 3. Tenant B lookup does NOT return Tenant A Customer.
    public function test_tenant_b_lookup_does_not_return_tenant_a_customer(): void
    {
        $tenantA = $this->setUpTenant();
        $this->grantFullPlanAccess();
        [, $customerA] = $this->seedActiveTenant('TENANTA');

        $tenantB = $this->setUpTenant();
        $this->grantFullPlanAccess();
        $this->seedActiveTenant('TENANTB');

        // In B: phone lookup returns only B's customer (John-TENANTB), not A's.
        $found = Customer::where('phone', '08123456789')->get();
        $this->assertCount(1, $found);
        $this->assertSame('John-TENANTB', $found->first()->name);
        $this->assertFalse(Customer::where('name', 'John-TENANTA')->exists());

        // Back in A: only A's customer.
        tenancy()->initialize($tenantA);
        $this->assertSame(1, Customer::where('phone', '08123456789')->count());
        $this->assertSame('John-TENANTA', Customer::where('phone', '08123456789')->first()->name);
    }

    // 4. Tenant B service history does NOT show Tenant A Service.
    public function test_tenant_b_service_history_does_not_show_tenant_a_service(): void
    {
        $tenantA = $this->setUpTenant();
        $this->grantFullPlanAccess();
        [, , , $serviceA] = $this->seedActiveTenant('TENANTA');

        $tenantB = $this->setUpTenant();
        $this->grantFullPlanAccess();
        [, $customerB, , $serviceB] = $this->seedActiveTenant('TENANTB');

        // In B: the customer sees only B's service.
        $bServiceIds = Service::where('customer_id', $customerB->id)->pluck('id')->all();
        $this->assertEquals([$serviceB->id], $bServiceIds);
        $this->assertFalse(Service::where('problem_description', 'Servis TENANTA')->exists());

        tenancy()->initialize($tenantA);
        $this->assertFalse(Service::where('problem_description', 'Servis TENANTB')->exists());
    }

    // 5. Tenant B cannot resolve Tenant A Customer ID.
    public function test_tenant_b_cannot_resolve_tenant_a_customer_id(): void
    {
        $tenantA = $this->setUpTenant();
        $this->grantFullPlanAccess();
        [, $customerA] = $this->seedActiveTenant('TENANTA');

        $tenantB = $this->setUpTenant();
        $this->grantFullPlanAccess();
        $this->seedActiveTenant('TENANTB');

        // Even though auto-increment ids may coincide, A's record is unreachable:
        // the row at A's id in B is B's own customer (or absent), never A's.
        $foundAtId = Customer::whereKey($customerA->id)->first();
        $this->assertTrue($foundAtId === null || $foundAtId->name !== 'John-TENANTA');
    }

    // 6. Tenant B cannot resolve Tenant A Device ID.
    public function test_tenant_b_cannot_resolve_tenant_a_device_id(): void
    {
        $tenantA = $this->setUpTenant();
        $this->grantFullPlanAccess();
        [, , $deviceA] = $this->seedActiveTenant('TENANTA');

        $tenantB = $this->setUpTenant();
        $this->grantFullPlanAccess();
        $this->seedActiveTenant('TENANTB');

        $foundAtId = Device::whereKey($deviceA->id)->first();
        $this->assertTrue($foundAtId === null || $foundAtId->imei !== 'IMEI-TENANTA');
    }

    // 7. Same IMEI/SN in isolated tenant does not expose Tenant A data.
    public function test_same_imei_in_isolated_tenant_does_not_expose_tenant_a_data(): void
    {
        $tenantA = $this->setUpTenant();
        $this->grantFullPlanAccess();
        [$branchA, $customerA, $deviceA] = $this->seedActiveTenant('TENANTA');
        // A's device uses a SHARED imei too.
        $deviceA->update(['imei' => 'IMEI-SHARED']);

        $tenantB = $this->setUpTenant();
        $this->grantFullPlanAccess();
        [, $customerB, $deviceB] = $this->seedActiveTenant('TENANTB');
        $deviceB->update(['imei' => 'IMEI-SHARED']);

        // In B: the shared-IMEI device is B's, owned by B's customer.
        $matchB = Device::where('imei', 'IMEI-SHARED')->first();
        $this->assertNotNull($matchB);
        $this->assertSame($deviceB->id, $matchB->id);
        $this->assertSame($customerB->id, $matchB->customer_id);

        tenancy()->initialize($tenantA);
        $matchA = Device::where('imei', 'IMEI-SHARED')->first();
        $this->assertNotNull($matchA);
        $this->assertSame($deviceA->id, $matchA->id);
        $this->assertSame($customerA->id, $matchA->customer_id);
    }

    // 8. Tenant A warranty invisible to Tenant B.
    public function test_tenant_a_warranty_invisible_to_tenant_b(): void
    {
        $tenantA = $this->setUpTenant();
        $this->grantFullPlanAccess();
        $this->seedActiveTenant('TENANTA');

        $tenantB = $this->setUpTenant();
        $this->grantFullPlanAccess();
        $this->seedActiveTenant('TENANTB');

        // Tenant A's warranty is unreachable in B — the row at A's service id
        // belongs to B's own service (ids coincide across tenants).
        $this->assertSame(0, ServiceWarranty::whereHas('service', fn($q) => $q->where('problem_description', 'Servis TENANTA'))->count());

        tenancy()->initialize($tenantA);
        $this->assertSame(1, ServiceWarranty::whereHas('service', fn($q) => $q->where('problem_description', 'Servis TENANTA'))->count());
    }

    // 9. Tenant A Sale/Payment invisible to Tenant B.
    public function test_tenant_a_sale_invisible_to_tenant_b(): void
    {
        $tenantA = $this->setUpTenant();
        $this->grantFullPlanAccess();
        $this->seedActiveTenant('TENANTA');

        $tenantB = $this->setUpTenant();
        $this->grantFullPlanAccess();
        $this->seedActiveTenant('TENANTB');

        // Tenant A's Sale is unreachable in B (ids coincide across tenants).
        $this->assertSame(0, Sale::whereHas('service', fn($q) => $q->where('problem_description', 'Servis TENANTA'))->count());

        tenancy()->initialize($tenantA);
        $this->assertSame(1, Sale::whereHas('service', fn($q) => $q->where('problem_description', 'Servis TENANTA'))->count());
    }

    // 10. Customer merge cannot cross tenant.
    public function test_customer_merge_cannot_cross_tenant(): void
    {
        $tenantA = $this->setUpTenant();
        $this->grantFullPlanAccess();
        [, $customerA] = $this->seedActiveTenant('TENANTA');

        $tenantB = $this->setUpTenant();
        $this->grantFullPlanAccess();
        [, $customerB] = $this->seedActiveTenant('TENANTB');

        // Same phone, but independent rows — no merge across tenants.
        $this->assertSame(1, Customer::where('phone', '08123456789')->count());
        $this->assertSame($customerB->id, Customer::where('phone', '08123456789')->first()->id);

        tenancy()->initialize($tenantA);
        $this->assertSame(1, Customer::where('phone', '08123456789')->count());
        $this->assertSame($customerA->id, Customer::where('phone', '08123456789')->first()->id);
    }

    // 11. Search/autocomplete is tenant-isolated.
    public function test_search_is_tenant_isolated(): void
    {
        $tenantA = $this->setUpTenant();
        $this->grantFullPlanAccess();
        $this->seedActiveTenant('TENANTA');

        $tenantB = $this->setUpTenant();
        $this->grantFullPlanAccess();
        $this->seedActiveTenant('TENANTB');

        $hits = Customer::where('name', 'like', '%John%')->orWhere('phone', 'like', '081%')->get();
        $this->assertCount(1, $hits);
        $this->assertSame('John-TENANTB', $hits->first()->name);

        tenancy()->initialize($tenantA);
        $hitsA = Customer::where('name', 'like', '%John%')->orWhere('phone', 'like', '081%')->get();
        $this->assertCount(1, $hitsA);
        $this->assertSame('John-TENANTA', $hitsA->first()->name);
    }

    // 12. Delete/update in Tenant B cannot affect Tenant A record.
    public function test_update_or_delete_in_tenant_b_cannot_affect_tenant_a(): void
    {
        $tenantA = $this->setUpTenant();
        $this->grantFullPlanAccess();
        [, $customerA] = $this->seedActiveTenant('TENANTA');

        $tenantB = $this->setUpTenant();
        $this->grantFullPlanAccess();
        [, $customerB] = $this->seedActiveTenant('TENANTB');

        // Update B's customer.
        $customerB->update(['name' => 'John-B-UPDATED']);

        // A's customer is unaffected.
        tenancy()->initialize($tenantA);
        $this->assertSame('John-TENANTA', $customerA->fresh()->name);
        $this->assertFalse(Customer::where('name', 'John-B-UPDATED')->exists());

        // Delete B's customer.
        tenancy()->initialize($tenantB);
        $customerB->delete();
        tenancy()->initialize($tenantA);
        $this->assertTrue(Customer::whereKey($customerA->id)->exists());
    }
}

