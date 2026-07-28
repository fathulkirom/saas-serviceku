<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Indent;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Service;
use App\Models\Tenant\ActivityLog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class IndentController extends Controller
{
    public function index(Request $request)
    {
        return redirect()->route('servis-tools.index')->with('info', 'Manajemen indent sudah dipindah ke Servis Tools.');
    }

    public function create(Request $request)
    {
        $customers = Customer::orderBy('name')->get();
        $services = Service::whereIn('status', [Service::STATUS_DIKERJAKAN, Service::STATUS_INDENT])
            ->with('customer')
            ->get();

        return inertia('Indents/Create', [
            'customers' => $customers,
            'services' => $services,
            'serviceId' => $request->get('service_id'),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Indent::class);
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'service_id' => 'nullable|exists:services,id',
            'product_name' => 'required|string|max:255',
            'qty' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'cost_estimate' => 'nullable|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
        ]);

        $user = auth()->user();

        $validated['branch_id'] = $user->branch_id;
        $validated['qty'] = $validated['qty'] ?? 1;
        $validated['status'] = Indent::STATUS_PENDING;

        $indent = Indent::create($validated);

        // Jika terkait dengan servis, update status servis menjadi indent
        if ($validated['service_id']) {
            Service::where('id', $validated['service_id'])->update([
                'status' => Service::STATUS_INDENT,
                'indent_id' => $indent->id,
            ]);
        }

        ActivityLog::log('indent_created', 'Inden #' . $indent->id . ' - ' . $validated['product_name'], $indent);

        return redirect()->route('indents.index')->with('success', 'Inden berhasil dibuat.');
    }

    public function edit(Indent $indent)
    {
        $indent->load(['customer', 'service', 'sales', 'branch']);
        $customers = Customer::orderBy('name')->get();
        $services = Service::whereIn('status', [Service::STATUS_DIKERJAKAN, Service::STATUS_INDENT])
            ->with('customer')
            ->get();
        return inertia('Indents/Edit', [
            'indent' => $indent,
            'customers' => $customers,
            'services' => $services,
        ]);
    }

    public function destroy(Indent $indent)
    {
        $this->authorize('delete', $indent);
        if ($indent->service_id) {
            Service::where('id', $indent->service_id)->update([
                'status' => Service::STATUS_DIKERJAKAN,
                'indent_id' => null,
            ]);
        }
        $indent->delete();
        ActivityLog::log('indent_deleted', 'Menghapus inden #' . $indent->id, $indent);
        return redirect()->route('indents.index')->with('success', 'Inden berhasil dihapus.');
    }

    public function show(Indent $indent)
    {
        $indent->load(['customer', 'service', 'sales', 'branch']);
        return inertia('Indents/Show', ['indent' => $indent]);
    }

    public function update(Request $request, Indent $indent)
    {
        $this->authorize('update', $indent);
        $validated = $request->validate([
            'status' => 'required|in:' . implode(',', [Indent::STATUS_PENDING, Indent::STATUS_DIPROSES, Indent::STATUS_SELESAI, Indent::STATUS_BATAL]),
            'cost_estimate' => 'nullable|numeric|min:0',
            'deposit' => 'nullable|numeric|min:0',
        ]);

        $oldStatus = $indent->status;
        $indent->update($validated);

        // Jika indent selesai, update service kembali ke dikerjakan
        if ($validated['status'] === Indent::STATUS_SELESAI && $indent->service_id) {
            Service::where('id', $indent->service_id)->update([
                'status' => Service::STATUS_DIKERJAKAN,
            ]);
        }

        // Jika indent dibatalkan, update service kembali ke dikerjakan
        if ($validated['status'] === Indent::STATUS_BATAL && $indent->service_id) {
            Service::where('id', $indent->service_id)->update([
                'status' => Service::STATUS_DIKERJAKAN,
                'indent_id' => null,
            ]);
        }

        ActivityLog::log('indent_updated', 'Status inden #' . $indent->id . ': ' . $oldStatus . ' → ' . $validated['status'], $indent);

        return back()->with('success', 'Status inden diperbarui.');
    }

    /**
     * Cetak nota DP inden.
     */
    public function printNota(Indent $indent)
    {
        $indent->load(['customer', 'service', 'branch']);

        $storeName = \App\Models\Tenant\TenantSetting::getValue('store_name', 'ServiceKU');
        $storeAddress = \App\Models\Tenant\TenantSetting::getValue('address', '');
        $storePhone = \App\Models\Tenant\TenantSetting::getValue('phone', '');
        $storeLogo = \App\Models\Tenant\TenantSetting::getValue('logo', '');
        $whatsappNumber = \App\Models\Tenant\TenantSetting::getValue('whatsapp_number', '');
        $paperSize = \App\Models\Tenant\TenantSetting::getValue('paper_size', 'a4');

        $pdf = Pdf::loadView('pdfs.indent-nota', [
            'indent' => $indent,
            'storeName' => $storeName,
            'storeAddress' => $storeAddress,
            'storePhone' => $storePhone,
            'storeLogo' => $storeLogo,
            'whatsappNumber' => $whatsappNumber,
        ]);

        $paperSizeMap = [
            'thermal_80' => [0, 0, 226.77, 1000],
            'thermal_58' => [0, 0, 164.41, 1000],
            'a5' => 'a5',
            'a4' => 'a4',
        ];

        $paperConfig = $paperSizeMap[$paperSize] ?? 'a4';
        if (is_array($paperConfig)) {
            $pdf->setPaper($paperConfig, 'portrait');
        } else {
            $pdf->setPaper($paperConfig, 'portrait');
        }

        return $pdf->stream('nota-inden-' . $indent->id . '.pdf');
    }
}
