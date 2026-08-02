# 01_Audit.md — Full Architecture Audit

**Date**: 2026-08-02  
**Sprint**: 7.2CR — Workflow Refinement & Universal Event Architecture  
**Auditor**: Principal Enterprise Architect  

---

## 1. EXECUTIVE SUMMARY

Sprint 7.2C delivered a **functional** Workflow & Automation Engine with 5 workflows, 41 states, 67 transitions, and 8 automation rules across 9 database tables. However, the architecture contains **7 categories of technical debt** that must be resolved before Sprint 7.3 (Customer Engine). The engine works but is not enterprise-grade.

**Architecture Score: 28/60 (47%) — Requires Refinement**

| Dimension | Score | Status |
|-----------|-------|--------|
| Separation of Concerns | 4/10 | ⚠️ |
| Provider Independence | 2/10 | 🔴 |
| Event-Driven Architecture | 3/10 | 🔴 |
| Data Normalization | 3/10 | 🔴 |
| Extensibility | 4/10 | ⚠️ |
| Multi-Tenant Readiness | 7/10 | ✅ |
| SOLID Compliance | 2/10 | 🔴 |
| DDD Alignment | 3/10 | 🔴 |

---

## 2. DUPLICATED RESPONSIBILITY

### 2.1 Triple-Write on Transition

Every `WorkflowEngine::transition()` writes to **THREE** tables:

```
transition($entity, 'accept')
  ├── WorkflowHistory::create()       ← Table: workflow_history
  ├── Event: WorkflowTransitioned      ← fired
  └── recordTimeline()
       └── RequestTimeline::record()   ← Table: request_timeline
```

Then the event triggers:
```
TriggerAutomationRules::handle()
  └── AutomationEngine::evaluate()
       └── actionCreateTimeline()
            └── RequestTimeline::record()  ← Table: request_timeline (AGAIN!)
```

**Result**: A single status change creates **3–4 records** across 2–3 tables.

### 2.2 Duplicated History Storage

Five tables store overlapping "event/log" data:

| Table | Writes On | Overlap With |
|-------|-----------|--------------|
| `workflow_history` | Every transition | request_history, request_timeline |
| `request_history` | Legacy status change | workflow_history |
| `request_timeline` | Timeline events | activity_logs, automation_logs |
| `automation_logs` | Rule execution | activity_logs |
| `activity_logs` | User actions | request_timeline |

**Identical columns across tables:**
- `actor_id`: present in ALL 5 tables
- `created_at`: present in ALL 5 tables
- `entity_type`/`entity_id` OR `request_id`: present in ALL 5
- `metadata`/`context`/`properties`: JSON blob in ALL 5
- `event`/`action`: present in 3 tables

**Root Cause**: Each Sprint (7.2, 7.2B, 7.2C) added its own history table without deprecating prior ones.

---

## 3. DIRECT PROVIDER COUPLING

### 3.1 AutomationEngine Hardcodes Provider Calls

The `AutomationEngine` directly instantiates provider services:

```php
// Line ~148: WhatsApp
$whatsappService = app(WhatsAppService::class);  // 🔴 Direct coupling
$whatsappService->sendMessage($to, $message);

// Line ~165: Google Drive
$driveService = app(GoogleDrivePhotoService::class);  // 🔴 Direct coupling

// Line ~160: Email
\Illuminate\Support\Facades\Mail::raw($body, ...);  // 🔴 Framework coupling

// Line ~253: WhatsApp AGAIN
\App\Services\WhatsAppService::send($customer->phone, $reviewLink);  // 🔴 Static call
```

**Impact**: To add a new messaging provider (Telegram, SMS), you must edit `AutomationEngine`. To change the WhatsApp implementation, the AutomationEngine is affected. This violates **Open/Closed Principle**.

### 3.2 WorkflowEngine Coupling

```php
// Line 10: Direct model dependency
use App\Models\Tenant\RequestTimeline;  // 🔴 Workflow knows about Timeline

// Lines 293-309: Timeline coupling
private function recordTimeline(...) {
    if ($entity instanceof Request) {
        RequestTimeline::record(...);  // 🔴 Direct call
    }
}
```

**Workflow should produce events, not write to arbitrary tables.**

---

## 4. EVENT ARCHITECTURE ISSUES

### 4.1 Single Event, Single Listener

Only one domain event exists:
- `WorkflowTransitioned` → `TriggerAutomationRules`

Missing events:
- `RequestCreated`, `RequestUpdated`, `RequestCancelled`
- `ServiceCreated`, `ServiceAssigned`, `ServiceCompleted`
- `WorkOrderCreated`, `WorkOrderCompleted`
- `PaymentReceived`
- `CustomerCreated`

### 4.2 String-Based Event Routing (No Bus)

Events are matched by string convention:
```php
$events = [
    'workflow.transitioned',
    "{$event->workflowKey}.transitioned",
    "{$event->workflowKey}.status_changed",
    "{$event->workflowKey}.{$from}_to_{$to}",
    strtolower($entityClass) . '.status_changed',
];
```

No event bus, no event hierarchy, no typed events. Adding a new subscriber requires editing the listener or adding a new one manually.

### 4.3 No Subscriber Pattern

There is no mechanism for independent subscribers to register interest in events. Every subscriber must be manually wired in `AppServiceProvider::boot()`.

