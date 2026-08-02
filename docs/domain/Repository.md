# ServiceKU — Repository

> **Sprint 6.1 · Blueprint Only.** **Repository** = antarmuka (interface) akses data untuk tiap **Aggregate Root**; menyembunyikan detail persistence (DB, tenancy).
> Blueprint — bukan implementasi, bukan tabel.

---

## 1. Prinsip Repository

1. **Satu Repository per Aggregate Root** — bukan per tabel.
2. Mengembalikan **Aggregate** utuh (root + child yang dibutuhkan), bukan row parsial.
3. Menyembunyikan: 1 DB per tenant, SQLite/MySQL, pagination, caching.
4. Digunakan oleh Domain Service & Application layer; tidak oleh entity/domain langsung.
5. Konsisten dengan tenant isolation — query otomatis di-scope tenant/branch.

---

## 2. Daftar Repository (Blueprint)

| Repository | Aggregate Root | Operasi utama (konseptual) |
|---|---|---|
| TenantRepository | Tenant | create (provisioning), findByDomain, updateBusinessType, updateSettings |
| BranchRepository | Branch | create, findActive, findById, listByTenant |
| UserRepository | User | create, findByEmail, findWithRoles, assignRole, suspend |
| RoleRepository | Role | seed, findById, findByKey, updatePermissions, merge |
| PermissionRepository | Permission | allByModule, find(key) |
| PolicyRepository | Policy | findActive(type), findByTenant, versioning |
| CustomerRepository | Customer | create, search, findWithDevices, findWithVisits |
| DeviceRepository | Device | create, findBySerial/IMEI, historyOf |
| ServiceOrderRepository | ServiceOrder | create, findByNumber, findByStatus, findByCustomer, findActive, transitionTo |
| WorkOrderRepository | WorkOrder | create, assignTechnician, findOpen |
| PartnerRepository | ServicePartner | create, findActive, findById |
| SupplierRepository | Supplier | create, search, findWithBalance |
| ProductRepository | Sparepart/Product | create, search, findById, findBySku |
| InventoryRepository | InventoryItem | findByBranch, adjust, transfer, movements, lowStock |
| PurchaseOrderRepository | PurchaseOrder | create, receive, pay, findBySupplier, findOpen |
| SalesOrderRepository | SalesOrder | create, findByStatus, findByCashier, findByBranch, void |
| CashShiftRepository | CashShift | open, close, findByBranch, findOpenShift |
| DepositRepository | Deposit | create, confirm, findByShift |
| WarrantyRepository | Warranty | create, findByService, findActive, findByDevice |
| ClaimRepository | Claim | create, approve/reject, findByWarranty |
| SupplierClaimRepository | SupplierClaim | create, updateStatus |
| ReplacementRepository | Replacement | create, findByClaim |
| CompensationRepository | Compensation | create, findByUser, findByPeriod, approve |
| SubscriptionRepository | Subscription | findByTenant, renew, suspend, history |
| ReportRepository | ReportDefinition | run, save, export |

---

## 3. Pola Query Kunci (Blueprint)

| Kebutuhan | Repository | Catatan |
|---|---|---|
| Tiket servis aktif per branch | ServiceOrderRepository | filter status non-terminal + branch |
| Stok menipis (reorder) | InventoryRepository | qty ≤ threshold |
| Hutang supplier | SupplierRepository / PurchaseOrderRepository | aggregate dari PO belum lunas |
| Riwayat servis per device | DeviceRepository / ServiceOrderRepository | by IMEI/serial |
| Laporan pendapatan periode | ReportRepository | agregasi (target: aggregate table) |
| Cek plan feature | SubscriptionRepository + Feature | full/read_only/none |

---

## 4. Aturan Repository

1. Interface didefinisikan di domain layer; implementasi (Eloquent/DB) di infrastructure.
2. Semua operasi **tenant-scoped**; tidak ada query lintas tenant.
3. Anti-corruption: tiap Bounded Context punya repository-nya sendiri.
4. Jangan menaruh logika bisnis di repository (hanya akses data).
5. Blueprint ini menetapkan kontrak; implementasi menyusul (bukan sekarang).

---

## 5. Verifikasi

Kondisi saat ini: akses data langsung via Eloquent model tenant (`App\Models\Tenant\*`). Konsep repository sebagai interface adalah **target/blueprint** — konsisten dengan `docs/architecture-engine/` (evolusi bertahap).
