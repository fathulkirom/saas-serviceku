<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $fillable = [
        'checklist_template_id', 'item_name', 'type', 'sort_order',
        'is_required', 'options', 'measurement_unit', 'category', 'default_value',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'options' => 'json',
    ];

    public function template()
    {
        return $this->belongsTo(ChecklistTemplate::class);
    }
}
