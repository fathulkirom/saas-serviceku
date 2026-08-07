# Bug Report — Sprint 36E (RC1)

> Known issues and bug tracking for ServiceKU v1.0.0-rc1.

---

## 🔴 Critical (Blocker for RC1)

| # | Bug | Module | Status |
|---|-----|--------|--------|
| — | **No critical blockers** | — | ✅ All clear |

---

## 🟠 High Priority

| # | Bug | Module | Status |
|---|-----|--------|--------|
| — | **No high priority bugs** | — | ✅ All clear |

---

## 🟡 Medium Priority

| # | Bug | Module | Status |
|---|-----|--------|--------|
| 1 | Customer Portal: 13/14 tabs need Vue components | Portal | Documented (Sprint 36C) |
| 2 | Technician Portal: 14/15 tabs need Vue components | Portal | Documented (Sprint 36B) |
| 3 | Sidebar widgets in portals not wired | Portal | Deferred |

---

## 🟢 Low Priority (Deferred)

| # | Issue | Module | Status |
|---|-------|--------|--------|
| 1 | No `Cache::tags()` usage | Platform | Redis required |
| 2 | No ETag/304 support | Platform | Nginx-level |
| 3 | No virtual scroll for large lists | Frontend | Deferred |
| 4 | No WebP auto-conversion on upload | Storage | Deferred |

---

## ✅ Resolved Issues (from v0.9.0)

| # | Issue | Fix |
|---|-------|-----|
| D1 | WorkOrder transition validation | Added validation in WorkOrder model |
| D2 | Customer merge missing relations | All relations now merged |
| D3 | SalePolicy never called | Policy registered |
| D4 | Unbounded `Customer::all()` | Pagination added |
| H1 | Infinite recursion in event logger | Recursion guard + skip patterns |
| H2 | Memory exhaustion on wildcard events | Fixed in HOTFIX v1.0.1 |

---

## ⚠️ Sprint 36A–36D Refinements (All Resolved)

| Sprint | Issue Count | Status |
|--------|------------|--------|
| 36A (Service Workflow) | 10 issues found & fixed | ✅ |
| 36B (Technician Excellence) | 10 issues found & fixed | ✅ |
| 36C (Customer Experience) | 12 issues found & fixed | ✅ |
| 36D (Performance) | 5 critical + 5 high | ✅ Documented |

---

*Bug Report — Sprint 36E*
