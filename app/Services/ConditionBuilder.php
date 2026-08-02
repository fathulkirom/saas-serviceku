<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;

/**
 * Condition Builder V2 — supports AND, OR, NOT, nested groups.
 *
 * Schema (JSON in automation_rules.conditions):
 * {
 *   "type": "group",
 *   "operator": "AND",        // AND | OR
 *   "conditions": [
 *     {"field": "status", "operator": "=", "value": "selesai"},
 *     {"field": "priority", "operator": "in", "value": ["vip", "express"]},
 *     {
 *       "type": "group",
 *       "operator": "OR",
 *       "conditions": [
 *         {"field": "customer.type", "operator": "=", "value": "corporate"},
 *         {"field": "amount", "operator": "gt", "value": 500000}
 *       ]
 *     },
 *     {"type": "not", "condition": {"field": "customer.is_blacklisted", "operator": "=", "value": true}}
 *   ]
 * }
 *
 * Usage:
 *   $builder = new ConditionBuilder();
 *   $pass = $builder->evaluate($rule->conditions, $entity, $context);
 */
class ConditionBuilder
{
    private const OPERATORS = [
        '=', '!=', '==', '===',
        '>', '<', '>=', '<=', 'gt', 'lt', 'gte', 'lte',
        'in', 'not_in', 'contains', 'not_contains',
        'starts_with', 'ends_with',
        'exists', 'empty', 'not_empty',
        'between', 'not_between',
        'regex', 'date_before', 'date_after', 'date_between',
    ];

    /**
     * Evaluate a condition tree against an entity.
     */
    public function evaluate(?array $conditions, Model $entity, array $context = []): bool
    {
        if (empty($conditions)) return true;

        $type = $conditions['type'] ?? 'group';

        return match ($type) {
            'group'     => $this->evaluateGroup($conditions, $entity, $context),
            'not'       => !$this->evaluate($conditions['condition'] ?? [], $entity, $context),
            'condition' => $this->evaluateSingle($conditions, $entity, $context),
            default     => $this->evaluateSingle($conditions, $entity, $context),
        };
    }

    /**
     * Evaluate a group (AND/OR).
     */
    private function evaluateGroup(array $group, Model $entity, array $context): bool
    {
        $operator = strtoupper($group['operator'] ?? 'AND');
        $conditions = $group['conditions'] ?? [];

        if (empty($conditions)) return true;

        foreach ($conditions as $cond) {
            $result = $this->evaluate($cond, $entity, $context);

            if ($operator === 'AND' && !$result) return false; // Short-circuit AND
            if ($operator === 'OR' && $result) return true;     // Short-circuit OR
        }

        return $operator === 'AND'; // AND: all passed | OR: none passed
    }

    /**
     * Evaluate a single condition.
     */
    private function evaluateSingle(array $cond, Model $entity, array $context): bool
    {
        $field = $cond['field'] ?? null;
        if (!$field) return true;

        $op = strtolower($cond['operator'] ?? '=');
        $value = $cond['value'] ?? null;

        // Resolve field value — supports dot notation and context
        $actual = match (true) {
            str_starts_with($field, 'context.') => data_get($context, substr($field, 8)),
            str_starts_with($field, 'customer.') => data_get($entity, $field),
            str_starts_with($field, 'branch.') => data_get($entity, $field),
            default => data_get($entity, $field),
        };

        return match ($op) {
            '=', '=='         => $actual == $value,
            '===', '!=='      => $actual === $value,
            '!='              => $actual != $value,
            '>', 'gt'        => (float) $actual > (float) $value,
            '<', 'lt'        => (float) $actual < (float) $value,
            '>=', 'gte'      => (float) $actual >= (float) $value,
            '<=', 'lte'      => (float) $actual <= (float) $value,
            'in'              => in_array($actual, (array) $value, false),
            'not_in'          => !in_array($actual, (array) $value, false),
            'contains'        => is_string($actual) && str_contains((string) $actual, (string) $value),
            'not_contains'    => is_string($actual) && !str_contains((string) $actual, (string) $value),
            'starts_with'     => is_string($actual) && str_starts_with((string) $actual, (string) $value),
            'ends_with'       => is_string($actual) && str_ends_with((string) $actual, (string) $value),
            'exists'          => !empty($actual),
            'empty'           => empty($actual),
            'not_empty'       => !empty($actual),
            'between'         => is_array($value) && count($value) === 2 && $actual >= $value[0] && $actual <= $value[1],
            'not_between'     => is_array($value) && count($value) === 2 && !($actual >= $value[0] && $actual <= $value[1]),
            'regex'           => is_string($actual) && preg_match((string) $value, (string) $actual) === 1,
            'date_before'     => strtotime((string) $actual) < strtotime((string) $value),
            'date_after'      => strtotime((string) $actual) > strtotime((string) $value),
            'date_between'    => is_array($value) && count($value) === 2
                && strtotime((string) $actual) >= strtotime((string) $value[0])
                && strtotime((string) $actual) <= strtotime((string) $value[1]),
            default           => true,
        };
    }
}
