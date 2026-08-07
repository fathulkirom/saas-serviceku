<?php

namespace Tests\Feature\Tenant;

use Tests\TestCase;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Product;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceRequiredPart;
use App\Models\Tenant\ServicePartReturn;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

/**
 * BR-FIX-01 — Migration verification for
 * 2026_08_07_000002_add_service_required_part_id_to_service_part_returns.php
 *
 * Requirements checked:
 *  - tenant migration (runs in tenant context)
 *  - additive + nullable (guarded by hasColumn)
 *  - valid FK with nullOnDelete
 *  - existing rows safe (nullable)
 *  - rollback works (down() removes column, up() re-adds)
 */
class MigrationServicePartReturnsTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_part_returns_has_required_part_column_and_fk(): void
    {
        $this->setUpTenant();

        $this->assertTrue(
            Schema::hasColumn('service_part_returns', 'service_required_part_id'),
            'Column service_required_part_id must exist (additive migration).'
        );

        // SQLite exposes FKs via PRAGMA (Schema::hasIndex does not list FK-only
        // constraints on SQLite). Verify the FK targets service_required_parts
        // with ON DELETE SET NULL (nullOnDelete).
        $fks = \Illuminate\Support\Facades\DB::select("PRAGMA foreign_key_list('service_part_returns')");
        $fk = collect($fks)->first(fn($f) => $f->from === 'service_required_part_id');
        $this->assertNotNull($fk, 'FK service_required_part_id must be declared.');
        $this->assertEquals('service_required_parts', $fk->table);
        $this->assertEquals('SET NULL', strtoupper($fk->on_delete ?? ''), 'FK must be nullOnDelete.');
    }

    public function test_null_on_delete_and_nullable_for_existing_rows(): void
    {
        $this->setUpTenant();

        $branch = Branch::create(['name' => 'B', 'is_active' => true]);
        $customer = Customer::create(['name' => 'C', 'branch_id' => $branch->id]);
        $product = Product::create(['name' => 'P', 'branch_id' => $branch->id, 'stock_quantity' => 5]);
        $service = Service::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'created_by' => 1,
            'status' => Service::STATUS_DIKERJAKAN,
        ]);
        $part = ServiceRequiredPart::create([
            'service_id' => $service->id,
            'product_id' => $product->id,
            'part_name' => 'P',
            'qty' => 1,
            'status' => ServiceRequiredPart::STATUS_USED,
        ]);

        $return = ServicePartReturn::create([
            'service_id' => $service->id,
            'product_id' => $product->id,
            'service_required_part_id' => $part->id,
            'quantity' => 1,
            'reason' => 'Uji migrasi',
        ]);
        $this->assertEquals($part->id, $return->fresh()->service_required_part_id);

        // nullOnDelete: deleting the required part must NULL the link, not fail.
        $part->delete();
        $this->assertNull(
            $return->fresh()->service_required_part_id,
            'Deleting the required part must null the FK (nullOnDelete).'
        );

        // Existing-row safety: a return without a link remains valid (nullable).
        $legacy = ServicePartReturn::create([
            'service_id' => $service->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'reason' => 'Tanpa part link',
        ]);
        $this->assertNull($legacy->fresh()->service_required_part_id);
    }

    public function test_migration_up_down_up_roundtrip(): void
    {
        $this->setUpTenant();

        $migrationPath = database_path('migrations/tenant/2026_08_07_000002_add_service_required_part_id_to_service_part_returns.php');
        $this->assertFileExists($migrationPath, 'Migration file must exist in tenant migrations.');

        $migration = require $migrationPath;

        // down() → column removed (rollback safe)
        $migration->down();
        $this->assertFalse(Schema::hasColumn('service_part_returns', 'service_required_part_id'), 'down() must drop the column.');

        // up() → column re-added (rollback/re-migrate safe)
        $migration->up();
        $this->assertTrue(Schema::hasColumn('service_part_returns', 'service_required_part_id'), 'up() must re-add the column.');
    }
}
