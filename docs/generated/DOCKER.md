# DOCKER.md — ServiceKU v1.0.0

## Architecture

```
┌─────────────────────────────────────────┐
│              Docker Host                 │
│                                          │
│  ┌──────────┐  ┌──────────┐             │
│  │  MySQL   │  │  Redis   │             │
│  │  :3306   │  │  :6379   │             │
│  └────┬─────┘  └────┬─────┘             │
│       │              │                   │
│  ┌────┴──────────────┴─────┐             │
│  │    ServiceKU App :80    │             │
│  │  ┌────────────────────┐ │             │
│  │  │ Nginx              │ │             │
│  │  │ PHP-FPM 8.3        │ │             │
│  │  │ Queue Worker ×2    │ │             │
│  │  │ Scheduler          │ │             │
│  │  │ Supervisor         │ │             │
│  │  └────────────────────┘ │             │
│  └─────────────────────────┘             │
└─────────────────────────────────────────┘
```

## Container Details

| Service | Image | Port | Purpose |
|---------|-------|------|---------|
| app | serviceku:latest (built) | 80 | Nginx + PHP-FPM + Queue + Scheduler |
| mysql | mysql:8.0 | 3306 | Central + tenant databases |
| redis | redis:7-alpine | 6379 | Cache + session + queue driver |

## Dockerfile Stages

### Stage 1: Frontend
- Node 22 Alpine
- npm ci → npm run build (Vite)
- Output: `public/build/`

### Stage 2: Backend
- PHP 8.3 FPM
- Nginx (reverse proxy)
- Supervisor (process manager)
- Composer install --no-dev
- OPcache enabled
- Redis extension

## Supervisor Programs

| Program | Command | Instances |
|---------|---------|-----------|
| php-fpm | php-fpm | 1 |
| nginx | nginx | 1 |
| queue-worker | artisan queue:work | 2 |
| scheduler | artisan schedule:work | 1 |

## Build & Run

```bash
# Build image
docker build -f docker/Dockerfile.prod -t serviceku:latest .

# Run with compose
docker compose -f docker/docker-compose.prod.yml up -d

# Rebuild after code changes
docker compose -f docker/docker-compose.prod.yml build app
docker compose -f docker/docker-compose.prod.yml up -d app
```
