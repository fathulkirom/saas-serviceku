# BACKUP_RESTORE.md — ServiceKU v1.0.0

## Backup Commands

### Database Backup

```bash
# Via Docker
docker compose -f docker/docker-compose.prod.yml exec app php artisan backup:database

# Custom path
docker compose -f docker/docker-compose.prod.yml exec app php artisan backup:database --path=/tmp/backup_$(date +%Y%m%d)
```

### What Gets Backed Up

| Item | Location | Format |
|------|----------|--------|
| Central DB | `storage/backups/{date}/central_db.sql` | SQL dump |
| Storage files | `storage/backups/{date}/storage.tar.gz` | Tar archive |

### Automatic Cleanup

Backups older than 7 days are automatically removed.

---

## Restore Commands

### Database Restore

```bash
# Via Docker
docker compose -f docker/docker-compose.prod.yml exec app php artisan restore:database storage/backups/2026-08-02_030000

# Then run migrations
docker compose -f docker/docker-compose.prod.yml exec app php artisan migrate --force
```

### Manual MySQL Restore

```bash
# Copy backup to container
docker cp backup.sql serviceku-mysql:/tmp/backup.sql

# Restore
docker exec serviceku-mysql mysql -u serviceku -p serviceku_master < /tmp/backup.sql
```

---

## Automated Backup (Cron)

Add to crontab (`crontab -e`):

```
# Daily backup at 2 AM
0 2 * * * cd /opt/serviceku && docker compose -f docker/docker-compose.prod.yml exec -T app php artisan backup:database >> /var/log/serviceku-backup.log 2>&1
```

---

## Disaster Recovery

1. **Stop application**: `docker compose down`
2. **Restore database**: `php artisan restore:database <backup-path>`
3. **Run migrations**: `php artisan migrate --force`
4. **Start application**: `docker compose up -d`
5. **Verify health**: `curl http://localhost/health`
