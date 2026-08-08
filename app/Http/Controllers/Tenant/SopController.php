<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\Sop;
use App\Models\Tenant\SopReadLog;
use App\Models\Tenant\User;
use Illuminate\Http\Request;

class SopController extends Controller
{
    public function index()
    {
        return redirect()->route('dokumen.index')->with('info', 'SOP sudah dipindah ke Dokumen.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'string',
            'is_mandatory' => 'nullable|boolean',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['branch_id'] = auth()->user()->branch_id;
        $validated['is_mandatory'] = $validated['is_mandatory'] ?? false;

        Sop::create($validated);

        ActivityLog::log('sop', 'Buat SOP: ' . $validated['title']);

        return back()->with('success', 'SOP berhasil dibuat.');
    }

    public function update(Request $request, Sop $sop)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'target_roles' => 'nullable|array',
            'target_roles.*' => 'string',
            'is_mandatory' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['version'] = $sop->version + 1;
        $sop->update($validated);

        $sop->readLogs()->delete();

        ActivityLog::log('sop', 'Ubah SOP: ' . $validated['title']);

        return back()->with('success', 'SOP berhasil diperbarui.');
    }

    public function destroy(Sop $sop)
    {
        $sop->readLogs()->delete();
        $sop->delete();

        ActivityLog::log('sop', 'Hapus SOP');

        return back()->with('success', 'SOP berhasil dihapus.');
    }

    public function markRead(Sop $sop)
    {
        SopReadLog::firstOrCreate([
            'sop_id' => $sop->id,
            'user_id' => auth()->id(),
            'read_at' => now(),
        ]);

        return back();
    }
}
