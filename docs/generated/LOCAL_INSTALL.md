# LOCAL_INSTALL.md — ServiceKU v1.0.0

## Prerequisites

- PHP 8.3+ with extensions: bcmath, gd, intl, pdo_mysql, exif, zip, mbstring, xml
- Composer 2.x
- Node.js 22+ & npm
- MySQL 8.0 or SQLite
- Redis (optional, for cache/queue)

## Step-by-Step

### 1. Clone Repository

```bash
git clone https://github.com/fathulkirom/saas-serviceku.git
cd saas-serviceku
```

### 2. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env`:
```
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# Or for MySQL:
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=serviceku_master
# DB_USERNAME=root
# DB_PASSWORD=
```

### 3. Install Dependencies

```bash
composer install
npm install
```

### 4. Database Setup

```bash
# SQLite (dev)
touch database/database.sqlite
php artisan migrate
php artisan migrate --path=database/migrations/tenant

# MySQL (optional)
php artisan migrate
```

### 5. Build Frontend

```bash
npm run dev    # Development with HMR
npm run build  # Production build
```

### 6. Start Server

```bash
php artisan serve
# or
composer run dev
```

Open http://localhost:8000

### 7. Create Admin User

```bash
php artisan db:seed --class=ProductionDemoSeeder
```

Login: owner@serviceku.test / password

### 8. Queue Worker (optional)

```bash
php artisan queue:work
```

### 9. Scheduler (optional)

```bash
php artisan schedule:work
```

## Subdomain Setup (for multi-tenant)

Add to `/etc/hosts`:
```
127.0.0.1 tenant1.serviceku.test
127.0.0.1 tenant2.serviceku.test
```

## Troubleshooting

| Issue | Fix |
|-------|-----|
| SQLite not writable | `chmod -R 777 database/` |
| Vite not loading | `npm run dev` in separate terminal |
| Permission denied | `chmod -R 775 storage bootstrap/cache` |
| Class not found | `composer dump-autoload` |
