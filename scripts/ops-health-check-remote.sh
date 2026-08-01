#!/usr/bin/env bash
set -euo pipefail

# Remote operational check runner for ServiceKU.
# Usage:
#   ./scripts/ops-health-check-remote.sh
#   ./scripts/ops-health-check-remote.sh kirom@192.168.1.33

TARGET="${1:-kirom@192.168.1.33}"
REMOTE_APP_DIR="${REMOTE_APP_DIR:-/home/kirom/serviceku}"
APP_CONTAINER="${APP_CONTAINER:-serviceku-app}"
QUEUE_CONTAINER="${QUEUE_CONTAINER:-serviceku-queue}"

echo "Remote target : ${TARGET}"
echo "App directory : ${REMOTE_APP_DIR}"
echo ""

echo "[1/3] Cek status container penting"
ssh -o BatchMode=yes -o ConnectTimeout=8 "${TARGET}" \
  "docker ps --filter name=${APP_CONTAINER} --filter name=${QUEUE_CONTAINER} --format 'table {{.Names}}\t{{.Status}}'"

echo ""
echo "[2/3] Jalankan health check aplikasi di container"
ssh -o BatchMode=yes -o ConnectTimeout=8 "${TARGET}" \
  "docker exec ${APP_CONTAINER} bash -lc 'cd /var/www/html && ./scripts/ops-health-check.sh'"

echo ""
echo "[3/3] Ringkasan queue failed jobs"
ssh -o BatchMode=yes -o ConnectTimeout=8 "${TARGET}" \
  "docker exec ${APP_CONTAINER} bash -lc 'cd /var/www/html && php artisan queue:failed | sed -n \"1,20p\"'"

echo ""
echo "Selesai."
