<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class MasterData extends Model
{
    protected $fillable = [
        'branch_id',
        'category',
        'name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    const CATEGORIES = [
        'device_category',
        'brand',
        'unit',
        'arrival_method',
        'payment_method',
        'equipment',
    ];

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForBranch($query, $branchId)
    {
        return $query->where(function ($q) use ($branchId) {
            $q->where('branch_id', $branchId)
              ->orWhereNull('branch_id');
        });
    }

    public static function getByCategory(string $category, $branchId = null)
    {
        return static::byCategory($category)
            ->active()
            ->when($branchId, fn($q) => $q->forBranch($branchId))
            ->orderBy('name')
            ->get();
    }

    public function getCategoryLabel(): string
    {
        return match ($this->category) {
            'device_category' => 'Kategori Perangkat',
            'brand' => 'Merek',
            'unit' => 'Satuan',
            'arrival_method' => 'Jalur Kedatangan',
            'payment_method' => 'Metode Pembayaran',
            'equipment' => 'Kelengkapan',
            default => ucfirst($this->category),
        };
    }
}
