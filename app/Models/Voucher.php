<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Voucher extends Model
{
    protected $connection = 'central'; // Central DB
    protected $fillable = [
        'code',
        'type',
        'value',
        'extra_months',
        'applicable_for',
        'tenant_id',
        'max_uses',
        'used_count',
        'min_plan_price',
        'expires_at',
        'is_active',
        'description',
        'created_by',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'min_plan_price' => 'decimal:2',
        'max_uses' => 'integer',
        'used_count' => 'integer',
        'extra_months' => 'integer',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Generate kode voucher unik.
     */
    public static function generateCode(int $length = 8): string
    {
        $code = strtoupper(Str::random($length));
        while (self::where('code', $code)->exists()) {
            $code = strtoupper(Str::random($length));
        }
        return $code;
    }

    /**
     * Cek apakah voucher masih valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->max_uses && $this->used_count >= $this->max_uses) return false;
        if ($this->expires_at && now()->gt($this->expires_at)) return false;
        return true;
    }

    /**
     * Hitung diskon dari harga plan.
     */
    public function calculateDiscount(float $planPrice): float
    {
        if ($this->type === 'percent') {
            return round($planPrice * ($this->value / 100), 2);
        }
        // fixed
        return min($this->value, $planPrice);
    }

    /**
     * Hitung harga akhir setelah diskon.
     */
    public function finalPrice(float $planPrice): float
    {
        return max(0, $planPrice - $this->calculateDiscount($planPrice));
    }

    /**
     * Apakah voucher bisa dipakai untuk tipe pendaftaran tertentu.
     */
    public function canApply(string $for): bool
    {
        return $this->applicable_for === 'both' || $this->applicable_for === $for;
    }

    /**
     * Pakai voucher (increment used_count).
     */
    public function markUsed(): void
    {
        $this->increment('used_count');
    }

    // Relasi
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tenants()
    {
        return $this->hasMany(Tenant::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
