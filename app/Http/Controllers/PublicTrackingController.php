<?php

namespace App\Http\Controllers;

use App\Models\Tenant\Service;
use App\Models\Tenant\TenantSetting;
use Illuminate\Http\Request;
use Stancl\Tenancy\Facades\Tenancy;

class PublicTrackingController extends Controller
{
    /**
     * Halaman tracking publik untuk pelanggan.
     */
    public function show($trackingCode)
    {
        // Cari service berdasarkan tracking_code
        // Karena ini multi-tenant, kita perlu inisialisasi tenancy dulu
        // Atau cari di central DB dulu untuk menentukan tenant

        $tenant = null;
        $service = null;
        $tenantData = [];

        // Cek apakah ada domain yang match dengan request
        $host = request()->getHost();

        // Jika akses dari subdomain tenant, langsung inisialisasi
        $domain = \Stancl\Tenancy\Database\Models\Domain::where('domain', $host)->first();

        if ($domain) {
            try {
                $tenant = \App\Models\Tenant::find($domain->tenant_id);
                if ($tenant && $tenant->is_active) {
                    tenancy()->initialize($tenant);

                    $service = Service::with(['customer', 'technician', 'branch', 'photos', 'kategoriPerangkat', 'merek'])
                        ->where('tracking_code', $trackingCode)
                        ->first();

                    if ($service) {
                        $storeName = TenantSetting::getValue('store_name', $tenant->tenant_name);
                        $storeLogo = TenantSetting::getValue('logo', '');
                        $primaryColor = TenantSetting::getValue('primary_color', '#4F46E5');

                        return inertia('Public/Track', [
                            'service' => $service,
                            'storeName' => $storeName,
                            'storeLogo' => $storeLogo,
                            'primaryColor' => $primaryColor,
                            'trackingCode' => $trackingCode,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                // Tenant DB mungkin tidak ada
            }
        }

        // Jika tidak ditemukan
        return inertia('Public/Track', [
            'service' => null,
            'storeName' => 'ServiceKU',
            'storeLogo' => '',
            'primaryColor' => '#4F46E5',
            'trackingCode' => $trackingCode,
            'error' => 'Kode tracking tidak ditemukan.',
        ]);
    }
}
