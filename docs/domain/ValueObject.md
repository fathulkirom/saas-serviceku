# ServiceKU — Value Object

> **Sprint 6.1 · Blueprint Only.** **Value Object (VO)** = objek tanpa identitas, nilainya yang penting; **immutable**; dapat dibandingkan berdasarkan nilai.
> Blueprint — bukan skema database.

---

## 1. Daftar Value Object

### Keuangan & Harga
| VO | Isi | Berlaku untuk |
|---|---|---|
| Money | jumlah + mata uang (IDR) | harga, biaya, total, kompensasi |
| Price | harga jual per unit (historis) | Product, SaleItem |
| Discount | tipe (nominal/persen) + nilai | Sales, Service |
| Tax | tipe + persen + nilai | Sales, Purchase |
| ServiceFee | biaya jasa servis | Service Order |
| CostPrice | harga beli/COGS | Product, PurchaseItem |
| DepositAmount | nominal setoran | Deposit |
| CompensationValue | nominal kompensasi + dasar perhitungan | Compensation |

### Waktu & Durasi
| VO | Isi | Berlaku untuk |
|---|---|---|
| WarrantyPeriod | durasi + unit (hari/bulan) | Warranty, Policy |
| Duration | durasi umum (janji selesai) | Service Order |
| SLA | target waktu selesai | Service Order |
| ShiftRange | waktu buka–tutup | CashShift |

### Identitas & Kontak
| VO | Isi | Berlaku untuk |
|---|---|---|
| PhoneNumber | nomor + validasi | Customer, Supplier, User |
| EmailAddress | email valid | User, Customer, Tenant |
| Address | alamat lengkap | Branch, Customer, Supplier |
| DeviceSpec | merek, model, varian, IMEI/serial | Device |

### Status & State (enum)
| VO | Nilai (sumber) | Berlaku untuk |
|---|---|---|
| ServiceStatus | 14 nilai (`menunggu_alokasi` … `diambil`) | Service Order |
| PaymentStatus | pending/success/failed/expired/refunded | Sales, Payment |
| SubscriptionStatus | trial/active/expired/suspended | Subscription |
| BusinessType | full_service/aksesoris_service/aksespare_service/gadget_full/retail_only | Tenant |
| UserRoleKey | owner/admin/manager/head_store/cs/technician/cashier/courier/custom | User |
| FeatureLevel | full/read_only/none | Plan–Feature |
| BranchStatus | aktif/nonaktif | Branch |

### Kuantitas & Pengukuran
| VO | Isi | Berlaku untuk |
|---|---|---|
| StockQty | jumlah stok (integer/desimal) | InventoryItem |
| MovementQty | jumlah mutasi + arah | InventoryMovement |
| Percentage | persen (diskon, komisi) | Discount, Compensation, Policy |
| Rating | nilai/rating | Partner, Service (target) |

### Lain-lain
| VO | Isi | Berlaku untuk |
|---|---|---|
| TrackingCode | nomor tiket/nota unik | ServiceOrder, Sales |
| ChecklistAnswer | item + hasil (ya/tidak/NA) | Checklist |
| CompensationRule | skema (persen/nominal/tier) + syarat | Policy |
| PlanLimits | max_users, max_branches, kuota | Plan |
| PermissionKey | `module.action` | Permission |

---

## 2. Aturan Value Object

1. **Immutable** — dibuat sekali; perubahan = objek baru.
2. **Equality by value** — dua `Money(50000, IDR)` dianggap sama.
3. **Domain value, bukan kolom lepas** — nilai seperti status/payment tidak boleh berupa string bebas di seluruh kode; gunakan VO/enum yang sama (konsisten `docs/Naming.md` §7).
4. VO tidak memiliki ID dan tidak disimpan sebagai entity; bisa diserialisasi (mis. JSON `features`) untuk persistence.
5. Jangan membuat nilai status/role/feature baru yang berbeda dari daftar resmi.

---

## 3. Verifikasi

Status/role/business type/feature/payment/subscription sesuai daftar resmi di `docs/Naming.md` & `docs/specification/*` (terkonfirmasi source). Pengelompokan VO di atas adalah **blueprint** pemodelan (belum ada kelas khusus di source).
