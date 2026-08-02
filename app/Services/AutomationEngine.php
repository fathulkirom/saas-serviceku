<?php

namespace App\Services;

use App\Models\Tenant\AutomationRule;
use App\Models\Tenant\AutomationLog;
use App\Models\Tenant\RequestTimeline;
use Illuminate\Database\Eloquent\Model;

/**
 * AutomationEngine — IF [condition] THEN [action] rule engine.
 * Evaluates rules against entities and executes actions.
 * Supports delays (wait X minutes before executing).
 *
 * All rules are DATA in automation_rules table — no hardcoded if/else.
 */
class AutomationEngine
{
    /**
     * Evaluate all active automation rules for an event + entity.
     * Called after workflow transitions, entity creation, etc.
     */
    public function evaluate(string $event, Model $entity, ?string $workflowKey = null, array $context = []): array
    {
        $results = [];
        $rules = $this->getMatchingRules($event, $entity, $workflowKey);

        foreach ($rules as $rule) {
            try {
                // Check conditions
                if (!$this->checkConditions($rule->conditions, $entity, $context)) {
                    $this->logExecution($rule, $entity, $event, 'skipped', 'Conditions not met');
                    continue;
                }

                // Check Feature Engine — is this module active?
                if ($rule->workflow_key && !$this->isFeatureEnabled($rule->workflow_key)) {
                    $this->logExecution($rule, $entity, $event, 'skipped', 'Feature disabled');
                    continue;
                }

                // Check delay
                $delay = $rule->delay_minutes ?? 0;
                $scheduledAt = $delay > 0 ? now()->addMinutes($delay) : now();

                if ($delay > 0) {
                    // Schedule for later execution
                    $this->logExecution($rule, $entity, $event, 'scheduled', "Delayed {$delay} minutes", $scheduledAt);
                    // Dispatch delayed job
                    \App\Jobs\ExecuteAutomationRule::dispatch($rule->id, $entity, $event, $context)
                        ->delay(now()->addMinutes($delay));
                } else {
                    // Execute immediately
                    $result = $this->executeAction($rule, $entity, $context);
                    $this->logExecution($rule, $entity, $event, $result['status'], $result['message'], now());
                }

                $results[] = ['rule_id' => $rule->id, 'rule_name' => $rule->name, 'status' => 'executed'];
            } catch (\Throwable $e) {
                $this->logExecution($rule, $entity, $event, 'failed', $e->getMessage());
                \Illuminate\Support\Facades\Log::error("Automation rule failed", [
                    'rule_id' => $rule->id, 'rule_name' => $rule->name,
                    'error' => $e->getMessage(), 'entity' => get_class($entity), 'entity_id' => $entity->getKey(),
                ]);
                $results[] = ['rule_id' => $rule->id, 'rule_name' => $rule->name, 'status' => 'failed', 'error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Execute a specific rule's action.
     */
    public function executeAction(AutomationRule $rule, Model $entity, array $context = []): array
    {
        $config = $rule->action_config ?? [];
        $adapter = app(ProviderAdapter::class);
        $actionType = $rule->action_type;

        return match ($actionType) {
            // Messaging — via ProviderAdapter
            'send_whatsapp'    => $adapter->send('whatsapp',
                $this->resolveRecipient($entity, $config['recipient'] ?? 'customer'),
                $this->interpolateMessage($config['message'] ?? '', $entity),
                $config,
            ),
            'send_email'       => $adapter->send('email',
                $this->resolveRecipient($entity, $config['recipient'] ?? 'customer'),
                $this->interpolateMessage($config['body'] ?? '', $entity),
                ['subject' => $this->interpolateMessage($config['subject'] ?? 'Notification', $entity)],
            ),

            // Storage — via ProviderAdapter
            'upload_gdrive'    => $adapter->upload('gdrive',
                data_get($entity, $config['file_field'] ?? 'photo_path') ?? '',
                $config['folder'] ?? 'services',
                $config,
            ),

            // Document — via ProviderAdapter
            'generate_pdf'     => $adapter->generateDocument('pdf', $entity, $config),

            // Core actions (no provider needed)
            'create_timeline'  => $this->actionCreateTimeline($entity, $config),
            'generate_review'  => $this->actionGenerateReview($entity, $config, $adapter),
            'assign_user'      => $this->actionAssignUser($entity, $config),
            'create_work_order'=> $this->actionCreateWorkOrder($entity, $config),
            'create_audit'     => $this->actionCreateAudit($entity, $config),
            'browser_notify'   => $adapter->send('browser_push', '', $this->interpolateMessage($config['message'] ?? '', $entity), [
                'entity_id' => $entity->getKey(),
                'tracking_code' => $entity->tracking_code ?? $entity->request_number ?? '',
                'status' => $entity->status ?? '',
            ]),
            'generate_reminder'=> $this->actionGenerateReminder($entity, $config),

            default => ['status' => 'skipped', 'message' => "Unknown action: {$actionType}"],
        };
    }

    // ======== CONDITION EVALUATION (via ConditionBuilder V2) ========

    private function checkConditions(?array $conditions, Model $entity, array $context): bool
    {
        if (empty($conditions)) return true;

        // Support both flat array (legacy) and new group format
        // Legacy: [{"field": "status", "operator": "=", "value": "selesai"}]
        // New:    {"type": "group", "operator": "AND", "conditions": [...]}
        if (isset($conditions['type'])) {
            // New format — use ConditionBuilder directly
            return app(ConditionBuilder::class)->evaluate($conditions, $entity, $context);
        }

        // Legacy flat array — wrap in AND group
        $wrapper = ['type' => 'group', 'operator' => 'AND', 'conditions' => $conditions];
        return app(ConditionBuilder::class)->evaluate($wrapper, $entity, $context);
    }

    // ======== ACTION EXECUTORS (non-provider) ========

    private function actionCreateTimeline(Model $entity, array $config): array
    {
        $requestId = null;
        if ($entity instanceof \App\Models\Tenant\Request) {
            $requestId = $entity->id;
        } elseif (method_exists($entity, 'request') && $entity->request) {
            $requestId = $entity->request->id;
        }

        if ($requestId) {
            RequestTimeline::record(
                $requestId,
                $config['event'] ?? 'automation',
                $this->interpolateMessage($config['label'] ?? 'Automation Event', $entity),
                $this->interpolateMessage($config['description'] ?? '', $entity),
                ['automation_rule' => $config['rule_name'] ?? null],
            );
            return ['status' => 'success', 'message' => 'Timeline recorded'];
        }
        return ['status' => 'skipped', 'message' => 'No request linked'];
    }

    private function actionGeneratePdf(Model $entity, array $config): array
    {
        // Store intent — PDF generation happens in queue
        \App\Jobs\GeneratePdf::dispatch($entity, $config);
        return ['status' => 'success', 'message' => 'PDF generation queued'];
    }

    private function actionAssignUser(Model $entity, array $config): array
    {
        $roleKey = $config['role'] ?? 'technician';
        $userId = $config['user_id'] ?? null;

        if (!$userId) {
            // Find first available user with that role
            $user = \App\Models\Tenant\User::whereHas('roles', fn($q) => $q->where('key', $roleKey))->first();
            $userId = $user?->id;
        }

        if ($userId && method_exists($entity, 'technician_id') || $entity->isFillable('technician_id')) {
            $entity->technician_id = $userId;
            $entity->save();
            return ['status' => 'success', 'message' => "Assigned user #{$userId}"];
        }
        return ['status' => 'skipped', 'message' => 'No assignable field'];
    }

    private function actionCreateWorkOrder(Model $entity, array $config): array
    {
        if ($entity instanceof \App\Models\Tenant\Request) {
            $action = app(\App\Actions\Request\CreateWorkOrderAction::class);
            $action->execute($entity, [
                'title' => $this->interpolateMessage($config['title'] ?? 'Auto Work Order', $entity),
                'description' => $config['description'] ?? null,
                'category' => $config['category'] ?? 'hardware',
                'device_id' => $config['device_id'] ?? null,
            ]);
            return ['status' => 'success', 'message' => 'Work order created'];
        }
        return ['status' => 'skipped', 'message' => 'Entity is not a Request'];
    }

    private function actionCreateAudit(Model $entity, array $config): array
    {
        \App\Models\Tenant\ActivityLog::log(
            $config['event'] ?? 'automation_audit',
            $this->interpolateMessage($config['description'] ?? 'Automation audit', $entity),
            $entity,
            $config,
        );
        return ['status' => 'success', 'message' => 'Audit log created'];
    }

    private function actionGenerateReview(Model $entity, array $config, ?ProviderAdapter $adapter = null): array
    {
        $customer = method_exists($entity, 'customer') ? $entity->customer : null;
        if ($customer && !empty($customer->phone)) {
            $reviewLink = $config['review_url'] ?? 'https://g.page/r/REPLACE_WITH_YOUR_PLACE_ID/review';
            $adapter = $adapter ?? app(ProviderAdapter::class);
            return $adapter->send('whatsapp', $customer->phone, "Terima kasih! Beri kami review: {$reviewLink}");
        }
        return ['status' => 'skipped', 'message' => 'No customer phone'];
    }

    private function actionGenerateReminder(Model $entity, array $config): array
    {
        // Schedule a follow-up reminder
        $days = $config['days'] ?? 3;
        \App\Models\Tenant\AutomationLog::create([
            'automation_rule_id' => $config['rule_id'] ?? null,
            'entity_type' => get_class($entity),
            'entity_id' => $entity->getKey(),
            'event' => 'reminder',
            'status' => 'scheduled',
            'message' => "Reminder scheduled for +{$days} days",
            'scheduled_at' => now()->addDays($days),
            'context' => json_encode($config),
        ]);
        return ['status' => 'success', 'message' => 'Reminder scheduled'];
    }

    // ======== HELPERS ========

    private function getMatchingRules(string $event, Model $entity, ?string $workflowKey): array
    {
        $query = AutomationRule::where('is_active', true)
            ->where(function ($q) use ($event) {
                $q->where('event', $event)->orWhere('event', '*');
            });

        if ($workflowKey) {
            $query->where(function ($q) use ($workflowKey) {
                $q->where('workflow_key', $workflowKey)->orWhereNull('workflow_key');
            });
        }

        return $query->orderBy('priority', 'desc')->get()->all();
    }

    private function resolveRecipient(Model $entity, string $recipientType): string
    {
        return match ($recipientType) {
            'customer' => data_get($entity, 'customer.phone') ?? data_get($entity, 'customer.whatsapp') ?? '',
            'technician' => data_get($entity, 'technician.phone') ?? '',
            'owner' => \App\Models\Tenant\TenantSetting::getValue('whatsapp_number', ''),
            'branch' => data_get($entity, 'branch.phone') ?? '',
            default => $recipientType, // direct number
        };
    }

    private function interpolateMessage(string $template, Model $entity): string
    {
        $replacements = [
            '{id}' => $entity->getKey(),
            '{status}' => $entity->status ?? '',
            '{customer_name}' => data_get($entity, 'customer.name') ?? data_get($entity, 'customer_name') ?? '',
            '{technician_name}' => data_get($entity, 'technician.name') ?? '',
            '{tracking_code}' => $entity->tracking_code ?? $entity->request_number ?? '',
            '{date}' => now()->format('d/m/Y'),
            '{time}' => now()->format('H:i'),
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    private function logExecution(AutomationRule $rule, Model $entity, string $event, string $status, string $message, $executedAt = null): void
    {
        AutomationLog::create([
            'automation_rule_id' => $rule->id,
            'entity_type' => get_class($entity),
            'entity_id' => $entity->getKey(),
            'event' => $event,
            'status' => $status,
            'message' => $message,
            'context' => json_encode(['rule_name' => $rule->name, 'event' => $event]),
            'scheduled_at' => $status === 'scheduled' ? $executedAt : null,
            'executed_at' => $status === 'success' ? ($executedAt ?? now()) : null,
        ]);
    }

    private function isFeatureEnabled(string $workflowKey): bool
    {
        try {
            return app(FeatureEngine::class)->can($workflowKey);
        } catch (\Throwable) {
            return true; // Default: enabled if FeatureEngine unavailable
        }
    }
}
