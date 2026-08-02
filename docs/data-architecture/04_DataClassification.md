# 04 — Data Classification

> **Sprint 6.2A · Blueprint Only.** Klasifikasi data ServiceKU berdasarkan sensitivitas, dampak kebocoran, dan aturan penanganan.

---

## 1. Level Klasifikasi

| Level | Label | Deskripsi | Contoh |
|---|---|---|---|
| **L4** | 🔴 **Sensitive** | Kebocoran = dampak hukum/finansial serius. | Credentials, Payment token, Encryption keys |
| **L3** | 🟠 **PII / Personal** | Data pribadi yang diatur regulasi (UU PDP). | Nama, Telepon, Email, Alamat, IMEI |
| **L2** | 🟡 **Financial** | Data keuangan — harus immutable setelah final. | Harga, Biaya, Deposit, Kompensasi, Profit |
| **L1** | 🟢 **Operational** | Data operasional — akurat & terkini. | Status servis, Stok, Shift, Assignment |
| **L0** | ⚪ **Public** | Data yang boleh ditampilkan publik. | Nama toko, Jam buka (jika publik) |

---

## 2. Klasifikasi per Domain

| Domain | Klasifikasi | Aturan khusus |
|---|---|---|
| **Tenant** | L2 Financial + L3 PII | Data kontak tenant = PII; payment info = L4 |
| **Plan / Feature** | L1 Operational | Konfigurasi platform |
| **Voucher** | L2 Financial | Kode + nilai = harus diamankan |
| **Platform Payment** | L4 Sensitive | Token Midtrans, amount, status |
| **Super Admin** | L4 Sensitive | Credentials admin platform |
| **Branch** | L1 Operational | Alamat cabang = L3 PII |
| **User** | L3 PII + L4 Sensitive | Nama/email/telepon = PII; password hash = L4 |
| **Role / Permission** | L1 Operational | Konfigurasi akses |
| **Policy** | L1 Operational | Aturan bisnis (non-PII) |
| **Settings** | L1 Operational | Preferensi tenant |
| **Customer** | L3 PII | Nama, telepon, alamat — diatur UU PDP. Hak akses & hapus. |
| **Device** | L3 PII | IMEI/serial = data pribadi perangkat |
| **Supplier** | L3 PII + L2 Financial | Kontak = PII; saldo hutang = financial |
| **Service Partner** | L3 PII + L2 Financial | Kontak = PII; komisi = financial |
| **Product** | L2 Financial | Harga beli/jual = financial |
| **Request** | L1 Operational | Type, channel, status |
| **Service Order** | L1 Operational + L2 Financial | Status = operasional; biaya = financial |
| **Sales Order** | L2 Financial | Amount, payment status, discount |
| **Purchase Order** | L2 Financial | Amount, hutang |
| **Warranty / Claim** | L1 Operational | Status klaim |
| **Cash Shift / Deposit** | L2 Financial | Saldo, setoran |
| **Inventory Movement** | L1 Operational + L2 Financial | Qty = operasional; value = financial |
| **Finance Aggregate** | L2 Financial | Pendapatan, biaya, profit |
| **Report** | L2 Financial | Agregat keuangan |
| **Audit Log** | L4 Sensitive | Berisi catatan siapa melakukan apa — sensitif |
| **History Log** | L1 Operational | Perubahan data |

---

## 3. Aturan per Level

| Level | Enkripsi at rest | Masking di log | Audit akses | Hak hapus (regulasi) | Export terbatas |
|---|---|---|---|---|---|
| L4 Sensitive | ✅ Wajib | ✅ Wajib | ✅ Setiap akses | ❌ (retensi) | ✅ |
| L3 PII | ✅ (opsional, rekomendasi) | ✅ Wajib | ✅ Akses baca | ✅ (UU PDP) | ✅ |
| L2 Financial | ❌ (tidak wajib) | ✅ (amount) | ✅ Setiap perubahan | ❌ (immutable) | ❌ |
| L1 Operational | ❌ | ❌ | ❌ (secukupnya) | ❌ (soft delete) | ❌ |
| L0 Public | ❌ | ❌ | ❌ | ❌ | ❌ |

---

## 4. Verifikasi

Konsisten dengan prinsip **Data Is Sacred** (Sprint 6.1, 6.1A) dan `docs/request-engine/05_RequestOwnership.md` (audit trail).
