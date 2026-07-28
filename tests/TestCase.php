<?php

namespace Tests;

use App\Models\Tenant;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Artisan;

abstract class TestCase extends BaseTestCase
{
    protected ?Tenant $testTenant = null;

    protected function setUp(): void
    {
        parent::setUp();

        // Run central migrations on BOTH sqlite and central connections
        // (SQLite :memory: creates separate DB per connection, so both need migrations)
        foreach (['sqlite', 'central'] as $connection) {
            Artisan::call('migrate', [
                '--path' => 'database/migrations',
                '--database' => $connection,
                '--force' => true,
            ]);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\Seeders\PlanSeeder',
            '--database' => 'central',
            '--force' => true,
        ]);
    }

    protected function setUpTenant(): Tenant
    {
        $tenant = Tenant::create([
            'id' => 'test-' . uniqid(),
            'tenant_name' => 'Test Toko',
            'plan_id' => 1,
            'data' => [],
        ]);

        $dbPath = database_path('testing_tenant_' . $tenant->id . '.sqlite');
        config(['database.connections.tenant' => [
            'driver' => 'sqlite',
            'database' => $dbPath,
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]]);

        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        touch($dbPath);

        tenancy()->initialize($tenant);

        Artisan::call('migrate', [
            '--path' => 'database/migrations/tenant',
            '--database' => 'tenant',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\Seeders\MasterDataSeeder',
            '--database' => 'tenant',
            '--force' => true,
        ]);

        $this->testTenant = $tenant;

        return $tenant;
    }

    protected function tearDown(): void
    {
        if ($this->testTenant) {
            tenancy()->end();
            $dbPath = database_path('testing_tenant_' . $this->testTenant->id . '.sqlite');
            if (file_exists($dbPath)) {
                unlink($dbPath);
            }
        }

        parent::tearDown();
    }

    protected function createTenantUser(array $attributes = []): \App\Models\Tenant\User
    {
        return \App\Models\Tenant\User::create(array_merge([
            'name' => 'Test User',
            'email' => 'user_' . uniqid() . '@test.com',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'active' => true,
        ], $attributes));
    }

    protected function createCustomer(array $attributes = []): \App\Models\Tenant\Customer
    {
        return \App\Models\Tenant\Customer::create(array_merge([
            'name' => 'Test Customer',
            'phone' => '08123456789',
        ], $attributes));
    }

    protected function createBranch(array $attributes = []): \App\Models\Tenant\Branch
    {
        return \App\Models\Tenant\Branch::create(array_merge([
            'name' => 'Cabang Utama',
            'address' => 'Jl. Test No. 1',
            'is_active' => true,
        ], $attributes));
    }

    protected function createService(array $attributes = []): \App\Models\Tenant\Service
    {
        if (!isset($attributes['customer_id'])) {
            $attributes['customer_id'] = $this->createCustomer()->id;
        }
        if (!isset($attributes['branch_id'])) {
            $attributes['branch_id'] = $this->createBranch()->id;
        }
        if (!isset($attributes['created_by'])) {
            $attributes['created_by'] = \App\Models\Tenant\User::first()?->id ?? 1;
        }
        return \App\Models\Tenant\Service::create(array_merge([
            'status' => \App\Models\Tenant\Service::STATUS_MENUNGGU_ALOKASI,
            'problem_description' => 'Test problem description',
        ], $attributes));
    }

    protected function createSale(array $attributes = []): \App\Models\Tenant\Sale
    {
        if (!isset($attributes['customer_id'])) {
            $attributes['customer_id'] = $this->createCustomer()->id;
        }
        if (!isset($attributes['branch_id'])) {
            $attributes['branch_id'] = $this->createBranch()->id;
        }
        return \App\Models\Tenant\Sale::create(array_merge([
            'sale_type' => \App\Models\Tenant\Sale::SALE_TYPE_LANGSUNG,
            'status' => \App\Models\Tenant\Sale::STATUS_DRAFT,
            'subtotal' => 100000,
            'total' => 100000,
        ], $attributes));
    }
}
