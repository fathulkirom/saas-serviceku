<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class WorkflowTransition extends Model
{
    protected $fillable = ['workflow_id', 'from_state', 'to_state', 'label', 'permission', 'role', 'guard', 'is_auto', 'conditions', 'is_active'];
    protected $casts = ['conditions' => 'json', 'is_auto' => 'bool', 'is_active' => 'bool'];

    public function workflow() { return $this->belongsTo(Workflow::class); }
    public function actions()  { return $this->hasMany(WorkflowAction::class, 'transition_id'); }
}
