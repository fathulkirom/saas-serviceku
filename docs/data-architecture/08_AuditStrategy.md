# 08 — Audit Strategy

> **Sprint 6.2A · Blueprint Only.** Strategi jejak audit — siapa melakukan apa, kapan, dan dari mana.

---

## 1. Prinsip Audit

- **Append-only** — audit log tidak dapat diupdate atau dihapus.
- **Immutable** — setelah ditulis, tidak bisa diubah.
- **Tenant-scoped** — setiap tenant memiliki audit log sendiri.
- **Platform-scoped** — Super Admin memiliki audit log platform (central).

---

## 2. Apa yang Diaudit?

| Kategori | Event yang dicatat | Data yang disimpan |
|---|---|---|
| **Akses** | Login, Logout, Failed login, Password change, 2FA | user_id, ip_address, user_agent, timestamp, success/fail |
| **Create** | Semua pembuatan entity transaksional | entity_type, entity_id, data snapshot (opsional) |
| **Update** | Setiap perubahan field entity transaksional | entity_type, entity_id, field, old_value, new_value |
| **Delete / Soft Delete** | Setiap penghapusan | entity_type, entity_id, deleted_by |
| **Status Change** | Setiap perubahan status | entity_type, entity_id, old_status, new_status |
| **Permission** | Perubahan role/permission, delegation grant/revoke | target_user, permission, granted_by |
| **Financial** | Void, Refund, Adjust, Confirm deposit | entity_type, entity_id, amount, reason |
| **Data Access** (L3/L4) | Read pada data PII/Sensitive | entity_type, entity_id, accessed_by |

---

## 3. Struktur Audit Log (Konsep — bukan SQL)

```
audit_log {
    id, tenant_id
    actor_id, actor_type (User/System/API)
    action: 'create' | 'update' | 'delete' | 'status_change' | 'access' | 'login' | ...
    entity_type, entity_id
    field (jika update)
    old_value, new_value (JSON)
    metadata: { ip, user_agent, reason, ... }
    timestamp
}
```

---

## 4. Siapa yang Melihat Audit?

| Role | Akses |
|---|---|
| **Super Admin** | Audit log platform + bisa melihat audit tenant (investigasi). |
| **Owner** | Audit log tenant-nya sendiri (semua). |
| **Admin / Manager** | Audit log tenant (kecuali akses data L4). |
| **CS / Kasir / Teknisi** | Tidak bisa melihat audit log (kecuali audit tindakan sendiri — future). |

---

## 5. Retensi & Arsip

- Audit log disimpan **minimal 1 tahun** di tabel aktif, lalu diarsipkan.
- Arsip audit = **7 tahun** (read-only, compressed).
- Tidak boleh dihapus sebelum 7 tahun.
- Backup audit log = bagian dari backup tenant.

---

## 6. Verifikasi

Konsisten dengan `docs/request-engine/05_RequestOwnership.md` (RequestHistory append-only), `04_DataClassification.md` (akses L3/L4 diaudit), prinsip **Data Is Sacred**.
