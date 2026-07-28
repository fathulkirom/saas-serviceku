<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Shift;
use App\Models\Tenant\Attendance;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SystemController extends Controller
{
    public function index(Request $request): Response
    {
        $tab = $request->get('tab', 'pengguna');

        return Inertia::render('Sistem/Index', [
            'activeTab' => $tab,

            'users' => fn() => User::with('branch')->latest()->paginate(15),

            'systemBranches' => fn() => Branch::where('is_active', true)->get(),

            'branches' => fn() => Branch::withCount(['users', 'services', 'products'])->latest()->paginate(15),

            'shifts' => fn() => Shift::where('branch_id', auth()->user()->branch_id)->get(),

            'attendances' => fn() => Attendance::with(['user', 'shift'])
                ->whereHas('user', fn($q) => $q->where('branch_id', auth()->user()->branch_id))
                ->latest()
                ->paginate(20),

            'attendanceUsers' => fn() => User::where('branch_id', auth()->user()->branch_id)
                ->where('active', true)
                ->get(),

            'attendanceShifts' => fn() => Shift::where('branch_id', auth()->user()->branch_id)->get(),
        ]);
    }
}
