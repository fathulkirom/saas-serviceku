<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * BR-010: Emergency Purchase — pembelian sparepart darurat dari kas toko
 * saat distributor kosong. Part langsung masuk stok, tercatat sebagai
 * pengeluaran, dan diaudit siapa yang melakukan.
 */
class EmergencyPurchase extends Model
{
    protected $fillable = [
        'branch_id', 'user_id', 'product_name', 'quantity',
        'cost_price', 'total', 'supplier_name', 'reason',
        'paid_from_cash', 'expense_id', 'product_id',
        'status', 'notes',
    ];

    protected $casts = [
        'quantity'       => 'integer',
        'cost_price'     => 'decimal:2',
        'total'          => 'decimal:2',
        'paid_from_cash' => 'boolean',
    ];

    public function branch() { return $this->belongsTo(Branch::class); }
    public function user()   { return $this->belongsTo(User::class); }
    public function expense(){ return $this->belongsTo(Expense::class); }
    public function product(){ return $this->belongsTo(Product::class); }
}
