# ROOT CAUSE ANALYSIS — ServiceKU Memory Exhaustion

**Date**: 2026-08-02  
**Server**: 192.168.1.33 (kirom)  
**Analyzer**: GitHub Copilot (DeepSeek V4 Pro)  
**Scope**: Analysis Only — No changes, no fixes, no restarts

---

## EXECUTIVE SUMMARY

Laravel bootstrap menghabiskan memory (256MB → 2GB) karena **infinite recursion** yang dipicu oleh **wildcard event listener** `Event::listen('*', ...)` yang mencatat SEMUA event ke tabel `event_logs` via `EventLog::create()`. Setiap kali `EventLog` dibuat, Laravel memicu Eloquent event (`eloquent.creating`, `eloquent.created`), yang ditangkap oleh wildcard listener yang sama, yang kembali membuat `EventLog` baru → **loop tak terbatas** → memory exhaustion.

---

## ROOT CAUSE

### Primary: Wildcard Event Listener + EventLog::create() = Infinite Recursion

**Lokasi**: `app/Providers/AppServiceProvider.php` baris 56

```php
Event::listen('*', [EventLogger::class, 'handle']);
```

**Lokasi**: `app/Listeners/EventLogger.php` baris 26

```php
\App\Models\Tenant\EventLog::create([
    'entity_type'    => $entity ? get_class($entity) : null,
    'entity_id'      => $entity ? $entity->getKey() : null,
    'event_key'      => class_basename($event),
    'event_class'    => get_class($event),
    // ... more fields
]);
```

### Recursion Chain

```
┌─────────────────────────────────────────────────────────┐
│                   INFINITE RECURSION                     │
│                                                         │
│  AppServiceProvider::boot()                             │
│    → Event::listen('*', [EventLogger::class])           │
│                                                         │
│  ANY Laravel Event fires (bootstrap, model, etc.)       │
│    ↓                                                    │
│  EventLogger::handle($eventName, $payload)              │
│    → extractEntity() → ReflectionClass analysis         │
│    → EventLog::create([...])        ← ◄═══════════╗    │
│    ↓                                               ║    │
│  Eloquent fires: eloquent.creating: EventLog       ║    │
│    ↓                                               ║    │
│  Wildcard Listener (*) catches it                  ║    │
│    ↓                                               ║    │
│  EventLogger::handle('eloquent.creating', ...)     ║    │
│    → extractEntity() → ReflectionClass             ║    │
│    → EventLog::create([...])  ─────────────────════╝    │
│                                                         │
│  RESULT: Unbounded recursion, memory → ∞                │
└─────────────────────────────────────────────────────────┘
```

### Secondary Entry Point: EventBus::logEvent()

**Lokasi**: `app/Services/EventBus.php` baris 101

```php
private function logEvent(object $event): void
{
    EventLog::create([...]);  // ← Sama: triggers wildcard → loop
}
```

`EventBus::dispatch()` juga memanggil `EventLog::create()` — ini jalur alternatif ke loop yang sama. Setiap kali `WorkflowEngine` atau kode lain memanggil `EventBus::dispatch()`, loop yang sama terpicu.

### Tertiary Amplifier: WorkflowPersistenceSubscriber

**Lokasi**: `app/Subscribers/WorkflowPersistenceSubscriber.php`

```php
WorkflowHistory::create([...]);   // ← Model create → wildcard → EventLog::create() → loop
ActivityLog::log(...);            // ← Model create → wildcard → EventLog::create() → loop  
RequestHistory::create([...]);    // ← Model create → wildcard → EventLog::create() → loop
```

Setiap `Model::create()` memicu Eloquent event yang ditangkap wildcard listener.

---

## STACK TRACE ANALYSIS

### Fatal Error #1 — PHP 256MB limit

```
PHP Fatal error: Allowed memory size of 268435456 bytes exhausted
  (tried to allocate 20480 bytes)
  in /var/www/html/vendor/laravel/framework/src/Illuminate/
     Foundation/Bootstrap/HandleExceptions.php:76
```

Line 76 HandleExceptions.php → Laravel exception handler. Gagal saat mencoba menangani error karena memory sudah habis.

### Fatal Error #2 — PHP 2GB limit (masih gagal!)

```
PHP Fatal error: Allowed memory size of 2147483648 bytes exhausted
  (tried to allocate 20480 bytes)
  in /var/www/html/vendor/laravel/framework/src/Illuminate/
     Foundation/Bootstrap/HandleExceptions.php:76
```

