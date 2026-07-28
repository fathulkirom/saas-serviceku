<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\Customer;
use App\Models\Tenant\ChecklistTemplate;
use App\Models\Tenant\Product;
use App\Models\Tenant\MasterData;
use App\Services\GoogleDrivePhotoService;
use Illuminate\Http\Request;
class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Service::class);
        $query = Service::with(['customer', 'technician', 'creator', 'branch']);

        if ($request->filled('status')) $query->where('status', $request->status);
        else $query->where('status', '!=', Service::STATUS_SELESAI);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q->where('id', 'like', "%{$search}%")->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%")));
        }
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('created_at', '<=', $request->date_to);

        return inertia('Services/Index', [
            'services' => $query->latest()->paginate(15),
            'stats' => [
                'all' => Service::where('status', '!=', Service::STATUS_SELESAI)->count(),
                Service::STATUS_MENUNGGU_ALOKASI => Service::where('status', Service::STATUS_MENUNGGU_ALOKASI)->count(),
                Service::STATUS_DIKERJAKAN => Service::whereIn('status', [Service::STATUS_DITERIMA, Service::STATUS_DIKERJAKAN])->count(),
                Service::STATUS_INDENT => Service::where('status', Service::STATUS_INDENT)->count(),
                Service::STATUS_ONPARTNER => Service::where('status', Service::STATUS_ONPARTNER)->count(),
                Service::STATUS_SIAP_DIAMBIL => Service::where('status', Service::STATUS_SIAP_DIAMBIL)->count(),
                Service::STATUS_SELESAI => Service::where('status', Service::STATUS_SELESAI)->count(),
                'cancel' => Service::whereIn('status', [Service::STATUS_CANCEL, Service::STATUS_VOID, Service::STATUS_CLOSE])->count(),
            ],
            'filters' => $request->only(['status', 'search', 'date_from', 'date_to']),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Service::class);
        $driveService = new GoogleDrivePhotoService(tenancy()->tenant->id);
        return inertia('Services/Create', [
            'customers' => Customer::orderBy('name')->get(),
            'templates' => ChecklistTemplate::where('type', 'masuk')->where('is_active', true)->with('items')->get(),
            'products' => Product::where('stock_quantity', '>', 0)->get(),
            'deviceCategories' => MasterData::getByCategory('device_category'),
            'brands' => MasterData::getByCategory('brand'),
            'arrivalMethods' => MasterData::getByCategory('arrival_method'),
            'equipment' => MasterData::getByCategory('equipment'),
            'driveConnected' => $driveService->isConnected(),
        ]);
    }

    public function show(Service $service)
    {
        $this->authorize('view', $service);
        $service->load(['customer', 'technician', 'creator', 'branch', 'checklists.checklistTemplate.items', 'spareparts.product', 'indent', 'sale.items', 'parentService', 'jalurKedatangan', 'kategoriPerangkat', 'merek', 'photos.uploader', 'warrantyClaims']);

        return inertia('Services/Show', [
            'service' => $service,
            'templatesKeluar' => ChecklistTemplate::where('type', 'keluar')->where('is_active', true)->with('items')->get(),
            'templatesMasuk' => ChecklistTemplate::where('type', 'masuk')->where('is_active', true)->with('items')->get(),
            'products' => Product::where('stock_quantity', '>', 0)->get(),
            'users' => \App\Models\Tenant\User::select('id', 'name', 'role', 'active')->where('active', true)->orderBy('name')->get(),
            'previousServices' => $service->customer_id
                ? Service::with('customer')->where('customer_id', $service->customer_id)->where('status', Service::STATUS_SELESAI)->where('payment_status', 'paid')->where('id', '!=', $service->id)->latest()->take(10)->get()
                : [],
            'driveConnected' => (new GoogleDrivePhotoService(tenancy()->tenant->id))->isConnected(),
        ]);
    }

    public function edit(Service $service)
    {
        $this->authorize('update', $service);
        $service->load(['customer', 'checklists.checklistTemplate.items', 'spareparts.product']);
        $driveService = new GoogleDrivePhotoService(tenancy()->tenant->id);
        return inertia('Services/Edit', [
            'service' => $service,
            'customers' => Customer::orderBy('name')->get(),
            'templates' => ChecklistTemplate::where('type', 'masuk')->where('is_active', true)->with('items')->get(),
            'products' => Product::where('stock_quantity', '>', 0)->get(),
            'deviceCategories' => MasterData::getByCategory('device_category'),
            'brands' => MasterData::getByCategory('brand'),
            'arrivalMethods' => MasterData::getByCategory('arrival_method'),
            'equipment' => MasterData::getByCategory('equipment'),
            'driveConnected' => $driveService->isConnected(),
        ]);
    }

    public function destroy(Service $service)
    {
        $this->authorize('delete', $service);
        $service->delete();
        \App\Models\Tenant\ActivityLog::log('deleted', 'Menghapus servis #' . $service->id, $service);
        return redirect()->route('services.index')->with('success', 'Servis berhasil dihapus.');
    }

    public function update(Request $request, Service $service)
    {
        $this->authorize('update', $service);
        $validated = $request->validate([
            'problem_description' => 'nullable|string', 'condition_note' => 'nullable|string',
            'service_charge' => 'nullable|numeric|min:0', 'tipe_unit' => 'nullable|string|max:100',
            'imei_sn' => 'nullable|string|max:100', 'sandi_pola' => 'nullable|string|max:50',
            'posisi_unit' => 'nullable|in:di_toko,dibawa_pelanggan', 'kelengkapan' => 'nullable|array',
            'status' => 'nullable|in:' . implode(',', [
                Service::STATUS_MENUNGGU_ALOKASI, Service::STATUS_DITERIMA, Service::STATUS_DIAGNOSA,
                Service::STATUS_DIKERJAKAN, Service::STATUS_KONFIRMASI_PELANGGAN,
                Service::STATUS_KONFIRMASI_INTERNAL, Service::STATUS_SIAP_DIAMBIL,
                Service::STATUS_INDENT, Service::STATUS_ONPARTNER, Service::STATUS_SELESAI,
                Service::STATUS_CANCEL, Service::STATUS_VOID, Service::STATUS_CLOSE,
            ]),
        ]);

        $oldStatus = $service->status;
        $service->update($validated);
        \App\Models\Tenant\ActivityLog::log(
            isset($validated['status']) && $validated['status'] !== $oldStatus ? 'status_updated' : 'updated',
            'Update servis #' . $service->id, $service
        );

        return back()->with('success', 'Servis berhasil diperbarui.');
    }
}
