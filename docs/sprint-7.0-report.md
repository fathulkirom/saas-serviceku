# Sprint 7.0 — Existing Architecture Audit & Implementation Roadmap

> **Sprint 7.0 · Implementation Phase.** Audit menyeluruh source code existing terhadap Blueprint v1.0 (FROZEN).
> **Prinsip:** "Refactor, Don't Rewrite." Pertahankan yang baik; catat gap; migrasi bertahap.

---

## 1. Tech Stack Audit

| Komponen | Existing | Blueprint Target | Status |
|---|---|---|---|
| **PHP** | 8.5.8 | ^8.2 | ✅ Melebihi target |
| **Laravel** | ^12.0 | ^12.0 | ✅ Tepat |
| **Vue** | ^3.5.40 | ^3.x | ✅ Tepat |
| **Inertia.js** | (via package.json) | 3.x | ✅ |
| **Tailwind CSS** | ^3.4.13 | 3.4 | ✅ Tepat |
| **Vite** | ^6.0.11 | 6.x | ✅ Tepat |
| **Multi-Tenant** | stancl/tenancy ^3.10 | ^3.x | ✅ Tepat |
| **Auth (SPA)** | Sanctum ^4.3 | ✅ | ✅ |
| **Auth (Social)** | Socialite ^5.29 | ✅ | ✅ |
| **2FA** | google2fa (existing) | ✅ | ✅ |
| **Payment** | PaymentGatewayService (Midtrans) | ✅ Ada |
| **PDF** | dompdf ^3.1 | ✅ | ✅ |
| **WhatsApp** | WhatsAppService | ✅ Ada |
| **Google Drive** | GoogleDriveService | ✅ Ada |
| **WebSocket** | Reverb ^1.11 | ✅ | ✅ |

**Verdict: Tech stack 100% aligned. No changes needed.**

---

## 2. Application Structure Audit

| Aspek | Existing | Blueprint Target (Sprint 6.3) | Gap |
|---|---|---|---|
| **Architecture** | Flat: `app/Models/`, `app/Http/Controllers/` | 4-layer: Domain→Application→Infrastructure→Presentation | 🔴 Major gap |
| **Domain Models** | 60+ Eloquent models in `app/Models/Tenant/` | Domain aggregate roots + value objects | 🟡 Needs separation |
| **Controllers** | 54 controllers (mostly Tenant\) | Thin controllers + Action pattern | 🟡 Business logic likely in controllers |
| **Actions** | ❌ Tidak ada | Use case pattern | 🔴 Missing |
| **DTOs** | ❌ Tidak ada | Data transfer objects | 🔴 Missing |
| **Repositories** | ❌ Tidak ada (Eloquent langsung) | Interface + Implementation | 🔴 Missing |
| **Services** | 6 services (feature flag, GDrive, mail, payment, WA) | Domain services + infrastructure providers | 🟡 Partial |
| **Events** | 1 event (ServiceStatusUpdated) | Full domain event catalog (20+) | 🔴 Major gap |
| **Listeners** | ❌ Tidak ada | Audit, Notification, Dashboard, History | 🔴 Missing |
| **Jobs** | 2 jobs (InvoicePdf, InvoiceEmail) | 8+ job types | 🟡 Needs expansion |
| **Policies** | 13 policies | 17+ policies (all aggregate roots) | 🟡 4 policies missing |
| **Middleware** | 9 middleware | 9+ (good) | ✅ Adequate |
| **FormRequests** | Mixed (some controllers have inline validation) | Dedicated FormRequest classes | 🟡 Partial |

---

## 3. Domain Coverage vs Blueprint (52 Tables)

### ✅ SUDAH ADA (Aligned)

