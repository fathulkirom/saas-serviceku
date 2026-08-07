<?php

namespace App\Enterprise\Form;

/**
 * FormRegistry — Central registry for all forms.
 * 
 * Every module registers its form definitions here.
 */
class FormRegistry
{
    /** @var FormDefinition[] */
    protected array $forms = [];

    /**
     * Register a form definition.
     */
    public function register(FormDefinition $form): self
    {
        $this->forms[$form->id] = $form;
        return $this;
    }

    /**
     * Get a form definition by ID.
     */
    public function get(string $id): ?FormDefinition
    {
        return $this->forms[$id] ?? null;
    }

    /**
     * Check if a form is registered.
     */
    public function has(string $id): bool
    {
        return isset($this->forms[$id]);
    }

    /**
     * Get all registered form IDs.
     */
    public function ids(): array
    {
        return array_keys($this->forms);
    }

    /**
     * Resolve a form schema with user context.
     */
    public function resolve(string $formId, string $userRole, array $planAccess, array $rolePermissions, string $businessType, mixed $data = null): ?array
    {
        $form = $this->get($formId);
        if (!$form) return null;

        $schema = $form->toSchema($userRole, $planAccess, $rolePermissions, $businessType);

        // Inject data values into fields
        if ($data) {
            $schema['fields'] = array_map(function ($field) use ($data) {
                $key = $field['key'];
                $value = is_array($data) ? ($data[$key] ?? null) : ($data->{$key} ?? null);
                $field['value'] = $value ?? $field['default'];
                return $field;
            }, $schema['fields']);
        } else {
            $schema['fields'] = array_map(function ($field) {
                $field['value'] = $field['default'];
                return $field;
            }, $schema['fields']);
        }

        return $schema;
    }
}
