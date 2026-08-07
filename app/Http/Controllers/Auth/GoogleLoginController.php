<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\LoginHistory;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Stancl\Tenancy\Database\Models\Domain;
use Throwable;

class GoogleLoginController extends Controller
{
    public function redirect(Request $request)
    {
        if (! config('services.google.client_id')) {
            return redirect()->route('login')->with('error', 'Login Google belum dikonfigurasi.');
        }

        $tenant = $this->resolveTenantForRequest($request);
        if (! $tenant) {
            return redirect()->route('login')->with('error', 'Pilih toko dulu sebelum login dengan Google.');
        }

        $request->session()->put('google_login_tenant_id', $tenant->id);
        $request->session()->put('google_login_tenant_host', $this->tenantHost($tenant, $request));

        return Socialite::driver('google')
            ->redirectUrl($this->centralCallbackUrl())
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    public function callback(Request $request)
    {
        $tenant = $this->resolveTenantFromGoogleSession($request);
        if (! $tenant) {
            return redirect()->route('login')->with('error', 'Sesi login Google tidak menemukan toko asal. Silakan coba dari halaman login toko.');
        }

        try {
            $googleUser = Socialite::driver('google')
                ->redirectUrl($this->centralCallbackUrl())
                ->user();
        } catch (Throwable $e) {
            return redirect()->away($this->tenantLoginUrl($tenant, $request))
                ->with('error', 'Login Google gagal: '.$e->getMessage());
        }

        tenancy()->initialize($tenant);

        $user = User::where('google_id', $googleUser->id)
            ->orWhere('email', $googleUser->email)
            ->first();

        if ($user) {
            $user->update([
                'google_id' => $googleUser->id,
                'google_avatar' => $googleUser->avatar ?? $user->google_avatar,
            ]);

            ActivityLog::log('auth', 'Login dengan Google: '.$user->name);
            LoginHistory::record($user->id, 'success');

            Auth::login($user);
            $request->session()->regenerate();
            $request->session()->put('tenant_id', $tenant->id);
            $request->session()->forget(['google_login_tenant_id', 'google_login_tenant_host']);

            return redirect()->away($this->tenantDashboardUrl($tenant, $request));
        }

        $request->session()->forget(['google_login_tenant_id', 'google_login_tenant_host']);

        return redirect()->away($this->tenantLoginUrl($tenant, $request))
            ->with('error', 'Tidak ada akun yang terhubung dengan Google ini. Hubungi owner untuk menghubungkan akun.');
    }

    public function link(string $googleId)
    {
        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login');
        }

        try {
            $googleUser = Socialite::driver('google')->userFromToken($googleId);
            $user->update([
                'google_id' => $googleUser->id,
                'google_avatar' => $googleUser->avatar,
            ]);

            ActivityLog::log('auth', 'Akun Google terhubung: '.$googleUser->email);

            return back()->with('success', 'Akun Google berhasil ditautkan.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menautkan akun Google.');
        }
    }

    public function unlink()
    {
        $user = Auth::user();
        $user->update([
            'google_id' => null,
            'google_avatar' => null,
        ]);

        ActivityLog::log('auth', 'Akun Google dilepaskan');

        return back()->with('success', 'Akun Google berhasil dilepaskan.');
    }

    private function resolveTenantForRequest(Request $request): ?Tenant
    {
        if (tenancy()->initialized) {
            return tenancy()->tenant;
        }

        if ($tenantId = $request->session()->get('tenant_id')) {
            return Tenant::find($tenantId);
        }

        $domain = Domain::where('domain', $request->getHost())->first();

        return $domain ? Tenant::find($domain->tenant_id) : null;
    }

    private function resolveTenantFromGoogleSession(Request $request): ?Tenant
    {
        $tenantId = $request->session()->get('google_login_tenant_id')
            ?? $request->session()->get('tenant_id');

        return $tenantId ? Tenant::find($tenantId) : null;
    }

    private function centralCallbackUrl(): string
    {
        return config('services.google.redirect')
            ?: rtrim(config('app.url'), '/').'/auth/google/callback';
    }

    private function tenantHost(Tenant $tenant, Request $request): string
    {
        $sessionHost = $request->session()->get('google_login_tenant_host');
        if ($sessionHost && Domain::where('domain', $sessionHost)->where('tenant_id', $tenant->id)->exists()) {
            return $sessionHost;
        }

        $requestHost = $request->getHost();
        if (Domain::where('domain', $requestHost)->where('tenant_id', $tenant->id)->exists()) {
            return $requestHost;
        }

        $domain = $tenant->domains()->first();
        if ($domain) {
            return $domain->domain;
        }

        $centralHost = parse_url(config('app.url'), PHP_URL_HOST) ?: config('tenancy.central_domains.0', $requestHost);

        return $tenant->slug ? $tenant->slug.'.'.$centralHost : $centralHost;
    }

    private function tenantLoginUrl(Tenant $tenant, Request $request): string
    {
        return $this->tenantUrl($tenant, $request, '/login');
    }

    private function tenantDashboardUrl(Tenant $tenant, Request $request): string
    {
        return $this->tenantUrl($tenant, $request, '/dashboard');
    }

    private function tenantUrl(Tenant $tenant, Request $request, string $path): string
    {
        $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: $request->getScheme();

        return $scheme.'://'.$this->tenantHost($tenant, $request).$path;
    }
}
