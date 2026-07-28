<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\Tenant\User;
use App\Models\Tenant\LoginHistory;
use App\Models\Tenant\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Stancl\Tenancy\Database\Models\Domain;

class DevLoginController extends Controller
{
    /**
     * Quick login untuk development mode.
     * ONLY available in local/development environment.
     */
    public function __invoke(Request $request)
    {
        if (!app()->environment('local', 'development')) {
            abort(404, 'Only available in development mode.');
        }

        $role = $request->get('role', 'owner');
        $tenantSlug = $request->get('tenant', 'toko-servis-abc');

        // Cari tenant
        $tenant = Tenant::where('slug', $tenantSlug)->first();

        if (!$tenant) {
            // Fallback: cari tenant pertama
            $tenant = Tenant::first();
        }

        if (!$tenant || !$tenant->is_active) {
            return redirect('/login')->withErrors(['error' => 'Tenant tidak ditemukan atau tidak aktif.']);
        }

        // Initialize tenancy
        try {
            tenancy()->initialize($tenant);
        } catch (\Exception $e) {
            return redirect('/login')->withErrors(['error' => 'Gagal mengakses database tenant.']);
        }

        // Cari user berdasarkan role
        $user = User::where('role', $role)->first();

        if (!$user) {
            // Fallback: ambil user pertama
            $user = User::first();
        }

        if (!$user) {
            tenancy()->end();
            return redirect('/login')->withErrors(['error' => 'Tidak ada user di tenant ini.']);
        }

        if (!$user->active) {
            tenancy()->end();
            return redirect('/login')->withErrors(['error' => 'Akun user tidak aktif.']);
        }

        // Login
        Auth::guard('web')->login($user);
        $request->session()->put('tenant_id', $tenant->id);

        // Catat history
        try {
            LoginHistory::record($user->id, 'success');
            ActivityLog::log('login', 'Dev quick login as ' . $user->role);
        } catch (\Exception $e) {}

        // Redirect ke subdomain tenant
        $slug = $tenant->slug ?? $tenant->domains->first()?->domain ?? 'toko-servis-abc';
        $host = str_contains($slug, '.') ? $slug : $slug . '.localhost';
        $serverPort = $_SERVER['SERVER_PORT'] ?? $request->getPort();
        $port = ($serverPort && $serverPort != 80 && $serverPort != 443) ? ':' . $serverPort : '';
        $url = 'http://' . $host . $port . '/dashboard';
        return redirect($url);
    }
}
