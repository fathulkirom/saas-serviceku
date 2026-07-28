#!/bin/bash
# ==========================================
# Setup Cloudflare Tunnel untuk ServiceKU
# ==========================================
# Prasyarat: Docker sudah terinstall
# ==========================================

set -e

echo "=== ServiceKU - Cloudflare Tunnel Setup ==="
echo ""

# 1. Cek cloudflared
if ! command -v cloudflared &> /dev/null; then
    echo "Menginstall cloudflared..."
    brew install cloudflare/cloudflare/cloudflared
fi

echo "✅ cloudflared terinstall"

# 2. Login ke Cloudflare
echo ""
echo "Silakan login ke Cloudflare di browser..."
cloudflared tunnel login

# 3. Buat tunnel
TUNNEL_NAME="serviceku-tunnel"
echo ""
echo "Membuat tunnel '$TUNNEL_NAME'..."
cloudflared tunnel create $TUNNEL_NAME 2>/dev/null || echo "Tunnel sudah ada"

# 4. Dapatkan Tunnel ID
TUNNEL_ID=$(cloudflared tunnel list | grep "$TUNNEL_NAME" | awk '{print $1}')
echo "✅ Tunnel ID: $TUNNEL_ID"

# 5. Buat konfigurasi
echo ""
echo "Membuat konfigurasi tunnel..."

# Domain master
CENTRAL_DOMAIN="admin.serviceku.app"

cat > ~/.cloudflared/$TUNNEL_ID.json << EOL
{
  "tunnel": "$TUNNEL_ID",
  "credentials-file": "/home/nonroot/.cloudflared/$TUNNEL_ID.json",
  "ingress": [
    {
      "hostname": "$CENTRAL_DOMAIN",
      "service": "http://localhost:8000"
    },
    {
      "service": "http://localhost:8000"
    }
  ]
}
EOL

# 6. Route DNS
echo ""
echo "Route DNS untuk domain tenant..."
echo "Contoh: Untuk tenant 'tokoku' → tokoku.serviceku.app"
echo ""
echo "Jalankan perintah berikut untuk setiap tenant:"
echo "  cloudflared tunnel route dns $TUNNEL_NAME tokoku.serviceku.app"
echo "  cloudflared tunnel route dns $TUNNEL_NAME $CENTRAL_DOMAIN"

# 7. Info .env
echo ""
echo "=== Tambahkan ke .env ==="
echo "APP_URL=https://$CENTRAL_DOMAIN"
echo "TUNNEL_TOKEN=<token-dari-cloudflare>"

echo ""
echo "✅ Selesai! Jalankan tunnel:"
echo "  cloudflared tunnel run $TUNNEL_NAME"
echo ""
echo "Atau via Docker:"
echo "  cd docker/cloudflare && docker compose -f docker-compose.tunnel.yml up -d"
