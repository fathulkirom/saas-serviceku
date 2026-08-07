# Service Module Test Matrix

> Verifikasi seluruh Service Module dengan Enterprise Platform.

---

## 🔄 Workflow Test Matrix (13 Statuses × 9 Roles)

| Status → Next | Owner | Admin | Manager | CS | Tech | Cashier | HeadStore | Courier |
|---------------|:-----:|:-----:|:-------:|:--:|:----:|:-------:|:---------:|:-------:|
| menunggu_alokasi → diterima | ✅ | ✅ | ✅ | ✅ | — | — | — | — |
| menunggu_alokasi → dikerjakan | ✅ | ✅ | ✅ | — | ✅ | — | — | — |
| menunggu_alokasi → indent | ✅ | ✅ | ✅ | ✅ | — | — | — | — |
| menunggu_alokasi → cancel | ✅ | ✅ | ✅ | ✅ | — | — | — | — |
| diterima → diagnosa | ✅ | ✅ | — | — | ✅ | — | — | — |
| diterima → dikerjakan | ✅ | ✅ | — | — | ✅ | — | — | — |
| diagnosa → dikerjakan | ✅ | ✅ | — | — | ✅ | — | — | — |
| diagnosa → indent | ✅ | ✅ | — | — | ✅ | — | — | — |
| dikerjakan → selesai | ✅ | ✅ | ✅ | — | ✅ | — | — | — |
| selesai → siap_diambil | ✅ | ✅ | ✅ | — | — | ✅ | — | — |
| siap_diambil → close | ✅ | ✅ | ✅ | — | — | ✅ | — | ✅ |

---

## 🏪 Business Type Test Matrix

| Feature | full_service | aksesoris | aksespare | gadget_full | retail_only |
|---------|:-----------:|:---------:|:---------:|:-----------:|:-----------:|
| Service List | ✅ | ✅ | ✅ | ✅ | ❌ |
| Service Create | ✅ | ✅ | ✅ | ✅ | ❌ |
| Service Workspace | ✅ | ✅ | ✅ | ✅ | ❌ |
| Diagnosis | ✅ | ✅ | ✅ | ✅ | ❌ |
| Technician Assignment | ✅ | ❌ | ✅ | ✅ | ❌ |
| Sparepart | ✅ | ✅ | ✅ | ✅ | ❌ |
| Warranty | ✅ | ✅ | ✅ | ✅ | ❌ |

---

## 🔐 Permission Test Matrix

| Action | Required Permission | Roles |
|--------|-------------------|-------|
| View Service | — | All |
| Create Service | — | CS, Owner, Admin, Manager |
| Edit Service | — | Owner, Admin, Manager |
| Assign Technician | `assign_technician` | Owner, Admin, CS |
| Work on Service | `work_on_services` | Technician, Owner, Admin |
| Manage Spareparts | `manage_products` | Owner, Admin, Manager, HeadStore |
| Create Invoice | `manage_sales` | Owner, Admin, Manager, Cashier |
| Manage Finance | `manage_finance` | Owner, Admin, Manager |
| Delete Service | `delete_models` | Owner, Admin |
| Void Transaction | `void_transactions` | Owner, Admin |

---

## 🚀 Performance Checklist

| Test | Expected |
|------|----------|
| Service List load < 1s | ✅ Pagination + eager loading |
| Workspace load < 2s | ✅ Partial reload + lazy tabs |
| Form autosave < 500ms | ✅ Debounced |
| Search response < 300ms | ✅ Debounced |
| 1000+ services in table | ✅ Virtual scroll ready |
| Photo upload < 5s | ✅ Chunked upload ready |

---

## ✅ Verification Checklist

- [x] Service List renders via EnterpriseDataTable
- [x] Service Create renders via FormRenderer
- [x] Service Workspace renders all 8 tabs
- [x] Workflow transitions work for all 9 roles
- [x] Automation fires on status change
- [x] Timeline shows workflow + automation events
- [x] Dashboard shows service metrics
- [x] Reports generate correct data
- [x] Export produces valid CSV
- [x] No hardcoded roles/permissions/statuses
- [x] Business type gates work (retail_only hides service)
- [x] Feature gates work (plan without services hides module)
- [x] Mobile responsive
