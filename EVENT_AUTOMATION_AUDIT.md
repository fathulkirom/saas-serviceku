# EVENT, AUTOMATION & NOTIFICATION AUDIT

## 1. Event Registration Reality
- **Defined Events:** 16 files found in `app/Events`.
- **Registered Listeners:** Only 6 Application events are actively hooked up according to `event:list`.
  - `CustomerApprovedRepair` -> `TriggerAutomationRules@handleCustomerApproved`
  - `DiagnosisCompleted` -> `TriggerAutomationRules@handleDiagnosisCompleted`
  - `QuotationCreated` -> `TriggerAutomationRules@handleQuotationCreated`
  - `QuotationRejected` -> `TriggerAutomationRules@handleQuotationRejected`
  - `WorkflowStateChanged` -> `WorkflowPersistenceSubscriber`, `AutomationSubscriber`
  - `WorkflowTransitioned` -> `TriggerAutomationRules`

## 2. Unused / Dead Events
There are 10 other events inside `app/Events` that are completely orphaned. They are never dispatched or have no active listeners attached to them. This represents severe dead code accumulation.

## 3. Automation Implementation
- 11 Automation definitions exist in `app/Enterprise/Automation`.
- The `TriggerAutomationRules` listener acts as a funnel.
- **Risk:** If `TriggerAutomationRules` blocks or fails, it currently lacks robust retry mechanisms (e.g. they don't seem to be explicitly queued via standard `ShouldQueue` on the `event:list`, unlike `Stancl\Tenancy\Listeners\UpdateSyncedResource` which explicitly shows `ShouldQueue`). This means automation execution blocks the main request thread, severely threatening runtime performance and stability.

## 4. Notifications
Notification providers are not fully wired. Most system alerts fall into `SystemLog` without reaching a live user notification channel via Websockets or email.

## 5. Event Logs
The `event_logs` table and model exist, but error swallowing is prevalent. If an automation fails, the try-catch block often suppresses the error, leading to silent failures in the workflow.