| Blueprint Table | Existing Model | Status |
|---|---|---|
| `tenants` | `App\Models\Tenant` | ✅ |
| `plans` | `App\Models\Plan` | ✅ |
| `plan_features` | (dalam Plan model/seeder) | 🟡 Partial |
| `vouchers` | `App\Models\Voucher` | ✅ |
| `platform_payments` | `App\Models\Payment` | ✅ |
| `super_admins` | `App\Models\User` (central) | ✅ |
| `branches` | `App\Models\Tenant\Branch` | ✅ |
| `users` | `App\Models\Tenant\User` | ✅ (role = string column) |
| `customers` | `App\Models\Tenant\Customer` | ✅ |
| `devices` | (dalam Customer / Service) | 🟡 Belum tabel terpisah |
| `suppliers` | `App\Models\Tenant\Supplier` | ✅ |
| `service_partners` | `App\Models\Tenant\PartnerTeknisi` | ✅ (rename) |
| `products` | `App\Models\Tenant\Product` | ✅ |
| `service_orders` | `App\Models\Tenant\Service` | ✅ (rename ke ServiceOrder) |
| `sales_orders` | `App\Models\Tenant\Sale` | ✅ (rename) |
| `sale_items` | `App\Models\Tenant\SaleItem` | ✅ |
| `purchase_orders` | `App\Models\Tenant\Purchase` | ✅ (rename) |
| `purchase_items` | `App\Models\Tenant\PurchaseItem` | ✅ |
| `cash_shifts` | `App\Models\Tenant\Shift` | ✅ (rename) |
| `deposits` | `App\Models\Tenant\DailyDeposit` | ✅ |
| `expenses` | `App\Models\Tenant\Expense` | ✅ |
| `inventory_items` | (in Product / InventoryMutation) | 🟡 Needs separation |
| `inventory_movements` | `App\Models\Tenant\InventoryMutation` | ✅ (rename) |
| `attachments` | `App\Models\Tenant\ServicePhoto` (only service) | 🟡 Needs polymorphic generalization |
| `customer_visits` | (implicit in Service creation) | 🟡 Legacy — not needed for new entry |
| `tenant_settings` | `App\Models\Tenant\TenantSetting` + `SystemSetting` | ✅ |
| `provider_credentials` | `WaGatewayConfig`, `GoogleDriveToken`, partial di settings | 🟡 Needs unification |
| `notifications` | (via Laravel Notification) | 🟡 Not persisted to DB |
| `audit_logs` | `App\Models\Tenant\ActivityLog` | 🟡 Partial — needs expansion |
| `subscriptions` | Kolom di `tenants` (subscription_status, ends_at) | 🟡 Not separate table |
| `commission` | `App\Models\Tenant\Commission` | ✅ (ahead of blueprint!) |
| `pickup_delivery` | `App\Models\Tenant\PickupDelivery` | ✅ (ahead of blueprint!) |

### ❌ BELUM ADA (Major Gaps)

| Blueprint Table | Priority | Impact |
|---|---|---|
| `requests` | 🔴 P0 Critical | ADR-001 core entry point — seluruh alur baru bergantung ini |
| `request_devices` | 🔴 P0 Critical | Multi-device support |
| `request_history` | 🔴 P0 Critical | Audit trail request |
| `roles` | 🔴 P0 Critical | Role = tabel, bukan string kolom |
| `permissions` | 🔴 P0 Critical | Permission = tabel, bukan array di middleware |
| `role_permission` | 🔴 P0 Critical | Pivot role↔permission |
| `user_role` | 🟠 P1 Target | Pivot user↔role (saat ini kolom `role`) |
| `positions` | 🟡 P2 Target | Struktur organisasi |
| `policies` | 🔴 P0 Critical | Policy engine — aturan bisnis sebagai data |
| `warranties` | 🟠 P1 | Garansi pasca-servis |
| `warranty_claims` | 🟠 P1 | Klaim garansi |
| `suplier_claims` | 🟡 P2 Target | Klaim ke supplier |
| `replacements` | 🟡 P2 Target | Barang pengganti |
| `compensations` | 🟡 P2 Target | Kompensasi (Commission sudah ada) |
| `finance_transactions` | 🟠 P1 | Agregat keuangan |
| `history_logs` | 🟠 P1 | Change log data master |
| `subscription_history` | 🟡 P2 | Riwayat subscription |
| `module_activations` | 🟡 P2 Target | Modul per tenant |
| `dashboard_widgets` | 🟡 P2 | Widget dashboard |
| `report_snapshots` | 🟡 P2 | Cache laporan |

---

## 4. Authorization Audit

### Current State: ⚠️ Hardcoded Role Checks

```php
// HasRoles.php — CURRENT (anti-pattern per Blueprint)
public function canManageUsers(): bool { return $this->isOwner(); }
public function canVoidTransaction(): bool { return $this->isOwner() || $this->isAdmin(); }
```

