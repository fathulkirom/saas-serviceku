<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\MasterData;
use App\Models\Tenant\MasterLaborService;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    /**
     * Daftar master data (filter by category).
     */
    public function index(Request $request)
    {
        $category = $request->get('category');
        $branchId = auth()->user()->branch_id;

        $query = MasterData::query();

        if ($category) {
            $query->where('category', $category);
        }

        $query->where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        });

        $data = $query->orderBy('category')->orderBy('name')->paginate(20);

        return redirect()->route('pengaturan.index', ['tab' => 'settings']);
    }

    /**
     * Tambah master data.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|in:' . implode(',', MasterData::CATEGORIES),
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;

        MasterData::create($validated);

        return back()->with('success', 'Data berhasil ditambahkan.');
    }

    /**
     * Update master data.
     */
    public function update(Request $request, MasterData $masterData)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|boolean',
        ]);

        $masterData->update($validated);

        return back()->with('success', 'Data berhasil diperbarui.');
    }

    /**
     * Hapus master data.
     */
    public function destroy(MasterData $masterData)
    {
        $masterData->delete();
        return back()->with('success', 'Data berhasil dihapus.');
    }

    // ========== MASTER LABOR SERVICES ==========

    /**
     * Daftar katalog jasa.
     */
    public function laborIndex(Request $request)
    {
        $query = MasterLaborService::with('branch')
            ->where('branch_id', auth()->user()->branch_id);

        $data = $query->orderBy('name')->paginate(20);

        return redirect()->route('pengaturan.index', ['tab' => 'settings']);
    }

    /**
     * Tambah katalog jasa.
     */
    public function laborStore(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;

        MasterLaborService::create($validated);

        return back()->with('success', 'Jasa berhasil ditambahkan.');
    }

    /**
     * Update katalog jasa.
     */
    public function laborUpdate(Request $request, MasterLaborService $masterLaborService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'nullable|numeric|min:0',
        ]);

        $masterLaborService->update($validated);

        return back()->with('success', 'Jasa berhasil diperbarui.');
    }

    /**
     * Hapus katalog jasa.
     */
    public function laborDestroy(MasterLaborService $masterLaborService)
    {
        $masterLaborService->delete();
        return back()->with('success', 'Jasa berhasil dihapus.');
    }
}
