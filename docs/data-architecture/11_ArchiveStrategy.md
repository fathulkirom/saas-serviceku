# 11 — Archive Strategy

> **Sprint 6.2A · Blueprint Only.** Strategi pengarsipan data — kapan data dipindahkan, bagaimana, dan berapa lama disimpan.

---

## 1. Prinsip Arsip

- **Arsip = read-only** — data tidak bisa diubah/dihapus setelah diarsipkan.
- **Arsip ≠ delete** — data masih ada, hanya dipindahkan ke storage arsip.
- **Restore dimungkinkan** — Owner/Admin dapat me-restore data arsip ke DB aktif.
- **Arsip dikompresi** — untuk efisiensi storage.

---

## 2. Jadwal Arsip per Domain

| Domain | Arsip setelah | Trigger |
|---|---|---|
| **Request** | 1 tahun setelah `closed`/`cancelled` | Otomatis (cron) |
| **Service Order** | 1 tahun setelah `diambil`/`close` | Otomatis |
| **Sales Order** | 3 tahun setelah `success`/`refunded`/`void` | Otomatis |
| **Purchase Order** | 3 tahun setelah `close` | Otomatis |
| **Warranty / Claim** | 1 tahun setelah `resolved` | Otomatis |
| **Cash Shift** | 3 tahun setelah `final` | Otomatis |
| **Deposit** | 3 tahun setelah konfirmasi | Otomatis |
| **Inventory Movement** | 7 tahun (semua movement) | Otomatis (jarang — permanen) |
| **Audit Log** | 1 tahun | Otomatis; pindah ke storage arsip terkompresi |
| **History Log** | 1 tahun | Otomatis |
| **Tenant (nonaktif)** | 90 hari setelah nonaktif | Super Admin (manual/auto) |

---

## 3. Mekanisme Arsip

```
DB Aktif ──cron──> Storage Arsip (compressed, read-only)
                        ↓
                  Restore (jika dibutuhkan)
```

- Arsip disimpan per tenant (`{tenant_id}/archive/{domain}/{year}/`).
- Format: compressed (gzip) + indexed (metadata JSON).
- Query arsip: via Report/Archive UI (bukan query DB langsung).

---

## 4. Retensi Arsip

| Jenis data | Retensi arsip | Setelah itu |
|---|---|---|
| Finansial (Sales, Purchase, Cash, Deposit) | 7 tahun | Boleh dihapus permanen (setelah retensi minimum) |
| Transaksional (Request, Service, Warranty) | 7 tahun | Boleh dihapus |
| Audit / History | 7 tahun | Boleh dihapus |
| Customer / Device (PII) | 7 tahun | Anonymize; hapus data PII |
| Tenant nonaktif | 7 tahun | Hapus DB tenant (Super Admin) |

---

## 5. Aturan

1. **Arsip = otomatis** — cron job, bukan manual.
2. **Sebelum arsip**: pastikan data sudah terminal ≥ periode arsip.
3. **Restore**: Owner/Admin request; data kembali muncul di UI (read-only).
4. **Hapus permanen**: hanya Super Admin; setelah retensi minimum + konfirmasi.

---

## 6. Verifikasi

Konsisten dengan `03_DataLifecycle.md`, `10_SoftDeleteStrategy.md`, prinsip **Data Is Sacred**.
