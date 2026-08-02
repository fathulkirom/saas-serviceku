<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class WorkflowState extends Model
{
    protected $fillable = ['workflow_id', 'key', 'label', 'color', 'icon', 'category', 'metadata', 'sort_order', 'is_terminal', 'is_active'];
    protected $casts = ['metadata' => 'json', 'is_terminal' => 'bool', 'is_active' => 'bool'];

    public function workflow() { return $this->belongsTo(Workflow::class); }
}
