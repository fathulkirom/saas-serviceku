# PRODUCTION_INSTALL.md — ServiceKU v1.0.0

## Server Requirements

| Resource | Minimum | Recommended |
|----------|---------|-------------|
| CPU | 2 cores | 4 cores |
| RAM | 2 GB | 4 GB |
| Disk | 10 GB | 50 GB SSD |
| OS | Ubuntu 22.04+ | Ubuntu 24.04 LTS |

## Software Stack

- Docker 24+ & Docker Compose v2
- MySQL 8.0 (via Docker)
- Redis 7 (via Docker)
- Nginx (via Docker)
- PHP 8.3 FPM (via Docker)

---

## Step 1: Clone & Configure

```bash
git clone https://github.com/fathulkirom/saas-serviceku.git /opt/serviceku
cd /opt/serviceku

cp .env.example .env
```

Edit `.env`:
```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_DATABASE=serviceku_master
DB_USERNAME=serviceku
DB_PASSWORD=<strong-password>
DB_ROOT_PASSWORD=<strong-root-password>

REDIS_HOST=redis
QUEUE_CONNECTION=database
CACHE_DRIVER=redis
SESSION_DRIVER=redis

SEED_DEMO=false
```

Generate app key:
```bash
php artisan key:generate
```

---

## Step 2: Build & Start

```bash
docker compose -f docker/docker-compose.prod.yml build
docker compose -f docker/docker-compose.prod.yml up -d
```

---

## Step 3: Verify

```bash
# Check containers
docker compose -f docker/docker-compose.prod.yml ps

# Health check
curl http://localhost/health

# View logs
docker compose -f docker/docker-compose.prod.yml logs -f app
```

---

## Step 4: SSL (with Cloudflare or Let's Encrypt)

### Cloudflare (Recommended)
1. Point DNS to your server IP
2. Enable proxy (orange cloud)
3. Set SSL mode to "Full (strict)"

### Let's Encrypt (Direct)
```bash
apt install certbot python3-certbot-nginx
certbot --nginx -d your-domain.com
```

---

## Step 5: Backup Cron

Add to crontab:
```bash
0 2 * * * cd /opt/serviceku && docker compose -f docker/docker-compose.prod.yml exec -T app php artisan backup:database
```

---

## Production Checklist

- [ ] APP_DEBUG=false
- [ ] APP_ENV=production
- [ ] Strong DB password
- [ ] SSL enabled
- [ ] Health check passing
- [ ] Backup cron configured
- [ ] Queue worker running
- [ ] Scheduler running
- [ ] OPcache enabled
- [ ] Security headers active
