# 02 — Folder Structure · 03 — Module Architecture

> **Sprint 6.3 · Engineering Blueprint Only.**

---

## Part A — Folder Structure (02)

```
laravel/
├── app/
│   ├── Domain/                           # Domain Layer (Business Logic)
│   │   ├── Shared/                       # Shared abstractions
│   │   │   ├── ValueObjects/
│   │   │   │   ├── Money.php
│   │   │   │   ├── PhoneNumber.php
│   │   │   │   ├── EmailAddress.php
│   │   │   │   ├── Address.php
│   │   │   │   ├── ServiceStatus.php
│   │   │   │   ├── PaymentStatus.php
│   │   │   │   └── RequestStatus.php
│   │   │   ├── Contracts/
│   │   │   │   ├── RepositoryInterface.php
│   │   │   │   ├── StorageInterface.php
│   │   │   │   ├── MessagingInterface.php
│   │   │   │   ├── PaymentInterface.php
│   │   │   │   └── AIInterface.php
│   │   │   ├── Events/
│   │   │   │   └── DomainEvent.php       # Base domain event
│   │   │   └── Exceptions/
│   │   │       ├── DomainException.php
│   │   │       ├── BusinessRuleException.php
│   │   │       └── InvariantViolationException.php
│   │   │
│   │   ├── Tenant/                       # 1 module = 1 subfolder
│   │   │   ├── Tenant.php                # Aggregate Root (Eloquent model)
│   │   │   ├── TenantRepositoryInterface.php
│   │   │   ├── Events/
│   │   │   │   ├── TenantCreated.php
│   │   │   │   └── TenantSuspended.php
│   │   │   └── Exceptions/
│   │   │       └── TenantNotFoundException.php
│   │   │
│   │   ├── Customer/
│   │   │   ├── Customer.php
│   │   │   ├── CustomerRepositoryInterface.php
│   │   │   ├── Device.php
│   │   │   ├── Events/
│   │   │   │   ├── CustomerCreated.php
│   │   │   │   └── DeviceRegistered.php
│   │   │   └── Exceptions/
│   │   │
│   │   ├── Request/                      # ADR-001 Core Entry Point
│   │   │   ├── Request.php               # Aggregate Root
│   │   │   ├── RequestRepositoryInterface.php
│   │   │   ├── ValueObjects/
│   │   │   │   ├── RequestType.php
│   │   │   │   ├── RequestSource.php
│   │   │   │   └── RequestChannel.php
│   │   │   ├── Events/
│   │   │   │   ├── RequestCreated.php
│   │   │   │   ├── RequestScheduled.php
│   │   │   │   ├── RequestAssigned.php
│   │   │   │   ├── RequestProcessing.php
│   │   │   │   ├── RequestCompleted.php
│   │   │   │   └── RequestCancelled.php
│   │   │   └── Exceptions/
│   │   │
│   │   ├── Service/                      # Service Order
│   │   ├── Sales/
│   │   ├── Purchase/
│   │   ├── Inventory/
│   │   ├── Cash/
│   │   ├── Supplier/
│   │   ├── Warranty/
│   │   ├── Finance/
│   │   ├── Policy/
│   │   ├── User/
│   │   ├── Notification/
│   │   ├── Dashboard/
│   │   ├── Attachment/
│   │   └── Audit/
│   │
│   ├── Application/                      # Application Layer (Use Cases)
│   │   ├── Customer/
│   │   │   ├── DTOs/
│   │   │   │   ├── CreateCustomerDTO.php
│   │   │   │   └── CustomerData.php
│   │   │   ├── Actions/
│   │   │   │   ├── CreateCustomerAction.php
│   │   │   │   ├── UpdateCustomerAction.php
│   │   │   │   └── RegisterDeviceAction.php
│   │   │   └── Jobs/
│   │   │
│   │   ├── Request/
│   │   │   ├── DTOs/
│   │   │   ├── Actions/
│   │   │   │   ├── CreateRequestAction.php
│   │   │   │   ├── AssignRequestAction.php
│   │   │   │   ├── SchedulePickupAction.php
│   │   │   │   ├── ForkToServiceOrderAction.php
│   │   │   │   ├── ForkToSalesOrderAction.php
│   │   │   │   └── CancelRequestAction.php
│   │   │   └── Jobs/
│   │   │
│   │   ├── Service/
│   │   │   ├── DTOs/
│   │   │   ├── Actions/
│   │   │   │   ├── CreateServiceOrderAction.php
│   │   │   │   ├── AssignTechnicianAction.php
│   │   │   │   ├── TransitionServiceStatusAction.php
│   │   │   │   ├── UseSparepartAction.php
│   │   │   │   ├── CompleteServiceAction.php
│   │   │   │   └── VoidServiceAction.php
│   │   │   └── Jobs/
│   │   │
│   │   ├── Sales/
│   │   ├── Purchase/
│   │   ├── Inventory/
│   │   ├── Cash/
│   │   ├── Warranty/
│   │   ├── Finance/
│   │   ├── Policy/
│   │   ├── Notification/
│   │   └── Report/
│   │
│   ├── Infrastructure/                   # Infrastructure Layer
│   │   ├── Persistence/
│   │   │   └── Eloquent/
│   │   │       ├── CustomerRepository.php
│   │   │       ├── RequestRepository.php
│   │   │       ├── ServiceOrderRepository.php
│   │   │       └── ...
│   │   ├── Providers/
│   │   │   ├── Storage/
│   │   │   │   ├── LocalStorageProvider.php
│   │   │   │   ├── S3StorageProvider.php
│   │   │   │   └── GoogleDriveProvider.php
│   │   │   ├── Messaging/
│   │   │   │   ├── WhatsAppWebProvider.php
│   │   │   │   └── WhatsAppCloudApiProvider.php
│   │   │   ├── Payment/
│   │   │   │   ├── CashPaymentProvider.php
│   │   │   │   └── MidtransProvider.php
│   │   │   ├── AI/
│   │   │   │   ├── OpenAIProvider.php
│   │   │   │   └── DeepSeekProvider.php
│   │   │   └── Notification/
│   │   │       ├── EmailNotificationProvider.php
│   │   │       └── BrowserNotificationProvider.php
│   │   ├── Events/
│   │   │   └── Listeners/
│   │   │       ├── CreateAuditLogListener.php
│   │   │       ├── SendNotificationListener.php
│   │   │       ├── UpdateDashboardListener.php
│   │   │       └── RecordHistoryListener.php
│   │   └── Providers/
│   │       ├── ProviderFactory.php
│   │       └── ProviderRegistry.php
│   │
│   ├── Http/                             # Presentation Layer
│   │   ├── Controllers/
│   │   │   ├── Tenant/                   # Tenant routes
│   │   │   │   ├── RequestController.php
│   │   │   │   ├── ServiceController.php
│   │   │   │   └── ...
│   │   │   ├── Admin/                    # Platform admin routes
│   │   │   │   ├── TenantController.php
│   │   │   │   └── PlanController.php
│   │   │   ├── Auth/
│   │   │   └── Api/
│   │   ├── Requests/                     # FormRequest
│   │   │   ├── Tenant/
│   │   │   │   ├── StoreRequestRequest.php
│   │   │   │   └── UpdateServiceRequest.php
│   │   │   └── Admin/
│   │   ├── Resources/                    # API Resources
│   │   │   ├── RequestResource.php
│   │   │   ├── ServiceResource.php
│   │   │   └── ...
│   │   └── Middleware/
│   │       ├── TenantScope.php
│   │       ├── CheckPlanFeature.php
│   │       └── RequirePermission.php
│   │
│   └── Support/                          # Helpers & Base Classes
│       ├── BaseAction.php
│       ├── BaseRepository.php
│       ├── Traits/
│       │   ├── HasTenantScope.php
│       │   └── DispatchDomainEvents.php
│       └── Helpers/
│
├── config/
├── database/
│   └── migrations/                       # Migration (Sprint 6.4)
├── routes/
│   ├── web.php
│   ├── tenant.php
│   ├── admin.php
│   └── api.php
└── tests/
    ├── Unit/
    │   └── Domain/
    ├── Feature/
    │   └── Application/
    └── Integration/
        └── BusinessReality/
```

