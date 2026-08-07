# DEPLOYMENT.md — ServiceKU v1.0.0

## Quick Start (Local Development)

```bash
git clone https://github.com/fathulkirom/saas-serviceku.git
cd saas-serviceku

# Copy environment
cp .env.example .env

# Install dependencies
composer install
npm install && npm run build

# Generate app key
php artisan key:generate

# Run migrations
php artisan migrate

# Seed demo data (optional)
php artisan db:seed --class=ProductionDemoSeeder

# Start dev server
composer run dev
```

Open http://localhost:8000

---

## Docker Deployment (Production)

```bash
# Build and start
docker compose -f docker/docker-compose.prod.yml up -d

# With demo data
SEED_DEMO=true docker compose -f docker/docker-compose.prod.yml up -d

# Check status
docker compose -f docker/docker-compose.prod.yml ps

# View logs
docker compose -f docker/docker-compose.prod.yml logs -f app

# Stop
docker compose -f docker/docker-compose.prod.yml down
```

---

## Health Check

```bash
curl http://localhost/health
```

Response:
```json
{
  "status": "healthy",
  "version": "1.0.0",
  "checks": {
    "app": {"healthy": true, "env": "production", "debug": false},
    "database": {"healthy": true, "driver": "mysql"},
    "redis": {"healthy": true},
    "queue": {"healthy": true, "message": "0 pending, 0 failed"},
    "storage": {"healthy": true},
    "disk": {"healthy": true, "message": "Free: 45.2 GB"},
    "cache": {"healthy": true}
  }
}
```

---

## Demo Accounts

| Role | Email | Password |
|------|-------|----------|
| Owner | owner@serviceku.test | password |
| CS | cs@serviceku.test | password |
| Teknisi | teknisi@serviceku.test | password |
| Kasir | kasir@serviceku.test | password |
| Gudang | gudang@serviceku.test | password |
| Finance | finance@serviceku.test | password |

---

## Environment Variables

| Variable | Default | Description |
|----------|---------|-------------|
| APP_ENV | production | Environment |
| APP_DEBUG | false | Debug mode (must be false in prod) |
| APP_URL | http://localhost | Application URL |
| DB_HOST | mysql | Database host |
| DB_DATABASE | serviceku_master | Database name |
| DB_USERNAME | serviceku | Database user |
| DB_PASSWORD | serviceku_pass | Database password |
| REDIS_HOST | redis | Redis host |
| QUEUE_CONNECTION | database | Queue driver |
| CACHE_DRIVER | redis | Cache driver |
| SESSION_DRIVER | redis | Session driver |
| SEED_DEMO | false | Auto-seed demo data |
