# 10 — Request Summary

> **Sprint 6.1D · Architecture Freeze · Blueprint Only.**
> Ringkasan akhir — kesimpulan, status freeze, dan rekomendasi untuk Sprint 6.2.

---

## 1. Apa yang Berubah?

**Sebelum Sprint 6.1D:**
```
Customer → CustomerVisit → ServiceOrder
         ↘ SalesOrder (walk-in retail, tanpa visit)
```

**Setelah Sprint 6.1D (ADR-001):**
```
Customer → REQUEST → Device(s) → [FORK]
                                    ├── ServiceOrder
                                    ├── SalesOrder
                                    ├── Warranty Claim
                                    ├── Booking
                                    ├── PickupTask
                                    └── DeliveryTask
```

**Perubahan fundamental:**
- `CustomerVisit` **didepresiasi** sebagai entry point. Fungsinya digantikan oleh `Request(type=walk_in)`.
- Request adalah **satu-satunya funnel** — semua channel masuk lewat sini.
- Domain turunan (ServiceOrder, SalesOrder, Warranty) tidak lagi dibuat langsung — wajib melalui Request.
- Origin trace tunggal (`request_id`) di seluruh domain turunan.

---

## 2. Hasil Validasi

| Validasi | Hasil |
|---|---|
| 20 Business Reality (BR-001..020) | **20/20** ✅ |
| Checklist operasional (17 item) | **17/17** ✅ |
| Prinsip domain (11 prinsip) | **11/11** ✅ |
| Regresi (gangguan ke domain existing) | **0** |
| Perluasan masa depan (marketplace, API, mobile app, dll) | **Semua siap** tanpa perubahan fondasi |

---

## 3. Status Freeze

| Komponen | Status |
|---|---|
| Request sebagai Core Entry Point | ✅ **FREEZE** — ADR-001 ACCEPTED |
| Request Lifecycle (14 status) | ✅ **FREEZE** |
| Request Type catalog (10 + 3 future) | ✅ **FREEZE** (additive diperbolehkan) |
| Request Relationship (fork model) | ✅ **FREEZE** |
| Request Ownership (3 lapis) | ✅ **FREEZE** |
| RequestHistory (append-only) | ✅ **FREEZE** |
| `request_id` origin trace | ✅ **FREEZE** |

---

## 4. Keputusan Final

> ### ADR-001 ACCEPTED ✅
>
> **Request adalah Core Entry Point tunggal ServiceKU.**
>
> Semua interaksi operasional — walk-in, pickup, home service, courier, corporate, booking, WhatsApp, marketplace, API, warranty claim — WAJIB dimulai sebagai Request sebelum di-fork ke domain turunan.

---

## 5. Sprint 6.2 (Enterprise ERD Blueprint)

> ### BOLEH DIMULAI ✅
>
> Dengan ketentuan:
> 1. Tabel `requests` adalah **salah satu tabel inti pertama** dalam ERD.
> 2. `requests.id` menjadi FK (`request_id`) di `service_orders`, `sales_orders`, `warranty_claims`.
> 3. `request_id` bersifat **nullable** untuk data existing (backward compatible).
> 4. Tabel `request_history` — append-only audit trail.
> 5. Channel/type/source = tabel lookup/data (bukan enum hardcoded).
> 6. Lifecycle Request = 14 status (Workflow Engine).
> 7. PickupTask & DeliveryTask (opsional, P1 — target).
> 8. Perubahan dokumen domain (Sprint 6.1) sesuai dampak ADR-001 (CustomerVisit didepresiasi, relasi diperbarui).

---

## 6. Impact ke Dokumen Existing

| Dokumen | Perubahan yang diperlukan |
|---|---|
| `docs/domain/CoreDomain.md` | Tambah Request sebagai domain level atas; depresiasi CustomerVisit |
| `docs/domain/DomainRelationship.md` | Perbarui relasi: Customer→Request→Device→Domain turunan |
| `docs/domain/Entity.md` | Tambah entity Request; tandai CustomerVisit sebagai legacy |
| `docs/domain/Aggregate.md` | Tambah Request aggregate root |
| `docs/domain/DomainLifecycle.md` | Tambah lifecycle Request |
| `docs/domain/Engine.md` | Tambah Request Engine |
| `docs/domain/Ownership.md` | Tambah ownership Request (tenant/user/customer) |
| `docs/domain/FutureExpansion.md` | Sesuaikan — Request sebagai fondasi perluasan channel |
| `docs/domain-validation/ArchitectureAdjustment.md` | Tambah ADJ-017 (Request Engine) |
| `docs/specification/PROJECT_SPECIFICATION.md` | Perbarui domain hierarchy |

> Revisi dokumen dilakukan **sebelum atau bersamaan dengan Sprint 6.2** — bukan pada sprint ini (6.1D fokus pada Architecture Freeze).

---

## 7. Verifikasi Akhir

- ✅ 10 dokumen dibuat di `docs/request-engine/`.
- ✅ ADR-001 formal (problem, decision, alternatives, consequences, trade-offs, impact, future).
- ✅ 14 keputusan tercatat (11 FINAL, 2 TARGET, 1 DEFERRED, 0 REJECTED).
- ✅ Seluruh Business Reality, checklist, dan prinsip tervalidasi.
- ✅ Tidak ada kode, DB, migration, ERD, API, controller, atau Vue yang dibuat.
- ✅ Architecture Freeze — struktur Request tidak boleh berubah tanpa ADR baru.

---

## 8. Penutup

> **"Jangan mendesain hanya untuk kondisi saat ini. Desainlah agar tetap relevan 10 tahun ke depan tanpa perlu mengubah fondasi."**
>
> Request Engine menjawab tantangan ini: **satu funnel, semua channel, tumbuh tanpa migrasi.**

**Sprint 6.1D selesai. Sprint 6.2 (Enterprise ERD Blueprint) BOLEH DIMULAI.**
