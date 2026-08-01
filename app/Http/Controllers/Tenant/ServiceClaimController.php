<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceClaimController extends Controller
{
    public function createWarrantyClaim(Request $request, Service $service)
    {
        $user = Auth::user();

        $this->authorize('create', Service::class);
        $this->ensureServiceBranchAccess($service, $user?->branch_id);

        if (!$service->isWarrantyValid()) {
            return back()->with('error', 'Garansi servis ini sudah berakhir.');
        }

        $validated = $request->validate(['problem_description' => 'nullable|string']);

        $claimService = Service::create([
            'branch_id' => $service->branch_id, 'customer_id' => $service->customer_id,
            'created_by' => $user->id, 'technician_id' => $service->technician_id,
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
            'problem_description' => $validated['problem_description'] ?? 'Klaim garansi dari servis #' . $service->id,
            'is_warranty_claim' => true, 'parent_service_id' => $service->id,
            'service_charge' => 0, 'total_cost' => 0,
            'posisi_unit' => $service->posisi_unit, 'kategori_perangkat_id' => $service->kategori_perangkat_id,
            'merek_id' => $service->merek_id, 'tipe_unit' => $service->tipe_unit,
            'imei_sn' => $service->imei_sn,
        ]);

        ActivityLog::log('warranty_claim', 'Klaim garansi servis #' . $service->id . ' → #' . $claimService->id, $claimService);
        return redirect()->route('services.show', $claimService->id)->with('success', 'Klaim garansi berhasil dibuat. Biaya Rp 0.');
    }

    private function ensureServiceBranchAccess(Service $service, $userBranchId): void
    {
        if (!$userBranchId || !$service->branch_id) {
            return;
        }

        if ((string) $service->branch_id !== (string) $userBranchId) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'service' => 'Servis tidak berada pada cabang aktif Anda.',
            ]);
        }
    }
}
