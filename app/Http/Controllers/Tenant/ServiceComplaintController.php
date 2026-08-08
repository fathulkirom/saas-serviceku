<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceComplaint;
use Illuminate\Http\Request;

/**
 * BR-014: Cross-Branch Complaint Controller.
 *
 * Service awal cabang A → komplain di cabang B.
 * Riwayat service asli tetap, teknisi komplain bisa berbeda,
 * bonus teknisi pertama tidak dipindahkan.
 */
class ServiceComplaintController extends Controller
{
    public function index(Request $request)
    {
        $branchId = auth()->user()->branch_id;
        $complaints = ServiceComplaint::with([
                'service.customer', 'user', 'technician',
                'originalBranch', 'originalTech',
            ])
            ->where('branch_id', $branchId)
            ->latest()->paginate(15)
            ->through(function ($complaint) {
                $complaint->is_cross_branch = $complaint->isCrossBranch();
                $complaint->status_label = $complaint->statusLabel();
                $complaint->customer = $complaint->service?->customer?->name;
                return $complaint;
            });

        return inertia('Services/Complaints', [
            'complaints' => $complaints,
        ]);
    }

    public function store(Request $request, Service $service)
    {
        $validated = $request->validate([
            'problem_description' => 'required|string|max:1000',
            'technician_id'      => 'nullable|exists:users,id',
            'attribution'        => 'nullable|in:original,complaint,shared',
            'notes'             => 'nullable|string|max:500',
        ]);

        $complaint = ServiceComplaint::create([
            'service_id'            => $service->id,
            'branch_id'             => auth()->user()->branch_id,
            'user_id'               => auth()->id(),
            'technician_id'         => $validated['technician_id'] ?? null,
            'status'                => 'open',
            'problem_description'   => $validated['problem_description'],
            'original_branch_id'    => $service->branch_id,
            'original_technician_id'=> $service->technician_id,
            'attribution'           => $validated['attribution'] ?? null,
            'notes'                 => $validated['notes'] ?? null,
        ]);

        return back()->with('success', $complaint->isCrossBranch()
            ? "✅ Komplain lintas cabang dicatat — #{$complaint->id}. Service asli tetap di cabang asal."
            : "✅ Komplain dicatat — #{$complaint->id}."
        );
    }

    public function update(Request $request, ServiceComplaint $complaint)
    {
        $validated = $request->validate([
            'status'       => 'nullable|in:open,in_progress,resolved,closed',
            'resolution'   => 'nullable|string|max:1000',
            'technician_id'=> 'nullable|exists:users,id',
            'notes'        => 'nullable|string',
        ]);

        $complaint->update(array_filter($validated));

        return back()->with('success', "Komplain #{$complaint->id} diperbarui.");
    }
}
