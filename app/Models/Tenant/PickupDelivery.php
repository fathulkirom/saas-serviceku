<?php
namespace App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;

class PickupDelivery extends Model
{
    protected $fillable = ['service_id', 'type', 'address', 'scheduled_at', 'pic_id', 'status', 'notes', 'created_by', 'branch_id'];

    protected $casts = ['scheduled_at' => 'datetime'];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
