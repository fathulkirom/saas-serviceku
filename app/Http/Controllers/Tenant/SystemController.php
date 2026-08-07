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

        // PILOT-READY-01 (P0): Sistem exposes all users + branch counts + role
        // assignment. Restricted to owner/admin (user-management authority).
        $user = auth()->user();
        abort_unless($user->isOwner() || $user->isAdmin(), 403, 'Anda tidak memiliki akses ke manajemen pengguna.');

        return Inertia::render('Sistem/Index', [
            'activeTab' => $tab,

            'users' => fn() => User::with('branch', 'branches')->latest()->paginate(15),

            // BR-FIX-03 — Controlled delegation management (grant/revoke).
            'canManageDelegations' => fn() => auth()->user()->canViaPermission('delegation.grant')
                || auth()->user()->canViaPermission('delegation.revoke'),

            'delegations' => fn() => \App\Models\Tenant\Delegation::with(['user:id,name,role,branch_id', 'branch:id,name', 'granter:id,name'])
                ->latest()
                ->get()
                ->map(fn($d) => [
                    'id' => $d->id,
                    'user_name' => $d->user?->name,
                    'user_role' => $d->user?->role,
                    'permission' => $d->permission,
                    'branch_id' => $d->branch_id,
                    'branch_name' => $d->branch?->name ?? 'Semua cabang',
                    'granted_by' => $d->granter?->name,
                    'starts_at' => $d->starts_at?->toDateTimeString(),
                    'expires_at' => $d->expires_at?->toDateTimeString(),
                    'revoked_at' => $d->revoked_at?->toDateTimeString(),
                    'reason' => $d->reason,
                    'active' => $d->isActive(),
                ]),

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
