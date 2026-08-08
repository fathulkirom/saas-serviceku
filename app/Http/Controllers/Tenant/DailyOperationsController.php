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

    // ======== SERVICE REOPEN (BR-020) ========
    public function requestReopen(Request $request, Service $service)
    {
        $user = auth()->user();
        abort_unless(in_array($user->role, ['owner', 'admin', 'manager'], true), 403, 'Tidak berwenang meminta reopen.');

        $data = $request->validate([
            'reason' => 'required|string|max:500',
            'type'   => 'required|in:administrative,rework',
        ]);

        // Guard: don't allow reopening a service that already has a pending reopen.
        $existing = ServiceReopen::where('service_id', $service->id)
            ->where('status', 'pending')->exists();
        if ($existing) {
            return back()->with('error', 'Service ini sudah memiliki permintaan reopen yang pending.');
        }

        // Capture snapshot before any changes.
        $snapshot = $service->only(['id', 'status', 'total_cost', 'service_charge', 'technician_id', 'resolution']);

        ServiceReopen::create([
            'service_id'       => $service->id,
            'reason'           => $data['reason'],
            'type'             => $data['type'],
            'requested_by'     => auth()->id(),
            'service_snapshot' => $snapshot,
        ]);

        $typeLabel = $data['type'] === 'rework' ? 'Pekerjaan Ulang' : 'Administratif';
        return back()->with('success', "Permintaan reopen ({$typeLabel}) diajukan. Menunggu approval.");
    }

    public function approveReopen(ServiceReopen $reopen)
    {
        $user = auth()->user();
        abort_unless(in_array($user->role, ['owner', 'admin', 'manager'], true), 403, 'Tidak berwenang menyetujui reopen.');

        $reopen->approve(auth()->id());
        return back()->with('success', 'Reopen disetujui. Service dapat diedit kembali. Riwayat sebelum reopen tetap tersimpan.');
    }

    public function rejectReopen(Request $request, ServiceReopen $reopen)
    {
        $user = auth()->user();
        abort_unless(in_array($user->role, ['owner', 'admin', 'manager'], true), 403, 'Tidak berwenang.');

        $data = $request->validate(['rejection_reason' => 'required|string|max:500']);
        $reopen->reject(auth()->id(), $data['rejection_reason']);
        return back()->with('success', 'Permintaan reopen ditolak.');
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
