<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'branch_id',
        'code',
        'name',
        'description',
        'unit',
        'cost_price',
        'selling_price',
        'stock_quantity',
        'min_stock',
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

    public function isLowStock(): bool
    {
        return $this->stock_quantity <= $this->min_stock;
    }

    public function reduceStock(int $quantity): void
    {
        $this->decrement('stock_quantity', $quantity);
    }

    public function increaseStock(int $quantity): void
    {
        $this->increment('stock_quantity', $quantity);
    }

    public function getStockValue(): float
    {
        return $this->stock_quantity * $this->cost_price;
    }
}
