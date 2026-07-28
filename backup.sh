#!/bin/bash
# ==========================================
# Backup Database & Storage ServiceKU
# Menyimpan backup ke HDD (1TB)
# ==========================================
# Penggunaan:
#   chmod +x backup.sh
#   ./backup.sh                    # Backup sekali
#   ./backup.sh --auto             # Backup tanpa konfirmasi
#   ./backup.sh --restore FILE     # Restore dari file backup
#
# Cron (setiap jam 3 pagi):
#   0 3 * * * /path/to/backup.sh --auto >> /var/log/serviceku-backup.log 2>&1
# ==========================================

set -e

# ========== KONFIGURASI ==========
# Ubah sesuai direktori HDD kamu
BACKUP_DIR="/Volumes/HDD/Backup/ServiceKU"
# Alternatif: BACKUP_DIR="/mnt/hdd/Backup/ServiceKU"

# Konfigurasi Database (baca dari environment, fallback untuk local)
DB_HOST="${DB_HOST:-127.0.0.1}"
DB_PORT="${DB_PORT:-3306}"
DB_USER="${DB_USERNAME:-serviceku}"
DB_PASS="${DB_PASSWORD:-serviceku_pass}"
DB_MASTER="${DB_DATABASE:-serviceku_master}"

# Docker container name
MYSQL_CONTAINER="serviceku-mysql"

# Lokasi project
PROJECT_DIR="/Users/macbook/saas"

# Berapa hari backup disimpan
RETENTION_DAYS=30

# ========== FUNGSI ==========
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

info()  { echo -e "${BLUE}[INFO]${NC} $1"; }
ok()    { echo -e "${GREEN}[OK]${NC} $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $1"; }
err()   { echo -e "${RED}[ERROR]${NC} $1"; }

# ========== CEK PRASYARAT ==========
check_prerequisites() {
    if [ ! -d "$BACKUP_DIR" ]; then
        warn "Direktori $BACKUP_DIR tidak ditemukan."
        echo "Membuat direktori..."
        mkdir -p "$BACKUP_DIR" || {
            err "Gagal membuat direktori. Cek apakah HDD sudah ter-mount."
            echo ""
            echo "Cek mount point HDD:"
            echo "  ls /Volumes/   (macOS)"
            echo "  ls /mnt/       (Linux)"
            exit 1
        }
    fi

    # Cek Docker berjalan
    docker ps > /dev/null 2>&1 || {
        err "Docker tidak berjalan. Jalankan Docker terlebih dahulu."
        exit 1
    }

    # Cek container MySQL
    docker exec $MYSQL_CONTAINER mysqladmin ping -u"$DB_USER" -p"$DB_PASS" --silent > /dev/null 2>&1 || {
        err "Container MySQL ($MYSQL_CONTAINER) tidak merespons."
        exit 1
    }

    ok "Semua prasyarat terpenuhi"
}

# ========== BACKUP DATABASE ==========
backup_database() {
    local timestamp=$(date +"%Y-%m-%d_%H-%M-%S")
    local db_dir="$BACKUP_DIR/databases"
    mkdir -p "$db_dir"

    info "Memulai backup database..."

    # 1. Backup master database
    info "  Backup master database..."
    docker exec $MYSQL_CONTAINER mysqldump \
        -u"$DB_USER" -p"$DB_PASS" \
        --routines --triggers --events \
        --single-transaction \
        "$DB_MASTER" > "$db_dir/master_$timestamp.sql" \
        2>/dev/null

    # Kompres
    gzip -f "$db_dir/master_$timestamp.sql"
    ok "  Master database selesai: master_$timestamp.sql.gz"

    # 2. Backup semua tenant databases
    info "  Mencari database tenant..."
    TENANTS=$(docker exec $MYSQL_CONTAINER mysql \
        -u"$DB_USER" -p"$DB_PASS" \
        -N -e "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME LIKE 'tenant_%';" \
        2>/dev/null)

    TENANT_COUNT=0
    for tenant_db in $TENANTS; do
        info "  Backup tenant: $tenant_db ..."
        docker exec $MYSQL_CONTAINER mysqldump \
            -u"$DB_USER" -p"$DB_PASS" \
            --routines --triggers --events \
            --single-transaction \
            "$tenant_db" > "$db_dir/${tenant_db}_$timestamp.sql" \
            2>/dev/null
        gzip -f "$db_dir/${tenant_db}_$timestamp.sql"
        TENANT_COUNT=$((TENANT_COUNT + 1))
    done

    ok "  $TENANT_COUNT database tenant selesai"

    # 3. Buat manifest
    local manifest="$db_dir/backup_$timestamp.manifest"
    echo "Backup ServiceKU" > "$manifest"
    echo "Timestamp: $timestamp" >> "$manifest"
    echo "Master DB: $DB_MASTER" >> "$manifest"
    echo "Tenant DBs: $TENANT_COUNT" >> "$manifest"
    echo "Files:" >> "$manifest"
    ls -lh "$db_dir/"*"$timestamp"* 2>/dev/null >> "$manifest"

    ok "Backup database selesai! ($TENANT_COUNT tenant)"
}

