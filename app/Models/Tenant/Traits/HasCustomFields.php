<?php

namespace App\Models\Tenant\Traits;

use App\Models\Tenant\CustomField;
use App\Models\Tenant\CustomFieldValue;
use Illuminate\Support\Collection;

trait HasCustomFields
{
    /**
     * Module name for custom fields (customer, service, device).
     */
    abstract public function customFieldModule(): string;

    /**
     * Get custom field definitions for this module.
     */
    public function getCustomFieldDefinitions(): Collection
    {
        return CustomField::where('module', $this->customFieldModule())
            ->where('is_active', true)
            ->orderBy('ordering')
            ->get();
    }

    /**
     * Get existing values for this entity's custom fields.
     */
    public function getCustomFieldValues(): Collection
    {
        $fieldIds = CustomField::where('module', $this->customFieldModule())
            ->pluck('id');

        return CustomFieldValue::whereIn('custom_field_id', $fieldIds)
            ->where('entity_id', $this->id)
            ->get()
            ->keyBy('custom_field_id');
    }

    /**
     * Save custom field values for this entity.
     */
    public function saveCustomFieldValues(array $values): void
    {
        $fields = CustomField::where('module', $this->customFieldModule())
            ->where('is_active', true)
            ->get();

        foreach ($fields as $field) {
            $key = "custom_field_{$field->id}";
            if (array_key_exists($key, $values)) {
                $value = $values[$key];

                if (is_array($value)) {
                    $value = json_encode($value);
                }

                CustomFieldValue::updateOrCreate(
                    [
                        'custom_field_id' => $field->id,
                        'entity_id' => $this->id,
                    ],
                    ['value' => $value]
                );
            }
        }
    }

    /**
     * Get custom field values formatted for Vue form (keyed by custom_field_id).
     */
    public function getCustomFieldFormData(): array
    {
        $data = [];
        foreach ($this->getCustomFieldValues() as $fieldId => $value) {
            $data["custom_field_{$fieldId}"] = $value->value;
        }
        return $data;
    }
}
