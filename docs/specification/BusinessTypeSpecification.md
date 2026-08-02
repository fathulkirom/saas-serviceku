# ServiceKU — Business Type Specification

> Business type = **template onboarding tenant** (diatur saat pendaftaran/onboarding). Menentukan **fitur default** yang tersedia untuk tenant.
> Sumber: `app/Models/Tenant.php` → `getBusinessTypes()` & `getBusinessTypeFeatures()`.
> Keputusan arsitektur lebih lanjut: `docs/architecture-engine/BusinessTemplateEngine.md`.

---

## 1. Business Type Resmi (4)

| # | Business Type | Source Key | Deskripsi |
|---|---|---|---|
| 1 | **Retail** (Jualan Saja) | `retail_only` | Tidak menerima servis. Fokus POS & stok. |
| 2 | **Aksesoris + Service** | `aksesoris_service` | Toko aksesoris yang juga menerima servis (dilempar ke teknisi/partner). |
| 3 | **Pusat Service + Sparepart** | `full_service` | Servis & jual sparepart (service center penuh). |
| 4 | **HP/Laptop Baru + Service** | `gadget_full` | Gadget store (jual baru/second HP/Laptop/MacBook) + servis. |

> **Catatan source:** `Tenant::getBusinessTypes()` juga mendefinisikan nilai ke-5: `aksespare_service` → **"Aksesoris & Sparepart + Ada Teknisi"**. Nilai ini ada di source dan berperilaku sama dengan `full_service` (fitur penuh), namun **bukan business type resmi** pada spesifikasi ini. Jangan menambah business type baru tanpa melalui `docs/specification/PROJECT_SPECIFICATION.md`.

---

## 2. Fitur per Business Type (Sumber `getBusinessTypeFeatures`)

Feature keys: `services`, `customers`, `products`, `sales`, `reports`, `settings`, `monitoring`, `multi_branch`, `transfer_stock`, `users`, `expenses`, `purchases`, `deposits`, `checklist`, `indents`.

| Feature | Retail (`retail_only`) | Aksesoris+Service (`aksesoris_service`) | Pusat Service (`full_service`) | Gadget+Service (`gadget_full`) |
|---|---|---|---|---|
| services | ❌ | ✅ | ✅ | ✅ |
| checklist | ❌ | ✅ | ✅ | ✅ |
| customers | ✅ | ✅ | ✅ | ✅ |
| products | ✅ | ✅ | ✅ | ✅ |
| sales | ✅ | ✅ | ✅ | ✅ |
| reports | ✅ | ✅ | ✅ | ✅ |
| settings | ✅ | ✅ | ✅ | ✅ |
| monitoring | ✅ | ✅ | ✅ | ✅ |
| multi_branch | ✅ | ✅ | ✅ | ✅ |
| transfer_stock | ✅ | ✅ | ✅ | ✅ |
| users | ✅ | ✅ | ✅ | ✅ |
| expenses | ✅ | ✅ | ✅ | ✅ |
| purchases | ✅ | ✅ | ✅ | ✅ |
| deposits | ✅ | ✅ | ✅ | ✅ |
| indents | ✅ | ✅ | ✅ | ✅ |

> `aksespare_service` (nilai tambahan di source) memiliki set fitur yang sama dengan `full_service`.

---

## 3. Dampak Business Type

1. **Runtime filter**: business type menentukan fitur aktif via `CheckPlanFeature` + `getBusinessTypeFeatures` (bukan hanya saat onboarding).
2. **Modul yang hilang**: `retail_only` → modul **Service** & **Checklist** tidak tersedia.
3. **Template onboarding**: business type mempengaruhi menu/langkah awal (menu core, topbar, onboarding focus) — lihat `resources/js/Layouts` & `AuthenticatedLayout.vue`.
4. **Tidak menambah arsitektur**: business type **tidak** mengubah skema DB/migrasi; hanya memengaruhi fitur yang aktif.

---

## 4. Business Type vs Plan (Perbedaan)

| Aspek | Business Type | Plan (Subscription) |
|---|---|---|
| Apa yang diatur | Jenis bisnis / fitur domain | Batas pengguna, cabang, fitur premium |
| Contoh | `retail_only` → tanpa servis | `basic` → max 3 user, 1 cabang |
| Saat diatur | Onboarding (bisa diubah owner) | Dibeli/di-upgrade tenant |
| Pengaruh | Fitur domain modul | Batas kuantitatif + fitur (full/read_only/none) |

Keduanya dikombinasikan: **akses efektif = role ∩ plan feature ∩ business type** (3 lapis).

---

## 5. Verifikasi Sumber

**Terkonfirmasi:** 5 nilai `getBusinessTypes()` + set fitur per tipe (`getBusinessTypeFeatures()`); `retail_only` tanpa `services`/`checklist`.

**Perlu Verifikasi:** alur UI perubahan business type pasca-onboarding; apakah perubahan business type langsung mematikan/menyalakan modul di runtime untuk tenant aktif.
