<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class StockOpname extends Model
{
    protected $fillable = ['opname_number', 'location_id', 'status', 'created_by', 'approved_by', 'completed_at'];
    protected $casts = ['completed_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(fn($o) => $o->opname_number ??= 'OPN' . date('ymd') . strtoupper(substr(uniqid(), -4)));
    }

    public function location() { return $this->belongsTo(StockLocation::class); }
    public function items() { return $this->hasMany(StockOpnameItem::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function approver() { return $this->belongsTo(User::class, 'approved_by'); }

    public function complete(int $approverId): void
    {
        $this->update(['status' => 'completed', 'approved_by' => $approverId, 'completed_at' => now()]);
        event(new \App\Events\Entity\StockOpnameCompleted($this));
    }
}

class StockOpnameItem extends Model
{
    protected $fillable = ['stock_opname_id', 'product_id', 'system_qty', 'physical_qty', 'difference', 'note'];
    public function opname() { return $this->belongsTo(StockOpname::class); }
    public function product() { return $this->belongsTo(Product::class); }
}

class StockAdjustment extends Model
{
    protected $fillable = ['product_id', 'type', 'quantity', 'before_stock', 'after_stock', 'reason', 'created_by', 'approved_by'];
    public function product() { return $this->belongsTo(Product::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public static function record(Product $product, string $type, int $qty, string $reason, int $userId): self
    {
        $before = $product->stock_quantity;
        $product->increment('stock_quantity', $qty);
        $after = $product->fresh()->stock_quantity;

        $adj = static::create([
            'product_id' => $product->id, 'type' => $type, 'quantity' => $qty,
            'before_stock' => $before, 'after_stock' => $after,
            'reason' => $reason, 'created_by' => $userId,
        ]);

        InventoryMutation::create([
            'product_id' => $product->id, 'type' => $type, 'quantity' => $qty,
            'before_stock' => $before, 'after_stock' => $after,
            'reference_type' => 'stock_adjustment', 'reference_id' => $adj->id,
            'note' => $reason, 'created_by' => $userId,
        ]);

        event(new \App\Events\Entity\StockAdjusted($adj));
        return $adj;
    }
}

class ProductSerial extends Model
{
    protected $fillable = ['product_id', 'serial_number', 'status', 'location_id', 'service_id'];
    public function product() { return $this->belongsTo(Product::class); }
    public function location() { return $this->belongsTo(StockLocation::class); }
    public function service() { return $this->belongsTo(Service::class); }

    public function assignToService(int $serviceId): void
    {
        $this->update(['status' => 'used', 'service_id' => $serviceId]);
        event(new \App\Events\Entity\SerialAssigned($this));
    }
}

class TechnicianInventory extends Model
{
    protected $fillable = ['technician_id', 'product_id', 'quantity'];
    protected $table = 'technician_inventories';
    public function technician() { return $this->belongsTo(User::class, 'technician_id'); }
    public function product() { return $this->belongsTo(Product::class); }
}

class StockTransfer extends Model
{
    protected $fillable = ['transfer_number', 'from_location_id', 'to_location_id', 'status', 'created_by', 'approved_by', 'received_by', 'sent_at', 'received_at', 'notes'];
    protected $casts = ['sent_at' => 'datetime', 'received_at' => 'datetime'];

    protected static function booted(): void
    {
        static::creating(fn($t) => $t->transfer_number ??= 'TRF' . date('ymd') . strtoupper(substr(uniqid(), -4)));
    }

    public function fromLocation() { return $this->belongsTo(StockLocation::class, 'from_location_id'); }
    public function toLocation() { return $this->belongsTo(StockLocation::class, 'to_location_id'); }
    public function items() { return $this->hasMany(StockTransferItem::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function receive(int $userId): void
    {
        $this->update(['status' => 'received', 'received_by' => $userId, 'received_at' => now()]);
        event(new \App\Events\Entity\StockTransferred($this));
    }
}

class StockTransferItem extends Model
{
    protected $fillable = ['stock_transfer_id', 'product_id', 'quantity'];
    public function transfer() { return $this->belongsTo(StockTransfer::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
