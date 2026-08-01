<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\IndentController;
use App\Models\Tenant\Indent;
use App\Models\Tenant\Service;
use Illuminate\Http\Request;
use Tests\TestCase;

class TenantIndentWorkflowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_store_indent_sets_service_to_indent_and_links_indent_id(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_DIKERJAKAN,
        ]);

        $this->actingAs($owner);

        $request = Request::create('/indents', 'POST', [
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'product_name' => 'IC Power',
            'qty' => 1,
            'cost_estimate' => 150000,
            'deposit' => 50000,
        ]);

        $response = app(IndentController::class)->store($request);

        $this->assertEquals(302, $response->getStatusCode());

        $indent = Indent::latest('id')->first();
        $this->assertNotNull($indent);
        $this->assertEquals($service->id, $indent->service_id);

        $freshService = $service->fresh();
        $this->assertEquals(Service::STATUS_INDENT, $freshService->status);
        $this->assertEquals($indent->id, $freshService->indent_id);
    }

    public function test_update_indent_to_selesai_restores_service_to_dikerjakan_and_clears_indent_link(): void
    {
        $owner = $this->createTenantUser(['role' => 'owner']);
        $branch = $this->createBranch();
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_INDENT,
        ]);

        $indent = Indent::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'product_name' => 'LCD',
            'qty' => 1,
            'cost_estimate' => 250000,
            'deposit' => 100000,
            'status' => Indent::STATUS_PENDING,
        ]);

        $service->update(['indent_id' => $indent->id]);

        $this->actingAs($owner);

        $request = Request::create('/indents/' . $indent->id, 'PUT', [
            'status' => Indent::STATUS_SELESAI,
            'cost_estimate' => 250000,
            'deposit' => 100000,
        ]);

        $response = app(IndentController::class)->update($request, $indent);

        $this->assertEquals(302, $response->getStatusCode());

        $freshService = $service->fresh();
        $this->assertEquals(Service::STATUS_DIKERJAKAN, $freshService->status);
        $this->assertNull($freshService->indent_id);
    }

    public function test_destroy_indent_restores_service_and_removes_link_when_service_still_points_to_indent(): void
    {
        $owner = $this->createTenantUser(['role' => 'owner']);
        $branch = $this->createBranch();
        $customer = $this->createCustomer();

        $service = $this->createService([
            'branch_id' => $branch->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_INDENT,
        ]);

        $indent = Indent::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'service_id' => $service->id,
            'product_name' => 'Baterai',
            'qty' => 1,
            'cost_estimate' => 175000,
            'deposit' => 50000,
            'status' => Indent::STATUS_PENDING,
        ]);

        $service->update(['indent_id' => $indent->id]);

        $this->actingAs($owner);
        $response = app(IndentController::class)->destroy($indent);

        $this->assertEquals(302, $response->getStatusCode());
        $this->assertNull(Indent::find($indent->id));

        $freshService = $service->fresh();
        $this->assertEquals(Service::STATUS_DIKERJAKAN, $freshService->status);
        $this->assertNull($freshService->indent_id);
    }
}
