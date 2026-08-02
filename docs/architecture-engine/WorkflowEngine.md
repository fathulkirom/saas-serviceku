# Workflow Engine — ServiceKU

> **Keputusan target:** workflow (state machine) didefinisikan **per modul**, bukan per business template. Status & transisi dikonfigurasi data sehingga dapat disesuaikan tanpa mengubah kode inti.
>
> ⚠️ **Target/roadmap.** Kondisi saat ini: status service hardcoded (14 status) di source + transisi ditangani per controller.

---

## 1. Kondisi Saat Ini (source) vs Target

| Aspek | Saat Ini (source) | Target |
|---|---|---|
| Definisi status | Enum/const di model (`Service::STATUS_*`) | Registry workflow per modul (data) |
| Transisi | Per aksi di controller | Definisi transisi terpusat: `from → action → to` |
| Izin transisi | Tersebar (permission per action) | Workflow mendeklarasikan permission per transisi |
| Penyesuaian | Ubah kode + deploy | Ubah konfigurasi (owner untuk beberapa modul; Super Admin) |

---

## 2. Workflow per Modul (Target)

| Modul | Workflow | Sumber status saat ini |
|---|---|---|
| **Service** | menunggu_alokasi → diterima → diagnosa → dikerjakan → menunggu_konfirmasi_pelanggan/internal → siap_diambil → selesai → diambil/close (plus indent, onpartner, cancel, void) | 14 status (source) |
| **POS/Sales** | draft → selesai → pending → success/failed/expired → refunded/void | 5 status payment (source) |
| **Purchase** | draft → terima → hutang → bayar → close | (source) |
| **Inventory** | masuk → tersedia → terpakai/transfer/reorder → adjustment | (source) |
| **Warranty** | aktif → klaim → diterima/ditolak | (source, sebagian) |
| **Subscription** | trial → active → expired/suspended → perpanjang | 4 status (source) |

Detail transisi: `docs/specification/WorkflowSpecification.md`.

---

## 3. Struktur Workflow (Target)

```php
Workflow {
    module: string
    states: []            // daftar status
    transitions: []       // { from, action, to, permission }
    onEnter: []           // side-effect (notifikasi, log, foto)
}
```

- Setiap transisi memiliki `permission` → otomatis divalidasi Permission Engine.
- `onEnter` menyediakan hook (notifikasi pelanggan, activity log, auto-assign).

---

## 4. Aturan Workflow Engine

1. Workflow **mengikuti modul**, bukan business template (perbedaan utama dengan kondisi saat ini yang terikat business type).
2. Status inti (service 14, payment 5, subscription 4) tidak dikurangi — hanya dipindahkan ke definisi data.
3. Workflow default sama untuk semua tenant; owner tidak mengubah transisi inti (Perlu Verifikasi — target: hanya Super Admin / config).
4. UI stepper digeneralisasi dari definisi workflow (konsisten dengan `KDialog`/komponen).

---

## 5. Verifikasi

Status & alur utama (service, payment, subscription, tenant) terkonfirmasi dari source. Konsep definisi workflow terpusat per modul adalah **target/roadmap**.