**2GB pun habis!** Ini membuktikan bahwa masalah BUKAN limit memory — ini **unbounded recursion**. Tidak ada jumlah memory yang cukup karena recursion terus bertambah tanpa batas.

### Fatal Error #3 — Container.php

```
PHP Fatal error: Allowed memory size of 1610612736 bytes exhausted
  (tried to allocate 262144 bytes)
  in /var/www/html/vendor/laravel/framework/src/Illuminate/
     Container/Container.php:903
```

Line 903 Container.php → `resolve()` atau `build()` method. Gagal saat Laravel container mencoba me-resolve dependency di tengah recursion.

### Warning Pattern (symptom, not cause)

```
PHP Warning: Undefined array key "App\Models\Tenant\EventLog"
  in /var/www/html/vendor/laravel/framework/src/Illuminate/
     Database/Eloquent/Model.php:398
```

Line 398 Model.php → `initializeTraits()`:
```php
foreach (static::$traitInitializers[static::class] as $method) {
```

`EventLog::class` tidak ada dalam `$traitInitializers` karena model sedang dalam proses recursive creation dan belum selesai bootstrapping. Ini **symptom** dari recursion, bukan penyebab.

```
PHP Warning: foreach() argument must be of type array|object, null given
  in /var/www/html/vendor/laravel/framework/src/Illuminate/
     Database/Eloquent/Model.php:398
```

Akibat dari warning di atas — `$traitInitializers[EventLog::class]` adalah `null`.

---

## LAST LOADED CLASS

Berdasarkan stack trace dan warning pattern, class terakhir yang berhasil diload sebelum memory exhaustion:

