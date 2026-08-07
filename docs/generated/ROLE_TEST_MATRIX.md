# Role Test Matrix — Sprint 36E (RC1)

> Complete role-based access control verification.

---

## 🎭 Role Definitions

| # | Role | Key | Description |
|---|------|-----|-------------|
| 1 | Super Admin | `super_admin` | Platform-level admin (all tenants) |
| 2 | Owner | `owner` | Business owner (full access) |
| 3 | Manager | `manager` | Operational manager |
| 4 | Admin | `admin` | Administrative staff |
| 5 | CS | `cs` | Customer service (front desk) |
| 6 | Teknisi | `technician` | Repair technician |
| 7 | Kasir | `cashier` | Cashier |
| 8 | Courier | `courier` | Delivery/pickup |
| 9 | Head Store | `head_store` | Store supervisor |

---

## 📊 Access Matrix

| Feature | Super Admin | Owner | Manager | Admin | CS | Teknisi | Kasir | Courier | Head Store |
|---------|:----------:|:-----:|:-------:|:-----:|:--:|:------:|:-----:|:-------:|:----------:|
| **Dashboard** | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ | ✅ |
| **Service** | | | | | | | | | |
| View All | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ |
| Create | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ |
| Assign Tech | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ |
| Diagnose | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Repair | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| QC | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Cancel | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| **Inventory** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| **Sales/POS** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ | ❌ | ✅ |
| **Purchasing** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **CRM** | ✅ | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ✅ |
| **Finance** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **HRM** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |
| **Reports** | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ✅ |
| **Settings** | ✅ | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ | ❌ |

---

## 🔐 Permission Verification Checklist

- [ ] Super Admin can access all tenants and platform settings
- [ ] Owner cannot access other tenants' data
- [ ] CS cannot access finance reports
- [ ] Teknisi can only see assigned services
- [ ] Kasir cannot modify service status
- [ ] Courier can only see delivery-related data
- [ ] Head Store has branch-scoped access
- [ ] Custom roles respect assigned permissions
- [ ] Feature flags disable menu items correctly
- [ ] Business type restrictions apply correctly

---

*Role Test Matrix — Sprint 36E*
