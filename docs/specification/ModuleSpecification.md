# ServiceKU — Module Specification

> Daftar **modul nyata** yang ada di source code (route + controller + halaman Vue nyata).
> Modul yang belum ada di source diberi status **Future Module** dan TIDAK didokumentasikan sebagai kondisi saat ini.
> Sumber: `routes/tenant.php` (280 route — 96 GET, 146 POST, 21 resource), controller `app/Http/Controllers/Tenant/` (78 file), halaman `resources/js/Pages/` (123 file).

---

## 1. Daftar Modul

| # | Modul | Deskripsi singkat | Status |
|---|---|---|---|
| 1 | **Dashboard** | Ringkasan toko: pendapatan, servis aktif, statistik, grafik | ✅ Aktif |
| 2 | **Service** | Tiket servis penuh (alokasi→diagnosa→dikerjakan→selesai→diambil; indent, partner, garansi) | ✅ Aktif |
| 3 | **Customer** | Data pelanggan, histori servis & transaksi | ✅ Aktif |
| 4 | **Penjualan / POS** | Transaksi jual (keranjang, pembayaran, nota) | ✅ Aktif |
| 5 | **Pembelian** | PO & penerimaan barang dari supplier | ✅ Aktif |
| 6 | **Inventory / Stok** | Stok produk/sparepart, mutasi, transfer antar cabang, reorder | ✅ Aktif |
| 7 | **Kasir & Kas** | Shift kasir, buka/tutup kas, cash register | ✅ Aktif |
| 8 | **Setoran Harian (Daily Deposit)** | Setoran uang kasir & konfirmasi | ✅ Aktif |
| 9 | **Keuangan** | Ringkasan finansial, income/expense, deposit | ✅ Aktif |
| 10 | **Laporan (Report)** | Laporan pendapatan, servis, stok, dll | ✅ Aktif |
| 11 | **Monitoring** | Pemantauan operasional real-time | ✅ Aktif |
| 12 | **User & Role** | Manajemen user (owner) & role tenant | ✅ Aktif |
| 13 | **Cabang (Branch)** | Multi-cabang & transfer stok | ✅ Aktif |
| 14 | **Subscription / Billing** | Paket, status, voucher, perpanjangan | ✅ Aktif |
| 15 | **Settings** | Pengaturan toko, branding, tim, preferensi | ✅ Aktif |
| 16 | **Dokumen** (SOP / Knowledge Base / Quick Reply) | Template & dokumen operasional | ✅ Aktif |
| 17 | **Servis Tools** | Utilitas servis (estimasi, perangkat, dll) | ✅ Aktif |
| 18 | **Indent** | Pemesanan barang yang belum ada di stok | ✅ Aktif |
| 19 | **Supplier** | Data pemasok | ✅ Aktif |
| 20 | **Checklist** | Checklist perangkat servis (non-retail) | ✅ Aktif |
| 21 | **Garansi** | Klaim & masa garansi dari tiket selesai | ✅ Aktif |
| 22 | **Pencarian Global** | Cari servis/pelanggan/produk cepat (Cmd+K) | ✅ Aktif |
| 23 | **Tenant & Platform** (Admin) | Panel Super Admin: tenant, plan, voucher, payment, backup, logs | ✅ Aktif |
| 24 | **API** | Endpoint `Api/` (2 controller) — internal/eksternal terbatas | ⚠️ Sebagian |

---

## 2. Detail Modul Inti

### 2.1 Dashboard
- **Akses**: semua role tenant (feature `monitoring`/`reports` tergantung plan).
- **Konten**: statistik servis, pendapatan, status tiket, grafik penjualan, akses cepat.
- **Gating**: plan feature; onboarding focus mode saat data masih kosong.

### 2.2 Service (Modul Core)
- **Sub-halaman**: index (daftar+filter), show (detail), create, edit; action: alokasi teknisi, status stepper, photos, histori, checklist, indent, partner, cancel/void/close, complete, diambil.
- **Status service** (source): `menunggu_alokasi`, `diterima`, `diagnosa`, `dikerjakan`, `menunggu_konfirmasi_pelanggan`, `menunggu_konfirmasi_internal`, `siap_diambil`, `indent`, `onpartner`, `selesai`, `cancel`, `void`, `close`, `diambil`.
- **Business type**: tidak ada untuk `retail_only` (tidak terima servis).
- **Gating**: feature `services`, `checklist`, `indents`; permission `work_on_services`, `assign_technician`.

### 2.3 Penjualan / POS
- **Alur**: keranjang → simpan (draft/selesai) → pembayaran → nota → kas.
- **Status payment** (source): `pending`, `success`, `failed`, `expired`, `refunded`.
- **Gating**: feature `sales`; permission `manage_sales`, `void_transactions`.

### 2.4 Inventory & Pembelian
- **Inventory**: produk, kategori, stok masuk/keluar, transfer antar cabang (`transfer_stock`), reorder.
- **Pembelian**: PO → penerimaan → stok + hutang supplier.
- **Gating**: feature `products`, `purchases`, `transfer_stock`, `multi_branch`; `manage_products`, `manage_purchases`.

### 2.5 Kas, Setoran, Keuangan
- **Kas**: shift & cash register (`manage_cash_register`).
- **Setoran**: daily deposit + konfirmasi (`manage_deposits`, `canConfirmDeposit`).
- **Keuangan**: ringkasan, income/expense, hutang/piutang.
- **Gating**: feature `expenses`, `deposits`, `cash_register`.

### 2.6 Laporan & Monitoring
- **Laporan**: pendapatan, servis, stok, kas, dll (feature `reports`).
- **Monitoring**: aktivitas real-time (feature `monitoring`).

### 2.7 Subscription
- **Status** (source): `trial`, `active`, `expired`, `suspended`.
- **Plan** (source): `trial`, `basic`, `pro`, `enterprise` (lihat `docs/specification/SubscriptionSpecification.md`).
- **Cara bayar**: voucher, payment gateway (Midtrans), super admin override.

---

## 3. Future Module (Belum Ada di Source)

> Status **Future** — jangan dianggap kondisi saat ini.

- CRM penuh (hanya menu placeholder `#` di `menuItems`).
- Accounting / double-entry formal.
- HRD & Payroll penuh (menu placeholder).
- Marketing / Campaign.
- Module Marketplace & Plugin System.
- Public API eksternal & Webhook.
- AI Assistant.

---

## 4. Aturan Modul

1. Modul baru **harus** dicatat di dokumen ini + `docs/architecture-engine/ModuleEngine.md` sebelum dibangun.
2. Setiap modul wajib mendefinisikan: fitur plan-nya, permission-nya, dan dampak business type-nya.
3. Modul berstatus **Future** tidak boleh didokumentasikan sebagai aktif di dokumen produk.
4. Modul yang di-disable plan (`none`) → route diblokir `check.plan.feature` + UI disembunyikan.

---

## 5. Verifikasi Sumber

**Terkonfirmasi:** daftar modul di atas sesuai route/controller/halaman yang ada di `routes/tenant.php`, `app/Http/Controllers/Tenant/`, dan `resources/js/Pages/Tenant/`.

**Perlu Verifikasi:** daftar lengkap sub-halaman per modul; behavior read-only tiap plan per modul; endpoint API eksternal.
