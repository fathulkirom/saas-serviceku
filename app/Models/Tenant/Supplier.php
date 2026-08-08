<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $fillable = [
        'branch_id', 'name', 'contact_person', 'phone', 'email',
        'address', 'category', 'notes', 'is_active',
        'purchase_count', 'total_purchased', 'last_purchase_at',
    ];

    protected $casts = [
        'is_active'        => 'boolean',
        'purchase_count'   => 'integer',
        'total_purchased'  => 'decimal:2',
        'last_purchase_at' => 'datetime',
    ];

    public function branch() { return $this->belongsTo(Branch::class); }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public static function categories(): array
    {
        return ['sparepart', 'tools', 'aksesoris', 'umum'];
    }

    public function categoryLabel(): string
    {
        return match ($this->category) {
            'sparepart'  => 'Sparepart',
            'tools'      => 'Tools / Peralatan',
            'aksesoris'  => 'Aksesoris',
            default      => 'Umum',
        };
    }

    public function recordPurchase(float $amount): void
    {
        $this->increment('purchase_count');
        $this->increment('total_purchased', $amount);
        $this->update(['last_purchase_at' => now()]);
    }
}
