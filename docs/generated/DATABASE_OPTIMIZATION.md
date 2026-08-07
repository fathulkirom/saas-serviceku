# Database Optimization — Sprint 36D

> Query optimization patterns, eager loading checklist, and index strategy.

---

## 📋 Eager Loading Checklist

### Service Model
- **List queries**: `with(['customer:id,name,phone', 'technician:id,name', 'branch:id,name'])`
- **Detail queries**: `with(['diagnosis', 'photos', 'checklists', 'spareparts', 'delivery', 'quotations', 'qcCheck'])`
- **Dashboard**: `with(['customer', 'technician'])`

### Sale Model
- **List queries**: `with(['customer:id,name', 'items'])`
- **Detail queries**: `with(['items.product', 'service.customer', 'payments'])`

### WorkOrder Model
- **Always**: `with(['technician', 'service.customer'])`

### Customer Model
- **List queries**: Lean — no eager loading needed
- **Detail queries**: `with(['services', 'sales', 'devices', 'tags'])`

---

## 🚫 N+1 Prevention Rules

| Rule | Severity |
|------|----------|
| Always eager-load relationships used in loops | 🔴 Critical |
| Use `select()` to limit columns on large tables | 🟠 High |
| Use `chunk()` or `lazy()` for large dataset processing | 🔴 Critical |
| Use `toBase()` for aggregate-only queries | 🟡 Medium |
| Avoid `whereHas()` with large tables — use join/subquery | 🟠 High |
| Index columns in WHERE, JOIN, ORDER BY, GROUP BY | 🔴 Critical |
| Always paginate — never return unbounded result sets | 🔴 Critical |
| Use conditional aggregates for counts | 🟡 Medium |

---

## 🔍 Query Patterns: Bad → Good

### N+1 in loops
```php
// ❌ BAD — N+1 queries
foreach ($services as $s) {
    echo $s->customer->name;
}

// ✅ GOOD — 2 queries
$services = Service::with('customer')->get();
```

### Unbounded results
```php
// ❌ BAD — loads all records
Service::all();

// ✅ GOOD — paginated
Service::paginate(25);
```

### Large dataset processing
```php
// ❌ BAD — loads all into memory
Service::all()->each(fn($s) => $s->update([...]));

// ✅ GOOD — streams records
Service::lazy()->each(fn($s) => $s->update([...]));
```

---

*Database Optimization — Sprint 36D*
