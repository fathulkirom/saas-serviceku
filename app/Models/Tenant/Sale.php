<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{
    protected $fillable = [
        'branch_id',
        'customer_id',
        'sale_type',
        'status',
        'service_id',
        'indent_id',
        'subtotal',
        'discount',
        'total',
        'payment_method',
        'payment_method_id',
        'paid_amount',
        'change',
        'pdf_url',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'change' => 'decimal:2',
    ];

    const SALE_TYPE_SERVIS = 'servis';
    const SALE_TYPE_LANGSUNG = 'langsung';
    const SALE_TYPE_INDEN = 'inden';

    const STATUS_DRAFT = 'draft';
    const STATUS_PAID = 'paid';
    const STATUS_CANCEL = 'cancel';

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function indent()
    {
        return $this->belongsTo(Indent::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(MasterData::class, 'payment_method_id');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopePaid($query)
    {
        return $query->where('status', self::STATUS_PAID);
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }
}
