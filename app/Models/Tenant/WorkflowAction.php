<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class WorkflowAction extends Model
{
    protected $fillable = ['workflow_id', 'transition_id', 'name', 'label', 'action_class', 'config', 'trigger', 'is_active'];
    protected $casts = ['config' => 'json', 'is_active' => 'bool'];

    public function workflow()   { return $this->belongsTo(Workflow::class); }
    public function transition() { return $this->belongsTo(WorkflowTransition::class); }
}
