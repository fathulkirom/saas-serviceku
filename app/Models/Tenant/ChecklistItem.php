<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ChecklistItem extends Model
{
    protected $fillable = [
        'checklist_template_id',
        'item_name',
        'sort_order',
    ];

    public function template()
    {
        return $this->belongsTo(ChecklistTemplate::class);
    }
}