# ========== BACKUP STORAGE ==========
backup_storage() {
    local timestamp=$(date +"%Y-%m-%d_%H-%M-%S")
    local storage_dir="$BACKUP_DIR/storage"
    mkdir -p "$storage_dir"

    info "Memulai backup storage..."

    # Backup folder storage (logo, uploads, dll)
    if [ -d "$PROJECT_DIR/storage/app" ]; then
        tar -czf "$storage_dir/storage_$timestamp.tar.gz" \
            -C "$PROJECT_DIR" \
            storage/app/public \
            storage/app/logos \
            storage/framework \
            2>/dev/null || true
        ok "  Storage selesai: storage_$timestamp.tar.gz"
    else
        warn "  Folder storage tidak ditemukan, dilewati"
    fi

    # Backup .env
    if [ -f "$PROJECT_DIR/.env" ]; then
        cp "$PROJECT_DIR/.env" "$storage_dir/env_$timestamp.backup"
        ok "  File .env di-backup"
    fi

    # Backup docker volumes (MySQL data)
    info "  Backup Docker volume MySQL..."
    docker run --rm \
        -v mysql_data:/source \
        -v "$storage_dir:/backup" \
        alpine tar -czf "/backup/mysql_data_$timestamp.tar.gz" \
        -C /source . \
        2>/dev/null || warn "  Gagal backup Docker volume (non-kritis)"
}

# ========== CLEANUP BACKUP LAMA ==========
cleanup_old_backups() {
    info "Membersihkan backup lebih dari $RETENTION_DAYS hari..."

    local deleted=0
    deleted=$((deleted + $(find "$BACKUP_DIR/databases" -name "*.sql.gz" -mtime +$RETENTION_DAYS -delete -print | wc -l)))
    deleted=$((deleted + $(find "$BACKUP_DIR/storage" -name "*.tar.gz" -mtime +$RETENTION_DAYS -delete -print | wc -l)))
    deleted=$((deleted + $(find "$BACKUP_DIR/storage" -name "*.backup" -mtime +$RETENTION_DAYS -delete -print | wc -l)))

    if [ "$deleted" -gt 0 ]; then
        ok "  $deleted file backup lama dihapus"
    else
        info "  Tidak ada file lama yang perlu dihapus"
    fi
}

