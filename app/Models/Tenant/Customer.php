<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'branch_id',
        'name',
        'phone',
        'email',
        'address',
        'is_member',
        'card_number',
        'points',
    ];

    protected $casts = [
        'is_member' => 'boolean',
        'points' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    public function indents()
    {
        return $this->hasMany(Indent::class);
    }
}
