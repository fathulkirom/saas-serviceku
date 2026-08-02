<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class StockLocation extends Model
{
    protected $fillable = ['name', 'type', 'branch_id', 'is_active'];
    public function branch() { return $this->belongsTo(Branch::class); }
}

class ProductStockByLocation extends Model
{
    protected $table = 'product_stock_by_location';
    protected $fillable = ['product_id', 'location_id', 'quantity'];
    public $timestamps = false;
}

// Supplier moved to Supplier.php (Sprint 7.5F — duplicate class cleanup)

class PurchaseOrder extends Model
{
    protected $fillable = ['po_number', 'supplier_id', 'status', 'total_cost', 'created_by', 'received_by', 'received_at', 'notes'];
    protected $casts = ['received_at' => 'datetime', 'total_cost' => 'decimal:2'];

    protected static function booted(): void
    {
        static::creating(function ($po) {
            if (empty($po->po_number)) $po->po_number = 'PO' . date('ymd') . strtoupper(substr(uniqid(), -4));
        });
    }

    public function supplier() { return $this->belongsTo(Supplier::class); }
    public function items() { return $this->hasMany(PurchaseOrderItem::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }

    public function receive(int $userId): void
    {
        \DB::transaction(function () use ($userId) {
            foreach ($this->items as $item) {
                $product = $item->product;
                $before = $product->stock_quantity;
                $product->increaseStock($item->quantity);
                InventoryMutation::create([
                    'product_id' => $product->id,
                    'type' => 'purchase',
                    'quantity' => $item->quantity,
                    'before_stock' => $before,
                    'after_stock' => $product->fresh()->stock_quantity,
                    'unit_cost' => $item->unit_cost,
                    'reference_type' => 'purchase_order',
                    'reference_id' => $this->id,
                    'note' => "PO #{$this->po_number}",
                    'created_by' => $userId,
                ]);
                $item->update(['received_qty' => $item->quantity]);
                event(new \App\Events\Entity\StockReceived($product, $item->quantity));
            }
            $this->update(['status' => 'received', 'received_by' => $userId, 'received_at' => now()]);
        });
    }
}

class PurchaseOrderItem extends Model
{
    protected $fillable = ['purchase_order_id', 'product_id', 'quantity', 'received_qty', 'unit_cost'];
    public function purchaseOrder() { return $this->belongsTo(PurchaseOrder::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
