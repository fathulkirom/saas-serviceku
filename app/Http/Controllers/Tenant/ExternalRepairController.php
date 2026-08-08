<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ExternalPartner;
use App\Models\Tenant\ExternalRepair;
use App\Models\Tenant\Service;
use Illuminate\Http\Request;

/**
 * BR-018: External Repair Partner.
 *
 * Kirim service ke teknisi/vendor luar tanpa harus buat akun pegawai penuh.
 * Tracking unit keluar/masuk, biaya partner, margin toko, estimasi kembali.
 */
class ExternalRepairController extends Controller
{
    // ── Partners ──────────────────────────────────────────────────────────

    public function index()
    {
        $branchId = auth()->user()->branch_id;
        $partners = ExternalPartner::where('branch_id', $branchId)->latest()->get();
        $repairs = ExternalRepair::with(['service.customer', 'partner', 'user'])
            ->where('branch_id', $branchId)->latest()->paginate(15);

        $activeCount = ExternalRepair::where('branch_id', $branchId)
            ->whereIn('status', ['sent', 'in_progress'])->count();
        $overdueCount = ExternalRepair::where('branch_id', $branchId)
            ->whereIn('status', ['sent', 'in_progress'])
            ->whereNotNull('estimated_return')
            ->where('estimated_return', '<', now())->count();

        return inertia('Services/ExternalRepairs', [
            'partners'     => $partners,
            'repairs'      => $repairs,
            'activeCount'  => $activeCount,
            'overdueCount' => $overdueCount,
        ]);
    }

    public function storePartner(Request $request)
    {
        $validated = $request->validate([
            'name'      => 'required|string|max:255',
            'phone'     => 'nullable|string|max:50',
            'specialty' => 'nullable|string|max:255',
            'address'   => 'nullable|string|max:500',
            'notes'     => 'nullable|string',
        ]);

        ExternalPartner::create(array_merge($validated, [
            'branch_id' => auth()->user()->branch_id,
        ]));

        return back()->with('success', 'Partner eksternal ditambahkan.');
    }

    // ── Repairs ───────────────────────────────────────────────────────────

    public function store(Request $request, Service $service)
    {
        $validated = $request->validate([
            'partner_id'         => 'required|exists:external_partners,id',
            'partner_cost'       => 'required|numeric|min:0',
            'customer_charge'    => 'required|numeric|min:0',
            'problem_description'=> 'nullable|string|max:1000',
            'estimated_return'   => 'nullable|date',
        ]);

        $repair = ExternalRepair::create([
            'service_id'          => $service->id,
            'partner_id'          => $validated['partner_id'],
            'branch_id'           => auth()->user()->branch_id,
            'user_id'             => auth()->id(),
            'status'              => 'sent',
            'partner_cost'        => $validated['partner_cost'],
            'customer_charge'     => $validated['customer_charge'],
            'problem_description' => $validated['problem_description'] ?? null,
            'estimated_return'    => $validated['estimated_return'] ?? null,
            'sent_at'             => now(),
        ]);
        $repair->calculateMargin();
        $repair->save();

        $margin = $repair->customer_charge - $repair->partner_cost;
        $marginText = $margin >= 0 ? "margin Rp " . number_format($margin, 0, ',', '.') : "rugi Rp " . number_format(abs($margin), 0, ',', '.');

        return back()->with('success', "✅ Service dikirim ke partner — {$marginText}.");
    }

    public function update(Request $request, ExternalRepair $repair)
    {
        $validated = $request->validate([
            'status'           => 'nullable|in:sent,in_progress,done,returned,completed',
            'resolution'       => 'nullable|string|max:1000',
            'tracking_notes'   => 'nullable|string',
            'partner_cost'     => 'nullable|numeric|min:0',
            'customer_charge'  => 'nullable|numeric|min:0',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'returned') {
            $validated['returned_at'] = now();
        }

        $repair->update($validated);
        if (isset($validated['partner_cost']) || isset($validated['customer_charge'])) {
            $repair->calculateMargin();
            $repair->save();
        }

        return back()->with('success', 'Status external repair diperbarui.');
    }
}
