# 07 — Request Future Expansion

> **Sprint 6.1D · Architecture Freeze · Blueprint Only.**
> Kemampuan Request menangani perluasan di masa depan **tanpa mengubah fondasi**.
> Prinsip: Channel/type baru = tambah value di registry + (opsional) modul baru. Tidak ada migrasi skema.

---

## 1. Perluasan yang Diuji

| # | Perluasan | Status | Mekanisme |
|---|---|---|---|
| 1 | **Marketplace** (Tokopedia/Shopee) | ✅ Siap | type=marketplace + modul integrasi marketplace. Sudah ada di channel catalog. |
| 2 | **Public API** | ✅ Siap | type=api + source=api_client + rate limit (Subscription Engine). Sudah ada di channel catalog. |
| 3 | **Mobile App** (customer self-service) | ✅ Siap | type baru `mobile_app` + source=customer. Request Lifecycle status minimal (created→assigned→processing→completed→closed). |
| 4 | **Home Service** | ✅ Siap | type=home_service sudah ada di channel catalog. Modul Home Service = add-on. |
| 5 | **Corporate Contract** | ✅ Siap | type=corporate sudah ada. Corporate Contract = modul yang menambah SLA + billing bulanan. Request tidak berubah. |
| 6 | **Maintenance Contract** | ✅ Siap | Subscription + scheduled Request (type=booking atau type baru `maintenance`). Recurring request auto-generated oleh System. |
| 7 | **Subscription Service** (servis rutin) | ✅ Siap | Auto-generate Request berkala oleh System; type=subscription_service (future). |
| 8 | **Appointment / Queue** | ✅ Siap | type=booking + scheduled_at + QueueSystem (future module). |
| 9 | **Customer Portal** | ✅ Siap | Customer melihat Request mereka (projection); customer buat Request (source=customer, channel=website/mobile_app). |
| 10 | **IoT / Smart Device** | ✅ Siap | Device mengirim request otomatis (source=system, channel=public_api). |
| 11 | **AI Auto-classify** | ✅ Siap | Request masuk → AI menentukan type/priority/assign. Request Engine menerima hasil klasifikasi sebagai input. |
| 12 | **Multi-language / Multi-currency** | ✅ Siap | Request tidak terikat bahasa/mata uang; itu adalah konfigurasi tenant. |

---

## 2. Yang Tidak Berubah Saat Perluasan

| Komponen | Alasan |
|---|---|
| Request Engine | Menerima type/source/channel baru sebagai data. |
| Request Lifecycle | Status yang ada mencakup semua perluasan. Paling banyak menambah 1-2 status baru (additive). |
| Request Ownership | Model tiga lapis (tenant/user/customer) tetap. |
| Request Relationship | Fork ke domain turunan tetap. |
| Domain turunan (ServiceOrder, SalesOrder, Warranty) | Tidak berubah — hanya dipanggil dari Request baru. |

---

## 3. Kapasitas Request — Uji 10 Tahun

> **Tesis:** Jika Request tidak mampu menampung channel baru dalam 10 tahun tanpa mengubah fondasi, maka desainnya salah.

| Channel masa depan yang mungkin | Ditangani tanpa perubahan fondasi? |
|---|---|
| Customer Mobile App | ✅ type baru `mobile_app` |
| WhatsApp Business API resmi | ✅ type=whatsapp, via modul integrasi |
| Chatbot AI (ChatGPT/LLM) | ✅ source=chatbot, type disimpulkan AI |
| Marketplace baru (TikTok Shop, Lazada) | ✅ type=marketplace + modul integrasi |
| IoT — device rusak auto-request | ✅ source=system + type=api |
| Blockchain-based warranty claim | ✅ type=warranty_claim, tidak berubah |
| VR/AR remote diagnostics | ✅ type baru `remote_diag`, fork ke ServiceOrder |
| Drone pickup/delivery | ✅ type= courier + modul logistik; PickupTask/DeliveryTask. |
| Multi-tenant franchise (sub-tenant) | ✅ Request scope tenant; franchise = tenant hierarchy (future). |
| Self-service kiosk in-store | ✅ type=walk_in, source=kiosk, channel=store. |

**Kesimpulan: Request mampu menangani perluasan 10 tahun ke depan tanpa mengubah fondasi.**

---

## 4. Batasan yang Disengaja

| Batasan | Alasan |
|---|---|
| Request tidak menangani non-operasional (chat, marketing, survey) | Itu domain terpisah (CRM/Customer Engagement — future). |
| Request tidak menangani payment/billing langsung | Payment ada di domain turunan (SalesOrder/Subscription). |
| Request tidak menyimpan data servis detail | Detail ada di ServiceOrder (biaya, part, checklist). |
| Request tidak menggantikan Workflow | Request punya lifecycle sendiri; Workflow Engine menangani transisi domain turunan. |

---

## 5. Prinsip Jangka Panjang

| Prinsip | Jaminan |
|---|---|
| Grow Without Migration | Channel/type baru = value baru di registry. Request table tidak perlu di-migrate. |
| Simple by Default | Kasus umum (walk-in) tetap 5 status. Tidak dipaksa kompleks. |
| Progressive Complexity | Modul Pickup/Corporate/Marketplace diaktifkan bertahap. |
| Configuration over Code | Perilaku channel = policy + registry, bukan if-else di kode. |
| Backward Compatible | Data Request lama tetap valid saat channel baru ditambahkan. |

---

## 6. Verifikasi

Selaras dengan `docs/domain/FutureExpansion.md` (Sprint 6.1) — Request adalah **fondasi yang memungkinkan** seluruh perluasan tanpa mengubah struktur inti.
