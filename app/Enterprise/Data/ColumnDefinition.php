<?php

namespace App\Enterprise\Data;

/**
 * ColumnDefinition — Defines a table column.
 * 
 * Supports 30+ display types: text, number, currency, badge, status, date, 
 * avatar, tags, progress, relation, actions, custom slot, etc.
 */
class ColumnDefinition
{
    public function __construct(
        public readonly string $key,
        public readonly string $label = '',
        public readonly string $type = 'text',         // text|number|currency|badge|status|date|datetime|avatar|tags|progress|relation|actions|boolean|rating|qrcode|barcode|slot
        public readonly bool $sortable = false,
        public readonly bool $filterable = false,
        public readonly bool $searchable = true,
        public readonly bool $hidden = false,
        public readonly bool $pinnable = false,
        public readonly ?string $width = null,          // e.g. '120px', '15%'
        public readonly ?string $minWidth = null,
        public readonly ?string $maxWidth = null,
        public readonly string $align = 'left',         // left|center|right
        public readonly bool $bold = false,
        public readonly bool $wrap = false,
        public readonly bool $truncate = true,
        public readonly ?string $format = null,         // For number/currency: 'decimal', 'percent', etc.
        public readonly ?string $relationName = null,   // For relation type
        public readonly ?string $relationDisplay = null,// Display field from relation
        public readonly ?string $badgeMap = null,       // For badge type: JSON map value→color
        public readonly ?string $statusMap = null,      // For status type: status key mapping
        public readonly ?string $emptyText = '-',       // Display when value is null
        public readonly ?string $description = null,    // Column description/tooltip
        public readonly array $roles = [],              // Role visibility gate
        public readonly array $permissions = [],
        public readonly array $features = [],
        public readonly int $order = 0,
        public readonly bool $aggregate = false,        // Show aggregate in footer
        public readonly ?string $aggregateType = null,  // sum|avg|count|min|max
        public readonly array $meta = [],
    ) {}

    public function toArray(string $userRole, array $planAccess, array $rolePermissions): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->type,
            'sortable' => $this->sortable,
            'filterable' => $this->filterable,
            'searchable' => $this->searchable,
            'hidden' => $this->shouldHide($userRole, $planAccess, $rolePermissions),
            'pinnable' => $this->pinnable,
            'width' => $this->width,
            'minWidth' => $this->minWidth,
            'maxWidth' => $this->maxWidth,
            'align' => $this->align,
            'bold' => $this->bold,
            'wrap' => $this->wrap,
            'truncate' => $this->truncate,
            'format' => $this->format,
            'relationName' => $this->relationName,
            'relationDisplay' => $this->relationDisplay,
            'badgeMap' => $this->badgeMap,
            'statusMap' => $this->statusMap,
            'emptyText' => $this->emptyText,
            'description' => $this->description,
            'order' => $this->order,
            'aggregate' => $this->aggregate,
            'aggregateType' => $this->aggregateType,
            'meta' => $this->meta,
        ];
    }

    public function shouldHide(string $userRole, array $planAccess, array $rolePermissions): bool
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
        return false;
    }
}

/**
 * FilterDefinition — Defines a table filter.
 */
class FilterDefinition
{
    public function __construct(
        public readonly string $key,
        public readonly string $label = '',
        public readonly string $type = 'text',          // text|number|select|date|date_range|status|multi_select|toggle|range
        public readonly ?array $options = null,         // For select/multi_select
        public readonly ?string $placeholder = null,
        public readonly mixed $default = null,
        public readonly bool $quick = false,            // Show in quick filter bar
        public readonly int $order = 0,
    ) {}

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'label' => $this->label,
            'type' => $this->type,
            'options' => $this->options,
            'placeholder' => $this->placeholder,
            'default' => $this->default,
            'quick' => $this->quick,
            'order' => $this->order,
        ];
    }
}

/**
 * BulkAction — Defines a bulk action for selected rows.
 */
class BulkAction
{
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly ?string $icon = null,
        public readonly string $variant = 'default',     // default|danger|success
        public readonly bool $confirm = false,
        public readonly ?string $confirmMessage = null,
        public readonly array $roles = [],
        public readonly array $permissions = [],
        public readonly ?int $minSelected = 1,
        public readonly ?string $endpoint = null,        // POST endpoint
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'icon' => $this->icon,
            'variant' => $this->variant,
            'confirm' => $this->confirm,
            'confirmMessage' => $this->confirmMessage,
            'minSelected' => $this->minSelected,
            'endpoint' => $this->endpoint,
        ];
    }
}
