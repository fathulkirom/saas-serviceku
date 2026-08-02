<?php

namespace App\Services;

use App\Models\Tenant\SlaConfig;

/**
 * SlaEngine — Service Level Agreement resolver.
 * Determines SLA targets based on workflow + priority.
 * Supports escalation notification logic.
 */
class SlaEngine
{
    /**
     * Get SLA config for a workflow + priority.
     */
    public function getConfig(string $workflowKey, string $priority = 'normal'): ?SlaConfig
    {
        return SlaConfig::where('workflow_key', $workflowKey)
            ->where('priority', $priority)
            ->where('is_active', true)
            ->first()
            ?? SlaConfig::where('workflow_key', $workflowKey)
                ->where('priority', 'normal')
                ->where('is_active', true)
                ->first();
    }

    /**
     * Check if an entity has breached SLA.
     * Returns array of breached targets and escalation levels.
     */
    public function checkBreach($entity, string $workflowKey, string $priority = 'normal'): array
    {
        $sla = $this->getConfig($workflowKey, $priority);
        if (!$sla) return ['breached' => false];

        $breaches = [];
        $createdAt = $entity->created_at ?? now();
        $elapsedMinutes = $createdAt->diffInMinutes(now());

        if ($entity->status === 'checking' && $sla->target_checking_minutes) {
            if ($elapsedMinutes > $sla->target_checking_minutes) {
                $breaches[] = ['target' => 'checking', 'limit' => $sla->target_checking_minutes, 'elapsed' => $elapsedMinutes];
            }
        }

        $dikerjakanAt = $entity->dikerjakan_at ?? $createdAt;
        $repairElapsed = $dikerjakanAt->diffInMinutes(now());

        if (in_array($entity->status, ['dikerjakan', 'repair']) && $sla->target_repair_minutes) {
            if ($repairElapsed > $sla->target_repair_minutes) {
                $breaches[] = ['target' => 'repair', 'limit' => $sla->target_repair_minutes, 'elapsed' => $repairElapsed];
            }
        }

        return [
            'breached' => !empty($breaches),
            'breaches' => $breaches,
            'escalation' => $this->getEscalationLevel($repairElapsed, $sla),
            'total_elapsed_minutes' => $elapsedMinutes,
        ];
    }

    /**
     * Determine escalation level based on elapsed time.
     */
    private function getEscalationLevel(int $elapsedMinutes, SlaConfig $sla): array
    {
        $level = 'none';
        $notify = null;

        if ($sla->escalation_level2_minutes && $elapsedMinutes > $sla->escalation_level2_minutes) {
            $level = 'level2';
            $notify = $sla->escalation_level2_role;
        } elseif ($sla->escalation_level1_minutes && $elapsedMinutes > $sla->escalation_level1_minutes) {
            $level = 'level1';
            $notify = $sla->escalation_level1_role;
        }

        return ['level' => $level, 'notify_role' => $notify, 'elapsed_minutes' => $elapsedMinutes];
    }

    /**
     * Get all SLA configs for a workflow.
     */
    public function getAllForWorkflow(string $workflowKey): array
    {
        return SlaConfig::where('workflow_key', $workflowKey)
            ->where('is_active', true)
            ->orderByRaw("FIELD(priority, 'normal', 'priority', 'express', 'vip', 'corporate')")
            ->get()
            ->toArray();
    }
}
