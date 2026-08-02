# ADR-001 — Request as Core Entry Point

> **Architecture Decision Record · Sprint 6.1D · 2026-08-02.**
> Status: **ACCEPTED** ✅
> Setelah sprint ini, struktur Request tidak boleh berubah tanpa ADR baru.

---

## 1. Context

ServiceKU adalah platform SaaS ERP modular untuk bisnis servis elektronik, sparepart, aksesoris, gadget, dan retail. Selama Sprint 6.1, domain model menetapkan **CustomerVisit** sebagai entry point utama operasional — dengan asumsi bahwa semua pekerjaan dimulai dari kunjungan fisik pelanggan ke toko atau langsung ke ServiceOrder.

Namun, analisis seluruh Business Reality (Sprint 6.1A, BR-001..020) mengungkap bahwa:
- Customer bisa **dijemput kurir** (BR-001, pickup).
- Teknisi bisa **datang ke rumah** (home service).
- **Corporate** dapat mengirim banyak device sekaligus.
- **Marketplace** (Tokopedia/Shopee) dapat membuat order.
- **WhatsApp** dapat membuat request.
- **Website** dapat membuat booking.
- **Public API** akan membuat request di masa depan.

Artinya, **yang menjadi pintu masuk sistem bukan Service — melainkan REQUEST**. CustomerVisit hanyalah salah satu channel (walk-in). Mempertahankan CustomerVisit sebagai entry point akan memaksa pembuatan entitas baru untuk setiap channel (PickupVisit, HomeVisit, CorporateOrder, MarketplaceOrder, ...) — melanggar **Grow Without Migration**.

---

## 2. Problem

Tidak ada entitas terpadu yang dapat menampung **semua jenis permintaan operasional** dari seluruh channel (walk-in, pickup, home service, courier, corporate, booking, WhatsApp, marketplace, API, warranty claim) sebelum di-fork ke domain turunan (ServiceOrder, SalesOrder, Warranty, Booking).

Tanpa entitas terpadu:
- Setiap channel baru membutuhkan entitas & tabel baru → migrasi skema terus-menerus.
- Tidak ada **origin trace** tunggal — `request_id` tersebar di berbagai tabel.
- Tidak ada lifecycle terpadu — setiap channel punya status sendiri yang tidak konsisten.
- Tidak ada ownership & audit trail yang seragam.

---

## 3. Decision

**Menetapkan `Request` sebagai Core Entry Point tunggal ServiceKU.**

> *Setiap interaksi operasional — walk-in, pickup, home service, courier, corporate, booking, WhatsApp, marketplace, API, warranty claim — WAJIB dimulai sebagai Request.*

Request adalah **abstraksi level atas** yang menangkap:
- **Apa** (`type`: walk_in, pickup, home_service, courier, corporate, booking, whatsapp, marketplace, api, warranty_claim)
- **Dari mana/siapa** (`source`: customer, cs, owner, marketplace, whatsapp_bot, api_client, system)
- **Bagaimana** (`channel`: store, phone, whatsapp, website, marketplace, public_api, admin_panel)
- **Kapan** (`scheduled_at` untuk booking/janji)
- **Untuk siapa** (`customer_id`, `device_ids`)
- **Dimana** (`pickup_address`, `branch_id`, `pickup_branch`)

Request kemudian **di-fork** ke domain turunan yang sesuai (ServiceOrder, SalesOrder, WarrantyClaim, Booking) dan menyimpan `request_id` sebagai **origin trace** yang immutable di seluruh turunannya.

**Prinsip desain:**
1. **1 Request → N Device → N Domain Turunan** (parallel fork).
2. Lifecycle Request **unified** — channel memilih subset status yang relevan (walk-in = 5 status minimal; pickup/courier = 10+ status).
3. Channel/type/source baru = **tambah value di registry** — bukan tabel baru.
4. Request adalah **data tenant**, dengan audit trail append-only.

---

## 4. Alternatives Considered

| Alternatif | Penilaian | Ditolak karena... |
|---|---|---|
| **A. Pertahankan CustomerVisit + buat entitas per channel** (PickupVisit, HomeVisit, CorporateOrder, ...) | ❌ Ditolak | Melanggar **Grow Without Migration**. Setiap channel baru = tabel baru = migrasi. Tidak scalable. |
| **B. Pertahankan CustomerVisit + tambah kolom `type` pada CustomerVisit** | ❌ Ditolak | `CustomerVisit` secara semantik adalah "kunjungan fisik". Memaksakan type=pickup pada entitas "visit" ambigu dan misleading. |
| **C. Hapus CustomerVisit; semua langsung ke ServiceOrder dengan kolom `channel`** | ❌ Ditolak | ServiceOrder sudah kompleks (14 status, workflow servis). Menambah channel management di ServiceOrder membuatnya "god object" dan melanggar Single Responsibility. Juga memaksa ServiceOrder menangani retail (SalesOrder) — tidak tepat. |
| **D. Request sebagai Core Entry Point (DIPILIH)** | ✅ Diterima | Satu funnel semua channel; fork ke domain turunan; origin trace tunggal; channel baru = data. Memenuhi seluruh prinsip & Business Reality. |

