<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    public function run(): void
    {
        Plan::updateOrCreate(['slug' => 'trial'], [
            'name' => 'Trial',
            'description' => 'Masa percobaan gratis',
            'price' => 0,
            'trial_days' => 14,
            'features' => [
                // Akses penuh untuk fitur dasar
                'services' => true,
                'customers' => true,
                'products' => true,
                // Read only untuk fitur lanjutan
                'sales' => 'read_only',
                'reports' => 'read_only',
                'settings' => 'read_only',
                'monitoring' => 'read_only',
                // Tidak ada akses
                'multi_branch' => 'none',
                'transfer_stock' => 'none',
                'users' => 'none',
                'expenses' => 'none',
                'purchases' => 'none',
                'deposits' => 'none',
                'checklist' => 'none',
                'indents' => 'none',
                // Batas numerik
                'max_users' => 1,
                'max_branches' => 1,
            ],
            'is_active' => true,
        ]);

        Plan::updateOrCreate(['slug' => 'basic'], [
            'name' => 'Basic',
            'description' => 'Paket dasar untuk toko kecil',
            'price' => 99000,
            'trial_days' => 0,
            'features' => [
                'services' => true,
                'customers' => true,
                'products' => true,
                'sales' => true,
                'reports' => true,
                'settings' => true,
                'monitoring' => true,
                'multi_branch' => 'none',
                'transfer_stock' => 'none',
                'users' => 'read_only',
                'expenses' => true,
                'purchases' => true,
                'deposits' => true,
                'checklist' => true,
                'indents' => true,
                'cash_register' => true,
                'master_data' => true,
                'max_users' => 3,
                'max_branches' => 1,
            ],
            'is_active' => true,
        ]);

        Plan::updateOrCreate(['slug' => 'pro'], [
            'name' => 'Pro',
            'description' => 'Paket untuk toko multi-cabang',
            'price' => 199000,
            'trial_days' => 0,
            'features' => [
                'services' => true,
                'customers' => true,
                'products' => true,
                'sales' => true,
                'reports' => true,
                'settings' => true,
                'monitoring' => true,
                'multi_branch' => true,
                'transfer_stock' => true,
                'users' => true,
                'expenses' => true,
                'purchases' => true,
                'deposits' => true,
                'checklist' => true,
                'indents' => true,
                'cash_register' => true,
                'master_data' => true,
                'max_users' => 10,
                'max_branches' => 5,
            ],
            'is_active' => true,
        ]);

        Plan::updateOrCreate(['slug' => 'enterprise'], [
            'name' => 'Enterprise',
            'description' => 'Paket untuk jaringan servis besar',
            'price' => 499000,
            'trial_days' => 0,
            'features' => [
                'services' => true,
                'customers' => true,
                'products' => true,
                'sales' => true,
                'reports' => true,
                'settings' => true,
                'monitoring' => true,
                'multi_branch' => true,
                'transfer_stock' => true,
                'users' => true,
                'expenses' => true,
                'purchases' => true,
                'deposits' => true,
                'checklist' => true,
                'indents' => true,
                'cash_register' => true,
                'master_data' => true,
                'max_users' => 999,
                'max_branches' => 999,
            ],
            'is_active' => true,
        ]);
    }
}
