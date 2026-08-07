# FRONTEND RUNTIME AUDIT

## 1. Vue Pages vs Controller Wiring
There are **131** Vue `.vue` files inside `resources/js/Pages`. However, a deep search of all controllers (`app/Http/Controllers`) shows that:
- `Inertia::render()` is called exactly **14** times.
- The `inertia()` helper function is called exactly **70** times.
- Total potential frontend page renders from backend: **84**.

This means **nearly 50 Vue pages** are orphans, legacy, or definitions that can never be reached by a backend route. 

## 2. Shell UI and Placeholders
- **Halaman Shell:** Most of the Enterprise pages and workspace wrappers are purely shell. `WorkspaceShell.vue` is completely detached.
- **CustomEvent tanpa listener:** Dozens of JavaScript actions are defined via `window.dispatchEvent(new CustomEvent(...))` (e.g. `customer:send-wa`, `asset:dispose`). There is **not a single listener** attached to these events. Clicking a button that dispatches these will do absolutely nothing.
- **Fake Charts & Data:** The dashboards rely heavily on hardcoded JSON structures and mock data definitions rather than live queries. 

## 3. Frontend Module Status

| Module | UI Reality Status | Evidence / Notes |
|--------|------------------|------------------|
| **Service** | 🟠 SHELL ONLY | The intake flow exists in Vue but is missing deep form posting wiring and valid status state changes. |
| **Customer** | 🟡 PARTIALLY WORKING | Basic CRUD might exist, but relations to Service and `customer:send-wa` events are disconnected. |
| **Inventory** | 🟠 SHELL ONLY | Stock deduction and mutation logic on the backend is disconnected from the UI. |
| **Purchasing** | 🟠 SHELL ONLY | Purchase orders and supplier management exist as forms but lack complex endpoint handling. |
| **POS** | 🔴 BROKEN | Cannot complete a true checkout cycle to generate a valid `Invoice` with tax and discounts safely. |
| **Finance inti** | 🔴 BROKEN | Stubbed modules. "Universal integration" claims are false. |
| **Setup** | 🟡 PARTIALLY WORKING | Tenant setup logic exists but is fragile during module activation. |
| **User/Role** | 🟡 PARTIALLY WORKING | Spatie permission UI is present but often bypasses strict policy checks on core actions. |
| **Customer Portal**| 🟠 SHELL ONLY | Only basic views; appointments and portal messaging drop into empty `CustomEvent` calls. |
| **Technician Portal**| 🟠 SHELL ONLY | State transitions (Start, Pause, Finish) lack the strict backend timeline validations. |

## 4. Final Verdict
The frontend is visually extensive but functionally shallow. It is essentially a **high-fidelity prototype** rather than a production-ready application.
