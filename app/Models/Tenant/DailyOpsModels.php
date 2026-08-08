<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Worklog extends Model
{
    protected $table = 'worklogs';
    public $timestamps = false;
    protected $fillable = ['work_order_id', 'service_id', 'description', 'created_by'];
    protected $casts = ['created_at' => 'datetime'];

    public function workOrder() { return $this->belongsTo(WorkOrder::class); }
    public function service() { return $this->belongsTo(Service::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}

class PartBooking extends Model
{
    protected $fillable = ['product_id', 'service_id', 'quantity', 'expires_at', 'status', 'created_by'];
    protected $casts = ['expires_at' => 'date'];

    public function product() { return $this->belongsTo(Product::class); }
    public function service() { return $this->belongsTo(Service::class); }

    public function isExpired(): bool { return $this->expires_at->isPast(); }
    public function cancel(): void { $this->update(['status' => 'cancelled']); }
    public function use(): void { $this->update(['status' => 'used']); }
}

class PriceChangeRequest extends Model
{
    protected $fillable = ['service_id', 'item_type', 'old_price', 'new_price', 'reason', 'status', 'requested_by', 'approved_by'];
    protected $casts = ['old_price' => 'decimal:2', 'new_price' => 'decimal:2'];

    public function service() { return $this->belongsTo(Service::class); }

    public function approve(int $userId): void
    {
        $this->update(['status' => 'approved', 'approved_by' => $userId]);
        event(new \App\Events\Entity\PriceApproved($this));
    }
}
