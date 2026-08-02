# ServiceKU — Ownership

> **Sprint 6.1 · Blueprint Only.** **Ownership** = siapa pemilik (pemegang kendali) tiap domain/aggregate — menentukan siapa yang bisa membuat, mengubah, menyetujui, dan menghapus. Konsisten dengan `docs/specification/PermissionMatrix.md`, `RoleMatrix.md`, dan `BusinessRules.md`.

---

## 1. Konsep Ownership

- **Pemilik domain** = role yang memegang permission terkait (`manage_*`, `canX()`).
- Ownership berlapis: **Platform** (tenant, plan) → **Tenant Owner** (pengaturan, user, policy) → **Role operasional** (CS/Teknisi/Kasir) → **Profil sendiri** (user).
- Super Admin = pemilik platform; Owner = pemilik tenant; user lain = pemegang sebagian.

---

## 2. Matriks Ownership per Domain

| Domain | Pemilik Utama | Pengelola | Pelaksana | Catatan |
|---|---|---|---|---|
| Tenant | Super Admin | Owner | — | 1 DB per tenant |
| Branch | Owner (`canManageBranch`) | Owner | Manager/Admin (ops.) | plan Pro+ multi-branch |
| User | Owner (`manage_users`) | Owner | — | owner terakhir tak bisa dihapus |
| Position | Owner (target) | Owner | — | struktural |
| Role | Platform (seed) / Owner (kustom, target) | Owner | — | 7 resmi + kustom |
| Permission | Platform (Module registry) | — | — | pusat otorisasi |
| Policy | Owner | Owner | — | kompensasi/garansi/harga |
| Customer | Owner/Admin/Manager/CS (`manage_customers`) | CS/Admin | CS/Admin | — |
| Customer Visit | CS/Admin/Manager/Owner | CS | CS | — |
| Device | CS/Admin/Manager/Owner | CS/Admin | — | terkait customer |
| Service Order | Owner/Admin/Manager/CS (`work_on_services`; `assign_technician`) | CS/Admin | Teknisi | void/delete: Owner/Admin |
| Work Order | Teknisi/Admin/Manager/Owner | Admin/Manager | Teknisi | — |
| Service Partner | Owner/Admin/Manager | Owner/Admin | — | onpartner |
| Supplier | Owner/Admin/Manager (`manage_purchases`) | Admin/Manager | — | — |
| Sparepart/Product | Owner/Admin/Manager (`manage_products`) | Admin/Manager | Kasir/Teknisi (pakai) | — |
| Inventory | Owner/Admin/Manager (`manage_products` + `transfer_stock`) | Admin/Manager | — | adjust: Owner/Admin |
| Purchase | Owner/Admin/Manager (`manage_purchases`) | Admin/Manager | — | void: Owner/Admin |
| Sales | Kasir/Owner/Admin/Manager (`manage_sales`) | Kasir | Kasir | void/delete/refund: Owner/Admin |
| Cash Shift | Kasir/Owner/Admin/Manager (`manage_cash_register`) | Kasir | Kasir | — |
| Deposit | Owner/Admin/Manager (`manage_deposits`) | Owner/Admin | Kasir (buat, PV) | konfirmasi: Owner/Admin |
| Warranty | Owner/Admin/Manager/CS | CS/Admin | — | policy menentukan |
| Compensation | Owner/Admin/Manager (target) | Owner | — | mengikuti Policy |
| Dashboard | Owner/Manager/Admin | Owner (widget, target) | — | permission-based |
| Subscription | Owner (bayar/upgrade) / Super Admin (override) | Owner | — | — |
| Module | Platform (registry) / Super Admin | — | — | plan mengaktifkan |
| Feature | Platform (Feature Engine) | — | — | full/read_only/none |

---

## 3. Aturan Ownership

1. **Owner tenant** adalah pemilik tertinggi di scope tenant; **Super Admin** di atas tenant (platform).
2. Setiap domain punya **satu pemilik utama** untuk keputusan destruktif/konfigurasi.
3. Akses efektif = **role ∩ plan ∩ business type** (3 lapis, lihat `BusinessRules.md`).
4. **Tidak boleh** ada dua pemilik utama yang saling menghapus data tanpa jejak.
5. Transisi kepemilikan (mis. device ganti customer) harus tercatat.

---

## 4. Ownership vs Business Reality

- Customer "memiliki" Device → tapi **pengelolaan data** device oleh CS/Admin tenant.
- Teknisi "mengerjakan" Service Order → tapi **keputusan void/close** tetap Owner/Admin.
- Policy "dimiliki" Owner → kompensasi **dihitung** mengikuti policy, **disetujui** Owner/Manager.

---

## 5. Verifikasi

Semua `manage_*`/`canX()` di atas dari `role_permissions` & `Traits/HasRoles` (source, terkonfirmasi). Compensation/Position sebagai ownership **target** (belum utuh di source).
