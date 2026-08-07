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
            // PLATFORM-SYNC-01/GIT-SYNC-01: timestamps are stored/serialized in
            // UTC ("...Z") but the app runs in Asia/Jakarta (UTC+7). Comparing
            // the raw UTC string against the LOCAL date fails between 00:00 and
            // 07:00 Jakarta time. Parse the value and compare its date in the
            // APP timezone against local today — deterministic at any hour.
            $this->assertSame(
                $today,
                Carbon::parse((string) ($row['updated_at'] ?? ''))
                    ->setTimezone(config('app.timezone'))
                    ->toDateString()
            );
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
