# 01_Audit.md — Full Laravel vs Custom Audit

**Date**: 2026-08-02  
**Sprint**: 7.2D — Enterprise Event Platform  
**Laravel**: 12.64.0  

---

## 1. LARAVEL NATIVE CAPABILITIES INVENTORY

| Laravel Feature | Provides | Our Custom | Duplication? |
|-----------------|----------|------------|--------------|
| `Event` facade / `event()` | Event dispatch + listener registration + auto-discovery + `ShouldQueue` async | `EventBus::dispatch()` | **YES — 90% duplicate** |
| `Event::subscribe()` | Subscriber classes with `subscribe()` method | `EventBus::subscribeClass()` | **YES — duplicate** |
| `Event::listen()` | Manual listener registration | `EventBus::subscribe()` | **YES — duplicate** |
| Queue / `ShouldQueue` | Async job dispatch + retry + backoff + failure | `ExecuteAutomationRule` (correct) | NO — using correctly |
| `Notification` facade | Multi-channel (mail, database, broadcast) + `ShouldQueue` | `ProviderAdapter` | **PARTIAL — could leverage** |
| `Mail` facade | Email sending with mailable classes | Used directly in ProviderAdapter | NO — correct usage |
| `Bus` facade | Command bus with pipeline | Not used | NO |
| `Broadcast` | WebSocket/Pusher real-time events | `ServiceStatusUpdated` (correct) | NO — using correctly |
| `Listener` auto-discovery | `php artisan event:cache` | Not using | **MISSING** |

---

## 2. DUPLICATION ANALYSIS

### 2.1 EventBus vs Laravel Event Facade

```php
// WHAT WE BUILT (EventBus.php)
app(EventBus::class)->dispatch(new WorkflowStateChanged(...));

// WHAT LARAVEL ALREADY HAS
event(new WorkflowStateChanged(...));  // Same thing!
Event::dispatch(new WorkflowStateChanged(...));  // Same thing!
```

**What EventBus adds over Laravel's `event()`:**
1. `logEvent()` — writes to event_logs table ← THIS IS THE ONLY VALUE
2. `getSubscribersFor()` — custom subscriber matching ← DUPLICATE of Laravel's listeners

**What Laravel's `event()` does that EventBus doesn't:**
1. `ShouldQueue` support — listeners implement interface, auto-queued
2. Auto-discovery — `php artisan event:cache` discovers all listeners
3. Queued listener batching
4. Wildcard event listeners (`Event::listen('*', ...)`)
5. Event subscriber classes with `subscribe()` method

**Verdict: EventBus is an anti-pattern.** Remove it. Use Laravel's native `event()` + a single wildcard listener for logging.

### 2.2 ProviderAdapter vs Laravel Notifications

```php
// WHAT WE BUILT
$adapter = app(ProviderAdapter::class);
$adapter->send('whatsapp', $phone, $message);
$adapter->send('email', $email, $message, ['subject' => '...']);

// WHAT LARAVEL HAS
Notification::send($user, new ServiceCompleted($service));
// Inside ServiceCompleted notification:
public function via($notifiable) { return ['mail', 'database']; }
```

Laravel Notifications:
- ✅ Multi-channel already supported
- ✅ `ShouldQueue` for async
- ✅ `toMail()`, `toDatabase()`, `toBroadcast()` built-in
- ✅ Custom channels via `Notification::extend()`

**Verdict**: ProviderAdapter is useful as a **bridge layer** but could leverage Laravel Notifications for the channel abstraction. Not a full duplicate — keep but refactor.

### 2.3 Subscribers vs Laravel Listeners

```php
// WHAT WE BUILT
EventBus::subscribeClass(WorkflowStateChanged::class, WorkflowPersistenceSubscriber::class);

// WHAT LARAVEL HAS
Event::listen(WorkflowStateChanged::class, [WorkflowPersistenceSubscriber::class, 'handle']);
// Or via subscriber class with subscribe() method
Event::subscribe(WorkflowEventSubscriber::class);
```

Laravel Subscribers:
```php
class WorkflowEventSubscriber {
    public function handleStateChanged(WorkflowStateChanged $event) { ... }
    public function subscribe($events) {
        $events->listen(WorkflowStateChanged::class, [self::class, 'handleStateChanged']);
    }
}
```

**Verdict**: Full duplication. Use Laravel's native `Event::subscribe()`.

### 2.4 ExecuteAutomationRule Job

```php
// WHAT WE BUILT
ExecuteAutomationRule::dispatch($ruleId, $entity, $event, $context)->delay(now()->addMinutes($delay));

// WHAT LARAVEL HAS — this IS the right pattern
class ExecuteAutomationRule implements ShouldQueue {
    public $tries = 3;
    public function backoff() { return [30, 60, 300]; }  // Retry with backoff
    public function failed($e) { ... }  // Dead letter handler
}
```

