<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceDelivery;
use App\Models\Tenant\ServiceWarranty;
use Illuminate\Http\Request;

/**
 * Service Delivery Controller — Sprint 7.3G.
 * Handles pickup verification, payment check, warranty creation.
 */
class ServiceDeliveryController extends Controller
{
    /** Mark service as ready for pickup */
    public function markReady(Service $service)
    {
        $this->authorize('update', $service);
        ServiceDelivery::firstOrCreate(
            ['service_id' => $service->id],
            ['ready_at' => now(), 'handled_by' => auth()->id()]
        );
        event(new \App\Events\Entity\ServiceReadyPickup($service));
        return back()->with('success', 'Service siap diambil.');
    }

    /** Verify payment before pickup */
    public function verifyPayment(Service $service)
    {
        $this->authorize('update', $service);
        $delivery = ServiceDelivery::where('service_id', $service->id)->firstOrFail();
        $delivery->verifyPayment(auth()->id());
        event(new \App\Events\Entity\PaymentVerified($delivery));
        return back()->with('success', 'Pembayaran terverifikasi.');
    }

    /** Complete pickup */
    public function pickup(Request $request, Service $service)
    {
        $user = auth()->user();

        // BR-FIX-02 (BR-004): pickup follows CUSTODY, not origin.
        // A service entered at Branch A that was transferred & received at
        // Branch B is picked up at B. Origin (service.branch_id) is preserved.
        $custodyBranchId = (int) ($service->currentCustodyBranchId() ?? $service->branch_id);

        // BR-FIX-03 (BR-001): pickup is gated by the service.pickup capability,
        // scoped to the CUSTODY branch (delegation must cover it). Role-based
        // grants require branch access; a granular delegation is honored only
        // when its branch scope matches the custody branch.
        if (!$user->canViaPermissionInBranch('service.pickup', $custodyBranchId)) {
            abort(403, 'Anda tidak memiliki izin untuk memproses pickup.');
        }

        // Precondition: service must be marked ready
        $delivery = ServiceDelivery::where('service_id', $service->id)->first();
        if (!$delivery || !$delivery->ready_at) {
            return back()->with('error', 'Servis belum ditandai siap diambil.');
        }

        // Idempotency: if already picked up, reject
        if ($delivery->picked_up_at) {
            return back()->with('error', 'Servis sudah diserahkan pada ' . $delivery->picked_up_at->format('d/m/Y H:i') . '.');
        }

        $data = $request->validate([
            'received_by' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'receiver_relation' => 'nullable|string|in:self,family,friend,staff',
            'identity_type' => 'nullable|string',
            'identity_number' => 'nullable|string',
            'signature_image' => 'nullable|string',
            'handover_photo' => 'nullable|string',
        ]);

        $delivery->complete(
            $data['received_by'],
            $data['receiver_phone'],
            $data
        );

        // BR-FIX-02: record the custody/pickup branch (may differ from origin).
        $delivery->update(['pickup_branch_id' => $custodyBranchId]);

        $service->update(['status' => \App\Models\Tenant\Service::STATUS_DIAMBIL]);

        \App\Models\Tenant\ActivityLog::log('pickup', "Pickup oleh {$data['received_by']} untuk servis #{$service->tracking_code} di cabang #{$custodyBranchId}", $service, ['pickup_branch_id' => $custodyBranchId]);

        // Auto-create warranty — PILOT-READY-01: warranty_days may be unset (0)
        // on services taken in without an explicit warranty; default to 30 so a
        // fresh service never gets a meaningless 0-day (instantly expired) warranty.
        $durationDays = (int) ($service->warranty_days ?? 0);
        if ($durationDays <= 0) {
            $durationDays = 30;
        }
        $warranty = ServiceWarranty::createFromService($service, $durationDays);

        event(new \App\Events\Entity\PickupCompleted($delivery));
        event(new \App\Events\Entity\WarrantyCreated($warranty));
        event(new \App\Events\Entity\ServiceDelivered($service));

        return back()->with('success', 'Unit berhasil diserahkan. Garansi ' . $durationDays . ' hari aktif.');
    }

    /** Customer warranty history for Customer 360 */
    public function customerWarranties($customerId)
    {
        $warranties = ServiceWarranty::whereHas('service', fn($q) => $q->where('customer_id', $customerId))
            ->with('service.device')
            ->latest()
            ->get()
            ->map(fn($w) => [
                'id' => $w->id,
                'service_id' => $w->service_id,
                'device' => $w->service->device?->brand . ' ' . $w->service->device?->model,
                'work_done' => $w->service->problem_description,
                'warranty_type' => $w->warranty_type,
                'duration_days' => $w->duration_days,
                'start_date' => $w->start_date->format('d M Y'),
                'end_date' => $w->end_date->format('d M Y'),
                'days_remaining' => $w->daysRemaining(),
                'is_active' => $w->isActive(),
                'status' => $w->status,
            ]);

        return response()->json(['warranties' => $warranties]);
    }
}
