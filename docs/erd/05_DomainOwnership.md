# 05 — Domain Ownership

> **Sprint 6.2C · Conceptual Blueprint.** Siapa pemilik tiap entity — scope (Platform/Tenant/Branch), role pengelola, dan aturan akses.
> Dari Sprint 6.2A `02_DataOwnership.md`.

---

## 1. Ownership Matrix

| Entity | DB | Scope | Pengelola utama | Bisa diakses oleh |
|---|---|---|---|---|
| tenants | Central | Global | Super Admin | Super Admin; Owner (read sendiri) |
| plans | Central | Global | Super Admin | Super Admin; Owner (read) |
| vouchers | Central | Global | Super Admin | Super Admin |
| platform_payments | Central | Global | System | Super Admin |
| super_admins | Central | Global | Super Admin | Super Admin |
| branches | Tenant | Tenant | Owner (`manage_branches`) | Owner, Admin, Manager |
| users | Tenant | Tenant | Owner (`manage_users`) | Owner; User (profil sendiri) |
| roles | Tenant | Tenant | Owner (target); Platform (seed) | Owner, Admin |
| permissions | Tenant | Tenant (registry cache) | Platform (source) | Semua (read via role) |
| policies | Tenant | Tenant | Owner | Owner, Admin, Manager |
| tenant_settings | Tenant | Tenant | Owner (`manage_settings`) | Owner, Admin (read) |
| provider_credentials | Tenant | Tenant | Owner | Owner only |
| module_activations | Tenant | Tenant | Owner | Semua (read) |
| customers | Tenant | Tenant | Owner/Admin/Manager/CS | CS, Admin, Manager, Owner |
| devices | Tenant | Tenant | CS/Admin | Semua role servis |
| suppliers | Tenant | Tenant | Owner/Admin/Manager | Admin, Manager, Owner |
| service_partners | Tenant | Tenant | Owner/Admin | Admin, Manager, Owner |
| products | Tenant | Tenant | Owner/Admin/Manager | Kasir (read), Teknisi (read) |
| requests | Tenant | Tenant + Branch | CS/Owner/Admin/System/API | Semua role servis |
| service_orders | Tenant | Branch | CS/Admin (buat); Teknisi (kerja) | Semua role servis |
| sales_orders | Tenant | Branch | Kasir/Owner/Admin | Kasir, Admin, Manager, Owner |
| purchase_orders | Tenant | Tenant | Owner/Admin/Manager | Admin, Manager, Owner |
| cash_shifts | Tenant | Branch | Kasir | Kasir, Admin, Manager, Owner |
| inventory_items | Tenant | Branch | System (auto); Owner/Admin (adjust) | Admin, Manager, Owner |
| warranties | Tenant | Tenant | CS/Admin | Semua role servis |
| compensations | Tenant | Tenant | Owner/Admin/Manager | Owner, Manager, Admin |
| subscriptions | Central | Global | Owner (bayar); Super Admin (override) | Owner, Super Admin |
| audit_logs | Tenant | Tenant | System (auto) | Owner, Super Admin |
| notifications | Tenant | Tenant | System (auto) | User terkait |

---

## 2. Aturan

1. **Tenant = pemilik data operasional** — tidak bisa dibaca tenant lain.
2. **Platform = pemilik data platform** — tenant tidak bisa mengubah Plan/Feature registry.
3. **System = pemilik log** — audit & history append-only, tidak bisa diedit user.
4. **Customer melihat via proyeksi** — Customer Portal (future) = read-only data miliknya.

---

## 3. Verifikasi

Konsisten dengan `docs/data-architecture/02_DataOwnership.md` (Sprint 6.2A), `docs/domain/Ownership.md` (Sprint 6.1), `docs/request-engine/05_RequestOwnership.md` (Sprint 6.1D).
