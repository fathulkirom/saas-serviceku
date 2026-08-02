<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Automation Rule — IF [condition] THEN [action].
 * Fully data-driven — no hardcoded if/else.
 */
class AutomationRule extends Model
{
    protected $fillable = ['name', 'event', 'entity_type', 'workflow_key', 'conditions', 'action_type', 'action_config', 'delay_minutes', 'priority', 'is_active', 'is_template', 'template_key', 'tenant_id'];
    protected $casts = ['conditions' => 'json', 'action_config' => 'json', 'is_active' => 'bool', 'is_template' => 'bool', 'delay_minutes' => 'int'];

    public function logs() { return $this->hasMany(AutomationLog::class); }

    public function scopeActive($q)  { return $q->where('is_active', true); }
    public function scopeForEvent($q, string $event) { return $q->where('event', $event); }
    public function scopeTemplates($q) { return $q->where('is_template', true); }
}
