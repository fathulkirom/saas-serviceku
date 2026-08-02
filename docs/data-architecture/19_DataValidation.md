# 19 — Data Validation

> **Sprint 6.2A · Blueprint Only.** Aturan validasi data per domain — apa yang wajib, format, dan batasan. Menjadi dasar FormRequest & model validation.

---

## 1. Validasi per Domain

### Tenant
- `subdomain`: wajib, unik global, regex `[a-z0-9-]+`, 3-30 karakter.
- `business_type`: wajib, salah satu dari 5 nilai resmi.
- `email`: wajib, unik global, format email valid.

### Branch
- `name`: wajib, unik per tenant, 2-100 karakter.
- `address`: opsional, text.
- `status`: `active` | `inactive`.

### User
- `name`: wajib, 2-100 karakter.
- `email`: wajib, unik per tenant, format email valid.
- `phone`: opsional, format `08xxxxxxxxxx`, 10-13 digit.
- `role`: wajib (saat ini 1; target multi-role).
- `specialization`: opsional, array string (BR-006).

### Role
- `key`: wajib, unik per tenant, `snake_case`.
- `name`: wajib, unik per tenant.
- `is_system`: boolean — system role tidak bisa dihapus.

### Customer
- `name`: wajib, 2-150 karakter.
- `phone`: wajib, `08xxxxxxxxxx`, unik per tenant (peringatan, bukan tolak).
- `address`: opsional, text.

### Device
- `customer_id`: wajib (referensi customer).
- `type`: wajib (HP, Laptop, Tablet, dll).
- `brand`: wajib.
- `model`: opsional.
- `IMEI` / `serial`: salah satu wajib; unik per tenant; format validasi IMEI (15 digit / Luhn).

### Request
- `customer_id`: wajib (kecuali walk-in guest).
- `branch_id`: wajib.
- `type`: wajib, salah satu dari katalog type resmi.
- `source`: wajib, salah satu dari katalog source resmi.
- `channel`: wajib, salah satu dari katalog channel resmi.
- `device_ids`: minimal 1 (kecuali retail-only).
- `scheduled_at`: wajib jika `type=booking`; opsional lainnya.
- `pickup_address`: wajib jika `type=pickup`/`home_service`.
- `pickup_branch_id`: opsional (BR-001).

### Service Order
- `request_id`: wajib (kecuali legacy).
- `customer_id`: wajib.
- `device_id`: wajib.
- `status`: wajib, salah satu dari 14 status resmi.
- `technician_id`: wajib saat status ≥ `diagnosa`.
- `service_cost`: opsional; integer (sen).

### Sales Order
- `request_id`: wajib (retail dari Request) / opsional (POS langsung).
- `branch_id`: wajib.
- `items`: minimal 1; setiap item: product_id, qty (>0), price (≥0).
- `total_amount`: auto-hitung dari items; integer (sen).

### Purchase Order
- `supplier_id`: wajib.
- `items`: minimal 1.
- `total_amount`: auto-hitung.

### Warranty / Claim
- `service_order_id`: wajib.
- `claim_date`: wajib; harus dalam periode policy.
- `resolution_type`: wajib sebelum `resolved` (BR-012).

### Inventory Movement
- `product_id`: wajib.
- `branch_id`: wajib.
- `qty`: ≠ 0 (positif = masuk, negatif = keluar).
- `type`: `in` / `out` / `transfer` / `adjust`.
- Hasil stok setelah movement: **tidak boleh < 0**.

### Cash Shift
- `branch_id`: wajib.
- `cashier_id`: wajib.
- Tidak boleh ada shift terbuka di branch yang sama.

### Deposit
- `shift_id`: wajib.
- `amount`: > 0; integer (sen).

### Policy
- `type`: wajib (`compensation`/`warranty`/`pricing`/`human_error`/`commission`).
- `rules`: JSON valid; wajib.
- `version`: auto-increment.

---

## 2. Aturan Umum

1. **Validasi dua lapis:** Frontend (UX) + Backend (FormRequest/Model) — defense in depth.
2. **Required vs Optional:** Kolom `wajib` = harus diisi sebelum save; `opsional` = bisa null.
3. **Format telepon Indonesia:** `08xxxxxxxxxx` (10-13 digit), validasi regex.
4. **Amount integer (sen):** tidak menerima desimal; konversi di frontend.
5. **Status:** selalu dari daftar resmi (`docs/Naming.md`) — tidak boleh string bebas.

---

## 3. Verifikasi

Konsisten dengan `docs/specification/WorkflowSpecification.md` (status resmi), `docs/domain/Entity.md` (atribut), `18_DataStandards.md` (tipe).
