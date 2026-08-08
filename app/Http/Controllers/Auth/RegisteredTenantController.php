<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Tenant;
use App\Models\RegistrationVerification;
use App\Mail\WelcomeMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegisteredTenantController extends Controller
{
    public function create()
    {
        // Ambil semua paket aktif untuk dipilih di registrasi
        $plans = Plan::where('is_active', true)->get()->map(function ($plan) {
            return [
                'id' => $plan->id,
                'name' => $plan->name,
                'slug' => $plan->slug,
                'description' => $plan->description,
                'price' => (float) $plan->price,
                'promo_price' => $plan->promo_price ? (float) $plan->promo_price : null,
                'is_promo_active' => $plan->isPromoActive(),
                'discount_percent' => $plan->discountPercent(),
                'effective_price' => $plan->effectivePrice(),
                'trial_days' => $plan->trial_days,
                'features' => $plan->features ?? [],
                'business_types' => $plan->business_types ?? [],
            ];
        });

        $businessTypes = Plan::getAvailableBusinessTypes();

        return inertia('Auth/Register', [
            'plans' => $plans,
            'businessTypes' => $businessTypes,
        ]);
    }

    // Step 1: Submit form & kirim OTP
    public function sendOtp(Request $request)
    {
        $validated = $request->validate([
            'tenant_name' => 'required|string|max:255|unique:tenants,tenant_name',
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tenants,email',
            'phone' => 'nullable|string|max:20|unique:tenants,phone',
            'password' => 'required|string|min:8|confirmed',
            'business_type' => 'required|string|in:full_service,aksesoris_service,aksespare_service,gadget_full,retail_only',
            'plan_id' => 'nullable|exists:plans,id',
            'voucher_id' => 'nullable|exists:vouchers,id',
            'voucher_code' => 'nullable|string|max:50',
        ]);

        // Cek juga duplikat email/phone di data JSON tenant lama
        $email = $validated['email'];
        $phone = $validated['phone'] ?? '';

        $duplicateEmail = \App\Models\Tenant::where('data->email', $email)->exists();
        if ($duplicateEmail) {
            return back()->withErrors(['email' => 'Email sudah terdaftar.'])->onlyInput('email');
        }

        if ($phone) {
            $duplicatePhone = \App\Models\Tenant::where('data->phone', $phone)->exists();
            if ($duplicatePhone) {
                return back()->withErrors(['phone' => 'No. telepon sudah terdaftar.'])->onlyInput('phone');
            }
        }

        // Ambil plan yang dipilih (default: trial)
        $plan = $validated['plan_id'] ? Plan::find($validated['plan_id']) : Plan::where('slug', 'trial')->first();

        // Validasi tipe bisnis didukung oleh plan yang dipilih
        if ($plan && !$plan->supportsBusinessType($validated['business_type'])) {
            return back()->withErrors(['business_type' => 'Tipe bisnis tidak didukung oleh paket ini.'])->onlyInput('business_type');
        }

        // Simpan data registrasi & generate OTP
        $verification = RegistrationVerification::generateOtp($validated['email'], [
            'tenant_name' => $validated['tenant_name'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => $validated['password'],
            'business_type' => $validated['business_type'],
            'plan_id' => $plan?->id,
            'plan_name' => $plan?->name,
            'voucher_id' => $validated['voucher_id'] ?? null,
            'voucher_code' => $validated['voucher_code'] ?? null,
        ]);

        // Kirim OTP via canonical transactional mail abstraction (PILOT-MAIL-04R).
        // Resend when configured; safe env fallback otherwise. On failure the
        // registration stays pending — NO tenant/DB/domain is provisioned.
        $otpSent = \App\Services\TransactionalMailService::sendOtp(
            $validated['email'],
            $verification->otp,
            $validated['tenant_name']
        );
        if (!$otpSent) {
            Log::error('Gagal kirim OTP ke ' . $validated['email'] . ' — transactional mail tidak tersedia.');
            return back()->withErrors(['email' => 'Gagal mengirim kode verifikasi. Periksa konfigurasi email platform.']);
        }

        return redirect()->route('register.verify', ['email' => $validated['email']])
            ->with('success', 'Kode OTP telah dikirim ke email Anda.');
    }

    // Step 2: Form verifikasi OTP
    public function showVerifyForm(Request $request)
    {
        $email = $request->get('email');
        if (!$email) {
            return redirect()->route('register');
        }

        return inertia('Auth/VerifyOtp', ['email' => $email]);
    }

    // Step 3: Verifikasi OTP & buat akun
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'otp' => 'required|string|size:6',
        ]);

        $verification = RegistrationVerification::verifyOtp($request->email, $request->otp);

        if (!$verification) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid atau sudah kedaluwarsa.']);
        }

        $data = $verification->data;

        // Ambil plan dari data registrasi (default trial)
        $planId = $data['plan_id'] ?? null;
        $plan = $planId ? Plan::find($planId) : Plan::where('slug', 'trial')->first();
        $trialDays = $plan ? $plan->trial_days : 14;

        // Tentukan status subscription berdasarkan plan
        $isTrial = $plan && $plan->slug === 'trial';
        $subscriptionStatus = $isTrial ? 'trial' : 'active';

        // Proses voucher jika ada
        $voucherId = null;
        $voucherDiscount = null;
        $voucherExtraMonths = null;
        $voucherCode = isset($data['voucher_code']) ? strtoupper(trim($data['voucher_code'])) : null;
        if ($voucherCode) {
            $voucher = \App\Models\Voucher::where('code', $voucherCode)->first();
            if ($voucher && $voucher->isValid() && $voucher->canApply('new') && !$voucher->tenant_id) {
                $planPrice = (float) ($plan?->promo_price ?: $plan?->price ?? 0);
                $voucherDiscount = $voucher->calculateDiscount($planPrice);
                $voucherExtraMonths = $voucher->extra_months;
                $voucherId = $voucher->id;
                // markUsed() moved inside try — don't consume the voucher
                // if tenant creation fails (REGISTRATION-ROLLBACK-FIX).
            }
        }

        // REGISTRATION-ROLLBACK-FIX: Tenant::create() fires TenantCreated →
        // CreateDatabase (sync). If the DB already exists from a previous failed
        // attempt, MySQL throws 1007 "database exists". Move creation inside the
        // try block so rollback cleans up the stale DB + tenant record on retry.
        $tenantId = 'tenant_' . Str::random(10);

        // Inisialisasi tenant & database (PLATFORM-SYNC-01 STEP 8)
        try {
            $tenant = Tenant::create([
                'id' => $tenantId,
                'tenant_name' => $data['tenant_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? '',
                'plan_id' => $plan?->id,
                'voucher_id' => $voucherId,
                'voucher_discount' => $voucherDiscount,
                'extra_months' => $voucherExtraMonths,
                'subscription_status' => $subscriptionStatus,
                'trial_ends_at' => $isTrial ? now()->addDays($trialDays) : null,
                'subscribed_at' => $isTrial ? null : now(),
                'is_active' => true,
                'data' => [
                    'business_type' => $data['business_type'] ?? 'full_service',
                    'registered_plan' => $plan?->name ?? 'Trial',
                    'voucher_code' => $voucherCode,
                ],
            ]);

            // Mark voucher consumed only after tenant creation succeeds.
            if ($voucherId && isset($voucher)) {
                $voucher->markUsed();
            }

            tenancy()->initialize($tenant);

            app('migrator')->run(database_path('migrations/tenant'));

            // Buat branch & user
            $branchId = DB::table('branches')->insertGetId([
                'name' => 'Cabang Utama',
                'address' => '',
                'phone' => $data['phone'] ?? '',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('users')->insert([
                'branch_id' => $branchId,
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'owner',
                'active' => true,
                'remember_token' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('tenant_settings')->insert([
                ['key' => 'store_name', 'value' => $data['tenant_name']],
                ['key' => 'primary_color', 'value' => '#4F46E5'],
            ]);

            // Set slug tenant
            $slug = Str::slug($data['tenant_name']);

            // Cegah slug yang di-reserved (admin, kirom, www, dll)
            $reservedSlugs = \App\Models\Tenant::reservedSlugs();
            if (in_array($slug, $reservedSlugs)) {
                $slug = $slug . '-' . Str::random(4);
            }

            // Cegah duplicate slug (misal nama toko sama persis)
            while (\App\Models\Tenant::where('slug', $slug)->exists()) {
                $slug = $slug . '-' . Str::lower(Str::random(4));
            }

            $tenant->update(['slug' => $slug]);

            // Auto-login sebagai owner (dalam tenant context)
            $user = \App\Models\Tenant\User::where('email', $data['email'])->first();
            if ($user) {
                Auth::guard('web')->login($user);
                $request->session()->put('tenant_id', $tenant->id);
            }

            $configuredBaseDomain = env('CENTRAL_DOMAIN');
            $requestHost = request()->getHost();
            $requestHostNoPort = preg_replace('/:\\d+$/', '', $requestHost);
            $baseDomain = $configuredBaseDomain ?: $requestHostNoPort;

            // Buat domain untuk subdomain tenant: {slug}.basedomain
            $domain = $slug . '.' . $baseDomain;
            \Stancl\Tenancy\Database\Models\Domain::create([
                'tenant_id' => $tenant->id,
                'domain' => $domain,
            ]);
        } catch (\Exception $e) {
            Log::error('Registrasi gagal, rollback tenant ' . $tenantId . ': ' . $e->getMessage());

            // Ensure tenancy is ended regardless of where the failure occurred.
            try { tenancy()->end(); } catch (\Exception $ignored) {}

            // Look up the tenant — may be already in DB even if create()
            // threw (the INSERT happens before the CreateDatabase job).
            $failedTenant = isset($tenant) ? $tenant : Tenant::find($tenantId);
            if ($failedTenant) {
                try {
                    $failedTenant->domains()->delete();
                    $failedTenant->database()->manager()->deleteDatabase($failedTenant);
                } catch (\Exception $cleanupErr) {
                    Log::warning('Gagal bersihkan tenant DB/domain saat rollback: ' . $cleanupErr->getMessage());
                }
                try {
                    $failedTenant->delete();
                } catch (\Exception $deleteErr) {
                    Log::warning('Gagal hapus tenant record saat rollback: ' . $deleteErr->getMessage());
                }
            }

            return back()->withErrors([
                'email' => 'Registrasi gagal diinisialisasi. Silakan coba lagi atau hubungi dukungan.',
            ])->onlyInput('email');
        }

        // Kembali ke central database
        tenancy()->end();

        // Tentukan URL login tenant (subdomain)
        $scheme = request()->getScheme();
        $requestPort = request()->getPort();
        $tenantHost = $slug . '.' . $baseDomain;
        $isLocalDomain = str_contains($baseDomain, 'localhost') || filter_var($baseDomain, FILTER_VALIDATE_IP);
        $portSuffix = ($isLocalDomain && !in_array($requestPort, [80, 443], true)) ? ':' . $requestPort : '';
        $tenantLoginUrl = $scheme . '://' . $tenantHost . $portSuffix . '/login';

        // Kirim email selamat datang — canonical provider first, legacy SMTP fallback.
        try {
            \App\Services\TransactionalMailService::sendWelcome(
                $data['email'], $tenant, $data['password'], $tenantLoginUrl
            );
        } catch (\Exception $e) {
            Log::warning('Gagal kirim email welcome: ' . $e->getMessage());
        }

        // Redirect ke subdomain tenant
        return redirect()->away($tenantLoginUrl);
    }

    // Step 4: Halaman sukses registrasi
    public function success(Request $request)
    {
        return inertia('Auth/RegistrationSuccess', [
            'email' => $request->get('email'),
            'domain' => $request->get('domain'),
            'emailSent' => $request->get('email_sent') === '1',
            'plan' => $request->get('plan', 'Trial'),
        ]);
    }

    // Kirim ulang OTP
    public function resendOtp(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $existing = RegistrationVerification::where('email', $request->email)
            ->whereNull('verified_at')
            ->first();

        if (!$existing) {
            return back()->withErrors(['email' => 'Data registrasi tidak ditemukan. Silakan daftar ulang.']);
        }

        $newOtp = str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $existing->update([
            'otp' => $newOtp,
            'expires_at' => now()->addMinutes(15),
        ]);

        try {
            \App\Services\TransactionalMailService::sendOtp(
                $request->email,
                $newOtp,
                $existing->data['tenant_name'] ?? 'ServiceKU'
            );
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Gagal mengirim ulang kode.']);
        }

        return back()->with('success', 'Kode OTP baru telah dikirim.');
    }
}
