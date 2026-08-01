<?php

namespace Tests\Feature;

use App\Models\Tenant\Sale;
use Carbon\Carbon;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class TenantFinanceTransactionVisibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_cashier_only_sees_today_paid_transactions(): void
    {
        $branch = $this->createBranch();
        $cashier = $this->createTenantUser([
            'role' => 'cashier',
            'branch_id' => $branch->id,
        ]);

        $todayPaid = Sale::create([
            'branch_id' => $branch->id,
            'sale_type' => Sale::SALE_TYPE_LANGSUNG,
            'status' => Sale::STATUS_PAID,
            'subtotal' => 100000,
            'total' => 100000,
            'payment_method' => 'cash',
            'paid_amount' => 100000,
            'change' => 0,
        ]);

        $yesterdayPaid = Sale::create([
            'branch_id' => $branch->id,
            'sale_type' => Sale::SALE_TYPE_LANGSUNG,
            'status' => Sale::STATUS_PAID,
            'subtotal' => 120000,
            'total' => 120000,
            'payment_method' => 'cash',
            'paid_amount' => 120000,
            'change' => 0,
        ]);
        $yesterdayPaid->timestamps = false;
        $yesterdayPaid->created_at = Carbon::yesterday();
        $yesterdayPaid->updated_at = Carbon::yesterday();
        $yesterdayPaid->save();

        Sale::create([
            'branch_id' => $branch->id,
            'sale_type' => Sale::SALE_TYPE_LANGSUNG,
            'status' => Sale::STATUS_DRAFT,
            'subtotal' => 90000,
            'total' => 90000,
            'payment_method' => 'draft',
            'paid_amount' => 0,
            'change' => 0,
        ]);

        $this->actingAs($cashier);

        $response = $this->get(route('keuangan.index'));

        $response->assertOk();
        $page = $response->viewData('page');
        $rows = collect($page['props']['sales']['data'] ?? [])->map(fn($row) => (array) $row)->values()->all();
        $ids = array_map(fn($row) => $row['id'] ?? null, $rows);

        $this->assertContains($todayPaid->id, $ids);
        $this->assertNotContains($yesterdayPaid->id, $ids);
        $this->assertGreaterThanOrEqual(1, count($rows));

        $today = now()->toDateString();
        foreach ($rows as $row) {
            $this->assertSame(Sale::STATUS_PAID, $row['status'] ?? null);
            $this->assertStringStartsWith($today, (string) ($row['created_at'] ?? ''));
        }

        $this->assertSame(Sale::STATUS_PAID, $page['props']['salesFilters']['status'] ?? null);
        $this->assertSame(0, $page['props']['salesStats']['drafts'] ?? null);
    }

    public function test_owner_still_can_see_transactions_from_other_days_and_drafts(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branch->id,
        ]);

        Sale::create([
            'branch_id' => $branch->id,
            'sale_type' => Sale::SALE_TYPE_LANGSUNG,
            'status' => Sale::STATUS_PAID,
            'subtotal' => 100000,
            'total' => 100000,
            'payment_method' => 'cash',
            'paid_amount' => 100000,
            'change' => 0,
        ]);

        $ownerYesterdayPaid = Sale::create([
            'branch_id' => $branch->id,
            'sale_type' => Sale::SALE_TYPE_LANGSUNG,
            'status' => Sale::STATUS_PAID,
            'subtotal' => 120000,
            'total' => 120000,
            'payment_method' => 'cash',
            'paid_amount' => 120000,
            'change' => 0,
        ]);
        $ownerYesterdayPaid->timestamps = false;
        $ownerYesterdayPaid->created_at = Carbon::yesterday();
        $ownerYesterdayPaid->updated_at = Carbon::yesterday();
        $ownerYesterdayPaid->save();

        Sale::create([
            'branch_id' => $branch->id,
            'sale_type' => Sale::SALE_TYPE_LANGSUNG,
            'status' => Sale::STATUS_DRAFT,
            'subtotal' => 90000,
            'total' => 90000,
            'payment_method' => 'draft',
            'paid_amount' => 0,
            'change' => 0,
        ]);

        $this->actingAs($owner);

        $response = $this->get(route('keuangan.index'));

        $response->assertOk();
        $response->assertInertia(fn(Assert $page) => $page
            ->component('Keuangan/Index')
            ->has('sales.data', 3)
            ->where('salesStats.drafts', 1)
            ->where('salesStats.paid', 2)
        );
    }

    public function test_admin_harian_custom_role_only_sees_today_paid_transactions(): void
    {
        $branch = $this->createBranch();
        $adminHarian = $this->createTenantUser([
            'role' => 'custom',
            'custom_role' => 'admin harian',
            'branch_id' => $branch->id,
        ]);

        $todayPaid = Sale::create([
            'branch_id' => $branch->id,
            'sale_type' => Sale::SALE_TYPE_LANGSUNG,
            'status' => Sale::STATUS_PAID,
            'subtotal' => 100000,
            'total' => 100000,
            'payment_method' => 'cash',
            'paid_amount' => 100000,
            'change' => 0,
        ]);

        $yesterdayPaid = Sale::create([
            'branch_id' => $branch->id,
            'sale_type' => Sale::SALE_TYPE_LANGSUNG,
            'status' => Sale::STATUS_PAID,
            'subtotal' => 120000,
            'total' => 120000,
            'payment_method' => 'cash',
            'paid_amount' => 120000,
            'change' => 0,
        ]);
        $yesterdayPaid->timestamps = false;
        $yesterdayPaid->created_at = Carbon::yesterday();
        $yesterdayPaid->updated_at = Carbon::yesterday();
        $yesterdayPaid->save();

        $this->actingAs($adminHarian);

        $response = $this->get(route('keuangan.index'));

        $response->assertOk();
        $page = $response->viewData('page');
        $rows = collect($page['props']['sales']['data'] ?? [])->map(fn($row) => (array) $row)->values()->all();
        $ids = array_map(fn($row) => $row['id'] ?? null, $rows);

        $this->assertContains($todayPaid->id, $ids);
        $this->assertNotContains($yesterdayPaid->id, $ids);
        $this->assertSame(Sale::STATUS_PAID, $page['props']['salesFilters']['status'] ?? null);
        $this->assertSame(0, $page['props']['salesStats']['drafts'] ?? null);
    }
}
