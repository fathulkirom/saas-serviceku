<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class UserManagementController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        // PLATFORM-SYNC-01 (STEP 9): enforce the plan's max_users at creation.
        // max_users counts ALL user accounts (owner + staff) — consistent with
        // the Trial plan (max_users=1 = the single owner). Reject over-limit
        // with an understandable plan-limit message instead of silently
        // exceeding the advertised quota.
        $maxUsers = tenancy()->tenant?->plan?->maxValue('max_users');
        if ($maxUsers > 0 && User::count() >= $maxUsers) {
            throw ValidationException::withMessages([
                'name' => "Kuota user paket Anda sudah penuh (maks. {$maxUsers} user). Silakan upgrade paket untuk menambah user.",
            ]);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:owner,admin,manager,head_store,cs,technician,cashier,courier,custom',
            'custom_role' => 'required_if:role,custom|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            // BR-FIX-02: additional authorized branches (zero or more).
            'additional_branches' => 'nullable|array',
            'additional_branches.*' => 'integer|exists:branches,id',
        ]);

        $userBranchId = auth()->user()->branch_id;

        if ($userBranchId && !empty($validated['branch_id']) && (string) $validated['branch_id'] !== (string) $userBranchId) {
            throw ValidationException::withMessages([
                'branch_id' => 'Hanya bisa membuat user di cabang aktif Anda.',
            ]);
        }

        $validated['branch_id'] = $validated['branch_id'] ?? $userBranchId;

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        // BR-FIX-02: sync additional branch access (owner-only assignment beyond
        // the actor's own scope; non-owner actors may only assign within scope).
        $this->syncBranchAssignments($user, $validated['additional_branches'] ?? []);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        // NOTE: parameter is `$user` to match the resource route segment {user}
        // (route-model binding). Renamed from $userManagement (BR-FIX-02).
        $this->authorize('update', $user);
        $this->ensureUserBranchAccess($user);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:owner,admin,manager,head_store,cs,technician,cashier,courier,custom',
            'custom_role' => 'required_if:role,custom|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'active' => 'nullable|boolean',
            'password' => 'nullable|string|min:8',
            // BR-FIX-02: additional authorized branches (zero or more).
            'additional_branches' => 'nullable|array',
            'additional_branches.*' => 'integer|exists:branches,id',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        // BR-FIX-02: sync additional branch access + audit + cache invalidation.
        $this->syncBranchAssignments($user, $validated['additional_branches'] ?? []);

        return back()->with('success', 'User berhasil diperbarui.');
    }

    /**
     * BR-FIX-02 — Sync additional branch assignments on a user.
     *
     * The primary/home branch (users.branch_id) is never removed. Additional
     * access is explicit via the user_branches pivot. Non-owner actors may only
     * assign branches within their own access scope. Audits add/remove and
     * invalidates the cached branch-access list.
     */
    protected function syncBranchAssignments(User $user, array $newIds): void
    {
        $actor = auth()->user();

        if (!$actor->isOwner()) {
            $actorAccess = \App\Services\BranchAccessService::accessibleBranchIds($actor);
            foreach (array_map('intval', $newIds) as $bid) {
                if (!in_array($bid, $actorAccess, true)) {
                    abort(403, 'Tidak berhak menetapkan akses cabang di luar jangkauan Anda.');
                }
            }
        }

        $primaryId = $user->branch_id ? (int) $user->branch_id : null;
        $additional = array_values(array_filter(
            array_unique(array_map('intval', $newIds)),
            fn($id) => $id !== $primaryId
        ));

        $old = $user->branches()->pluck('branches.id')->map(fn($id) => (int) $id)->all();
        $user->branches()->sync($additional);
        $user->clearBranchAccessCache();

        $added = array_diff($additional, $old);
        $removed = array_diff($old, $additional);

        foreach ($added as $bid) {
            \App\Models\Tenant\ActivityLog::log(
                'manager_branch_assigned',
                "Cabang #{$bid} ditambahkan ke akses user #{$user->id} ({$user->name})",
                $user,
                ['branch_id' => $bid, 'by' => $actor?->id]
            );
        }
        foreach ($removed as $bid) {
            \App\Models\Tenant\ActivityLog::log(
                'manager_branch_removed',
                "Cabang #{$bid} dihapus dari akses user #{$user->id} ({$user->name})",
                $user,
                ['branch_id' => $bid, 'by' => $actor?->id]
            );
        }
    }

    public function destroy(User $user)
    {
        if ($user->isOwner()) {
            return back()->with('error', 'Tidak bisa menghapus user owner.');
        }

        $this->authorize('delete', $user);
        $this->ensureUserBranchAccess($user);
        $user->delete();
        return back()->with('success', 'User berhasil dihapus.');
    }

    /**
     * Halaman Profil Saya (untuk user yang sedang login).
     */
    public function myProfile()
    {
        $user = auth()->user();
        $user->load('branch');
        return inertia('Users/Profile', [
            'user' => $user,
        ]);
    }

    /**
     * Update profil user yang sedang login.
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    /**
     * Update preferensi UI (layout, menu, theme).
     */
    public function updatePreferences(Request $request)
    {
        if (!auth()->user()->canManageSettings()) {
            abort(403, 'Hanya owner yang dapat mengubah preferensi tampilan.');
        }

        $user = auth()->user();

        $validated = $request->validate([
            'layout' => 'required|in:sidebar,topbar,slim-sidebar',
            'menu_style' => 'required|in:expanded,grouped',
            'sidebar_theme' => 'nullable|in:light,dark,colored',
            'theme_style' => 'nullable|in:classic,modern,hybrid,glass,dark',
        ]);

        $preferences = [
            'layout' => $validated['layout'],
            'menu_style' => $validated['menu_style'],
            'sidebar_theme' => $validated['sidebar_theme'] ?? 'dark',
            'theme_style' => $validated['theme_style'] ?? 'modern',
        ];

        $user->update(['ui_preferences' => $preferences]);

        return back()->with('success', 'Preferensi tampilan berhasil disimpan.');
    }

    private function ensureUserBranchAccess(User $user): void
    {
        $userBranchId = auth()->user()?->branch_id;

        if (!$userBranchId || !$user->branch_id) {
            return;
        }

        if ((string) $user->branch_id !== (string) $userBranchId) {
            throw ValidationException::withMessages([
                'user' => 'User tidak berada pada cabang aktif Anda.',
            ]);
        }
    }

    /**
     * Data menu access untuk suatu user (dikonsumsi halaman Sistem via fetch JSON).
     * Dipakai oleh drawer akses menu — bukan halaman Inertia terpisah.
     */
    public function getMenuAccess(User $userManagement)
    {
        $plan = \App\Models\Plan::find(tenant()->plan_id);
        $defaultMenus = $plan ? $plan->getDefaultMenusForRole($userManagement->role) : \App\Models\Plan::getBuiltInDefaultMenus($userManagement->role);
        $customMenus = $userManagement->custom_permissions['menu_access'] ?? null;

        $planAccess = [];
        if (tenancy()->initialized) {
            $planAccess = tenancy()->tenant->getAllEffectiveFeatureAccess();
        }

        return response()->json([
            'user' => $userManagement->load('branch'),
            'defaultMenus' => $defaultMenus,
            'customMenus' => $customMenus,
            'usingCustom' => $customMenus !== null,
            'allAvailableMenus' => \App\Models\Plan::getAllAvailableMenus(),
            'planName' => $plan?->name ?? 'Trial',
            'planAccess' => $planAccess,
            'roleLabel' => User::roleLabel($userManagement->role),
        ]);
    }

    /**
     * Update menu access untuk suatu user.
     */
    public function updateMenuAccess(Request $request, User $userManagement)
    {
        $validated = $request->validate([
            'menu_access' => 'nullable|array',
            'menu_access.*' => 'string',
            'reset_to_default' => 'boolean',
        ]);

        $permissions = $userManagement->custom_permissions ?? [];

        if ($validated['reset_to_default'] ?? false) {
            unset($permissions['menu_access']);
        } else {
            $permissions['menu_access'] = $validated['menu_access'] ?? [];
        }

        $userManagement->update(['custom_permissions' => $permissions]);

        return back()->with('success', 'Menu akses berhasil diperbarui.');
    }

    public function assignTechnician(Request $request)
    {
        if (!auth()->user()->canAssignTechnician()) {
            return back()->with('error', 'Anda tidak memiliki izin untuk menugaskan teknisi.');
        }

        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'technician_id' => 'required|exists:users,id',
        ]);

        $service = \App\Models\Tenant\Service::findOrFail($validated['service_id']);
        $technician = User::findOrFail($validated['technician_id']);

        if (!$technician->isTechnician() && !$technician->isOwner()) {
            return back()->with('error', 'User harus memiliki peran teknisi.');
        }

        $service->update([
            'technician_id' => $technician->id,
            'status' => 'dikerjakan',
        ]);

        return back()->with('success', 'Teknisi berhasil ditugaskan.');
    }
}
