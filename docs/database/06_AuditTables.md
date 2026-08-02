# 06 — Audit & History Tables

> **Sprint 6.2D · Table Blueprint Only.** Spesifikasi tabel audit, history, dan log. Append-only.
> **Tidak ada SQL.**

---

## AU01 — `audit_logs`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Jejak semua aksi — create, update, delete, status change, login, akses data sensitif. Data Is Sacred. |
| **2. Aggregate Owner** | N/A — Append-only log (bukan aggregate). |
| **3. Lifecycle** | dibuat → permanen (1 tahun aktif → arsip 7 tahun). |
| **4. Business Responsibility** | Compliance, investigasi, reversal (BR-015). |
| **5. Primary Key** | `id` BIGINT AUTO_INCREMENT. |
| **6. Foreign Key** | `tenant_id` (tenant scope); `actor_id` → users(id) NULLABLE (System=NULL). |
| **7. Candidate Unique** | — |
| **8. Index** | `(tenant_id, entity_type, entity_id, created_at)` — cari audit per entity; `(tenant_id, actor_id, created_at)` — aktivitas user; `(tenant_id, action, created_at)` — filter aksi. |
| **9. Soft Delete?** | ❌ **Tidak boleh dihapus** — immutable. |
| **10. Audit?** | N/A (ini adalah audit log itu sendiri). |
| **11. History?** | N/A. |
| **12. Retention** | 7 tahun; arsip setelah 1 tahun. |
| **13. Future** | Partitioning untuk performa (lihat `16_PartitionStrategy.md`). |
| **Kolom kunci (konseptual)** | `tenant_id`, `actor_id`, `action` (create/update/delete/status_change/login/access), `entity_type`, `entity_id`, `field` (nullable), `old_value` JSON, `new_value` JSON, `metadata` JSON (ip, user_agent, reason), `created_at`. |

---

## AU02 — `history_logs`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Riwayat perubahan nilai data master/konfigurasi (bukan transaksional). |
| **2. Aggregate Owner** | N/A — Append-only log. |
| **3. Lifecycle** | dibuat → permanen. |
| **5. Primary Key** | `id` BIGINT. |
| **8. Index** | `(tenant_id, entity_type, entity_id, created_at)`. |
| **9. Soft Delete?** | ❌ Immutable. |
| **12. Retention** | 7 tahun. |
| **Digunakan untuk** | Perubahan customer, device, product name, settings. |

---

## AU03 — `request_history`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Jejak setiap perubahan status Request (ADR-001). Append-only. |
| **2. Aggregate Owner** | ❌ Child of Request. |
| **5. Primary Key** | `id` BIGINT. FK: `request_id` → requests(id) CASCADE. |
| **8. Index** | `(request_id, created_at)`. |
| **9. Soft Delete?** | ❌ Immutable. |
| **Kolom kunci** | `request_id`, `old_status`, `new_status`, `actor_id`, `note`, `created_at`. |

---

## AU04 — `notifications`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Notifikasi terkirim ke user/customer. |
| **5. Primary Key** | `id` BIGINT. FK: `recipient_id` → users(id) / `customer_id` NULLABLE. Polymorphic: `notifiable_type` + `notifiable_id` (Request/ServiceOrder). |
| **8. Index** | `(recipient_id, is_read, created_at)`; `(notifiable_type, notifiable_id)`. |
| **9. Soft Delete?** | ✅ (user bisa hapus notifikasi). |
| **12. Retention** | 1 tahun; arsip opsional. |

---

## AU05 — `attachments`

| Poin | Spesifikasi |
|---|---|
| **1. Tujuan** | Lampiran polymorphic — foto, PDF, dokumen. |
| **5. Primary Key** | `id` BIGINT. Polymorphic: `attachable_type` + `attachable_id`. |
| **8. Index** | `(attachable_type, attachable_id)`; `(tenant_id, created_at)`. |
| **9. Soft Delete?** | ✅ (bersama entity induk). |
| **Kolom kunci** | `tenant_id`, `attachable_type`, `attachable_id`, `file_path`, `file_name`, `mime_type`, `size_bytes`, `uploaded_by`, `checksum`, `created_at`. |

---

## Matriks Audit Wajib

| Tabel | Audit wajib? | Event yang dicatat |
|---|---|---|
| customers | ✅ | create, update, delete |
| devices | ✅ | create, update, delete |
| suppliers | ✅ | create, update, delete |
| service_partners | ✅ | create, update, delete |
| products | ✅ | create, update (harga = snapshot di history) |
| requests | ✅ | create, status change, assign, cancel |
| service_orders | ✅ | create, status change, update biaya, void |
| sales_orders | ✅ | create, void, refund |
| purchase_orders | ✅ | create, terima, void |
| cash_shifts | ✅ | open, close |
| deposits | ✅ | create, confirm, reject |
| warranties | ✅ | create, claim |
| warranty_claims | ✅ | create, resolve |
| compensations | ✅ | create, approve, pay (target) |
| users | ✅ | create, suspend, role change |
| policies | ✅ | create, revise |
| provider_credentials | ✅ | create, update, delete |
| inventory_items | ❌ (movement = audit) | — |
| inventory_movements | ❌ (sendiri adalah audit) | — |
| audit_logs | ❌ | — |
| history_logs | ❌ | — |
| notifications | ❌ | — |
| attachments | ❌ | — |
| dashboard_widgets | ❌ | — |
| report_snapshots | ❌ | — |

---

## Verifikasi

5 tabel audit/log. Audit wajib untuk 17 tabel transaksional/master. Append-only untuk audit_logs, history_logs, request_history. Konsisten dengan Sprint 6.2A `08_AuditStrategy.md`.
