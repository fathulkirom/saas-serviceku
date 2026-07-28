#!/bin/bash
# ==========================================
# Setup Google Drive Backup dengan rclone
# ==========================================
# Penggunaan:
#   chmod +x setup-gdrive.sh
#   ./setup-gdrive.sh
# ==========================================

set -e

echo "=== ServiceKU - Google Drive Backup Setup ==="
echo ""

# 1. Cek / Install rclone
if command -v rclone &> /dev/null; then
    echo "✅ rclone sudah terinstall: $(rclone version | head -1)"
else
    echo "Menginstall rclone..."
    if [[ "$OSTYPE" == "darwin"* ]]; then
        brew install rclone
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        curl -O https://downloads.rclone.org/rclone-current-linux-amd64.deb
        sudo dpkg -i rclone-current-linux-amd64.deb
        rm rclone-current-linux-amd64.deb
    else
        echo "❌ OS tidak dikenal. Install manual: https://rclone.org/install/"
        exit 1
    fi
    echo "✅ rclone terinstall: $(rclone version | head -1)"
fi

echo ""
echo "=========================================="
echo "  📋 Langkah Selanjutnya:"
echo "=========================================="
echo ""
echo "1. Jalankan konfigurasi rclone:"
echo "   rclone config"
echo ""
echo "2. Pilih 'n' untuk remote baru"
echo "3. Nama remote: serviceku-backup"
echo "4. Pilih 'drive' (Google Drive)"
echo "5. Ikuti petunjuk autentikasi di browser"
echo "6. Pilih folder Google Drive (biarkan kosong untuk root)"
echo "7. Selesai!"
echo ""
echo "=========================================="
echo "  🔧 Setelah Setup:"
echo "=========================================="
echo ""
echo "Buat folder khusus di Google Drive untuk backup:"
echo "   rclone mkdir serviceku-backup:/ServiceKU-Backup"
echo ""
echo "Dapatkan Folder ID dari URL browser:"
echo "   https://drive.google.com/drive/folders/xxx_FOLDER_ID_xxx"
echo ""
echo "Masukkan Folder ID di SuperAdmin → Backup → Google Drive"
echo ""
echo "Uji coba upload:"
echo "   echo 'test' > /tmp/test.txt"
echo "   rclone copy /tmp/test.txt serviceku-backup:/ServiceKU-Backup/"
echo ""
echo "✅ Selesai!"
