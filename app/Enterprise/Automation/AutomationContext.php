<?php

namespace App\Enterprise\Automation;

class AutomationContext
{
    public function __construct(
        public readonly string $triggerEvent,
        public readonly ?object $subject = null,
        public readonly array $original = [],
        public readonly array $changes = [],
        public readonly ?object $user = null,
        public readonly array $extra = [],
    ) {}

    public function getOldValue(string $field): mixed
    {
        return $this->original[$field] ?? null;
    }

    public function getNewValue(string $field): mixed
    {
        return $this->changes[$field] ?? ($this->subject?->{$field} ?? null);
    }
}
