<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\ActivityLog;
use App\Services\BranchAccessService;
use App\Services\WarrantyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceClaimController extends Controller
{
    /**
     * BR-FIX-04.1 — Canonical warranty claim OPENING (complaint recording only).
     * Creates a `submitted` claim; NO approval, NO rework Service yet. An
     * authorized approval (decideClaim) creates the rework later.
     */
    public function createWarrantyClaim(Request $request, Service $service)
    {
        $user = Auth::user();

        $this->authorize('create', Service::class);

        // BR-FIX-02/04 — branch-safe: only a user with access to the service's
        // custody branch may open a claim (legit cross-branch allowed).
        $custodyBranchId = $service->currentCustodyBranchId() ?? $service->branch_id;
        if (!BranchAccessService::canAccess($user, $custodyBranchId)) {
            return back()->with('error', 'Servis tidak berada pada cabang yang berwenang.');
        }

        // Canonical eligibility — backend source of truth (no frontend text).
        if (!WarrantyService::isEligibleForStoreWarranty($service)) {
            return back()->with('error', 'Garansi servis ini sudah berakhir atau tidak berlaku.');
        }

        $validated = $request->validate(['problem_description' => 'nullable|string']);

        $handlingBranchId = $request->input('branch_id') ? (int) $request->input('branch_id') : (int) ($user->branch_id ?? $custodyBranchId);

        try {
            $claim = WarrantyService::openClaim(
                $service,
                $user,
                $validated['problem_description'] ?? 'Klaim garansi dari servis #' . $service->id,
                $handlingBranchId
            );
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Klaim garansi #' . $claim->claim_number . ' dibuka dan menunggu persetujuan.');
    }
}

