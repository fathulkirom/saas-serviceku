# PRODUCTION GAP BACKLOG

## P0 — BLOCKERS
- **B-01:** Connect `Inertia::render()` for all Service Intake routes. The current Vue pages are orphaned.
- **B-02:** Remove or implement `WorkspaceShell.vue` and `WorkspaceMetaPresenter`. The claim of "Universal Workspace Integration" is completely false, leaving major UI sections unmountable.
- **B-03:** Wire `CustomEvent` listeners in the frontend. Dozens of actions (like `service:create`) fire events into the void.
- **B-04:** Implement global branch scopes and Policy-based authorization (`authorize()`) in all tenant controllers to prevent cross-branch data leaks.

## P1 — CORE OPERATIONAL
- **C-01 Intake:** Wire `CustomerController` and `DeviceController` seamlessly into the Intake form.
- **C-02 Master Data:** Provide basic CRUD UI for `ChecklistTemplate`, `MasterLaborService`, and `TaxSetting`.
- **C-03 Technician:** Hook the state machine (Start/Pause/Finish) in `Technician Portal` to actual `WorkOrder` transitions.
- **C-04 QC:** Build the controller endpoint to receive and store `ServiceQcCheck` data from the UI.
- **C-05 Payment:** Integrate POS checkout to safely generate an `Invoice` with exact `TaxSetting` calculations.
- **C-06 Pickup:** Wire the `PickupDelivery` endpoint to close the `Service` status.

## P2 — IMPORTANT (Workarounds Exist)
- **I-01:** Refactor monolithic migrations (`create_tenant_core_tables`) to separate files for easier debugging.
- **I-02:** Resolve `personal_access_tokens_table` pending migration.
- **I-03:** Clean up 10 orphaned `app/Events` that are not registered in `event:list`.

## P3 — DEFERRED (Not Needed for Pilot)
- **D-01:** AI integration and predictions (`ai:ask-insight`, etc.).
- **D-02:** Cross-branch stock indentations.
- **D-03:** Digital warranty claims.

## Final Recommendation for Next Prompt
**Next Prompt Recommendation:**
"Implement P0-B01 and B-03: Create a real, functional Service Intake controller that properly renders the intake Vue page using Inertia, and replace the empty `CustomEvent` triggers with actual Axios API calls to save an Intake record."
