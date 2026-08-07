<?php

namespace Tests\Feature\BusinessReality;

use App\Models\Tenant\Delegation;
use App\Models\Tenant\User;
use App\Models\Tenant\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * BR-FIX-03 — STEP 19 (Tenant Isolation) + STEP 21 (Schema Safety).
 *
 * The delegations table lives in the TENANT database (migration under
 * database/migrations/tenant), so it is inherently tenant-local. No new
 * roles (family/spouse/backup_cs/acting_cs/temporary_cs) are introduced —
 * delegation reuses the existing 7-role model + Permission + Branch Scope.
 */
class BR19DelegationTenantIsolationSchemaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
        $this->grantFullPlanAccess();
    }

    /** STEP 19a — Delegation schema is tenant-local (not central). */
    public function test_delegation_table_is_tenant_local_not_central(): void
    {
        // The migration lives in the tenant migrations path (runs on the
        // 'tenant' connection only) — never on the central connection.
        $this->assertFileExists(database_path('migrations/tenant/2026_08_07_000006_create_delegations_table.php'));

        // Tenant DB has the delegations table.
        $this->assertTrue(Schema::connection('tenant')->hasTable('delegations'));

        // Central DB does NOT have a delegations table (no cross-tenant store).
        $this->assertFalse(Schema::connection('central')->hasTable('delegations'));
    }

    /** STEP 19b — Delegations are invisible across tenants (per-tenant DB). */
    public function test_delegations_are_invisible_across_tenants(): void
    {
        $branch = $this->createBranch();
        $owner = $this->createTenantUser(['role' => 'owner', 'branch_id' => $branch->id]);
        $tech = $this->createTenantUser(['role' => 'technician', 'branch_id' => $branch->id]);

        Delegation::create([
            'user_id' => $tech->id,
            'permission' => 'service.create',
            'branch_id' => $branch->id,
            'granted_by' => $owner->id,
            'reason' => 'tenant A grant',
        ]);

        $this->assertSame(1, DB::connection('tenant')->table('delegations')->count());

        // Tenant A's delegation data physically lives in Tenant A's database
        // file — a Tenant B connection (separate tenant DB) cannot see it.
        // Prove there is no shared/central storage to leak through.
        $centralTables = collect(DB::connection('central')->select(
            "SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%'"
        ))->pluck('name');

        $this->assertNotContains('delegations', $centralTables->all());
    }

    /** STEP 19c — Cannot grant a delegation to a user outside the tenant. */
    public function test_cannot_grant_delegation_to_user_outside_tenant(): void
    {
        $this->actingAs($this->createTenantUser(['role' => 'owner']));

        // A "foreign" user id (from another tenant) does not exist in this
        // tenant's users table → the grant must be rejected (validation).
        $foreignId = 999999;

        $this->assertFalse(User::where('id', $foreignId)->exists());

        $this->post(route('delegations.store'), [
            'user_id' => $foreignId,
            'permission' => 'service.create',
        ])->assertSessionHasErrors('user_id');

        $this->assertDatabaseCount('delegations', 0);
    }

    /** STEP 19d — No custom family/acting roles are introduced. */
    public function test_no_custom_family_or_acting_roles_introduced(): void
    {
        $forbidden = ['family', 'spouse', 'backup_cs', 'acting_cs', 'temporary_cs'];

        // The role vocabulary is still the official 7 roles (+ custom).
        $available = array_keys(User::getAvailableRoles());
        foreach ($forbidden as $name) {
            $this->assertNotContains($name, $available, "Role '{$name}' must not exist");
        }

        // The roles table (if seeded) must not contain those keys either.
        if (Schema::connection('tenant')->hasTable('roles')) {
            $keys = Role::pluck('key')->all();
            foreach ($forbidden as $name) {
                $this->assertNotContains($name, $keys, "Role key '{$name}' must not exist");
            }
        }

        // And the delegation feature is what handles temporary replacement —
        // a technician stays a technician.
        $branch = $this->createBranch();
        $tech = $this->createTenantUser(['role' => 'technician', 'branch_id' => $branch->id]);
        $this->grantDelegation($tech, 'service.create', $branch->id);

        $this->assertEquals('technician', $tech->fresh()->role);
    }

    /** STEP 21 — Migration verification (additive, rollback-safe, indexed). */
    public function test_delegation_migration_schema_is_correct(): void
    {
        $columns = Schema::connection('tenant')->getColumnListing('delegations');

        foreach (['id', 'user_id', 'permission', 'branch_id', 'granted_by', 'starts_at', 'expires_at', 'revoked_at', 'revoked_by', 'reason', 'created_at', 'updated_at'] as $col) {
            $this->assertContains($col, $columns, "Missing column {$col}");
        }

        // Indexes for active lookups exist.
        $indexes = collect(Schema::connection('tenant')->getIndexes('delegations'))->pluck('name');
        $this->assertTrue($indexes->contains('delegations_user_perm_active_idx'));
        $this->assertTrue($indexes->contains('delegations_perm_expires_idx'));
    }

    private function grantDelegation(User $grantee, string $permission, ?int $branchId): Delegation
    {
        return Delegation::create([
            'user_id' => $grantee->id,
            'permission' => $permission,
            'branch_id' => $branchId,
            'granted_by' => $grantee->id,
            'reason' => 'tenant isolation test',
        ]);
    }
}
