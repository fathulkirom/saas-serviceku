<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ServiceTransfer extends Model
{
    protected $fillable = [
        'service_id',
        'from_branch_id',
        'to_branch_id',
        'note',
        'transferred_by',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function fromBranch()
    {
        return $this->belongsTo(Branch::class, 'from_branch_id');
    }

    public function toBranch()
    {
        return $this->belongsTo(Branch::class, 'to_branch_id');
    }

    public function transferor()
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