---

## 5. CONDITION ENGINE LIMITATIONS

### 5.1 Flat Conditions Only

The current `checkConditions()` supports only flat AND logic:
```php
// All conditions must pass (implicit AND)
foreach ($conditions as $cond) { if (!$pass) return false; }
```

No support for:
- OR groups
- NOT negation
- Nested conditions
- Expression evaluation

### 5.2 Operator Set Is Minimal

Only 14 operators: `=`, `!=`, `==`, `in`, `not_in`, `contains`, `gt`, `lt`, `gte`, `lte`, `exists`, `empty`, `not_empty`. Missing:
- Regex
- Date comparisons (before, after, between)
- Numeric between
- Collection size (count > N)
- Relationship check (has related model)

---

## 6. DELAY ENGINE SIMPLICITY

### 6.1 Single Delay Field

```php
$rule->delay_minutes  // 🔴 Only absolute minutes
```

No support for:
- Specific time (e.g., "9 AM tomorrow")
- Specific date
- Business hours only
- Skip weekends/holidays
- Cron expressions
- Relative to event time
- Queue-based scheduling

---

## 7. APPROVAL EMBEDDED IN WORKFLOW

Approval is modeled as workflow states (`waiting_approval`, `approved`, `rejected`). This works for simple cases but does not scale to:

- Multi-step sequential approval
- Parallel approval (any/all must approve)
- Dynamic approver assignment
- Approval delegation
- Timeout/auto-approve
- Quotation approval
- Purchase approval
- Expense approval
- Warranty approval
- Refund approval
- Discount approval
- Corporate hierarchical approval

---

## 8. WORKFLOW COUPLING SUMMARY

```
WorkflowEngine
  ├── Knows about: States ✅
  ├── Knows about: Transitions ✅
  ├── Knows about: Guards ✅
  ├── Knows about: Permissions ✅
  ├── Knows about: Conditions ✅
  ├── Knows about: Timeline ❌ (should be event subscriber)
  ├── Knows about: Activity Log ❌ (should be event subscriber)
  ├── Knows about: WorkflowHistory ❌ (should be event projection)
  └── Directly fires: WorkflowTransitioned event ✅ (but only ONE event)

AutomationEngine
  ├── Knows about: Rules ✅
  ├── Knows about: Conditions ✅
  ├── Knows about: WhatsApp ❌ (should go through ProviderAdapter)
  ├── Knows about: Google Drive ❌ (should go through ProviderAdapter)
  ├── Knows about: Email ❌ (should go through ProviderAdapter)
  ├── Knows about: Timeline ❌ (should be event subscriber)
  ├── Knows about: FeatureEngine ❌ (should be guard/condition)
  ├── Knows about: CreateWorkOrderAction ❌ (should fire event)
  └── Knows about: User roles ❌ (should be condition)
```

---

## 9. TECHNICAL DEBT INVENTORY

| # | Issue | Severity | Files Affected | Fix |
|---|-------|----------|---------------|-----|
| 1 | Triple-write per transition | HIGH | WorkflowEngine.php | Canonical event_logs |
| 2 | Provider hardcode in Automation | CRITICAL | AutomationEngine.php | ProviderAdapter |
| 3 | 5 overlapping history tables | HIGH | 5 tables | Canonical event_logs |
| 4 | Workflow knows Timeline | HIGH | WorkflowEngine.php | Event-driven |
| 5 | No event bus | HIGH | TriggerAutomationRules | EventBus |
| 6 | Flat conditions only | MEDIUM | AutomationEngine.php | ConditionBuilder |
| 7 | Simple delay only | MEDIUM | AutomationEngine.php | DelayEngine |
| 8 | Approval embedded in workflow | MEDIUM | workflows table | ApprovalEngine |
| 9 | No workflow templates | MEDIUM | WorkflowAutomationSeeder | Template system |
| 10 | SLA simple minute targets | MEDIUM | SlaEngine.php | WorkingCalendar |
| 11 | AutomationEngine 11 action types | HIGH | AutomationEngine.php | ProviderAdapter |
| 12 | String-matching event routing | HIGH | TriggerAutomationRules | EventBus |
| 13 | Static WhatsApp call in review action | HIGH | AutomationEngine.php | ProviderAdapter |
| 14 | FeatureEngine called from Automation | MEDIUM | AutomationEngine.php | Guard condition |

---

## 10. MIGRATION STRATEGY PRINCIPLES

1. **Additive only** — Never drop tables, only add new ones
2. **Deprecate, don't delete** — Mark `workflow_actions` as deprecated, keep data
3. **Canonical event_logs** — New single table, old tables become views/projections
4. **Provider Adapter** — New layer, wraps existing services
5. **Backward compatible** — All existing code paths must continue to work

---

## 11. TARGET ARCHITECTURE SCORE

| Dimension | Current | Target |
|-----------|---------|--------|
| Separation of Concerns | 4/10 | 9/10 |
| Provider Independence | 2/10 | 9/10 |
| Event-Driven Architecture | 3/10 | 9/10 |
| Data Normalization | 3/10 | 8/10 |
| Extensibility | 4/10 | 9/10 |
| SOLID Compliance | 2/10 | 8/10 |
| **Overall** | **28/60 (47%)** | **52/60 (87%)** |
