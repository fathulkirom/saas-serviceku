#!/bin/bash
# ==========================================
# Deploy ServiceKU ke Server Lokal
# ==========================================
# Penggunaan:
#   ./deploy.sh                  # Deploy normal
#   ./deploy.sh --skip-build     # Skip Vite build
#   ./deploy.sh --restart        # Restart containers saja
# ==========================================

set -e

RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

info()  { echo -e "${BLUE}[INFO]${NC} $1"; }
ok()    { echo -e "${GREEN}[OK]${NC} $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
err()   { echo -e "${RED}[ERROR]${NC} $1"; }

# ========== KONFIGURASI ==========
SSH_USER="kirom"
SSH_HOST="192.168.1.33"
SSH_PORT="22"
REMOTE_DIR="/home/kirom/serviceku"
LOCAL_DIR="/Users/macbook/saas"

# Port untuk aplikasi (8000 sudah dipakai kalkulator-usaha)
APP_PORT="8081"

# ========== ARGUMENTS ==========
SKIP_BUILD=false
RESTART_ONLY=false

case "${1:-}" in
    --skip-build) SKIP_BUILD=true ;;
    --restart) RESTART_ONLY=true ;;
esac

echo ""
echo "=========================================="
echo "  🚀 Deploy ServiceKU"
echo "  📡 $SSH_USER@$SSH_HOST"
echo "  📁 $REMOTE_DIR"
echo "=========================================="
echo ""

# ========== BUILD FRONTEND ==========
if [ "$RESTART_ONLY" = false ] && [ "$SKIP_BUILD" = false ]; then
    info "Building frontend assets..."
    cd "$LOCAL_DIR"
    npx vite build 2>&1 | tail -1
    ok "Frontend built!"
fi

if [ "$RESTART_ONLY" = true ]; then
    info "Restarting containers only..."
    ssh $SSH_USER@$SSH_HOST "cd $REMOTE_DIR && docker compose restart" 2>&1
    ok "Restarted!"
    exit 0
fi

# ========== COPY FILES ==========
info "Creating remote directory..."
ssh $SSH_USER@$SSH_HOST "mkdir -p $REMOTE_DIR/docker/cloudflare $REMOTE_DIR/storage/app/public $REMOTE_DIR/storage/framework/cache $REMOTE_DIR/storage/framework/sessions $REMOTE_DIR/storage/framework/views $REMOTE_DIR/storage/logs $REMOTE_DIR/bootstrap/cache"

info "Copying application files..."
rsync -avzO --progress \
    --exclude '.env' \
    --exclude '.env.*' \
    --exclude 'node_modules' \
    --exclude 'vendor' \
    --exclude '.git' \
    --exclude 'storage' \
    --exclude 'bootstrap/cache/*' \
    --exclude 'public/build' \
    --exclude 'public/hot' \
    --exclude 'database/tenant_*' \
    --exclude 'database/testing_tenant_*' \
    --exclude 'database/*.sqlite*' \
    "$LOCAL_DIR/" "$SSH_USER@$SSH_HOST:$REMOTE_DIR/" || true

info "Copying built assets..."
rsync -avzO "$LOCAL_DIR/public/build/" "$SSH_USER@$SSH_HOST:$REMOTE_DIR/public/build/" || true

info "Copying Docker configs..."
rsync -avzO "$LOCAL_DIR/docker-compose.yml" "$SSH_USER@$SSH_HOST:$REMOTE_DIR/docker-compose.yml" || true
rsync -avzO "$LOCAL_DIR/docker/" "$SSH_USER@$SSH_HOST:$REMOTE_DIR/docker/" --exclude 'mysql' || true

ok "Files copied!"

# ========== SETUP .ENV ==========
info "Setting up .env..."
ssh $SSH_USER@$SSH_HOST "
if [ ! -f $REMOTE_DIR/.env ]; then
  cat > $REMOTE_DIR/.env << 'ENVEOF'
APP_NAME=ServiceKU
APP_ENV=production
APP_DEBUG=false
APP_TIMEZONE=Asia/Jakarta
APP_KEY=
APP_URL=https://serviceku.my.id

LOG_CHANNEL=stack
LOG_STACK=daily
LOG_DAILY_DAYS=14
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=serviceku_master
DB_USERNAME=serviceku
DB_PASSWORD=serviceku_pass

SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_SECURE_COOKIE=true

CACHE_STORE=redis
QUEUE_CONNECTION=database
REDIS_HOST=serviceku-redis
REDIS_PORT=6379
REDIS_PASSWORD=null

MAIL_MAILER=log
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=notifications@serviceku.my.id
MAIL_FROM_NAME=ServiceKU

# Error monitoring (isi DSN dari dashboard Sentry)
SENTRY_DSN=
SENTRY_ENVIRONMENT=production
SENTRY_SAMPLE_RATE=1.0
ENVEOF
  echo 'Created new .env file'
