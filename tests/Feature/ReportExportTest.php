<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Tenant\Sale;

/**
 * Tahap 2.5.9 — Export data (CSV/PDF) via /reports/export/{type}.
 */
class ReportExportTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    private function createSaleWithData(): Sale
    {
        $branch = $this->createBranch();
        $customer = $this->createCustomer();

        return Sale::create([
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'sale_type' => Sale::SALE_TYPE_LANGSUNG,
            'status' => Sale::STATUS_PAID,
            'subtotal' => 50000,
            'total' => 50000,
            'payment_method' => 'cash',
            'paid_amount' => 50000,
            'change' => 0,
        ]);
    }

    public function test_sales_csv_export_returns_csv()
    {
        $this->actingAs($this->createTenantUser());
        $this->createSaleWithData();

        $response = $this->get('/reports/export/sales?format=csv');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('Pelanggan', $response->streamedContent());
        $this->assertStringContainsString('Test Customer', $response->streamedContent());
    }

    public function test_services_csv_export_returns_csv()
    {
        $this->actingAs($this->createTenantUser());
        $this->createService();

        $response = $this->get('/reports/export/services?format=csv');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('Pelanggan', $response->streamedContent());
    }

    public function test_export_redirects_when_unauthenticated()
    {
        $response = $this->get('/reports/export/sales?format=csv');

        // Tidak login -> redirect ke login
        $response->assertRedirect();
    }
}
