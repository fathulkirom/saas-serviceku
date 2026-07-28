<?php

namespace App\Models\Tenant;

use App\Models\Tenant\Traits\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable, HasRoles;

    protected $fillable = [
        'branch_id',
        'name',
        'email',
        'password',
        'role',
        'custom_role',
        'custom_permissions',
        'ui_preferences',
        'active',
        'google_id',
        'google_avatar',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'custom_permissions' => 'json',
        'ui_preferences' => 'json',
        'active' => 'boolean',
        'password' => 'hashed',
    ];

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
}
