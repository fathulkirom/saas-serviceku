<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceClaimController extends Controller
{
    public function createWarrantyClaim(Request $request, Service $parentService)
    {
        $this->authorize('create', Service::class);
        if (!$parentService->isWarrantyValid()) return back()->with('error', 'Garansi servis ini sudah berakhir.');

        $validated = $request->validate(['problem_description' => 'nullable|string']);

        $service = Service::create([
            'branch_id' => $parentService->branch_id, 'customer_id' => $parentService->customer_id,
            'created_by' => $user->id, 'technician_id' => $parentService->technician_id,
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
            'problem_description' => $validated['problem_description'] ?? 'Klaim garansi dari servis #' . $parentService->id,
            'is_warranty_claim' => true, 'parent_service_id' => $parentService->id,
            'service_charge' => 0, 'total_cost' => 0,
            'posisi_unit' => $parentService->posisi_unit, 'kategori_perangkat_id' => $parentService->kategori_perangkat_id,
            'merek_id' => $parentService->merek_id, 'tipe_unit' => $parentService->tipe_unit,
            'imei_sn' => $parentService->imei_sn,
        ]);

        ActivityLog::log('warranty_claim', 'Klaim garansi servis #' . $parentService->id . ' → #' . $service->id, $service);
        return redirect()->route('services.show', $service->id)->with('success', 'Klaim garansi berhasil dibuat. Biaya Rp 0.');
    }
}
