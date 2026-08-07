<?php

namespace App\Enterprise\Reporting;

use Illuminate\Database\Eloquent\Builder;

/**
 * MetricDefinition — A single KPI/metric in a report.
 */
class MetricDefinition
{
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly string $aggregation,        // sum|count|avg|min|max|median|growth|percent|margin|sla|custom
        public readonly string $field,
        public readonly ?string $table = null,
        public readonly string $format = 'number',           // number|currency|percent|decimal|duration
        public readonly ?string $color = null,               // For KPI cards
        public readonly ?string $icon = null,
        public readonly bool $trend = false,                 // Show trend indicator
        public readonly int $order = 0,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id, 'label' => $this->label,
            'aggregation' => $this->aggregation, 'field' => $this->field,
            'format' => $this->format, 'color' => $this->color, 'icon' => $this->icon,
            'trend' => $this->trend, 'order' => $this->order,
        ];
    }
}

/**
 * DimensionDefinition — A grouping/breakdown dimension.
 */
class DimensionDefinition
{
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly string $field,
        public readonly ?string $table = null,
        public readonly string $type = 'string',            // string|date|month|week|year|status|branch|user
        public readonly int $order = 0,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id, 'label' => $this->label,
            'field' => $this->field, 'type' => $this->type, 'order' => $this->order,
        ];
    }
}

/**
 * ReportFilter — Filter available for a report.
 */
class ReportFilter
{
    public function __construct(
        public readonly string $id,
        public readonly string $label,
        public readonly string $type = 'select',             // select|date_range|multi_select|toggle|text
        public readonly ?string $field = null,
        public readonly ?array $options = null,
        public readonly mixed $default = null,
        public readonly bool $required = false,
        public readonly int $order = 0,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id, 'label' => $this->label, 'type' => $this->type,
            'field' => $this->field, 'options' => $this->options,
            'default' => $this->default, 'required' => $this->required, 'order' => $this->order,
        ];
    }
}

/**
 * ReportDefinition — Complete report definition.
 */
class ReportDefinition
{
    /** @var MetricDefinition[] */
    public array $metrics = [];

    /** @var DimensionDefinition[] */
    public array $dimensions = [];

    /** @var ReportFilter[] */
    public array $filters = [];

    public function __construct(
        public readonly string $id,
        public readonly string $title = '',
        public readonly ?string $description = null,
        public readonly string $type = 'summary',            // summary|detail|grouped|pivot|trend|comparison
        public readonly ?string $primaryTable = null,       // Main DB table
        public readonly ?string $modelClass = null,         // Eloquent model
        public readonly string $chartType = 'bar',           // bar|line|pie|donut|area|kpi|table
        public readonly array $roles = [],
        public readonly array $permissions = [],
        public readonly array $features = [],
        public readonly bool $exportable = true,
        public readonly bool $schedulable = false,
        public readonly bool $cacheable = true,
        public readonly int $cacheTtlMinutes = 15,
        public readonly array $config = [],
    ) {}

    public function addMetric(MetricDefinition $m): self
    {
        $this->metrics[] = $m;

        return $this;
    }

    public function addDimension(DimensionDefinition $d): self
    {
        $this->dimensions[] = $d;

        return $this;
    }

    public function addFilter(ReportFilter $f): self
    {
        $this->filters[] = $f;

        return $this;
    }

    public function addMetrics(array $metrics): self
    {
        foreach ($metrics as $m) {
            $this->addMetric($m);
        }

return $this;
    }

    public function addDimensions(array $dims): self
    {
        foreach ($dims as $d) {
            $this->addDimension($d);
        }

return $this;
    }

    public function addFilters(array $filters): self
    {
        foreach ($filters as $f) {
            $this->addFilter($f);
        }

return $this;
    }

    public function toArray(bool $isAccessible = true): array
    {
        return [
            'id' => $this->id, 'title' => $this->title,
            'description' => $this->description, 'type' => $this->type,
            'chartType' => $this->chartType,
            'metrics' => array_map(fn ($m) => $m->toArray(), $this->metrics),
            'dimensions' => array_map(fn ($d) => $d->toArray(), $this->dimensions),
            'filters' => array_map(fn ($f) => $f->toArray(), $this->filters),
            'exportable' => $this->exportable,
            'schedulable' => $this->schedulable,
            'config' => $this->config,
            'accessible' => $isAccessible,
        ];
    }

    /**
     * Build the base Eloquent query for this report.
     */
    public function buildQuery(array $filterValues = []): ?Builder
    {
        if (! $this->modelClass || ! class_exists($this->modelClass)) {
            return null;
        }
        $query = $this->modelClass::query();

        foreach ($this->filters as $filter) {
            $val = $filterValues[$filter->id] ?? $filter->default;
            if ($val === null) {
                continue;
            }

            match ($filter->type) {
                'select', 'toggle' => $query->where($filter->field ?? $filter->id, $val),
                'date_range' => $this->applyDateFilter($query, $filter->field ?? $filter->id, $val),
                'multi_select' => $query->whereIn($filter->field ?? $filter->id, (array) $val),
                'text' => $query->where($filter->field ?? $filter->id, 'like', "%{$val}%"),
                default => null,
            };
        }

        return $query;
    }

    private function applyDateFilter($query, string $field, mixed $val): void
    {
        if (is_array($val)) {
            if (! empty($val['start'])) {
                $query->whereDate($field, '>=', $val['start']);
            }
            if (! empty($val['end'])) {
                $query->whereDate($field, '<=', $val['end']);
            }
        }
    }

    public function isAccessible(string $userRole, array $planAccess, array $rolePermissions): bool
    {
        if (! empty($this->roles) && ! in_array($userRole, $this->roles)) {
            return false;
        }
        if (! empty($this->permissions)) {
            foreach ($this->permissions as $p) {
                if (! in_array($p, $rolePermissions)) {
                    return false;
                }
            }
        }
        if (! empty($this->features)) {
            foreach ($this->features as $f) {
                if (($planAccess[$f] ?? 'none') === 'none') {
                    return false;
                }
            }
        }

        return true;
    }
}
