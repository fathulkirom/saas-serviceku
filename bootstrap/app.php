<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckPlanFeature;
use App\Http\Middleware\CheckSubscription;
use App\Http\Middleware\RedirectIfNotTenant;
use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\InitializeTenancyBySession;
use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\HandleCors;
use App\Http\Middleware\EnsureJsonForApi;
use App\Http\Middleware\RequirePermission;
use App\Http\Middleware\RequireSuperAdmin;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            // Rate Limiter untuk login (6x per menit)
            RateLimiter::for('login', function (Request $request) {
                return Limit::perMinute(6)->by($request->input('email') ?: $request->ip());
            });
            // Rate Limiter untuk registrasi (3x per menit)
            RateLimiter::for('register', function (Request $request) {
                return Limit::perMinute(3)->by($request->ip());
            });
            // Rate Limiter untuk OTP (2x per menit)
            RateLimiter::for('otp', function (Request $request) {
                return Limit::perMinute(2)->by($request->input('email') ?: $request->ip());
            });
            // Rate Limiter API umum (60x per menit)
            RateLimiter::for('api', function (Request $request) {
                return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
            });
        },
    )
    ->withSchedule(function (\Illuminate\Console\Scheduling\Schedule $schedule) {
        // Backup otomatis setiap jam 03:00 pagi (jika diaktifkan)
        $schedule->command('backup:run --force')
            ->dailyAt('03:00')
            ->when(function () {
                return \App\Models\SystemSetting::getValue('backup_auto_enabled', 'false') === 'true';
            })
            ->appendOutputTo(storage_path('logs/backup-schedule.log'));

        // Upload backup ke Google Drive (setelah backup lokal)
        $schedule->call(function () {
            if (\App\Models\SystemSetting::getValue('gdrive_enabled', 'false') === 'true') {
                $backupPath = \App\Models\SystemSetting::getValue('backup_path', '/mnt/hdd/Backup/ServiceKU');
                \App\Services\GoogleDriveService::uploadBackupFolder($backupPath);
            }
        })->dailyAt('04:00')
            ->appendOutputTo(storage_path('logs/gdrive-schedule.log'));

        // Cek subscription yang habis setiap jam
        $schedule->command('subscription:check')
            ->hourly()
            ->appendOutputTo(storage_path('logs/subscription-check.log'));

        // Cleanup tenant expired > 30 hari (setiap hari jam 02:00)
        $schedule->command('tenants:cleanup --days=30 --force')
            ->dailyAt('02:00')
            ->appendOutputTo(storage_path('logs/tenant-cleanup.log'));

        // Retry failed jobs setiap jam
        $schedule->command('queue:retry --all')
            ->hourly()
            ->appendOutputTo(storage_path('logs/queue-retry.log'));

        // Bersihkan cache setiap hari
        $schedule->command('cache:clear')
            ->daily()
            ->appendOutputTo(storage_path('logs/cache-clear.log'));

        // GC session expired setiap hari
        $schedule->command('session:gc')
            ->daily()
            ->appendOutputTo(storage_path('logs/session-gc.log'));
    })
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'check.subscription' => CheckSubscription::class,
            'check.plan.feature' => CheckPlanFeature::class,
            'tenant.auth' => RedirectIfNotTenant::class,
            'tenancy.session' => InitializeTenancyBySession::class,
            'admin.auth' => AdminAuthenticate::class,
            'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
            'cors' => HandleCors::class,
            'ensure.json' => EnsureJsonForApi::class,
            'permission' => RequirePermission::class,
            'super_admin' => RequireSuperAdmin::class,
        ]);

        $middleware->api(
            prepend: ['throttle:api'], // Laravel 12 tidak lagi menyertakan throttle di grup api
            append: [
                EnsureJsonForApi::class,
                HandleCors::class,
            ],
        );

        $middleware->web(append: [
            HandleInertiaRequests::class,
            SecurityHeaders::class,
        ]);

        // Trust proxies (Cloudflare, load balancer)
        $middleware->trustProxies(at: '*');

        // CSRF exception untuk webhook payment gateway
        $middleware->validateCsrfTokens(except: [
            'payment/webhook',
            'customers/ajax-store',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Sentry error tracking (non-local only)
        if (!app()->environment('local')) {
            $exceptions->report(function (\Throwable $e) {
                if (app()->bound('sentry')) {
                    app('sentry')->captureException($e);
                }
            });
        }

        $exceptions->render(function (AuthenticationException $e, Request $request) {
            // Pastikan request ke /api/* tanpa autentikasi selalu 401 JSON,
            // bukan redirect 302 ke /login (yang 404 di landing page).
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
        });

        // Sprint v4.0C: Render HTTP exceptions (403/404/409/422) as plain/JSON responses.
        // Prevents the Inertia error view from crashing (undefined $page) and masking
        // the real HTTP status code — especially in tests.
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            $status = $e->getStatusCode();
            $message = $e->getMessage() ?: (\Symfony\Component\HttpFoundation\Response::$statusTexts[$status] ?? 'Error');

            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json(['message' => $message, 'status' => $status], $status);
            }

            if (app()->environment('testing')) {
                return response($status . ' ' . $message, $status);
            }

            return response()->view('errors.' . $status, ['message' => $message], $status);
        });

        $exceptions->respond(function (\Symfony\Component\HttpFoundation\Response $response, \Throwable $exception, Request $request) {
            $status = $response->getStatusCode();

            // JSON/API requests → always return JSON error (no Inertia view)
            if ($request->is('api/*') || $request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage() ?: 'Error ' . $status,
                    'status' => $status,
                ], $status);
            }

            // In testing, don't render Inertia error view (needs $page shared prop not present in tests).
            if (app()->environment('testing')) {
                return response($status . ' ' . \Symfony\Component\HttpFoundation\Response::$statusTexts[$status] ?? '', $status);
            }

            if (!app()->environment('local') && in_array($status, [500, 503, 404, 403, 419])) {
                return inertia('Errors/' . $status, ['status' => $status])
                    ->toResponse($request)
                    ->setStatusCode($status);
            }
            return $response;
        });
    })->create();
