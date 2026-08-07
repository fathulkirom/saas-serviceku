#!/bin/bash
# ============================================
# ServiceKU — Container Entrypoint
# ============================================
set -e

echo "========================================="
echo " ServiceKU v1.0.0 — Starting..."
echo "========================================="

# Wait for MySQL
echo "[1/6] Waiting for MySQL..."
until php -r "new PDO('mysql:host=${DB_HOST:-mysql};port=${DB_PORT:-3306};dbname=${DB_DATABASE:-serviceku_master}', '${DB_USERNAME:-serviceku}', '${DB_PASSWORD:-serviceku_pass}');" 2>/dev/null; do
    echo "  Waiting..."
    sleep 2
done
echo "  MySQL is ready."

# Storage setup
echo "[2/6] Setting up storage..."
mkdir -p storage/framework/{cache,sessions,testing,views}
mkdir -p storage/logs
mkdir -p bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
php artisan storage:link --force 2>/dev/null || true

# Run migrations
echo "[3/6] Running migrations..."
php artisan migrate --force --no-interaction

# Cache optimization
echo "[4/6] Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Seed demo data (if SEED_DEMO=true)
if [ "${SEED_DEMO:-false}" = "true" ]; then
    echo "[5/6] Seeding demo data..."
    php artisan db:seed --class=ProductionDemoSeeder --force
else
    echo "[5/6] Skipping demo seed (set SEED_DEMO=true to enable)."
fi

echo "[6/6] Starting services..."

# Start Supervisor (PHP-FPM + Nginx + Queue + Scheduler)
exec /usr/bin/supervisord -n -c /etc/supervisor/supervisord.conf
