<?php

namespace App\Jobs;

use App\Models\Tenant\AutomationRule;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Executes a delayed automation rule.
 * Dispatched by AutomationEngine when a rule has delay_minutes > 0.
 */
class ExecuteAutomationRule implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Retry with exponential backoff */
    public $tries = 3;
    public $maxExceptions = 5;
    public $timeout = 120;

    public function backoff(): array
    {
        return [30, 120, 600]; // 30s, 2min, 10min
    }

    public function __construct(
        private int $ruleId,
        private $entity,
        private string $event,
        private array $context = [],
    ) {}

    public function handle(): void
    {
        $rule = AutomationRule::find($this->ruleId);
        if (!$rule || !$rule->is_active) return;

        $engine = app(\App\Services\AutomationEngine::class);
        $result = $engine->executeAction($rule, $this->entity, $this->context);

        \App\Models\Tenant\AutomationLog::create([
            'automation_rule_id' => $rule->id,
            'entity_type' => get_class($this->entity),
            'entity_id' => $this->entity->getKey(),
            'event' => $this->event,
            'status' => $result['status'],
            'message' => $result['message'],
            'executed_at' => now(),
            'context' => json_encode($this->context),
        ]);
    }

    /** Dead letter handler */
    public function failed(\Throwable $e): void
    {
        \Illuminate\Support\Facades\Log::error('ExecuteAutomationRule failed permanently', [
            'rule_id' => $this->ruleId, 'event' => $this->event, 'error' => $e->getMessage(),
        ]);
    }
}
