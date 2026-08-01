<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

/** @deprecated Use consolidated controller instead. See FinanceController, CashController, InventarisController, ServiceToolsController, SystemController, DocumentController, SettingController. */
class UserManagementController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'role' => 'required|in:owner,admin,manager,head_store,cs,technician,cashier,courier,custom',
            'custom_role' => 'required_if:role,custom|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return back()->with('success', 'User berhasil ditambahkan.');
    }

    public function update(Request $request, User $userManagement)
    {
        $this->authorize('update', $userManagement);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($userManagement->id)],
            'role' => 'required|in:owner,admin,manager,head_store,cs,technician,cashier,courier,custom',
            'custom_role' => 'required_if:role,custom|string|max:255',
            'branch_id' => 'nullable|exists:branches,id',
            'active' => 'nullable|boolean',
            'password' => 'nullable|string|min:8',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $userManagement->update($validated);

        return back()->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $userManagement)
    {
        if ($userManagement->isOwner()) {
            return back()->with('error', 'Tidak bisa menghapus user owner.');
        }

        $this->authorize('delete', $userManagement);
        $userManagement->delete();
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
