<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class SopReadLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'sop_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public function sop()
    {
        return $this->belongsTo(Sop::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
