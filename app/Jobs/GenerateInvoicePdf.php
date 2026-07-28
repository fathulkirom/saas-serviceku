<?php

namespace App\Jobs;

use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\Sale;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class GenerateInvoicePdf implements ShouldQueue
{
    use Queueable;

    public function __construct(public Sale $sale) {}

    public function handle(): void
    {
        $this->sale->load(['customer', 'items', 'branch']);

        $storeName = TenantSetting::getValue('store_name', 'ServiceKU');
        $storeAddress = TenantSetting::getValue('address', '');
        $storePhone = TenantSetting::getValue('phone', '');
        $storeLogo = TenantSetting::getValue('logo', '');
        $paperSize = TenantSetting::getValue('paper_size', 'a4');

        $pdf = Pdf::loadView('pdfs.sale-invoice', compact('sale', 'storeName', 'storeAddress', 'storePhone', 'storeLogo'));

        $sizeMap = ['thermal_80' => [0, 0, 226.77, 1000], 'thermal_58' => [0, 0, 164.41, 1000], 'a5' => 'a5'];
        $pdf->setPaper($sizeMap[$paperSize] ?? 'a4', 'portrait');

        $filename = 'invoice-' . $this->sale->id . '-' . time() . '.pdf';
        $path = storage_path('app/public/invoices/' . $filename);
        if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);

        $pdf->save($path);
        $this->sale->update(['pdf_url' => '/storage/invoices/' . $filename]);
    }
}
