<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Cache;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, Notifiable, HasRoles;

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
        return !is_null($this->two_factor_confirmed_at);
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
            if (\Illuminate\Support\Facades\Schema::hasTable('roles')) {
                $roleKeys = $this->roles()->pluck('key')->toArray();

                if (!empty($roleKeys)) {
                    return Permission::keysForRoles($roleKeys);
                }
            }

            // Fallback: use existing role column + role_permissions from HandleInertiaRequests
            return $this->getLegacyPermissions();
        });
    }

    /**
     * Check if user has a specific permission via the new engine.
     */
    public function canViaPermission(string $permissionKey): bool
    {
        return in_array($permissionKey, $this->getPermissionKeys());
    }

    /**
     * Legacy permission lookup (fallback when roles table doesn't exist yet).
     */
    protected function getLegacyPermissions(): array
    {
        $rolePermissions = [
            'owner' => ['manage_users', 'manage_settings', 'manage_finance', 'manage_products', 'manage_customers', 'manage_sales', 'manage_cash_register', 'manage_deposits', 'manage_purchases', 'manage_branches', 'manage_indents', 'void_transactions', 'assign_technician', 'work_on_services', 'delete_models', 'quick_stock'],
            'admin' => ['manage_finance', 'manage_products', 'manage_customers', 'manage_sales', 'manage_cash_register', 'manage_deposits', 'manage_purchases', 'manage_indents', 'void_transactions', 'assign_technician', 'work_on_services', 'delete_models'],
            'manager' => ['manage_finance', 'manage_products', 'manage_customers', 'manage_sales', 'manage_cash_register', 'manage_deposits', 'manage_purchases', 'manage_indents', 'work_on_services'],
            'head_store' => ['manage_finance', 'manage_products', 'manage_customers', 'manage_sales', 'manage_cash_register', 'manage_deposits', 'work_on_services'],
            'cs' => ['manage_customers', 'manage_indents', 'assign_technician', 'work_on_services'],
            'technician' => ['work_on_services'],
            'cashier' => ['manage_sales', 'manage_cash_register'],
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
        $this->notify(new \App\Notifications\VerifyEmailNotification);
    }

    /**
     * Send 2FA code via email as fallback.
     */
    public function sendTwoFactorCodeNotification(string $code): void
    {
        $this->notify(new \App\Notifications\TwoFactorCodeNotification($code));
    }
}
