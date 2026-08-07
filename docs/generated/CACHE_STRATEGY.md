# Cache Strategy — Sprint 36D

> Multi-layer cache strategy for ServiceKU production deployment.

---

## 📊 Cache Layers

| Layer | Store | TTL | What |
|-------|-------|-----|------|
| Dashboard Widgets | Redis | 60s | Widget metric data |
| User Permissions | Redis | 300s | Permission cache per user |
| Feature Flags | Redis | 3600s | Feature toggles per tenant |
| Tenant Settings | Redis | 300s | Per-tenant configuration |
| Reports | Redis | 600s | Pre-computed report data |
| Menu Structure | Redis | 3600s | Navigation menu per user |
| Search Hot Queries | Redis | 300s | Frequently searched terms |
| Static Assets | CDN/Headers | 1 year | JS, CSS, fonts, icons |

---

## 🔄 Invalidation Strategy

| Data | Invalidation Trigger |
|------|---------------------|
| Dashboard Widgets | Service status change, sale created |
| User Permissions | Role change, permission update |
| Feature Flags | Feature toggle, plan change |
| Tenant Settings | Setting save |
| Reports | New data in report date range |
| Menu | Permission change, module install |

---

## ⚠️ ANTI-PATTERN: Cache::flush()

**NEVER call `Cache::flush()`** — it wipes ALL cache including other tenants!
- `WorkflowEngine::clearCache()` currently does this
- **Fix**: Replace with `Cache::forget('workflow_transitions')` (targeted key)

---

## 🚀 Production Cache Setup

```env
# .env.production
CACHE_STORE=redis
REDIS_CACHE_CONNECTION=cache
```

---

*Cache Strategy — Sprint 36D*
