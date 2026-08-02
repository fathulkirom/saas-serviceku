<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceDelivery;
use App\Models\Tenant\ServiceDiagnosis;
use App\Models\Tenant\ServiceDiagnosisHistory;
use App\Models\Tenant\ServiceWarrantyClaim;
use Illuminate\Http\Request;

/**
 * Service Exception Controller — Sprint 7.3H.
 * Warranty claims, diagnosis revision, unclaimed monitoring.
 */
class ServiceExceptionController extends Controller
{
    // ======== WARRANTY CLAIMS ========

    /** Submit warranty claim */
    public function createClaim(Request $request, Service $service)
    {
        $this->authorize('update', $service);
        $warranty = $service->warranty;
        if (!$warranty || !$warranty->isActive()) {
            return back()->with('error', 'Garansi tidak aktif atau sudah expired.');
        }

        $data = $request->validate(['problem_description' => 'required|string']);
        $claim = ServiceWarrantyClaim::create([
            'service_warranty_id' => $warranty->id,
            'customer_id' => $service->customer_id,
            'service_id' => $service->id,
            'problem_description' => $data['problem_description'],
            'status' => 'submitted',
        ]);

        event(new \App\Events\Entity\WarrantyClaimCreated($claim));
        return back()->with('success', 'Klaim garansi #' . $claim->claim_number . ' dibuat.');
    }

    /** Approve/reject claim */
    public function decideClaim(Request $request, ServiceWarrantyClaim $claim)
    {
        $this->authorize('update', $claim->service);
        $data = $request->validate([
            'decision' => 'required|in:approve,reject',
            'note' => 'nullable|string',
        ]);

        if ($data['decision'] === 'approve') {
            $claim->approve(auth()->id(), $data['note'] ?? null);
        } else {
            $claim->reject(auth()->id(), $data['note'] ?? 'Ditolak.');
        }

        return back()->with('success', 'Klaim ' . ($data['decision'] === 'approve' ? 'disetujui' : 'ditolak') . '.');
    }

    // ======== DIAGNOSIS REVISION ========

    /** Record diagnosis change (append-only history) */
    public function reviseDiagnosis(Request $request, Service $service)
    {
        $this->authorize('update', $service);
        $data = $request->validate([
            'findings' => 'required|string',
            'cause' => 'nullable|string',
            'solution' => 'required|string',
            'reason' => 'required|string',
        ]);

        $oldDiagnosis = $service->diagnosis?->only(['findings', 'cause', 'solution']) ?? [];

        // Update current diagnosis
        $service->diagnosis()->updateOrCreate(
            ['service_id' => $service->id],
            [
                'findings' => $data['findings'],
                'cause' => $data['cause'] ?? null,
                'solution' => $data['solution'],
                'diagnosed_by' => auth()->id(),
            ]
        );

        // Record immutable history
        ServiceDiagnosisHistory::record(
            $service->id,
            $oldDiagnosis,
            ['findings' => $data['findings'], 'cause' => $data['cause'], 'solution' => $data['solution']],
            $data['reason'],
            auth()->id()
        );

        return back()->with('success', 'Diagnosis direvisi. Riwayat tersimpan.');
    }

    // ======== UNCLAIMED DEVICES ========

    /** List unclaimed devices */
    public function unclaimed()
    {
        $deliveries = ServiceDelivery::with(['service.customer', 'service.device'])
            ->whereNull('picked_up_at')
            ->whereNotNull('ready_at')
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'service_id' => $d->service_id,
                'customer_name' => $d->service->customer?->name,
                'device' => $d->service->device?->brand . ' ' . $d->service->device?->model,
                'ready_since' => $d->ready_at->format('d M Y'),
                'days_waiting' => (int) $d->ready_at->diffInDays(now()),
                'level' => match (true) {
                    (int) $d->ready_at->diffInDays(now()) >= 90 => 'abandoned',
                    (int) $d->ready_at->diffInDays(now()) >= 30 => 'attention',
                    (int) $d->ready_at->diffInDays(now()) >= 7 => 'warning',
                    default => 'normal',
                },
            ]);

        return inertia('Monitoring/Unclaimed', [
            'unclaimed' => $deliveries,
            'stats' => [
                'total' => $deliveries->count(),
                'warning_7' => $deliveries->where('level', 'warning')->count(),
                'attention_30' => $deliveries->where('level', 'attention')->count(),
                'abandoned_90' => $deliveries->where('level', 'abandoned')->count(),
            ],
        ]);
    }
}
