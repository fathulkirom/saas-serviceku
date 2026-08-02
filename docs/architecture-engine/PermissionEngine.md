# Permission Engine — ServiceKU

> **Keputusan target:** sistem **permission-centric** — seluruh otorisasi diselesaikan lewat **permission**, bukan pengecekan nama role. Role hanyalah kumpulan permission.

---

## 1. Kondisi Saat Ini (source) vs Target

| Aspek | Saat Ini (source) | Target |
|---|---|---|
| Penyimpanan permission | Array `role_permissions` hardcoded di `HandleInertiaRequests.php` | **Tabel `permissions`** (data, bukan string di kode) |
| Pengecekan | `canX()` per method + string permission | Permission resolver tunggal: `auth()->user()->can('service.void')` |
| Relasi | Role → array string | `role_permission` pivot + `user_role` pivot (many-to-many) |
| Pengelolaan | Hanya diubah lewat kode | Dikelola data (Super Admin; owner untuk role-nya) |

---

## 2. Jenis Permission (Target)

| Tipe | Format | Contoh |
|---|---|---|
| Modul | `<module>.<action>` | `service.create`, `pos.void`, `inventory.transfer` |
| Sistem | `system.*` | `system.manage_users`, `system.manage_settings` |
| Plan | dari `planFeature` | `feature.services`, `feature.multi_branch` |
| Kustom | `<module>.<custom>` | `service.assign_technician`, `report.export` |

---

## 3. Resolusi Permission (Target)

```mermaid
flowchart LR
    U[User] --> R[Roles (1..n)]
    R --> P[Permissions (union)]
    P --> C{Cek Permission}
    C -->|ada| ALLOW
    C -->|tidak| DENY
```

- Permission user = **gabungan (union)** semua permission dari role-nya.
- Pengecekan TIDAK pernah berbasis nama role (`isAdmin()`) di dalam logika aksi.

---

## 4. Katalog Permission (Pemetaan dari `role_permissions` saat ini)

Permission inti yang sudah ada (dipetakan ke permission atomik):

| Capability (saat ini) | Permission atomik (target) |
|---|---|
| manage_users | `user.manage` |
| manage_settings | `settings.manage` |
| manage_finance | `finance.manage` |
| manage_products | `product.manage` |
| manage_customers | `customer.manage` |
| manage_sales | `pos.create`, `pos.read`, `pos.update` |
| manage_cash_register | `cash_register.manage` |
| manage_deposits | `deposit.manage` |
| manage_purchases | `purchase.manage` |
| manage_branches | `branch.manage` |
| manage_indents | `indent.manage` |
| void_transactions | `pos.void`, `purchase.void` |
| assign_technician | `service.assign_technician` |
| work_on_services | `service.work` |
| delete_models | `*.delete` |
| quick_stock | `product.quick_stock` |

---

## 5. Aturan Permission Engine

1. **Tidak ada** pengecekan `role === 'x'` di dalam controller/policy (target).
2. Permission mengikuti **modul** (Module Engine): menonaktifkan modul → permission-nya tidak dihitung.
3. Plan & business type tetap sebagai lapis **filter tambahan** di atas permission (3 lapis: role∩plan∩business type) — lihat `docs/specification/BusinessRules.md`.
4. Semua UI mengecek permission yang sama (frontend prop `can`) agar tidak ada celah tampilan vs server.

---

## 6. Verifikasi

`role_permissions` & `canX()` adalah kondisi **saat ini** dari source. Konsep tabel permission & resolver tunggal adalah **target/roadmap**.