---

## Part B — Module Architecture (03)

### Setiap Module (contoh: Request) wajib memiliki:

| Komponen | Lokasi | Peran |
|---|---|---|
| **Aggregate Root** | `Domain/Request/Request.php` | Model domain; encapsulation business rules |
| **Value Objects** | `Domain/Request/ValueObjects/` | RequestType, RequestSource, RequestChannel |
| **Repository Interface** | `Domain/Request/RequestRepositoryInterface.php` | Kontrak akses data |
| **Domain Events** | `Domain/Request/Events/` | RequestCreated, RequestCancelled, etc. |
| **Domain Exceptions** | `Domain/Request/Exceptions/` | RequestAlreadyForkedException, etc. |
| **DTOs** | `Application/Request/DTOs/` | CreateRequestDTO, RequestFilterDTO |
| **Actions** | `Application/Request/Actions/` | CreateRequestAction, ForkToServiceOrderAction |
| **Policies** | `Domain/Request/RequestPolicy.php` | Authorization: view, create, update, delete, cancel |
| **Listeners** | `Infrastructure/Events/Listeners/` | React to RequestCreated, etc. |
| **Eloquent Model** | `Infrastructure/Persistence/Eloquent/` | (jika terpisah dari Domain model) |
| **Repository Impl** | `Infrastructure/Persistence/Eloquent/RequestRepository.php` | Eloquent implementation |
| **FormRequest** | `Http/Requests/Tenant/StoreRequestRequest.php` | Validasi input |
| **Controller** | `Http/Controllers/Tenant/RequestController.php` | Thin orchestration |
| **Resource** | `Http/Resources/RequestResource.php` | API response transformation |

### Module yang ada (17):

`Tenant`, `Customer`, `Request`, `Service`, `Sales`, `Purchase`, `Inventory`, `Cash`, `Supplier`, `Warranty`, `Finance`, `Policy`, `User`, `Notification`, `Dashboard`, `Attachment`, `Audit`

### Aturan:
1. Setiap module memiliki pola yang **sama** — developer bisa pindah antar module tanpa belajar ulang.
2. Module baru = copy template module → sesuaikan.
3. Module tidak boleh bergantung langsung ke module lain — via interface/event.
4. `Shared\` = kode yang dipakai >1 module (Money, PhoneNumber, base classes).
