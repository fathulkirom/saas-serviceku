<?php

namespace App\Enterprise\Form;

/**
 * FormField — Definisi satu field dalam form.
 * 
 * Supports 40+ field types. Semua konfigurasi di sini.
 * Tidak ada hardcode — setiap field mendefinisikan dirinya sendiri.
 */
class FormField
{
    public function __construct(
        public readonly string $key,
        public readonly string $type = 'text',
        public readonly string $label = '',
        public readonly ?string $placeholder = null,
        public readonly ?string $helper = null,
        public readonly mixed $default = null,
        public readonly bool $required = false,
        public readonly bool $disabled = false,
        public readonly bool $readonly = false,
        public readonly bool $hidden = false,
        public readonly int $cols = 12,               // 1-12 column span
        public readonly ?string $section = null,       // Section ID this field belongs to
        public readonly array $options = [],           // For select/radio/checkbox
        public readonly ?string $optionLabel = null,   // For async selects
        public readonly ?string $optionValue = null,   // For async selects
        public readonly ?string $asyncUrl = null,      // For async autocomplete
        public readonly array $rules = [],             // Laravel validation rules
        public readonly array $conditions = [],        // Conditional visibility rules
        public readonly array $meta = [],              // Extra metadata
        public readonly array $roles = [],             // Roles that can see this field
        public readonly array $permissions = [],       // Permissions required
        public readonly array $features = [],          // Feature flags required
        public readonly array $businessTypes = [],     // Business types
        public readonly array $attrs = [],             // HTML attributes
        public readonly ?string $prefix = null,        // Input prefix (e.g. 'Rp')
        public readonly ?string $suffix = null,        // Input suffix (e.g. 'kg')
        public readonly ?int $min = null,
        public readonly ?int $max = null,
        public readonly ?int $step = null,
        public readonly ?int $maxLength = null,
        public readonly ?string $accept = null,        // File accept types
        public readonly ?int $maxSize = null,          // File max size in MB
        public readonly bool $multiple = false,        // File multi-upload
        public readonly ?string $relationModel = null, // For relation picker
        public readonly ?string $relationDisplay = null,
        public readonly ?string $previewUrl = null,    // For image/file preview
        public readonly array $tabs = [],              // For tabbed sections
        public readonly ?string $description = null,   // Rich description
        public readonly ?int $order = null,            // Sort order
    ) {}

    /**
     * Convert to array for frontend consumption.
     */
    public function toArray(string $userRole, array $planAccess, array $rolePermissions, string $businessType): array
    {
        return [
            'key' => $this->key,
            'type' => $this->type,
            'label' => $this->label,
            'placeholder' => $this->placeholder ?? $this->label,
            'helper' => $this->helper,
            'default' => $this->default,
            'required' => $this->required,
            'disabled' => $this->disabled,
            'readonly' => $this->readonly,
            'hidden' => $this->shouldHide($userRole, $planAccess, $rolePermissions, $businessType),
            'cols' => $this->cols,
            'section' => $this->section,
            'options' => $this->options,
            'optionLabel' => $this->optionLabel,
            'optionValue' => $this->optionValue,
            'asyncUrl' => $this->asyncUrl,
            'rules' => $this->rules,
            'conditions' => $this->conditions,
            'meta' => $this->meta,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
            'min' => $this->min,
            'max' => $this->max,
            'step' => $this->step,
            'maxLength' => $this->maxLength,
            'accept' => $this->accept,
            'maxSize' => $this->maxSize,
            'multiple' => $this->multiple,
            'relationModel' => $this->relationModel,
            'relationDisplay' => $this->relationDisplay,
            'previewUrl' => $this->previewUrl,
            'tabs' => $this->tabs,
            'description' => $this->description,
            'order' => $this->order ?? 0,
            'attrs' => $this->attrs,
        ];
    }

    public function shouldHide(string $userRole, array $planAccess, array $rolePermissions, string $businessType): bool
    {
        if ($this->hidden) return true;
        if (!empty($this->roles) && !in_array($userRole, $this->roles)) return true;
        if (!empty($this->permissions)) {
            foreach ($this->permissions as $p) {
                if (!in_array($p, $rolePermissions)) return true;
            }
        }
        if (!empty($this->features)) {
            foreach ($this->features as $f) {
                if (($planAccess[$f] ?? 'none') === 'none') return true;
            }
        }
        if (!empty($this->businessTypes) && !in_array($businessType, $this->businessTypes)) return true;
        return false;
    }
}
