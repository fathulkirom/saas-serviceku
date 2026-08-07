<?php

namespace Tests\Feature\Tenant;

use App\Models\Tenant\User;
use App\Models\Tenant\Service;
use App\Models\Tenant\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServicePolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_cs_can_only_view_own_branch_services()
    {
        $branch1 = Branch::create(['name' => 'Branch A']);
        $branch2 = Branch::create(['name' => 'Branch B']);

        $cs = $this->createTenantUser([
            'role' => 'cs',
            'branch_id' => $branch1->id,
            'active' => true
        ]);

        $service1 = $this->createService(['branch_id' => $branch1->id]);
        $service2 = $this->createService(['branch_id' => $branch2->id]);

        $this->actingAs($cs);

        $this->assertTrue($cs->can('view', $service1));
        $this->assertFalse($cs->can('view', $service2));
    }

    public function test_technician_can_only_access_assigned_service()
    {
        $branch = Branch::create(['name' => 'Branch A']);
        $tech1 = $this->createTenantUser(['role' => 'technician', 'branch_id' => $branch->id]);
        $tech2 = $this->createTenantUser(['role' => 'technician', 'branch_id' => $branch->id]);

        $service = $this->createService(['branch_id' => $branch->id, 'technician_id' => $tech1->id]);

        $this->assertTrue($tech1->can('update', $service));
        $this->assertFalse($tech2->can('update', $service));
    }

    public function test_owner_can_access_all_branches()
    {
        $branch1 = Branch::create(['name' => 'Branch A']);
        $branch2 = Branch::create(['name' => 'Branch B']);

        $owner = $this->createTenantUser(['role' => 'owner']);

        $service1 = $this->createService(['branch_id' => $branch1->id]);
        $service2 = $this->createService(['branch_id' => $branch2->id]);

        $this->assertTrue($owner->can('view', $service1));
        $this->assertTrue($owner->can('view', $service2));
    }
}
