# 16 — Error Handling · 17 — Testing · 18 — Coding Standard

> **Sprint 6.3 · Engineering Blueprint Only.**

---

## Part A — Error Handling (16)

### Exception Hierarchy

```
Exception
├── DomainException (base — semua domain exception)
│   ├── BusinessRuleException        # Aturan bisnis dilanggar
│   │   ├── InvariantViolationException
│   │   ├── CannotTransitionStatusException
│   │   ├── InsufficientStockException
│   │   └── WarrantyExpiredException
│   └── EntityNotFoundException      # Entity tidak ditemukan
│       ├── CustomerNotFoundException
│       ├── DeviceNotFoundException
│       └── RequestNotFoundException
│
├── ProviderException                # Provider eksternal gagal
│   ├── StorageProviderException
│   ├── MessagingProviderException
│   ├── PaymentGatewayException
│   └── AIProviderException
│
├── ValidationException              # Validasi input (Laravel)
│
└── SystemException                  # Error tak terduga → Sentry
```

### Exception Handling (Handler)
```php
// App\Exceptions\Handler
public function register(): void
{
    // Domain exceptions → 422 / 404
    $this->renderable(fn(BusinessRuleException $e) =>
        response()->json(['error' => $e->getMessage()], 422));

    $this->renderable(fn(EntityNotFoundException $e) =>
        response()->json(['error' => $e->getMessage()], 404));

    // Provider exceptions → fallback + log
    $this->reportable(fn(ProviderException $e) => /* notify + fallback */);

    // System exceptions → Sentry
    $this->reportable(fn(Throwable $e) =>
        Sentry::captureException($e));
}
```

### Aturan
1. **BusinessRuleException** → pesan user-friendly (Bahasa Indonesia).
2. **EntityNotFoundException** → 404.
3. **ProviderException** → fallback (lihat Sprint 6.2B §16 OfflineStrategy) + notifikasi Owner.
4. **SystemException** → Sentry + generic error message.

---

## Part B — Testing Strategy (17)

### Jenis Test

| Jenis | Scope | Lokasi | Contoh |
|---|---|---|---|
| **Unit Test** | Domain logic | `tests/Unit/Domain/` | `RequestTest::testCannotCancelTerminalRequest()` |
| **Feature Test** | Application use case | `tests/Feature/Application/` | `CreateRequestActionTest` |
| **Integration Test** | End-to-end flow | `tests/Integration/` | Request→Service→Warranty full flow |
| **Business Reality Test** | BR-001..020 | `tests/Integration/BusinessReality/` | `BR019_MultiDeviceVisitTest` |

### Unit Test Contoh
```php
class RequestTest extends TestCase
{
    public function test_cannot_cancel_terminal_request(): void
    {
        $request = Request::create(/* ... */);
        $request->complete();
        $request->close();

        $this->expectException(CannotTransitionStatusException::class);
        $request->cancel('test');
    }
}
```

### BR Test Contoh
```php
class BR019_MultiDeviceVisitTest extends TestCase
{
    /** @test */
    public function one_request_can_have_multiple_devices_and_service_orders(): void
    {
        $customer = Customer::create(...);
        $device1 = Device::register($customer, ...);
        $device2 = Device::register($customer, ...);

        $request = CreateRequestAction::execute(
            new CreateRequestDTO($customer->id, [$device1->id, $device2->id])
        );

        $this->assertCount(2, $request->devices);
        // Fork to 2 service orders
        $so1 = ForkToServiceOrderAction::execute($request, $device1);
        $so2 = ForkToServiceOrderAction::execute($request, $device2);

        $this->assertEquals($request->id, $so1->request_id);
        $this->assertEquals($request->id, $so2->request_id);
    }
}
```

### Aturan Testing
1. **Business Reality = acceptance test** — 20 BR = 20 integration test minimum.
2. **Invariant = unit test** — 23 invariant = 23 unit test minimum.
3. **Status flow = state machine test** — setiap transisi diuji.
4. **Provider = mock interface** — test fallback & error handling.
5. **Pest PHP** (opsional) atau PHPUnit.

---

## Part C — Coding Standard (18)

### PSR Compliance
- **PSR-12** — coding style
- **PSR-4** — autoloading (namespace = folder)
- **PSR-7** — HTTP message (opsional)

### Laravel Convention
| Aturan | Detail |
|---|---|
| **Controller** | PascalCase, suffix `Controller` |
| **Action** | PascalCase, suffix `Action`, verb+noun |
| **Repository** | PascalCase, suffix `Repository` |
| **Interface** | PascalCase, suffix `Interface` |
| **Policy** | PascalCase, suffix `Policy` |
| **Event** | PascalCase, past tense |
| **Listener** | PascalCase, suffix `Listener` |
| **DTO** | PascalCase, suffix `DTO` or `Data` |
| **FormRequest** | PascalCase, `Store<Model>Request`, `Update<Model>Request` |
| **Resource** | PascalCase, suffix `Resource` |
| **Trait** | PascalCase, prefix `Has` or `Can` |

### Dependency Rule (Clean Architecture)
```
Presentation ──depends on──> Application ──depends on──> Domain
Infrastructure ──implements──> Domain Interfaces
```

- Domain TIDAK import apa pun dari Application/Infrastructure/Http.
- Application hanya import dari Domain.
- Infrastructure import dari Domain (implement interface).
- Http import dari Application + Domain.

### Aturan Umum
1. **Max 250 baris per file** (Sprint 3).
2. **Max 50 baris per controller method**.
3. **1 class = 1 file**.
4. **No `new` di constructor** — inject via DI.
5. **No static methods** (kecuali factory method di Aggregate Root).
6. **No Eloquent di Controller** — selalu via Repository.
7. **PHP 8.2+** — gunakan readonly properties, enums, named arguments, union types.
