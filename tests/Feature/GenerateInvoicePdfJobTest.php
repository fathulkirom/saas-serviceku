<?php

namespace Tests\Feature;

use App\Jobs\GenerateInvoicePdf;
use App\Models\Tenant\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfWrapper;
use Mockery;
use Tests\TestCase;

class GenerateInvoicePdfJobTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpTenant();
    }

    public function test_generate_invoice_job_passes_sale_to_view_and_updates_pdf_url(): void
    {
        $sale = $this->createSale([
            'status' => Sale::STATUS_PAID,
            'payment_method' => 'cash',
            'paid_amount' => 100000,
            'change' => 0,
        ]);

        $pdfMock = Mockery::mock(DomPdfWrapper::class);
        $pdfMock->shouldReceive('setPaper')
            ->once()
            ->with('a4', 'portrait');

        $pdfMock->shouldReceive('save')
            ->once()
            ->withArgs(function (string $path) use ($sale): bool {
                return str_contains($path, '/app/public/invoices/invoice-' . $sale->id . '-')
                    && str_ends_with($path, '.pdf');
            });

        Pdf::shouldReceive('loadView')
            ->once()
            ->with('pdfs.sale-invoice', Mockery::on(function (array $payload) use ($sale): bool {
                return isset($payload['sale'])
                    && $payload['sale']->id === $sale->id
                    && array_key_exists('storeName', $payload)
                    && array_key_exists('storeAddress', $payload)
                    && array_key_exists('storePhone', $payload)
                    && array_key_exists('storeLogo', $payload);
            }))
            ->andReturn($pdfMock);

        $job = new GenerateInvoicePdf($sale);
        $job->handle();

        $sale->refresh();

        $this->assertNotNull($sale->pdf_url);
        $this->assertStringStartsWith('/storage/invoices/invoice-' . $sale->id . '-', $sale->pdf_url);
        $this->assertStringEndsWith('.pdf', $sale->pdf_url);
    }
}
