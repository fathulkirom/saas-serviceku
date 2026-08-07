# Service Module Deprecation List

> **Sprint 16.0** — Components ready for removal in Sprint 17.0+.

---

## 📦 Safe to Deprecate (Redirect Route)

| File | Route | Redirect To |
|------|-------|-------------|
| `Pages/CsDashboard.vue` | `dashboard` (role=cs) | `Dashboard.vue` (unified) |
| `Pages/CashierDashboard.vue` | `dashboard` (role=cashier) | `Dashboard.vue` (unified) |
| `Pages/TechnicianDashboard.vue` | `dashboard` (role=technician) | `Dashboard.vue` (unified) |
| `Pages/CourierDashboard.vue` | `dashboard` (role=courier) | `Dashboard.vue` (unified) |
| `Pages/Services/Show.vue` | `services.show` | `ServiceWorkspace/Index.vue` |

---

## ⚠️ Keep Active (Other Modules Depend)

| Component | Modules Using |
|-----------|--------------|
| `Components/KTable.vue` | Inventory, Finance, CRM, Purchasing |
| `Components/StatCard.vue` | CsDashboard, CashierDashboard (until redirected) |
| `Components/KCard.vue` | Settings, Profile, Reports |
| `Components/KButton.vue` | All legacy action buttons |
| `Components/KInput.vue` | All legacy form inputs |
| `Components/Badge.vue` | All status badges |
| `Components/Drawer.vue` | Base for KDrawer + SkDrawer |
| `Components/KDialog.vue` | Legacy modals |
| `Components/EmptyState.vue` | Legacy empty states |
| `Components/Pagination.vue` | Legacy pagination |

---

## 🔒 NEVER DELETE (Foundation)

| Component | Reason |
|-----------|--------|
| `Components/Icons.js` | Global icon registry |
| `Components/Toast.vue` | Global notification system |
| `Components/Logo.vue` | Branding |
| `Components/ThemeSwitcher.vue` | Theme toggle |
| `Layouts/AuthenticatedLayout.vue` | App shell |
| `Layouts/GuestLayout.vue` | Auth shell |
| `Layouts/Themes/*` | Layout theme components |

---

## 📊 Deprecation Timeline

| Sprint | Action |
|--------|--------|
| **16.0** | Identify + document deprecated items |
| **17.0** | Redirect routes, remove deprecated pages |
| **18.0** | Remove deprecated components (after confirming no usage) |
| **19.0** | Full cleanup |

---

*Service Module Deprecation — Sprint 16.0*
