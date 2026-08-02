# ServiceKU — Factory

> **Sprint 6.1 · Blueprint Only.** **Factory** = pembentuk Aggregate/Entity yang kompleks dengan **invariant awal** yang benar. Menyembunyikan detail pembuatan.
> Blueprint — bukan implementasi.

---

## 1. Prinsip Factory

- Dipakai saat pembuatan objek **kompleks** atau membutuhkan **validasi/invariant awal**.
- Memastikan objek lahir dalam **state valid** (tidak bisa dibuat "setengah jadi").
- Menghasilkan **Domain Event** `*Created` saat berhasil.
- Berbeda dari `store()` controller biasa — factory menegakkan aturan domain.

---

## 2. Daftar Factory (Blueprint)

| # | Factory | Menghasilkan | Invariant awal |
|---|---|---|---|
| 1 | **TenantFactory** | Tenant + DB + onboarding template (business type) | business type valid; subdomain unik; plan trial |
| 2 | **BranchFactory** | Branch | nama unik; batas cabang (plan) |
| 3 | **UserFactory** | User + role | role valid; email unik; minimal 1 role |
| 4 | **RoleFactory** | Role + permission | role resmi/kustom; permission valid |
| 5 | **PolicyFactory** | Policy (kompensasi/garansi/harga) | tipe & aturan valid; versi baru |
| 6 | **CustomerFactory** | Customer | kontak valid; deteksi duplikat |
| 7 | **DeviceFactory** | Device | identitas (IMEI/serial) unik per tenant |
| 8 | **ServiceOrderFactory** | Service Order | customer+device ada; status awal `menunggu_alokasi`; estimasi biaya (opsional) |
| 9 | **WorkOrderFactory** | Work Order | induk Service Order ada & tidak terminal |
| 10 | **WarrantyFactory** | Warranty | hanya dari Service selesai; periode mengikuti policy |
| 11 | **ClaimFactory** | Claim | dalam masa garansi; alasan valid |
| 12 | **SupplierClaimFactory** | Supplier Claim | supplier valid; warranty ada |
| 13 | **ReplacementFactory** | Replacement | claim diterima; stok tersedia/tak masalah (adjust) |
| 14 | **PurchaseOrderFactory** | Purchase Order | supplier ada; item valid; (opsional) dari indent/reorder |
| 15 | **SalesOrderFactory** | Sales Order | stok cukup; harga valid; (opsional) customer |
| 16 | **CashShiftFactory** | Cash Shift | tidak ada shift terbuka di branch yang sama |
| 17 | **CompensationFactory** | Compensation | policy aktif; dasar (service) valid |
| 18 | **SubscriptionFactory** | Subscription | plan valid; status awal trial/active |

---

## 3. Alur Contoh (Business Reality)

**Service Order lahir dari kunjungan:**
`CustomerVisit` + `Device` → `ServiceOrderFactory` → Service Order (`menunggu_alokasi`) → event `ServiceOrderCreated` → (jika butuh part) konsultasi Inventory.

**Warranty lahir dari servis selesai:**
`ServiceOrder` (selesai) → `WarrantyFactory` (periode policy) → event `WarrantyCreated`.

**Replacement lahir dari klaim supplier:**
`SupplierClaim` (diterima) → `ReplacementFactory` → replacement → event `ReplacementIssued` → Inventory bertambah.

---

## 4. Aturan Factory

1. Factory **bukan** sekadar constructor — ia menegakkan invariant & menghasilkan event.
2. Nama: `<Domain>Factory`.
3. Tidak menyimpan data (memanggil Repository untuk persist).
4. Jangan memakai factory untuk entity sederhana (cukup dibuat langsung).

---

## 5. Verifikasi

Saat ini pembuatan objek dilakukan di controller (store method). Konsep factory sebagai lapisan pembentukan dengan invariant adalah **target/blueprint** — konsisten dengan Domain Service & Repository.
