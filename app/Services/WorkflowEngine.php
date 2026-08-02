<?php

namespace App\Services;

use App\Events\WorkflowStateChanged;
use App\Models\Tenant\Workflow;
use App\Models\Tenant\WorkflowTransition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/**
 * WorkflowEngine v2 — Pure state machine.
 *
 * Responsibilities:
 *   ✅ Resolve workflow for entity
 *   ✅ Validate guard, permission, role, conditions
 *   ✅ Transition entity state
 *   ✅ Emit WorkflowStateChanged event
 *
 * DOES NOT:
 *   ❌ Write to any history table (→ WorkflowHistoryProjector subscriber)
 *   ❌ Write to any timeline table (→ TimelineProjector subscriber)
 *   ❌ Write to activity logs       (→ ActivityProjector subscriber)
 *   ❌ Call providers               (→ AutomationEngine via EventBus)
 *   ❌ Know about WhatsApp/Email/GDrive
 *
 * Usage:
 *   $engine = app(WorkflowEngine::class);
 *   $engine->transition($service, 'accept');
 */
class WorkflowEngine
{
    private const CACHE_TTL = 3600;
    private const CACHE_PREFIX = 'wf:';

    /**
     * Execute a workflow transition on an entity.
     * Pure state machine — emits event, delegates ALL side effects.
     *
     * @throws \RuntimeException if transition is invalid
     */
    public function transition(Model $entity, string $action, array $extra = []): array
    {
        $workflow = $this->resolveWorkflow($entity);
        if (!$workflow) {
            throw new \RuntimeException("No workflow defined for " . get_class($entity));
        }

        $currentStatus = $entity->status ?? $entity->getOriginal('status') ?? 'draft';
        $transition = $this->findTransition($workflow, $currentStatus, $action);

        if (!$transition) {
            throw new \RuntimeException("No transition found: {$currentStatus} → {$action} in workflow '{$workflow->key}'");
        }

        // === VALIDATION LAYER ===

        // 1. Permission check
        if ($transition->permission && auth()->check()) {
            if (!auth()->user()->canViaPermission($transition->permission)) {
                throw new \RuntimeException("Permission denied: {$transition->permission}");
            }
        }

        // 2. Role check
        if ($transition->role && auth()->check()) {
            if (!auth()->user()->hasRole($transition->role)) {
                throw new \RuntimeException("Role required: {$transition->role}");
            }
        }

        // 3. Guard check (custom logic class)
        if ($transition->guard && class_exists($transition->guard)) {
            $guard = app($transition->guard);
            if (!$guard->check($entity)) {
                throw new \RuntimeException("Guard failed: {$transition->guard}");
            }
        }

        // 4. Custom conditions
        if ($transition->conditions) {
            $this->evaluateConditions($transition->conditions, $entity);
        }

        // === TRANSITION EXECUTION ===

        $fromState = $currentStatus;
        $toState = $transition->to_state;

        $entity->status = $toState;
        if (method_exists($entity, 'setStatus')) {
            $entity->setStatus($toState);
        } else {
            if (!empty($extra)) {
                $entity->fill($extra);
            }
            $entity->save();
        }

        // === EMIT EVENT via Laravel native dispatcher ===
        // Sprint 7.2D: Replaced EventBus with Laravel's event() helper.
        // EventLogger (wildcard listener) handles event_logs persistence.
        event(new WorkflowStateChanged(
            entity:      $entity,
            workflowKey: $workflow->key,
            fromState:   $fromState,
            toState:     $toState,
            action:      $action,
            context:     array_merge($extra, [
                'workflow_id'    => $workflow->id,
                'transition_id'  => $transition->id,
                'transition_label' => $transition->label,
                'permission'     => $transition->permission,
                'is_auto'        => $transition->is_auto,
            ]),
        ));

        return [
            'from'       => $fromState,
            'to'         => $toState,
            'transition' => $transition->label ?? $action,
            'workflow'   => $workflow->key,
        ];
    }

    /**
     * Check if a transition is possible without executing it.
     */
    public function canTransition(Model $entity, string $action): bool
    {
        try {
            $workflow = $this->resolveWorkflow($entity);
            if (!$workflow) return false;
            $currentStatus = $entity->status ?? 'draft';
            $transition = $this->findTransition($workflow, $currentStatus, $action);
            if (!$transition) return false;
            if ($transition->permission && auth()->check() && !auth()->user()->canViaPermission($transition->permission)) return false;
            if ($transition->role && auth()->check() && !auth()->user()->hasRole($transition->role)) return false;
            return true;
        } catch (\Throwable) { return false; }
    }

