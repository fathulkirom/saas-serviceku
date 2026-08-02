<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class CashierShift extends Model
{
    protected $fillable = ['user_id', 'branch_id', 'opening_balance', 'closing_balance', 'expected_cash', 'actual_cash', 'difference', 'status', 'opened_at', 'closed_at', 'approved_by', 'notes'];
    protected $casts = ['opened_at' => 'datetime', 'closed_at' => 'datetime', 'opening_balance' => 'decimal:2', 'difference' => 'decimal:2'];

    public function user() { return $this->belongsTo(User::class); }
    public function branch() { return $this->belongsTo(Branch::class); }

    public function close(float $expectedCash, float $actualCash, ?string $notes = null): void
    {
        $this->update(['status' => 'closed', 'expected_cash' => $expectedCash, 'actual_cash' => $actualCash, 'difference' => $actualCash - $expectedCash, 'closed_at' => now(), 'notes' => $notes]);
        event(new \App\Events\Entity\ShiftClosed($this));
    }

    public function approve(int $userId): void
    {
        $this->update(['status' => 'approved', 'approved_by' => $userId]);
    }
}

class DiscountRule extends Model
{
    protected $fillable = ['name', 'type', 'value', 'conditions', 'start_date', 'end_date', 'is_active'];
    protected $casts = ['conditions' => 'json', 'start_date' => 'date', 'end_date' => 'date', 'value' => 'decimal:2'];
}

class ProductBundle extends Model
{
    protected $fillable = ['name', 'bundle_price', 'is_active'];
    protected $casts = ['bundle_price' => 'decimal:2'];
    public function items() { return $this->hasMany(ProductBundleItem::class); }
}

class ProductBundleItem extends Model
{
    protected $fillable = ['product_bundle_id', 'product_id', 'quantity'];
    public function bundle() { return $this->belongsTo(ProductBundle::class); }
    public function product() { return $this->belongsTo(Product::class); }
}

class ProductPriceLevel extends Model
{
    protected $fillable = ['product_id', 'level', 'price'];
    protected $casts = ['price' => 'decimal:2'];
}

class SaleSerial extends Model
{
    protected $fillable = ['sale_id', 'product_id', 'serial_number', 'serial_type', 'status'];
}

class SaleReturn extends Model
{
    protected $fillable = ['sale_id', 'type', 'amount', 'reason', 'status', 'created_by', 'approved_by'];
    protected $casts = ['amount' => 'decimal:2'];
    public function sale() { return $this->belongsTo(Sale::class); }
    public function items() { return $this->hasMany(SaleReturnItem::class); }

    public function complete(int $userId): void
    {
        \DB::transaction(function () use ($userId) {
            $this->update(['status' => 'completed', 'approved_by' => $userId]);
            foreach ($this->items as $item) {
                $product = $item->product;
                $before = $product->stock_quantity;
                $product->increaseStock($item->quantity);
                InventoryMutation::create([
                    'product_id' => $product->id, 'type' => 'return',
                    'quantity' => $item->quantity, 'before_stock' => $before, 'after_stock' => $product->fresh()->stock_quantity,
                    'reference_type' => 'sale_return', 'reference_id' => $this->id,
                    'note' => "Return Sale #{$this->sale_id}", 'created_by' => $userId,
                ]);
            }
        });
        event(new \App\Events\Entity\SaleReturned($this));
    }
}

class SaleReturnItem extends Model
{
    protected $fillable = ['sale_return_id', 'product_id', 'quantity', 'unit_price'];
    protected $casts = ['unit_price' => 'decimal:2'];
    public function return() { return $this->belongsTo(SaleReturn::class, 'sale_return_id'); }
    public function product() { return $this->belongsTo(Product::class); }
}

class Promotion extends Model
{
    protected $fillable = ['name', 'type', 'rules', 'reward', 'start_date', 'end_date', 'is_active'];
    protected $casts = ['rules' => 'json', 'reward' => 'json', 'start_date' => 'date', 'end_date' => 'date'];
}
