# 04 — Cardinality

> **Sprint 6.2C · Conceptual Blueprint.** Semua kardinalitas relationship dengan alasan bisnis.

---

## 1. Matriks Kardinalitas

| # | Relationship | Kardinalitas | Alasan Bisnis |
|---|---|---|---|
| 1 | Tenant → Branch | 1:N | Multi-cabang (Pro+). Tenant 1 cabang = 1 branch. |
| 2 | Tenant → User | 1:N | Banyak user per tenant. |
| 3 | Tenant → Customer | 1:N | Banyak pelanggan per tenant. |
| 4 | Tenant → Policy | 1:N | Banyak policy (kompensasi, garansi, harga). |
| 5 | Tenant → ProviderCredential | 1:N | Banyak provider (S3, Midtrans, WA API, AI). |
| 6 | Tenant → Subscription | 1:1 | Satu tenant = satu langganan aktif. |
| 7 | Branch → User | 1:N | User di-assign ke cabang (bisa multi-cabang via pivot). |
| 8 | Branch → InventoryItem | 1:N | Stok per cabang. |
| 9 | Branch → CashShift | 1:N | Banyak shift per cabang. |
| 10 | User → Role | N:M | Multi-role (BR-003, BR-004, BR-011). Pivot `user_role`. |
| 11 | Role → Permission | N:M | Role = kumpulan permission. Pivot `role_permission`. |
| 12 | Customer → Device | 1:N | Customer punya banyak device (BR-019). Device = 1 customer aktif. |
| 13 | Customer → Request | 1:N | Repeat customer. |
| 14 | Device → Request | N:M | Device diservis berkali-kali. Request bisa multi-device. **Pivot `request_devices`.** |
| 15 | Request → ServiceOrder | 1:N | 1 Request → N device → N ServiceOrder (BR-019, corporate). Toko kecil = 1:1. |
| 16 | Request → SalesOrder | 1:N | 1 Request → bisa ada Sales (beli sparepart sambil servis). |
| 17 | Request → Attachment | 1:N | Foto pickup, kondisi awal. |
| 18 | Request → RequestHistory | 1:N | Audit trail. Append-only. |
| 19 | Request → Notification | 1:N | Notifikasi CS & customer. |
| 20 | ServiceOrder → WorkOrder | 1:N | Progressive WO (BR-018). |
| 21 | ServiceOrder → Checklist | 1:N | Banyak item checklist. |
| 22 | ServiceOrder → TechnicianAssignment | 1:N | Assignment history + multi-teknisi per WO. |
| 23 | ServiceOrder → Attachment | 1:N | Foto progress. |
| 24 | ServiceOrder → Warranty | 1:1 (opsional) | Hanya service selesai. |
| 25 | ServiceOrder → InventoryMovement | 1:N | Sparepart dipakai → banyak movement. |
| 26 | SalesOrder → SaleItem | 1:N | Keranjang belanja. |
| 27 | SalesOrder → InventoryMovement | 1:N | Produk terjual → banyak movement. |
| 28 | PurchaseOrder → PurchaseItem | 1:N | Banyak item per PO. |
| 29 | PurchaseOrder → InventoryMovement | 1:N | PO diterima → banyak movement. |
| 30 | CashShift → Deposit | 1:N | Banyak setoran per shift. |
| 31 | Product → InventoryItem | 1:N | Satu produk, stok di banyak cabang. |
| 32 | InventoryItem → InventoryMovement | 1:N | Append-only. Qty = sum(movements). |
| 33 | Warranty → WarrantyClaim | 1:N | Bisa diklaim berkali-kali (BR-012). |
| 34 | WarrantyClaim → SuplierClaim | 0..1 | Tidak wajib ke supplier. |
| 35 | SuplierClaim → Replacement | 0..N | Claim disetujui → N barang pengganti. |
| 36 | Policy → Compensation | 1:N | Policy menentukan banyak kompensasi. |
| 37 | Compensation → User | N:1 | Penerima kompensasi. |
| 38 | ServiceOrder → Compensation | 1:N | Satu servis bisa menghasilkan >1 kompensasi (teknisi + CS). |
| 39 | Supplier → PurchaseOrder | 1:N | Banyak PO ke satu supplier. |
| 40 | Supplier → SuplierClaim | 1:N | Banyak klaim ke satu supplier. |

---

## 2. Kardinalitas Kunci yang Perlu Dijelaskan

### Device ↔ Request (N:M)
- **Mengapa N:M?** Satu Request bisa membawa 3 device (corporate, multi-device visit). Satu device bisa punya 50 Request sepanjang hidupnya (repeat repair). **N:M melalui pivot `request_devices` adalah satu-satunya cara menangani kedua kasus.**
- **Alternatif ditolak:** `request.device_id` (1:1) → tidak mendukung BR-019.

### Request ↔ ServiceOrder (1:N)
- **Mengapa 1:N bukan 1:1?** Lihat `03_Relationship.md` §3. Fondasi harus kuat untuk enterprise; UI menyederhanakan.

### User ↔ Role (N:M)
- **Mengapa N:M?** Target arsitektur (Sprint 5.2 Role Engine). Multi-role = fondasi fleksibilitas. Saat ini 1:1 (kolom `role`) → pivot `user_role` adalah target.

---

## 3. Verifikasi

Semua kardinalitas mengikuti justifikasi business reality. Tidak ada N:M yang dibuat tanpa alasan kuat. N:M hanya untuk: Device↔Request, User↔Role, Role↔Permission — tiga kasus yang benar-benar membutuhkan many-to-many.