# ========== RESTORE DATABASE ==========
restore_backup() {
    local backup_file="$1"

    if [ ! -f "$backup_file" ]; then
        err "File backup tidak ditemukan: $backup_file"
        echo ""
        echo "File tersedia di:"
        ls -lh "$BACKUP_DIR/databases/" 2>/dev/null | head -20
        exit 1
    fi

    warn "⚠️  RESTORE AKAN MENIMPA DATA YANG ADA!"
    warn "   Pastikan Anda benar-benar ingin melanjutkan."
    echo ""

    if [ "$AUTO_MODE" != "true" ]; then
        read -p "Lanjutkan restore? (yes/no): " CONFIRM
        if [ "$CONFIRM" != "yes" ]; then
            info "Restore dibatalkan."
            exit 0
        fi
    fi

    local filename=$(basename "$backup_file")

    if [[ "$filename" == master_* ]]; then
        info "Restore master database..."
        gunzip -c "$backup_file" | docker exec -i $MYSQL_CONTAINER mysql \
            -u"$DB_USER" -p"$DB_PASS" "$DB_MASTER" 2>/dev/null
        ok "Master database restored!"
    elif [[ "$filename" == tenant_* ]]; then
        local tenant_db="${filename%%_*}"
        info "Restore tenant database: $tenant_db ..."
        # Buat database dulu jika belum ada
        docker exec $MYSQL_CONTAINER mysql \
            -u"$DB_USER" -p"$DB_PASS" \
            -e "CREATE DATABASE IF NOT EXISTS \`$tenant_db\`;" 2>/dev/null
        gunzip -c "$backup_file" | docker exec -i $MYSQL_CONTAINER mysql \
            -u"$DB_USER" -p"$DB_PASS" "$tenant_db" 2>/dev/null
        ok "Tenant database $tenant_db restored!"
    else
        err "Format nama file tidak dikenal. Gunakan file dari hasil backup."
        exit 1
    fi
}

# ========== INFO ==========
show_info() {
    echo ""
    echo "=========================================="
    echo "   ServiceKU Backup System"
    echo "=========================================="
    echo ""
    echo "📍 Target Backup: $BACKUP_DIR"
    echo ""
    echo "💾 Cek ruang disk:"
    df -h "$BACKUP_DIR" 2>/dev/null | tail -1
    echo ""
    echo "📦 Ukuran database saat ini:"
    docker exec $MYSQL_CONTAINER mysql \
        -u"$DB_USER" -p"$DB_PASS" \
        -e "SELECT table_schema AS 'Database', ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS 'Size (MB)' FROM information_schema.tables GROUP BY table_schema ORDER BY Size DESC;" 2>/dev/null
    echo ""
    echo "📁 Isi folder backup saat ini:"
    ls -lh "$BACKUP_DIR/databases/" 2>/dev/null | tail -5
    echo ""
}

# ========== MAIN ==========
AUTO_MODE="false"

# Parse arguments
case "${1:-}" in
    --auto|-a)
        AUTO_MODE="true"
        ;;
    --restore|-r)
        if [ -z "$2" ]; then
            err "Gunakan: $0 --restore <file.sql.gz>"
            exit 1
        fi
        check_prerequisites
        restore_backup "$2"
        exit 0
        ;;
    --info|-i)
        check_prerequisites
        show_info
        exit 0
        ;;
    --help|-h)
        echo "Penggunaan: $0 [OPTION]"
        echo ""
        echo "Options:"
        echo "  --auto         Backup tanpa konfirmasi (untuk cron)"
        echo "  --restore FILE Restore dari file backup"
        echo "  --info         Tampilkan info backup"
        echo "  --help         Tampilkan bantuan ini"
        exit 0
        ;;
esac

# Jalankan backup
echo ""
echo "=========================================="
echo "  ServiceKU Backup - $(date +'%Y-%m-%d %H:%M')"
echo "=========================================="
echo ""

check_prerequisites

if [ "$AUTO_MODE" != "true" ]; then
    show_info
    read -p "Mulai backup sekarang? (yes/no): " CONFIRM
    if [ "$CONFIRM" != "yes" ]; then
        info "Backup dibatalkan."
        exit 0
    fi
fi

echo ""
backup_database
echo ""
backup_storage
echo ""
cleanup_old_backups

echo ""
echo "=========================================="
ok "✅ BACKUP SELESAI!"
echo "📁 Lokasi: $BACKUP_DIR"
echo "📅 Waktu: $(date +'%Y-%m-%d %H:%M:%S')"
echo "=========================================="
echo ""

# Tampilkan ukuran total backup
echo "Ukuran total backup:"
du -sh "$BACKUP_DIR/databases/" 2>/dev/null
du -sh "$BACKUP_DIR/storage/" 2>/dev/null
echo ""