---

## 5. Consequences

### Positif
1. **Origin trace tunggal** — `request_id` immutable di ServiceOrder, SalesOrder, Warranty, Booking.
2. **Audit trail seragam** — RequestHistory mencakup seluruh channel.
3. **Channel baru tanpa migrasi** — tambah value enum di registry Request Engine.
4. **Multi-device native** — 1 Request→N ServiceOrder tanpa entitas perantara.
5. **Policy & permission seragam** — `request.create/assign/cancel` berlaku untuk semua channel.
6. **Lifecycle terpadu** — status dipilih per channel; konsistensi format.

### Negatif
1. **Satu lapisan abstraksi tambahan** — Request sebelum domain turunan. Walk-in sederhana tetap harus melewati Request.
2. **Migrasi data existing** — data `CustomerVisit` & ServiceOrder yang sudah ada tanpa `request_id` harus diperlakukan sebagai legacy (tidak perlu di-backfill, cukup nullable `request_id`).
3. **Kompleksitas query bertambah** — laporan yang ingin melacak dari Request harus JOIN satu tabel tambahan.
4. **Tim harus memahami model baru** — dari "Visit→Service" menjadi "Request→[fork]→Domain".

---

## 6. Trade-offs

| Trade-off | Keputusan |
|---|---|
| Abstraksi vs Kesederhanaan untuk walk-in | Walk-in tetap sederhana (5 status). Tambahan abstraksi Request tidak membebani UX — CS hanya lihat form "Buat Request" (mirip form Visit saat ini). |
| Kompleksitas query vs Origin trace lengkap | Origin trace diprioritaskan. JOIN satu tabel untuk laporan dapat diatasi dengan view/aggregate. |
| Backward compatibility vs Data bersih | `request_id` nullable di tabel ServiceOrder/SalesOrder existing. Data lama tetap berfungsi. Tidak ada backfill wajib. |

---

## 7. Impact

| Area | Dampak |
|---|---|
| Domain Model (Sprint 6.1) | **CustomerVisit didepresiasi** sebagai entry point. Tetap ada sebagai data historis. `DomainRelationship.md`, `Entity.md`, `Aggregate.md`, `Engine.md` perlu diperbarui. |
| Sprint 6.2 (ERD) | Tabel `requests` adalah **salah satu tabel pertama** yang didesain. `requests.id` menjadi FK di `service_orders` (+ `request_id`), `sales_orders`, `warranty_claims`. |
| Permission (Sprint 5.1) | Tambah permission: `request.create`, `request.assign`, `request.cancel`, `request.override`. |
| Workflow Engine | Tambah state machine "Request" sebagai salah satu workflow modul. |
| UI / Frontend | Form "Buat Tiket" diganti/diperluas menjadi "Buat Request" dengan pemilihan type & channel. |
| API (future) | Endpoint `POST /requests` adalah entry point eksternal. |
| Reporting | Laporan dimulai dari `requests` (origin) → `service_orders`/`sales_orders` (detail). |

---

## 8. Future Compatibility

- ✅ Marketplace: type=marketplace.
- ✅ Public API: type=api.
- ✅ Mobile App: type=mobile_app (future).
- ✅ Home Service: type=home_service.
- ✅ Corporate Contract: type=corporate + modul.
- ✅ IoT device auto-request: source=system, channel=public_api.
- ✅ Customer Portal: customer melihat Request mereka (projection).
- ✅ Queue System: type=booking + modul antrian.
- ✅ Subscription Service: auto-generate Request berkala (source=system).
- ✅ AI auto-classify: Request menerima input AI untuk type/priority/assign.

**Tidak ada channel masa depan yang teridentifikasi yang membutuhkan perubahan fondasi Request.**

---

## 9. Status

| Status | Tanggal | Oleh |
|---|---|---|
| **ACCEPTED** ✅ | 2026-08-02 | Sprint 6.1D |

**Berlaku:** Mulai Sprint 6.2 (ERD) dan seterusnya.

**Referensi:**
- `docs/request-engine/01_RequestEngine.md`
- `docs/request-engine/06_RequestValidation.md`
- `docs/specification/PROJECT_SPECIFICATION.md`
- `docs/domain/` (Sprint 6.1) — perlu revisi sesuai dampak.
