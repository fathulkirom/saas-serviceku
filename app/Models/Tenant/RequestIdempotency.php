<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class RequestIdempotency extends Model
{
    protected $fillable = [
        'key',
        'action',
        'user_id',
        'resource_type',
        'resource_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
