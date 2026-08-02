# ServiceKU — Role Matrix

> Matriks akses menu per role. Dibangun dari **source code**:
> - `resources/js/Layouts/AuthenticatedLayout.vue` → `menuItems` (array `roles` per menu).
> - `app/Http/Middleware/HandleInertiaRequests.php` → `role_permissions`.
> - `app/Models/Tenant/Traits/HasRoles.php` → method `canX()`.
>
> **Legend:** ✅ = Full (menu + aksi) · 👁 = Read / terbatas · ❌ = No Access · **Perlu Verifikasi** = belum dapat dipastikan dari source.
>
> Catatan penting: akses aksi juga dibatasi **plan feature** (`check.plan.feature`) dan **business type**. Tabel ini fokus pada **visibilitas menu & kemampuan role** sesuai source; action-level detail di `docs/specification/PermissionMatrix.md`.

---

## 1. Matriks Menu (Tenant Roles)

| Menu / Modul | Owner | Manager | Admin | CS | Kasir | Teknisi |
|---|---|---|---|---|---|---|
| Dashboard | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Service (Servis) | ✅ | ✅ | ✅ | ✅ | 👁 * | ✅ |
| Pelanggan (Customer) | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| POS / Penjualan (Sales) | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| Keuangan | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| Kas / Setoran Harian | ✅ | ✅ | ✅ | ❌ | 👁 * | ❌ |
| Inventaris (Stok) | ✅ | ✅ | ✅ | ❌ | ❌ | 👁 * |
| Pembelian | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Supplier | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Indent | ✅ | ✅ | ✅ | ✅ | ❌ | 👁 * |
| Laporan (Report) | ✅ | ✅ | ✅ | 👁 * | 👁 * | ❌ |
| Monitoring | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Pengaturan (Settings) | ✅ | 👁 * | 👁 * | ❌ | ❌ | ❌ |
| Manajemen User | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Cabang (Multi-Branch) | ✅ | 👁 * | 👁 * | ❌ | ❌ | ❌ |
| Subscription / Billing | ✅ | 👁 * | 👁 * | ❌ | ❌ | ❌ |
| Dokumen (SOP/KB/QuickReply) | ✅ | 👁 * | 👁 * | 👁 * | ❌ | ❌ |
| Servis Tools | 👁 * | 👁 * | 👁 * | 👁 * | ❌ | 👁 * |
| Pencarian Global (Cmd+K) | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| Profil Saya | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |

\* **Perlu Verifikasi** — lihat catatan per baris di bawah.

---

## 2. Matriks Menu (Super Admin — Panel Platform)

Super Admin **tidak** memakai menu tenant; ia memakai panel platform (`/admin`, layout `AdminLayout`):

| Menu Platform | Super Admin |
|---|---|
| Dashboard Admin | ✅ |
| Tenant (daftar/detail/CRUD) | ✅ |
| Plan | ✅ |
| Voucher / Kode Promo | ✅ |
| Payment & Payment Settings | ✅ |
| Backup | ✅ |
| Settings (system, feature flags, mail) | ✅ |
| Monitoring | ✅ |
| Logs | ✅ |

Super Admin untuk menu tenant = **❌ (tidak berlaku)** — akses ke data tenant dilakukan via "Login Sebagai" (impersonasi) dari panel admin.

---

## 3. Dasar Source per Baris (yang terkonfirmasi)

### Terkonfirmasi dari `menuItems` (`AuthenticatedLayout.vue`)
- **Dashboard**: `roles: ['*']` → semua role tenant.
- **Pelanggan**: `['owner','admin','manager','cs']`.
- **Service**: `['*']` (semua), tetapi aksi `work_on_services` hanya untuk role dengan permission itu.
- **POS/Sales**: `['owner','admin','manager','cashier']`.
- **Keuangan**: `['owner','admin','manager','cashier']`.
- **Inventaris**: `['owner','admin','manager','head_store']`.
- **Pembelian**: `['owner','admin','manager']`.
- **Supplier**: `['owner','admin','manager']`.
- **Laporan**: `['owner','admin','manager']`.
- **Pengaturan**: `['owner','admin']`.

