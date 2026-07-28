<?php
namespace App\Models\Tenant;
use Illuminate\Database\Eloquent\Model;

class CustomFieldValue extends Model
{
    protected $fillable = ['custom_field_id', 'entity_id', 'value'];

    public function customField()
    {
        return $this->belongsTo(CustomField::class);
    }
}
