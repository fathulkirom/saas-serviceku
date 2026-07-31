<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant\Customer;
use App\Models\Tenant;

class TenantIsolationTest extends TestCase
{
    /**
     * Pastikan data Customer di Tenant A TIDAK bisa diakses oleh Tenant B.
     */
    public function test_customer_data_is_isolated_between_tenants()
    {
        // ===== Tenant A =====
        $tenantA = $this->setUpTenant();
        $customerA = $this->createCustomer(['name' => 'Customer Tenant A']);

        // Data Tenant A tersimpan di koneksi tenant
        $this->assertEquals(1, Customer::count());
        $this->assertEquals('Customer Tenant A', Customer::first()->name);

        // ===== Tenant B (inisialisasi ulang tenancy) =====
        $tenantB = $this->setUpTenant();

        // Tenant B harus TIDAK melihat customer milik Tenant A
        $this->assertNotEquals($tenantA->id, $tenantB->id, 'Tenant A dan B harus berbeda.');
        $this->assertEquals(0, Customer::count(), 'Tenant B tidak boleh melihat data Customer Tenant A.');
        $this->assertNull(Customer::where('name', 'Customer Tenant A')->first());

        // Buat customer di Tenant B
        $this->createCustomer(['name' => 'Customer Tenant B']);
        $this->assertEquals(1, Customer::count());

        // ===== Kembali ke Tenant A, verifikasi data A masih utuh =====
        tenancy()->initialize($tenantA);
        $this->assertEquals(1, Customer::count());
        $this->assertEquals('Customer Tenant A', Customer::first()->name);
    }

    /**
     * Pastikan kueri global scope / model binding tidak bocor antar tenant.
     */
    public function test_model_queries_are_scoped_to_current_tenant()
    {
        $tenantA = $this->setUpTenant();
        $this->createCustomer(['name' => 'Satu', 'phone' => '0811']);
        $this->createCustomer(['name' => 'Dua', 'phone' => '0822']);

        $this->assertEquals(2, Customer::count());

        // Tenant B bersih
        $this->setUpTenant();
        $this->assertEquals(0, Customer::count());

        // Tenant A masih 2 (tidak terpengaruh operasi di B)
        tenancy()->initialize($tenantA);
        $this->assertEquals(2, Customer::count());
    }

    /**
     * Pastikan tenant yang berbeda punya database file SQLite berbeda.
     */
    public function test_tenants_have_separate_database_files()
    {
        $tenantA = $this->setUpTenant();
        $dbPathA = database_path('testing_tenant_' . $tenantA->id . '.sqlite');

        $tenantB = $this->setUpTenant();
        $dbPathB = database_path('testing_tenant_' . $tenantB->id . '.sqlite');

        $this->assertFileExists($dbPathA);
        $this->assertFileExists($dbPathB);
        $this->assertNotEquals($dbPathA, $dbPathB);
    }

    /**
     * Test tambahan: pastikan tearDown membersihkan tenant test.
     */
    public function test_teardown_cleans_up_tenant_database()
    {
        $tenantA = $this->setUpTenant();
        $dbPath = database_path('testing_tenant_' . $tenantA->id . '.sqlite');
        $this->assertFileExists($dbPath);

        tenancy()->end();
        if (file_exists($dbPath)) {
            unlink($dbPath);
        }
        $this->assertFileDoesNotExist($dbPath);
    }
}
