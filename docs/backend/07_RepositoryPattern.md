# 07 — Repository Pattern · 08 — Action Pattern · 09 — Event Architecture · 10 — Queue Architecture

> **Sprint 6.3 · Engineering Blueprint Only.**

---

## Part A — Repository Pattern (07)

### Interface (Domain Layer)
```php
// Domain/Request/RequestRepositoryInterface.php
interface RequestRepositoryInterface
{
    public function find(RequestId $id): ?Request;
    public function findByNumber(string $number): ?Request;
    public function findActiveByBranch(BranchId $branchId): Collection;
    public function findByCustomer(CustomerId $customerId): Collection;
    public function save(Request $request): void;
    public function delete(Request $request): void; // soft delete
}
```

### Implementation (Infrastructure Layer)
```php
// Infrastructure/Persistence/Eloquent/RequestRepository.php
class RequestRepository implements RequestRepositoryInterface
{
    public function find(RequestId $id): ?Request
    {
        return RequestEloquent::find($id)?->toDomain();
        // Atau: Domain model langsung extends Eloquent (practical hybrid)
    }

    public function findActiveByBranch(BranchId $branchId): Collection
    {
        return RequestEloquent::where('branch_id', $branchId)
            ->whereNotIn('status', ['closed', 'cancelled', 'archived'])
            ->whereNull('deleted_at')       // soft delete
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($m) => $m->toDomain());
    }
}
```

### Aturan Repository
1. **Interface di Domain**, Implementation di Infrastructure.
2. **Query scope otomatis** — tenant scope via middleware / trait `HasTenantScope`.
3. **Soft delete** — default exclude `WHERE deleted_at IS NOT NULL`.
4. **Method spesifik** — `findActiveByBranch()`, bukan `where('status', ...)` di Controller.
5. **Return Domain objects** (ideal) atau Eloquent models (practical hybrid — diperbolehkan selama transisi).

---

## Part B — Action Pattern (08)

### Struktur Action
```php
// Application/Request/Actions/CreateRequestAction.php
class CreateRequestAction
{
    public function __construct(
        private RequestRepositoryInterface $requestRepo,
        private CustomerRepositoryInterface $customerRepo,
        private DeviceRepositoryInterface $deviceRepo,
    ) {}

    public function execute(CreateRequestDTO $dto): Request
    {
        // 1. Validate business rules
        $customer = $this->customerRepo->find($dto->customerId)
            ?? throw CustomerNotFoundException::withId($dto->customerId);

        foreach ($dto->deviceIds as $deviceId) {
            $device = $this->deviceRepo->find($deviceId)
                ?? throw DeviceNotFoundException::withId($deviceId);
            // Validate device belongs to customer
        }

        // 2. Create aggregate
        $request = Request::create($dto);

        // 3. Persist
        $this->requestRepo->save($request);

        // 4. Dispatch events
        $request->releaseEvents(); // → Laravel Event Bus

        return $request;
    }
}
```

### Aturan Action
1. **Satu Action = satu use case.** Jangan gabung Create + Update.
2. **Nama deskriptif:** `CreateRequestAction`, `AssignTechnicianAction`, `VoidServiceAction`.
3. **Stateless** — dependency via constructor injection; state hanya di `execute()`.
4. **Throws domain exceptions** untuk aturan bisnis yang dilanggar.
5. **Tidak return HTTP response** — hanya return Domain object. Controller yang wrap Resource.

---

## Part C — Event Architecture (09)

### Domain Event → Listener

```
CreateRequestAction::execute()
  → Request::create() → raise(RequestCreated)
  → releaseEvents()

Event Bus:
  RequestCreated
    ├── CreateAuditLogListener       (sync)  → audit_logs INSERT
    ├── RecordRequestHistoryListener (sync)  → request_history INSERT
    ├── SendNotificationListener     (async) → notifications INSERT + dispatch
    └── UpdateDashboardListener      (async) → aggregate cache
```

### Event Catalog (dari Sprint 6.1)
| Event | Listener | Queue |
|---|---|---|
| RequestCreated | Audit, History, Notification | Sync (Audit + History), Async (Notif) |
| RequestStatusChanged | Audit, Notification | Sync + Async |
| ServiceOrderCreated | Audit, Inventory (allocate) | Sync |
| ServiceStatusChanged | Audit, Notification, Dashboard | Sync (Audit), Async |
| ServiceCompleted | Warranty (create), Finance (aggregate) | Sync |
| SparepartUsed | Inventory (stock out), Finance | Sync |
| SaleCompleted | Inventory, Finance, Cash | Sync |
| StockAdjusted | Audit, Dashboard | Sync |
| WarrantyClaimed | Audit, Notification | Sync |
| CompensationCalculated | Audit, Finance | Sync |

---

## Part D — Queue Architecture (10)

### Job yang di-queue (async)

| Job | Queue | Alasan |
|---|---|---|
| **GenerateInvoicePdf** | `pdf` | dompdf — berat |
| **SendWhatsAppMessage** | `messaging` | External API — bisa lambat/gagal |
| **SendEmailNotification** | `notifications` | External API |
| **UploadToCloudStorage** | `storage` | Upload file besar |
| **AIClassifyRequest** | `ai` | API AI — lambat |
| **GenerateReport** | `reports` | Query agregasi berat |
| **ProcessBackup** | `backup` | Operasi panjang |
| **SyncMarketplaceOrder** | `marketplace` | External API (future) |

### Queue Configuration
- **Driver:** Redis (production) / Database (dev)
- **Retry:** 3x dengan exponential backoff (1m, 5m, 15m)
- **Failed jobs:** `failed_jobs` table + notifikasi Owner
- **Horizon** (production) untuk monitoring queue

### Aturan Queue
1. **Sync untuk audit & integrity** — tidak boleh async.
2. **Async untuk eksternal & berat** — notifikasi, upload, AI, report.
3. **Idempotent jobs** — retry tidak boleh double-effect.
4. **Timeout** per job type (30s default, 5m untuk report/backup).
