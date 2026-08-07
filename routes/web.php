<?php

use Illuminate\Support\Facades\Route;

// Health check (no auth, no tenant)
Route::get('/health', [App\Http\Controllers\HealthController::class, '__invoke']);
use App\Http\Controllers\Auth\RegisteredTenantController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Admin\SuperAdminController;
use App\Http\Controllers\Admin\TenantManagementController;
use App\Http\Controllers\Admin\PlanController;
use App\Http\Controllers\Admin\SystemSettingsController;
use App\Http\Controllers\Admin\MonitoringController;

// ========== PUBLIC ROUTES ==========
Route::get('/', function () {
    $host = request()->getHost();

    // Akses dari subdomain admin -> redirect ke panel admin
    if (str_starts_with($host, 'kirom.')) {
        $tenantDomain = \Stancl\Tenancy\Database\Models\Domain::where('domain', $host)->first();
        if (!$tenantDomain) {
            return redirect('/admin');
        }
    }

    // Cek apakah akses dari subdomain tenant
    $domain = \Stancl\Tenancy\Database\Models\Domain::where('domain', $host)->first();

    if ($domain) {
        $tenant = \App\Models\Tenant::find($domain->tenant_id);
        if ($tenant && $tenant->is_active) {
            tenancy()->initialize($tenant);
            session()->put('tenant_id', $tenant->id);

            // Jika sudah login, redirect ke dashboard
            if (auth()->check()) {
                return redirect()->route('dashboard');
            }

            // Jika belum login, redirect ke halaman login tenant
            return redirect()->route('login');
        }
    }

    $plans = \App\Models\Plan::where('is_active', true)->get()->map(function ($plan) {
        return [
            'name' => $plan->name,
            'price' => (float) $plan->price,
            'promo_price' => $plan->promo_price ? (float) $plan->promo_price : null,
            'is_promo_active' => $plan->isPromoActive(),
            'discount_percent' => $plan->discountPercent(),
            'effective_price' => $plan->effectivePrice(),
            'featured' => $plan->slug === 'pro',
            'features' => $plan->features,
            'trial_days' => $plan->trial_days,
            'slug' => $plan->slug,
        ];
    });

    // PLATFORM-SYNC-01 (STEP 2/6): the DB `plans` table is the single source
    // of truth. A hardcoded fallback array (previously here) contradicted the
    // seeder (e.g. trial_days 14 on paid plans, users none on Basic) and was a
    // hidden stale constant — removed. Landing shows only real plan data.

    return view('welcome', compact('plans'));
})->name('home');

Route::view('/privacy-policy', 'legal.privacy')->name('privacy');
Route::view('/terms', 'legal.terms')->name('terms');

// ========== DEV QUICK LOGIN (development only) ==========
Route::get('/dev-login', App\Http\Controllers\DevLoginController::class)->name('dev.login')->middleware('guest');

// ========== TENANT LOOKUP (Cari Toko) ==========
Route::post('/tenant/lookup', [App\Http\Controllers\TenantLookupController::class, '__invoke'])->name('tenant.lookup');

// ========== GOOGLE LOGIN (central - for landing page) ==========
Route::get('/auth/google/redirect', [App\Http\Controllers\Auth\GoogleLoginController::class, 'redirect'])->name('google.login.central');
Route::get('/auth/google/callback', [App\Http\Controllers\Auth\GoogleLoginController::class, 'callback'])->name('google.callback.central');

// ========== VOUCHER APPLY ==========
Route::post('/voucher/apply', [App\Http\Controllers\VoucherApplyController::class, 'apply'])->name('voucher.apply');

