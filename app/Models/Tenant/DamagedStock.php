<?php
namespace App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;

class DamagedStock extends Model
{
    protected $fillable = ['product_id', 'quantity', 'type', 'source', 'notes', 'created_by', 'branch_id'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
