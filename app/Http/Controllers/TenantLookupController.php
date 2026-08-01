<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TenantLookupController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'search_type' => 'required|string|in:name,email,phone',
            'search_value' => 'required|string|max:255',
        ]);

        $searchType = $request->search_type;
        $searchValue = trim((string) $request->search_value);

        // Pencarian parsial & case-insensitive (bukan exact match)
        $tenants = Tenant::all()->filter(function ($tenant) use ($searchType, $searchValue) {
            $value = match ($searchType) {
                'name' => $tenant->tenant_name ?? '',
                'email' => $tenant->email ?? ($tenant->data['email'] ?? ''),
                'phone' => $tenant->phone ?? ($tenant->data['phone'] ?? ''),
                default => '',
            };
            return mb_stripos($value, $searchValue) !== false;
        })->values();

        $fieldLabel = match ($searchType) {
            'name' => 'Nama Toko',
            'email' => 'Email',
            'phone' => 'No. Telepon',
        };

        if ($tenants->isEmpty()) {
            return back()->withErrors([
                'search_value' => "{$fieldLabel} \"{$searchValue}\" tidak ditemukan.",
            ]);
        }

        // Beberapa toko cocok -> minta user mengetik lebih spesifik
        if ($tenants->count() > 1) {
            $names = $tenants->take(5)->pluck('tenant_name')->implode(', ');
            $suffix = $tenants->count() > 5 ? ', ...' : '';
            return back()->withErrors([
                'search_value' => "Ditemukan {$tenants->count()} toko ({$names}{$suffix}). Ketik {$fieldLabel} yang lebih spesifik.",
            ]);
        }

        $tenant = $tenants->first();

        // Redirect ke subdomain tenant: {slug}.{centralDomain}/login.
        // Pakai central domain dari config (bukan getHost) agar benar walaupun
        // form dikirim dari subdomain tenant (getHost akan mengembalikan subdomain).
        $scheme = $request->getScheme();
        $baseHost = str_replace('www.', '', (string) config('tenancy.central_domains.0', 'serviceku.my.id'));
        $subdomainUrl = $scheme . '://' . $tenant->slug . '.' . $baseHost . '/login';

        // Inertia::location untuk halaman login (SPA); redirect biasa agar form
        // di landing (Blade, non-Inertia) ikut mengarahkan browser.
        if ($request->header('X-Inertia')) {
            return Inertia::location($subdomainUrl);
        }

        return redirect()->away($subdomainUrl);
    }
}
