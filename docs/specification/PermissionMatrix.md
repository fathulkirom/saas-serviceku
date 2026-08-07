# ServiceKU — Permission Matrix

> Matriks **aksi (operation)** per role, diturunkan dari `role_permissions` & `canX()` (source). Operasi: **C**reate, **R**ead, **U**pdate, **D**elete, **A**pprove, **E**xport, **P**rint, **U**pload, **D**ownload, **As**sign, **Cl**ose, **Ca**ncel.
>
> Legend: ✅ = diizinkan (source) · 👁 = read/terbatas · ❌ = tidak diizinkan · **PV** = Perlu Verifikasi (belum pasti dari source).
>
> Super Admin tidak tercantum per-modul: ia mengelola **platform** (tenant/plan/voucher/payment/backup/logs) — bukan operasional tenant.

---

## 1. Matrix Operasi per Role

### 1.1 Service
| Operasi | Owner | Manager | Admin | CS | Kasir | Teknisi |
|---|---|---|---|---|---|---|
| Create | ✅ | ✅ | ✅ | ✅ | ❌ | 👁 |
| Read | ✅ | ✅ | ✅ | ✅ | 👁 | ✅ |
| Update status | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ (work) |
| Assign teknisi | ✅ | ❌ | ✅ | ✅ | ❌ | ❌ |
| Upload foto | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ |
| Export / Print | ✅ | 👁 | ✅ | ❌ | ❌ | ❌ |
| Cancel | ✅ | ❌ | ✅ | 👁 | ❌ | ❌ |
| Void / Close | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Delete | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |

*Cancel service: CS dapat membatalkan tiket awal (PV). Void/delete = `void_transactions`/`delete_models` (owner/admin).*

### 1.2 Customer
| Operasi | Owner | Manager | Admin | CS | Kasir | Teknisi |
|---|---|---|---|---|---|---|
| Create | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Read | ✅ | ✅ | ✅ | ✅ | 👁 | ❌ |
| Update | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Delete | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| Export | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |

*Semua dari `manage_customers` (owner/admin/manager/cs).*

### 1.3 Product / Inventory
| Operasi | Owner | Manager | Admin | CS | Kasir | Teknisi |
|---|---|---|---|---|---|---|
| Create | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Read | ✅ | ✅ | ✅ | ❌ | ✅ | 👁 |
| Update | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Delete | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Transfer stok | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Adjust stok | ✅ | 👁 | ✅ | ❌ | ❌ | ❌ |
| Reorder | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Quick Stock | ✅ | 👁 | 👁 | ❌ | ❌ | ❌ |

*Dari `manage_products`; Delete mengikuti capability `delete_models` (owner/admin — sinkron dengan `ProductPolicy`); transfer/adjust butuh feature `transfer_stock`; `quick_stock` hanya di `owner` (source).*

### 1.4 Penjualan (POS)
| Operasi | Owner | Manager | Admin | CS | Kasir | Teknisi |
|---|---|---|---|---|---|---|
| Create transaksi | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| Read nota | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| Update draft | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| Void | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Delete | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Refund | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Print nota | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |

*Dari `manage_sales` (owner/admin/manager/cashier) + `void_transactions`/`delete_models` (owner/admin).*

### 1.5 Kas & Setoran
| Operasi | Owner | Manager | Admin | CS | Kasir | Teknisi |
|---|---|---|---|---|---|---|
| Buka/tutup shift | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| Buat setoran | ✅ | ✅ | ✅ | ❌ | 👁 | ❌ |
| Konfirmasi setoran | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| Adjust kas | ✅ | 👁 | ✅ | ❌ | ❌ | ❌ |
| Export kas | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |

*`manage_cash_register` (owner/admin/manager/cashier); `canConfirmDeposit` (owner/admin). Setoran kasir = PV.*

### 1.6 Pembelian
| Operasi | Owner | Manager | Admin | CS | Kasir | Teknisi |
|---|---|---|---|---|---|---|
| Create PO | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Terima barang | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Update | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Void | ✅ | 👁 | ✅ | ❌ | ❌ | ❌ |
| Bayar hutang | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |

