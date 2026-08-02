# Business Template Engine — ServiceKU

> **Keputusan target:** business type menjadi **template onboarding** — menentukan konfigurasi awal tenant (modul default, role default, menu, alur setup) **hanya saat setup**, bukan gate akses runtime.
>
> ⚠️ **Target/roadmap.** Kondisi saat ini: business type memengaruhi fitur runtime (`Tenant::getBusinessTypeFeatures` + `CheckPlanFeature`).

---

## 1. Kondisi Saat Ini (source) vs Target

| Aspek | Saat Ini (source) | Target |
|---|---|---|
| Waktu pengaruh | Runtime (fitur dihitung terus dari business type) | **Onboarding** (template diterapkan saat setup; setelah itu modul diatur bebas) |
| Mekanisme | `getBusinessTypeFeatures()` → matikan modul | Template = snapshot modul/role/menu yang diterapkan saat pembuatan tenant |
| Perubahan bisnis | Mengubah business type = mengubah fitur otomatis | Mengubah template = hanya saran; owner mengelola modul sesuai kebutuhan |

---

## 2. Business Template (Target)

```php
BusinessTemplate {
    key: string            // 'retail', 'aksesoris_service', 'full_service', 'gadget_full'
    name: string
    defaultModules: []     // modul yang aktif saat onboarding
    defaultRoles: []       // role yang dibuat otomatis
    defaultMenus: []       // susunan menu awal
    onboardingSteps: []    // langkah setup
}
```

### Pemetaan Template ↔ Business Type resmi (source key)

| Template (resmi) | Source key | Default Modules | Catatan |
|---|---|---|---|
| **Retail** | `retail_only` | pos, inventory, customer, finance, report, … (tanpa service/checklist) | Tidak menerima servis |
| **Aksesoris + Service** | `aksesoris_service` | + service, checklist, indent | Servis dilempar |
| **Pusat Service + Sparepart** | `full_service` | + service, checklist, indent, warranty | Service center penuh |
| **HP/Laptop Baru + Service** | `gadget_full` | + service, checklist, indent, warranty | Gadget store |

> `aksespare_service` (nilai tambahan di source) dapat dipetakan sebagai template setara `full_service` bila tenant membutuhkannya — bukan template resmi baru.

---

## 3. Alur (Target)

1. Tenant registrasi → pilih business template.
2. Template menyalin default modules/roles/menus ke konfigurasi tenant.
3. Owner bebas menambah/mematikan modul setelahnya (Module Engine).
4. Business template **tidak lagi** menjadi gate akses runtime.

---

## 4. Aturan Business Template Engine

1. Template hanya berlaku saat onboarding; perubahan bisnis → owner menyesuaikan modul, bukan "migrasi business type".
2. 4 template resmi (Retail, Aksesoris+Service, Pusat Service+Sparepart, HP/Laptop Baru+Service). Jangan menambah template baru (PROJECT_SPECIFICATION §7).
3. Migrasi dari kondisi saat ini: `getBusinessTypeFeatures` → inisialisasi template (bukan runtime check).
4. Tetap kompatibel dengan plan feature (Subscription Engine) — dua mekanisme berbeda.

---

## 5. Verifikasi

Kondisi saat ini (`getBusinessTypes`, `getBusinessTypeFeatures` — runtime gate) terkonfirmasi dari source. Konsep template onboarding adalah **target/roadmap**.
