<?php

namespace App\Enterprise\Automation;

class AutomationRunner
{
    public function __construct(
        protected AutomationRegistry $registry,
        protected AutomationEvaluator $evaluator,
        protected AutomationDispatcher $dispatcher,
    ) {}

    /** @return AutomationResult[] */
    public function run(TriggerType $trigger, AutomationContext $context): array
    {
        $automations = $this->registry->getByTrigger($trigger);
        $results = [];

        foreach ($automations as $automation) {
            if (! $this->evaluator->evaluate($automation, $context)) {
                continue;
            }

            $results[$automation->id] = $this->dispatcher->dispatch($automation, $context);
        }

        return $results;
    }

    public function runById(string $automationId, AutomationContext $context): ?AutomationResult
    {
        $automation = $this->registry->get($automationId);
        if (! $automation || ! $automation->enabled) {
            return null;
        }

        if (! $this->evaluator->evaluate($automation, $context)) {
            return null;
        }

        return $this->dispatcher->dispatch($automation, $context);
    }
}
