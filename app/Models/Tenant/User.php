<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Traits\HasRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
    public function getRoleDisplayName(): string
    {
        if ($this->role === 'custom' && $this->custom_role) {
            return $this->custom_role;
        }
        return self::roleLabel($this->role);
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
