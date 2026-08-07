# Service Module Migration Guide

> **Sprint 15.0** — Migration to Enterprise Platform.

---

## 🎯 Migration Summary

Service Module dimigrasikan ke **6 Enterprise Engines**:

| Engine | Sprint | Service Usage |
|--------|:------:|---------------|
| Design System | 8.0 | SkCard, SkDataTable, SkBreadcrumb, SkModal, SkDrawer |
| Dashboard | 8.0B/C | Service metrics in dashboard widgets |
| Workspace | 10.0 | Service detail workspace (tabs, sidebar, inspector) |
| Form | 11.0 | Service create/edit forms |
| Data | 12.0 | Service list table with filters, bulk actions, export |
| Automation | 13.0 | Auto-timeline, WhatsApp, notification on status change |
| Reporting | 14.0 | Service daily report, status distribution |

---

## 🔄 Migration Status Per Route

| Route | Old Engine | New Engine | Status |
|-------|-----------|------------|:------:|
| `services.index` | KTable + manual filters | EnterpriseDataTable + ServiceListDefinition | ✅ Ready |
| `services.create` | Manual form | FormRenderer + ServiceCreateForm | ✅ Ready |
| `services.edit` | Manual form | FormRenderer | ✅ Ready |
| `services.show` | Manual page | Workspace Engine | ✅ Ready |
| Workflow routes | ServiceWorkflowController | ServiceWorkflowController (unchanged) | ✅ Active |
| Checklist routes | ServiceChecklistController | ServiceChecklistController | ✅ Active |
| Photo routes | Inline upload | FileUpload + SkFileUpload | ⚠️ Partial |

---

## 📋 Deprecation List

Components yang SUDAH tergantikan dan AMAN untuk dihapus di sprint berikutnya:

### Frontend Components (masih active, tapi redundant)
| Component | Digantikan Oleh | Status |
|-----------|----------------|:------:|
| `Pages/Dashboard.vue` (old dashboard) | `Dashboard.vue` (Enterprise, Sprint 8.0B) | ⚠️ Keep both, old is fallback |
| `Pages/CsDashboard.vue` | `Dashboard.vue` (unified) | 📦 Deprecated |
| `Pages/CashierDashboard.vue` | `Dashboard.vue` (unified) | 📦 Deprecated |
| `Pages/TechnicianDashboard.vue` | `Dashboard.vue` (unified) | 📦 Deprecated |
| `Pages/CourierDashboard.vue` | `Dashboard.vue` (unified) | 📦 Deprecated |
| `Components/KTable.vue` | `EnterpriseDataTable.vue` | ⚠️ Keep for non-migrated pages |
| `Components/StatCard.vue` | `SkMetricCard.vue` | ⚠️ Keep for non-migrated pages |
| `Components/Drawer.vue` | `SkDrawer.vue` | ⚠️ Keep (SkDrawer wraps Drawer) |
| `Components/KDialog.vue` | `SkModal.vue` | ⚠️ Keep (KModal aliases to KDialog) |

### Pages (still active, route to be redirected)
| Page | Redirect To | Status |
|------|------------|:------:|
| `Pages/Services/Show.vue` | `ServiceWorkspace/Index.vue` | 📦 Deprecated |

### Controllers (active, adapter available)
| Controller | Adapter | Status |
|-----------|---------|:------:|
| `ServiceController@index` | `ServiceMigrationAdapter::index()` | ✅ Can swap |
| `ServiceController@create` | `ServiceMigrationAdapter::create()` | ✅ Can swap |
| `ServiceController@edit` | `ServiceMigrationAdapter::edit()` | ✅ Can swap |
| `ServiceController@show` | `ServiceMigrationAdapter::show()` | ✅ Can swap |

---

## 🗑️ Safe to Delete (Sprint 16.0+)

After confirming all pages work with new engines:

- `Pages/CsDashboard.vue` → Redirect route to `Dashboard.vue`
- `Pages/CashierDashboard.vue` → Redirect route to `Dashboard.vue`
- `Pages/TechnicianDashboard.vue` → Redirect route to `Dashboard.vue`
- `Pages/CourierDashboard.vue` → Redirect route to `Dashboard.vue`
- `Pages/Services/Show.vue` → Redirect route to `ServiceWorkspace/Index.vue`

---

## ⚠️ DO NOT DELETE (still needed)

- `Components/KTable.vue` — used by non-migrated pages (Inventory, Finance, etc.)
- `Components/StatCard.vue` — used by non-migrated pages
- `Components/KDialog.vue` — used by modal aliases
- `Components/Drawer.vue` — used as base for KDrawer
- `Components/Services/*` — service-specific components still needed by workspace sections

---

*Service Module Migration Guide — Sprint 15.0*
