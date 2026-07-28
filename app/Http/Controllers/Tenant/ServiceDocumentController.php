<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\Product;
use App\Models\Tenant\InventoryMutation;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\SystemAlert;
use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\Commission;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceDocumentController extends Controller
{
    public function printReceipt(Service $service)
    {
        $service->load(['customer', 'technician', 'creator', 'branch', 'checklists.checklistTemplate.items', 'checklists.template.items', 'jalurKedatangan', 'kategoriPerangkat', 'merek']);

        $storeName = TenantSetting::getValue('store_name', 'ServiceKU');
        $storeAddress = TenantSetting::getValue('address', '');
        $storePhone = TenantSetting::getValue('phone', '');
        $storeLogo = TenantSetting::getValue('logo', '');
        $paperSize = TenantSetting::getValue('paper_size', 'a4');

        $pdf = Pdf::loadView('pdfs.service-receipt', compact('service', 'storeName', 'storeAddress', 'storePhone', 'storeLogo', 'paperSize'));

        $sizeMap = ['thermal_80' => [0, 0, 226.77, 1000], 'thermal_58' => [0, 0, 164.41, 1000], 'a5' => 'a5'];
        $pdf->setPaper($sizeMap[$paperSize] ?? 'a4', 'portrait');

        return $pdf->stream('tanda-terima-servis-' . $service->id . '.pdf');
    }

    public function complete(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        $validated = $request->validate([
            'checklist_template_id' => 'nullable|exists:checklist_templates,id',
            'checked_items' => 'nullable|array', 'condition_note' => 'nullable|string',
            'spareparts' => 'nullable|array', 'spareparts.*.product_id' => 'required_with:spareparts|exists:products,id',
            'spareparts.*.quantity' => 'required_with:spareparts|integer|min:1',
            'service_charge' => 'nullable|numeric|min:0',
        ]);

        if ($request->filled('checklist_template_id')) {
            $service->checklists()->create(['checklist_template_id' => $validated['checklist_template_id'], 'type' => 'keluar', 'checked_items' => $validated['checked_items'] ?? [], 'notes' => $validated['condition_note'] ?? '']);
        }

        if ($request->has('spareparts')) {
            foreach ($validated['spareparts'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $service->spareparts()->create(['product_id' => $product->id, 'quantity' => $item['quantity'], 'unit_price' => $product->selling_price, 'subtotal' => $product->selling_price * $item['quantity']]);
                $product->reduceStock($item['quantity']);
                InventoryMutation::create(['branch_id' => $service->branch_id, 'product_id' => $product->id, 'type' => 'keluar', 'quantity' => $item['quantity'], 'reference_type' => 'service', 'reference_id' => (string)$service->id, 'note' => 'Sparepart servis #' . $service->id, 'created_by' => Auth::id()]);
                if ($product->isLowStock()) {
                    SystemAlert::createAlert('low_stock', "Stok {$product->name} menipis", "Sisa stok: {$product->stock_quantity}", 'warning', ['product_id' => $product->id]);
                }
            }
        }

        $totalSparepart = $service->spareparts()->sum('subtotal');
        $service->update(['service_charge' => $validated['service_charge'] ?? 0, 'total_cost' => $totalSparepart + ($validated['service_charge'] ?? 0)]);

        Commission::autoCreateForService($service);

        ActivityLog::log('completed', 'Update biaya servis #' . $service->id . ' - Total: Rp ' . number_format($service->total_cost, 0, ',', '.'), $service);
        return redirect()->route('services.show', $service->id)->with('success', 'Data servis berhasil disimpan. Lanjutkan ke pembuatan nota.');
    }
}
