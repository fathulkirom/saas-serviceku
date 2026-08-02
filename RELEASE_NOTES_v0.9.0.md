# RELEASE NOTES — ServiceKU v0.9.0-beta

**Release Date**: 2026-08-02  
**Status**: FEATURE COMPLETE  
**Type**: Beta (Internal / Early Adopter)

---

## 🎉 What's New

ServiceKU v0.9.0-beta is the **first feature-complete milestone** of the multi-tenant ERP SaaS for electronics service businesses. All operational modules (Customer, Service, Inventory, POS, Dashboard) are implemented and frozen.

### Core Capabilities
- ✅ Multi-tenant architecture (1 DB per tenant, subdomain resolution)
- ✅ 5 business types: Full Service, Aksesoris+Service, Aksesoris+Sparepart+Teknisi, Gadget Full, Retail Only
- ✅ Role-based access: Owner, Admin, Manager, CS, Technician, Cashier, Courier, Warehouse
- ✅ Dynamic feature resolution per tenant (FeatureEngine)
- ✅ Plan-based feature gating (subscription tiers)

### Modules Delivered
| Module | Status | Key Features |
|--------|--------|-------------|
| Customer Engine | ✅ | 360° view, interactions, tags, segments, communications, notes, complaints |
| Service Intake | ✅ | Checklist, snapshot, device matching, condition confirmation |
| Technician Workflow | ✅ | Diagnosis, quotation, approval, part request, QC, repair tracking |
| Delivery & Pickup | ✅ | Ready notification, payment verification, pickup, warranty |
| Warranty & After Sales | ✅ | Claims, reopen, exception handling, revision tracking |
| Inventory Intelligence | ✅ | Stock movement, locations, POs, serials, opname, transfers |
| Part Flow | ✅ | Request → Approve → Use → Return (real service center flow) |
| Retail POS | ✅ | Cashier shift, payment, discounts, bundles, price levels, returns |
| Operational Control | ✅ | Kanban, pickup queue, approval center, SLA monitoring, load balancer |
| Production Hardening | ✅ | 20+ indexes, optimistic locking, stock integrity guards |
| Data Migration | ✅ | CSV import, preview, rollback, queue for large datasets |
| Setup Assistant | ✅ | Dynamic checklist, health check, welcome card, permission-based |

---

## ⚠️ Critical Issues — MUST FIX Before Production

| ID | Severity | Issue | Impact |
|----|----------|-------|--------|
| **D1** | 🔴 CRITICAL | Duplicate `Supplier` class in `InventoryModels.php` + `Supplier.php` | PHP fatal error on autoload |
| **D2** | 🔴 CRITICAL | Duplicate `RepairStarted` event in `DailyOpsEvents.php` + `TechnicianWorkflowEvents.php` | PHP fatal error |
| **M3** | 🔴 CRITICAL | Missing `CustomerMerged` event referenced by `Customer::merge()` | Runtime class-not-found |
| **FE1** | 🔴 CRITICAL | Missing Vue pages for `Requests` module (`Requests/Index`, `Create`, `Show`) | Inertia render error |

## ⚠️ Warnings — Should Fix

| ID | Severity | Issue |
|----|----------|-------|
| **S1** | 🟡 WARNING | `ProviderAdapter::send()` calls `sendMessage()` — WhatsAppService method is `send()` |
| **C1** | 🟡 WARNING | `ServiceWorkflowController` has 17 methods (exceeds 15 threshold) |
| **P1** | 🟡 WARNING | 6 Vue pages >500 lines (Inventaris, Keuangan, Pengaturan, Sistem, ServisTools, Dokumen) |
| **ST1** | 🟡 WARNING | Inconsistent status enum patterns across models |
| **D4** | 🟡 WARNING | Route name `login` collision between central and tenant routes |

---

## 📊 Project Statistics

| Metric | Count |
|--------|-------|
| Tenant Models | ~107 |
| Central Models | 11 |
| Controllers | 72 |
| Services | 19 |
| Events | ~75 |
| Jobs | 5 |
| Middleware | 11 |
| Policies | 13 |
| Vue Pages | 77 |
| Vue Components | 45 |
| Composables | 5 |
| Routes (tenant) | ~200+ |
| Database Tables | ~80+ |
| Business Types | 5 |
| Roles | 9 |
| Permissions | 15+ |

---

## 🔜 Next Development

| Sprint | Focus | Priority |
|--------|-------|----------|
| **7.6** | Finance & Accounting | HIGH |
| **7.7** | BI & Reporting | HIGH |
| **8.0** | Super Admin Platform | MEDIUM |
| **8.1** | Marketplace & Integrations | MEDIUM |
| **8.2** | Mobile Companion | LOW |

---

## 📦 Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --tenants
php artisan db:seed
npm run build
```

---

## 🔒 Security

- All tenant data is isolated per database
- PermissionEngine gates all controller actions
- FeatureEngine resolves per-tenant feature access
- Optimistic locking prevents concurrent edit conflicts
- Policy-based authorization on all CRUD operations
- Security headers middleware active
- Rate limiting on login endpoints

---

## 📄 License

Proprietary. All rights reserved.
