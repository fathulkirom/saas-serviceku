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
        $this->authorize('update', $service);
        $data = $request->validate([
            'received_by' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:20',
            'receiver_relation' => 'nullable|string|in:self,family,friend,staff',
            'identity_type' => 'nullable|string',
            'identity_number' => 'nullable|string',
            'signature_image' => 'nullable|string',
            'handover_photo' => 'nullable|string',
        ]);

        $delivery = ServiceDelivery::firstOrCreate(['service_id' => $service->id]);
        $delivery->complete(
            $data['received_by'],
            $data['receiver_phone'],
            $data
        );

        // Auto-create warranty
        $durationDays = $service->warranty_days ?? 30;
        $warranty = ServiceWarranty::createFromService($service, (int) $durationDays);

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
