# Release Checklist — Sprint 36E (RC1)

> Pre-release validation for ServiceKU v1.0.0-rc1.

---

## 🚀 Release Candidate Checklist

### Code Quality
- [ ] No critical or high priority bugs open
- [ ] All 51 existing tests passing (`php artisan test`)
- [ ] No PHP lint errors (`./vendor/bin/pint --test`)
- [ ] No JS lint errors (`npm run lint`)
- [ ] No `dd()`, `var_dump()`, `console.log()` in production code
- [ ] No hardcoded values (all from config/env)

### Features
- [ ] All 20 ERP modules operational
- [ ] All 7 Enterprise Engines functional
- [ ] All 21 workspaces registered
- [ ] All 70+ dashboard widgets rendering
- [ ] All automation rules triggering
- [ ] All reports generating
- [ ] No placeholder text or dummy data

### Roles & Permissions
- [ ] 9 roles tested with correct access
- [ ] Feature gates respect business types
- [ ] Menu items correct per role
- [ ] Action buttons correct per role + status

### Security
- [ ] `APP_DEBUG=false` in production
- [ ] All routes authorized via Policy/can()
- [ ] Rate limiting active on public endpoints
- [ ] File upload validation complete
- [ ] CSP headers active
- [ ] No sensitive data in client-side
- [ ] `composer audit` passes

### Performance
- [ ] Dashboard load < 1s
- [ ] Workspace load < 500ms
- [ ] Search response < 300ms
- [ ] Cache hit ratio > 80%
- [ ] Queue lag < 5s
- [ ] No N+1 queries in hot paths

### Database
- [ ] All migrations run cleanly (`php artisan migrate:fresh`)
- [ ] All seeders run without error
- [ ] Foreign keys enforced
- [ ] Indexes on all queried columns
- [ ] No soft-delete orphan records

### Deployment
- [ ] Docker build successful
- [ ] `composer install --optimize-autoloader` works
- [ ] `php artisan config:cache` works
- [ ] `php artisan route:cache` works
- [ ] `php artisan view:cache` works
- [ ] `php artisan event:cache` works
- [ ] Health check endpoint returns 200
- [ ] Queue worker starts correctly

### Documentation
- [ ] README.md up to date
- [ ] ARCHITECTURE.md reflects current state
- [ ] All 20 module architecture docs present
- [ ] All 35 sprint reports present
- [ ] All 5 refinement sprint docs present (36A-E)
- [ ] API documentation complete
- [ ] Deployment guide complete

### Monitoring
- [ ] Sentry configured (DSN set)
- [ ] EPOC dashboard operational
- [ ] MySQL slow query log enabled
- [ ] Queue monitoring active
- [ ] Backup schedule configured

---

## 📊 Release Score

| Category | Score | Max |
|----------|-------|-----|
| Code Quality | — | 10 |
| Features | — | 20 |
| Security | — | 15 |
| Performance | — | 15 |
| Database | — | 10 |
| Deployment | — | 10 |
| Documentation | — | 10 |
| Monitoring | — | 10 |
| **Total** | **—** | **100** |

---

*Release Checklist — Sprint 36E*
