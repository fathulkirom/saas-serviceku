# Feature Engine — ServiceKU

> **Keputusan target:** fitur dapat **di-toggle tanpa perubahan kode** (tanpa deploy). Feature flag adalah data, bukan konstanta di kode.
>
> ⚠️ **Target/roadmap.** Kondisi saat ini: fitur dikendalikan `CheckPlanFeature` (plan) + `getBusinessTypeFeatures` (business type) — tersebar, dan mengubah logika fitur membutuhkan edit kode.

---

## 1. Kondisi Saat Ini (source) vs Target

| Aspek | Saat Ini (source) | Target |
|---|---|---|
| Sumber toggle | `check.plan.feature` + business type (dua tempat) | **Feature Registry** tunggal |
| Perubahan | Edit kode + deploy | Ubah data (Super Admin; beberapa oleh owner) |
| Cakupan | full/read_only/none per plan | Global / per-tenant / per-plan / A/B |
| Ketergantungan | Manual | Deklarasi dependency modul/fitur |

---

## 2. Feature Registry (Target)

```php
Feature {
    key: string        // 'multi_branch', 'transfer_stock', 'ai_assistant', ...
    module: string     // modul pemilik
    type: 'boolean' | 'tier' | 'limit'
    default: mixed
    scopes: ['global', 'tenant', 'plan']
}
```

- `boolean` → on/off (mis. `transfer_stock`).
- `tier` → full/read_only/none (kompatibel `CheckPlanFeature`).
- `limit` → nilai numerik (max_users, max_branches, storage).

---

## 3. Resolusi (Target)

```
value(feature, tenant) = tenant.override ?? plan[feature] ?? global_default
```

- Urutan: override tenant > plan > global default.
- Semua lapis (role/permission, plan, feature) dihitung bersama di **satu resolver** — tidak tersebar.

---

## 4. Aturan Feature Engine

1. Fitur baru wajib didaftarkan di registry sebelum dipakai di kode/UI.
2. Toggle berdampak: route, menu, permission, dan UI sekaligus (tidak setengah).
3. Migrasi dari kondisi saat ini: petakan `planFeature` & `getBusinessTypeFeatures` ke registry (default data).
4. Tidak menambah business type/role resmi baru (tetap sesuai spesifikasi).

---

## 5. Verifikasi

Mekanisme `CheckPlanFeature` + `getBusinessTypeFeatures` adalah kondisi **saat ini** (source). Registry terpusat & toggle tanpa deploy adalah **target/roadmap**.