| Order | Class | File |
|-------|-------|------|
| 1 | `App\Providers\AppServiceProvider` | `app/Providers/AppServiceProvider.php` |
| 2 | `App\Listeners\EventLogger` | `app/Listeners/EventLogger.php` |
| 3 | `App\Models\Tenant\EventLog` | `app/Models/Tenant/EventLog.php` |
| 4 | `Illuminate\Database\Eloquent\Model` | `vendor/laravel/framework/.../Model.php` |
| 5 | *(recursive loop — class #3-#4 berulang ribuan kali)* | |

---

## LAST PROVIDER

`App\Providers\AppServiceProvider` — provider terakhir yang sukses di-boot sebelum loop dimulai. Provider ini mendaftarkan wildcard listener di method `boot()`.

---

## CIRCULAR DEPENDENCY

```
EventLogger
    ↓ (calls create on)
EventLog (Eloquent Model)
    ↓ (fires Eloquent events → eloquent.creating, eloquent.created, etc.)
Wildcard Listener (*)
    ↓ (catches ALL events → routes to)
EventLogger
    ↓ (calls create on)
EventLog
    ↓ ... ∞
```

### Dependency Graph

```
                    ┌──────────────────┐
                    │ AppServiceProvider│
                    │  boot()           │
                    └────────┬─────────┘
                             │ registers
                             ▼
                    ┌──────────────────┐
                    │  Wildcard (*)     │
                    │  Event::listen()  │
                    └────────┬─────────┘
                             │ catches ALL events
                             ▼
                    ┌──────────────────┐
            ┌──────│  EventLogger      │◄──────────────┐
            │      │  handle()         │               │
            │      └────────┬─────────┘               │
            │               │                          │
            │               │ EventLog::create()       │
            │               ▼                          │
            │      ┌──────────────────┐               │
            │      │  EventLog Model   │               │
            │      │  (Eloquent)       │               │
            │      └────────┬─────────┘               │
            │               │ fires                    │
            │               │ eloquent.creating        │
            │               │ eloquent.created         │
            │               ▼                          │
            │      ┌──────────────────┐               │
            │      │  Wildcard (*)     │               │
            │      │  catches again    │───────────────┘
            │      └──────────────────┘
            │
            │      ┌──────────────────┐
            └──────│  EventBus         │  (alternate path)
                   │  logEvent()       │
                   └────────┬─────────┘
                            │ EventLog::create()
                            ▼
                     (same loop)
```

---

## RECURSIVE MODEL ANALYSIS

### EventLog Model (`app/Models/Tenant/EventLog.php`)

- **No `boot()` or `booted()` method** — clean, no self-referencing logic
- **No Global Scopes** — no scope registered
- **No HasEvents/HasObserver traits** — clean Eloquent model
- **No Observer registered** — confirmed, no `::observe()` calls
- **Relationships**: `actor()` → `belongsTo(User)`, `branch()` → `belongsTo(Branch)` — simple, no circular reference
- **Only `$fillable`, `$casts`, scopes** — standard model

> **Kesimpulan**: Model `EventLog` sendiri **TIDAK bermasalah**. Masalahnya ada di luar model — yaitu wildcard listener yang memanggil `EventLog::create()` setiap kali event apa pun terjadi.

### EventLogger (`app/Listeners/EventLogger.php`)

```php
class EventLogger
{
    public function handle(string $eventName, array $payload): void
    {
        // Guard: skip empty payloads
        if (empty($payload)) return;
        // Guard: skip non-object events
        if (!$event || !is_object($event)) return;

        try {
            // ↓ THIS IS THE PROBLEM
            \App\Models\Tenant\EventLog::create([...]);
        } catch (\Throwable $e) {
            Log::warning('EventLogger failed: ' . $e->getMessage());
        }
    }
}
```

**Mengapa try-catch tidak membantu:**
- `EventLog::create()` memicu Eloquent events **SECARA SYNCHRONOUS** 
- Wildcard listener menangkap event tersebut dan memanggil `EventLogger::handle()` **SEBELUM** `create()` selesai
- Setiap panggilan rekursif menambah stack frame baru
- `catch` block pada frame N tidak pernah dieksekusi karena `create()` pada frame N tidak pernah return (menunggu frame N+1 selesai, yang menunggu frame N+2, ... ∞)
- Memory habis sebelum stack bisa unwind

---

## OBSERVER CHAIN

Tidak ada Observer yang terdaftar untuk model mana pun dalam project ini. Problem bukan dari Observer pattern.

Namun wildcard listener `Event::listen('*', ...)` secara efektif **BERTINDAK SEPERTI OBSERVER GLOBAL** — menangkap semua event dari semua model.

---

## KESIMPULAN

### Penyebab Tunggal

**`Event::listen('*', [EventLogger::class, 'handle'])` di `AppServiceProvider::boot()`**

Wildcard listener ini menangkap **setiap** event Laravel, termasuk Eloquent model events yang dipicu oleh `EventLog::create()` di dalam listener itu sendiri. Ini menciptakan **infinite recursion** yang tidak bisa dihentikan.

### Mengapa Baru Terjadi Sekarang

Kemungkinan pemicu:
1. Baru deploy ke server → wildcard listener aktif di environment yang berbeda
2. Sebelumnya mungkin ada guard condition yang mencegah loop (misal: env check, feature flag) yang sudah dihapus
3. Mungkin sebelumnya `EventBus::logEvent()` yang digunakan dan memiliki guard, tapi sekarang wildcard listener ditambahkan sebagai "improvement"

### Dampak

| Impact | Detail |
|--------|--------|
| HTTP 500 | Semua request gagal — app tidak bisa melayani pengguna |
| Artisan gagal | `route:list`, `migrate:status`, `optimize`, `schedule:run` — semua gagal |
| Cron gagal | Scheduler harian tidak berjalan |
| Session hilang | Redis 0 keys karena tidak ada request yang berhasil |
| Log spam | 28,600+ WARNING entries di laravel.log (12.8 MB) |

### Solusi yang Diperlukan (tidak dilakukan dalam analisis ini)

1. **Hapus wildcard listener** atau batasi hanya untuk event non-Eloquent
2. **Guard re-entrancy** — tambahkan static flag di EventLogger: `if (self::$recording) return;`
3. **Gunakan `EventLog::withoutEvents()`** atau `Model::unsetEventDispatcher()` saat create di dalam listener
4. **Pisahkan event logging ke queue job** — dispatch job async, jangan create langsung

---

## VERIFICATION: Memory Test

| Memory Limit | Result |
|-------------|--------|
| 256 MB (default) | ❌ Exhausted |
| 1.5 GB | ❌ Exhausted |
| 2 GB (2,147,483,648 bytes) | ❌ **Masih exhausted!** |

> 2GB habis membuktikan ini **bukan** masalah batas memory — ini **unbounded recursion** yang tidak bisa diselesaikan dengan menambah memory.

---

