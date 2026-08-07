<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceDelivery;
use App\Models\Tenant\ServiceDiagnosis;
use App\Models\Tenant\ServiceDiagnosisHistory;
use App\Models\Tenant\ServiceWarrantyClaim;
use App\Services\BranchAccessService;
use App\Services\WarrantyService;
use Illuminate\Http\Request;

/**
 * Service Exception Controller — Sprint 7.3H.
 * Warranty claims, diagnosis revision, unclaimed monitoring.
 */
class ServiceExceptionController extends Controller
{
    // ======== WARRANTY CLAIMS ========

    /**
     * Submit warranty claim — BR-FIX-04: canonical flow (eligibility →
     * ServiceWarrantyClaim → NEW linked rework Service). The previous
     * `$service->warranty` relation did not exist and always bailed; fixed.
     */
    public function createClaim(Request $request, Service $service)
    {
        $this->authorize('create', Service::class);

        $user = auth()->user();
        $custodyBranchId = $service->currentCustodyBranchId() ?? $service->branch_id;
        if (!BranchAccessService::canAccess($user, $custodyBranchId)) {
            return back()->with('error', 'Servis tidak berada pada cabang yang berwenang.');
        }

        if (!WarrantyService::isEligibleForStoreWarranty($service)) {
            return back()->with('error', 'Garansi tidak aktif atau sudah expired.');
        }

        $data = $request->validate(['problem_description' => 'required|string']);
        $handlingBranchId = $request->input('branch_id') ? (int) $request->input('branch_id') : (int) ($user->branch_id ?? $custodyBranchId);

        try {
            $claim = WarrantyService::openClaim($service, $user, $data['problem_description'], $handlingBranchId);
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Klaim garansi #' . $claim->claim_number . ' dibuat (rework #' . $claim->rework_service_id . ').');
    }

    /**
     * Approve/reject claim — BR-FIX-04.1.
     * Stronger authority (finance-level operational approval) + branch access.
     * Approve creates the NEW rework Service exactly once; reject never does.
     * The original Service is never modified.
     */
    public function decideClaim(Request $request, ServiceWarrantyClaim $claim)
    {
        $user = auth()->user();
        if (!$user->canManageFinance()) {
            abort(403, 'Tidak berwenang menyetujui/menolak klaim garansi.');
        }

        $custody = $claim->service?->currentCustodyBranchId() ?? $claim->service?->branch_id;
        if (!BranchAccessService::canAccess($user, $custody)) {
            abort(403, 'Klaim di luar jangkauan cabang Anda.');
        }

        $data = $request->validate([
            'decision' => 'required|in:approve,reject',
            'note' => 'nullable|string|required_if:decision,reject',
        ], [
            'note.required_if' => 'Alasan penolakan wajib diisi.',
        ]);

        try {
            if ($data['decision'] === 'approve') {
                $claim = WarrantyService::approveClaim($claim, $user, $data['note'] ?? null);
            } else {
                $claim = WarrantyService::rejectClaim($claim, $user, $data['note']);
            }
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with(
            'success',
            $data['decision'] === 'approve'
                ? 'Klaim disetujui' . ($claim->rework_service_id ? ' — rework #' . $claim->rework_service_id . ' dibuat.' : '.')
                : 'Klaim ditolak.'
        );
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
