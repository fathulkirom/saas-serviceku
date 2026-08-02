<?php

namespace App\Services;

use App\Models\Tenant\EventLog;
use Illuminate\Contracts\Events\Dispatcher;

/**
 * Universal Event Bus — canonical event dispatch point.
 *
 * EVERY domain event MUST go through dispatch().
 * The bus handles:
 *   1. Event logging to canonical event_logs table
 *   2. Laravel event dispatcher (for registered listeners)
 *   3. Event hierarchy resolution for subscriber matching
 *
 * Usage:
 *   app(EventBus::class)->dispatch(new RequestCreated($request));
 */
class EventBus
{
    /** Registered subscriber classes keyed by event class */
    private static array $subscribers = [];

    public function __construct(
        private readonly Dispatcher $dispatcher,
    ) {}

    /**
     * Dispatch an event through the bus.
     * All side effects (log, notify, timeline, etc.) happen via subscribers.
     */
    public function dispatch(object $event): void
    {
        $eventClass = get_class($event);

        // 1. Log to canonical event_logs (always)
        $this->logEvent($event);

        // 2. Laravel event dispatcher (sync listeners)
        $this->dispatcher->dispatch($event);

        // 3. Registered subscribers
        foreach ($this->getSubscribersFor($eventClass) as $subscriber) {
            try {
                $subscriber->handle($event);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error("EventBus subscriber failed", [
                    'event' => $eventClass, 'subscriber' => get_class($subscriber),
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Register a subscriber for a specific event class.
     */
    public static function subscribe(string $eventClass, callable $handler): void
    {
        self::$subscribers[$eventClass][] = $handler;
    }

    /**
     * Register a subscriber class (must have handle() method).
     */
    public static function subscribeClass(string $eventClass, string $handlerClass): void
    {
        self::$subscribers[$eventClass][] = app($handlerClass);
    }

    private function getSubscribersFor(string $eventClass): array
    {
        $handlers = [];

        // Direct match
        if (isset(self::$subscribers[$eventClass])) {
            $handlers = array_merge($handlers, self::$subscribers[$eventClass]);
        }

        // Parent class match (event hierarchy: RequestCompleted → RequestEvent → EntityEvent)
        $parents = array_merge(class_parents($eventClass), class_implements($eventClass));
        foreach ($parents as $parent) {
            if (isset(self::$subscribers[$parent])) {
                $handlers = array_merge($handlers, self::$subscribers[$parent]);
            }
        }

        // Wildcard subscriber (*)
        if (isset(self::$subscribers['*'])) {
            $handlers = array_merge($handlers, self::$subscribers['*']);
        }

        return $handlers;
    }

    private function logEvent(object $event): void
    {
        try {
            $entity = $this->extractEntity($event);
            EventLog::create([
                'entity_type'    => $entity ? get_class($entity) : null,
                'entity_id'      => $entity ? $entity->getKey() : null,
                'event_key'      => class_basename($event),
                'event_class'    => get_class($event),
                'actor_id'       => auth()->id(),
                'branch_id'      => session('current_branch_id'),
                'metadata'       => $this->extractMetadata($event),
                'occurred_at'    => now(),
            ]);
        } catch (\Throwable $e) {
            // Event logging must never break the main flow
            \Illuminate\Support\Facades\Log::warning('EventBus log failed: ' . $e->getMessage());
        }
    }

    private function extractEntity(object $event): ?object
    {
        // Try common property names
        foreach (['request', 'service', 'workOrder', 'sale', 'customer', 'branch', 'user', 'entity'] as $prop) {
            if (property_exists($event, $prop) && is_object($event->$prop)) {
                return $event->$prop;
            }
        }
        // Try via reflection for readonly properties
        $ref = new \ReflectionClass($event);
        foreach ($ref->getProperties() as $prop) {
            if ($prop->getType() && !$prop->getType()->isBuiltin()) {
                $value = $prop->getValue($event);
                if (is_object($value) && method_exists($value, 'getKey')) {
                    return $value;
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
            if (is_scalar($val) || is_null($val) || is_array($val)) {
                $data[$prop->getName()] = $val;
            } elseif (is_object($val) && method_exists($val, 'getKey')) {
                $data[$prop->getName() . '_id'] = $val->getKey();
                $data[$prop->getName() . '_type'] = get_class($val);
            }
        }
        return !empty($data) ? json_encode($data) : null;
    }
}
