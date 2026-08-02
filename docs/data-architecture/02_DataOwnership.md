# 02 — Data Ownership

> **Sprint 6.2A · Blueprint Only.** Siapa pemilik data per domain. Ownership = siapa yang bertanggung jawab atas keberadaan, integritas, dan siklus hidup data.

---

## 1. Model Ownership

| Level | Pemilik | Deskripsi | Contoh |
|---|---|---|---|
| **Platform** | Super Admin | Data yang mengatur seluruh platform; tidak terikat tenant. | Tenant registry, Plan, Voucher platform, Payment platform |
| **Tenant** | Owner (tenant) | Data milik tenant; hanya tenant yang mengelola. | Branch, User, Policy, Settings, Customer, Device, semua L3/L4/L5 |
| **System** | Otomatis | Data yang dibuat otomatis oleh sistem (event, log). | Audit log, History, Report snapshot |

---

## 2. Ownership per Domain

| Domain | Layer | Pemilik data | Pengelola utama | Bisa diakses oleh |
|---|---|---|---|---|
| **Tenant** | L1 Platform | Super Admin | Super Admin | Super Admin, Owner (read pengaturan sendiri) |
| **Plan / Feature** | L1 Platform | Super Admin | Super Admin | Super Admin, Owner (read) |
| **Voucher** | L1 Platform | Super Admin | Super Admin | Super Admin |
| **Platform Payment** | L1 Platform | System | Super Admin | Super Admin |
| **Super Admin** | L1 Platform | Super Admin | Super Admin | Super Admin |
| **Branch** | L2 Config | Tenant | Owner | Owner, Admin, Manager |
| **User** | L2 Config | Tenant | Owner (`manage_users`) | Owner, User (profil sendiri) |
| **Role** | L2 Config | Tenant (target) / Platform (seed) | Owner (target) | Owner, Admin |
| **Permission** | L2 Config | Platform (registry) | — | Semua (read via role) |
| **Policy** | L2 Config | Tenant | Owner | Owner, Admin, Manager |
| **Settings** | L2 Config | Tenant | Owner (`manage_settings`) | Owner, Admin (read) |
| **Module (active)** | L2 Config | Tenant | Owner (on/off) / Platform (registry) | Semua (read) |
| **Customer** | L3 Master | Tenant | Owner/Admin/Manager/CS | CS, Admin, Manager, Owner |
| **Device** | L3 Master | Tenant | CS/Admin | CS, Admin, Manager, Owner, Teknisi |
| **Supplier** | L3 Master | Tenant | Owner/Admin/Manager | Admin, Manager, Owner |
| **Service Partner** | L3 Master | Tenant | Owner/Admin | Admin, Manager, Owner |
| **Product** | L3 Master | Tenant | Owner/Admin/Manager | Kasir (read), Teknisi (read) |
| **Request** | L4 Trans | Tenant | CS/Owner/Admin (buat); System/API (auto) | CS, Admin, Manager, Owner, Teknisi terkait |
| **Service Order** | L4 Trans | Tenant | CS/Admin (buat); Teknisi (kerja) | Semua role servis |
| **Sales Order** | L4 Trans | Tenant | Kasir/Owner/Admin (buat) | Kasir, Admin, Manager, Owner |
| **Purchase Order** | L4 Trans | Tenant | Owner/Admin/Manager | Admin, Manager, Owner |
| **Warranty / Claim** | L4 Trans | Tenant | CS/Admin (klaim) | CS, Admin, Manager, Owner |
| **Cash Shift / Deposit** | L4 Trans | Tenant | Kasir (shift); Owner/Admin (konfirmasi) | Kasir, Admin, Manager, Owner |
| **Inventory Movement** | L4 Trans | Tenant | System (auto dari transaksi); Owner/Admin (adjust) | Admin, Manager, Owner |
| **Finance** | L5 Agregat | Tenant | System (auto aggregate) | Owner, Manager, Admin |
| **Report** | L5 Agregat | Tenant | Owner/Admin/Manager | Owner, Manager, Admin |
| **Dashboard** | L5 Agregat | Tenant | Owner (widget, target) | Sesuai permission |
| **Audit Log** | L5 Log | System | System (auto) | Super Admin, Owner |
| **History Log** | L5 Log | System | System (auto) | Owner, Admin, Manager |

---

## 3. Aturan Ownership

1. **Tenant adalah pemilik data operasional** — tidak bisa dipindahkan, tidak bisa dibaca tenant lain.
2. **Platform adalah pemilik data platform** — tenant tidak bisa mengubah Plan/Feature registry.
3. **System adalah pemilik data log** — tidak bisa diedit/dihapus oleh siapa pun (append-only).
4. **Customer melihat data miliknya via proyeksi** (Customer Portal future) — bukan shared ownership.
5. Perubahan ownership (mis. merger tenant) = future case → butuh ADR baru.

---

## 4. Verifikasi

Konsisten dengan `docs/domain/Ownership.md` (Sprint 6.1), `docs/request-engine/05_RequestOwnership.md` (Sprint 6.1D), `docs/specification/PermissionMatrix.md` (Sprint 5.1).
