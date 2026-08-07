# Production Tuning — Sprint 36D

> Complete production environment tuning guide for ServiceKU.

---

## 🐘 PHP-FPM Tuning

```ini
; www.conf
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500
```

---

## 🗄️ MySQL Tuning

```ini
# my.cnf
innodb_buffer_pool_size = 2G           # 70% of available RAM
innodb_log_file_size = 512M
innodb_flush_log_at_trx_commit = 2     # Production: 2 (slight durability trade for speed)
max_connections = 200
query_cache_type = 0                    # Disable (deprecated in MySQL 8)
slow_query_log = 1
long_query_time = 2
```

---

## 🔴 Redis Tuning

```ini
# redis.conf
maxmemory = 512mb
maxmemory-policy = allkeys-lru          # Evict least recently used keys
save 900 1                              # RDB snapshot every 15min if 1+ key changed
save 300 10
save 60 10000
```

---

## 🐳 Docker Production

```yaml
# docker-compose.prod.yml
services:
  app:
    deploy:
      replicas: 3
      resources:
        limits:
          cpus: '2'
          memory: 1G
    environment:
      - APP_DEBUG=false
      - CACHE_STORE=redis
      - QUEUE_CONNECTION=redis
```

---

## ☁️ Nginx Tuning

```nginx
# nginx.conf
gzip on;
gzip_types text/css application/javascript application/json image/svg+xml;
gzip_min_length 1000;

# Static asset caching
location /build/ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}

# API proxy
location /api/ {
    proxy_cache api_cache;
    proxy_cache_valid 200 10s;
    proxy_cache_use_stale error timeout updating;
}
```

---

## 📦 Laravel Optimization

```bash
# Run on every deploy
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
composer dump-autoload --optimize
```

---

## 🔧 Queue Worker (Supervisor)

```ini
# /etc/supervisor/conf.d/serviceku-worker.conf
[program:serviceku-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/html/artisan queue:work redis --queue=high,default,low --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
numprocs=3
```

---

## 📊 Monitoring

- **Sentry**: Error tracking + performance monitoring
- **EPOC**: Platform health, queue, cache, deployments
- **MySQL**: Slow query log
- **Redis**: INFO command for hit ratio
- **Nginx**: Access/error logs

---

*Production Tuning — Sprint 36D*
