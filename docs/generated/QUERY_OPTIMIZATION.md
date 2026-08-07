# Query Optimization — Sprint 36D

> Slow query detection, optimization checklist, and benchmark targets.

---

## 🐌 Slow Query Detection

### MySQL Level
```ini
# my.cnf
slow_query_log = 1
long_query_time = 2
log_queries_not_using_indexes = 1
```

### Application Level
Log queries exceeding 100ms with:
- Full SQL with bindings
- EXPLAIN output
- Calling controller/action
- Tenant ID

---

## 📊 Query Optimization Checklist

- [ ] All list queries use `paginate()` not `get()`
- [ ] All detail queries eager-load relationships via `with()`
- [ ] All looped queries pre-loaded (no N+1)
- [ ] `select()` used to limit columns on large tables
- [ ] `chunk()`/`lazy()` used for processing >1000 records
- [ ] `whereHas()` replaced with subquery/join where possible
- [ ] All WHERE/JOIN/ORDER BY columns have indexes
- [ ] `DB::raw()` usage audited (parameterized)
- [ ] `toBase()` used for aggregate-only queries
- [ ] Conditional aggregates replace multiple COUNT queries

---

## 🔍 EXPLAIN Checklist

For every query in hot paths (dashboard, workspace, datatable):
```sql
EXPLAIN SELECT ...;
```
Check for:
- `type`: should be `const`, `eq_ref`, `ref`, or `range` (NOT `ALL`)
- `rows`: should be small relative to table size
- `Extra`: should NOT contain `Using filesort` or `Using temporary`

---

*Query Optimization — Sprint 36D*
