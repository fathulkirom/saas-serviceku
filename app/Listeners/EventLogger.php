<?php

namespace App\Listeners;

use App\Models\Tenant\EventLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Wildcard event logger — logs EVERY dispatched event to event_logs.
 * Registered as: Event::listen('*', [EventLogger::class, 'handle'])
 *
 * This replaces the EventBus::logEvent() method.
 * Laravel's wildcard listener pattern is the correct approach.
 */
class EventLogger
{
    private static bool $recording = false;

    private const SKIP_PATTERNS = [
        'eloquent.*',
        'Illuminate\\*',
        'bootstrapped:*',
        'bootstrapping:*',
    ];

    public function handle(string $eventName, array $payload): void
    {
        if (self::$recording || $this->shouldSkip($eventName) || empty($payload)) {
            return;
        }

        $event = $payload[0] ?? null;
        if (! $event || ! is_object($event) || $event instanceof EventLog) {
            return;
        }

        self::$recording = true;

        try {
            $entity = $this->extractEntity($event);

            EventLog::create([
                'entity_type' => $entity ? get_class($entity) : null,
                'entity_id' => $entity ? $entity->getKey() : null,
                'event_key' => class_basename($event),
                'event_class' => get_class($event),
                'actor_id' => auth()->id(),
                'branch_id' => session('current_branch_id'),
                'tenant_id' => tenant()?->id,
                'correlation_id' => request()->header('X-Correlation-ID') ?? (string) Str::uuid(),
                'source' => 'system',
                'metadata' => $this->extractMetadata($event),
                'occurred_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::debug('EventLogger failed: '.$e->getMessage());
        } finally {
            self::$recording = false;
        }
    }

    private function shouldSkip(string $eventName): bool
    {
        foreach (self::SKIP_PATTERNS as $pattern) {
            if (Str::is($pattern, $eventName)) {
                return true;
            }
        }

        return false;
    }

    private function extractEntity(object $event): ?object
    {
        $ref = new \ReflectionClass($event);
        foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            if ($prop->getType() && ! $prop->getType()->isBuiltin()) {
                $val = $prop->getValue($event);
                if (is_object($val) && method_exists($val, 'getKey')) {
                    return $val;
                }
            }
        }

        return null;
    }

    private function extractMetadata(object $event): ?string
    {
        $data = [];
        $ref = new \ReflectionClass($event);
        foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            $val = $prop->getValue($event);
            if (is_scalar($val) || is_null($val)) {
                $data[$prop->getName()] = $val;
            } elseif (is_object($val) && method_exists($val, 'getKey')) {
                $data[$prop->getName().'_id'] = $val->getKey();
            }
        }

        return ! empty($data) ? json_encode($data) : null;
    }
}
