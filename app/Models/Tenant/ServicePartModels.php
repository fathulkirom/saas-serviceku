<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ServicePartUsage extends Model
{
    protected $table = 'service_part_usages';
    public $timestamps = false;
    protected $fillable = ['service_id', 'product_id', 'service_required_part_id', 'quantity', 'cost_price', 'selling_price', 'discount', 'subtotal', 'created_by'];
    protected $casts = ['created_at' => 'datetime', 'cost_price' => 'decimal:2', 'selling_price' => 'decimal:2', 'discount' => 'decimal:2', 'subtotal' => 'decimal:2'];

    public function service() { return $this->belongsTo(Service::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}

class ServicePartReturn extends Model
{
    protected $fillable = ['service_id', 'product_id', 'service_part_usage_id', 'quantity', 'reason', 'status', 'requested_by', 'processed_by'];

    public function service() { return $this->belongsTo(Service::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function usage() { return $this->belongsTo(ServicePartUsage::class, 'service_part_usage_id'); }
}
