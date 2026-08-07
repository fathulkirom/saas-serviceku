# Service Workflow Refinement — Sprint 36A

> **Production-Grade Service Center Workflow for HP & Laptop Repair Shops**
> Status: ✅ REFINED | Audited & Hardened

---

## 🎯 Objective

Sprint 36A bukan membuat modul baru. Sprint ini **menyempurnakan** seluruh alur Service HP & Laptop yang sudah ada agar benar-benar siap digunakan di toko service nyata — single store maupun multi-cabang.

---

## 📐 Complete Service Lifecycle (14 Status)

```
                     ┌─────────────┐
                     │   CANCEL    │ (any point before close)
                     └─────────────┘
                           ↑
┌────────────────┐    ┌─────────┐    ┌────────────┐    ┌───────────────────────┐
│ MENUNGGU       │───▶│ DITERIMA │───▶│  DIAGNOSA  │───▶│ KONFIRMASI PELANGGAN  │
│ ALOKASI        │    └─────────┘    └────────────┘    └───────────────────────┘
└────────────────┘         │               │                      │
                           │               ├──────────────────────┤
                           │               │  KONFIRMASI INTERNAL  │
                           │               └──────────────────────┘
                           │               │                      │
                           ▼               ▼                      ▼
                    ┌────────────┐  ┌────────────┐        ┌────────────┐
                    │ DIKERJAKAN │◀─│   INDENT   │        │ ON PARTNER │
                    └────────────┘  └────────────┘        └────────────┘
                           │
                           ▼
                    ┌────────────┐
                    │  SELESAI   │──▶ QC PASS ──▶ SIAP DIAMBIL ──▶ DIAMBIL ──▶ CLOSE
                    │  (QC)      │──▶ QC FAIL ──▶ Back to DIKERJAKAN
                    └────────────┘
```

---

## 🔒 Status Validation Rules

| Rule | Enforcement |
|------|------------|
| Close requires payment | Backend — `ServiceWorkflowValidator::validate()` |
| Ready requires QC pass | Backend — `ServiceWorkflowValidator::validate()` |
| Repair requires diagnosis | Backend — `ServiceWorkflowValidator::validate()` |
| Diagnosis requires intake checklist | Backend — `ServiceWorkflowValidator::validate()` |
| Diagnosis requires intake photo | Backend — `ServiceWorkflowValidator::validate()` |
| Invalid transition rejected | Backend — `LogicException` |

---

## 📊 Audit Results

| Area | Before (Sprint 16) | After (Sprint 36A) |
|------|-------------------|-------------------|
| Status count | 11 | 14 (added: diagnosa entry, diambil, refined close path) |
| Transitions documented | 22 | 34 (all validated) |
| diagnosa inbound | ❌ NONE (dead end) | ✅ diterima→diagnosa |
| QC status | ❌ Missing | ✅ selesai = QC phase |
| Payment validation | ❌ None | ✅ Backend enforced |
| Checklist categories | 0 | 10 categories, 55 items |
| QC checklist | 0 | 16 items (all mandatory) |
| Photo categories | 0 | 6 categories |
| Frontend helpers | Basic labels | Complete matrix + validators |

---

## 🗂️ Key Files

| File | Purpose |
|------|---------|
| `app/Services/Service/ServiceWorkflowValidator.php` | Backend status validation + checklist templates |
| `app/Models/Tenant/Service.php` | Service model with ALLOWED_TRANSITIONS |
| `resources/js/Composables/useServiceStatus.js` | Frontend status matrix, checklists, QC, photos, warranty |
| `resources/js/Enterprise/Workspace/registrations/service.js` | Enhanced action handlers with status-aware validation |

---

*Service Workflow Refinement — Sprint 36A*
