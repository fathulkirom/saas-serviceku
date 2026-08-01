<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Attendance;
use Illuminate\Http\Request;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class AttendanceController extends Controller
{
    public function index()
    {
        return redirect()->route('sistem.index')->with('info', 'Absensi sudah dipindah ke Sistem.');
    }

    public function clockIn(Request $request)
    {
        $request->validate(['shift_id' => 'nullable|exists:shifts,id']);
        Attendance::create([
            'user_id' => auth()->id(),
            'shift_id' => $request->shift_id,
            'date' => now()->toDateString(),
            'clock_in' => now(),
            'status' => 'hadir',
        ]);
        return back()->with('success', 'Clock-in berhasil.');
    }

    public function clockOut()
    {
        $attendance = Attendance::where('user_id', auth()->id())->whereNull('clock_out')->latest()->first();
        if ($attendance) {
            $attendance->update(['clock_out' => now()]);
        }
        return back()->with('success', 'Clock-out berhasil.');
    }

    public function updateStatus(Request $request, Attendance $attendance)
    {
        $validated = $request->validate(['status' => 'required|in:hadir,izin,sakit,alpha', 'notes' => 'nullable|string']);
        $attendance->update($validated);
        return back()->with('success', 'Status absensi berhasil diupdate.');
    }
}