// ========== AUTH ROUTES (CENTRAL) ==========
Route::middleware('guest')->group(function () {
    // Registrasi dengan OTP (rate limited: 3x/menit)
    Route::get('register', [RegisteredTenantController::class, 'create'])->name('register');
    Route::post('register/send-otp', [RegisteredTenantController::class, 'sendOtp'])->name('register.otp.send')->middleware('throttle:register');
    Route::get('register/verify', [RegisteredTenantController::class, 'showVerifyForm'])->name('register.verify');
    Route::post('register/verify', [RegisteredTenantController::class, 'verifyOtp'])->name('register.verify.submit');
    Route::post('register/resend-otp', [RegisteredTenantController::class, 'resendOtp'])->name('register.otp.resend')->middleware('throttle:otp');
    Route::get('register/success', [RegisteredTenantController::class, 'success'])->name('register.success');

    // Login utama (rate limited: 6x/menit)
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.post')->middleware('throttle:login');

    // Halaman cari toko (central): /login di-shadow oleh route tenant
    // (PreventAccessFromCentralDomains -> 404 di central), jadi pakai path /masuk.
    Route::get('masuk', fn () => inertia('Auth/Login', ['app_env' => app()->environment()]))->name('login.find');
});

Route::post('logout', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');

// Admin login (central auth, not tenant)
Route::get('/admin/login', function () {
    return inertia('Auth/AdminLogin');
})->name('admin.login');
Route::post('/admin/login', [App\Http\Controllers\Admin\AdminAuthController::class, 'login'])->name('admin.login.post');

// ========== PUBLIC TRACKING (Tanpa Login) ==========
Route::get('/track/{tracking_code}', [App\Http\Controllers\PublicTrackingController::class, 'show'])->name('public.track')->middleware('throttle:10,1');

// ========== PAYMENT WEBHOOK (public, no auth) ==========
Route::post('/payment/webhook', [App\Http\Controllers\Admin\PaymentController::class, 'webhook'])->name('payment.webhook');

// ========== SUPERADMIN ROUTES ==========
Route::prefix('admin')->name('admin.')->middleware('admin.auth')->group(function () {
    Route::get('/', [SuperAdminController::class, 'dashboard'])->name('dashboard');

    // Tenant management
    Route::get('/tenants', [TenantManagementController::class, 'index'])->name('tenant.index');
    Route::get('/tenants/create', [TenantManagementController::class, 'createForm'])->name('tenant.create');
    Route::post('/tenants', [TenantManagementController::class, 'store'])->name('tenant.store');
    Route::get('/tenants/{tenant}', [TenantManagementController::class, 'show'])->name('tenant.show');
    Route::get('/tenants/{tenant}/edit', [TenantManagementController::class, 'edit'])->name('tenant.edit');
    Route::post('/tenants/{tenant}/update', [TenantManagementController::class, 'update'])->name('tenant.update');
    Route::post('/tenants/{tenant}/domain', [TenantManagementController::class, 'updateDomain'])->name('tenant.domain');
    Route::post('/tenants/{tenant}/sync-stats', [TenantManagementController::class, 'syncStats'])->name('sync-tenant-stats');
    Route::post('/tenants/sync-all-stats', [TenantManagementController::class, 'syncAllStats'])->name('sync-all-stats');

    // ═══════ SUPER ADMIN ONLY — sensitive platform actions ═══════
    Route::middleware('super_admin')->group(function () {
        Route::post('/tenants/{tenantId}/delete', [TenantManagementController::class, 'destroy'])->name('tenant.delete');
        Route::post('/tenants/{tenant}/suspend', [TenantManagementController::class, 'suspend'])->name('tenant.suspend');
        Route::post('/tenants/{tenant}/activate', [TenantManagementController::class, 'activate'])->name('tenant.activate');
        Route::post('/tenants/{tenant}/extend-trial', [TenantManagementController::class, 'extendTrial'])->name('tenant.extend-trial');
        Route::post('/tenants/{tenant}/extend-subscription', [TenantManagementController::class, 'extendSubscription'])->name('tenant.extend-subscription');
        Route::post('/tenants/{tenant}/change-plan', [TenantManagementController::class, 'changePlan'])->name('tenant.change-plan');
        Route::post('/tenants/{tenant}/login-as', [TenantManagementController::class, 'loginAs'])->name('tenant.login-as');
        Route::post('/tenants/{tenant}/reset-password', [TenantManagementController::class, 'resetPassword'])->name('tenant.reset-password');

        // Plans (create / update / delete)
        Route::post('/plans', [PlanController::class, 'store'])->name('plans.store');
        Route::post('/plans/{plan}', [PlanController::class, 'update'])->name('plans.update');
        Route::post('/plans/{plan}/default-menus', [PlanController::class, 'updateDefaultMenus'])->name('plans.default-menus');

        // Vouchers delete
        Route::post('/vouchers/{voucher}/delete', [App\Http\Controllers\Admin\VoucherController::class, 'destroy'])->name('vouchers.destroy');

        // Payment confirm / cancel
        Route::post('/payments/{payment}/confirm', [App\Http\Controllers\Admin\PaymentController::class, 'confirmPayment'])->name('payments.confirm');
        Route::post('/payments/{payment}/cancel', [App\Http\Controllers\Admin\PaymentController::class, 'cancelPayment'])->name('payments.cancel');
        Route::post('/payment-settings', [App\Http\Controllers\Admin\PaymentController::class, 'updateSettings'])->name('payment-settings.update');

        // Backup actions
        Route::post('/backup/run', [BackupController::class, 'runBackup'])->name('backup.run');
        Route::post('/backup/delete', [BackupController::class, 'deleteBackup'])->name('backup.delete');
        Route::post('/backup/settings', [BackupController::class, 'updateSettings'])->name('backup.settings');
        Route::post('/backup/upload-drive', [BackupController::class, 'uploadToDrive'])->name('backup.upload-drive');

        // System settings
        Route::post('/settings', [SystemSettingsController::class, 'update'])->name('settings.update');
        Route::post('/settings/feature-flags', [SystemSettingsController::class, 'updateFeatureFlags'])->name('settings.feature-flags');

        // Clear logs
        Route::post('/logs/clear', [SystemSettingsController::class, 'clearLogs'])->name('logs.clear');
    });

    // Plans (view — all admins)
    Route::get('/plans', [PlanController::class, 'index'])->name('plans');

    // Vouchers (view — all admins)
    Route::get('/vouchers', [App\Http\Controllers\Admin\VoucherController::class, 'index'])->name('vouchers.index');
    Route::get('/vouchers/create', [App\Http\Controllers\Admin\VoucherController::class, 'create'])->name('vouchers.create');
    Route::post('/vouchers', [App\Http\Controllers\Admin\VoucherController::class, 'store'])->name('vouchers.store');
    Route::get('/vouchers/{voucher}/edit', [App\Http\Controllers\Admin\VoucherController::class, 'edit'])->name('vouchers.edit');
    Route::post('/vouchers/{voucher}', [App\Http\Controllers\Admin\VoucherController::class, 'update'])->name('vouchers.update');
    Route::get('/vouchers/generate-code', [App\Http\Controllers\Admin\VoucherController::class, 'generateCode'])->name('vouchers.generate-code');

    // Payments (view — all admins)
    Route::get('/payments', [App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('payments');
    Route::post('/payments/invoice', [App\Http\Controllers\Admin\PaymentController::class, 'createInvoice'])->name('payments.invoice');
    Route::get('/payment-settings', [App\Http\Controllers\Admin\PaymentController::class, 'settings'])->name('payment-settings');

    // Backup (view — all admins)
    Route::get('/backup', [BackupController::class, 'index'])->name('backup');

    // Settings & monitoring (view — all admins)
    Route::get('/settings', [SystemSettingsController::class, 'index'])->name('settings');
    Route::post('/settings/test-mail', [SystemSettingsController::class, 'testMail'])->name('settings.test-mail');
    Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring');
    Route::get('/logs', [SystemSettingsController::class, 'logs'])->name('logs');
});
