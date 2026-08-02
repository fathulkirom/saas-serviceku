<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\WorkOrder;
use App\Models\Tenant\Worklog;
use App\Models\Tenant\PartBooking;
use App\Models\Tenant\ServiceReopen;
use App\Models\Tenant\PriceChangeRequest;
use Illuminate\Http\Request;

/**
 * Daily Operations Controller — Sprint 7.4B.
 */
class DailyOperationsController extends Controller
{
    // ======== WORKLOG ========
    public function addWorklog(Request $request, WorkOrder $workOrder)
    {
        $data = $request->validate(['description' => 'required|string']);
        $workOrder->addWorklog($data['description']);
        return back()->with('success', 'Log pekerjaan ditambahkan.');
    }

    // ======== PAUSE / RESUME ========
    public function pauseRepair(WorkOrder $workOrder) { $workOrder->pause(); return back()->with('success', 'Pekerjaan ditunda.'); }
    public function resumeRepair(WorkOrder $workOrder) { $workOrder->resume(); return back()->with('success', 'Pekerjaan dilanjutkan.'); }

    // ======== FINISH WORK ORDER ========
    public function finishWorkOrder(WorkOrder $workOrder)
    {
        $workOrder->finish();
        return back()->with('success', 'Pekerjaan selesai.');
    }

    // ======== PART BOOKING ========
    public function bookPart(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'service_id' => 'nullable|exists:services,id',
            'quantity' => 'required|integer|min:1',
            'expires_at' => 'required|date|after:today',
        ]);

        $booking = PartBooking::create($data + ['created_by' => auth()->id()]);
        event(new \App\Events\Entity\BookingCreated($booking));
        return back()->with('success', 'Part dibooking hingga ' . $data['expires_at']);
    }

    // ======== AUDIT LOCK ========
    public function lockService(Service $service)
    {
        if (in_array($service->payment_status, ['paid']) || in_array($service->status, ['selesai', 'close'])) {
            $service->lock(auth()->id());
            return back()->with('success', 'Service dikunci. Tidak dapat diedit.');
        }
        return back()->with('error', 'Service belum bisa dikunci.');
    }

    // ======== SERVICE REOPEN ========
    public function requestReopen(Request $request, Service $service)
    {
        $data = $request->validate(['reason' => 'required|string']);
        ServiceReopen::create([
            'service_id' => $service->id,
            'reason' => $data['reason'],
            'requested_by' => auth()->id(),
        ]);
        return back()->with('success', 'Reopen requested.');
    }

    public function approveReopen(ServiceReopen $reopen)
    {
        $reopen->approve(auth()->id());
        return back()->with('success', 'Reopen disetujui. Service dapat diedit kembali.');
    }

    // ======== PRICE CHANGE APPROVAL ========
    public function requestPriceChange(Request $request, Service $service)
    {
        $data = $request->validate([
            'item_type' => 'required|in:part,service_charge',
            'old_price' => 'required|numeric',
            'new_price' => 'required|numeric',
            'reason' => 'required|string',
        ]);

        $change = PriceChangeRequest::create([
            'service_id' => $service->id,
            'item_type' => $data['item_type'],
            'old_price' => $data['old_price'],
            'new_price' => $data['new_price'],
            'reason' => $data['reason'],
            'requested_by' => auth()->id(),
        ]);

        event(new \App\Events\Entity\PriceChanged($change));
        return back()->with('success', 'Permintaan perubahan harga diajukan.');
    }

    public function approvePrice(PriceChangeRequest $change)
    {
        $change->approve(auth()->id());
        return back()->with('success', 'Perubahan harga disetujui.');
    }
}
