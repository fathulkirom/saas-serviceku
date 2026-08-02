# 14 — Multi-Tenant Strategy

> **Sprint 6.2A · Blueprint Only.** Strategi isolasi data multi-tenant — scope data per domain.

---

## 1. Model Isolasi

**1 Database per Tenant** (`stancl/tenancy` v3 — arsitektur existing).

- Central DB: tenant registry, plan, voucher, platform payment, super admin.
- Tenant DB: semua data operasional (L2–L5).

---

## 2. Scope Data per Domain

| Domain | Scope | DB | Keterangan |
|---|---|---|---|
| **Tenant** | Global | Central | Registry tenant |
| **Plan** | Global | Central | Paket & fitur |
| **Voucher** | Global | Central | Kode voucher platform |
| **Platform Payment** | Global | Central | Midtrans, billing |
| **Super Admin** | Global | Central | Admin platform |
| **Branch** | Tenant | Tenant | Cabang milik tenant |
| **User** | Tenant | Tenant | User milik tenant |
| **Role** | Tenant | Tenant (target); seed dari central | Role kustom tenant |
| **Permission** | Global (registry) → dipakai tenant | Central → Tenant (cache) | Permission didaftarkan platform; tenant memakai |
| **Policy** | Tenant | Tenant | Aturan bisnis tenant |
| **Settings** | Tenant | Tenant | Pengaturan tenant |
| **Module** | Global (registry) → diaktifkan tenant | Central → Tenant | Module didaftarkan platform; tenant menyalakan |
| **Customer** | Tenant | Tenant | Pelanggan tenant |
| **Device** | Tenant | Tenant | Perangkat milik pelanggan tenant |
| **Supplier** | Tenant | Tenant | Pemasok tenant |
| **Partner** | Tenant | Tenant | Partner servis tenant |
| **Product** | Tenant | Tenant | Katalog tenant |
| **Request** | Tenant | Tenant | Request tenant |
| **Service Order** | Tenant | Tenant | Tiket tenant |
| **Sales Order** | Tenant | Tenant | Penjualan tenant |
| **Purchase Order** | Tenant | Tenant | Pembelian tenant |
| **Warranty** | Tenant | Tenant | Garansi tenant |
| **Cash / Deposit** | Tenant | Tenant | Kas tenant |
| **Inventory** | Tenant | Tenant | Stok tenant |
| **Finance** | Tenant | Tenant | Keuangan tenant |
| **Audit / History** | Tenant | Tenant | Log tenant |

---

## 3. Aturan

1. **Tidak ada query lintas tenant.** Setiap query harus di-scope `WHERE tenant_id = current_tenant()`.
2. **Central DB hanya diakses platform** — tenant tidak bisa query central DB.
3. **Permission registry** = central → disalin/di-cache ke tenant (tidak real-time query).
4. **Backup per tenant** — 1 file backup untuk 1 tenant (tidak bercampur).
5. **Super Admin dapat "Login Sebagai" tenant** — impersonasi untuk support.

---

## 4. Verifikasi

Konsisten dengan arsitektur existing (stancl/tenancy, 1 DB per tenant), `docs/Architecture.md` (Sprint 4).
