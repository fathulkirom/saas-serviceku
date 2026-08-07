# Service Module Architecture (Enterprise Platform)

> **Sprint 15.0** — Service Module migrated to Enterprise Engines.

---

## 🏗️ Architecture Overview

```
┌─────────────────────────────────────────────────────────────┐
│                     SERVICE MODULE                           │
├─────────────────────────────────────────────────────────────┤
│  Service List     → Enterprise Data Engine (Sprint 12)      │
│  Service Create   → Enterprise Form Engine (Sprint 11)      │
│  Service Edit     → Enterprise Form Engine (Sprint 11)      │
│  Service Workspace→ Enterprise Workspace Engine (Sprint 10) │
│  Workflow         → Service Model Transitions               │
│  Automation       → Enterprise Automation Engine (Sprint 13)│
│  Reporting        → Enterprise Reporting Engine (Sprint 14) │
│  Dashboard        → Enterprise Dashboard (Sprint 8)         │
│  UI Components    → Enterprise Design System (Sprint 8)     │
├─────────────────────────────────────────────────────────────┤
│  Adapter: ServiceMigrationAdapter                           │
│    → Bridges existing routes to Enterprise Engines          │
│    → Zero changes to existing controllers                   │
└─────────────────────────────────────────────────────────────┘
```

---

## 📋 Module Inventory

### Pages (resources/js/Pages/Services/)
| Page | Status | Migration |
|------|:------:|-----------|
| `Index.vue` | Migrated | Uses `EnterpriseDataTable` via `tableProps` prop |
| `Create.vue` | Migrated | Uses `FormRenderer` via `formSchema` prop |
| `Edit.vue` | Migrated | Uses `FormRenderer` via `formSchema` prop |
| `Show.vue` | Active (fallback) | Legacy detail page, can redirect to workspace |
| `Workspace.vue` | Migrated | Uses Workspace Engine via `workspaceConfig` prop |
| `Kanban.vue` | Active | Kanban board view |

### Components (resources/js/Components/Services/)
| Component | Status |
|-----------|:------:|
| `Header.vue` | Active |
| `StatusStepper.vue` | Active |
| `ActionBar.vue` | Active (role-aware) |
| `InfoCards.vue` | Active |
| `Sections.vue` | Active |
| `Photos.vue` | Active |
| `History.vue` | Active |
| `ServiceAssignModal.vue` | Active |
| `ServiceCancelModal.vue` | Active |
| `ServicePartnerModal.vue` | Active |
| `ServiceCompleteModal.vue` | Active |
| `ServiceChecklistModal.vue` | Active |

---

## 🔌 Engine Wiring

### Data Engine
```php
// Service list uses ServiceListDefinition (Sprint 12)
$tableProps = $presenter->build('service.index', $paginator, $params);
return Inertia::render('Services/Index', ['tableProps' => $tableProps]);
```

### Form Engine
```php
// Service create uses ServiceCreateForm (Sprint 11)
$formSchema = $presenter->build('service.create');
return Inertia::render('Services/Create', ['formSchema' => $formSchema]);
```

### Workspace Engine
```php
// Service workspace uses Workspace Engine (Sprint 10)
$workspace = $workspaceService->build('service', $data);
return Inertia::render('ServiceWorkspace/Index', ['workspaceConfig' => $workspace]);
```

### Automation Engine
```php
// Registered automations (Sprint 13)
// service.completed → Add Timeline + Send WhatsApp + Notification
// Runs when ServiceWorkflowController triggers status change
```

### Reporting Engine
```php
// Available reports (Sprint 14)
// service.daily, service.status
// Dashboard widgets can select these reports
```

---

## 🔄 Workflow (13 Statuses)

```
menunggu_alokasi → diterima → diagnosa → dikerjakan
                                        → indent → dikerjakan
                                        → menunggu_konfirmasi_pelanggan
                                        → menunggu_konfirmasi_internal
                  → dikerjakan → selesai → siap_diambil → close
                  → indent
                  → onpartner → selesai
                  → cancel
```

All transitions defined in `Service::ALLOWED_TRANSITIONS`.

---

## 🗺️ Route → Engine Mapping

| Route | Engine | Adapter Method |
|-------|--------|----------------|
| `services.index` | Data Engine | `ServiceMigrationAdapter::index()` |
| `services.create` | Form Engine | `ServiceMigrationAdapter::create()` |
| `services.edit` | Form Engine | `ServiceMigrationAdapter::edit()` |
| `services.show` | Workspace Engine | `ServiceMigrationAdapter::show()` |

Workflow routes (`services.accept`, `services.start`, etc.) remain in `ServiceWorkflowController` — they handle business logic, not UI.

---

*Service Module Architecture — Sprint 15.0*
