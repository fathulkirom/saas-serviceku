<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\KnowledgeBase;
use App\Models\Tenant\MasterData;
use Illuminate\Http\Request;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class KnowledgeBaseController extends Controller
{
    public function index()
    {
        return redirect()->route('dokumen.index')->with('info', 'Knowledge base sudah dipindah ke Dokumen.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'device_type' => 'nullable|string|max:100',
            'device_brand' => 'nullable|string|max:100',
            'device_model' => 'nullable|string|max:100',
            'masalah' => 'required|string',
            'solusi' => 'required|string',
            'lampiran' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('lampiran')) {
            $validated['lampiran'] = $request->file('lampiran')->store('knowledge-base', 'public');
        }

        $validated['created_by'] = auth()->id();
        $validated['branch_id'] = auth()->user()->branch_id;

        KnowledgeBase::create($validated);

        ActivityLog::log('knowledge_base', 'Tambah artikel KB: ' . $validated['judul']);

        return back()->with('success', 'Artikel knowledge base berhasil ditambahkan.');
    }

    public function update(Request $request, KnowledgeBase $knowledgeBase)
    {
        $validated = $request->validate([
            'judul' => 'required|string|max:255',
            'device_type' => 'nullable|string|max:100',
            'device_brand' => 'nullable|string|max:100',
            'device_model' => 'nullable|string|max:100',
            'masalah' => 'required|string',
            'solusi' => 'required|string',
            'lampiran' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($request->hasFile('lampiran')) {
            $validated['lampiran'] = $request->file('lampiran')->store('knowledge-base', 'public');
        }

        $knowledgeBase->update($validated);

        ActivityLog::log('knowledge_base', 'Ubah artikel KB: ' . $validated['judul']);

        return back()->with('success', 'Artikel knowledge base berhasil diperbarui.');
    }

    public function destroy(KnowledgeBase $knowledgeBase)
    {
        $knowledgeBase->delete();

        ActivityLog::log('knowledge_base', 'Hapus artikel KB');

        return back()->with('success', 'Artikel knowledge base berhasil dihapus.');
    }

    public function search(Request $request)
    {
        $query = $request->get('q');
        $deviceType = $request->get('device_type');
        $deviceBrand = $request->get('device_brand');

        $articles = KnowledgeBase::where('branch_id', auth()->user()->branch_id);

        if ($query) {
            $articles->where(function ($q) use ($query) {
                $q->where('judul', 'like', "%{$query}%")
                    ->orWhere('masalah', 'like', "%{$query}%")
                    ->orWhere('solusi', 'like', "%{$query}%");
            });
        }

        if ($deviceType) {
            $articles->where('device_type', $deviceType);
        }

        if ($deviceBrand) {
            $articles->where('device_brand', $deviceBrand);
        }

        return response()->json([
            'articles' => $articles->with('creator')->latest()->get(),
        ]);
    }
}
