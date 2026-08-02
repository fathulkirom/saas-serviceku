# ServiceKU — Engine

> **Sprint 6.1 · Blueprint Only.** Identifikasi **engine utama** ServiceKU. Engine = komponen logika yang mengelola perilaku domain tertentu secara konsisten (input → proses → output), dengan dependency & arah pengembangan.
> Selaras dengan `docs/architecture-engine/` (Sprint 5.2). Engine di sini adalah pemetaan domain-level.

---

## 1. Peta Engine

| # | Engine | Bounded Context | Status |
|---|---|---|---|
| 1 | **Customer Engine** | BC3 Customer Mgmt | ⚠️ sebagian (CRUD ada; segmentasi target) |
| 2 | **Service Engine** | BC4 Service Execution | ✅ inti ada (workflow, assign, checklist, indent) |
| 3 | **Policy Engine** | BC1 Identity & Access / BC7 | ❌ target (policy = data) |
| 4 | **Permission Engine** | BC1 Identity & Access | ⚠️ sebagian (role_permissions hardcoded → target data) |
| 5 | **Workflow Engine** | lintas modul | ❌ target (state machine terpusat) |
| 6 | **Compensation Engine** | BC7 Post-Sale | ❌ target (mengikuti Policy) |
| 7 | **Inventory Engine** | BC5 Supply Chain | ⚠️ sebagian (mutasi stok ada; engine utuh target) |
| 8 | **Warranty Engine** | BC7 Post-Sale | ⚠️ sebagian (garansi ada; claim/replacement target) |
| 9 | **Supplier Engine** | BC5 Supply Chain | ⚠️ sebagian (supplier & purchase ada; claim target) |
| 10 | **Finance Engine** | BC8 Finance | ⚠️ sebagian (aggregate manual; formal target) |
| 11 | **Module Engine** | BC2 Subscription | ❌ target (registry) |
| 12 | **Branch Engine** | organisasi | ⚠️ sebagian (branch & transfer ada; engine target) |
| 13 | **Dashboard Engine** | BC8 Wawasan | ❌ target (permission-based widget) |
| 14 | **Subscription Engine** | BC2 Subscription | ⚠️ sebagian (plan/feature ada; dimensi luas target) |

---

## 2. Detail Engine

### 2.1 Customer Engine
- **Tujuan:** kelola relasi pelanggan: data, kunjungan, device, histori, deteksi duplikat.
- **Input:** data customer/visit/device; query pencarian; (target) segmentasi.
- **Output:** customer valid + device + visit → siap untuk Service/Commerce; profil 360°.
- **Dependency:** Permission (`manage_customers`), Module `customers`, Plan.
- **Future:** segmentasi, loyalitas/poin, blacklist, notifikasi WA, kredit limit.

### 2.2 Service Engine
- **Tujuan:** **core domain** — kelola tiket servis dari lahir sampai terminal (14 status), alokasi teknisi, checklist, indent, partner, garansi.
- **Input:** Visit/Device/Customer, aksi status, alokasi teknisi, pemakaian part, checklist.
- **Output:** Service Order valid; transisi status; event `SparepartUsed`, `ServiceCompleted`, `WarrantyCreated`; notifikasi.
- **Dependency:** Customer, Inventory, Policy (harga/garansi), Permission (`work_on_services`, `assign_technician`, `void_transactions`), Feature `services`/`checklist`/`indents`.
- **Future:** SLA, estimasi waktu, self-service tracking, foto AI, otomatisasi status.

### 2.3 Policy Engine
- **Tujuan:** aturan bisnis tenant (kompensasi, garansi, harga, diskon, setoran) sebagai data + versi.
- **Input:** definisi policy (owner), konteks bisnis (service/klaim/penjualan).
- **Output:** keputusan (kompensasi nominal, masa garansi, harga) yang **konsisten & auditable**.
- **Dependency:** Tenant, Business Type (template policy), Module.
- **Future:** policy builder UI, A/B policy, policy per cabang, approval policy.

### 2.4 Permission Engine
- **Tujuan:** pusat otorisasi — permission data, role = kumpulan permission, resolusi union (multi-role target).
- **Input:** user, role(s), aksi yang diminta, plan feature, business type.
- **Output:** ALLOW/DENY/read_only — konsisten di server & UI.
- **Dependency:** Module (permission modul), Role, User, Feature.
- **Future:** permission granular per baris/cabang, permission API, audit trail akses.

### 2.5 Workflow Engine
- **Tujuan:** state machine per modul (Service, POS, Purchase, Warranty, Subscription) — transisi valid + izin + efek samping.
- **Input:** aggregate, aksi, actor.
- **Output:** status baru; event; notifikasi; hook (onEnter).
- **Dependency:** Module, Permission, Domain Event.
- **Future:** Workflow Builder, transisi custom per tenant, SLA automation.

### 2.6 Compensation Engine
- **Tujuan:** hitung kompensasi teknisi/karyawan **mengikuti Policy** (persen/nominal/tier).
- **Input:** Service Order (dasar), Policy kompensasi, User penerima, periode.
- **Output:** Compensation (nominal + dasar hitung) → finance; status approval→bayar.
- **Dependency:** Policy, Service, Finance, User; Module (target HR/kompensasi).
- **Future:** komisi kompleks, payroll, slip kompensasi, approval multi-level.

