<?php

namespace App\Enterprise\Form;

/**
 * FormSection — Groups fields into logical sections.
 */
class FormSection
{
    public function __construct(
        public readonly string $id,
        public readonly string $label = '',
        public readonly ?string $icon = null,
        public readonly ?string $description = null,
        public readonly bool $collapsible = false,
        public readonly bool $collapsed = false,
        public readonly int $cols = 1,            // 1-4 column layout
        public readonly array $roles = [],
        public readonly array $permissions = [],
        public readonly array $features = [],
        public readonly string $layout = 'grid',   // grid | wizard | accordion | tabs
        public readonly int $order = 0,
    ) {}

    public function toArray(string $userRole, array $planAccess, array $rolePermissions, string $businessType): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'icon' => $this->icon,
            'description' => $this->description,
            'collapsible' => $this->collapsible,
            'collapsed' => $this->collapsed,
            'cols' => $this->cols,
            'layout' => $this->layout,
            'order' => $this->order,
            'visible' => $this->isVisible($userRole, $planAccess, $rolePermissions, $businessType),
        ];
    }

    public function isVisible(string $userRole, array $planAccess, array $rolePermissions, string $businessType): bool
    {
        if (!empty($this->roles) && !in_array($userRole, $this->roles)) return false;
        if (!empty($this->permissions)) {
            foreach ($this->permissions as $p) {
                if (!in_array($p, $rolePermissions)) return false;
            }
        }
        if (!empty($this->features)) {
            foreach ($this->features as $f) {
                if (($planAccess[$f] ?? 'none') === 'none') return false;
            }
        }
        return true;
    }
}

/**
 * FormAction — Defines a form action button.
 */
class FormAction
{
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly ?string $icon = null,
        public readonly string $variant = 'primary',  // primary | secondary | danger | success | outline
        public readonly ?string $shortcut = null,
        public readonly array $roles = [],
        public readonly array $permissions = [],
        public readonly string $position = 'toolbar',   // toolbar | footer | header | context
        public readonly bool $confirm = false,
        public readonly ?string $confirmMessage = null,
        public readonly int $order = 0,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'icon' => $this->icon,
            'variant' => $this->variant,
            'shortcut' => $this->shortcut,
            'position' => $this->position,
            'confirm' => $this->confirm,
            'confirmMessage' => $this->confirmMessage,
            'order' => $this->order,
        ];
    }
}
