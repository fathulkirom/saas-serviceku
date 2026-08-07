# LOCAL SERVER REPORT — ServiceKU Diagnostic

**Date**: 2026-08-02  
**Server**: 192.168.1.33 (kirom)  
**Auditor**: GitHub Copilot (DeepSeek V4 Pro)  
**Scope**: Audit Only — No changes, no fixes, no restarts  

---

## SERVER INFORMATION

| Item | Value |
|------|-------|
| Hostname | kirom |
| OS | Ubuntu 24.04 (Linux 6.8.0-136-generic x86_64) |
| CPU Cores | 4 |
| RAM | 7.6 GB total / 1.9 GB used / 5.8 GB available |
| Swap | 4.0 GB (0 used) |
| Uptime | 4 days 6 hours |
| Load Average | 0.29 / 0.22 / 0.15 |
| Disk | /dev/nvme0n1p2 116G total / 88G used / 23G free (80%) |

---

## PROJECT LOCATION

| Item | Value |
|------|-------|
| Path | `/home/kirom/serviceku` |
| Owner | kirom:kirom |
| Permission | 755 (drwxr-xr-x) |
| Size | 348 MB |
| Files | 48,933 |

---

## DOCKER STATUS

| Item | Value |
|------|-------|
| Docker Version | 29.6.2 |
| Docker Compose | v5.3.1 |
| Images | 17 total (8.18 GB) |
| Containers | 18 total (14 active) |
| Volumes | 7 local volumes (316 MB used) |
| Build Cache | **76.26 GB (71.95 GB reclaimable)** ⚠️ |
| Networks | 11 (1 for serviceku: `serviceku_serviceku-network`) |

**Docker Images (ServiceKU only)**:

| Image | Size |
|-------|------|
| serversideup/php:8.4-fpm-nginx | 773 MB |
| mysql:8.0 | 1.1 GB |
| redis:7-alpine | 57.8 MB |
| phpmyadmin:latest | 821 MB |

**Docker Volumes (ServiceKU)**:

| Volume | Driver |
|--------|--------|
| serviceku_mysql_data | local |
| serviceku_redis_data | local |

---

## CONTAINER STATUS

| Container | Image | Status | Health | Restarts | Uptime | Port |
|-----------|-------|--------|--------|----------|--------|------|
| serviceku-app | serversideup/php:8.4-fpm-nginx | Running | healthy | 0 | 20 hours | 8081→8080 |
| serviceku-queue | serversideup/php:8.4-fpm-nginx | Running | healthy | 0 | 27 hours | — |
| serviceku-mysql | mysql:8.0 | Running | healthy | 0 | 46 hours | 3306 |
| serviceku-redis | redis:7-alpine | Running | healthy | 0 | 4.3 days | 6379 |
| serviceku-phpmyadmin | phpmyadmin:latest | Running | — | 0 | 4 days | 8080 |

---

## LARAVEL STATUS

| Item | Value |
|------|-------|
| PHP Version | 8.4.23 (NTS, Zend Engine 4.4.23) |
| Composer | 2.10.2 |
| OPcache | Enabled |
| PHP Memory Limit | 256M |
| APP_ENV (.env) | production |
| APP_DEBUG (.env) | false |
| APP_URL | https://serviceku.my.id |
| APP_KEY | Set ✅ |
| DB_CONNECTION | mysql |
| DB_HOST | mysql |
| SESSION_DRIVER | redis |
| QUEUE_CONNECTION | database |
| CACHE_STORE | redis |
| REDIS_HOST | serviceku-redis |

### Artisan Commands Status

| Command | Result |
|---------|--------|
| `php -v` | ✅ PHP 8.4.23 |
| `composer --version` | ✅ Composer 2.10.2 |
| `php artisan about` | ❌ Fatal Error: Memory exhausted (256MB) |
| `php artisan route:list` | ❌ Fatal Error: Memory exhausted (256MB) |
| `php artisan optimize` | ❌ Fatal Error: Memory exhausted (256MB) |
| `php artisan storage:link` | ❌ Fatal Error: Memory exhausted (256MB) |
| `php artisan migrate:status` | ❌ Fatal Error: Memory exhausted (256MB) |

> ⚠️ **CRITICAL**: PHP Fatal Error `Allowed memory size of 268435456 bytes exhausted` prevents ALL artisan commands from executing. Root cause is `App\Models\Tenant\EventLog` model triggering infinite recursion/memory leak during Laravel bootstrap.

### Docker Compose vs Container Mismatch ⚠️

