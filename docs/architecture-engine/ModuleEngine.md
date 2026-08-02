# Module Engine — ServiceKU

> **Keputusan target:** platform = kumpulan **modul independen** yang terdaftar di Module Registry. Setiap modul memiliki identitas, fitur plan-nya, permission-nya, routes-nya, dan menu-nya sendiri.

---

## 1. Kondisi Saat Ini (source) vs Target

| Aspek | Saat Ini (source) | Target |
|---|---|---|
| Definisi modul | Tersebar: feature keys di `CheckPlanFeature`, menu di `AuthenticatedLayout`, route di `tenant.php` | **Module Registry** terpusat (`modules.php`) |
| Aktivasi | `getBusinessTypeFeatures` + plan feature | Modul dinonaktifkan = route+menu+permission otomatis mati |
| Penambahan modul | Edit banyak file manual | Daftarkan sekali di registry |

---

## 2. Struktur Module (Target)

```php
Module {
    key: string          // 'service', 'pos', 'inventory', ...
    name: string         // 'Service', 'Penjualan (POS)', ...
    icon: string
    planFeature: string  // 'services', 'sales', ...
    permissions: []      // permission yang dimiliki modul
    routes: string[]     // prefix route
    menus: []            // menu item & posisi
    status: 'active' | 'future' | 'disabled'
    requires: []         // modul yang wajib aktif (mis. POS butuh Inventory)
}
```

---

## 3. Daftar Modul (Aktif — saat ini) & Future

### Modul Aktif (sudah ada di source)
`dashboard`, `service`, `customer`, `pos` (sales), `purchase`, `inventory`, `cashier` (kas), `deposit` (setoran), `finance`, `report`, `monitoring`, `user` (user/role), `branch` (cabang), `subscription`, `settings`, `document` (SOP/KB/QuickReply), `service_tools`, `indent`, `supplier`, `checklist`, `warranty` (garansi), `global_search`, `tenant_admin` (platform).

Detail per modul: `docs/specification/ModuleSpecification.md`.

### Modul Future (BELUM ada — tidak boleh dianggap aktif)
`crm`, `accounting`, `hrd` (payroll penuh), `marketing`, `marketplace`, `plugin_system`, `public_api`, `webhook`, `automation`, `ai_assistant`.

---

## 4. Aturan Module Engine

1. Modul baru **wajib** terdaftar di registry sebelum routes/menu ditulis.
2. Setiap modul mendefinisikan `planFeature` → otomatis di-gate `CheckPlanFeature` (full/read_only/none).
3. Permission modul otomatis tersedia untuk Role Engine & Permission Engine.
4. Modul `disabled`/plan `none` → route diblokir, menu disembunyikan, permission tidak dihitung.
5. Dependency antar modul dideklarasikan (`requires`) — tidak boleh modul berjalan tanpa dependensinya.

---

## 5. Verifikasi

Modul aktif sesuai source (`routes/tenant.php`, controller, halaman). Konsep registry & status `future` adalah **target/roadmap** — belum ada di source.
