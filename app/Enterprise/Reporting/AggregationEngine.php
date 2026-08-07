<?php

namespace App\Enterprise\Reporting;

use Illuminate\Support\Collection;

class AggregationEngine
{
    public function compute(MetricDefinition $metric, Collection $rows): float|int|null
    {
        $values = $rows->pluck($metric->field)->filter(fn ($value) => is_numeric($value));

        return match ($metric->aggregation) {
            'sum' => $values->sum(),
            'count' => $rows->count(),
            'avg' => $values->avg(),
            'min' => $values->min(),
            'max' => $values->max(),
            'median' => $this->median($values),
            'percent' => $values->count() > 0 ? ($values->sum() / $values->count()) * 100 : 0,
            default => $values->sum(),
        };
    }

    private function median(Collection $values): ?float
    {
        $sorted = $values->sort()->values();
        $count = $sorted->count();

        if ($count === 0) {
            return null;
        }

        $mid = (int) floor($count / 2);

        return $count % 2 ? $sorted[$mid] : ($sorted[$mid - 1] + $sorted[$mid]) / 2;
    }
}
