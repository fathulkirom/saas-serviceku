<?php

namespace Tests\Feature;

use App\Models\Tenant\Service;
use App\Models\Tenant\User;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use Tests\TestCase;

class TenantBranchIsolationSmokeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    // ==================== USER MANAGEMENT ====================

    public function test_owner_cannot_update_user_from_other_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerA = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branchA->id, 'name' => 'Owner A']);
        $userB = $this->createTenantUser(['role' => 'technician', 'branch_id' => $branchB->id, 'name' => 'Teknisi B']);

        $this->actingAs($ownerA);

        $response = $this->put(route('users.update', $userB), [
            'name' => 'Teknisi B Revisi',
            'email' => $userB->email,
            'role' => 'technician',
            'active' => true,
        ]);

        $response->assertRedirect();

        $userB->refresh();
        $this->assertSame('Teknisi B', $userB->name);
    }

    public function test_owner_cannot_delete_user_from_other_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerA = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branchA->id]);
        $userB = $this->createTenantUser(['role' => 'cs', 'branch_id' => $branchB->id, 'name' => 'CS B']);

        $this->actingAs($ownerA);

        $response = $this->delete(route('users.destroy', $userB));

        $response->assertRedirect();
        $this->assertNotNull(User::find($userB->id));
    }

    // ==================== CUSTOMER ====================

    public function test_owner_cannot_delete_customer_from_other_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerA = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branchA->id]);
        $customerB = $this->createCustomer(['branch_id' => $branchB->id, 'name' => 'Customer B']);

        $this->actingAs($ownerA);

        $response = $this->delete(route('customers.destroy', $customerB));

        $response->assertSessionHasErrors(['customer']);
        $this->assertNotNull(Customer::find($customerB->id));
    }

    // ==================== PRODUCT ====================

    public function test_owner_cannot_delete_product_from_other_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerA = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branchA->id]);
        $productB = Product::create([
            'branch_id' => $branchB->id,
            'name' => 'Produk B',
            'code' => 'PRD-B-' . uniqid(),
            'stock_quantity' => 10,
        ]);

        $this->actingAs($ownerA);

        $response = $this->delete(route('products.destroy', $productB));

        $response->assertSessionHasErrors(['product']);
        $this->assertNotNull(Product::find($productB->id));
    }

    // ==================== SERVICE DETAIL ====================

    public function test_owner_cannot_edit_service_from_other_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerA = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branchA->id]);
        $ownerB = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branchB->id]);
        $customerB = $this->createCustomer(['branch_id' => $branchB->id]);

        $service = $this->createService([
            'branch_id' => $branchB->id,
            'customer_id' => $customerB->id,
            'created_by' => $ownerB->id,
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
            'problem_description' => 'Asli',
        ]);

        $this->actingAs($ownerA);

        $response = $this->put(route('services.update', $service), [
            'problem_description' => 'Diubah lintas cabang',
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
        ]);

        $response->assertSessionHasErrors(['service']);

        $service->refresh();
        $this->assertSame('Asli', $service->problem_description);
    }

    // ==================== SERVICE API ENDPOINTS ====================

    public function test_owner_cannot_partner_service_from_other_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerA = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branchA->id]);
        $ownerB = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branchB->id]);
        $customerB = $this->createCustomer(['branch_id' => $branchB->id]);

        $service = $this->createService([
            'branch_id' => $branchB->id,
            'customer_id' => $customerB->id,
            'created_by' => $ownerB->id,
            'status' => Service::STATUS_DIKERJAKAN,
        ]);

        $this->actingAs($ownerA);

        $response = $this->post(route('services.partner', $service), ['partner_note' => 'X']);

        $response->assertSessionHasErrors(['service']);

        $service->refresh();
        $this->assertSame(Service::STATUS_DIKERJAKAN, $service->status);
    }

    public function test_owner_cannot_confirm_internal_from_other_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerA = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branchA->id]);
        $ownerB = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branchB->id]);
        $customerB = $this->createCustomer(['branch_id' => $branchB->id]);

        $service = $this->createService([
            'branch_id' => $branchB->id,
            'customer_id' => $customerB->id,
            'created_by' => $ownerB->id,
            'status' => Service::STATUS_DIKERJAKAN,
        ]);

        $this->actingAs($ownerA);

        $response = $this->post(route('services.confirm-internal', $service));

        $response->assertSessionHasErrors(['service']);

        $service->refresh();
        $this->assertSame(Service::STATUS_DIKERJAKAN, $service->status);
    }
}
