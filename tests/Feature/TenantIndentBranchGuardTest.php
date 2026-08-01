<?php

namespace Tests\Feature;

use App\Http\Controllers\Tenant\IndentController;
use App\Models\Tenant\Indent;
use App\Models\Tenant\Service;
use Illuminate\Http\Request;
use Tests\TestCase;

class TenantIndentBranchGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_servis_tools_only_lists_indents_from_active_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerA = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branchA->id,
        ]);

        $customerA = $this->createCustomer(['branch_id' => $branchA->id, 'name' => 'Customer A']);
        $customerB = $this->createCustomer(['branch_id' => $branchB->id, 'name' => 'Customer B']);

        $indentA = Indent::create([
            'branch_id' => $branchA->id,
            'customer_id' => $customerA->id,
            'product_name' => 'LCD A',
            'qty' => 1,
            'status' => Indent::STATUS_PENDING,
        ]);

        $indentB = Indent::create([
            'branch_id' => $branchB->id,
            'customer_id' => $customerB->id,
            'product_name' => 'LCD B',
            'qty' => 1,
            'status' => Indent::STATUS_PENDING,
        ]);

        $this->actingAs($ownerA);

        $response = $this->get(route('servis-tools.index', ['tab' => 'inden']));

        $response->assertOk();
        $page = $response->viewData('page');
        $rows = collect($page['props']['indents']['data'] ?? [])->map(fn ($row) => (array) $row)->values()->all();
        $ids = array_map(fn ($row) => $row['id'] ?? null, $rows);

        $this->assertContains($indentA->id, $ids);
        $this->assertNotContains($indentB->id, $ids);
    }

    public function test_owner_cannot_update_indent_from_other_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerA = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branchA->id,
        ]);

        $customerB = $this->createCustomer(['branch_id' => $branchB->id]);

        $indent = Indent::create([
            'branch_id' => $branchB->id,
            'customer_id' => $customerB->id,
            'product_name' => 'IC Power',
            'qty' => 1,
            'status' => Indent::STATUS_PENDING,
        ]);

        $this->actingAs($ownerA);

        $beforeStatus = $indent->status;

        $response = $this->put(route('indents.update', $indent), [
            'status' => Indent::STATUS_SELESAI,
            'cost_estimate' => 150000,
            'deposit' => 50000,
        ]);

        $response->assertRedirect();

        $indent->refresh();
        $this->assertSame($beforeStatus, $indent->status);
        $this->assertSame(Indent::STATUS_PENDING, $indent->status);
    }

    public function test_cs_cannot_create_indent_for_customer_from_other_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $cs = $this->createTenantUser([
            'role' => 'cs',
            'branch_id' => $branchA->id,
        ]);

        $customerB = $this->createCustomer(['branch_id' => $branchB->id]);

        $beforeCount = Indent::count();

        $this->actingAs($cs);

        $response = $this->post(route('indents.store'), [
            'customer_id' => $customerB->id,
            'product_name' => 'Speaker',
            'qty' => 1,
            'cost_estimate' => 175000,
        ]);

        $response->assertRedirect();
        $this->assertSame($beforeCount, Indent::count());
    }
}
