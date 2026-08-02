# Sprint 7.2CR — Decision Log

## D-001: Deprecate workflow_actions, Replace with AutomationRules + Subscribers

**Context**: workflow_actions table allowed attaching side effects (send_whatsapp, upload_gdrive) to specific workflow transitions. However, this created two parallel execution paths for the same thing: workflow_actions AND automation_rules.

**Decision**: DEPRECATE workflow_actions. AutomationRules + EventBus subscribers replace all workflow_actions functionality.

**Rationale**:
- workflow_actions are transition-specific (tight coupling)
- automation_rules are event-driven (loose coupling)
- Subscribers handle persistence (history, timeline) which are NOT "actions" but system requirements

**Consequences**:
- workflow_actions table preserved (no data loss)
- New actions must be created as automation_rules
- Existing 3 workflow_actions replaced by WorkflowPersistenceSubscriber

---

## D-002: Canonical event_logs vs. Multiple History Tables

**Context**: 5 tables stored overlapping event data (workflow_history, request_history, request_timeline, automation_logs, activity_logs).

**Decision**: ADD event_logs as canonical source. Existing tables become projections — still written for backward compat, but NEW queries should use event_logs.

**Rationale**:
- Single source of truth eliminates inconsistency
- Projections are denormalized views for query performance
- Timeline = event_logs WHERE entity_type = 'Request' ORDER BY occurred_at
- Activity = event_logs WHERE actor_id = N ORDER BY occurred_at
- Audit = event_logs WHERE event_key IN ('WorkflowStateChanged', 'RequestCreated', ...)

**Migration path**: Future sprint may replace projection writes with DB views.

---

## D-003: ProviderAdapter as Singleton

**Context**: AutomationEngine had 5 private methods directly calling WhatsAppService, GoogleDrivePhotoService, Mail facade, etc.

**Decision**: Create ProviderAdapter as a singleton abstraction. AutomationEngine calls `$adapter->send('whatsapp', ...)` instead of `app(WhatsAppService::class)->sendMessage(...)`.

**Rationale**:
- Adding Telegram = 1 method in ProviderAdapter, ZERO changes to AutomationEngine
- Testing = mock ProviderAdapter, not 5 different services
- Provider health = centralized in one place

---

## D-004: WorkflowEngine as Pure State Machine

**Context**: WorkflowEngine was writing to WorkflowHistory, RequestTimeline, AND firing events — triple responsibility.

**Decision**: Refactor WorkflowEngine to ONLY validate and transition. Emit single event via EventBus. All side effects in subscribers.

**Rationale**:
- Single Responsibility Principle
- Testable in isolation (no DB writes, no events to mock)
- Add a new "side effect" = add a subscriber, don't touch WorkflowEngine

---

## D-005: EventBus as Central Dispatcher

**Context**: Events were dispatched directly via Laravel's `event()` helper, with listeners manually registered.

**Decision**: Build EventBus service that wraps Laravel's dispatcher AND logs to event_logs AND supports subscriber pattern.

**Rationale**:
- Every event automatically logged (no forgotten events)
- Subscriber pattern allows independent modules to react
- Event hierarchy (RequestCompleted is-a EntityEvent) enables wildcard subscribers

---

## D-006: Keep Legacy Tables as Projections

**Context**: 5 existing history tables cannot be dropped without breaking existing reports/dashboards.

**Decision**: Keep ALL existing tables. Write to them via WorkflowPersistenceSubscriber. Treat as projections of event_logs.

**Future**: When reporting/dashboard queries are migrated to event_logs, the old tables can be phased out.
