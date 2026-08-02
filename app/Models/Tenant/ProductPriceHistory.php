<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ProductPriceHistory extends Model
{
    protected $table = 'product_price_history';
    public $timestamps = false;
    protected $fillable = ['product_id', 'old_cost_price', 'new_cost_price', 'old_selling_price', 'new_selling_price', 'changed_by'];
    protected $casts = ['created_at' => 'datetime'];

    public function product() { return $this->belongsTo(Product::class); }
    public function changer() { return $this->belongsTo(User::class, 'changed_by'); }

    public static function record(Product $product, int $userId): void
    {
        $original = $product->getOriginal();
        $changed = false;

        if ($product->isDirty('cost_price')) {
            static::create([
                'product_id' => $product->id,
                'old_cost_price' => $original['cost_price'] ?? null,
                'new_cost_price' => $product->cost_price,
                'changed_by' => $userId,
            ]);
            $changed = true;
        }

        if ($product->isDirty('selling_price')) {
            static::create([
                'product_id' => $product->id,
                'old_selling_price' => $original['selling_price'] ?? null,
                'new_selling_price' => $product->selling_price,
                'changed_by' => $userId,
            ]);
            $changed = true;
        }
    }
}
