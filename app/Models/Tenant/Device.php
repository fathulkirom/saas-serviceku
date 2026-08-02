<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Device extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id', 'brand', 'model', 'type', 'imei', 'serial_number',
        'color', 'storage', 'purchase_date', 'purchase_source', 'warranty_until',
        'warranty_status', 'notes', 'condition_summary',
        'repair_count', 'last_repaired_at', 'last_service_date', 'status',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_until' => 'date',
        'last_repaired_at' => 'datetime',
        'repair_count' => 'int',
    ];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }
    public function requests(): BelongsToMany { return $this->belongsToMany(Request::class, 'request_devices')->withPivot('damage_description', 'photo_references')->withTimestamps(); }
    public function healthHistory() { return $this->hasMany(DeviceHealthHistory::class)->orderBy('recorded_at'); }
    public function services() { return $this->hasMany(Service::class, 'device_id'); }

    public function isUnderWarranty(): bool
    {
        return $this->warranty_until && $this->warranty_until->isFuture();
    }

    public function scopeActive($q) { return $q->where('status', 'active'); }
    public function scopeByCustomer($q, int $customerId) { return $q->where('customer_id', $customerId); }
}