| Setting | docker-compose.yml | Running Container |
|---------|-------------------|-------------------|
| Port | 8000:8080 | **8081**:8080 |
| APP_ENV | local | *(not set in container)* |
| APP_DEBUG | true | *(not set in container)* |

> The docker-compose.yml was modified AFTER container start. Container was NOT recreated. The `.env` file overrides runtime behavior (APP_ENV=production).

---

## FRONTEND STATUS

| Item | Value |
|------|-------|
| Node.js | ❌ Not installed (host & container) |
| npm | ❌ Not installed |
| node_modules | ❌ Not present |
| `public/build/` | ✅ Exists |
| `manifest.json` | ✅ 1,481 entries |
| Assets | ✅ 3,373 files in `public/build/assets/` |
| Vite Version | ^6.0.11 (in package.json) |
| PWA Plugin | vite-plugin-pwa ^1.3.0 |
| Build Type | **Production Build** (pre-built) |
| Dev Server (Vite) | ❌ Not running (no Node) |
| HTTP Response | ❌ **500 Internal Server Error** |

> Frontend assets are properly built and deployed. No runtime Node.js is needed (production build). But the app returns HTTP 500 due to backend memory exhaustion.

---

## DATABASE STATUS

| Item | Value |
|------|-------|
| MySQL Version | 8.0.46 (Community Server) |
| Charset | utf8mb4 |
| Database | serviceku_master |
| Size | 0.66 MB |
| Tables | 22 |
| Uptime | 1 day 22 hours |
| Slow Queries | 0 ✅ |
| Queries/sec | 2.12 |
| Connections | Localhost via UNIX socket |

### Latest Migrations

| Migration | Batch |
|-----------|-------|
| 2026_07_31_000001_create_personal_access_tokens_table | 9 |
| 2026_07_27_044406_create_google_drive_tokens_table | 8 |
| 2026_07_20_000001_add_tenant_id_to_vouchers_table | 7 |

---

## REDIS STATUS

| Item | Value |
|------|-------|
| Version | 7.4.9 |
| Uptime | 4.3 days |
| Used Memory | 1.28 MB |
| Peak Memory | 2.28 MB |
| Max Memory | Unlimited (0B) |
| **Keys** | **0** ⚠️ |
| Connection | Accessible (ping OK) |

> ⚠️ **Redis has 0 keys.** Session driver is set to `redis` but no session data is stored. This is consistent with the app being unable to complete requests.

---

## QUEUE STATUS

| Item | Value |
|------|-------|
| Queue Worker | Running (healthy, 27h uptime) |
| Queue Connection | database |
| Failed Jobs | 0 |
| Pending Jobs | 0 |
| Worker Command | `php artisan queue:work --sleep=3 --tries=3 --timeout=300` |
| Healthcheck | `pgrep -f 'artisan queue:work'` |

> Queue worker is running but inherits the same memory exhaustion issue. New jobs that trigger EventLog model processing will also fail.

---

## STORAGE STATUS

| Item | Value |
|------|-------|
| storage/ | 775 drwxrwxr-x kirom:www-data |
| storage/logs/ | ✅ Writable (www-data) |
| laravel.log | **12.88 MB** (93,119 lines) ⚠️ |
| bootstrap/cache/ | ✅ Writable (root-owned files) |
| `public/storage` | ❌ **BROKEN SYMLINK** → `/Users/macbook/saas/storage/app/public` |
| Disk Free | 23 GB (80% full) |

> ❌ **CRITICAL**: `public/storage` symlink points to macOS dev path `/Users/macbook/saas/storage/app/public` — completely invalid on Linux. All uploaded files, tenant avatars, and storage assets will fail to load.

---

## NGINX STATUS

| Item | Value |
|------|-------|
| Image | serversideup/php:8.4-fpm-nginx (built-in nginx) |
| HTTP Port | 8080 (container) → 8081 (host) |
| HTTPS Port | 8443 (container) |
| Webroot | `/var/www/html/public` |
| PHP-FPM | Running (127.0.0.1:9000) |
| Server Name | `_` (catch-all) |
| access.log | **0 bytes** (empty) |
| error.log | **0 bytes** (empty) |
| Healthcheck | `/healthcheck` endpoint |

> Nginx logs are empty — access logging is disabled in the serversideup template (`access_log off`). Error log is also empty (no nginx-level errors).

---

## CRON / SCHEDULER

| Item | Value |
|------|-------|
| Laravel Scheduler | `* * * * * docker exec serviceku-app php artisan schedule:run` |
| Backup Cron | `0 3 * * * /home/kirom/serviceku/backup.sh --auto` |
| Container Cron | Standard system cron only (no Laravel cron inside container) |

