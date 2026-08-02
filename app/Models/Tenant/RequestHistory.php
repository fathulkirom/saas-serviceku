<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Request History — append-only audit trail for Request status changes.
 */
class RequestHistory extends Model
{
    protected $table = 'request_history';
    public $timestamps = false; // Only created_at

    protected $fillable = ['request_id', 'from_status', 'to_status', 'actor_id', 'note', 'metadata'];
    protected $casts = ['metadata' => 'json', 'created_at' => 'datetime'];

    public function request()
    {
        return $this->belongsTo(Request::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'actor_id');
    }
}
