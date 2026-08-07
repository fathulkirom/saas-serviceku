<?php

namespace App\Enterprise\Automation;

use Illuminate\Support\Facades\Log;

class AutomationDispatcher
{
    public function dispatch(AutomationDefinition $automation, AutomationContext $context): AutomationResult
    {
        $result = new AutomationResult;
        $startTime = microtime(true);

        foreach ($automation->steps as $index => $step) {
            $stepStart = microtime(true);

            try {
                $this->executeStep($step, $context);
                $result->addStepResult($index, $step->action->value, true, 'OK', (microtime(true) - $stepStart) * 1000);
            } catch (\Throwable $e) {
                Log::error("[Automation] Step {$index} failed: {$e->getMessage()}", [
                    'automation' => $automation->id,
                    'action' => $step->action->value,
                ]);
                $result->addStepResult($index, $step->action->value, false, $e->getMessage(), (microtime(true) - $stepStart) * 1000);

                if (! $step->continueOnError) {
                    $result->error = "Step {$index} failed: {$e->getMessage()}";
                    break;
                }
            }
        }

        $result->totalDurationMs = round((microtime(true) - $startTime) * 1000, 2);

        return $result;
    }

    private function executeStep(AutomationStep $step, AutomationContext $context): void
    {
        match ($step->action) {
            ActionType::ADD_TIMELINE => $this->addTimeline($context, $step->config),
            ActionType::CREATE_ACTIVITY => $this->createActivity($context, $step->config),
            ActionType::CHANGE_STATUS => $this->changeStatus($context, $step->config),
            ActionType::PUSH_NOTIFICATION => $this->pushNotification($context, $step->config),
            ActionType::SEND_WHATSAPP => $this->sendWhatsApp($context, $step->config),
            ActionType::SEND_EMAIL => $this->sendEmail($context, $step->config),
            ActionType::CREATE_TASK => $this->createTask($context, $step->config),
            default => Log::info("[Automation] Action '{$step->action->value}' not yet implemented."),
        };
    }

    private function addTimeline(AutomationContext $context, array $config): void
    {
        if (! $context->subject || ! method_exists($context->subject, 'worklogs')) {
            return;
        }

        $context->subject->worklogs()->create([
            'user_id' => $context->user?->id,
            'action' => 'automation',
            'description' => $config['message'] ?? 'Automation: event triggered',
            'metadata' => ['automation' => true, 'event' => $context->triggerEvent],
        ]);
    }

    private function createActivity(AutomationContext $context, array $config): void
    {
        Log::info("[Automation] Activity: {$context->triggerEvent}", [
            'message' => $config['message'] ?? 'Automation triggered',
            'subject_id' => $context->subject?->id,
        ]);
    }

    private function changeStatus(AutomationContext $context, array $config): void
    {
        if (! $context->subject || ! isset($config['new_status'])) {
            return;
        }

        if (method_exists($context->subject, 'canTransitionTo') && ! $context->subject->canTransitionTo($config['new_status'])) {
            throw new \RuntimeException("Cannot transition to {$config['new_status']}");
        }

        $context->subject->status = $config['new_status'];
        $context->subject->save();
    }

    private function pushNotification(AutomationContext $context, array $config): void
    {
        Log::info("[Automation] Notification: {$context->triggerEvent}", [
            'title' => $config['title'] ?? 'Automation',
            'body' => $config['body'] ?? '',
            'user_id' => $config['user_id'] ?? null,
        ]);
    }

    private function sendWhatsApp(AutomationContext $context, array $config): void
    {
        Log::info("[Automation] WhatsApp: {$context->triggerEvent}", [
            'to' => $config['to'] ?? 'N/A',
            'message' => $config['message'] ?? '',
        ]);
    }

    private function sendEmail(AutomationContext $context, array $config): void
    {
        Log::info("[Automation] Email: {$context->triggerEvent}", [
            'to' => $config['to'] ?? 'N/A',
            'subject' => $config['subject'] ?? 'Automation Notification',
        ]);
    }

    private function createTask(AutomationContext $context, array $config): void
    {
        Log::info("[Automation] Task created: {$context->triggerEvent}", [
            'title' => $config['title'] ?? 'Follow-up',
            'assigned_to' => $config['assignee_id'] ?? null,
        ]);
    }
}