**Masalah:**
1. Pengecekan role hardcoded (`isOwner()`, `isAdmin()`) — melanggar "Permission over Role".
2. `role_permissions` array di `HandleInertiaRequests.php` — bukan data di DB.
3. Tidak ada `can()` berbasis permission key (`'service.void'`).
4. Role kustom tidak mungkin (kolom string).

### Target: Permission-based (Sprint 6.3 §13)

```php
// Target: Policy-based authorization
Gate::authorize('cancel', $request);  // → RequestPolicy::cancel()
$user->can('service.void');           // → permission check
```

**Migration path:** Tambah tabel `permissions` + `role_permission` (additive); `HasRoles` tetap ada untuk backward compatibility sementara.

---

## 5. Frontend Audit

| Aspek | Status |
|---|---|
| **K* Components** | ✅ Sudah dimigrasi di Sprint 3b — 15 komponen |
| **Service UI** | ✅ Komponen Services/ lengkap (ServiceHeader, ActionBar, StatusStepper, dll.) |
| **Vue Pages** | 60+ halaman — coverage bagus |
| **Request UI** | ❌ Belum ada — ADR-001 belum diimplementasikan |
| **Companion Mode** | ❌ Belum ada (QRScanner page exists though) |
| **Provider Settings UI** | ❌ Belum ada |

---

## 6. Gap Classification

| Klasifikasi | Jumlah | Item |
|---|---|---|
| ✅ Sudah Sesuai Blueprint | **30+** | Tech stack, K* components, 30+ models, Policies, middleware |
| 🟡 Perlu Refactor Ringan | **15** | Rename models, unify provider_credentials, separate devices table, expand ActivityLog→AuditLog |
| 🔴 Perlu Refactor Besar | **12** | Role/Permission→tabel, Policy engine, Request engine (ADR-001), Warranty, Compensation, Event architecture |
| ❌ Harus Dibuat Baru | **10** | `requests`, `request_devices`, `warranties`, `warranty_claims`, `policies`, `roles`, `permissions`, `finance_transactions`, `history_logs`, `notifications` |

---

## 7. Refactoring Roadmap — 6 Phase

### PHASE 1 — Platform Foundation (Sprint 7.1–7.3) 🔴 P0

| Task | Priority | Detail |
|---|---|---|
| **1.1 Permission Engine** | P0 Critical | `permissions` table, `roles` table, `role_permission` pivot, seed 7 roles + permissions. Backward compat: keep `role` column + `HasRoles` trait. |
| **1.2 Policy-Based Authorization** | P0 | Update 13 policies to use `can('permission')`. Remove hardcoded `isOwner()` checks gradually. |
| **1.3 Module Registry** | P1 | `module_activations` table. Module on/off per tenant. |
| **1.4 Feature Flag Engine** | P1 | `FeatureFlagService` → data-driven. Plan features from DB. |
| **1.5 Subscription Table** | P1 | Extract subscription from `tenants` columns to `subscriptions` + `subscription_history`. |
| **1.6 Provider Credentials** | P1 | Unify `WaGatewayConfig`, `GoogleDriveToken`, settings → `provider_credentials` polymorphic. |
| **1.7 Settings Unification** | P2 | Consolidate `TenantSetting` + `SystemSetting`. |

### PHASE 2 — Customer Engine (Sprint 7.4) 🟠 P1

| Task | Priority |
|---|---|
| **2.1 Devices Table** | P1 — Separate `devices` from implicit Customer data |
| **2.2 Attachments Polymorphic** | P1 — Generalize `ServicePhoto` → `attachments` morph |
| **2.3 Customer History** | P2 — `history_logs` for customer changes |

### PHASE 3 — Request Engine (Sprint 7.5) 🔴 P0 CRITICAL

| Task | Priority |
|---|---|
| **3.1 `requests` Table** | P0 — ADR-001 core |
| **3.2 `request_devices` Pivot** | P0 — Multi-device |
| **3.3 `request_history` Append-Only** | P0 — Audit trail |
| **3.4 Request Actions** | P0 — CreateRequest, AssignRequest, ForkToServiceOrder |
| **3.5 Request API + Vue Pages** | P0 — UI untuk Request |

### PHASE 4 — Service Engine (Sprint 7.6) 🟠 P1

