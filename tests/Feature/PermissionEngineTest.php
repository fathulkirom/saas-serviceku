<?php

namespace Tests\Feature;

use App\Models\Tenant\Permission;
use App\Models\Tenant\Role;
use App\Models\Tenant\User;
use App\Services\RoleService;
use Database\Seeders\Tenant\PermissionEngineSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class PermissionEngineTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tables for testing — tenant migrations not auto-loaded by RefreshDatabase
        if (! Schema::hasTable('permissions')) {
            if (! Schema::hasColumn('users', 'role')) {
                Schema::table('users', function ($table) {
                    $table->string('role')->default('cs');
                    $table->boolean('active')->default(true);
                });
            }

            Schema::create('permissions', function ($table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('label');
                $table->string('module');
                $table->string('action');
                $table->string('description')->nullable();
                $table->timestamps();
            });
            Schema::create('roles', function ($table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('label');
                $table->boolean('is_system')->default(false);
                $table->text('description')->nullable();
                $table->timestamps();
            });
            Schema::create('role_permission', function ($table) {
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->foreignId('permission_id')->constrained('permissions')->cascadeOnDelete();
                $table->primary(['role_id', 'permission_id']);
            });
            Schema::create('user_role', function ($table) {
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete();
                $table->primary(['user_id', 'role_id']);
                $table->timestamps();
            });
        }

        $this->seed(PermissionEngineSeeder::class);
    }

    #[Test]
    public function permissions_are_seeded_correctly(): void
    {
        $this->assertGreaterThan(70, Permission::count());
        $this->assertNotNull(Permission::where('key', 'service.void')->first());
        $this->assertNotNull(Permission::where('key', 'customer.create')->first());
    }

    #[Test]
    public function roles_are_seeded_with_permissions(): void
    {
        $ownerRole = Role::where('key', 'owner')->first();
        $this->assertNotNull($ownerRole);
        $this->assertTrue($ownerRole->isSystem());
        $this->assertTrue($ownerRole->permissions()->count() > 30);

        $techRole = Role::where('key', 'technician')->first();
        $this->assertTrue($techRole->hasPermission('service.work'));
        $this->assertFalse($techRole->hasPermission('finance.manage'));
    }

    #[Test]
    public function cashier_has_sales_permissions_only(): void
    {
        $role = Role::where('key', 'cashier')->first();
        $this->assertTrue($role->hasPermission('sales.create'));
        $this->assertTrue($role->hasPermission('cash_register.manage'));
        $this->assertFalse($role->hasPermission('service.work'));
    }

    #[Test]
    public function legacy_role_fallback_works(): void
    {
        $user = User::create([
            'name' => 'Test Owner', 'email' => 'owner_test@test.com',
            'password' => bcrypt('password'), 'role' => 'owner', 'active' => true,
        ]);
        $this->assertTrue($user->canViaPermission('user.delete'));
    }

    #[Test]
    public function system_role_cannot_be_deleted(): void
    {
        $this->expectException(\RuntimeException::class);
        (new RoleService)->deleteRole(Role::where('key', 'owner')->first());
    }

    #[Test]
    public function permission_check_by_role(): void
    {
        $owner = User::create([
            'name' => 'Owner', 'email' => 'o@t.com', 'password' => bcrypt('pw'),
            'role' => 'owner', 'active' => true,
        ]);
        $tech = User::create([
            'name' => 'Tech', 'email' => 't@t.com', 'password' => bcrypt('pw'),
            'role' => 'technician', 'active' => true,
        ]);
        $cashier = User::create([
            'name' => 'Cashier', 'email' => 'c@t.com', 'password' => bcrypt('pw'),
            'role' => 'cashier', 'active' => true,
        ]);

        $this->assertTrue($owner->canViaPermission('service.void'));
        $this->assertTrue($tech->canViaPermission('service.work'));
        $this->assertFalse($tech->canViaPermission('service.void'));
        $this->assertFalse($cashier->canViaPermission('service.work'));
    }
}
