# ServiceKU — Subscription Specification

> Paket & batas layanan per plan. Sumber utama: `database/seeders/PlanSeeder.php` + `config/tenancy.php`/billing (validasi batas).
> Level akses fitur: **full** · **read_only** · **none**.

---

## 1. Status Subscription

**Status** (source): `trial`, `active`, `expired`, `suspended`.

| Status | Makna |
|---|---|
| trial | Periode percobaan (14 hari) sejak registrasi |
| active | Paket berbayar aktif |
| expired | Masa berlaku habis (tidak diperpanjang) |
| suspended | Diblokir (pembayaran gagal / tindakan platform) |

Transisi & detail: `docs/specification/WorkflowSpecification.md` §6.

---

## 2. Daftar Plan

| Plan | Harga | Trial (hari) | Max Users | Max Branches |
|---|---|---|---|---|
| **Trial** | Rp 0 | 14 | 1 | 1 |
| **Basic** | Rp 99.000 | 0 | 3 | 1 |
| **Pro** | Rp 199.000 | 0 | 10 | 5 |
| **Enterprise** | Rp 499.000 | 0 | Lebih tinggi (unlimited / sesuai negosiasi) | Lebih tinggi |

> Harga satuan/bulan (IDR). Nilai max users/branches dari `PlanSeeder`; nilai Enterprise yang lebih tinggi **Perlu Verifikasi** angka pastinya.

---

## 3. Fitur per Plan

Feature keys: `services`, `customers`, `products`, `sales`, `reports`, `settings`, `monitoring`, `multi_branch`, `transfer_stock`, `users`, `expenses`, `purchases`, `deposits`, `checklist`, `indents`, `cash_register`, `master_data`.

| Feature | Trial | Basic | Pro | Enterprise |
|---|---|---|---|---|
| services | ✅ full | ✅ full | ✅ full | ✅ full |
| customers | ✅ full | ✅ full | ✅ full | ✅ full |
| products | ✅ full | ✅ full | ✅ full | ✅ full |
| sales | 👁 read_only | ✅ full | ✅ full | ✅ full |
| reports | 👁 read_only | ✅ full | ✅ full | ✅ full |
| settings | 👁 read_only | ✅ full | ✅ full | ✅ full |
| monitoring | 👁 read_only | ✅ full | ✅ full | ✅ full |
| cash_register | 👁 read_only * | ✅ full | ✅ full | ✅ full |
| master_data | 👁 read_only * | ✅ full | ✅ full | ✅ full |
| users | ❌ none | 👁 read_only | ✅ full | ✅ full |
| expenses | ❌ none | ✅ full | ✅ full | ✅ full |
| purchases | ❌ none | ✅ full | ✅ full | ✅ full |
| deposits | ❌ none | ✅ full | ✅ full | ✅ full |
| checklist | ❌ none | ✅ full | ✅ full | ✅ full |
| indents | ❌ none | ✅ full | ✅ full | ✅ full |
| multi_branch | ❌ none | ❌ none | ✅ full | ✅ full |
| transfer_stock | ❌ none | ❌ none | ✅ full | ✅ full |

\* cash_register/master_data pada Trial: nilai pasti **Perlu Verifikasi** (apakah read_only atau none).

---

## 4. Batas Kuantitatif (Limit)

| Limit | Trial | Basic | Pro | Enterprise |
|---|---|---|---|---|
| Max users | 1 | 3 | 10 | Lebih tinggi |
| Max branches | 1 | 1 | 5 | Lebih tinggi |

- **Multi-branch & transfer stok** hanya untuk **Pro/Enterprise**.
- **Manajemen user** penuh hanya untuk **Pro/Enterprise** (Basic = read_only).

---

## 5. Alur Billing

1. Tenant baru → **Trial** (14 hari, gratis).
2. Konversi/upgrade ke **Basic/Pro/Enterprise**: pembayaran (Midtrans) atau redeem voucher.
3. Masa aktif mengikuti durasi paket; habis → `expired`; gagal bayar → `suspended`.
4. Super Admin dapat mengubah plan/status dari panel `/admin` (tenant management).

---

## 6. Verifikasi Sumber

**Terkonfirmasi:** nama plan (trial/basic/pro/enterprise), harga, hari trial, fitur utama & batas users/branches dari `PlanSeeder`.

**Perlu Verifikasi:**
- Nilai pasti `cash_register`/`master_data` pada Trial.
- Batas Enterprise yang lebih tinggi (angka pastinya).
- Durasi perpanjangan & harga prorata (bila ada).
- Enforce limit (user ke-4 saat Basic) di sisi server.
