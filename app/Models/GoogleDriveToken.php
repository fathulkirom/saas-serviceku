<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleDriveToken extends Model
{
    protected $connection = 'central';
    protected $fillable = [
        'tenant_id',
        'access_token',
        'refresh_token',
        'token_expiry',
        'root_folder_id',
        'connected_email',
    ];

    protected $casts = [
        'token_expiry' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
