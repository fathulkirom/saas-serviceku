#!/usr/bin/env bash
set -u

# ServiceKU operational preflight checker for trial/staging environments.
# This script is safe to run repeatedly and only reads configuration/runtime state.

GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

PASS_COUNT=0
WARN_COUNT=0
FAIL_COUNT=0

ACTION_ITEMS=""

ok() {
  PASS_COUNT=$((PASS_COUNT + 1))
  echo -e "${GREEN}[PASS]${NC} $1"
}

warn() {
  WARN_COUNT=$((WARN_COUNT + 1))
  echo -e "${YELLOW}[WARN]${NC} $1"
}

fail() {
  FAIL_COUNT=$((FAIL_COUNT + 1))
  echo -e "${RED}[FAIL]${NC} $1"
}

add_action() {
  if [[ -z "$ACTION_ITEMS" ]]; then
    ACTION_ITEMS="$1"
  else
    ACTION_ITEMS+=$'\n'$1
  fi
}

read_env() {
  local key="$1"
  if [[ ! -f .env ]]; then
    echo ""
    return 0
  fi

  # Read KEY=value lines while ignoring comments and preserving dots/slashes.
  local line
  line=$(grep -E "^${key}=" .env | tail -n 1 || true)
  line="${line#*=}"
  line="${line%\"}"
  line="${line#\"}"
  echo "$line"
}

check_command() {
  local cmd="$1"
  if command -v "$cmd" >/dev/null 2>&1; then
    ok "Command tersedia: $cmd"
  else
    fail "Command tidak tersedia: $cmd"
  fi
}

check_url() {
  local url="$1"
  local label="$2"

  if [[ -z "$url" ]]; then
    warn "$label belum dikonfigurasi"
    return 0
  fi

  local code
  code=$(curl -sS -o /dev/null -m 12 -w "%{http_code}" "$url" 2>/dev/null || true)
  if [[ "$code" == "200" || "$code" == "301" || "$code" == "302" ]]; then
    ok "$label akses OK ($code): $url"
  elif [[ -z "$code" || "$code" == "000" ]]; then
    fail "$label tidak bisa diakses: $url"
  else
    warn "$label merespons status $code: $url"
  fi
}

echo "ServiceKU Ops Health Check"
echo "Waktu: $(date '+%Y-%m-%d %H:%M:%S')"
echo "Lokasi: $(pwd)"
echo ""

if [[ ! -f artisan ]]; then
  fail "File artisan tidak ditemukan. Jalankan dari root project Laravel."
  echo ""
  echo "Ringkasan: PASS=$PASS_COUNT WARN=$WARN_COUNT FAIL=$FAIL_COUNT"
  exit 1
fi

check_command "php"
check_command "curl"

APP_URL="$(read_env APP_URL)"
CENTRAL_DOMAIN="$(read_env CENTRAL_DOMAIN)"
ADMIN_DOMAIN="$(read_env ADMIN_DOMAIN)"
QUEUE_CONNECTION="$(read_env QUEUE_CONNECTION)"
MAIL_MAILER="$(read_env MAIL_MAILER)"
MAIL_HOST="$(read_env MAIL_HOST)"
MAIL_PORT="$(read_env MAIL_PORT)"
SESSION_DOMAIN="$(read_env SESSION_DOMAIN)"
TENANT_SMOKE_DOMAIN="$(read_env TENANT_SMOKE_DOMAIN)"

