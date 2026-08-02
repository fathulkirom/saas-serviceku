<?php

namespace Database\Seeders\Tenant;

use App\Models\Tenant\Module;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ModuleRegistrySeeder extends Seeder
{
    protected array $modules = [
        ['key' => 'dashboard', 'name' => 'Dashboard', 'icon' => 'dashboard', 'sort_order' => 1, 'category' => 'operational', 'is_default' => true, 'requires' => null, 'features' => ['dashboard']],
        ['key' => 'customer', 'name' => 'Customer', 'icon' => 'users', 'sort_order' => 2, 'category' => 'operational', 'is_default' => true, 'requires' => null, 'features' => ['customers']],
        ['key' => 'service', 'name' => 'Service', 'icon' => 'wrench', 'sort_order' => 3, 'category' => 'operational', 'is_default' => true, 'requires' => ['customer'], 'features' => ['services', 'checklist', 'indents']],
        ['key' => 'inventory', 'name' => 'Inventory', 'icon' => 'package', 'sort_order' => 4, 'category' => 'operational', 'is_default' => true, 'requires' => null, 'features' => ['products', 'transfer_stock']],
        ['key' => 'sales', 'name' => 'POS / Sales', 'icon' => 'cart', 'sort_order' => 5, 'category' => 'operational', 'is_default' => true, 'requires' => ['inventory'], 'features' => ['sales']],
        ['key' => 'purchase', 'name' => 'Purchase', 'icon' => 'truck', 'sort_order' => 6, 'category' => 'operational', 'is_default' => false, 'requires' => ['inventory', 'supplier'], 'features' => ['purchases']],
        ['key' => 'supplier', 'name' => 'Supplier', 'icon' => 'building', 'sort_order' => 7, 'category' => 'operational', 'is_default' => false, 'requires' => null, 'features' => []],
        ['key' => 'cash_register', 'name' => 'Cash Register', 'icon' => 'cash', 'sort_order' => 8, 'category' => 'operational', 'is_default' => true, 'requires' => null, 'features' => ['cash_register', 'deposits']],
        ['key' => 'expense', 'name' => 'Expense', 'icon' => 'receipt', 'sort_order' => 9, 'category' => 'operational', 'is_default' => false, 'requires' => null, 'features' => ['expenses']],
        ['key' => 'finance', 'name' => 'Finance', 'icon' => 'chart', 'sort_order' => 10, 'category' => 'operational', 'is_default' => true, 'requires' => null, 'features' => []],
        ['key' => 'report', 'name' => 'Report', 'icon' => 'document', 'sort_order' => 11, 'category' => 'operational', 'is_default' => true, 'requires' => null, 'features' => ['reports']],
        ['key' => 'monitoring', 'name' => 'Monitoring', 'icon' => 'eye', 'sort_order' => 12, 'category' => 'operational', 'is_default' => false, 'requires' => null, 'features' => ['monitoring']],
        ['key' => 'branch', 'name' => 'Multi Branch', 'icon' => 'git-branch', 'sort_order' => 13, 'category' => 'operational', 'is_default' => false, 'requires' => null, 'features' => ['multi_branch']],
        ['key' => 'user', 'name' => 'User Management', 'icon' => 'shield', 'sort_order' => 14, 'category' => 'configuration', 'is_default' => true, 'requires' => null, 'features' => ['users']],
        ['key' => 'settings', 'name' => 'Settings', 'icon' => 'cog', 'sort_order' => 15, 'category' => 'configuration', 'is_default' => true, 'requires' => null, 'features' => ['settings']],
        ['key' => 'warranty', 'name' => 'Warranty', 'icon' => 'shield-check', 'sort_order' => 20, 'category' => 'operational', 'is_default' => false, 'requires' => ['service'], 'features' => [], 'status' => 'future'],
        ['key' => 'provider', 'name' => 'Provider Integrations', 'icon' => 'plug', 'sort_order' => 21, 'category' => 'configuration', 'is_default' => false, 'requires' => null, 'features' => []],
        ['key' => 'subscription', 'name' => 'Subscription', 'icon' => 'credit-card', 'sort_order' => 22, 'category' => 'configuration', 'is_default' => true, 'requires' => null, 'features' => []],
        ['key' => 'marketplace', 'name' => 'Marketplace', 'icon' => 'store', 'sort_order' => 30, 'category' => 'future', 'is_default' => false, 'requires' => ['inventory'], 'features' => [], 'status' => 'future'],
        ['key' => 'ai', 'name' => 'AI Assistant', 'icon' => 'sparkles', 'sort_order' => 31, 'category' => 'future', 'is_default' => false, 'requires' => null, 'features' => [], 'status' => 'future'],
    ];

    public function run(): void
    {
        if (!Schema::hasTable('modules')) {
            return;
        }

        foreach ($this->modules as $mod) {
            Module::updateOrCreate(
                ['key' => $mod['key']],
                array_merge($mod, ['status' => $mod['status'] ?? 'active'])
            );
        }
    }
}
