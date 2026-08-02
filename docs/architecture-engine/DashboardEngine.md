# Dashboard Engine — ServiceKU

> **Keputusan target:** dashboard dibangun berdasarkan **permission**, bukan nama role. Komponen/widget dashboard adalah unit modular yang tampil bila user memiliki permission terkait.
>
> ⚠️ **Target/roadmap.** Kondisi saat ini: dashboard menampilkan widget berdasarkan role & plan (dengan beberapa pengecualian role).

---

## 1. Kondisi Saat Ini (source) vs Target

| Aspek | Saat Ini (source) | Target |
|---|---|---|
| Dasar penentuan widget | Campuran role/plan/feature | **Permission-based**: setiap widget punya permission |
| Dashboard user | Satu layout dashboard | **Widget registry**; user melihat widget yang diizinkan |
| Role khusus | Beberapa widget khusus role | Tidak ada logika `if role` — semua via permission |
| Super Admin | Panel terpisah | Tetap terpisah (platform), bukan dashboard tenant |

---

## 2. Widget Registry (Target)

```php
DashboardWidget {
    key: string        // 'revenue_chart', 'active_services', 'low_stock', ...
    permission: string // 'finance.manage' | 'service.read' | ...
    module: string     // modul pemilik widget
    render: Component
    order: int
}
```

### Contoh pemetaan

| Widget | Permission | Role yang melihat (implied) |
|---|---|---|
| Pendapatan & grafik | `finance.manage` | owner, manager, admin, head_store |
| Servis aktif / status | `service.read` | role dengan akses service |
| Stok menipis | `product.manage` | owner, manager, admin |
| Penjualan hari ini | `pos.read` | owner, manager, admin, kasir |
| Setoran belum konfirmasi | `deposit.manage` | owner, manager, admin |
| Aktivitas monitoring | `monitoring.*` | owner, manager, admin |
| Quick actions | permission per aksi | sesuai permission |

---

## 3. Aturan Dashboard Engine

1. **Tidak ada** `if role === 'x'` dalam komposisi dashboard.
2. Widget = komponen independen; layout diatur via registry + permission.
3. Widget kosong/0 tetap tampil dengan EmptyState (konsisten `docs/product/Interaction.md`).
4. Onboarding focus mode tetap didukung (data kosong → panduan setup).
5. Kinerja: dashboard ringan; grafik memakai data agregat (lihat `Scalability.md`).

---

## 4. Verifikasi

Widget saat ini (pendapatan, servis aktif, stok, dll) sesuai source; mekanisme permission-based adalah **target/roadmap**.