# Config checks
if [[ "$APP_URL" == https://* ]]; then
  ok "APP_URL sudah HTTPS ($APP_URL)"
else
  warn "APP_URL belum HTTPS ($APP_URL). Untuk publik sebaiknya pakai https://domain"
  add_action "Set APP_URL=https://domain-publik di .env lalu jalankan: php artisan optimize:clear && php artisan config:cache"
fi

if [[ "$MAIL_MAILER" == "smtp" ]]; then
  ok "MAIL_MAILER sudah smtp"
else
  fail "MAIL_MAILER saat ini '$MAIL_MAILER' (seharusnya smtp untuk kirim email nyata)"
  add_action "Ubah MAIL_MAILER=smtp, isi SMTP Brevo, lalu sync ulang config Laravel"
fi

if [[ -n "$MAIL_HOST" && -n "$MAIL_PORT" ]]; then
  ok "MAIL_HOST/MAIL_PORT terisi ($MAIL_HOST:$MAIL_PORT)"
else
  fail "MAIL_HOST atau MAIL_PORT belum terisi"
  add_action "Isi MAIL_HOST=smtp-relay.brevo.com dan MAIL_PORT=587 (atau 2525 bila 587 diblokir)"
fi

if [[ "$QUEUE_CONNECTION" == "database" || "$QUEUE_CONNECTION" == "redis" ]]; then
  ok "QUEUE_CONNECTION siap async ($QUEUE_CONNECTION)"
elif [[ "$QUEUE_CONNECTION" == "sync" ]]; then
  fail "QUEUE_CONNECTION masih sync (job tidak diproses worker)"
  add_action "Set QUEUE_CONNECTION=database lalu jalankan worker persistent (systemd/supervisor)"
else
  warn "QUEUE_CONNECTION tidak umum: '$QUEUE_CONNECTION'"
  add_action "Pastikan queue driver valid dan worker aktif untuk driver tersebut"
fi

if [[ -n "$SESSION_DOMAIN" && "$SESSION_DOMAIN" != "null" ]]; then
  ok "SESSION_DOMAIN terisi ($SESSION_DOMAIN)"
else
  warn "SESSION_DOMAIN belum di-set (disarankan .domain-utama untuk multi-subdomain)"
  add_action "Set SESSION_DOMAIN=.domain-utama agar login lintas subdomain konsisten"
fi

echo ""

echo "Cek endpoint publik"
if [[ -n "$CENTRAL_DOMAIN" ]]; then
  check_url "https://$CENTRAL_DOMAIN" "Central domain"
else
  warn "CENTRAL_DOMAIN belum di-set"
  add_action "Isi CENTRAL_DOMAIN=serviceku.my.id (atau domain utama kamu)"
fi

if [[ -n "$ADMIN_DOMAIN" ]]; then
  check_url "https://$ADMIN_DOMAIN" "Admin domain"
else
  warn "ADMIN_DOMAIN belum di-set"
  add_action "Isi ADMIN_DOMAIN=admin.serviceku.my.id (atau admin domain kamu)"
fi

if [[ -n "$TENANT_SMOKE_DOMAIN" ]]; then
  check_url "https://$TENANT_SMOKE_DOMAIN/login" "Tenant smoke domain"
else
  warn "TENANT_SMOKE_DOMAIN kosong. Tambahkan di .env untuk uji tenant otomatis."
  add_action "Isi TENANT_SMOKE_DOMAIN dengan satu subdomain tenant aktif untuk smoke test"
fi

echo ""
echo "Cek queue & backup"

FAILED_OUTPUT=$(php artisan queue:failed 2>&1 || true)
if echo "$FAILED_OUTPUT" | grep -qi "No failed jobs"; then
  ok "Tidak ada failed jobs"
elif echo "$FAILED_OUTPUT" | grep -qi "failed_jobs"; then
  warn "Ada failed jobs, cek detail: php artisan queue:failed"
  add_action "Jalankan php artisan queue:failed lalu retry/flush sesuai kebijakan"
else
  warn "Tidak bisa memastikan failed jobs (cek manual): php artisan queue:failed"
  add_action "Validasi manual failed job dengan php artisan queue:failed"
fi

BACKUP_DIR="storage/backups"
if [[ -d "$BACKUP_DIR" ]]; then
  latest_backup=$(find "$BACKUP_DIR" -type f -print 2>/dev/null | head -n 1 || true)
  if [[ -n "$latest_backup" ]]; then
    ok "Folder backup ada dan berisi file"
  else
    warn "Folder backup ada tapi belum ada file"
    add_action "Pastikan cron backup berjalan dan tulis ke storage/backups"
  fi
else
  warn "Folder backup tidak ditemukan di $BACKUP_DIR"
  add_action "Buat atau sesuaikan path backup, lalu verifikasi job backup harian"
fi

echo ""
echo "Ringkasan: PASS=$PASS_COUNT WARN=$WARN_COUNT FAIL=$FAIL_COUNT"

if [[ -n "$ACTION_ITEMS" ]]; then
  echo ""
  echo "Remediasi cepat:"
  i=1
  while IFS= read -r item; do
    printf "%d. %s\n" "$i" "$item"
    i=$((i + 1))
  done <<< "$ACTION_ITEMS"
fi

if [[ $FAIL_COUNT -gt 0 ]]; then
  echo -e "Status: ${RED}NO-GO${NC}"
  echo "Saran: selesaikan semua FAIL, lalu jalankan ulang script ini."
  exit 2
fi

if [[ $WARN_COUNT -gt 0 ]]; then
  echo -e "Status: ${YELLOW}GO DENGAN CATATAN${NC}"
  echo "Saran: operasional boleh lanjut, tapi tindak lanjuti warning hari ini."
  exit 0
fi

echo -e "Status: ${GREEN}GO${NC}"
echo "Saran: lanjutkan perbaikan aplikasi secara bertahap."
exit 0
