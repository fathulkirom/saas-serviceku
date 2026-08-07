<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $fillable = [
        'name',
        'address',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function locations()
    {
        return $this->hasMany(StockLocation::class);
    }

    /**
     * BR-FIX-02 (BR-005) — Branches whose stock THIS branch may READ
     * (configured via the branch_visibility pivot). Read visibility only.
     */
    public function visibleBranches()
    {
        return $this->belongsToMany(Branch::class, 'branch_visibility', 'branch_id', 'visible_branch_id')
            ->withTimestamps();
    }

    /**
     * BR-FIX-02 (BR-005) — Inverse: branches that may READ this branch's stock.
     */
    public function visibleToBranches()
    {
        return $this->belongsToMany(Branch::class, 'branch_visibility', 'visible_branch_id', 'branch_id')
            ->withTimestamps();
    }
}
