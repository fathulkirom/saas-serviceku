<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class WorkflowHistory extends Model
{
    protected $table = 'workflow_history';
    public $timestamps = false;
    protected $fillable = ['entity_type', 'entity_id', 'workflow_id', 'transition_id', 'from_state', 'to_state', 'action', 'metadata', 'actor_id'];
    protected $casts = ['metadata' => 'json', 'created_at' => 'datetime'];

    public function entity()       { return $this->morphTo(); }
    public function workflow()     { return $this->belongsTo(Workflow::class); }
    public function transition()   { return $this->belongsTo(WorkflowTransition::class); }
    public function actor()        { return $this->belongsTo(User::class, 'actor_id'); }
}
