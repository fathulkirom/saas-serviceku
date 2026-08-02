# 11 — Policy Architecture · 12 — Authentication · 13 — Authorization · 14 — Multi-Tenant · 15 — Subdomain

> **Sprint 6.3 · Engineering Blueprint Only.**

---

## Part A — Policy Architecture (11)

### Struktur Policy
```php
// Domain/Request/RequestPolicy.php
class RequestPolicy
{
    // Permission checks — TIDAK BOLEH hardcode role name
    public function view(User $user, Request $request): bool
    {
        return $user->can('request.view')
            || $user->id === $request->creatorId;
    }

    public function create(User $user): bool
    {
        return $user->can('request.create');
    }

    public function assign(User $user, Request $request): bool
    {
        return $user->can('request.assign');
    }

    public function cancel(User $user, Request $request): bool
    {
        return $user->can('request.cancel')
            && !$request->isTerminal();
    }

    public function override(User $user): bool
    {
        // BR-011 — delegation via permission
        return $user->can('request.override');
    }
}
```

### Registrasi Policy
```php
// AuthServiceProvider
protected $policies = [
    Request::class      => RequestPolicy::class,
    ServiceOrder::class => ServiceOrderPolicy::class,
    SalesOrder::class   => SalesOrderPolicy::class,
    // ... all aggregate roots
];
```

### Permission Keys (dari Sprint 5.1 + tambahan)
| Module | Permission |
|---|---|
| `request.*` | `request.create`, `request.view`, `request.assign`, `request.cancel`, `request.override` |
| `service.*` | `service.create`, `service.view`, `service.work`, `service.void`, `service.delete` |
| `sales.*` | `sales.create`, `sales.view`, `sales.void`, `sales.refund` |
| `customer.*` | `customer.create`, `customer.view`, `customer.update`, `customer.delete` |
| `inventory.*` | `inventory.view`, `inventory.adjust`, `inventory.transfer` |
| `finance.*` | `finance.view`, `finance.manage` |

---

## Part B — Authentication Architecture (12)

### Flow Login Tenant
```
User buka https://tokosaya.serviceku.my.id/login
  → Subdomain middleware resolve tenant
    → GET /login → LoginController@show
      → User input email + password
        → Auth::attempt()
          → Session dibuat (tenant-scoped)
          → Redirect ke Dashboard

User buka https://admin.serviceku.my.id/login
  → Central domain → Super Admin auth (terpisah)
```

### Metode Auth
| Metode | Package | Untuk |
|---|---|---|
| **Session (Web)** | Laravel Session | Tenant user (CS, Teknisi, Kasir, dll.) |
| **Sanctum (SPA)** | Laravel Sanctum | Inertia.js SPA (existing) |
| **OTP** | Custom (existing) | Reset password, verifikasi |
| **2FA** | google2fa (existing) | Keamanan tambahan |
| **Socialite** | Laravel Socialite (existing) | Google/Facebook login (future) |
| **Remember Me** | Laravel built-in | Remember login |

---

## Part C — Authorization Architecture (13)

### Gate Checks
```php
// Di Controller atau Blade:
Gate::authorize('create', Request::class);        // Policy auto-discovery
Gate::authorize('assign', $request);
Gate::authorize('cancel', $request);
```

### Di FormRequest
```php
class StoreRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('request.create');
    }
}
```

### Aturan Authorization
1. **Tidak boleh** `if ($user->role === 'owner')` — selalu pakai `can('permission')`.
2. **Policy method** = 1 aksi. Nama method sesuai aksi (view, create, update, delete, assign, cancel, void).
3. **Delegation** (BR-011) = cek temporary grant di Policy.
4. **Plan feature check** = middleware `CheckPlanFeature` (existing) — cek apakah modul diizinkan plan.

---

## Part D — Multi-Tenant Architecture (14)

### Tenant Resolution
```
https://{tenant}.serviceku.my.id
  → Subdomain middleware → resolve tenant dari subdomain
    → stancl/tenancy::initialize(tenant)
      → Set database connection (tenant DB)
      → Set cache prefix
      → Set filesystem root
```

### Middleware Stack
```php
// Tenant routes (tenant.php)
Route::middleware([
    'web',
    'auth',
    'tenant',               // Initialize stancl/tenancy
    'check.subscription',   // Cek status trial/active/expired
    'check.plan.feature',   // Cek modul diizinkan plan
])->group(function () {
    // All tenant routes
});
```

### Scope Otomatis
```php
// Trait HasTenantScope — applied to ALL tenant models
trait HasTenantScope
{
    protected static function bootHasTenantScope(): void
    {
        static::addGlobalScope('tenant', function ($query) {
            if (tenancy()->initialized) {
                $query->where('tenant_id', tenant()->id);
            }
        });

        static::creating(function ($model) {
            if (tenancy()->initialized && !$model->tenant_id) {
                $model->tenant_id = tenant()->id;
            }
        });
    }
}
```

---

## Part E — Subdomain Architecture (15)

### Domain List
| Domain | Fungsi | DB |
|---|---|---|
| `serviceku.my.id` | Landing page | Central |
| `admin.serviceku.my.id` | Super Admin Panel | Central |
| `api.serviceku.my.id` | Public API (future) | Central → resolve tenant |
| `*.serviceku.my.id` | Tenant app | Tenant DB |

### Subdomain Middleware
```php
// Resolve tenant dari subdomain
$subdomain = explode('.', request()->getHost())[0];
$tenant = Tenant::where('subdomain', $subdomain)->firstOrFail();
tenancy()->initialize($tenant);
```
