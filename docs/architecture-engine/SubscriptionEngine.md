# Subscription Engine — ServiceKU

> **Keputusan target:** subscription tidak hanya menentukan "fitur on/off" — ia menjadi **engine kontrol** atas banyak dimensi layanan: **Module · Limit · Feature · Storage · User · Branch · API · Backup · WhatsApp · Marketplace · AI**.
>
> ⚠️ **Target/roadmap.** Kondisi saat ini: plan = harga + trial + fitur (full/read_only/none) + batas users/branches (`PlanSeeder` + `CheckPlanFeature`).

---

## 1. Kondisi Saat Ini (source) vs Target

| Dimensi | Saat Ini (source) | Target |
|---|---|---|
| Fitur | full/read_only/none per feature key | ✅ (dipertahankan) + diperluas |
| User | max_users | ✅ (dipertahankan) |
| Branch | max_branches | ✅ (dipertahankan) |
| Module | — (lewat feature) | **Kontrol module per plan** (Module Engine) |
| Storage | — | Kuota file/foto (servis photos, dokumen) |
| API | — | Kuota & level API (rate limit, endpoint) |
| Backup | — | Jadwal & ketersediaan backup otomatis |
| WhatsApp | — | Fitur integrasi WhatsApp (notifikasi) |
| Marketplace | — | Akses marketplace/plugin |
| AI | — | Fitur AI assistant (jika tersedia) |

---

## 2. Dimensi Subscription (Target)

| Dimensi | Contoh nilai |
|---|---|
| Module | plan menetapkan modul aktif (mis. `multi_branch` hanya Pro+) |
| Limit | max_users, max_branches, max_storage, max_api_requests |
| Feature | full / read_only / none |
| Storage | kuota GB / jumlah file |
| User | jumlah user aktif |
| Branch | jumlah cabang |
| API | level API & rate limit |
| Backup | ada/tidak, frekuensi |
| WhatsApp | fitur notifikasi WA |
| Marketplace | akses modul marketplace/plugin |
| AI | akses AI assistant |

---

## 3. Matriks Plan (ringkas — lihat `docs/specification/SubscriptionSpecification.md`)

| Dimensi | Trial | Basic | Pro | Enterprise |
|---|---|---|---|---|
| Fitur core | services/customers/products full; sales/reports/settings/monitoring read_only | full | full | full |
| users | none | read_only | full | full |
| multi_branch / transfer_stock | none | none | full | full |
| Max users | 1 | 3 | 10 | lebih tinggi |
| Max branches | 1 | 1 | 5 | lebih tinggi |
| Storage / API / Backup / WA / Marketplace / AI | none | none/terbatas (PV) | terbatas (PV) | penuh (PV) |

> Dimensi baru (storage/API/backup/WA/marketplace/AI) adalah **target**; nilai pasti per plan **Perlu Verifikasi** (belum ada di source).

---

## 4. Aturan Subscription Engine

1. Subscription mengontrol **Module/Limit/Feature/Storage/User/Branch/API/Backup/WhatsApp/Marketplace/AI** secara terpusat.
2. Enforce di sisi server (middleware `check.plan.feature` + limit checks); UI hanya mencerminkan.
3. Status (`trial/active/expired/suspended`) mengunci/ membuka dimensi sesuai workflow (Subscription).
4. Perubahan plan diterapkan tanpa migrasi data (batas dihitung real-time).
5. Super Admin dapat override dari panel platform.

---

## 5. Verifikasi

Plan, harga, trial, fitur, batas users/branches terkonfirmasi dari `PlanSeeder`. Dimensi storage/API/backup/WA/marketplace/AI adalah **target/roadmap** — **Perlu Verifikasi** untuk nilai pastinya.
