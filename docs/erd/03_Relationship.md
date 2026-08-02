# 03 — Relationship (Business Driven)

> **Sprint 6.2C · Conceptual Blueprint.** Setiap relationship harus lahir dari Business Reality, bukan karena mudah. Setiap relationship harus menjawab: **Mengapa?**

---

## 1. Customer ↔ Device (1:N)

**Relasi:** `customers.id` → `devices.customer_id`

**Business Reality:** BR-019 (Multi Device Visit). Satu customer bisa memiliki banyak perangkat (HP + laptop + tablet). Satu device pada satu waktu milik satu customer (bisa transfer kepemilikan dengan catatan).

**Dampak:** Device punya riwayat servis sendiri; lifetime cost (BR-014) dihitung per device. Customer dapat melihat semua device-nya.

**Alternatif ditolak:** Device tanpa customer (anonim) → ditolak karena menyulitkan tracking & garansi.

---

## 2. Device ↔ Request (1:N)

**Relasi:** `devices.id` → `requests` via `request_devices` (pivot, atau `device_id` di `requests` untuk 1 device)

**Business Reality:** BR-019 (multi-device visit) + Repeat Repair. Satu device diservis berkali-kali sepanjang hidupnya → banyak Request. Satu Request bisa membawa banyak device (corporate/pickup). **Diperlukan pivot `request_devices`** untuk 1 Request → N Device.

**Dampak:** `request_devices` memungkinkan 1 Request → N ServiceOrder (setiap device punya ServiceOrder sendiri).

**Alternatif ditolak:** Request hanya 1 device → ditolak karena corporate batch (BR-005 future, enterprise).

---

## 3. Request ↔ ServiceOrder (1:N)

**Relasi:** `requests.id` → `service_orders.request_id`

**Mengapa 1:N padahal toko kecil biasanya 1:1?**
- **Business Reality:** BR-019. Satu kunjungan/pickup bisa membawa 3 device → 1 Request → 3 ServiceOrder (satu per device).
- **Corporate:** 1 Request corporate → 20 ServiceOrder (batch).
- **Progressive Complexity:** UI toko kecil cukup menampilkan 1 ServiceOrder. Tapi database harus mendukung 1:N agar toko tidak perlu migrasi saat berkembang.
- **Enterprise ready:** Desain fondasi, bukan optimasi tampilan. UI menyederhanakan; DB tetap kuat.

**Dampak:** `request_id` di `service_orders` memungkinkan multiple service orders per request. Origin trace lengkap.

**Alternatif ditolak:** 1:1 Request↔ServiceOrder → ditolak karena memaksa 3 Request untuk 3 device (tidak natural, BR-019).

---

## 4. Customer ↔ Request (1:N)

**Relasi:** `customers.id` → `requests.customer_id` (nullable untuk walk-in guest)

**Business Reality:** Customer bisa membuat banyak Request sepanjang waktu (repeat customer). Walk-in guest (BR-020) = customer opsional (`customer_id` nullable, `is_walk_in=true`).

**Dampak:** Semua Request customer terlacak. Walk-in didukung.

---

## 5. Request ↔ Attachment (1:N)

**Relasi:** `requests.id` → `attachments` (polymorphic: `attachable_type='Request'`, `attachable_id=request_id`)

**Business Reality:** Foto device saat pickup (BR-001), foto kondisi awal, foto identitas. Attachment terpusat di satu tabel polymorphic agar semua domain bisa pakai.

**Dampak:** Satu tabel `attachments` untuk Request, ServiceOrder, Product, WarrantyClaim. Konsisten dengan Sprint 6.2A §07.

---

## 6. Request ↔ RequestHistory (1:N)

**Relasi:** `requests.id` → `request_history.request_id`

**Business Reality:** BR-015 (Human Error), Data Is Sacred. Setiap perubahan status, assign, cancel → 1 baris history. Append-only.

**Dampak:** Audit trail request lengkap. Tidak bisa dihapus.

---

## 7. Request ↔ Notification (1:N)

**Relasi:** `requests.id` → `notifications` (polymorphic)

**Business Reality:** Request dibuat → notifikasi CS. Status berubah → notifikasi customer. Sprint 6.2B §10.

---

## 8. ServiceOrder ↔ WorkOrder (1:N)

**Relasi:** `service_orders.id` → `work_orders.service_order_id`

**Business Reality:** BR-018 (Progressive Work Order). Servis awalnya 1 pekerjaan. Saat diagnosa ditemukan kerusakan tambahan → WO baru ditambahkan. Progresif.

**Dampak:** Satu tiket bisa punya multiple work order. UI bisa tampilkan sebagai daftar pekerjaan.

**Alternatif ditolak:** 1:1 ServiceOrder↔WorkOrder → ditolak karena tidak mendukung BR-018.

---

## 9. ServiceOrder ↔ Checklist (1:N)

**Relasi:** `service_orders.id` → `checklists.service_order_id`

**Business Reality:** Checklist perangkat servis (non-retail). Banyak item checklist per tiket.

---

## 10. ServiceOrder ↔ TechnicianAssignment (1:N)

**Relasi:** `service_orders.id` → `technician_assignments.service_order_id`

**Business Reality:** BR-006 (Technician Specialization) + BR-018 (progressive). Satu tiket bisa di-assign ke teknisi berbeda untuk WO yang berbeda. Assignment history.

