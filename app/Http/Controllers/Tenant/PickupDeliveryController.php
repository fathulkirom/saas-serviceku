<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\PickupDelivery;
use App\Models\Tenant\Service;
use App\Models\Tenant\ActivityLog;
use Illuminate\Http\Request;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class PickupDeliveryController extends Controller
{
    public function index()
    {
        return redirect()->route('servis-tools.index')->with('info', 'Pickup & delivery sudah dipindah ke Servis Tools.');
    }

    public function create()
    {
        $services = Service::with('customer')
            ->where('branch_id', auth()->user()->branch_id)
            ->active()->get();
        return inertia('PickupDeliveries/Create', ['services' => $services]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'type' => 'required|in:pickup,delivery',
            'address' => 'nullable|string',
            'scheduled_at' => 'nullable|date',
            'pic_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
        ]);
        $validated['created_by'] = auth()->id();
        $validated['branch_id'] = auth()->user()->branch_id;
        PickupDelivery::create($validated);
        ActivityLog::log('pickup_delivery', 'Buat jadwal ' . $validated['type']);
        return redirect()->route('pickup-deliveries.index')->with('success', 'Jadwal berhasil dibuat.');
    }

    public function updateStatus(Request $request, PickupDelivery $pickupDelivery)
    {
        $validated = $request->validate([
            'status' => 'required|in:dijadwalkan,dijemput,diantar,sampai',
        ]);
        $pickupDelivery->update(['status' => $validated['status']]);
        return back()->with('success', 'Status berhasil diupdate.');
    }

    public function destroy(PickupDelivery $pickupDelivery)
    {
        $pickupDelivery->delete();
        return back()->with('success', 'Jadwal berhasil dihapus.');
    }
}
