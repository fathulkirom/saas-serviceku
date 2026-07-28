<?php
namespace App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;

class CustomField extends Model
{
    protected $fillable = ['module', 'label', 'type', 'options', 'is_required', 'ordering', 'is_active', 'branch_id'];

    protected $casts = ['options' => 'array', 'is_required' => 'boolean', 'is_active' => 'boolean'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function values()
    {
        return $this->hasMany(CustomFieldValue::class);
    }
}
