# Performance Checklist — Sprint 36D

> Pre-production performance validation checklist.

---

## ✅ Backend Performance

- [ ] All list queries use pagination (no unbounded `get()`)
- [ ] All detail queries eager-load relationships
- [ ] No N+1 queries in hot paths (verified with Laravel Debugbar/Telescope)
- [ ] `Model::preventLazyLoading()` enabled in dev
- [ ] Query count per page: Dashboard < 10, Workspace < 15, List < 5
- [ ] Slow queries (>100ms) identified and optimized
- [ ] Missing indexes added (8 recommended)
- [ ] `Cache::flush()` replaced with targeted invalidation
- [ ] Redis configured as cache driver (not database)

## ✅ Queue Performance

- [ ] `after_commit: true` on all queue connections
- [ ] Queue priorities configured (high/default/low)
- [ ] Failed job retry uses exponential backoff
- [ ] Queue workers supervised (Supervisor/Horizon)
- [ ] Queue lag < 5s under normal load

## ✅ Frontend Performance

- [ ] Vite code splitting configured (manualChunks)
- [ ] Lazy loading for tab content and below-fold widgets
- [ ] `useDebounce` on search inputs (300ms)
- [ ] Images use `loading="lazy"` and WebP format
- [ ] No memory leaks (verified in Chrome DevTools Memory profiler)
- [ ] Bundle size: vendor < 200KB, app < 100KB (gzipped)
- [ ] Lighthouse score > 90 (Performance)

## ✅ Inertia Optimization

- [ ] Partial reloads used for workspace/dashboard refresh
- [ ] `preserveScroll` on navigation
- [ ] `preserveState` on form re-renders
- [ ] Lazy/deferred props for expensive data

## ✅ Database Optimization

- [ ] Slow query log enabled (long_query_time=2)
- [ ] All foreign keys indexed
- [ ] EXPLAIN run on top 10 most frequent queries
- [ ] No `SELECT *` on large tables without `select()` column limit
- [ ] `chunk()`/`lazy()` used for batch processing

## ✅ Cache Performance

- [ ] Dashboard widget cache hit ratio > 80%
- [ ] Permission cache per user (Redis)
- [ ] Feature flag cache per tenant
- [ ] Settings cache per tenant
- [ ] No `Cache::flush()` in application code

## ✅ Security Hardening

- [ ] All controller actions authorized
- [ ] Rate limiting on all public endpoints
- [ ] File upload validation complete
- [ ] `APP_DEBUG=false` in production
- [ ] `DB::prohibitDestructiveCommands()` enabled

---

*Performance Checklist — Sprint 36D*
