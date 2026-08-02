# PROJECT SPECIFICATION — ServiceKU

> **Dokumen tertinggi (Single Source of Truth)** untuk proyek ServiceKU. Jika terjadi konflik dengan dokumen lain, **dokumen ini menjadi acuan utama**.
> Berlaku untuk seluruh sprint. Semua keputusan harus selaras dengan `docs/` (teknis) dan `docs/product/` (identitas).

---

## 1. Project Overview

**ServiceKU** adalah **SaaS ERP Modular** multi-tenant untuk bisnis servis elektronik (HP & laptop), sparepart, toko aksesoris, gadget store, dan retail — dengan dukungan multi-cabang. Satu platform mengelola seluruh operasional: servis, penjualan (POS), inventori/sparepart, pembelian, pelanggan, keuangan, kas, HR (shift/absensi), monitoring, dan laporan.

| Aspek | Nilai |
|---|---|
| Jenis produk | SaaS ERP Modular, multi-tenant, multi-cabang |
| Pasar | Bisnis servis HP/laptop, sparepart, aksesoris, gadget, retail |
| Arsitektur | Laravel 12 + Vue 3 + Inertia; multi-tenant (1 DB per tenant) |
| Dokumentasi teknis | `docs/Architecture.md` .. `docs/FolderStructure.md` |
| Dokumentasi identitas | `docs/product/` |
| Dokumen ini | Acuan tertinggi (spesifikasi resmi) |

---

## 2. Vision

> **"Satu platform, seluruh operasional toko servis Anda — dari tiket masuk hingga garansi, dari toko kecil hingga jaringan multi-cabang."**

ServiceKU ingin menjadi platform manajemen operasional paling tepercaya bagi bisnis servis elektronik & retail di Indonesia.

---

## 3. Mission

1. Merapikan proses servis (tiket, status, checklist, garansi) sehingga tidak ada tiket hilang.
2. Menyatukan servis, stok/sparepart, penjualan, keuangan, dan pelanggan dalam satu data.
3. Memberi kendali penuh kepada pemilik (dashboard, laporan, multi-cabang).
4. Mengurangi beban admin melalui otomatisasi catatan, notifikasi, dan dokumen.
5. Mendukung pertumbuhan dari toko kecil → jaringan (multi-cabang) tanpa mengubah arsitektur inti.

---

## 4. Target Market

| Segmen | Kebutuhan utama |
|---|---|
| Toko servis HP | Alur servis & sparepart, status ke pelanggan |
| Toko servis laptop | Garansi, klaim, sparepart |
| Toko sparepart / aksesoris | POS, stok, pembelian |
| Gadget store (jual baru/second) | POS + servis |
| Retail umum | POS, kas, setoran |
| Multi-cabang | Transfer stok, laporan gabungan, manajemen user |

**Geografi:** Indonesia (Bahasa Indonesia, Rupiah, zona waktu lokal).

---

## 5. Business Domain

Domain inti ServiceKU (modul nyata di source code):

**Servis** (core) → **Pelanggan** → **Produk/Sparepart** → **Penjualan (POS)** → **Pembelian** → **Inventory/Stok** → **Kas & Setoran** → **Keuangan** → **Laporan** → **Monitoring** → **HR (User/Shift/Absensi)** → **Multi-Cabang** → **Subscription/Billing** → **Tenant (platform)**.

Domain mendukung business type: servis penuh, aksesoris+servis, retail murni, gadget (lihat `docs/specification/BusinessTypeSpecification.md`).

---

## 6. Role Resmi

Role resmi saat ini (**7**, dari source code; jangan menambah role baru):

| # | Role | Keterangan (source key) |
|---|---|---|
| 1 | **Super Admin** | Admin platform (panel `/admin`; kelola tenant/plan/platform) — terpisah dari user tenant |
| 2 | **Owner** | Pemilik toko (`owner`) — akses penuh tenant |
| 3 | **Manager** | Pengawas operasional (`manager`) |
| 4 | **Admin** | Admin toko (`admin`) |
| 5 | **Customer Service (CS)** | Front desk / penerima servis (`cs`) |
| 6 | **Kasir** | Cashier / POS (`cashier`) |
| 7 | **Teknisi** | Teknisi servis (`technician`) |

> **Catatan source:** source code juga mendefinisikan `head_store` (Kepala Toko), `courier` (Kurir), dan `custom` (role kustom) di `role_permissions`. Ketiganya **bukan role resmi utama** pada spesifikasi ini; didokumentasikan sebagai peran tambahan yang ada di source (lihat `docs/specification/RoleMatrix.md`). Role lain (Gudang, QC, Purchasing, HRD, Marketing, Supervisor) **tidak ada** → bukan role resmi, beri status **Future Expansion** bila diperlukan.

---

## 7. Business Type Resmi

Business type adalah **template onboarding tenant** (liat juga keputusan arsitektur `docs/architecture-engine/BusinessTemplateEngine.md`). Business type resmi (**4**):

| # | Business Type | Source key |
|---|---|---|
| 1 | **Retail** (Jualan Saja) | `retail_only` |
| 2 | **Aksesoris + Service** (Terima servis, dilempar) | `aksesoris_service` |
| 3 | **Pusat Service + Sparepart** (Servis & jual sparepart) | `full_service` |
| 4 | **HP/Laptop Baru + Service** (Gadget store) | `gadget_full` |

> **Catatan source:** source juga mendefinisikan `aksespare_service` (Aksesoris & Sparepart + Ada Teknisi). Nilai ini ada di source (`Tenant::getBusinessTypes`); didokumentasikan sebagai nilai tambahan, bukan business type resmi (lihat `docs/specification/BusinessTypeSpecification.md`). Jangan menambah business type baru.

