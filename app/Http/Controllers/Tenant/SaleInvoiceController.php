<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Service;
use App\Models\Tenant\TenantSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class SaleInvoiceController extends Controller
{
    public function print(Sale $sale)
    {
        $sale->load(['customer', 'items', 'branch']);
        $pdf = $this->buildPdf($sale);
        return $pdf->download('invoice-' . $sale->id . '.pdf');
    }

    public function printChecklist(Sale $sale)
    {
        if (!$sale->service_id) return back()->with('error', 'Penjualan ini bukan dari servis.');

        $service = Service::with(['customer', 'technician', 'checklists.checklistTemplate.items', 'checklists.template.items', 'branch'])->findOrFail($sale->service_id);

        $storeName = TenantSetting::getValue('store_name', 'ServiceKU');
        $storeAddress = TenantSetting::getValue('address', '');
        $storeLogo = TenantSetting::getValue('logo', '');

        $pdf = Pdf::loadView('pdfs.checklist-keluar', [
            'service' => $service, 'sale' => $sale,
            'storeName' => $storeName, 'storeAddress' => $storeAddress, 'storeLogo' => $storeLogo,
        ]);

        return $pdf->download('checklist-keluar-' . $sale->id . '.pdf');
    }

    public function generatePdf(Sale $sale)
    {
        try {
            $sale->load(['customer', 'items', 'branch']);
            $storeName = TenantSetting::getValue('store_name', 'ServiceKU');
            $storeAddress = TenantSetting::getValue('address', '');
            $storePhone = TenantSetting::getValue('phone', '');
            $storeLogo = TenantSetting::getValue('logo', '');
            $paperSize = TenantSetting::getValue('paper_size', 'a4');

            $pdf = Pdf::loadView('pdfs.sale-invoice', [
                'sale' => $sale, 'storeName' => $storeName,
                'storeAddress' => $storeAddress, 'storePhone' => $storePhone, 'storeLogo' => $storeLogo,
            ]);

            $paperSizeMap = ['thermal_80' => [0, 0, 226.77, 1000], 'thermal_58' => [0, 0, 164.41, 1000], 'a5' => 'a5', 'a4' => 'a4'];
            $paperConfig = $paperSizeMap[$paperSize] ?? 'a4';
            $pdf->setPaper($paperConfig, 'portrait');

            $filename = 'invoice-' . $sale->id . '-' . time() . '.pdf';
            $path = storage_path('app/public/invoices/' . $filename);
            if (!is_dir(dirname($path))) mkdir(dirname($path), 0755, true);
            $pdf->save($path);

            $sale->update(['pdf_url' => '/storage/invoices/' . $filename]);
        } catch (\Exception $e) {
            report($e);
        }
    }

    private function buildPdf(Sale $sale)
    {
        $storeName = TenantSetting::getValue('store_name', 'ServiceKU');
        $storeAddress = TenantSetting::getValue('address', '');
        $storePhone = TenantSetting::getValue('phone', '');
        $storeLogo = TenantSetting::getValue('logo', '');

        return Pdf::loadView('pdfs.sale-invoice', [
            'sale' => $sale, 'storeName' => $storeName,
            'storeAddress' => $storeAddress, 'storePhone' => $storePhone, 'storeLogo' => $storeLogo,
        ]);
    }
}
