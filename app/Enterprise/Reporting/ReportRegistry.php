<?php

namespace App\Enterprise\Reporting;

class ReportRegistry
{
    /** @var ReportDefinition[] */
    protected array $reports = [];

    public function register(ReportDefinition $report): self
    {
        $this->reports[$report->id] = $report;

        return $this;
    }

    public function registerAll(array $reports): self
    {
        foreach ($reports as $report) {
            $this->register($report);
        }

        return $this;
    }

    public function get(string $id): ?ReportDefinition
    {
        return $this->reports[$id] ?? null;
    }

    /** @return ReportDefinition[] */
    public function all(): array
    {
        return $this->reports;
    }

    /** @return ReportDefinition[] */
    public function accessible(string $userRole, array $planAccess, array $rolePermissions): array
    {
        return array_filter($this->reports, fn ($report) => $report->isAccessible($userRole, $planAccess, $rolePermissions));
    }
}
