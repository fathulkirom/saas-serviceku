<?php

namespace App\Enterprise\Automation;

enum ConditionOperator: string
{
    case EQUALS = 'eq';
    case NOT_EQUALS = 'neq';
    case GREATER = 'gt';
    case LESS = 'lt';
    case BETWEEN = 'between';
    case CONTAINS = 'contains';
    case STARTS_WITH = 'starts_with';
    case ENDS_WITH = 'ends_with';
    case IN = 'in';
    case NOT_IN = 'not_in';
    case EMPTY = 'empty';
    case NOT_EMPTY = 'not_empty';
    case ROLE = 'role';
    case PERMISSION = 'permission';
    case BRANCH = 'branch';
    case BUSINESS_TYPE = 'business_type';
    case PLAN = 'plan';
    case FEATURE = 'feature';
    case DATE_BEFORE = 'date_before';
    case DATE_AFTER = 'date_after';
    case DAY_IS = 'day_is';
    case MONTH_IS = 'month_is';

    public function label(): string
    {
        return match ($this) {
            self::EQUALS => 'Equals',
            self::NOT_EQUALS => 'Not Equals',
            self::GREATER => 'Greater Than',
            self::LESS => 'Less Than',
            self::BETWEEN => 'Between',
            self::CONTAINS => 'Contains',
            self::STARTS_WITH => 'Starts With',
            self::ENDS_WITH => 'Ends With',
            self::IN => 'In List',
            self::NOT_IN => 'Not In List',
            self::EMPTY => 'Is Empty',
            self::NOT_EMPTY => 'Is Not Empty',
            self::ROLE => 'User Role',
            self::PERMISSION => 'Has Permission',
            self::BRANCH => 'Branch Is',
            self::BUSINESS_TYPE => 'Business Type',
            self::PLAN => 'Plan Level',
            self::FEATURE => 'Feature Enabled',
            self::DATE_BEFORE => 'Before Date',
            self::DATE_AFTER => 'After Date',
            self::DAY_IS => 'Day Is',
            self::MONTH_IS => 'Month Is',
        };
    }
}
