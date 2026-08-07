<?php

namespace App\Enterprise\Form;

/**
 * FormDefinition — Complete form schema definition.
 * 
 * One definition per form (e.g., service.create, customer.edit, product.create).
 * Contains all fields, sections, actions, and layout config.
 */
class FormDefinition
{
    /** @var FormField[] */
    public array $fields = [];

    /** @var FormSection[] */
    public array $sections = [];

    /** @var FormAction[] */
    public array $actions = [];

    public function __construct(
        public readonly string $id,
        public readonly string $title = '',
        public readonly string $method = 'POST',
        public readonly ?string $endpoint = null,
        public readonly string $layout = 'default',   // default | wizard | stepper | split | inspector
        public readonly array $roles = [],
        public readonly array $permissions = [],
        public readonly array $features = [],
        public readonly array $config = [],
    ) {}

    /**
     * Add a field to the form.
     */
    public function addField(FormField $field): self
    {
        $this->fields[] = $field;
        return $this;
    }

    /**
     * Add multiple fields at once.
     */
    public function addFields(array $fields): self
    {
        foreach ($fields as $field) {
            $this->addField($field);
        }
        return $this;
    }

    /**
     * Add a section to the form.
     */
    public function addSection(FormSection $section): self
    {
        $this->sections[] = $section;
        return $this;
    }

    /**
     * Add an action button.
     */
    public function addAction(FormAction $action): self
    {
        $this->actions[] = $action;
        return $this;
    }

    /**
     * Build the full form schema for frontend.
     */
    public function toSchema(string $userRole, array $planAccess, array $rolePermissions, string $businessType): array
    {
        // Filter visible fields
        $visibleFields = array_values(array_filter(
            array_map(fn($f) => $f->toArray($userRole, $planAccess, $rolePermissions, $businessType), $this->fields),
            fn($f) => !$f['hidden']
        ));

        // Filter visible sections
        $visibleSections = array_values(array_filter(
            array_map(fn($s) => $s->toArray($userRole, $planAccess, $rolePermissions, $businessType), $this->sections),
            fn($s) => $s['visible']
        ));

        // Filter actions by role
        $visibleActions = array_values(array_filter(
            array_map(fn($a) => $a->toArray(), $this->actions),
            fn($a) => empty($this->roles) || in_array($userRole, $this->roles)
        ));

        return [
            'id' => $this->id,
            'title' => $this->title,
            'method' => $this->method,
            'endpoint' => $this->endpoint,
            'layout' => $this->layout,
            'config' => $this->config,
            'fields' => $visibleFields,
            'sections' => $visibleSections,
            'actions' => $visibleActions,
            'meta' => [
                'fieldCount' => count($visibleFields),
                'sectionCount' => count($visibleSections),
                'hasFiles' => $this->hasFileFields(),
                'hasRelations' => $this->hasRelationFields(),
            ],
        ];
    }

    /**
     * Get validation rules for Laravel.
     */
    public function getValidationRules(): array
    {
        $rules = [];
        foreach ($this->fields as $field) {
            if (!empty($field->rules)) {
                $rules[$field->key] = $field->rules;
            } elseif ($field->required) {
                $rules[$field->key] = ['required'];
            }
        }
        return $rules;
    }

    private function hasFileFields(): bool
    {
        return !empty(array_filter($this->fields, fn($f) => in_array($f->type, ['photo', 'file', 'gallery', 'pdf', 'signature', 'image'])));
    }

    private function hasRelationFields(): bool
    {
        return !empty(array_filter($this->fields, fn($f) => $f->relationModel !== null));
    }
}
