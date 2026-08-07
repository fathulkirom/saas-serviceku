#!/bin/bash
# Restore tunnel pakai credential yang SUDAH ADA di ~/.cloudflared/
SSH_HOST="kirom@192.168.1.33"
REMOTE="/home/kirom/serviceku/docker/cloudflare"

echo "Cek credential..."
ssh $SSH_HOST "ls ~/.cloudflared/*.json 2>/dev/null" && echo "✅ Credential aman" || echo "❌ Credential hilang"

cat > /tmp/docker-compose.tunnel.yml << 'EOF'
version: '3.8'
services:
  cloudflared:
    image: cloudflare/cloudflared:latest
    container_name: serviceku-tunnel
    restart: unless-stopped
    command: tunnel run
    volumes:
      - ~/.cloudflared:/home/nonroot/.cloudflared
    networks:
      - serviceku-network
networks:
  serviceku-network:
    external: true
EOF

scp /tmp/docker-compose.tunnel.yml $SSH_HOST:$REMOTE/docker-compose.tunnel.yml
ssh $SSH_HOST "cd /home/kirom/serviceku && docker compose -f docker/cloudflare/docker-compose.tunnel.yml down 2>/dev/null; docker compose -f docker/cloudflare/docker-compose.tunnel.yml up -d"
echo "✅ Tunnel restored. Cek: ssh $SSH_HOST 'docker logs serviceku-tunnel'"
rm -f /tmp/docker-compose.tunnel.yml
