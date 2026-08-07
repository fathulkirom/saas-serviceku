# Performance Guide — Sprint 36D

> Complete performance optimization guide for ServiceKU production deployment.

---

## 🎯 Performance Targets

| Page Type | Target | Max | Metric |
|-----------|--------|-----|--------|
| Dashboard | < 1s | 2s | First Contentful Paint |
| Service Workspace | < 500ms | 1s | Time to Interactive |
| Data Table (List) | < 300ms | 800ms | Server Response |
| Data Table (100K+ rows) | < 500ms | 1.5s | Query + Render |
| Form Save | < 200ms | 500ms | Server Response |
| Global Search | < 150ms | 400ms | Server Response |
| API Endpoint | < 100ms | 300ms | Server Response |
| File Upload (5MB) | < 3s | 5s | Upload Duration |
| Report Generation | < 3s | 10s | Server Response |
| Notification Delivery | < 5s | 15s | End-to-End |

---

## 🔴 Critical Fixes (MUST do before production)

| # | Issue | Fix | Risk |
|---|-------|-----|------|
| 1 | Queue `after_commit=false` | Set `after_commit=true` in `config/queue.php` | Data inconsistency |
| 2 | No `preventLazyLoading()` | Add in `AppServiceProvider::boot()` | N+1 queries in production |
| 3 | `Cache::flush()` in WorkflowEngine | Replace with targeted key deletion | Wipes ALL cache |
| 4 | Default cache driver is `database` | Set `CACHE_STORE=redis` | DB hammered on every request |
| 5 | No Vite code splitting | Add `manualChunks` in `vite.config.js` | Single large JS bundle |

---

## 🟠 High Priority Improvements

| # | Issue | Fix |
|---|-------|-----|
| 6 | No debounce/throttle composable | Created `usePerformance.js` |
| 7 | `maintenance.driver=file` | Set to `cache` for multi-server |
| 8 | No response caching | Add `Cache-Control` middleware |
| 9 | No destructive command protection | `DB::prohibitDestructiveCommands()` in production |
| 10 | No query benchmark | Log queries >100ms with EXPLAIN |

---

## 📊 Recommended Indexes

| Table | Columns | Reason |
|-------|---------|--------|
| `services` | `(status, branch_id)` | Dashboard filtering |
| `services` | `(customer_id, status)` | Customer history |
| `services` | `(technician_id, status)` | Technician workload |
| `services` | `(tracking_code)` | Tracking lookup |
| `services` | `(imei_sn)` | IMEI lookup |
| `sales` | `(customer_id, created_at)` | Purchase history |
| `sales` | `(branch_id, payment_status)` | Revenue reports |
| `jobs` | `(queue, available_at)` | Queue polling |

---

*Performance Guide — Sprint 36D*
