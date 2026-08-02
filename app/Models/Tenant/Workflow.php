<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

/**
 * Workflow — a named state machine definition.
 * e.g. "Service Workflow", "Request Workflow", "Warranty Workflow"
 */
class Workflow extends Model
{
    protected $fillable = ['key', 'label', 'model', 'initial_state', 'terminal_states', 'config', 'is_active', 'module_key'];
    protected $casts = ['terminal_states' => 'json', 'config' => 'json', 'is_active' => 'bool'];

    public function states()     { return $this->hasMany(WorkflowState::class)->orderBy('sort_order'); }
    public function transitions(){ return $this->hasMany(WorkflowTransition::class); }
    public function actions()    { return $this->hasMany(WorkflowAction::class); }
    public function history()    { return $this->hasMany(WorkflowHistory::class); }

    public function getState(string $key): ?WorkflowState { return $this->states()->where('key', $key)->first(); }
    public function isTerminalState(string $key): bool { return $this->states()->where('key', $key)->where('is_terminal', true)->exists(); }
    public function getTerminalStates(): array { return $this->states()->where('is_terminal', true)->pluck('key')->toArray(); }
}
