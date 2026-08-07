<?php

namespace App\Enterprise\Reporting;

class ChartEngine
{
    public function formatForChart(ReportDefinition $report, array $data, array $dimensionValues): array
    {
        return match ($report->chartType) {
            'pie', 'donut' => $this->formatPieData($report, $data),
            'bar', 'line', 'area' => $this->formatSeriesData($report, $data, $dimensionValues),
            'kpi' => $this->formatKpiData($report, $data),
            default => $this->formatSeriesData($report, $data, $dimensionValues),
        };
    }

    private function formatPieData(ReportDefinition $report, array $data): array
    {
        $metric = $report->metrics[0] ?? null;
        $dimension = $report->dimensions[0] ?? null;

        if (! $metric) {
            return [];
        }

        return array_map(fn ($row) => [
            'label' => $dimension ? ($row[$dimension->field] ?? 'Unknown') : 'Value',
            'value' => (float) ($row[$metric->id] ?? $row[$metric->field] ?? 0),
        ], $data);
    }

    private function formatSeriesData(ReportDefinition $report, array $data, array $dimensions): array
    {
        $labels = [];
        $series = [];

        foreach ($report->metrics as $metric) {
            $series[$metric->id] = ['label' => $metric->label, 'data' => []];
        }

        foreach ($data as $row) {
            $label = [];
            foreach ($report->dimensions as $dimension) {
                $label[] = $row[$dimension->field] ?? '';
            }
            $labels[] = implode(' ', $label);

            foreach ($report->metrics as $metric) {
                $series[$metric->id]['data'][] = (float) ($row[$metric->id] ?? $row[$metric->field] ?? 0);
            }
        }

        return ['labels' => $labels, 'series' => array_values($series)];
    }

    private function formatKpiData(ReportDefinition $report, array $data): array
    {
        return array_map(fn ($metric) => [
            'id' => $metric->id,
            'label' => $metric->label,
            'value' => $data[0][$metric->id] ?? $data[0][$metric->field] ?? 0,
            'format' => $metric->format,
            'color' => $metric->color,
            'icon' => $metric->icon,
        ], $report->metrics);
    }
}
