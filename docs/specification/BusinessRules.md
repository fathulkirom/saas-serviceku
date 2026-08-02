# ServiceKU — Business Rules

> Aturan bisnis **siapa yang boleh membuat / mengubah / menghapus / melihat** data, per entitas.
> Dasar source: `role_permissions`, `Traits/HasRoles.php` (`canX()`), `CheckPlanFeature`, dan pola controller.
> Konvensi: bila belum dipastikan dari source, tulis **"Perlu Verifikasi"** — **JANGAN mengarang**.

---

## 0. Lapisan Aturan

Akses efektif sebuah aksi = **3 lapis**:
1. **Role** → `role_permissions` / `canX()` (siapa yang boleh).
2. **Plan feature** → `CheckPlanFeature` (`full` / `read_only` / `none`).
3. **Business type** → `Tenant::getBusinessTypeFeatures` (mis. retail tanpa modul servis/checklist).

Alur: request masuk → middleware/auth → `check.plan.feature` → Policy/`canX()` → controller.

---

## 1. Aturan Per Entitas

### 1.1 User (Manajemen User)
- **Create / Update / Delete**: hanya **Owner** (`canManageUsers`). *Admin tidak bisa.*
- **Melihat daftar user**: `menuItems` user-manage → owner (Perlu Verifikasi untuk admin).
- Role user tidak dapat diubah oleh role selain owner.

### 1.2 Settings (Pengaturan Toko)
- **Update**: hanya **Owner** (`canManageSettings`).
- **Melihat**: menu tampil untuk owner + admin (admin read-only → Perlu Verifikasi).

### 1.3 Tenant / Business Type / Branding
- **Ubah business type / branding / paket**: Owner (dan proses onboarding). Perlu Verifikasi untuk alur perubahan pasca-onboarding.

### 1.4 Service (Tiket Servis)
| Aksi | Role yang berhak (source) |
|---|---|
| Buat tiket | Owner, Admin, Manager, CS (`work_on_services` + front desk) |
| Assign teknisi | Owner, Admin, CS (`assign_technician`) |
| Kerjakan / ubah status | Owner, Admin, Manager, CS, Teknisi (`work_on_services`); kasir **tidak** |
| Pindah partner (onpartner) | Owner, Admin, Manager (Perlu Verifikasi) |
| Konfirmasi pelanggan | Owner, Admin, CS (Perlu Verifikasi) |
| Cancel / void / close | Owner, Admin (`void_transactions`, `delete_models` untuk penghapusan) |
| Hapus tiket | Owner, Admin (`delete_models`) |
| Lihat semua tiket | Semua role dengan akses modul service; dibatasi plan/business type |
- **Constraint**: tiket selesai masuk masa garansi (lihat Garansi); tiket indent menunggu stok.

### 1.5 Customer
- **Create / Update / Delete**: Owner, Admin, Manager, CS (`manage_customers`). Kasir/Teknisi **tidak**.
- **Melihat**: role dengan `manage_customers`; plus plan feature `customers`.

### 1.6 Product / Sparepart
- **Create / Update / Delete**: Owner, Admin, Manager (`manage_products`).
- **Melihat**: semua role yang butuh (POS/kasir, teknisi via servis); Perlu Verifikasi menu untuk teknisi.

### 1.7 Penjualan (POS / Sales)
- **Buat transaksi**: Owner, Admin, Manager, Kasir (`manage_sales`). CS/Teknisi **tidak**.
- **Void transaksi**: Owner, Admin (`void_transactions`).
- **Hapus transaksi**: Owner, Admin (`delete_models`).
- **Baca nota / histori**: Owner, Admin, Manager, Kasir.

### 1.8 Cash Register / Kas
- **Buka/tutup shift & kelola kas**: Owner, Admin, Manager, Kasir (`manage_cash_register`).
- **Konfirmasi setoran**: Owner, Admin (`canConfirmDeposit`).
- **Buat setoran harian**: Perlu Verifikasi (kasir? owner/admin/manager via `manage_deposits`).

