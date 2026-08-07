# Bug Fix Log — ServiceKU v1.0

> All bugs found and fixed during production implementation.

---

## 🔴 Critical (Blocker) — All Resolved

| # | Bug | Found | Fixed | Root Cause | Fix |
|---|-----|:-----:|:-----:|------------|-----|
| D1 | Duplicate Supplier class | v0.9.0 | ✅ | `app/Models/Tenant/Supplier.php` + `app/Models/Supplier.php` | Removed duplicate, use Tenant model |
| D2 | Duplicate RepairStarted event | v0.9.0 | ✅ | Two classes with same name | Consolidated to `Events/Entity/TechnicianWorkflowEvents.php` |
| M3 | Missing CustomerMerged event | v0.9.0 | ✅ | Event not created when merge happens | Created `Events/Customer/CustomerMerged.php` |
| FE1 | Missing Requests Vue pages | v0.9.0 | ✅ | Pages not created | Created `Create.vue`, `Index.vue`, `Show.vue` |
| S1 | ProviderAdapter sendMessage vs send | v0.9.0 | ✅ | Method name mismatch | Standardized to `send()` |

## 🟠 High Priority — All Resolved

| # | Bug | Found | Fixed | Fix |
|---|-----|:-----:|:-----:|-----|
| H1 | Infinite recursion in EventLogger | HOTFIX v1.0.1 | ✅ | Added recursion guard + skip patterns |
| H2 | Memory exhaustion on wildcard events | HOTFIX v1.0.1 | ✅ | Fixed Event::listen('*') pattern |
| H3 | `diagnosa` status had NO inbound transition | Sprint 36A | ✅ | Added `diterima→diagnosa` |
| H4 | `close` allowed without payment validation | Sprint 36A | ✅ | `ServiceWorkflowValidator` blocks |
| H5 | `ready` allowed without QC pass | Sprint 36A | ✅ | `ServiceWorkflowValidator` blocks |

## 🟡 Medium Priority — All Resolved

| # | Bug | Found | Fixed | Fix |
|---|-----|:-----:|:-----:|-----|
| M1 | 10/10 technician action handlers empty stubs | Sprint 36B | ✅ | All 10 implemented with fetch() |
| M2 | 7/7 customer portal action handlers empty stubs | Sprint 36C | ✅ | All 7 implemented |
| M3 | No QC status in service lifecycle | Sprint 36A | ✅ | `selesai` = QC phase |
| M4 | Checklist had no categories | Sprint 36A | ✅ | 10 categories, 55 items |
| M5 | Photo management uncategorized | Sprint 36A | ✅ | 6 categories defined |
| M6 | Timer tracked but no UI | Sprint 36B | ✅ | Wired to WorkOrder API |
| M7 | No technician KPI dashboard | Sprint 36B | ✅ | 10 KPIs defined |
| M8 | No diagnosis templates | Sprint 36B | ✅ | 18 templates defined |
| M9 | No customer journey mapping | Sprint 36C | ✅ | 12 stages mapped |
| M10 | No tracking progress system | Sprint 36C | ✅ | 7-stage progress bar |

## 🟢 Low Priority — Deferred

| # | Issue | Status |
|---|-------|:------:|
| L1 | No `useDebounce` composable (inline timeouts) | ✅ Created Sprint 36D |
| L2 | No Vite code splitting | 📋 Documented |
| L3 | `Cache::flush()` in WorkflowEngine | 📋 Documented (needs refactor) |
| L4 | `after_commit=false` on queue | 📋 Documented |
| L5 | `maintenance.driver=file` | 📋 Documented |
| L6 | No WebP auto-conversion | 📋 Deferred |
| L7 | No virtual scroll | 📋 Deferred |
| L8 | Portal tabs unwired (20 modules) | 📋 Deferred (needs Vue components) |

---

## 🐛 Open Bugs (v1.0 Production Candidate)

| Severity | Count | Details |
|:--------:|:-----:|---------|
| Critical | **0** | — |
| High | **0** | — |
| Medium | **3** | Portal stubs (documented), device_id not in fillable, IMEI auto-detect not wired |
| Low | **7** | Queue/cache config, WebP, virtual scroll, master data UI gaps |

---

*Bug Fix Log — ServiceKU v1.0*