*Dari `manage_purchases` (owner/admin/manager).*

### 1.7 Supplier
| Operasi | Owner | Manager | Admin | CS | Kasir | Teknisi |
|---|---|---|---|---|---|---|
| CRUD | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |

### 1.8 Indent
| Operasi | Owner | Manager | Admin | CS | Kasir | Teknisi |
|---|---|---|---|---|---|---|
| Create | ✅ | ✅ | ✅ | ✅ | ❌ | 👁 |
| Read | ✅ | ✅ | ✅ | ✅ | ❌ | 👁 |
| Konfirmasi datang | ✅ | 👁 | ✅ | 👁 | ❌ | ❌ |
| Cancel | ✅ | 👁 | ✅ | 👁 | ❌ | ❌ |

*`manage_indents` (owner/admin/manager/cs).*

### 1.9 User & Role
| Operasi | Owner | Manager | Admin | CS | Kasir | Teknisi |
|---|---|---|---|---|---|---|
| Buat/ubah/hapus user | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| Ubah role | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

*`canManageUsers` = owner saja.*

### 1.10 Settings & Cabang
| Operasi | Owner | Manager | Admin | CS | Kasir | Teknisi |
|---|---|---|---|---|---|---|
| Pengaturan toko | ✅ | 👁 | 👁 | ❌ | ❌ | ❌ |
| Kelola cabang | ✅ | 👁 | 👁 | ❌ | ❌ | ❌ |

*`canManageSettings`/`canManageBranch` = owner; admin lihat sebagian (PV).*

### 1.11 Laporan
| Operasi | Owner | Manager | Admin | CS | Kasir | Teknisi |
|---|---|---|---|---|---|---|
| Lihat laporan | ✅ | ✅ | ✅ | 👁 | 👁 | ❌ |
| Export laporan | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| Print laporan | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |

*Feature `reports`: full untuk owner/admin/manager; CS/Kasir tergantung plan (PV).*

### 1.12 Dokumen (SOP/KB/QuickReply)
| Operasi | Owner | Manager | Admin | CS | Kasir | Teknisi |
|---|---|---|---|---|---|---|
| Kelola | ✅ | 👁 | 👁 | ❌ | ❌ | ❌ |
| Lihat | ✅ | ✅ | ✅ | ✅ | ❌ | 👁 |

*PV untuk detail per role.*

---

## 2. Ringkasan Capability (Sumber `role_permissions`)

| Capability | Owner | Manager | Admin | CS | Kasir | Teknisi |
|---|---|---|---|---|---|---|
| manage_users | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| manage_settings | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| manage_finance | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| manage_products | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| manage_customers | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| manage_sales | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| manage_cash_register | ✅ | ✅ | ✅ | ❌ | ✅ | ❌ |
| manage_deposits | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| manage_purchases | ✅ | ✅ | ✅ | ❌ | ❌ | ❌ |
| manage_branches | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |
| manage_indents | ✅ | ✅ | ✅ | ✅ | ❌ | ❌ |
| void_transactions | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| assign_technician | ✅ | ❌ | ✅ | ✅ | ❌ | ❌ |
| work_on_services | ✅ | ✅ | ✅ | ✅ | ❌ | ✅ |
| delete_models | ✅ | ❌ | ✅ | ❌ | ❌ | ❌ |
| quick_stock | ✅ | ❌ | ❌ | ❌ | ❌ | ❌ |

---

## 3. Verifikasi Sumber

**Terkonfirmasi:** seluruh "Capability" pada §2 persis dari `role_permissions` (HandleInertiaRequests).

**Perlu Verifikasi:** pemetaan operation→capability per halaman (beberapa halaman mungkin mengizinkan/ membatasi lebih detail via Policy/form request); read-only untuk laporan/monitoring per plan; konfirmasi setoran kasir; delete supplier/customer guard.