### 1.9 Pembelian
- **Buat / ubah / hapus**: Owner, Admin, Manager (`manage_purchases`).
- **Konfirmasi penerimaan**: Owner, Admin, Manager.
- **Void PO**: Owner, Admin (Perlu Verifikasi).

### 1.10 Inventory / Transfer Stok
- **Mutasi & transfer antar cabang**: Owner, Admin, Manager (`manage_products` + feature `transfer_stock`/`multi_branch`).
- **Adjustment**: Owner, Admin (Perlu Verifikasi).
- **Stok menipis (reorder)**: Owner, Admin, Manager (Perlu Verifikasi untuk notifikasi).

### 1.11 Supplier
- **Create / Update / Delete**: Owner, Admin, Manager (menu `['owner','admin','manager']`).

### 1.12 Indent
- **Buat**: Owner, Admin, Manager, CS (`manage_indents`); teknisi via alur servis.
- **Konfirmasi datang**: Owner, Admin (Perlu Verifikasi).

### 1.13 Garansi
- **Buat klaim**: Owner, Admin, Manager, CS (berbasis tiket selesai). Perlu Verifikasi detail.

### 1.14 Checklist (perangkat servis)
- **Isi checklist**: role yang mengerjakan servis (teknisi/admin/manager/owner/cs) pada business type non-retail. Perlu Verifikasi.

### 1.15 Laporan
- **Lihat semua laporan**: Owner, Admin, Manager (feature `reports` full). CS/Kasir = `read_only`/none tergantung plan → Perlu Verifikasi.

### 1.16 Monitoring
- **Lihat**: Owner, Admin, Manager, Head Store (feature `monitoring`). Perlu Verifikasi untuk CS.

### 1.17 Cabang
- **Kelola cabang**: Owner (`canManageBranch`).
- **Bekerja lintas cabang**: sesuai feature `multi_branch` (Owner/Manager/Admin).

### 1.18 Subscription & Voucher
- **Melihat paket & billing**: Owner; Manager/Admin read-only (Perlu Verifikasi).
- **Redeem voucher / bayar**: Owner (Perlu Verifikasi).
- **Mengubah paket tenant**: Super Admin (platform).

### 1.19 Dokumen (SOP / Knowledge Base / Quick Reply)
- **Kelola**: Owner, Manager, Admin (Perlu Verifikasi).
- **Melihat**: role yang memakai (CS/Teknisi) — Perlu Verifikasi.

### 1.20 Tenant & Platform (Super Admin)
- **Semua aksi** (buat tenant, ubah plan, voucher, payment settings, backup, logs): **Super Admin** via panel `/admin`.

---

## 2. Aturan Umum

1. **Tidak ada user yang menghapus dirinya sendiri** sebagai last owner (Perlu Verifikasi guard).
2. **Aksi destruktif** (void, cancel, hapus) selalu butuh konfirmasi di UI (modal) — konsisten dengan `docs/product/Interaction.md`.
3. **Perubahan status finansial** (void, refund, hapus transaksi) hanya Owner/Admin.
4. **Data tenant terisolasi**: tenant A tidak pernah melihat data tenant B (1 DB per tenant).
5. **Setoran & kas** harus terkunci setelah shift ditutup (Perlu Verifikasi).

---

## 3. Verifikasi Sumber

**Terkonfirmasi dari source:**
- `role_permissions` penuh (9 role) — `HandleInertiaRequests.php`.
- `canX()` — `Traits/HasRoles.php` (users=owner, settings=owner, branch=owner, void/delete=owner+admin, assign=owner+admin+cs, cash_register=owner/admin/manager/cashier, deposit confirm=owner+admin, purchases=owner/admin/manager).
- Menu visibility per role — `menuItems`.

**Belum terkonfirmasi (Perlu Verifikasi):**
- Detail aksi halaman Dokumen, Servis Tools, Garansi, Checklist per role.
- Batas setoran kasir; guard "last owner"; kunci shift.
- Perilaku `reports`/`monitoring` untuk CS & Kasir pada tiap plan.