| Task | Priority |
|---|---|
| **4.1 Refactor Service→ServiceOrder** | P1 — Rename model; add `request_id` FK |
| **4.2 Work Orders** | P2 — Optional child table |
| **4.3 Warranty Tables** | P1 — `warranties` + `warranty_claims` |
| **4.4 Policy Engine** | P1 — `policies` table; warranty/compensation rules |

### PHASE 5 — Inventory & Finance (Sprint 7.7) 🟠 P1

| Task | Priority |
|---|---|
| **5.1 Inventory Items** | P1 — Separate `inventory_items` from Product |
| **5.2 Finance Transactions** | P1 — Aggregate table |
| **5.3 Commission→Compensation** | P2 — Generalize Commission |

### PHASE 6 — Post-Sale & Dashboard (Sprint 7.8) 🟡 P2

| Task | Priority |
|---|---|
| **6.1 Suplier Claims + Replacements** | P2 |
| **6.2 Dashboard Widgets** | P2 |
| **6.3 Report Snapshots** | P2 |

---

## 8. Technical Debt Catalog

| Debt | Severity | Fix |
|---|---|---|
| Hardcoded role checks (`isOwner()`, `isAdmin()`) | 🔴 High | Permission tables + Policy refactor (Phase 1) |
| `role_permissions` array in middleware | 🔴 High | DB table (Phase 1) |
| No `requests` table — Service created directly | 🔴 Critical | ADR-001 implementation (Phase 3) |
| ServicePhoto only for service domain | 🟡 Medium | Polymorphic attachments (Phase 2) |
| Subscription status in `tenants` columns | 🟡 Medium | Separate table (Phase 1) |
| No domain events (only 1 event) | 🟡 Medium | Event catalog (ongoing) |
| Business logic in controllers | 🟡 Medium | Action pattern (ongoing) |
| `activity_logs` limited scope | 🟢 Low | Expand to full audit_logs (Phase 1) |
| Mixed naming (Service vs ServiceOrder) | 🟢 Low | Gradual rename |

---

## 9. Risk Analysis — Implementation

| Risk | Level | Mitigation |
|---|---|---|
| ADR-001 breaks existing flow | 🔴 Critical | `request_id` NULLABLE; Service tetap bisa dibuat langsung (legacy mode) |
| Migration data loss | 🔴 Critical | Semua FK additive; soft delete; backup before migration |
| Performance impact (Request table) | 🟡 Medium | Proper indexes dari Table Blueprint |
| Developer confusion (old vs new structure) | 🟡 Medium | `app/Domain/` parallel ke `app/Models/Tenant/`; gradual migration |
| Vue pages break during refactor | 🟢 Low | K* components already migrated; pages refactored one by one |

---

## 10. Sprint 7.1 Implementation Plan

### Scope: Phase 1 — Platform Foundation (Permission Engine + Policy)

| Deliverable | Detail |
|---|---|
| **Migration** | `permissions`, `roles` (seed 7 roles), `role_permission`, `user_role` (optional, additive) |
| **Seeder** | `RoleAndPermissionSeeder` — 7 roles + ~70 permissions |
| **Model** | `Role`, `Permission` (Domain layer skeleton) |
| **Policy Refactor** | Update `ServicePolicy`, `SalePolicy`, dll. → `can('permission.key')` |
| **Auth Update** | `HandleInertiaRequests` → baca dari DB tables (fallback: existing array) |
| **Feature Test** | Permission check untuk setiap role |
| **Unit Test** | Role model, Permission model |

---

## 11. KESIMPULAN

> ### SERVICEKU SIAP MEMASUKI IMPLEMENTATION PHASE ✅
>
> **Audit selesai.** 60+ file model, 54 controller, 60+ Vue pages, 366 route tenant — codebase sudah besar dan berfungsi.
>
> **Gap utama:** ADR-001 (Request engine), Permission engine (role = tabel), Policy engine, Warranty.
>
> **Strategi:** "Refactor, Don't Rewrite." Struktur existing dipertahankan. Tabel baru = additive (FK nullable). Permission = tabel baru, backward compatible dengan `HasRoles` trait.
>
> **Sprint 7.1 fokus:** Permission Engine + Policy Refactor — fondasi untuk semua engine berikutnya.

---

## 12. Verifikasi

Audit dilakukan terhadap seluruh source code existing (`app/`, `resources/js/`, `routes/`, `config/`). Tidak ada file yang diubah pada sprint ini — murni audit.