---

## 8. Module Resmi

Module berikut **benar-benar ada** di source code (halaman/route/controller nyata):

Dashboard, Service, Pelanggan (Customer), POS/Penjualan (Sales), Pembelian (Purchase), Inventory/Stok, Kasir & Kas, Setoran Harian (Daily Deposit), Keuangan, Laporan (Report), Monitoring, User & Role, Cabang (Branch), Subscription/Billing, Settings/Pengaturan, Dokumen (SOP/Knowledge Base/Quick Reply), Servis Tools, Indent, Supplier, Checklist, Garansi, Transfer Stok (Stock Allocation), Inventaris, Pencarian (Search), Tenant (platform/admin).

> Module yang **belum ada** di source (mis. CRM, Accounting formal, HRD/payroll penuh) → status **Future Module** (lihat `docs/specification/ModuleSpecification.md`).

---

## 9. Workflow Utama

Workflow nyata (detail: `docs/specification/WorkflowSpecification.md`):

1. **Workflow Service** — tiket masuk → alokasi → dikerjakan → konfirmasi → siap diambil → selesai (+ indent, partner, garansi).
2. **Workflow Penjualan (POS)** — keranjang → simpan (draft/selesai) → pembayaran → nota.
3. **Workflow Pembelian** — PO/penerimaan → stok bertambah → pembayaran hutang.
4. **Workflow Inventory** — mutasi masuk/keluar, transfer antar cabang, stok menipis, adjustment.
5. **Workflow Garansi** — klaim dari servis selesai yang masih dalam masa garansi.
6. **Workflow Subscription** — trial → active → expired/suspended → perpanjang (voucher/payment).
7. **Workflow Tenant** — registrasi (OTP) → buat DB → onboarding → aktif.

---

## 10. Permission Philosophy

- **Permission adalah pusat sistem** (keputusan arsitektur target: `docs/architecture-engine/PermissionEngine.md`).
- **Role = kumpulan permission** (role resmi 7 saat ini; target: role sebagai template permission yang dapat dikelola).
- Akses ditentukan oleh: **peran (role) + plan feature (fitur paket) + business type**.
- Level akses fitur: `full` / `read_only` / `none` (`CheckPlanFeature`).
- Akses aksi (manage_*, void, assign, delete) mengikuti `role_permissions` & method `canX()` (`Traits/HasRoles`).
- Detail: `docs/specification/PermissionMatrix.md` & `docs/specification/BusinessRules.md`.

---

## 11. UI Philosophy

- **Information First** — data didahulukan; tabel terbaca; angka jelas.
- **One Primary Action** per layar/modal.
- **Minimal Color** — warna bermakna (primary + semantik status).
- **Consistent Components** — wajib komponen `K*`; dilarang HTML mentah di halaman.
- **No Visual Noise**, **Fast Interaction**, **Clear Status**, **Safety Before Destruction** (konfirmasi destruktif).
- Bahasa **Indonesia profesional**; format id-ID (Rp).
- Detail: `docs/product/DesignPrinciples.md`, `docs/product/Interaction.md`, `docs/product/CopyWriting.md`.

---

## 12. Coding Philosophy

- Backend: Laravel 12, controller ber-role (`Tenant/`, `Admin/`, `Auth/`, `Api/`), model central vs tenant, FormRequest & Policy untuk validasi/otorisasi (bertahap).
- Frontend: Vue 3 `<script setup>` + Inertia; komponen `K*`; composables untuk helper; state via Inertia props (tanpa Pinia).
- Multi-tenancy: satu DB per tenant; jangan cross-query antar tenant; setiap resource tenant dilindungi `check.plan.feature`.
- Konvensi penamaan & struktur: `docs/Naming.md`, `docs/FolderStructure.md`.
- Dokumentasi teknis penuh: `docs/Backend.md`, `docs/Frontend.md`.

---

## 13. Roadmap

| Fase | Fokus |
|---|---|
| Saat ini (MVP) | Modul inti: servis, POS, inventory, keuangan, laporan, multi-cabang, subscription, tenant |
| Sprint 6+ | Konsolidasi permission & role; konsistensi komponen; perbaikan a11y |
| Target arsitektur | ERP Modular: Role Engine, Feature Engine, Business Template (lihat `docs/architecture-engine/`) |
| Jangka menengah | Workflow Builder, modul CRM/HRD, public API, webhook |
| Jangka panjang | Module Marketplace, Plugin System, AI Assistant |

---

## 14. Future Expansion

Fitur/konsep yang **belum ada** di source code — dicatat sebagai *Future Expansion* (bukan kondisi saat ini):

- Role baru: Gudang (Stock Clerk), QC, Purchasing, HRD, Marketing, Supervisor.
- Module baru: CRM, Accounting penuh, HRD/payroll, Marketplace, AI Assistant.
- Multi-role per user (target `docs/architecture-engine/UserEngine.md`).
- Workflow Builder, Role Builder, Plugin System.
- Public API & Webhook (belum ada; hanya endpoint internal & search JSON).

> Semua Future Expansion **tidak** mengubah arsitektur inti; diaktifkan lewat permission/feature/module engine tanpa migrasi data besar.

---

## 15. Verifikasi Sumber

Dokumen ini ditulis berdasarkan **source code nyata**. Informasi yang tidak dapat dipastikan dari source diberi status **"Perlu Verifikasi"** pada dokumen terkait. Lihat bagian VERIFIKASI di akhir tiap dokumen untuk daftar yang terkonfirmasi vs belum.
