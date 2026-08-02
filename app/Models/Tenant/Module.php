<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Module extends Model
{
    protected $fillable = ['key', 'name', 'description', 'icon', 'sort_order', 'category', 'is_default', 'requires', 'features', 'status'];

    protected $casts = [
        'is_default' => 'boolean',
        'requires' => 'json',
        'features' => 'json',
        'sort_order' => 'integer',
    ];

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function unmetDependencies(array $activeModuleKeys): array
    {
        if (empty($this->requires)) { return []; }
        return array_diff($this->requires, $activeModuleKeys);
    }

    public function getFeatureKeys(): array
    {
        return $this->features ?? [];
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
