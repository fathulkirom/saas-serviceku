<?php

namespace App\Enterprise\Automation;

class AutomationEvaluator
{
    public function evaluate(AutomationDefinition $automation, AutomationContext $context): bool
    {
        if (empty($automation->conditions)) {
            return true;
        }

        $results = [];
        foreach ($automation->conditions as $condition) {
            $results[] = $this->evaluateCondition($condition, $context);
        }

        return ! in_array(false, $results, true);
    }

    private function evaluateCondition(ConditionClause $condition, AutomationContext $context): bool
    {
        $fieldValue = $context->getNewValue($condition->field) ?? $context->subject?->{$condition->field};
        $compareValue = $condition->value;

        return match ($condition->operator) {
            ConditionOperator::EQUALS => $fieldValue == $compareValue,
            ConditionOperator::NOT_EQUALS => $fieldValue != $compareValue,
            ConditionOperator::GREATER => (float) $fieldValue > (float) $compareValue,
            ConditionOperator::LESS => (float) $fieldValue < (float) $compareValue,
            ConditionOperator::CONTAINS => str_contains((string) $fieldValue, (string) $compareValue),
            ConditionOperator::STARTS_WITH => str_starts_with((string) $fieldValue, (string) $compareValue),
            ConditionOperator::ENDS_WITH => str_ends_with((string) $fieldValue, (string) $compareValue),
            ConditionOperator::IN => in_array($fieldValue, (array) $compareValue),
            ConditionOperator::NOT_IN => ! in_array($fieldValue, (array) $compareValue),
            ConditionOperator::EMPTY => empty($fieldValue),
            ConditionOperator::NOT_EMPTY => ! empty($fieldValue),
            ConditionOperator::ROLE => $context->user?->role === $compareValue,
            ConditionOperator::PERMISSION => in_array($compareValue, $context->extra['permissions'] ?? []),
            ConditionOperator::BRANCH => ($context->subject?->branch_id ?? null) == $compareValue,
            default => true,
        };
    }
}