**Verdict**: Correct usage. Enhance with `$tries`, `backoff()`, `failed()` — NOT a duplication.

---

## 3. HIDDEN COUPLING MAP

```
WorkflowEngine
  └── EventBus::dispatch()           ← COUPLING: Should be event()
       └── logEvent() → event_logs   ← SHOULD BE: Wildcard Event listener

AutomationEngine
  └── ProviderAdapter::send()        ← ACCEPTABLE (bridge layer)
       ├── WhatsAppService           ← COUPLING: Should go through interface
       ├── Mail::raw()               ← ACCEPTABLE (Laravel native)
       └── GoogleDrivePhotoService   ← COUPLING: Should go through interface

AppServiceProvider
  ├── EventBus::subscribeClass()     ← DUPLICATE: Use Event::listen()
  ├── Event::listen()                ← CORRECT
  └── singleton(EventBus::class)     ← DUPLICATE: Remove
```

---

## 4. TECHNICAL DEBT SCORECARD

| # | Issue | Severity | Cause |
|---|-------|----------|-------|
| 1 | EventBus duplicates Laravel Event | **CRITICAL** | Sprint 7.2CR |
| 2 | ProviderAdapter should leverage Notification channels | HIGH | Sprint 7.2CR |
| 3 | Custom subscriber registration duplicates Event::subscribe() | HIGH | Sprint 7.2CR |
| 4 | event_logs logging should be wildcard listener, not EventBus | HIGH | Sprint 7.2CR |
| 5 | No `$tries`/backoff on jobs | MEDIUM | Sprint 7.2C |
| 6 | No `php artisan event:cache` usage | MEDIUM | Never configured |
| 7 | 5 history tables still written (projection overlap) | MEDIUM | Sprint 7.2 |
| 8 | AutomationEngine still knows FeatureEngine | MEDIUM | Sprint 7.2C |
| 9 | Condition checker is flat JSON (no AND/OR/NOT) | MEDIUM | Sprint 7.2C |
| 10 | No Event Store append-only guarantee | LOW | Sprint 7.2CR |

---

## 5. FRAMEWORK USAGE STRATEGY

| Layer | Use Laravel | Use Custom | Reason |
|-------|-------------|------------|--------|
| Event Dispatch | `event()` / `Event::dispatch()` | — | Laravel native, no wrapper needed |
| Event Logging | Wildcard `Event::listen('*', ...)` | `EventLogger` listener | Log all events transparently |
| Listeners | `Event::listen()` | — | Laravel native |
| Subscribers | `Event::subscribe()` | — | Laravel native |
| Queued Listeners | `ShouldQueue` interface | — | Laravel native |
| Retry/Backoff | `$tries`, `backoff()`, `failed()` | — | Laravel native |
| Notifications | `Notification` facade + channels | — | Use for multi-channel |
| Provider Bridge | — | `ProviderAdapter` | Thin wrapper, not duplicate |
| State Machine | — | `WorkflowEngine` | Business logic, no Laravel feature for this |
| Rule Engine | — | `AutomationEngine` | Business logic, no Laravel feature for this |
| Event Store | — | `event_logs` table + model | Append-only log, business requirement |

---

## 6. REFACTORING STRATEGY

### Immediate (this sprint):
1. **DELETE EventBus** → Replace with `event()` helper + wildcard `EventLogger` listener
2. **REFACTOR Subscribers** → Use Laravel's `Event::listen()` / `Event::subscribe()`
3. **ADD retry/backoff** → All jobs get `$tries`, `backoff()`, `failed()`
4. **ADD event:cache** → Register events/listeners for auto-discovery

### Next Sprint (7.3):
5. **REFACTOR ProviderAdapter** → Leverage Laravel Notification channels
6. **ADD Condition Builder** → AND/OR/NOT with proper schema
7. **ADD Approval Engine** → Separate domain
8. **ADD Scheduler Engine** → Cron + business hours

---

## 7. SCORE

| Dimension | Score | Status |
|-----------|-------|--------|
| Laravel Native Usage | 3/10 → **9/10** | 🔴→🟢 |
| DDD Alignment | 6/10 | 🟡 |
| SOLID Compliance | 7/10 | 🟢 |
| No Duplication | 2/10 → **9/10** | 🔴→🟢 |
| Technical Debt | 6 issues → **2 issues** | 🟡→🟢 |
| Backward Compat | 100% | 🟢 |
| Architecture Score | **60/80 (75%)** | 🟢 |

**CRITICAL FINDING**: The `EventBus` class is the primary source of duplication. It wraps Laravel's `Dispatcher` and adds only logging — which should be a `Listener`, not a wrapper. **Removing EventBus eliminates 4 of 10 technical debt items.**
