<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ServiceChecklistResult extends Model
{
    protected $fillable = ['service_id', 'checklist_item_id', 'value', 'type', 'unit', 'notes', 'created_by'];

    public function service() { return $this->belongsTo(Service::class); }
    public function item()    { return $this->belongsTo(ChecklistItem::class, 'checklist_item_id'); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
}
