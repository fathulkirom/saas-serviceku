<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\SaleStoreController;
use App\Models\Tenant\Indent;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TenantSaleStoreGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        Queue::fake();
    }

    public function test_sale_type_servis_requires_service_id(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);

        $this->actingAs($owner);

        $request = Request::create('/sales', 'POST', [
            'as_draft' => true,
            'sale_type' => Sale::SALE_TYPE_SERVIS,
            'items' => [
                [
                    'item_type' => 'jasa',
                    'description' => 'Biaya servis',
                    'quantity' => 1,
                    'price' => 100000,
                ],
            ],
        ]);

        $this->expectException(ValidationException::class);
        app(SaleStoreController::class)->store($request);
    }

    public function test_cannot_create_second_active_sale_for_same_service(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);

        $service = $this->createService([
            'branch_id' => $branch->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_SELESAI,
        ]);

        Sale::create([
            'branch_id' => $branch->id,
            'customer_id' => $service->customer_id,
            'sale_type' => Sale::SALE_TYPE_SERVIS,
            'status' => Sale::STATUS_DRAFT,
            'service_id' => $service->id,
            'subtotal' => 100000,
            'discount' => 0,
            'total' => 100000,
            'payment_method' => 'draft',
            'paid_amount' => 0,
            'change' => 0,
        ]);

        $this->actingAs($owner);

        $request = Request::create('/sales', 'POST', [
            'as_draft' => true,
            'sale_type' => Sale::SALE_TYPE_SERVIS,
            'service_id' => $service->id,
            'items' => [
                [
                    'item_type' => 'jasa',
                    'description' => 'Biaya servis',
                    'quantity' => 1,
                    'price' => 100000,
                ],
            ],
        ]);

        $this->expectException(ValidationException::class);
        app(SaleStoreController::class)->store($request);
    }

    public function test_cannot_create_sale_with_service_from_another_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'A']);
        $branchB = $this->createBranch(['name' => 'B']);

        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branchA->id]);

        $serviceBranchB = $this->createService([
            'branch_id' => $branchB->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_SELESAI,
        ]);

        $this->actingAs($owner);

        $request = Request::create('/sales', 'POST', [
            'as_draft' => true,
            'sale_type' => Sale::SALE_TYPE_SERVIS,
            'service_id' => $serviceBranchB->id,
            'items' => [
                [
                    'item_type' => 'jasa',
                    'description' => 'Biaya servis',
                    'quantity' => 1,
                    'price' => 100000,
                ],
            ],
        ]);

        $this->expectException(ValidationException::class);
        app(SaleStoreController::class)->store($request);
    }

    public function test_cannot_create_sale_with_indent_from_another_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'A']);
        $branchB = $this->createBranch(['name' => 'B']);

        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branchA->id]);

        $indentBranchB = Indent::create([
            'branch_id' => $branchB->id,
            'customer_id' => $this->createCustomer()->id,
            'product_name' => 'LCD',
            'qty' => 1,
            'cost_estimate' => 200000,
            'deposit' => 50000,
            'status' => Indent::STATUS_PENDING,
        ]);

        $this->actingAs($owner);

        $request = Request::create('/sales', 'POST', [
            'as_draft' => true,
            'sale_type' => Sale::SALE_TYPE_INDEN,
            'indent_id' => $indentBranchB->id,
            'items' => [
                [
                    'item_type' => 'jasa',
                    'description' => 'Jasa inden',
                    'quantity' => 1,
                    'price' => 200000,
                ],
            ],
        ]);

        $this->expectException(ValidationException::class);
        app(SaleStoreController::class)->store($request);
    }
}
