# ARCHITECTURE.md — ServiceKU v0.9.0-beta

## System Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    CENTRAL LAYER                         │
│  ┌─────────┐  ┌─────────┐  ┌──────────┐  ┌───────────┐ │
│  │  Plans  │  │ Tenants │  │Payments  │  │ Vouchers  │ │
│  └─────────┘  └─────────┘  └──────────┘  └───────────┘ │
│  ┌──────────────┐  ┌──────────────────┐                 │
│  │ SystemSettings│  │ GoogleDriveTokens│                 │
│  └──────────────┘  └──────────────────┘                 │
└─────────────────────────────────────────────────────────┘
                           │
                    stancl/tenancy
                    (subdomain resolution)
                           │
        ┌──────────────────┼──────────────────┐
        ▼                  ▼                  ▼
┌───────────────┐  ┌───────────────┐  ┌───────────────┐
│  Tenant DB 1  │  │  Tenant DB 2  │  │  Tenant DB N  │
│  (full schema)│  │  (full schema)│  │  (full schema)│
└───────────────┘  └───────────────┘  └───────────────┘
```

## Tenant Layer — Domain Model

```
┌─────────────────────────────────────────────────────────┐
│                     ENGINES (read-only)                   │
│  WorkflowEngine  AutomationEngine  PermissionEngine      │
│  FeatureEngine   SlaEngine        ConditionBuilder       │
│  EventBus (deprecated → Laravel native event())           │
└─────────────────────────────────────────────────────────┘
                           │
┌─────────────────────────────────────────────────────────┐
│                     CORE ENTITIES                        │
│                                                          │
│  Customer ──── Service ──── WorkOrder ──── Invoice       │
│     │             │                                     │
│     ├─ Device     ├─ Diagnosis                           │
│     ├─ Interaction├─ Quotation                          │
│     ├─ Tag        ├─ ServicePart                        │
│     ├─ Note       ├─ Checklist                          │
│     ├─ Complaint  ├─ QC Check                           │
│     └─ Comm      ├─ Delivery                            │
│                  └─ Warranty                            │
│                                                          │
│  Product ──── InventoryMutation ──── StockLocation      │
│     │             │                                     │
│     ├─ Supplier   ├─ StockOpname                        │
│     ├─ Purchase   ├─ StockTransfer                      │
│     └─ PriceHist  └─ TechnicianInventory                │
│                                                          │
│  Sale ──── SaleItem ──── CashRegister ──── Shift        │
│     │                                                   │
│     ├─ DiscountRule                                      │
│     ├─ ProductBundle                                    │
│     └─ Promotion                                        │
│                                                          │
│  User ──── Role ──── Permission                          │
│     │                                                   │
│     └─ Branch                                           │
└─────────────────────────────────────────────────────────┘
```

## Request Flow

```
Browser Request
    │
    ▼
Nginx / subdomain.domain.com
    │
    ▼
Laravel Kernel
    │
    ├─ InitializeTenancyBySession (restore tenant from session)
    ├─ CheckSubscription (trial/active/expired)
    │
    ▼
Route → Controller
    │
    ├─ check.plan.feature middleware → FeatureEngine::can()
    ├─ permission middleware → PermissionEngine
    ├─ Policy → authorize()
    │
    ▼
Controller Method
    │
    ├─ Validate (FormRequest or inline)
    ├─ Business Logic (inline or delegated to Service)
    ├─ event() → EventLogger (wildcard listener)
    ├─ DB::transaction() (for multi-table writes)
    │
    ▼
Inertia Response or JSON
    │
    ▼
Vue 3 SPA (client-side hydration)
```

## Feature Resolution (FeatureEngine)

```
User requests feature
    │
    ▼
Layer 1: Module Activation
    tenant_modules.enabled == false? → 'none'
    │
    ▼
Layer 2: Plan Feature
    plan.features[business_type][feature] → 'full' | 'read_only' | 'none'
    │
    ▼
Layer 3: Business Type Constraint
    retail_only + services → 'none'
    default → 'full'
```

## Business Types

| Type | Services | In-House Repair | Inventory | POS |
|------|----------|-----------------|-----------|-----|
| `full_service` | ✅ | ✅ | ✅ | ✅ |
| `aksesoris_service` | ✅ | ❌ (dilempar) | ✅ | ✅ |
| `aksespare_service` | ✅ | ✅ | ✅ | ✅ |
| `gadget_full` | ✅ | ✅ | ✅ | ✅ |
| `retail_only` | ❌ | ❌ | ✅ | ✅ |

## Database Architecture

- **Central DB**: tenants, plans, payments, vouchers, system_settings, google_drive_tokens
- **Tenant DBs**: ~80 tables, full schema per tenant
- **Migrations**: 16 central + 52 tenant (all additive, no destructive changes)
- **Indexes**: 20+ performance indexes added in Sprint 7.5D

## Frontend Architecture

- **Framework**: Vue 3.5 (Composition API, `<script setup>`)
- **SPA**: Inertia.js (no API calls, direct controller → page)
- **Styling**: Tailwind CSS (utility-first)
- **Routing**: Ziggy (Laravel routes → JS)
- **State**: Inertia shared props + composables (no Vuex/Pinia)
- **Components**: 45 reusable components (K-prefix design system)
- **Pages**: 77 Vue pages across 22 directories

## Key Design Decisions

1. **No separate API layer** — Inertia.js serves pages directly, no REST API for frontend
2. **EventBus deprecated** — Replaced by Laravel native `event()` + wildcard EventLogger
3. **Permission additive** — New role_permission tables coexist with legacy `role` column
4. **Dynamic severity** — Setup Assistant evaluates checklist severity based on business type + FeatureEngine
5. **Optimistic locking** — `lock_version` on Service and Product for concurrent edit safety
6. **SQLite for dev** — Each tenant gets its own SQLite file mimicking MySQL isolation
