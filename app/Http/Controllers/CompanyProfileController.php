<?php

namespace App\Http\Controllers;

use App\Models\Plan;

class CompanyProfileController extends Controller
{
    public function index()
    {
        if (request()->getHost() === env('ADMIN_DOMAIN', 'admin.serviceku.my.id')) {
            return redirect()->route('admin.dashboard');
        }

        // Hitung total tenant aktif untuk statistik
        $totalTenants = \App\Models\Tenant::where('is_active', true)->count();

        // Ambil paket aktif untuk ditampilkan
        $plans = Plan::where('is_active', true)->get()->map(function ($plan) {
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
            ];
        });

        // Fallback jika tidak ada paket aktif
        if ($plans->isEmpty()) {
            $plans = collect([
                ['name' => 'Basic', 'price' => 99000, 'promo_price' => null, 'is_promo_active' => false, 'discount_percent' => 0, 'effective_price' => 99000, 'featured' => false, 'features_list' => ['3 User', '1 Cabang', 'Manajemen Servis', 'POS & Stok', 'Laporan Dasar'], 'trial_days' => 14],
                ['name' => 'Pro', 'price' => 199000, 'promo_price' => null, 'is_promo_active' => false, 'discount_percent' => 0, 'effective_price' => 199000, 'featured' => true, 'features_list' => ['10 User', '5 Cabang', 'Multi-Cabang', 'Transfer Stok', 'Laporan Lengkap', 'Monitoring'], 'trial_days' => 14],
                ['name' => 'Enterprise', 'price' => 499000, 'promo_price' => null, 'is_promo_active' => false, 'discount_percent' => 0, 'effective_price' => 499000, 'featured' => false, 'features_list' => ['Unlimited User', 'Unlimited Cabang', 'Custom Role', 'Prioritas Support', 'Semua Fitur'], 'trial_days' => 14],
            ]);
        }

        return inertia('Landing/Index', [
            'stats' => [
                'tenants' => $totalTenants,
                'features' => 8,
                'users' => $totalTenants * 2,
            ],
            'plans' => $plans,
        ]);
    }
}
