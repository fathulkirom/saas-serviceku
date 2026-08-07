<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use \App\Models\Tenant\Traits\HasOptimisticLocking;
    protected $fillable = [
        'branch_id', 'code', 'sku', 'barcode',
        'name', 'category', 'brand', 'type',
        'description', 'unit',
        'cost_price', 'selling_price',
        'stock_quantity', 'min_stock', 'stock_status',
    ];

    protected $casts = [
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'min_stock' => 'integer',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function unitRelation()
    {
        return $this->belongsTo(MasterData::class, 'unit_id');
    }

    public function mutations()
    {
        return $this->hasMany(InventoryMutation::class);
    }

    // Sprint 7.5D — Stock integrity guard
    public function reduceStock(int $quantity): void
    {
        if ($quantity <= 0) return;
        if ($this->stock_quantity < $quantity) {
            throw new \RuntimeException("Stok tidak mencukupi. Tersedia: {$this->stock_quantity}, diminta: {$quantity}.");
        }
        $this->decrement('stock_quantity', $quantity);
        $this->updateStockStatus();
    }

    public function increaseStock(int $quantity): void
    {
        if ($quantity <= 0) return;
        $this->increment('stock_quantity', $quantity);
        $this->updateStockStatus();
    }

    private function updateStockStatus(): void
    {
        $this->refresh();
        $status = match (true) {
            $this->stock_quantity <= 0 => 'out',
            $this->stock_quantity <= $this->min_stock => 'low',
            default => 'available',
        };
        if ($this->stock_status !== $status) {
            $this->updateQuietly(['stock_status' => $status]);
        }
    }

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock;
    }

    public function getStockValue(): float
    {
        return $this->stock_quantity * $this->cost_price;
    }

    // ============================================================
    // BR-FIX-01 (BR-009) — Reserved / Available stock.
    //
    // Reservation is DERIVED from authoritative ServiceRequiredPart
    // records that are approved (or reserved) but not yet consumed.
    // This avoids a mutable duplicated total on the Product row.
    //
    // available_stock = physical_stock - reserved_stock
    // ============================================================

    public function getReservedQuantityAttribute(): int
    {
        return (int) \App\Models\Tenant\ServiceRequiredPart::query()
            ->where('product_id', $this->id)
            ->whereIn('status', [
                \App\Models\Tenant\ServiceRequiredPart::STATUS_APPROVED,
                \App\Models\Tenant\ServiceRequiredPart::STATUS_RESERVED,
            ])
            ->sum('qty');
    }

    public function getAvailableQuantityAttribute(): int
    {
        return max(0, $this->stock_quantity - $this->reserved_quantity);
    }

    // Sprint 7.4A — Auto-record price history
    protected static function booted(): void
    {
        static::updated(function (Product $product) {
            if ($product->isDirty('cost_price') || $product->isDirty('selling_price')) {
                ProductPriceHistory::record($product, auth()->id() ?? 1);
            }
            if ($product->isLowStock()) {
                event(new \App\Events\Entity\LowStockDetected($product, $product->stock_quantity, $product->min_stock));
            }
        });
    }
}
