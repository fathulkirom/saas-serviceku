<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Traits\HasRoles;
use App\Notifications\TwoFactorCodeNotification;
use App\Notifications\VerifyEmailNotification;
use App\Services\BranchAccessService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasRoles, Notifiable;

    protected $fillable = [
        'branch_id',
        'name',
        'email',
        'email_verified_at',
        'password',
        'role',
        'custom_role',
        'custom_permissions',
        'ui_preferences',
        'active',
        'google_id',
        'google_avatar',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    protected $casts = [
        'custom_permissions' => 'json',
        'ui_preferences' => 'json',
        'active' => 'boolean',
        'password' => 'hashed',
        'email_verified_at' => 'datetime',
        'two_factor_confirmed_at' => 'datetime',
    ];

    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_confirmed_at);
    }

    public function twoFactorQrCodeSvg(): string
    {
        $google2fa = app('pragmarx.google2fa');
        $company = config('app.name');

        return $google2fa->getQRCodeInline($company, $this->email, $this->two_factor_secret);
    }

    public function twoFactorRecoveryCodes(): array
    {
        return json_decode(decrypt($this->two_factor_recovery_codes), true) ?? [];
    }

    public function regenerateTwoFactorRecoveryCodes(): void
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = strtoupper(
                implode('-', [
                    substr(bin2hex(random_bytes(3)), 0, 6),
                    substr(bin2hex(random_bytes(3)), 0, 6),
                ])
            );
        }
        $this->forceFill([
            'two_factor_recovery_codes' => encrypt(json_encode($codes)),
        ])->save();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    /**
     * BR-FIX-02 — ADDITIONAL authorized branches (beyond the primary/home
     * branch). `users.branch_id` remains the primary/home branch; this pivot
     * holds zero or more additional branches a user may access.
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'user_branches')
            ->withTimestamps();
    }

    /**
     * BR-FIX-02 — All branch IDs this user may access.
     * Owner → all tenant branches. Others → primary branch + explicitly
     * assigned pivot branches. Cached; invalidated on assignment changes.
     */
    public function branchAccessIds(): array
    {
        $cacheKey = "user:{$this->id}:branch_access";

        return Cache::remember($cacheKey, 300, function () {
            if ($this->isOwner()) {
                return Branch::query()->pluck('id')->map(fn($id) => (int) $id)->values()->all();
            }

            $ids = $this->branch_id ? [(int) $this->branch_id] : [];
            foreach ($this->branches()->pluck('branches.id') as $id) {
                $ids[] = (int) $id;
            }

            return array_values(array_unique($ids));
        });
    }

    /**
     * BR-FIX-02 — Invalidate the cached branch-access list. Must be called
     * whenever additional branch assignments change (add/remove) so stale
     * authorization is never served.
     */
    public function clearBranchAccessCache(): void
    {
        Cache::forget("user:{$this->id}:branch_access");
    }

    /**
     * Dapatkan daftar semua role yang tersedia.
     */
    public static function getAvailableRoles(): array
    {
        return [
            'owner' => 'Owner',
            'admin' => 'Admin',
            'manager' => 'Manager',
            'head_store' => 'Kepala Toko',
            'cs' => 'CS',
            'technician' => 'Teknisi',
            'cashier' => 'Kasir',
            'courier' => 'Kurir',
            'custom' => 'Kustom 🎨',
        ];
    }

    /**
     * Dapatkan label untuk suatu role.
     */
    public static function roleLabel(string $role): string
    {
        return self::getAvailableRoles()[$role] ?? ucfirst($role);
    }

    /**
     * Dapatkan tampilan nama role (custom_role jika ada, atau label default).
     */
    public function getRoleDisplayName(): string
    {
        if ($this->role === 'custom' && filled($this->custom_role)) {
            return (string) $this->custom_role;
        }

        return self::roleLabel((string) $this->role);
    }

    /**
     * Roles assigned via the new permission engine (user_role pivot).
     * Additive — keeps existing `role` column for backward compatibility.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'user_role')
            ->withTimestamps();
    }

    /**
     * Get all permission keys for this user.
     * Priority: new role_permission tables → fallback to existing HasRoles trait.
     */
    public function getPermissionKeys(): array
    {
        $cacheKey = "user:{$this->id}:permissions";

        return Cache::remember($cacheKey, 300, function () {
            // Check if the roles table exists (migration may not have run yet)
            if (Schema::hasTable('roles')) {
                $roleKeys = $this->roles()->pluck('key')->toArray();

                if (! empty($roleKeys)) {
                    return Permission::keysForRoles($roleKeys);
                }
            }

            // Fallback: use existing role column + role_permissions from HandleInertiaRequests
            return $this->getLegacyPermissions();
        });
    }

    /**
     * Check if user has a specific permission via the new engine.
     *
     * BR-FIX-03: also honors ACTIVE granular delegations. Delegated
     * capabilities are evaluated fresh at request time (never merged into
     * the cached role-permission list) so expiry/revocation take effect
     * immediately, and a delegation never changes the user's role.
     */
    public function canViaPermission(string $permissionKey): bool
    {
        if (in_array($permissionKey, $this->getPermissionKeys())) {
            return true;
        }

        return $this->hasActiveDelegation($permissionKey);
    }

    /**
     * BR-FIX-03 — Branch-aware permission check:
     *  - role-based permission → requires branch access (BranchAccessService),
     *  - delegated permission  → requires the grant to cover $branchId
     *    (Delegation::coversBranch — null branch scope covers any branch).
     * Returns false when $branchId is null for role-based permissions.
     */
    public function canViaPermissionInBranch(string $permissionKey, ?int $branchId): bool
    {
        if (in_array($permissionKey, $this->getPermissionKeys())) {
            return BranchAccessService::canAccess($this, $branchId);
        }

        return $this->hasActiveDelegation($permissionKey, $branchId);
    }

    /**
     * BR-FIX-03 — Whether an ACTIVE delegation grants $permission to this
     * user, optionally scoped to a branch. Expiration/revocation are
     * evaluated at REQUEST TIME via Delegation::scopeActive().
     *
     * If the delegations table does not exist yet (tenant not migrated, or an
     * in-memory test schema without the tenant migration), this safely returns
     * false — permission resolution must never 500 because of a missing table.
     */
    public function hasActiveDelegation(string $permission, ?int $branchId = null): bool
    {
        if (!Schema::hasTable('delegations')) {
            return false;
        }

        return Delegation::query()
            ->active()
            ->where('user_id', $this->id)
            ->where('permission', $permission)
            ->when($branchId !== null, function ($query) use ($branchId) {
                $query->where(function ($q) use ($branchId) {
                    $q->whereNull('branch_id')->orWhere('branch_id', $branchId);
                });
            })
            ->exists();
    }

    /**
     * Legacy permission lookup (fallback when roles table doesn't exist yet).
     *
     * BR-FIX-03 additions: 'service.create' (CS intake), 'service.pickup'
     * (pickup), 'sales.create' (payment/cashier), 'finance.view' (finance
     * reporting — deliberately ABSENT from cs/technician/cashier so a
     * restricted operational user cannot read profit/revenue), and
     * 'delegation.grant'/'delegation.revoke' (owner/admin/manager only).
     */
    protected function getLegacyPermissions(): array
    {
        $rolePermissions = [
            'owner' => ['manage_users', 'manage_settings', 'manage_finance', 'manage_products', 'manage_customers', 'manage_sales', 'manage_cash_register', 'manage_deposits', 'manage_purchases', 'manage_branches', 'manage_indents', 'void_transactions', 'assign_technician', 'work_on_services', 'delete_models', 'quick_stock', 'user.delete', 'service.work', 'service.update', 'service.finish', 'service.void', 'service.create', 'service.pickup', 'sales.create', 'finance.view', 'report.view', 'report.export', 'delegation.grant', 'delegation.revoke'],
            'admin' => ['manage_finance', 'manage_products', 'manage_customers', 'manage_sales', 'manage_cash_register', 'manage_deposits', 'manage_purchases', 'manage_indents', 'void_transactions', 'assign_technician', 'work_on_services', 'delete_models', 'service.work', 'service.update', 'service.finish', 'service.void', 'service.create', 'service.pickup', 'sales.create', 'finance.view', 'report.view', 'report.export', 'delegation.grant', 'delegation.revoke'],
            'manager' => ['manage_finance', 'manage_products', 'manage_customers', 'manage_sales', 'manage_cash_register', 'manage_deposits', 'manage_purchases', 'manage_indents', 'work_on_services', 'service.work', 'service.update', 'service.finish', 'service.create', 'service.pickup', 'sales.create', 'finance.view', 'report.view', 'report.export', 'delegation.grant', 'delegation.revoke'],
            'head_store' => ['manage_finance', 'manage_products', 'manage_customers', 'manage_sales', 'manage_cash_register', 'manage_deposits', 'work_on_services', 'service.work', 'service.finish', 'service.create', 'service.pickup', 'sales.create', 'finance.view'],
            'cs' => ['manage_customers', 'manage_indents', 'assign_technician', 'work_on_services', 'service.work', 'service.assign', 'service.finish', 'service.create', 'service.pickup', 'sales.create'],
            'technician' => ['work_on_services', 'service.work', 'service.finish'],
            // PILOT-READY-01: cashier performs counter pickup + must open the
            // service page (invoice/pay/pickup buttons) — service.view &
            // service.pickup are the minimal daily grants (matches the menu/toolbar).
            'cashier' => ['manage_sales', 'manage_cash_register', 'sales.create', 'cash_register.manage', 'service.view', 'service.pickup'],
            'courier' => [],
            'custom' => [],
        ];

        return $rolePermissions[$this->role] ?? [];
    }

    /**
     * Invalidate permission cache for this user.
     */
    public function clearPermissionCache(): void
    {
        Cache::forget("user:{$this->id}:permissions");
    }

    public function hasPermission(string $permission): bool
    {
        $permissions = $this->custom_permissions ?? [];

        return in_array($permission, $permissions);
    }

    /**
     * Override default verification notification.
     */
    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Send 2FA code via email as fallback.
     */
    public function sendTwoFactorCodeNotification(string $code): void
    {
        $this->notify(new TwoFactorCodeNotification($code));
    }
}
