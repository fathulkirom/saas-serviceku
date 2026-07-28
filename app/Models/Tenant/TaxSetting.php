<?php
namespace App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;

class TaxSetting extends Model
{
    protected $fillable = ['pkp_status', 'ppn_percentage', 'npwp', 'pkp_number', 'branch_id'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