### Terkonfirmasi dari `role_permissions` (capability per role)
| Role | Permissions |
|---|---|
| owner | manage_users, manage_settings, manage_finance, manage_products, manage_customers, manage_sales, manage_cash_register, manage_deposits, manage_purchases, manage_branches, manage_indents, void_transactions, assign_technician, work_on_services, delete_models, quick_stock |
| admin | manage_finance, manage_products, manage_customers, manage_sales, manage_cash_register, manage_deposits, manage_purchases, manage_indents, void_transactions, assign_technician, work_on_services, delete_models |
| manager | manage_finance, manage_products, manage_customers, manage_sales, manage_cash_register, manage_deposits, manage_purchases, manage_indents, work_on_services |
| cs | manage_customers, manage_indents, assign_technician, work_on_services |
| technician | work_on_services |
| cashier | manage_sales, manage_cash_register |
| head_store * | manage_finance, manage_products, manage_customers, manage_sales, manage_cash_register, manage_deposits, work_on_services |
| courier * | (kosong) |
| custom * | (kosong) |

\* `head_store`, `courier`, `custom` ada di source namun **bukan role resmi utama** (lihat `PROJECT_SPECIFICATION.md` §6).

### Terkonfirmasi dari `Traits/HasRoles.php` (`canX()`)
- `canManageUsers` → **owner** saja.
- `canManageSettings` → **owner** saja.
- `canManageFinance` → owner/admin/manager/head_store.
- `canManageProducts` → owner/admin/manager.
- `canManageCustomers` → owner/admin/manager/cs.
- `canManageSales` → owner/admin/manager/head_store/cashier.
- `canVoidTransaction` → owner/admin.
- `canDeleteModel` → owner/admin.
- `canAssignTechnician` → owner/admin/cs.
- `canManageBranch` → owner saja.
- `canManageCashRegister` → owner/admin/cashier/manager.
- `canConfirmDeposit` → owner/admin.
- `canManagePurchases` → owner/admin/manager.

---

## 4. Catatan "Perlu Verifikasi"

| Baris | Yang belum pasti |
|---|---|
| Service (Kasir) | Menu tampil untuk semua (`*`), tetapi kasir tidak punya `work_on_services` → aksi servis dibatasi; perlu verifikasi tampilan aksi per status. |
| Kas (Kasir) | `manage_deposits` tidak ada untuk cashier; namun menu Kas muncul di topbar. Perlu verifikasi apakah kasir bisa membuat setoran. |
| Inventaris (Teknisi) | Teknisi memakai sparepart lewat servis, bukan menu inventaris; perlu verifikasi menu. |
| Indent (Teknisi) | Teknisi dapat request indent lewat alur servis; perlu verifikasi menu mandiri. |
| Laporan (CS/Kasir) | Plan `reports` bisa `read_only`; perlu verifikasi tampilan menu untuk role ini. |
| Pengaturan (Manager/Admin) | Menu tampil untuk owner+admin; `canManageSettings` = owner saja → admin hanya sebagian. |
| Cabang (Manager/Admin) | Feature `multi_branch` vs `canManageBranch` (owner) → perlu verifikasi. |
| Subscription (Manager/Admin) | Halaman billing/profil toko; perlu verifikasi role yang boleh akses. |
| Dokumen / Servis Tools | Tidak ada daftar `roles` eksplisit di `menuItems`; perlu verifikasi. |

---

## 5. Aturan

1. Role resmi = 7 (Super Admin + 6 tenant). Jangan menambah role baru.
2. Setiap modul baru harus menetapkan matriks aksesnya di dokumen ini.
3. Bila informasi tidak dapat dipastikan, tulis **"Perlu Verifikasi"** — jangan mengarang.
4. Akses efektif = role ∩ plan feature ∩ business type (tiga lapis).