    /**
     * Get available transitions from the current state.
     */
    public function availableTransitions(Model $entity): array
    {
        $workflow = $this->resolveWorkflow($entity);
        if (!$workflow) return [];
        $current = $entity->status ?? 'draft';

        return $workflow->transitions()
            ->where('from_state', $current)
            ->where('is_active', true)
            ->get()
            ->map(fn($t) => [
                'action'       => $t->to_state,
                'label'        => $t->label ?? $t->to_state,
                'permission'   => $t->permission,
                'is_auto'      => $t->is_auto,
                'can_execute'  => $this->canTransition($entity, $t->to_state),
            ])->toArray();
    }

    /**
     * Resolve which workflow applies to an entity.
     */
    public function resolveWorkflow(Model $entity): ?Workflow
    {
        $class = get_class($entity);
        $cacheKey = self::CACHE_PREFIX . 'resolve:' . md5($class);

        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($class, $entity) {
            $wf = Workflow::where('model', $class)->where('is_active', true)->first();
            if ($wf) return $wf;

            $map = [
                \App\Models\Tenant\Request::class   => 'request',
                \App\Models\Tenant\Service::class   => 'service',
                \App\Models\Tenant\WorkOrder::class => 'work_order',
                \App\Models\Tenant\Sale::class      => 'sale',
            ];
            foreach ($map as $fqcn => $key) {
                if ($entity instanceof $fqcn) {
                    return Workflow::where('key', $key)->where('is_active', true)->first();
                }
            }
            return null;
        });
    }

    public function getWorkflow(string $key): ?Workflow
    {
        return Cache::remember(self::CACHE_PREFIX . $key, self::CACHE_TTL, fn() =>
            Workflow::where('key', $key)->where('is_active', true)->first()
        );
    }

    public function getStates(string $workflowKey): array
    {
        $wf = $this->getWorkflow($workflowKey);
        return $wf ? $wf->states()->where('is_active', true)->orderBy('sort_order')->get()->toArray() : [];
    }

    public function getStateGraph(string $workflowKey): array
    {
        $wf = $this->getWorkflow($workflowKey);
        if (!$wf) return [];

        $states = $wf->states()->where('is_active', true)->get()
            ->map(fn($s) => ['key' => $s->key, 'label' => $s->label, 'color' => $s->color, 'is_terminal' => $s->is_terminal])
            ->toArray();

        $transitions = $wf->transitions()->where('is_active', true)->get()
            ->map(fn($t) => ['from' => $t->from_state, 'to' => $t->to_state, 'label' => $t->label, 'is_auto' => $t->is_auto])
            ->toArray();

        return ['key' => $wf->key, 'label' => $wf->label, 'initial' => $wf->initial_state, 'states' => $states, 'transitions' => $transitions];
    }

    public function clearCache(): void { Cache::flush(); }

    // ======== PRIVATE ========

    private function findTransition(Workflow $workflow, string $currentStatus, string $action): ?WorkflowTransition
    {
        $cacheKey = self::CACHE_PREFIX . "tr:{$workflow->id}:{$currentStatus}:{$action}";
        return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($workflow, $currentStatus, $action) {
            $t = $workflow->transitions()
                ->where('from_state', $currentStatus)
                ->where('to_state', $action)
                ->where('is_active', true)
                ->first();
            if ($t) return $t;
            return $workflow->transitions()
                ->where('from_state', $currentStatus)
                ->where('label', $action)
                ->where('is_active', true)
                ->first();
        });
    }

    private function evaluateConditions($conditions, Model $entity): void
    {
        foreach ($conditions as $cond) {
            $field = $cond['field'] ?? null;
            $op = $cond['operator'] ?? '=';
            $value = $cond['value'] ?? null;
            $actual = data_get($entity, $field);

            $pass = match ($op) {
                '='  => $actual == $value,
                '!=' => $actual != $value,
                'in' => in_array($actual, (array) $value),
                'not_in' => !in_array($actual, (array) $value),
                'exists' => !empty($actual),
                'empty'  => empty($actual),
                'gt'  => $actual > $value,
                'lt'  => $actual < $value,
                'gte' => $actual >= $value,
                'lte' => $actual <= $value,
                default => true,
            };

            if (!$pass) {
                $msg = $cond['message'] ?? "Condition failed: {$field} {$op} " . json_encode($value);
                throw new \RuntimeException($msg);
            }
        }
    }
}
