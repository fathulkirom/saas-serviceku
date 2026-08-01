<?php

namespace Tests\Feature;

use App\Models\Tenant\Service;
use Carbon\Carbon;
use Tests\TestCase;

class TenantServiceVisibilityTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_cs_only_sees_today_when_filtering_completed_services(): void
    {
        $branch = $this->createBranch();
        $cs = $this->createTenantUser([
            'role' => 'cs',
            'branch_id' => $branch->id,
        ]);

        $todayCompleted = $this->createService([
            'branch_id' => $branch->id,
            'created_by' => $cs->id,
            'status' => Service::STATUS_SELESAI,
        ]);

        $yesterdayCompleted = $this->createService([
            'branch_id' => $branch->id,
            'created_by' => $cs->id,
            'status' => Service::STATUS_SELESAI,
        ]);
        $yesterdayCompleted->timestamps = false;
        $yesterdayCompleted->updated_at = Carbon::yesterday();
        $yesterdayCompleted->created_at = Carbon::yesterday();
        $yesterdayCompleted->save();

        $this->actingAs($cs);

        $response = $this->get(route('services.index', ['status' => Service::STATUS_SELESAI]));

        $response->assertOk();
        $page = $response->viewData('page');
        $rows = collect($page['props']['services']['data'] ?? [])->map(fn($row) => (array) $row)->values()->all();
        $ids = array_map(fn($row) => $row['id'] ?? null, $rows);

        $this->assertContains($todayCompleted->id, $ids);
        $this->assertNotContains($yesterdayCompleted->id, $ids);

        $today = now()->toDateString();
        foreach ($rows as $row) {
            $this->assertSame(Service::STATUS_SELESAI, $row['status'] ?? null);
            $this->assertStringStartsWith($today, (string) ($row['updated_at'] ?? ''));
        }
    }

    public function test_owner_can_still_see_completed_services_from_other_days(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser([
            'role' => 'owner',
            'branch_id' => $branch->id,
        ]);

        $this->createService([
            'branch_id' => $branch->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_SELESAI,
        ]);

        $yesterdayCompleted = $this->createService([
            'branch_id' => $branch->id,
            'created_by' => $owner->id,
            'status' => Service::STATUS_SELESAI,
        ]);
        $yesterdayCompleted->timestamps = false;
        $yesterdayCompleted->updated_at = Carbon::yesterday();
        $yesterdayCompleted->created_at = Carbon::yesterday();
        $yesterdayCompleted->save();

        $this->actingAs($owner);

        $response = $this->get(route('services.index', ['status' => Service::STATUS_SELESAI]));

        $response->assertOk();
        $page = $response->viewData('page');
        $rows = collect($page['props']['services']['data'] ?? [])->map(fn($row) => (array) $row)->values()->all();

        $this->assertGreaterThanOrEqual(2, count($rows));
    }
}
