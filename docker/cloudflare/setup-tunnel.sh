#!/bin/bash
# ==========================================
# Setup Cloudflare Tunnel (token-based) untuk ServiceKU
# ==========================================
# Cara pakai:
#   ./setup-tunnel.sh [domain]
# Contoh:
#   ./setup-tunnel.sh serviceku.my.id
#
# Prasyarat:
#   - Tunnel sudah dibuat di dashboard Cloudflare (remotely managed)
#   - Punya TUNNEL_TOKEN dari dashboard
# ==========================================

set -e

DIR="$(cd "$(dirname "$0")" && pwd)"
DOMAIN="${1:-serviceku.my.id}"

echo "=========================================="
echo "  🚀 ServiceKU - Cloudflare Tunnel Setup"
echo "  🌐 Domain: $DOMAIN"
echo "=========================================="
echo ""

# 1. Cek docker
if ! command -v docker &> /dev/null; then
    echo "❌ Docker tidak terinstall."
    exit 1
fi

# 2. Minta token (diketik langsung di terminal, TIDAK masuk history)
if [ -n "$TUNNEL_TOKEN" ]; then
    echo "✅ Menggunakan TUNNEL_TOKEN dari environment."
else
    read -s -p "Paste TUNNEL_TOKEN dari dashboard Cloudflare: " TOKEN
    echo ""
    if [ -z "$TOKEN" ]; then
        echo "❌ Token kosong. Batalkan."
        exit 1
    fi
    TUNNEL_TOKEN="$TOKEN"
fi

# 3. Simpan ke .env (chmod 600 karena berisi secret)
cat > "$DIR/.env" << EOF
TUNNEL_TOKEN=$TUNNEL_TOKEN
EOF
chmod 600 "$DIR/.env"
echo "✅ Token disimpan di $DIR/.env (mode 600)"

# 4. Jalankan tunnel container
echo ""
echo "Menjalankan tunnel container..."
cd "$DIR"
docker compose --env-file .env -f docker-compose.tunnel.yml up -d

echo ""
echo "=========================================="
echo "✅ Tunnel '$DOMAIN' berjalan!"
echo ""
echo "⚠️  PASTIKAN di dashboard Cloudflare (tunnel -> Public Hostnames):"
echo "   Hostname : $DOMAIN"
echo "   Service  : HTTP -> serviceku-app:8080"
echo "   (jika gagal, coba HTTP -> 192.168.1.33:8081)"
echo "=========================================="