---

## 11. ServiceOrder ↔ Attachment (1:N)

**Relasi:** Polymorphic — `attachments` (foto progress, checklist visual, tanda tangan)

---

## 12. Warranty ↔ ServiceOrder (1:1)

**Relasi:** `warranties.service_order_id` → `service_orders.id`

**Business Reality:** Garansi lahir dari servis selesai. Satu servis = satu garansi. 1:1.

---

## 13. Warranty ↔ WarrantyClaim (1:N)

**Relasi:** `warranties.id` → `warranty_claims.warranty_id`

**Business Reality:** BR-012 (Warranty Resolution). Satu garansi bisa diklaim berkali-kali dalam periode policy.

---

## 14. WarrantyClaim ↔ SuplierClaim (0..1)

**Relasi:** `warranty_claims.id` → `suplier_claims.warranty_claim_id`

**Business Reality:** BR-013 (Supplier Warranty). Klaim bisa (tidak wajib) diteruskan ke supplier.

---

## 15. SuplierClaim ↔ Replacement (0..N)

**Relasi:** `suplier_claims.id` → `replacements.suplier_claim_id`

**Business Reality:** BR-013. Claim disetujui → barang pengganti masuk inventory.

---

## 16. Branch ↔ InventoryItem (1:N)

**Relasi:** `branches.id` → `inventory_items.branch_id`

**Business Reality:** Multi-branch. Stok per cabang. Transfer stok antar cabang via `inventory_movements`.

---

## 17. Product ↔ InventoryItem (1:N)

**Relasi:** `products.id` → `inventory_items.product_id`. Composite unique: `(branch_id, product_id)`.

**Business Reality:** Satu produk punya stok di banyak cabang (multi-branch).

---

## 18. InventoryItem ↔ InventoryMovement (1:N)

**Relasi:** `inventory_items.id` → `inventory_movements.inventory_item_id`

**Business Reality:** Data Is Sacred. Setiap mutasi tercatat. Qty = sum(movements). Tidak ada update qty langsung.

---

## 19. SalesOrder ↔ SaleItem (1:N)

**Relasi:** `sales_orders.id` → `sale_items.sales_order_id`

**Business Reality:** Satu transaksi bisa berisi banyak produk (keranjang belanja).

---

## 20. PurchaseOrder ↔ PurchaseItem (1:N)

**Relasi:** `purchase_orders.id` → `purchase_items.purchase_order_id`

**Business Reality:** Satu PO bisa berisi banyak produk.

---

## 21. CashShift ↔ Deposit (1:N)

**Relasi:** `cash_shifts.id` → `deposits.shift_id`

**Business Reality:** Satu shift bisa menghasilkan beberapa setoran.

---

## 22. Policy ↔ Compensation (1:N)

**Relasi:** `policies.id` → `compensations.policy_id`

**Business Reality:** BR-016 (Compensation Policy). Policy menentukan aturan; kompensasi mengikuti policy. Versioning menjaga kompensasi historis tetap valid.

---

## 23. User ↔ Role (N:M)

**Relasi:** `users` ↔ `user_role` ↔ `roles`

**Business Reality:** BR-003 (Owner Family) + BR-004 (Manager Multi Function) + BR-011 (No SPOF). Multi-role = fondasi fleksibilitas. Saat ini: 1 kolom `role`. Target: pivot.

---

## 24. Role ↔ Permission (N:M)

**Relasi:** `roles` ↔ `role_permission` ↔ `permissions`

**Business Reality:** Permission Engine (target). Role = kumpulan permission atomic. Bukan hardcode string.

---

## 25. Tenant ↔ ProviderCredential (1:N)

**Relasi:** `tenants.id` → `provider_credentials.tenant_id`

**Business Reality:** Sprint 6.2B. Tenant bisa mengaktifkan banyak provider (S3, Midtrans, WA API, AI). Credential terenkripsi per tenant.

---

## 26. Tenant ↔ Subscription (1:1)

**Relasi:** `tenants.id` → `subscriptions.tenant_id` (central DB)

**Business Reality:** Satu tenant = satu subscription aktif.

---

## 27. ServiceOrder ↔ InventoryMovement (1:N)

**Relasi:** `inventory_movements` via polymorphic reference (`reference_type`, `reference_id`)

**Business Reality:** Sparepart dipakai servis → `StockOut` movement dengan reference ke ServiceOrder.

---

## 28. SalesOrder ↔ InventoryMovement (1:N)

**Relasi:** via polymorphic reference

**Business Reality:** Penjualan produk → `StockOut`.

---

## 29. PurchaseOrder ↔ InventoryMovement (1:N)

**Relasi:** via polymorphic reference

**Business Reality:** PO diterima → `StockIn`.

---

## 30. Replacement ↔ InventoryMovement (1:N)

**Relasi:** via polymorphic reference

**Business Reality:** Replacement masuk → `StockIn`.

---

## 31. ServiceOrder ↔ Warranty (1:1, opsional)

**Relasi:** `service_orders.id` → `warranties.service_order_id`

Hanya service `selesai` yang menghasilkan warranty.

---

## Verifikasi

Setiap relationship di atas memiliki justifikasi Business Reality (BR-001..BR-020). Tidak ada relationship "karena mudah". Semua mengikuti prinsip **Business Driven**, **Progressive Complexity**, **Grow Without Migration**.
