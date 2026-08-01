<?php

namespace Tests\Feature;

use App\Models\Tenant\Service;
use Tests\TestCase;

class TenantServiceControllerBranchScopeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_branch_user_only_sees_services_from_own_branch_in_index_and_stats(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerA = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branchA->id,
        ]);

        $ownerB = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branchB->id,
        ]);

        $customerA = $this->createCustomer(['branch_id' => $branchA->id]);
        $customerB = $this->createCustomer(['branch_id' => $branchB->id]);

        $serviceA = $this->createService([
            'branch_id' => $branchA->id,
            'customer_id' => $customerA->id,
            'created_by' => $ownerA->id,
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
        ]);

        $serviceB = $this->createService([
            'branch_id' => $branchB->id,
            'customer_id' => $customerB->id,
            'created_by' => $ownerB->id,
            'status' => Service::STATUS_DIKERJAKAN,
        ]);

        $this->actingAs($ownerA);

        $response = $this->get(route('services.index'));

        $response->assertOk();
        $page = $response->viewData('page');

        $rows = collect($page['props']['services']['data'] ?? [])->map(fn($row) => (array) $row)->values()->all();
        $ids = array_map(fn($row) => $row['id'] ?? null, $rows);

        $this->assertContains($serviceA->id, $ids);
        $this->assertNotContains($serviceB->id, $ids);

        $stats = (array) ($page['props']['stats'] ?? []);
        $this->assertSame(1, (int) ($stats['all'] ?? 0));
        $this->assertSame(1, (int) ($stats[Service::STATUS_MENUNGGU_ALOKASI] ?? 0));
        $this->assertSame(0, (int) ($stats[Service::STATUS_DIKERJAKAN] ?? 0));
    }

    public function test_branch_user_cannot_open_service_detail_from_other_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerA = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branchA->id,
        ]);

        $ownerB = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branchB->id,
        ]);

        $customerB = $this->createCustomer(['branch_id' => $branchB->id]);

        $serviceB = $this->createService([
            'branch_id' => $branchB->id,
            'customer_id' => $customerB->id,
            'created_by' => $ownerB->id,
        ]);

        $this->actingAs($ownerA);

        $response = $this->get(route('services.show', $serviceB));

        $response->assertRedirect();
        $response->assertSessionHasErrors(['service']);
    }

    public function test_index_only_exposes_assignable_users_from_same_branch(): void
    {
        $branchA = $this->createBranch(['name' => 'Cabang A']);
        $branchB = $this->createBranch(['name' => 'Cabang B']);

        $ownerA = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branchA->id,
            'name' => 'Owner A',
        ]);

        $technicianA = $this->createTenantUser([
            'role' => 'technician',
            'branch_id' => $branchA->id,
            'name' => 'Teknisi A',
        ]);

        $this->createTenantUser([
            'role' => 'cs',
            'branch_id' => $branchA->id,
            'name' => 'CS A',
        ]);

        $this->createTenantUser([
            'role' => 'technician',
            'branch_id' => $branchB->id,
            'name' => 'Teknisi B',
        ]);

        $this->createTenantUser([
            'role' => 'technician',
            'branch_id' => $branchA->id,
            'name' => 'Teknisi Nonaktif',
            'active' => false,
        ]);

        $this->actingAs($ownerA);

        $response = $this->get(route('services.index'));

        $response->assertOk();
        $page = $response->viewData('page');
        $users = collect($page['props']['users'] ?? [])->map(fn ($row) => (array) $row)->values();

        $names = $users->pluck('name')->all();
        $roles = $users->pluck('role', 'name');

        $this->assertContains('Owner A', $names);
        $this->assertContains('Teknisi A', $names);
        $this->assertNotContains('CS A', $names);
        $this->assertNotContains('Teknisi B', $names);
        $this->assertNotContains('Teknisi Nonaktif', $names);
        $this->assertSame('owner', $roles['Owner A']);
        $this->assertSame('technician', $roles['Teknisi A']);
    }
}
