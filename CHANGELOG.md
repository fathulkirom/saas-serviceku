# CHANGELOG

## v0.9.0-beta (2026-08-02) — FEATURE COMPLETE

### 🎯 Milestone: Operational Module Freeze
Semua modul operasional ServiceKU dinyatakan **Feature Complete** dan dibekukan (FROZEN).

### ✅ Completed Sprints

#### Sprint 7.3 — Customer Engine
- **7.3**: Customer 360° — profile, interactions, tags, segments
- **7.3B**: Customer Relationship Core
- **7.3C**: Customer Communication (WhatsApp, Email, SMS templates)
- **7.3D**: Customer Intelligence (notes, complaints, risk scoring)
- **7.3E**: Service Intake (checklist, snapshot, condition confirmation)
- **7.3E-H**: Intake Hardening (device lifecycle, health history)
- **7.3F**: Technician Workflow (diagnosis, quotation, part request, QC)
- **7.3G**: Service Delivery & Pickup
- **7.3H**: Warranty & After Sales (claims, reopen, exception handling)

#### Sprint 7.4 — Inventory Intelligence
- **7.4**: Stock movement, locations, suppliers, POs, serials
- **7.4 Revision**: Real service center part flow (request → approve → use → return)
- **7.4A**: Operational Refinement (warehouse dashboard, CS stats, owner KPI)
- **7.4B**: Daily Operations (worklog, pause/resume, booking, lock/reopen)

#### Sprint 7.5 — Retail POS & Operations
- **7.5**: Retail/POS (shifts, payment, discounts, bundles, price levels, returns, promotions)
- **7.5A**: UX Productivity (universal search, keyboard shortcuts)
- **7.5B**: Service Workspace (unified service view)
- **7.5C**: Operational Control (kanban, pickup queue, approval center, SLA)
- **7.5D**: Production Hardening (indexes, optimistic locking, stock integrity)
- **7.5E**: Data Migration (base importer, CSV import, preview, queue, rollback)
- **7.5F**: Setup Assistant (dynamic checklist, health check, welcome card, permission-based)

### 🏗️ Core Engines (STABLE)
- WorkflowEngine — pure state machine with transition validation
- AutomationEngine — rule-based triggers with ProviderAdapter
- PermissionEngine — role-permission with module-level gating
- FeatureEngine — unified resolution: module → plan → business type
- SlaEngine — configurable SLA with breach detection
- ConditionBuilder — nested AND/OR/NOT evaluation
- ProviderAdapter — WhatsApp, Email, GDrive, Push

### 🔧 Technical Stack
- **Backend**: Laravel 12 + PHP 8.5
- **Frontend**: Vue 3.5 (Composition API) + Inertia.js + Tailwind CSS
- **Multi-Tenant**: stancl/tenancy (1 DB per tenant, subdomain resolution)
- **Database**: SQLite (dev) / MySQL (production)

### ⚠️ Known Issues (see RELEASE_NOTES)
- **D1**: Duplicate `Supplier` class (InventoryModels.php + Supplier.php) — CRITICAL
- **D2**: Duplicate `RepairStarted` event (DailyOpsEvents + TechnicianWorkflowEvents) — CRITICAL
- **M3**: Missing `CustomerMerged` event referenced by Customer::merge() — CRITICAL
- **FE1**: Missing Vue pages for Requests module (Requests/Index, Create, Show) — CRITICAL
- **S1**: ProviderAdapter::send() calls sendMessage() but WhatsAppService has send() — WARNING

---

## v0.5.0 (Historical)
- Initial tenant scaffolding
- stancl/tenancy integration
- Basic CRUD for services, customers, products
- Role-based dashboard routing
- Login / registration flow

## v0.1.0 (Historical)
- Project bootstrap
- Laravel + Inertia setup
- Tailwind CSS integration
