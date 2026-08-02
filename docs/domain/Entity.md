# ServiceKU — Entity

> **Sprint 6.1 · Blueprint Only.** Daftar **Entity** (memiliki identitas kontinu dan lifecycle) beserta identitas, atribut kunci, dan aturannya.
> Blueprint — tidak ada skema database.

---

## 1. Prinsip Entity

- Entity memiliki **identitas** yang kontinu (ID unik per tenant / platform).
- Atribut dapat berubah sepanjang hidupnya; identitas tetap.
- Identitas mengikuti konvensi `Naming.md`: FK = `<tunggal>_id`, tabel = snake_case jamak (nanti).

---

## 2. Daftar Entity

### Platform (Central)
| Entity | Identitas | Atribut kunci | Catatan |
|---|---|---|---|
| Tenant | tenant_id | nama, subdomain, business_type, status | 1 DB per tenant |
| Plan | plan_id | key (trial/basic/pro/enterprise), harga, trial_hari, fitur/limits | dari PlanSeeder |
| Voucher | voucher_id | kode, tipe, nilai, kuota | platform |
| PlatformPayment | payment_id | tenant, plan, status | Midtrans |
| SuperAdmin | admin_id | kredensial, panel admin | terpisah dari user tenant |

### Tenant — Organisasi
| Entity | Identitas | Atribut kunci | Catatan |
|---|---|---|---|
| Branch | branch_id | nama, alamat, status | plan Pro+ multi-branch |
| User | user_id | nama, email, role/roles, branch, status | role saat ini 1 kolom (target multi-role) |
| Position | position_id | nama, level | pelengkap role (target) |
| Role | role_id | key, nama, is_system | 7 resmi + kustom (target) |
| Permission | permission_id | key (module.action) | pusat otorisasi (target data) |
| Policy | policy_id | tipe (kompensasi/garansi/harga), aturan, versi | milik tenant |

### Tenant — Relasi & Operasional
| Entity | Identitas | Atribut kunci | Catatan |
|---|---|---|---|
| Customer | customer_id | nama, telepon, alamat, status | manage_customers |
| CustomerVisit | visit_id | customer, tanggal, catatan, device dibawa | bisa → Service Order |
| Device | device_id | tipe, merek, model, IMEI/serial, customer | riwayat servis |
| ServiceOrder | service_id | nomor servis, customer, device, status (14), teknisi, biaya | core domain |
| WorkOrder | work_order_id | service, teknisi, deskripsi, status | optional |
| ServicePartner | partner_id | nama, komisi, status | onpartner |
| ServicePhoto | photo_id | service, file, catatan | lampiran |
| ServiceHistory | history_id | service, status lama→baru, user, waktu | jejak audit |
| Checklist | checklist_id | service, item, hasil | non-retail |
| Supplier | supplier_id | nama, kontak, saldo hutang | purchases |
| Sparepart/Product | product_id | nama, sku, harga beli/jual, kategori | katalog |

### Tenant — Transaksi
| Entity | Identitas | Atribut kunci | Catatan |
|---|---|---|---|
| InventoryItem | inventory_item_id | branch, product, qty | stok per cabang |
| InventoryMovement | movement_id | item, tipe (masuk/keluar/transfer/adjust), qty, ref | jejak mutasi |
| PurchaseOrder | purchase_id | supplier, status, total, hutang | manage_purchases |
| PurchaseItem | purchase_item_id | purchase, product, qty, harga | anak PO |
| SalesOrder | sale_id | branch, kasir, customer, status, total | manage_sales |
| SaleItem | sale_item_id | sale, product, qty, harga | anak sale |
| CashShift | shift_id | branch, kasir, buka/tutup, saldo awal/akhir | cash register |
| Deposit | deposit_id | shift, jumlah, status (menunggu/konfirmasi) | daily deposit |
| Expense | expense_id | branch, kategori, jumlah, bukti | biaya |

### Tenant — Pasca-Jual
| Entity | Identitas | Atribut kunci | Catatan |
|---|---|---|---|
| Warranty | warranty_id | service, periode mulai/selesai, status | dari service selesai |
| Claim | claim_id | warranty, tanggal, alasan, hasil | diterima/ditolak |
| SupplierClaim | supplier_claim_id | warranty/claim, supplier, status | klaim ke supplier |
| Replacement | replacement_id | claim, product pengganti, qty, stok keluar/masuk | memengaruhi inventory |
| Compensation | compensation_id | user penerima, service, policy, nominal, status | mengikuti policy |

### Tenant — Wawasan
| Entity | Identitas | Atribut kunci | Catatan |
|---|---|---|---|
| ReportDefinition | report_id | tipe laporan, filter, params | laporan |
| DashboardWidget | widget_id | key, permission, posisi, order | target |

---

## 3. Aturan Entity

1. **Tidak menghapus fisik** entity berhistori (Service, Customer, Device, Sale, Movement) — gunakan status terminal/arsip (`delete_models` hanya owner/admin dengan jejak).
2. Setiap entity tenant memiliki `tenant` scope (diimplementasikan via 1 DB per tenant).
3. Entity referensi antar aggregate via ID (`<tunggal>_id`).
4. Penambahan entity baru hanya melalui `docs/domain/` (blueprint) + Module Engine.

---

## 4. Verifikasi

Entity utama (Customer, Device, Service, Branch, User, Purchase, Sales, Cash/Deposit, Warranty, Subscription/Plan, Supplier, Product) terkonfirmasi ada di source. Position, WorkOrder, Compensation, Replacement, Policy sebagai entity **target/blueprint** (belum utuh di source).
