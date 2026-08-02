# 04 — Request Channel

> **Sprint 6.1D · Architecture Freeze · Blueprint Only.**
> Katalog **Request Type** (apa permintaannya), **Request Source** (dari mana/siapa), dan **Channel** (bagaimana masuk).
> Prinsip: Channel baru = tambah value, bukan tambah tabel → **Grow Without Migration**.

---

## 1. Request Type (Apa permintaannya?)

| # | Type | Deskripsi | Status lifecycle | Domain turunan (fork) |
|---|---|---|---|---|
| 1 | `walk_in` | Customer datang langsung ke toko. | created→assigned→processing→completed→closed | ServiceOrder / SalesOrder |
| 2 | `pickup` | Customer minta dijemput; device diambil dari alamat. | created→scheduled→waiting_pickup→picked_up→received→assigned→processing→completed→delivered→closed | ServiceOrder + PickupTask + DeliveryTask |
| 3 | `home_service` | Teknisi datang ke rumah customer; servis di tempat. | created→scheduled→assigned→processing→completed→closed | ServiceOrder (on-site) |
| 4 | `courier` | Device dikirim via kurir/logistik (jarak jauh). | created→scheduled→waiting_pickup→picked_up→in_transit→received→assigned→processing→completed→delivered→closed | ServiceOrder + PickupTask + DeliveryTask |
| 5 | `corporate` | Perusahaan mengirim banyak device sekaligus. | created→scheduled→assigned→processing→completed→delivered→closed | N ServiceOrder (batch) |
| 6 | `booking` | Janji waktu/appointment (tanpa device fisik dulu). | created→scheduled→assigned→processing→completed→closed | Booking → (nanti) ServiceOrder |
| 7 | `whatsapp` | Customer request lewat WhatsApp. | created→assigned→processing→completed→closed | ServiceOrder / SalesOrder |
| 8 | `marketplace` | Order dari marketplace (Tokopedia/Shopee/dll). | created→assigned→processing→completed→delivered→closed | SalesOrder / ServiceOrder |
| 9 | `api` | Request dari Public API (integrasi eksternal). | created→assigned→processing→completed→closed | ServiceOrder / SalesOrder / Warranty |
| 10 | `warranty_claim` | Customer datang klaim garansi. | created→assigned→processing(resolved)→completed→closed | Warranty Claim |
| F1 | `mobile_app` | Future: customer app (self-service). | TBD | ServiceOrder / Booking |
| F2 | `subscription_service` | Future: servis rutin/berlangganan. | TBD | ServiceOrder (recurring) |
| F3 | `maintenance_contract` | Future: kontrak perawatan korporat. | TBD | ServiceOrder (scheduled) |

---

## 2. Request Source (Dari mana/siapa?)

| # | Source | Deskripsi | Contoh |
|---|---|---|---|
| 1 | `customer` | Pelanggan langsung. | Walk In, Telepon, WhatsApp dari pelanggan |
| 2 | `cs` | Customer Service (front desk). | CS membuatkan tiket untuk walk-in |
| 3 | `owner` | Pemilik toko. | Owner memasukkan request manual |
| 4 | `admin` | Admin toko. | Admin membuat request dari dashboard |
| 5 | `marketplace` | Platform marketplace. | Order dari Tokopedia/Shopee |
| 6 | `whatsapp_bot` | Bot WhatsApp otomatis. | Auto-reply → buat request |
| 7 | `api_client` | Aplikasi eksternal via API. | Website booking, mobile app, ERP lain |
| 8 | `system` | Otomatis sistem. | Subscription renewal, auto-reorder, reminder garansi |

---

## 3. Channel (Bagaimana masuk?)

| # | Channel | Deskripsi | Contoh |
|---|---|---|---|
| 1 | `store` | Di dalam toko (fisik). | Walk-in ke counter CS |
| 2 | `phone` | Telepon. | Customer telepon → CS buatkan request |
| 3 | `whatsapp` | WhatsApp (manual / bot). | Chat ke nomor toko |
| 4 | `website` | Website toko / booking page. | Form booking online |
| 5 | `marketplace` | Marketplace integration. | Order masuk dari marketplace |
| 6 | `public_api` | API publik ServiceKU. | Aplikasi pihak ketiga |
| 7 | `admin_panel` | Dashboard admin/CS/Owner. | Manual entry oleh staf |

---

## 4. Matriks Type × Source × Channel

| Type | Source utama | Channel utama |
|---|---|---|
| walk_in | customer, cs | store |
| pickup | customer, cs | phone, whatsapp, website |
| home_service | customer, cs | phone, whatsapp, website |
| courier | customer, cs | phone, whatsapp, website |
| corporate | customer (perusahaan), cs, owner | phone, website, admin_panel |
| booking | customer | website, whatsapp, phone |
| whatsapp | customer, whatsapp_bot | whatsapp |
| marketplace | marketplace | marketplace |
| api | api_client | public_api |
| warranty_claim | customer, system | store, phone, whatsapp |

---

## 5. Aturan Channel

1. **Type & Source & Channel adalah data** — bukan enum hardcoded di kode. Didaftarkan di Request Engine.
2. Channel baru (mis. `mobile_app`, `iot_device`) = **tambah value di registry** — tidak mengubah struktur inti.
3. Setiap type **memilih subset lifecycle** (tidak dipaksa melewati status yang tidak relevan).
4. `source` menentukan permission & audit trail — tapi request tetap dimiliki tenant (lihat `05_RequestOwnership.md`).
5. `channel` menentukan notifikasi & SLA default — mis. WhatsApp = fast response, Corporate = scheduled SLA.

---

## 6. Prinsip yang Dipenuhi

| Prinsip | Cara |
|---|---|
| Simple by Default | Walk-in = type paling sederhana (5 status). |
| Progressive Complexity | Pickup/Courier/Corporate/Marketplace = status tambahan, hanya bila tenant menggunakan. |
| Grow Without Migration | Channel baru = data (registry), bukan skema baru. |
| Configuration over Code | Perilaku per channel = konfigurasi (SLA, notifikasi, policy), bukan if-else. |
| Business Driven | Channel mencerminkan realita bisnis servis Indonesia (WA, marketplace, telepon, walk-in). |

---

## 7. Verifikasi

Semua channel saat ini (walk-in, phone, WhatsApp, marketplace) sudah diakomodasi. `CustomerVisit` yang lama menjadi sub-kasus dari `type=walk_in`. `Booking` adalah type tersendiri. Selaras dengan Business Reality (BR-001 s.d. BR-020).
