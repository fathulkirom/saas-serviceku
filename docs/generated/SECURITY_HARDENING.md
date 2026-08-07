# Security Hardening — Sprint 36D

> Production security hardening checklist for ServiceKU.

---

## 🔒 Hardening Checklist

### Authorization
- [ ] All controller actions use Policy or `can()` gate
- [ ] No hardcoded role names in business logic
- [ ] Tenant isolation verified (no cross-tenant data access)
- [ ] Public tracking endpoint rate-limited

### Mass Assignment
- [ ] All models have `$fillable` (whitelist) or `$guarded` (blacklist)
- [ ] No `$guarded = []` (allows mass assignment to all columns)

### Rate Limiting
- [ ] Login: 6 attempts/minute
- [ ] Register: 3 attempts/minute
- [ ] OTP: 2 attempts/minute
- [ ] API: 60 requests/minute
- [ ] Public tracking: 30 requests/minute (NEW)

### Session Security
- [ ] `secure: true` (HTTPS only)
- [ ] `http_only: true` (no JS access)
- [ ] `same_site: 'lax'`
- [ ] Session lifetime: 120 minutes (idle)

### CSRF Protection
- [ ] All POST/PUT/DELETE routes protected
- [ ] AJAX requests include `X-CSRF-TOKEN` header
- [ ] Public API routes exempted intentionally

### XSS Prevention
- [ ] Vue auto-escapes by default ✅
- [ ] `v-html` usage audited and sanitized
- [ ] CSP headers configured

### SQL Injection Prevention
- [ ] All queries use Eloquent/Query Builder (auto-parameterized) ✅
- [ ] `DB::raw()` usage audited
- [ ] `DB::select()` with user input → parameterized

### File Upload Security
- [ ] MIME type validation: `jpg, jpeg, png, webp, pdf`
- [ ] Max file size: 10MB
- [ ] File content validation (not just extension)
- [ ] Virus scan (ClamAV) for production

### Dependency Security
- [ ] `composer audit` in CI/CD pipeline
- [ ] `npm audit` in build pipeline
- [ ] Dependabot/Snyk for automated alerts

### Production Safeguards
- [ ] `APP_DEBUG=false`
- [ ] `DB::prohibitDestructiveCommands()` enabled
- [ ] `Model::preventLazyLoading()` in dev
- [ ] Maintenance mode via cache driver (not file)

---

*Security Hardening — Sprint 36D*
