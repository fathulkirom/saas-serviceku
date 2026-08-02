<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class AutomationLog extends Model
{
    protected $table = 'automation_logs';
    public $timestamps = false;
    protected $fillable = ['automation_rule_id', 'entity_type', 'entity_id', 'event', 'status', 'message', 'context', 'scheduled_at', 'executed_at'];
    protected $casts = ['context' => 'json', 'scheduled_at' => 'datetime', 'executed_at' => 'datetime', 'created_at' => 'datetime'];

    public function rule()  { return $this->belongsTo(AutomationRule::class, 'automation_rule_id'); }
    public function entity() { return $this->morphTo(); }
}
