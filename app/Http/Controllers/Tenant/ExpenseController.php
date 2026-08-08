<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Expense;
use App\Models\Tenant\ActivityLog;
use App\Services\GoogleDrivePhotoService;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        return redirect()->route('keuangan.index', ['tab' => 'pengeluaran'])->with('info', 'Halaman pengeluaran sudah dipindah ke Keuangan.');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'expense_date' => 'required|date',
            'category' => 'nullable|string|in:' . implode(',', array_keys(Expense::CATEGORIES)),
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:5120',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;
        $validated['created_by'] = auth()->id();

        if ($request->hasFile('photo')) {
            $driveService = new GoogleDrivePhotoService(tenancy()->tenant->id);
            if (!$driveService->isConnected()) {
                return back()->with('error', 'Upload foto membutuhkan Google Drive. Hubungkan Google Drive di Pengaturan terlebih dahulu.');
            }

            $path = $request->file('photo')->store('expenses', 'public');
            $validated['photo'] = $path;
            $driveUrl = $driveService->upload(
                storage_path('app/public/' . $path),
                'expense_' . time() . '.jpg',
                'expenses'
            );
            if ($driveUrl) {
                $validated['photo'] = $driveUrl;
            }
        }

        Expense::create($validated);

        ActivityLog::log('expense', 'Catat pengeluaran: ' . $validated['description']);

        return back()->with('success', 'Pengeluaran berhasil dicatat.');
    }
}
