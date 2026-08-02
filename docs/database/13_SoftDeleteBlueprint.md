# 13 — Soft Delete Blueprint · 14 — Audit Blueprint · 15 — History Blueprint

> **Sprint 6.2D · Table Blueprint Only.** Spesifikasi soft delete, audit, dan history. Dokumen gabungan.
> **Tidak ada SQL.**

---

## Part A — Soft Delete Blueprint (13)

### Kategori

| Kategori | Aturan | Tabel |
|---|---|---|
| **Tidak boleh dihapus** | Immutable. Tidak ada `deleted_at`. | `audit_logs`, `history_logs`, `request_history`, `inventory_movements`, `cash_shifts`, `finance_transactions` |
| **Soft Delete** | `deleted_at` TIMESTAMP NULL. Data tersembunyi; bisa restore. | Semua L3/L4/L5 lainnya |
| **Hard Delete** | Hapus fisik. Hanya non-transaksional. | `report_snapshots`, `dashboard_widgets`, `notifications` (user-triggered) |

### Kolom Soft Delete
- `deleted_at` TIMESTAMP NULL DEFAULT NULL
- `deleted_by` BIGINT UNSIGNED NULL — FK ke users(id) (opsional, untuk audit)

### Cascade Soft Delete
| Parent | Cascade |
|---|---|
| `requests` (soft delete) | → `request_devices` (soft cascade), → `service_orders` (soft cascade), → `sales_orders` (soft cascade) |
| `service_orders` (soft delete) | → `work_orders`, `checklists`, `technician_assignments` |
| `sales_orders` (soft delete) | → `sale_items` |
| `purchase_orders` (soft delete) | → `purchase_items` |

---

## Part B — Audit Blueprint (14)

### Tabel yang WAJIB diaudit

| Kelompok | Tabel |
|---|---|
| **Master** | `customers`, `devices`, `suppliers`, `service_partners`, `products` |
| **Transaksi** | `requests`, `service_orders`, `sales_orders`, `purchase_orders`, `cash_shifts`, `deposits`, `expenses`, `warranties`, `warranty_claims` |
| **Finance** | `compensations`, `finance_transactions` |
| **Security** | `users`, `roles`, `policies` |
| **Integration** | `provider_credentials` |

### Event yang dicatat
- `create` — entity dibuat
- `update` — field berubah (field, old_value, new_value)
- `delete` — soft delete
- `status_change` — perubahan status
- `login` — user login/logout
- `access` — akses data L3/L4 (PII/Sensitive)
- `restore` — restore dari soft delete

### Tabel yang TIDAK diaudit
- `audit_logs`, `history_logs`, `request_history` (sendiri adalah audit)
- `inventory_movements` (sendiri adalah audit trail)
- `notifications`, `attachments`, `dashboard_widgets`, `report_snapshots`

---

## Part C — History Blueprint (15)

### Strategi History

| Strategi | Digunakan untuk | Mekanisme |
|---|---|---|
| **Snapshot** | Harga produk, nilai policy | Tabel terpisah (`product_prices`). Setiap perubahan = insert baru; `valid_from`/`valid_to`. |
| **Versioning** | Policy, settings | `policies` dengan kolom `version`, `valid_from`, `valid_to`. Revisi = insert baru + `valid_to` versi lama. |
| **Append-only** | Inventory movement, Request status | `inventory_movements`, `request_history` — tidak ada update; hanya insert. |
| **Change log** | Customer, device, product (non-harga) | `history_logs` — mencatat field, old_value, new_value setiap perubahan. |

### Tabel History per Domain

| Domain | Strategi | Tabel history |
|---|---|---|
| **Product price** | Snapshot | `product_prices` (product_id, price, cost_price, valid_from, valid_to) |
| **Policy** | Versioning | Di dalam `policies` sendiri (version + valid_from/valid_to) |
| **Inventory** | Append-only | `inventory_movements` |
| **Request status** | Append-only | `request_history` |
| **Service status** | Append-only (via audit) | `audit_logs` (action=status_change) |
| **Customer** | Change log | `history_logs` |
| **Device** | Change log | `history_logs` |
| **Product (non-price)** | Change log | `history_logs` |
| **Settings** | Change log | `history_logs` |

---

## Verifikasi

Konsisten dengan `docs/data-architecture/08_AuditStrategy.md`, `09_HistoryStrategy.md`, `10_SoftDeleteStrategy.md` (Sprint 6.2A).
