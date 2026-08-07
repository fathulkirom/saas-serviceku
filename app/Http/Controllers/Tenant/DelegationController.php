<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\Branch;
use App\Models\Tenant\Delegation;
use App\Models\Tenant\User;
use App\Services\BranchAccessService;
use Illuminate\Http\Request;

/**
 * BR-FIX-03 — Controlled delegation lifecycle.
 *
 * Grant (store) / Revoke only — this is a thin management surface over the
 * existing Role + Permission + Branch Scope architecture. Delegations never
 * alter a user's role; they only make the delegated capability resolve to
 * true while the grant is active, scoped to a branch and bounded in time.
 */
class DelegationController extends Controller
{
    /**
     * List delegations (with grantee/branch/granter) for the Sistem UI.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Delegation::class);

        $delegations = Delegation::with(['user:id,name,role,branch_id', 'branch:id,name', 'granter:id,name'])
            ->latest()
            ->get()
            ->map(function (Delegation $d) {
                return [
                    'id' => $d->id,
                    'user_id' => $d->user_id,
                    'user_name' => $d->user?->name,
                    'user_role' => $d->user?->role,
                    'permission' => $d->permission,
                    'branch_id' => $d->branch_id,
                    'branch_name' => $d->branch?->name ?? 'Semua cabang',
                    'granted_by' => $d->granter?->name,
                    'starts_at' => $d->starts_at?->toDateTimeString(),
                    'expires_at' => $d->expires_at?->toDateTimeString(),
                    'revoked_at' => $d->revoked_at?->toDateTimeString(),
                    'revoked_by' => $d->revoker?->name,
                    'reason' => $d->reason,
                    'active' => $d->isActive(),
                ];
            });

        $users = User::with('branch')->where('active', true)->orderBy('name')->get(['id', 'name', 'role', 'branch_id']);

        return response()->json([
            'delegations' => $delegations,
            'users' => $users,
            'branches' => Branch::where('is_active', true)->get(['id', 'name']),
            'capabilities' => [
                'service.create' => 'Buat service / CS intake',
                'service.pickup' => 'Proses pickup service',
                'sales.create' => 'Catat pembayaran / kasir',
                'finance.view' => 'Lihat laporan keuangan',
                'report.view' => 'Lihat laporan',
            ],
        ]);
    }

    /**
     * Grant a granular, branch-scoped, time-bounded delegation.
     */
    public function store(Request $request)
    {
        $this->authorize('grant', Delegation::class);

        $user = $request->user();

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'permission' => 'required|string|max:120',
            'branch_id' => 'nullable|exists:branches,id',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after_or_equal:starts_at',
            'reason' => 'nullable|string|max:1000',
        ]);

        $grantee = User::findOrFail($validated['user_id']);

        // Owner may delegate any capability anywhere in the tenant.
        // Everyone else may only delegate a capability they themselves hold,
        // and only within branches they can access (BR-FIX-02 branch reach).
        if (!$user->isOwner()) {
            if (!$user->canViaPermission($validated['permission'])) {
                abort(403, 'Anda tidak memiliki capability yang didelegasikan.');
            }

            $branchId = $validated['branch_id'] ?? null;
            if ($branchId !== null && !BranchAccessService::canAccess($user, (int) $branchId)) {
                abort(403, 'Cabang berada di luar jangkauan Anda.');
            }
        }

        // Prevent delegating to self (meaningless) and to the owner (owner already has all).
        if ((int) $validated['user_id'] === (int) $user->id) {
            abort(422, 'Tidak dapat mendelegasikan ke diri sendiri.');
        }
        if ($grantee->isOwner()) {
            abort(422, 'Owner sudah memiliki seluruh akses.');
        }

        $delegation = Delegation::create([
            'user_id' => $grantee->id,
            'permission' => $validated['permission'],
            'branch_id' => $validated['branch_id'] ?? null,
            'granted_by' => $user->id,
            'starts_at' => $validated['starts_at'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
            'reason' => $validated['reason'] ?? null,
        ]);

        // Invalidate only the relevant authorization cache (the grantee's).
        $grantee->clearPermissionCache();

        ActivityLog::log(
            'delegation_granted',
            "Delegasi {$delegation->permission} diberikan kepada {$grantee->name}",
            $grantee,
            [
                'delegation_id' => $delegation->id,
                'permission' => $delegation->permission,
                'branch_id' => $delegation->branch_id,
                'starts_at' => $delegation->starts_at?->toDateTimeString(),
                'expires_at' => $delegation->expires_at?->toDateTimeString(),
                'granted_by' => $user->id,
                'reason' => $delegation->reason,
            ]
        );

        return back()->with('success', 'Delegasi akses diberikan.');
    }

    /**
     * Revoke a delegation immediately (request-time effect).
     */
    public function revoke(Request $request, Delegation $delegation)
    {
        $this->authorize('revoke', $delegation);

        $user = $request->user();

        $delegation->revoke($user->id);

        // Invalidate only the relevant authorization cache (the grantee's).
        $delegation->user?->clearPermissionCache();

        ActivityLog::log(
            'delegation_revoked',
            "Delegasi {$delegation->permission} dicabut dari {$delegation->user?->name}",
            $delegation->user,
            [
                'delegation_id' => $delegation->id,
                'permission' => $delegation->permission,
                'branch_id' => $delegation->branch_id,
                'revoked_by' => $user->id,
            ]
        );

        return back()->with('success', 'Delegasi akses dicabut.');
    }
}
