<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        $manifestPath = public_path('build/manifest.json');
        if (file_exists($manifestPath)) {
            return md5_file($manifestPath);
        }
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'app_env' => fn () => app()->environment(),
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
            ],
            'tenant' => function () {
                if (!tenancy()->initialized) return null;
                $tenantId = tenant('id');
                if (!$tenantId) return null;
                return Cache::remember('tenant_theme_' . $tenantId, 300, function () use ($tenantId) {
                    return [
                        'name' => \App\Models\Tenant\TenantSetting::getValue('store_name', tenant()->tenant_name ?? 'ServiceKU'),
                        'id' => $tenantId,
                        'primary_color' => \App\Models\Tenant\TenantSetting::getValue('primary_color', '#4F46E5'),
                    ];
                });
            },
            'demo_mode' => fn () => tenancy()->initialized
                ? Cache::remember('tenant_demo_' . tenant('id'), 300, function () {
                    return \App\Models\Tenant\TenantSetting::getValue('demo_mode', 'false') === 'true';
                })
                : false,
            'plan_access' => fn () => tenancy()->initialized
                ? tenancy()->tenant->getAllEffectiveFeatureAccess()
                : null,
            'default_menus' => fn () => tenancy()->initialized && $request->user()
                ? tenancy()->tenant->plan?->getDefaultMenusForRole($request->user()->role)
                    ?? \App\Models\Plan::getBuiltInDefaultMenus($request->user()->role)
                : [],
            'timezone' => fn () => config('app.timezone', 'UTC'),
            'role_permissions' => fn () => [
                'owner' => ['manage_users', 'manage_settings', 'manage_finance', 'manage_products', 'manage_customers', 'manage_sales', 'manage_cash_register', 'manage_deposits', 'manage_purchases', 'manage_branches', 'manage_indents', 'void_transactions', 'assign_technician', 'work_on_services', 'delete_models', 'quick_stock'],
                'admin' => ['manage_finance', 'manage_products', 'manage_customers', 'manage_sales', 'manage_cash_register', 'manage_deposits', 'manage_purchases', 'manage_indents', 'void_transactions', 'assign_technician', 'work_on_services', 'delete_models'],
                'manager' => ['manage_finance', 'manage_products', 'manage_customers', 'manage_sales', 'manage_cash_register', 'manage_deposits', 'manage_purchases', 'manage_indents', 'work_on_services'],
                'head_store' => ['manage_finance', 'manage_products', 'manage_customers', 'manage_sales', 'manage_cash_register', 'manage_deposits', 'work_on_services'],
                'cs' => ['manage_customers', 'manage_indents', 'assign_technician', 'work_on_services'],
                'technician' => ['work_on_services'],
                'cashier' => ['manage_sales', 'manage_cash_register'],
                'courier' => [],
                'custom' => [],
            ],
        ];
    }
}
