# 04 — Domain-Driven Design · 05 — Request Lifecycle · 06 — Service Layer

> **Sprint 6.3 · Engineering Blueprint Only.**

---

## Part A — Domain-Driven Design (04)

### Aggregate Root (contoh: Request)

```php
// Domain/Request/Request.php
class Request extends AggregateRoot
{
    // State
    private RequestId $id;
    private RequestType $type;
    private RequestStatus $status;
    private CustomerId $customerId;
    private BranchId $branchId;
    private ?BranchId $pickupBranchId;

    // Factory method
    public static function create(CreateRequestDTO $dto): self

    // Business methods (modify state + raise events)
    public function schedule(\DateTime $scheduledAt): void       // → RequestScheduled
    public function assign(UserId $technicianId): void           // → RequestAssigned
    public function startProcessing(): void                      // → RequestProcessing
    public function complete(): void                             // → RequestCompleted
    public function cancel(string $reason): void                 // → RequestCancelled

    // Invariant enforcement
    private function guardNotTerminal(): void
    private function guardCanTransitionTo(RequestStatus $new): void
}
```

### Value Object
```php
// Domain/Shared/ValueObjects/Money.php
class Money
{
    public function __construct(
        private int $amountInSen,    // 5000000 = Rp 50.000
        private string $currency = 'IDR'
    ) {}

    public function add(Money $other): Money
    public function subtract(Money $other): Money
    public function isGreaterThan(Money $other): bool
    // No setters — immutable
}
```

### Domain Service
```php
// Domain/Service/Services/ServicePricingService.php
class ServicePricingService
{
    // Stateless — pure business logic crossing aggregates
    public function calculate(
        ServiceOrder $order,
        ProductRepositoryInterface $products,
        PolicyRepositoryInterface $policies
    ): Money;
}
```

### Domain Event
```php
// Domain/Request/Events/RequestCreated.php
class RequestCreated extends DomainEvent
{
    public function __construct(
        public readonly RequestId $requestId,
        public readonly CustomerId $customerId,
        public readonly RequestType $type,
    ) {}
}
```

---

## Part B — Request Lifecycle (05)

### Alur Lengkap

```
HTTP POST /tenant/requests
  → StoreRequestRequest (FormRequest — validasi)
    → RequestController::store()
      → CreateRequestAction::execute(dto)
        → Request::create(dto)          // Aggregate Root — factory
        → $request->raise(RequestCreated)
        → RequestRepository::save($request)
        → dispatch(RequestCreated)      // Listener: audit, notif, history
      → return RequestResource($request)

HTTP POST /tenant/requests/{id}/process
  → RequestController::process()
    → ForkToServiceOrderAction::execute($request, $deviceId)
      → ServiceOrder::fromRequest($request, $device)
      → $request->startProcessing()    // → RequestProcessing event
      → ServiceOrderRepository::save($serviceOrder)
      → InventoryService::allocate($parts)  // Domain Service
      → dispatch(RequestProcessing)
        → CreateAuditLogListener
        → SendNotificationListener
        → UpdateDashboardListener
      → return ServiceResource($serviceOrder)
```

### Status Transitions
| Action | From Status | To Status | Event |
|---|---|---|---|
| Create | — | created | RequestCreated |
| Schedule | created | scheduled | RequestScheduled |
| Pickup | waiting_pickup | picked_up | RequestPickedUp |
| Receive | picked_up/in_transit | received | RequestReceived |
| Assign | created/received | assigned | RequestAssigned |
| Process | assigned | processing | RequestProcessing |
| Complete | processing | completed | RequestCompleted |
| Deliver | completed | delivered | RequestDelivered |
| Close | completed/delivered | closed | RequestClosed |
| Cancel | any non-terminal | cancelled | RequestCancelled |

---

## Part C — Service Layer (06)

### Aturan Controller
1. **Controller HANYA** menerima input → panggil Action → return response.
2. **Tidak boleh** Query Builder / Eloquent langsung di Controller.
3. **Tidak boleh** business logic di Controller.
4. **Maksimal 50 baris** per method controller.

### Contoh Controller (Ideal)
```php
// Http/Controllers/Tenant/RequestController.php
class RequestController extends Controller
{
    public function store(
        StoreRequestRequest $formRequest,
        CreateRequestAction $action
    ): RequestResource {
        $dto = CreateRequestDTO::fromRequest($formRequest);
        $request = $action->execute($dto);
        return new RequestResource($request);
    }

    public function assign(
        Request $request,
        AssignRequestRequest $formRequest,
        AssignRequestAction $action
    ): RequestResource {
        $dto = AssignRequestDTO::fromRequest($formRequest);
        $request = $action->execute($request, $dto);
        return new RequestResource($request);
    }
}
```

### Dependency Injection
- Actions, Repositories, Services di-inject via constructor (Laravel Service Container).
- Binding dilakukan di `AppServiceProvider`:
  ```php
  $this->app->bind(RequestRepositoryInterface::class, RequestRepository::class);
  $this->app->bind(StorageInterface::class, fn() => ProviderFactory::make('storage'));
  ```