> ⚠️ Scheduler runs every minute but `php artisan` commands fail with memory exhaustion. Scheduled tasks (subscription checks, tenant cleanup, etc.) are NOT executing successfully.

---

## PERFORMANCE

| Metric | Value |
|--------|-------|
| CPU Load | 0.29 (low) |
| Memory Usage | 25% (1.9/7.6 GB) |
| PHP Memory Limit | 256M |
| OPcache | Enabled |
| MySQL Slow Queries | 0 |
| Docker Build Cache | 76.26 GB (wasted) |

---

## ERROR FOUND

### 🔴 CRITICAL

| # | Error | Impact |
|---|-------|--------|
| 1 | **PHP Memory Exhaustion (256MB)** — 82 occurrences in laravel.log. `App\Models\Tenant\EventLog` model causes infinite recursion during Laravel bootstrap | ALL pages return HTTP 500. All artisan commands fail. Scheduler fails. App is DOWN. |
| 2 | **`public/storage` broken symlink** — Points to `/Users/macbook/saas/storage/app/public` (macOS path) | All uploaded files, tenant images, and storage assets return 404 |
| 3 | **Docker Compose / Container mismatch** — `.yml` says port 8000 & `APP_ENV=local`, but running container uses port 8081 & has no env vars | Stale container running old configuration |

### 🟡 WARNING

| # | Warning | Detail |
|---|---------|--------|
| 1 | `EventLog::scopeAuditTrail()` PHP 8.4 deprecation | `string $entityType = null` should be `?string $entityType = null` |
| 2 | `Undefined array key "App\Models\Tenant\EventLog"` | 28,600+ WARNING entries in laravel.log |
| 3 | Redis has 0 keys | Session driver is redis, but no data stored |
| 4 | laravel.log is 12.88 MB | 93,119 lines, mostly duplicate warnings |
| 5 | `node_modules` missing | Cannot rebuild frontend without Node.js |
| 6 | Node.js not installed | Neither host nor container has Node |
| 7 | Docker build cache 76 GB | 71.95 GB reclaimable — wasting disk space |
| 8 | Disk 80% full | 23 GB free of 116 GB |
| 9 | `bootstrap/cache/packages.php` owned by root | Permission inconsistency |
| 10 | MySQL deprecated config warnings | `--skip-host-cache`, `innodb_log_file_size` deprecated in 8.0 |

### 🟢 INFO

| # | Note |
|---|------|
| 1 | MySQL, Redis, Queue containers all healthy |
| 2 | No failed jobs in queue |
| 3 | No MySQL slow queries |
| 4 | Frontend production build exists and complete (3,373 assets) |
| 5 | Vite manifest.json valid (1,481 entries) |
| 6 | No SQLSTATE errors in logs |
| 7 | OPcache enabled |
| 8 | Backup cron configured (daily 3 AM) |

---

## WARNING

> ⚠️ **The application is effectively DOWN.** Every HTTP request returns HTTP 500. The root cause is PHP memory exhaustion triggered by the `EventLog` model during Laravel's bootstrap/auto-discovery phase. The `EventLog` model has a PHP 8.4 deprecation (`implicitly nullable parameter`) and appears to cause an infinite loop during model reflection/binding in Laravel's container, exhausting the 256MB memory limit on every request.

---

## RECOMMENDATION

### Immediate (to restore service)

1. **Fix EventLog model** — Change line 53 from `string $entityType = null` to `?string $entityType = null` to resolve PHP 8.4 deprecation that triggers memory exhaustion
2. **Increase PHP memory limit** — Temporarily set `memory_limit=512M` via `.env` or php.ini while investigating root cause
3. **Fix `public/storage` symlink** — Recreate pointing to `/home/kirom/serviceku/storage/app/public`
4. **Recreate app container** — Run `docker compose up -d app` to apply current docker-compose.yml settings

### Short-term

5. Install Node.js on host for frontend rebuild capability
6. Run `docker system prune --all --volumes` to reclaim 76 GB build cache (use with caution)
7. Clear laravel.log and set up log rotation
8. Fix `bootstrap/cache/` ownership to www-data
9. Update MySQL config to remove deprecated directives

### Monitoring

10. Set up disk usage alerts (80% threshold)
11. Enable PHP error reporting to Sentry or similar
12. Configure nginx access log for traffic monitoring

---

## VERDICT

# NOT READY

**Reason**: Application returns HTTP 500 on all requests due to PHP memory exhaustion. The `EventLog` model triggers an infinite recursion/memory leak during Laravel bootstrap. The `public/storage` symlink is also broken, preventing any file access. Docker container configuration is stale and out of sync with docker-compose.yml. Application is effectively **offline** for end users.

---


