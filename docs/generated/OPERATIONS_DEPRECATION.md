# Operations Deprecation

---

## ❌ Deprecated in Sprint 35

| Component | Replacement |
|-----------|-------------|
| Manual server health checks | Platform Health tab |
| SSH-based queue inspection | Queue & Jobs tab |
| Cron-based backup scripts | Backup & Recovery tab |
| Manual deployment via SSH | Deployment Center |
| Ad-hoc recovery procedures | Disaster Recovery tab |
| Spreadsheet performance tracking | Performance Logs |
| Manual security log review | Security Monitoring tab |
| CLI-based cache clearing | Cache & Session tab |
| Separate monitoring dashboards per service | Unified EPOC overview |
| Email-based ops alerts | EPOC Notification integration |
| Manual capacity planning | AI Operations Advisor |

---

## 🔄 Migration Path

All legacy operations should route through EPOC:
1. Health checks → Platform Health tab
2. Queue management → Queue & Jobs tab
3. Backups → Backup & Recovery tab
4. Deployments → Deployment Center
5. Security events → Security Monitoring tab

---

*Operations Deprecation — Sprint 35.0*
