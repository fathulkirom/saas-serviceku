<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ServiceChecklist extends Model
{
    protected $fillable = [
        'service_id',
        'checklist_template_id',
        'template_id',
        'type',
        'checked_items',
        'notes',
        'note',
    ];

    protected $casts = [
        'checked_items' => 'json',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function template()
    {
        return $this->belongsTo(ChecklistTemplate::class, 'template_id');
    }

    public function checklistTemplate()
    {
        return $this->belongsTo(ChecklistTemplate::class, 'checklist_template_id');
    }
}
