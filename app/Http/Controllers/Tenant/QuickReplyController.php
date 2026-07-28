<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\QuickReply;
use Illuminate\Http\Request;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class QuickReplyController extends Controller
{
    public function index()
    {
        return redirect()->route('pengaturan.index')->with('info', 'Quick replies sudah dipindah ke Pengaturan.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['branch_id'] = auth()->user()->branch_id;

        QuickReply::create($validated);

        ActivityLog::log('quick_reply', 'Buat balasan cepat: ' . $validated['title']);

        return back()->with('success', 'Balasan cepat berhasil dibuat.');
    }

    public function update(Request $request, QuickReply $quickReply)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);

        $quickReply->update($validated);

        ActivityLog::log('quick_reply', 'Ubah balasan cepat: ' . $validated['title']);

        return back()->with('success', 'Balasan cepat berhasil diperbarui.');
    }

    public function destroy(QuickReply $quickReply)
    {
        $quickReply->delete();

        ActivityLog::log('quick_reply', 'Hapus balasan cepat');

        return back()->with('success', 'Balasan cepat berhasil dihapus.');
    }
}
