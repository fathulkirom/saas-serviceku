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
        $searchValue = $request->search_value;

        // Cari tenant berdasarkan field yang dipilih (case-insensitive)
        $tenants = Tenant::all()->filter(function ($tenant) use ($searchType, $searchValue) {
            $value = match ($searchType) {
                'name' => $tenant->tenant_name ?? '',
                'email' => $tenant->email ?? ($tenant->data['email'] ?? ''),
                'phone' => $tenant->phone ?? ($tenant->data['phone'] ?? ''),
                default => '',
            };
            return strtolower(trim($value)) === strtolower(trim($searchValue));
        });

        if ($tenants->isEmpty()) {
            $fieldLabel = match ($searchType) {
                'name' => 'Nama Toko',
                'email' => 'Email',
                'phone' => 'No. Telepon',
            };
            return back()->withErrors([
                'search_value' => "{$fieldLabel} \"{$searchValue}\" tidak ditemukan.",
            ]);
        }

        $tenant = $tenants->first();

        // Redirect ke subdomain tenant: {slug}.basedomain
        // Gunakan Inertia::location untuk external redirect
        $scheme = $request->getScheme();
        $baseHost = $request->getHost();
        $subdomainUrl = $scheme . '://' . $tenant->slug . '.' . $baseHost . '/login';

        return Inertia::location($subdomainUrl);
    }
}
