<?php
namespace App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;

class PartnerTeknisi extends Model
{
    protected $fillable = ['name', 'phone', 'expertise', 'tariff', 'is_active', 'branch_id'];

    protected $casts = ['is_active' => 'boolean', 'tariff' => 'decimal:2'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