### 2.7 Inventory Engine
- **Tujuan:** kelola stok per branch; mutasi masuk/keluar/transfer/adjust; reorder.
- **Input:** event `StockIn/StockOut/StockTransferred/StockLow`; PO diterima; sale; part servis; replacement.
- **Output:** stok konsisten (tidak negatif), mutasi tercatat, peringatan reorder.
- **Dependency:** Branch, Purchase, Sales, Service, Warranty (replacement), Feature `products`/`transfer_stock`.
- **Future:** multi-gudang, batch/expiry, barcode, forecasting, auto-reorder.

### 2.8 Warranty Engine
- **Tujuan:** kelola masa garansi dari servis selesai; klaim; supplier claim; replacement.
- **Input:** ServiceOrder selesai, Policy garansi, Claim, Supplier, Inventory.
- **Output:** Warranty aktif; keputusan klaim; SupplierClaim; Replacement (→ stok).
- **Dependency:** Service, Policy, Supplier, Inventory, Finance.
- **Future:** garansi berbayar, transfer garansi, reminder kadaluarsa, klaim otomatis dari device.

### 2.9 Supplier Engine
- **Tujuan:** kelola supplier, purchase, hutang, dan supplier claim (Business Reality).
- **Input:** data supplier, PO, penerimaan, pembayaran, claim.
- **Output:** supplier + saldo hutang akurat; claim → replacement; purchase → stok.
- **Dependency:** Purchase, Inventory, Warranty (claim), Finance.
- **Future:** rating supplier, lead time, auto-reorder per supplier, negosiasi harga.

### 2.10 Finance Engine
- **Tujuan:** agregat keuangan tenant (pendapatan, biaya, hutang/piutang, profit, kompensasi).
- **Input:** event finansial (sale, purchase, deposit, expense, kompensasi, replacement).
- **Output:** ringkasan keuangan & laporan konsisten (tidak dihitung ulang manual).
- **Dependency:** seluruh modul transaksional, Module `expenses`/`deposits`.
- **Future:** double-entry, neraca, arus kas, pajak, rekonsiliasi.

### 2.11 Module Engine
- **Tujuan:** registry modul; aktivasi/penonaktifan; permission & menu otomatis.
- **Input:** definisi modul (key, fitur, permission, routes, menu), plan tenant.
- **Output:** modul aktif/disabled; route & menu tersaring; permission tersedia.
- **Dependency:** Subscription, Feature, Permission.
- **Future:** marketplace modul, plugin, modul pihak ketiga.

### 2.12 Branch Engine
- **Tujuan:** kelola multi-cabang; stok & kas per branch; transfer stok.
- **Input:** data branch, assignment user, transfer stok, laporan gabungan.
- **Output:** branch aktif, stok/kas terisolasi per branch, transfer tercatat, laporan gabungan.
- **Dependency:** Tenant, Feature `multi_branch`/`transfer_stock`, Inventory, Cash.
- **Future:** lokasi geografis, jam buka, PIC cabang, sinkron lintas cabang.

### 2.13 Dashboard Engine
- **Tujuan:** wawasan operasional berbasis **permission** (target).
- **Input:** data agregat modul, permission user, preferensi.
- **Output:** widget yang sesuai akses; laporan cepat.
- **Dependency:** seluruh modul (agregasi), Permission, Feature `monitoring`/`reports`.
- **Future:** widget builder, dashboard per role, export otomatis.

### 2.14 Subscription Engine
- **Tujuan:** kontrol layanan per plan: **Module/Limit/Feature/Storage/User/Branch/API/Backup/WhatsApp/Marketplace/AI**.
- **Input:** status subscription, aksi user, kuota.
- **Output:** izin/tolak (full/read_only/none); limit ter-enforce; status trial/active/expired/suspended.
- **Dependency:** Plan, Module, Feature, Payment platform, Tenant.
- **Future:** prorata, auto-renew, invoice otomatis, kuota storage/API.

---

## 3. Matriks Input–Output–Dependency Ringkas

| Engine | Input utama | Output utama | Dependency kunci |
|---|---|---|---|
| Customer | data customer/visit/device | profil 360° | Permission, Module customers |
| Service | visit/device/aksi | tiket valid + event | Inventory, Policy, Permission |
| Policy | aturan owner | keputusan (kompensasi/garansi/harga) | Tenant, Business Type |
| Permission | user/role/aksi/plan | ALLOW/DENY/read_only | Module, Role, Feature |
| Workflow | aggregate/aksi/actor | status + event + notif | Module, Permission |
| Compensation | service/policy/user | Compensation + finance | Policy, Finance |
| Inventory | event mutasi | stok konsisten + reorder | Branch, Purchase, Sales, Warranty |
| Warranty | service selesai/policy/claim | garansi + claim + replacement | Service, Policy, Supplier, Inventory |
| Supplier | supplier/PO/claim | saldo + claim + stok | Purchase, Inventory, Finance |
| Finance | event finansial | laporan keuangan | semua modul transaksi |
| Module | definisi modul + plan | modul aktif/disabled | Subscription, Feature |
| Branch | branch/transfer | stok/kas per branch | Tenant, Feature multi_branch |
| Dashboard | agregat + permission | widget & laporan | modul, Permission |
| Subscription | status/aksi/kuota | izin + limit + status | Plan, Module, Feature, Payment |

---

## 4. Verifikasi

Engine yang sudah ada di source: **Service** (inti), **Subscription** (CheckPlanFeature/CheckSubscription), **Payment Gateway** (service), **Inventory** (mutasi stok di controller). Engine lain (Policy, Workflow, Compensation, Dashboard, Module, Branch, Finance formal, Permission data) adalah **target/blueprint** sesuai `docs/architecture-engine/`.
