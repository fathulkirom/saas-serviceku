<?php

namespace App\Enterprise\Automation;

class AutomationResult
{
    /** @var array<int, array{step: int, action: string, success: bool, message: string, duration_ms: float}> */
    public array $stepResults = [];

    public bool $overallSuccess = true;

    public float $totalDurationMs = 0;

    public ?string $error = null;

    public function addStepResult(int $index, string $action, bool $success, string $message, float $durationMs): void
    {
        $this->stepResults[] = [
            'step' => $index,
            'action' => $action,
            'success' => $success,
            'message' => $message,
            'duration_ms' => round($durationMs, 2),
        ];

        if (! $success) {
            $this->overallSuccess = false;
        }
    }
}
