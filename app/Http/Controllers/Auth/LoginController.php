<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Tenant\LoginHistory;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Stancl\Tenancy\Database\Models\Domain;

class LoginController extends Controller
{
    public function create()
    {
        $appEnv = app()->environment();

        // Jika sudah di subdomain tenant (tenancy aktif), tampilkan form email/password
        if (tenancy()->initialized) {
            return inertia('Auth/SubdomainLogin', [
                'tenantName' => tenancy()->tenant->tenant_name,
                'app_env' => $appEnv,
            ]);
        }

        // Cek apakah host termasuk tenant subdomain (central route, tenancy belum aktif)
        $host = request()->getHost();
        $domain = Domain::where('domain', $host)->first();
        if ($domain) {
            $tenant = Tenant::find($domain->tenant_id);
            if ($tenant) {
                tenancy()->initialize($tenant);
                session()->put('tenant_id', $tenant->id);
                return inertia('Auth/SubdomainLogin', [
                    'tenantName' => $tenant->tenant_name,
                    'app_env' => $appEnv,
                ]);
            }
        }

        // Jika ada tenant_id di session, redirect ke subdomain tenant
        if ($tenantId = session('tenant_id')) {
            $tenant = Tenant::find($tenantId);
            if ($tenant && $tenant->slug) {
                $scheme = request()->getScheme();
                return redirect()->away($scheme . '://' . $tenant->slug . '.' . $host . '/login');
            }
        }

        // Di central domain tanpa session tenant → tampilkan form pencarian toko
        return inertia('Auth/Login', [
            'app_env' => app()->environment(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $login = $request->login;

        // Cari tenant: prioritas dari session, tenancy aktif, domain, lalu email
        $tenant = null;

        // 1. Dari session tenant_id
        $tenantId = session('tenant_id');
        if ($tenantId) {
            $tenant = Tenant::find($tenantId);
        }

        // 2. Dari tenancy yang sudah aktif
        if (!$tenant && tenancy()->initialized) {
            $tenant = tenancy()->tenant;
        }

        // 3. Dari domain/host (subdomain)
        if (!$tenant) {
            $host = $request->getHost();
            $domain = Domain::where('domain', $host)->first();
            if ($domain) {
                $tenant = Tenant::find($domain->tenant_id);
            }
        }

        // 4. Dari email di central DB
        if (!$tenant) {
            $tenant = Tenant::where('email', $login)->first();
        }
        if (!$tenant) {
            $tenant = Tenant::where('data->email', $login)->first();
        }

        if (!$tenant || !($tenant->is_active ?? false)) {
            return back()->withErrors(['login' => 'Akun tidak ditemukan atau tidak aktif.']);
        }

        // Initialize tenancy
        try {
            tenancy()->initialize($tenant);
        } catch (\Exception $e) {
            return back()->withErrors(['login' => 'Gagal mengakses database tenant.']);
        }

        // Simpan tenant_id di session (untuk session-based tenancy)
        $request->session()->put('tenant_id', $tenant->id);

        // Cari user di database tenant berdasarkan email ATAU username (name)
        $user = User::where('email', $login)->orWhere('name', $login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            // Catat failed login
            try {
                LoginHistory::record($user?->id ?? 0, 'failed', 'Email/Username atau password salah');
            } catch (\Exception $e) {}

            tenancy()->end();
            return back()->withErrors(['login' => 'Email/Username atau password salah.']);
        }

        if (!$user->active) {
            tenancy()->end();
            return back()->withErrors(['login' => 'Akun Anda dinonaktifkan.']);
        }

        // Cek 2FA: jika user sudah setup 2FA, redirect ke halaman challenge
        if ($user->hasTwoFactorEnabled() && \App\Services\FeatureFlagService::isEnabled('two_factor_auth')) {
            $request->session()->put('two_factor_user_id', $user->id);
            tenancy()->end();
            return redirect()->route('two-factor.challenge');
        }

        // Login user
        Auth::guard('web')->login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // Pastikan tenant_id tetap ada setelah session regenerate
        $request->session()->put('tenant_id', $tenant->id);

        // Catat login history & activity
        try {
            LoginHistory::record($user->id, 'success');
            ActivityLog::log('login', 'User login');
        } catch (\Exception $e) {}

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request)
    {
        try {
            ActivityLog::log('logout', 'User logout');
        } catch (\Exception $e) {}

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
