<?php
namespace App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;

class PurchaseReturn extends Model
{
    protected $fillable = ['purchase_id', 'supplier_id', 'return_number', 'reason', 'status', 'created_by'];

    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items()
    {
        return $this->hasMany(PurchaseReturnItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected static function booted()
    {
        static::creating(function ($return) {
            if (empty($return->return_number)) {
                $return->return_number = 'RET-' . strtoupper(\Illuminate\Support\Str::random(8));
            }
        });
    }
}
