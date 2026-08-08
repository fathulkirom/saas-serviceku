<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        return redirect()->route('sistem.index')->with('info', 'Manajemen cabang sudah dipindah ke Sistem.');
    }

    public function store(Request $request)
    {
        $this->authorize('create', Branch::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);

        Branch::create($validated);

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil ditambahkan.');
    }

    public function update(Request $request, Branch $branch)
    {
        $this->authorize('update', $branch);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $branch->update($validated);

        return back()->with('success', 'Cabang berhasil diperbarui.');
    }

    public function destroy(Branch $branch)
    {
        $this->authorize('delete', $branch);
        if ($branch->users()->count() > 0 || $branch->services()->count() > 0) {
            return back()->with('error', 'Tidak bisa menghapus cabang yang masih memiliki data.');
        }

        $branch->delete();
        return redirect()->route('branches.index')->with('success', 'Cabang berhasil dihapus.');
    }
}
