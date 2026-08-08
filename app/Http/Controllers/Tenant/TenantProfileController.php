<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\Service;
use App\Models\Tenant\Product;
use App\Models\Tenant\Sale;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TenantProfileController extends Controller
{
    /**
     * Tampilkan halaman profil toko / tenant.
     */
    public function index()
    {
        $tenant = tenancy()->tenant;
        $tenant->load('plan', 'domains');

        // Ambil settings toko
        $settings = [
            'store_name' => TenantSetting::getValue('store_name', $tenant->tenant_name),
            'primary_color' => TenantSetting::getValue('primary_color', '#4F46E5'),
            'logo' => TenantSetting::getValue('logo'),
            'address' => TenantSetting::getValue('address'),
            'phone' => TenantSetting::getValue('phone'),
            'whatsapp_number' => TenantSetting::getValue('whatsapp_number'),
        ];

        // Ambil statistik ringkas
        $stats = [
            'total_users' => User::count(),
            'total_services' => Service::count(),
            'active_services' => Service::whereIn('status', ['menunggu_alokasi', 'dikerjakan'])->count(),
            'completed_services' => Service::where('status', 'selesai')->count(),
            'total_products' => Product::count(),
            'low_stock_products' => Product::whereColumn('stock_quantity', '<=', 'min_stock')->count(),
            'total_sales' => Sale::count(),
            'revenue_today' => Sale::whereDate('created_at', today())->sum('total'),
            'revenue_month' => Sale::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total'),
        ];

        // Informasi cabang
        $branches = DB::table('branches')->get();

        return redirect()->route('pengaturan.index', ['tab' => 'profil']);
    }
}
