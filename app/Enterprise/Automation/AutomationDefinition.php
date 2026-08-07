<?php

namespace App\Enterprise\Automation;

/**
 * ConditionClause — A single condition in an automation rule.
 */
class ConditionClause
{
    public function __construct(
        public readonly ConditionOperator $operator,
        public readonly string $field,
        public readonly mixed $value = null,
        public readonly ?string $logicGate = 'AND',  // AND | OR
    ) {}

    public function toArray(): array
    {
        return [
            'operator' => $this->operator->value,
            'operatorLabel' => $this->operator->label(),
            'field' => $this->field,
            'value' => $this->value,
            'logicGate' => $this->logicGate,
        ];
    }
}

/**
 * AutomationStep — One action step in an automation.
 */
class AutomationStep
{
    public function __construct(
        public readonly ActionType $action,
        public readonly array $config = [],
        public readonly ?string $delaySeconds = null,
        public readonly ?int $retries = 0,
        public readonly bool $continueOnError = false,
    ) {}

    public function toArray(): array
    {
        return [
            'action' => $this->action->value,
            'actionLabel' => $this->action->label(),
            'config' => $this->config,
            'delaySeconds' => $this->delaySeconds,
            'retries' => $this->retries,
            'continueOnError' => $this->continueOnError,
        ];
    }
}

/**
 * AutomationDefinition — Complete automation rule definition.
 */
class AutomationDefinition
{
    /** @var ConditionClause[] */
    public array $conditions = [];

    /** @var AutomationStep[] */
    public array $steps = [];

    public function __construct(
        public readonly string $id,
        public readonly string $name = '',
        public readonly ?string $description = null,
        public readonly TriggerType $trigger = TriggerType::MANUAL,
        public readonly ?string $module = null,         // e.g. 'service', 'inventory'
        public readonly ?string $modelClass = null,     // Eloquent model
        public readonly bool $enabled = true,
        public readonly ?string $schedule = null,       // Cron expression for schedule trigger
        public readonly array $roles = [],              // Who can edit
        public readonly array $config = [],
    ) {}

    public function addCondition(ConditionClause $condition): self
    {
        $this->conditions[] = $condition;

        return $this;
    }

    public function addStep(AutomationStep $step): self
    {
        $this->steps[] = $step;

        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'trigger' => $this->trigger->value,
            'triggerLabel' => $this->trigger->label(),
            'module' => $this->module,
            'enabled' => $this->enabled,
            'schedule' => $this->schedule,
            'conditions' => array_map(fn ($c) => $c->toArray(), $this->conditions),
            'steps' => array_map(fn ($s) => $s->toArray(), $this->steps),
            'config' => $this->config,
        ];
    }
}

/**
 * AutomationRegistry — Central registry for all automation rules.
 */
class AutomationRegistry
{
    /** @var AutomationDefinition[] */
    protected array $automations = [];

    public function register(AutomationDefinition $def): self
    {
        $this->automations[$def->id] = $def;

        return $this;
    }

    public function registerAll(array $defs): self
    {
        foreach ($defs as $def) {
            $this->register($def);
        }

        return $this;
    }

    /** @return AutomationDefinition[] */
    public function getByTrigger(TriggerType $trigger): array
    {
        return array_filter($this->automations, fn ($a) => $a->trigger === $trigger && $a->enabled);
    }

    /** @return AutomationDefinition[] */
    public function getByModule(string $module): array
    {
        return array_filter($this->automations, fn ($a) => $a->module === $module && $a->enabled);
    }

    public function get(string $id): ?AutomationDefinition
    {
        return $this->automations[$id] ?? null;
    }

    /** @return AutomationDefinition[] */
    public function all(): array
    {
        return $this->automations;
    }

    /** @return AutomationDefinition[] */
    public function enabled(): array
    {
        return array_filter($this->automations, fn ($a) => $a->enabled);
    }
}
