# ServiceKU — Workflow Specification

> Workflow nyata dari source code. Status & transisi ditulis sesuai yang terdefinisi di source; transisi yang tidak dipastikan diberi **"Perlu Verifikasi"**.

---

## 1. Workflow Service (Tiket Servis)

**Status resmi** (source): `menunggu_alokasi`, `diterima`, `diagnosa`, `dikerjakan`, `menunggu_konfirmasi_pelanggan`, `menunggu_konfirmasi_internal`, `siap_diambil`, `indent`, `onpartner`, `selesai`, `cancel`, `void`, `close`, `diambil`.

```mermaid
stateDiagram-v2
    [*] --> menunggu_alokasi: Tiket dibuat (CS/Admin)
    menunggu_alokasi --> diterima: Alokasi/terima
    diterima --> diagnosa: Mulai diagnosa
    diagnosa --> dikerjakan: Teknisi kerjakan
    dikerjakan --> menunggu_konfirmasi_pelanggan: Minta konfirmasi (biaya/selesai)
    menunggu_konfirmasi_pelanggan --> dikerjakan: Pelanggan revisi
    menunggu_konfirmasi_pelanggan --> siap_diambil: Disetujui pelanggan
    menunggu_konfirmasi_internal --> siap_diambil: Disetujui internal (non-bayar)
    siap_diambil --> selesai: Selesai & serah terima
    selesai --> diambil: Barang diambil pelanggan
    selesai --> close: Tiket ditutup
    indent --> dikerjakan: Sparepart indent datang
    onpartner --> dikerjakan: Kembali dari partner
    menunggu_alokasi --> cancel: Dibatalkan
    dikerjakan --> cancel: Dibatalkan
    siap_diambil --> close
    cancel --> close
    void --> close
```

**Aturan:**
- `indent`: tiket menunggu sparepart (stok belum ada) → menunggu pembelian/indent.
- `onpartner`: servis dilempar ke partner/teknisi luar.
- `void`: pembatalan oleh owner/admin (finansial) → status terminal.
- `close`: status akhir terminal (dipakai untuk arsip).

---

## 2. Workflow Penjualan (POS)

```mermaid
stateDiagram-v2
    [*] --> Keranjang: Tambah produk
    Keranjang --> Draft: Simpan draft
    Draft --> Selesai: Finalisasi
    Keranjang --> Selesai: Checkout langsung
    Selesai --> Pending: Menunggu pembayaran
    Pending --> Success: Bayar (cash/transfer/QR)
    Pending --> Failed: Gagal
    Pending --> Expired: Kedaluwarsa
    Success --> Refunded: Retur/refund (owner/admin)
    Failed --> Void
    Expired --> Void
```

**Status payment** (source): `pending`, `success`, `failed`, `expired`, `refunded`.

---

## 3. Workflow Pembelian

```mermaid
flowchart LR
    A[Buat PO] --> B[Terima Barang]
    B --> C[Stok bertambah]
    B --> D[Hutang ke Supplier]
    C --> E[Stok siap jual / dipakai servis]
    D --> F[Bayar hutang]
```

- PO dibuat oleh owner/admin/manager (`manage_purchases`).
- Penerimaan menambah stok & mencatat hutang supplier.

---

## 4. Workflow Inventory

```mermaid
flowchart LR
    A[Stok Masuk] --> B[Stok Tersedia]
    B --> C[Terpakai: Penjualan / Servis]
    B --> D[Transfer Antar Cabang]
    B --> E[Stok Menipis → Reorder]
    E --> F[Usulan Pembelian/Indent]
    B --> G[Adjustment (owner/admin)]
```

- `transfer_stock`: mutasi antar cabang (feature plan `multi_branch`/`transfer_stock`).
- `indent`: stok tidak ada → pesan (dari alur servis).

---

## 5. Workflow Garansi

```mermaid
flowchart LR
    A[Service Selesai] --> B[Masa Garansi Aktif]
    B --> C[Klaim Garansi Masuk]
    C --> D[Validasi: dalam periode?]
    D --> E[Diterima → servis ulang gratis]
    D --> F[Ditolak → bukan garansi]
```

- Garansi berbasis tiket servis yang sudah `selesai`.
- Detail periode & klaim per role: **Perlu Verifikasi**.

---

## 6. Workflow Subscription

**Status** (source): `trial`, `active`, `expired`, `suspended`.

```mermaid
stateDiagram-v2
    [*] --> trial: Tenant baru (paket Trial)
    trial --> active: Bayar / upgrade (voucher/payment)
    trial --> expired: Masa trial habis
    active --> expired: Tidak perpanjang
    active --> suspended: Pembayaran gagal / diblokir
    expired --> active: Perpanjang / bayar
    suspended --> active: Pulihkan (bayar / super admin)
```

**Alur:**
1. Tenant terdaftar → `trial` (14 hari, plan Trial).
2. Konversi ke `basic/pro/enterprise` saat bayar (Midtrans) atau redeem voucher.
3. `expired`/`suspended` → fitur non-inti dibatasi oleh `CheckPlanFeature`.
4. Super Admin dapat mengubah plan/status tenant dari panel admin.

---

## 7. Workflow Tenant (Onboarding)

```mermaid
flowchart LR
    A[Registrasi: nama toko + email] --> B[Verifikasi OTP]
    B --> C[Buat Database Tenant]
    C --> D[Pilih Business Type]
    D --> E[Onboarding: data toko, kategori, produk, user]
    E --> F[Aktif: Plan Trial 14 hari]
    F --> G[Operasional Harian]
```

- Multi-tenant: 1 database per tenant (`tenant_<uuid>`).
- Business type dipilih saat onboarding → menentukan fitur default (template).
- Super Admin mengelola tenant dari panel `/admin`.

---

## 8. Verifikasi Sumber

**Terkonfirmasi dari source:**
- Status service (14 nilai), status payment (5 nilai), status subscription (4 nilai).
- Alur onboarding & pembuatan DB tenant (stancl/tenancy).
- Alur POS & kas (draft/selesai/pending/success).

**Perlu Verifikasi:**
- Transisi status service yang diizinkan per role (guard sisi server per aksi).
- Periode & aturan klaim garansi.
- Batas waktu transisi `pending → expired` di POS.
- Aturan reorder / ambang stok.
