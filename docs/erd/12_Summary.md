# 12 — ERD Summary

> **Sprint 6.2C · Conceptual Blueprint.** Ringkasan & kesimpulan akhir — verdict untuk melanjutkan ke Sprint 6.2D (Table Blueprint).

---

## 1. Yang Telah Ditetapkan

| # | Dokumen | Isi |
|---|---|---|
| 1 | `01_ERDConcept.md` | 5-layer entity architecture; 52 entity; origin trace `request_id` |
| 2 | `02_AggregateMapping.md` | 30 Aggregate Root + child tables + invariant |
| 3 | `03_Relationship.md` | 31 relationship — semua dengan justifikasi Business Reality |
| 4 | `04_Cardinality.md` | 40 kardinalitas + alasan bisnis (termasuk N:M Device↔Request) |
| 5 | `05_DomainOwnership.md` | Ownership matrix: Platform/Tenant/Branch + role |
| 6 | `06_RequestFlow.md` | Origin trace; fork points; cascade rules |
| 7 | `07_EntityResponsibility.md` | 33 entity — mengapa ada, lifecycle, ownership, wajib/opsional, future |
| 8 | `08_Invariant.md` | 23 invariant (data integrity + bisnis) |
| 9 | `09_BusinessRealityValidation.md` | 18/18 Business Reality LOLOS; 7/7 Quality Test LOLOS |
| 10 | `10_ERDDecisionLog.md` | 13 keputusan (12 FINAL, 1 TARGET) |
| 11 | `11_FutureExpansion.md` | 12 perluasan siap; 9 perluasan butuh entity baru (additive) |
| 12 | `12_Summary.md` | Dokumen ini |

---

## 2. Statistik ERD

| Metrik | Nilai |
|---|---|
| Total entity (tabel konseptual) | **52** |
| Central DB entity | 6 |
| Tenant DB entity | 46 |
| Aggregate Roots | 30 |
| Pivot tables | 4 (`request_devices`, `user_role`, `role_permission`, `branch_cluster` future) |
| Polymorphic tables | 2 (`attachments`, `notifications`) |
| Relationship | 31 (semua dengan justifikasi bisnis) |
| Invariant | 23 |
| Business Reality lolos | 18/18 |

---

## 3. Keputusan Desain Kunci

| Keputusan | Detail |
|---|---|
| **Request = entry point** | `requests.id` → FK `request_id` di seluruh transaksional (ADR-001) |
| **Device↔Request N:M** | Pivot `request_devices` (BR-019, corporate, repeat repair) |
| **Request→ServiceOrder 1:N** | Fondasi enterprise; UI sederhana untuk toko kecil |
| **Inventory append-only** | `inventory_movements` — tidak update qty langsung |
| **Attachments polymorphic** | Satu tabel untuk semua domain |
| **User↔Role N:M** | Pivot `user_role` (target) |
| **Policy versioning** | `valid_from`/`valid_to` — data historis tetap valid |
| **Amount dalam sen** | Bigint; hindari FLOAT |
| **Soft delete** | Semua transaksional — tidak hard delete |
| **1 DB per tenant** | Central DB untuk platform; Tenant DB untuk operasional |

---

## 4. Prinsip yang Dipenuhi

| Prinsip | Bukti |
|---|---|
| Business Driven | Semua relationship punya justifikasi BR |
| Simple by Default | Toko kecil = 1 device, 1 service order. UI sederhana. |
| Progressive Complexity | 1:N/N:M = fondasi; UI menyembunyikan kompleksitas |
| Grow Without Migration | Pivot, kolom baru, entity baru = additive |
| Configuration over Code | Policy, provider credential, module, permission = data |
| Data Is Sacred | Soft delete, append-only movement, immutable request_id |
| Tenant Data Isolation | 1 DB per tenant + scope query |
| No Single Point Of Failure | Multi-owner, delegation (target) |

---

## 5. KESIMPULAN

> ### SPRINT 6.2D (TABLE BLUEPRINT) BOLEH DIMULAI ✅
>
> Enterprise Conceptual ERD telah menetapkan **seluruh fondasi data** ServiceKU:
> - **52 entity** dalam 5-layer architecture.
> - **31 relationship** — setiap relationship lahir dari Business Reality, bukan kemudahan.
> - **Origin trace `request_id`** (ADR-001) di seluruh tabel transaksional.
> - **Device↔Request N:M** memungkinkan multi-device visit & corporate batch.
> - **Inventory append-only** — Data Is Sacred.
> - **Siap 10 tahun** — semua perluasan bersifat additive (tabel/kolom/pivot baru).

### Ketentuan Sprint 6.2D (Table Blueprint):
1. Definisikan setiap tabel dengan kolom, tipe, constraint.
2. Ikuti konvensi `docs/data-architecture/18_DataStandards.md`.
3. FK `request_id` nullable di `service_orders`, `sales_orders`.
4. Pivot `request_devices` — device_id + request_id.
5. Pivot `user_role` — target (saat ini: kolom `role` di `users`).
6. `inventory_movements` sebagai append-only.
7. `attachments` polymorphic.
8. Semua tabel L3/L4/L5 memiliki `deleted_at`.

### ADR Candidate (jika ada Business Reality baru):
- **Tidak ada.** Core Domain di-freeze. Tidak ada perubahan domain yang diperlukan berdasarkan analisis ERD ini.

---

## 6. Verifikasi

Selaras dengan seluruh Sprint 4–6.2B. `git status` hanya `?? docs/erd/` — **tidak ada file sumber yang berubah**. Murni conceptual blueprint.
