<?php

namespace App\Enterprise\Data;

use Illuminate\Database\Eloquent\Builder;

/**
 * DataDefinition — Complete table definition.
 * 
 * One definition per list page (e.g., service.index, customer.index).
 */
class DataDefinition
{
    /** @var ColumnDefinition[] */
    public array $columns = [];

    /** @var FilterDefinition[] */
    public array $filters = [];

    /** @var BulkAction[] */
    public array $bulkActions = [];

    public function __construct(
        public readonly string $id,
        public readonly string $title = '',
        public readonly ?string $modelClass = null,    // Eloquent model
        public readonly array $defaultSort = [],        // ['key' => 'direction']
        public readonly int $perPage = 25,
        public readonly array $perPageOptions = [10, 25, 50, 100],
        public readonly bool $selectable = true,
        public readonly bool $exportable = false,
        public readonly bool $searchable = true,
        public readonly string $rowKey = 'id',
        public readonly array $views = ['table'],        // table|card|grid|kanban|timeline
        public readonly bool $showToolbar = true,
        public readonly bool $virtualScroll = false,
        public readonly array $config = [],
    ) {}

    public function addColumn(ColumnDefinition $col): self { $this->columns[] = $col; return $this; }
    public function addColumns(array $cols): self { foreach ($cols as $c) $this->addColumn($c); return $this; }
    public function addFilter(FilterDefinition $f): self { $this->filters[] = $f; return $this; }
    public function addBulkAction(BulkAction $a): self { $this->bulkActions[] = $a; return $this; }

    /**
     * Build schema for frontend.
     */
    public function toSchema(string $userRole, array $planAccess, array $rolePermissions): array
    {
        $visibleCols = array_values(array_filter(
            array_map(fn($c) => $c->toArray($userRole, $planAccess, $rolePermissions), $this->columns),
            fn($c) => !$c['hidden']
        ));

        $visibleFilters = array_map(fn($f) => $f->toArray(), $this->filters);

        $visibleActions = array_values(array_filter(
            array_map(fn($a) => $a->toArray(), $this->bulkActions),
            fn($a) => empty($a['roles']) || in_array($userRole, $a['roles'] ?? [])
        ));

        return [
            'id' => $this->id,
            'title' => $this->title,
            'columns' => $visibleCols,
            'filters' => $visibleFilters,
            'bulkActions' => $visibleActions,
            'defaultSort' => $this->defaultSort,
            'perPage' => $this->perPage,
            'perPageOptions' => $this->perPageOptions,
            'selectable' => $this->selectable,
            'exportable' => $this->exportable,
            'searchable' => $this->searchable,
            'rowKey' => $this->rowKey,
            'views' => $this->views,
            'showToolbar' => $this->showToolbar,
            'virtualScroll' => $this->virtualScroll,
            'config' => $this->config,
        ];
    }

    /**
     * Get sortable column keys for query building.
     */
    public function getSortableColumns(): array
    {
        return array_map(fn($c) => $c->key, array_filter($this->columns, fn($c) => $c->sortable));
    }

    /**
     * Get searchable column keys.
     */
    public function getSearchableColumns(): array
    {
        return array_map(fn($c) => $c->key, array_filter($this->columns, fn($c) => $c->searchable));
    }

    /**
     * Apply query modifiers (sort, search, filter) to an Eloquent builder.
     */
    public function applyToQuery(Builder $query, array $params = []): Builder
    {
        // Search
        if (!empty($params['search'])) {
            $search = $params['search'];
            $searchCols = $this->getSearchableColumns();
            $query->where(function ($q) use ($search, $searchCols) {
                foreach ($searchCols as $col) {
                    $q->orWhere($col, 'like', "%{$search}%");
                }
            });
        }

        // Sort
        $sort = $params['sort'] ?? $this->defaultSort;
        if (!empty($sort)) {
            foreach ((array) $sort as $key => $dir) {
                if (in_array($key, $this->getSortableColumns())) {
                    $query->orderBy($key, $dir === 'desc' ? 'desc' : 'asc');
                }
            }
        }

        // Filters
        foreach ($this->filters as $filter) {
            $val = $params['filters'][$filter->key] ?? null;
            if ($val === null || $val === '') continue;

            match ($filter->type) {
                'text' => $query->where($filter->key, 'like', "%{$val}%"),
                'select', 'status' => $query->where($filter->key, $val),
                'date_range' => $this->applyDateRange($query, $filter->key, $val),
                'range' => $this->applyRange($query, $filter->key, $val),
                'multi_select' => $query->whereIn($filter->key, (array) $val),
                default => null,
            };
        }

        return $query;
    }

    private function applyDateRange(Builder $q, string $key, mixed $val): void
    {
        if (is_array($val) && !empty($val['start'])) {
            $q->whereDate($key, '>=', $val['start']);
        }
        if (is_array($val) && !empty($val['end'])) {
            $q->whereDate($key, '<=', $val['end']);
        }
    }

    private function applyRange(Builder $q, string $key, mixed $val): void
    {
        if (is_array($val) && isset($val['min'])) {
            $q->where($key, '>=', $val['min']);
        }
        if (is_array($val) && isset($val['max'])) {
            $q->where($key, '<=', $val['max']);
        }
    }
}
