<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TechnicianBonusConfig;
use App\Models\Tenant\TechnicianBonusRecord;
use App\Models\Tenant\User;
use Illuminate\Http\Request;

/**
 * BR-015: Technician Bonus & Compensation.
 */
class TechnicianBonusController extends Controller
{
    public function index()
    {
        $branchId = auth()->user()->branch_id;

        $configs = TechnicianBonusConfig::with('user')
            ->where('branch_id', $branchId)->get();

        $technicians = User::where('branch_id', $branchId)
            ->where('role', 'technician')->where('active', true)->get();

        $records = TechnicianBonusRecord::with(['service.customer', 'user'])
            ->where('branch_id', $branchId)
            ->latest()->paginate(20);

        $pendingTotal = TechnicianBonusRecord::where('branch_id', $branchId)
            ->where('status', 'pending')->sum('amount');
        $approvedTotal = TechnicianBonusRecord::where('branch_id', $branchId)
            ->where('status', 'approved')->sum('amount');

        $recap = TechnicianBonusRecord::where('branch_id', $branchId)
            ->where('status', 'approved')
            ->selectRaw('user_id, SUM(amount) as total_bonus, COUNT(*) as service_count')
            ->groupBy('user_id')->with('user')->get();

        return inertia('Sistem/TechnicianBonus', [
            'configs'       => $configs,
            'technicians'   => $technicians,
            'records'       => $records,
            'pendingTotal'  => $pendingTotal,
            'approvedTotal' => $approvedTotal,
            'recap'         => $recap,
        ]);
    }

    public function saveConfig(Request $request)
    {
        $validated = $request->validate([
            'user_id'               => 'required|exists:users,id',
            'bonus_type'            => 'required|in:percentage,fixed,per_category,combined',
            'percentage'            => 'nullable|numeric|min:0|max:100',
            'fixed_amount'          => 'nullable|numeric|min:0',
            'category_rates'        => 'nullable|json',
            'base_salary'           => 'nullable|numeric|min:0',
            'exclude_warranty_rework'=> 'boolean',
            'is_active'             => 'boolean',
        ]);

        TechnicianBonusConfig::updateOrCreate(
            ['user_id' => $validated['user_id']],
            array_merge($validated, ['branch_id' => auth()->user()->branch_id])
        );

        return back()->with('success', 'Konfigurasi bonus teknisi disimpan.');
    }

    public function approve(Request $request, TechnicianBonusRecord $record)
    {
        $record->update([
            'status'      => 'approved',
            'approved_at' => now(),
            'approved_by' => auth()->id(),
        ]);

        return back()->with('success', "Bonus #{$record->id} disetujui.");
    }

    public function approveBatch(Request $request)
    {
        $ids = $request->input('ids', []);
        TechnicianBonusRecord::whereIn('id', $ids)
            ->where('status', 'pending')
            ->where('branch_id', auth()->user()->branch_id)
            ->update([
                'status'      => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

        return back()->with('success', count($ids) . ' bonus disetujui.');
    }
}
