<?php
namespace App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;

class PaymentReconciliation extends Model
{
    protected $fillable = ['sale_id', 'bank_name', 'reference_number', 'amount', 'status', 'notes', 'created_by', 'branch_id'];

    public function sale()
    {
        return $this->belongsTo(Sale::class);
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
