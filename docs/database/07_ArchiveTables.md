# 07 — Archive Tables

> **Sprint 6.2D · Table Blueprint Only.** Strategi arsip — tabel mana yang diarsipkan, kapan, dan bagaimana.
> **Tidak ada SQL.** Arsip = proses, bukan tabel baru.

---

## 1. Strategi Arsip per Tabel

| Tabel | Arsip setelah | Retensi arsip | Setelah retensi |
|---|---|---|---|
| `requests` | 1 tahun setelah closed/cancelled | 7 tahun | Hapus permanen |
| `request_history` | Ikut requests | 7 tahun | Hapus permanen |
| `service_orders` | 1 tahun setelah diambil/close | 7 tahun | Hapus permanen |
| `work_orders` | Ikut service_orders | 7 tahun | Hapus permanen |
| `sales_orders` | 3 tahun setelah success/refunded/void | 7 tahun | Hapus permanen |
| `purchase_orders` | 3 tahun setelah close | 7 tahun | Hapus permanen |
| `warranties` | 1 tahun setelah resolved/expired | 7 tahun | Hapus permanen |
| `warranty_claims` | Ikut warranties | 7 tahun | Hapus permanen |
| `cash_shifts` | 3 tahun setelah final | 7 tahun | Hapus permanen |
| `deposits` | Ikut cash_shifts | 7 tahun | Hapus permanen |
| `inventory_movements` | 7 tahun | 7 tahun | Hapus permanen |
| `audit_logs` | 1 tahun | 7 tahun | Hapus permanen |
| `history_logs` | 1 tahun | 7 tahun | Hapus permanen |
| `notifications` | 1 tahun | 3 tahun | Hapus permanen |
| `customers` (nonaktif) | 7 tahun setelah inactive | Anonymize | Hapus PII |
| `devices` | 7 tahun setelah servis terakhir | 7 tahun | Hapus permanen |
| `report_snapshots` | 3 tahun | — | Hard delete |
| `tenants` (nonaktif) | 90 hari setelah nonaktif | 7 tahun | Hapus DB tenant |

---

## 2. Mekanisme Arsip (Konsep — bukan implementasi)

```
DB Aktif ──cron (bulanan)──> Storage Arsip
    ├── Data dipindahkan (INSERT ke storage arsip, DELETE dari DB aktif)
    ├── Format: compressed (gzip) + metadata JSON untuk indexing
    └── Storage: S3 / R2 / Local (sesuai provider tenant)
```

- **Arsip = read-only.** Data tidak bisa diubah setelah diarsipkan.
- **Restore** = Admin/Owner request; data dikembalikan ke DB aktif (read-only).
- **Tidak ada tabel arsip terpisah** di DB — arsip disimpan sebagai file di storage provider.

---

## 3. Tabel yang TIDAK Diarsipkan (selamanya di DB aktif)

| Tabel | Alasan |
|---|---|
| `branches` | Konfigurasi; sedikit baris. |
| `users` | Konfigurasi; sedikit baris. |
| `roles`, `permissions`, `role_permission` | Konfigurasi; sedikit baris. |
| `policies` | Versioning — versi lama tetap di DB (sedikit baris). |
| `tenant_settings` | Konfigurasi; 1 baris per key. |
| `provider_credentials` | Konfigurasi; sedikit baris. |
| `products` | Master data; stok = inventory_items. |
| `suppliers`, `service_partners` | Master data; sedikit baris. |
| `customers` (aktif) | Master; tetap di DB aktif. |
| `devices` (aktif) | Master; tetap di DB aktif. |
| `inventory_items` | Stok aktif; tidak diarsipkan. |
| `dashboard_widgets` | Konfigurasi UI. |
| `attachments` | File disimpan di storage; metadata tetap di DB. |

---

## 4. Aturan

1. **Arsip = otomatis** — cron job bulanan.
2. **Verifikasi** — sebelum hapus dari DB aktif, pastikan data sudah tersimpan di storage arsip.
3. **Restore dimungkinkan** — self-service untuk Owner/Admin.
4. **Data finansial & PII** — wajib arsip 7 tahun sebelum boleh dihapus.

---

## 5. Verifikasi

Konsisten dengan `docs/data-architecture/11_ArchiveStrategy.md` (Sprint 6.2A), `docs/data-architecture/03_DataLifecycle.md`.