else
  echo '.env file already exists. Preserving cloudflare and mail settings.'
fi
"

# ========== INSTALL COMPOSER ==========
info "Installing composer dependencies..."
ssh $SSH_USER@$SSH_HOST "cd $REMOTE_DIR && cp .env .env.backup 2>/dev/null; docker run --rm -v $REMOTE_DIR:/app composer:latest composer install --no-dev --optimize-autoloader --no-interaction" 2>&1

# ========== GENERATE APP KEY ==========
# Hanya generate jika APP_KEY kosong (jangan rotasi key yang sudah ada,
# karena akan merusak data terenkripsi & sesi pengguna).
info "Generating APP_KEY (hanya jika kosong)..."
ssh $SSH_USER@$SSH_HOST "cd $REMOTE_DIR && if grep -qE '^APP_KEY=[A-Za-z0-9]' .env; then echo 'APP_KEY sudah ada — dipertahankan'; else docker run --rm -v $REMOTE_DIR:/app composer:latest php artisan key:generate --force; fi" 2>&1

# ========== SET PERMISSIONS ==========
info "Setting permissions..."
ssh $SSH_USER@$SSH_HOST "chmod -R 775 $REMOTE_DIR/storage $REMOTE_DIR/bootstrap/cache && sudo chown -R 1000:1000 $REMOTE_DIR/storage $REMOTE_DIR/bootstrap/cache 2>/dev/null || true"

# ========== DOCKER COMPOSE ==========
info "Starting Docker containers..."
ssh $SSH_USER@$SSH_HOST "cd $REMOTE_DIR && docker compose up -d mysql redis phpmyadmin" 2>&1

info "Waiting for MySQL to be ready..."
sleep 10

# ========== START APP CONTAINER ==========
info "Starting Laravel app on port $APP_PORT..."
ssh $SSH_USER@$SSH_HOST "docker rm -f serviceku-app 2>/dev/null || true"
ssh $SSH_USER@$SSH_HOST "cd $REMOTE_DIR && docker run -d --name serviceku-app --restart unless-stopped \
    --network serviceku_serviceku-network \
    -p $APP_PORT:8080 \
    -v $REMOTE_DIR:/var/www/html \
    -e PHP_OPCACHE_ENABLE=1 \
    -e PHP_SESSION_COOKIE_SECURE=true \
    serversideup/php:8.4-fpm-nginx" 2>&1

info "Waiting for app container to boot..."
sleep 5

# ========== RUN MIGRATIONS ==========
info "Running central migrations..."
ssh $SSH_USER@$SSH_HOST "docker exec serviceku-app php artisan migrate --force" 2>&1

info "Running tenant migrations..."
ssh $SSH_USER@$SSH_HOST "docker exec serviceku-app php artisan tenants:migrate --force" 2>&1

info "Seeding database..."
ssh $SSH_USER@$SSH_HOST "docker exec serviceku-app php artisan db:seed --class=PlanSeeder --force" 2>&1
ssh $SSH_USER@$SSH_HOST "docker exec serviceku-app php artisan db:seed --class=SystemSettingSeeder --force" 2>&1

# ========== CLEAR CACHE ==========
info "Clearing Laravel cache..."
ssh $SSH_USER@$SSH_HOST "docker exec serviceku-app php artisan optimize:clear" 2>&1

# ========== START QUEUE WORKER (persistent) ==========
# Tanpa worker, job (GenerateInvoicePdf, SendInvoiceEmail dll) menumpuk di
# tabel jobs dan tidak pernah diproses. Container restart: unless-stopped.
info "Starting queue worker (persistent container)..."
ssh $SSH_USER@$SSH_HOST "cd $REMOTE_DIR && docker compose up -d queue-worker" 2>&1
ssh $SSH_USER@$SSH_HOST "docker ps --filter name=serviceku-queue --format '{{.Names}} {{.Status}}'" 2>&1

# ========== CHECK STATUS ==========
echo ""
echo "=========================================="
ok "✅ DEPLOY SELESAI!"
echo ""
echo "📡 Aplikasi: http://$SSH_HOST:$APP_PORT"
echo "📁 Lokasi: $REMOTE_DIR"
echo "🔄 Restart: ./deploy.sh --restart"
echo ""
echo "📋 Next steps:"
echo "  1. Cloudflare Tunnel (SUDAH AKTIF via systemd, tidak perlu setup lagi):"
echo "     systemctl status cloudflared-serviceku"
echo "     cat ~/.cloudflared/serviceku.yml   # serviceku.my.id -> localhost:8081"
echo "     # Domain otomatis menyajikan container serviceku-app di port 8081"
echo "  2. Setup backup:"
echo "     crontab -e"
echo "     0 3 * * * cd $REMOTE_DIR && bash backup.sh --auto"
echo "  3. Setup Google Drive:"
echo "     bash docker/cloudflare/setup-gdrive.sh"
echo "=========================================="
