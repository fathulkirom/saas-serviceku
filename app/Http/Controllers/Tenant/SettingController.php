<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\MasterData;
use App\Models\Tenant\MasterLaborService;
use App\Models\Tenant\ChecklistTemplate;
use App\Models\Tenant\CustomField;
use App\Models\Tenant\User;
use App\Models\Tenant\Service;
use App\Models\Tenant\Product;
use App\Models\Tenant\Sale;
use App\Models\Plan;
use App\Services\GoogleDrivePhotoService;
use App\Services\SettingsService;
use App\Services\FeatureEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(Request $request): Response
    {
        $tab = $request->get('tab', 'profil');
        $driveService = new GoogleDrivePhotoService(tenancy()->tenant->id);

        return Inertia::render('Pengaturan/Index', [
            'activeTab' => $tab,

            'profile' => fn() => tenancy()->tenant->load('plan', 'domains'),

            'profileSettings' => fn() => [
                'store_name' => TenantSetting::getValue('store_name', tenancy()->tenant->tenant_name),
                'primary_color' => TenantSetting::getValue('primary_color', '#4F46E5'),
                'logo' => TenantSetting::getValue('logo'),
                'address' => TenantSetting::getValue('address'),
                'phone' => TenantSetting::getValue('phone'),
                'whatsapp_number' => TenantSetting::getValue('whatsapp_number'),
            ],

            'profileStats' => fn() => [
                'branches' => DB::table('branches')->count(),
                'users' => User::count(),
                'total_users' => User::count(),
                'customers' => \App\Models\Tenant\Customer::count(),
                'total_services' => Service::count(),
                'active_services' => Service::whereIn('status', ['menunggu_alokasi', 'dikerjakan'])->count(),
                'completed_services' => Service::where('status', 'selesai')->count(),
                'products' => Product::count(),
                'total_products' => Product::count(),
                'low_stock_products' => Product::whereColumn('stock_quantity', '<=', 'min_stock')->count(),
                'total_sales' => Sale::count(),
                'revenue_today' => Sale::whereDate('created_at', today())->sum('total'),
                'revenue_month' => Sale::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total'),
            ],

            'profileBranches' => fn() => DB::table('branches')->get(),

            'settings' => fn() => [
                'store_name' => TenantSetting::getValue('store_name', 'ServiceKU'),
                'primary_color' => TenantSetting::getValue('primary_color', '#4F46E5'),
                'logo' => TenantSetting::getValue('logo'),
                'address' => TenantSetting::getValue('address'),
                'phone' => TenantSetting::getValue('phone'),
                'whatsapp_number' => TenantSetting::getValue('whatsapp_number'),
                'paper_size' => TenantSetting::getValue('paper_size', 'a4'),
                'maintenance_mode' => TenantSetting::getValue('maintenance_mode', 'false'),
                'maintenance_message' => TenantSetting::getValue('maintenance_message', ''),
            ],

            'deviceCategories' => fn() => MasterData::getByCategory('device_category'),
            'brands' => fn() => MasterData::getByCategory('brand'),
            'units' => fn() => MasterData::getByCategory('unit'),
            'arrivalMethods' => fn() => MasterData::getByCategory('arrival_method'),
            'paymentMethods' => fn() => MasterData::getByCategory('payment_method'),
            'equipment' => fn() => MasterData::getByCategory('equipment'),
            'laborServices' => fn() => MasterLaborService::where('branch_id', auth()->user()->branch_id)->orderBy('name')->get(),
            'checklistTemplates' => fn() => ChecklistTemplate::with('items')->where('is_active', true)->get(),
            'driveConnected' => fn() => $driveService->isConnected(),
            'driveInfo' => fn() => $driveService->getConnectionInfo(),
            'driveAuthUrl' => fn() => $driveService->getAuthUrl(),

            'tenant' => fn() => tenancy()->tenant,
            'currentPlan' => fn() => tenancy()->tenant->plan ? [
                'id' => tenancy()->tenant->plan->id,
                'name' => tenancy()->tenant->plan->name,
                'slug' => tenancy()->tenant->plan->slug,
                'price' => (float) tenancy()->tenant->plan->price,
            ] : null,
            'plans' => fn() => Plan::where('is_active', true)->get()->map(function ($plan) {
                return [
                    'id' => $plan->id,
                    'name' => $plan->name,
                    'slug' => $plan->slug,
                    'description' => $plan->description,
                    'price' => (float) $plan->price,
                    'promo_price' => $plan->promo_price ? (float) $plan->promo_price : null,
                    'is_promo_active' => $plan->isPromoActive(),
                    'discount_percent' => $plan->discountPercent(),
                    'effective_price' => $plan->effectivePrice(),
                    'trial_days' => $plan->trial_days,
                    'features' => $plan->features ?? [],
                ];
            }),
            'voucherDiscount' => fn() => tenancy()->tenant->voucher_discount,

            'demoStats' => fn() => [
                'customers_count' => \App\Models\Tenant\Customer::count(),
                'products_count' => Product::count(),
                'services_count' => Service::count(),
                'demo_mode' => TenantSetting::getValue('demo_mode', 'false') === 'true',
                'demo_data_generated' => TenantSetting::getValue('demo_data_generated', 'false') === 'true',
                'demo_generated_at' => TenantSetting::getValue('demo_generated_at'),
            ],

            'customFields' => fn() => auth()->user()->canManageCustomFields()
                ? CustomField::where('branch_id', auth()->user()->branch_id)->orderBy('ordering')->get()
                : [],

            'canManageCustomFields' => auth()->user()->canManageCustomFields(),

            'waGateway' => fn() => \App\Models\Tenant\WaGatewayConfig::where('tenant_id', tenancy()->tenant->id)->first(),
        ]);
    }
}
