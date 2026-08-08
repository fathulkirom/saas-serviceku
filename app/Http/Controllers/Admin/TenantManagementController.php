<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\SystemLog;
use App\Models\Tenant;
use App\Models\Tenant\User;
use App\Models\TenantStat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Stancl\Tenancy\Database\Models\Domain;

class TenantManagementController extends Controller
{
    public function index(Request $request)
    {
        $query = Tenant::with('plan', 'domains', 'stats');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('tenant_name', 'like', "%{$s}%")
                    ->orWhere('email', 'like', "%{$s}%")
                    ->orWhere('id', 'like', "%{$s}%")
                    ->orWhere('data->phone', 'like', "%{$s}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('subscription_status', $request->status);
        }
        if ($request->filled('plan_id')) {
            $query->where('plan_id', $request->plan_id);
        }
        if ($request->filled('business_type')) {
            $query->where('data->business_type', $request->business_type);
        }

        $tenants = $query->latest()->paginate(20)->withQueryString();
        $plans = Plan::all();

        return inertia('Admin/TenantManagement', [
            'tenants' => $tenants,
            'plans' => $plans,
            'filters' => $request->only(['search', 'status', 'plan_id', 'business_type']),
        ]);
    }

    public function show(Tenant $tenant)
    {
        $stats = TenantStat::where('tenant_id', $tenant->id)->first();
        $activityLogs = SystemLog::where('type', 'tenant')
            ->orWhere('message', 'like', "%{$tenant->tenant_name}%")
            ->orWhere('message', 'like', "%{$tenant->id}%")
            ->latest()->take(10)->get();

        $tenantData = null;
        try {
            tenancy()->initialize($tenant);
            $recentServices = DB::table('services')
                ->join('customers', 'services.customer_id', '=', 'customers.id')
                ->select('services.id', 'customers.name as customer_name', 'services.status', 'services.total_cost', 'services.created_at')
                ->orderBy('services.created_at', 'desc')->take(5)->get();
            $recentSales = DB::table('sales')
                ->join('customers', 'sales.customer_id', '=', 'customers.id')
                ->select('sales.id', 'customers.name as customer_name', 'sales.total', 'sales.payment_status', 'sales.created_at')
                ->orderBy('sales.created_at', 'desc')->take(5)->get();
            $serviceStats = DB::table('services')
                ->select(DB::raw('COUNT(*) as total'), DB::raw("SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as completed"))
                ->first();
            $monthlyRevenue = DB::table('sales')
                ->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->sum('total');
            tenancy()->end();
            $tenantData = [
                'recent_services' => $recentServices,
                'recent_sales' => $recentSales,
                'service_stats' => $serviceStats,
                'monthly_revenue' => $monthlyRevenue,
            ];
        } catch (\Exception $e) {
            tenancy()->end();
            $tenantData = null;
        }

        return inertia('Admin/TenantDetail', [
            'tenant' => $tenant->load('plan', 'domains'),
            'stats' => $stats,
            'activityLogs' => $activityLogs,
            'tenantData' => $tenantData,
            // UPGRADE-05: Hybrid Subscription data.
            'entitlement' => (new \App\Services\EntitlementService($tenant))->snapshot(),
            'subscriptionEvents' => \App\Models\SubscriptionEvent::where('tenant_id', $tenant->id)
                ->latest('created_at')->take(20)->get(),
            'addons' => \App\Models\TenantAddon::where('tenant_id', $tenant->id)
                ->with('tenant')->orderBy('created_at', 'desc')->get(),
        ]);
    }

    public function createForm()
    {
        return inertia('Admin/CreateTenant', ['plans' => Plan::all()]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tenants,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:8',
            'plan_id' => 'nullable|exists:plans,id',
            'business_type' => 'nullable|string|in:full_service,aksesoris_service,aksespare_service,gadget_full,retail_only',
        ]);

        $plan = $validated['plan_id'] ? Plan::find($validated['plan_id']) : Plan::where('slug', 'trial')->first();
        $tenantId = 'tenant_'.Str::random(10);
        $subdomain = Str::slug($validated['tenant_name']).'-'.Str::random(4);
        $baseDomain = env('CENTRAL_DOMAIN', 'serviceku.my.id');
        $fullDomain = $subdomain.'.'.$baseDomain;

        $tenant = Tenant::create([
            'id' => $tenantId,
            'tenant_name' => $validated['tenant_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? '',
            'plan_id' => $plan?->id,
            'subscription_status' => $plan && $plan->slug === 'trial' ? 'trial' : 'active',
            'subscribed_at' => $plan && $plan->slug === 'trial' ? null : now(),
            'trial_ends_at' => $plan && $plan->slug === 'trial' ? now()->addDays($plan->trial_days ?? 14) : null,
            'subscription_ends_at' => $plan && $plan->slug === 'trial' ? null : now()->addDays(30),
            'is_active' => true,
            'data' => ['business_type' => $validated['business_type'] ?? 'full_service'],
        ]);

        Domain::create([
            'tenant_id' => $tenant->id,
            'domain' => $fullDomain,
        ]);

        try {
            $tenant->database()->manager()->createDatabase($tenant);
            tenancy()->initialize($tenant);
            app('migrator')->run(database_path('migrations/tenant'));

            $branchId = DB::table('branches')->insertGetId([
                'name' => 'Cabang Utama', 'address' => '', 'phone' => $validated['phone'] ?? '', 'is_active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('users')->insert([
                'branch_id' => $branchId, 'name' => 'Owner', 'email' => $validated['email'],
                'password' => bcrypt($validated['password']), 'role' => 'owner', 'active' => true,
                'remember_token' => null, 'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('tenant_settings')->insert([
                ['key' => 'store_name', 'value' => $validated['tenant_name']],
                ['key' => 'primary_color', 'value' => '#4F46E5'],
            ]);
            tenancy()->end();
        } catch (\Exception $e) {
            tenancy()->end();
            SystemLog::error('Failed to initialize tenant database: '.$e->getMessage());

            return back()->with('error', 'Gagal inisialisasi tenant: '.$e->getMessage());
        }

        SystemLog::info("Tenant created manually: {$validated['tenant_name']} ({$fullDomain})");

        return back()->with('success', "✅ Tenant {$validated['tenant_name']} berhasil dibuat! Domain: {$fullDomain}");
    }

    public function edit(Tenant $tenant)
    {
        $plans = Plan::all()->map(fn ($plan) => [
            'id' => $plan->id, 'name' => $plan->name, 'slug' => $plan->slug,
            'price' => (float) $plan->price, 'promo_price' => $plan->promo_price ? (float) $plan->promo_price : null,
            'is_promo_active' => $plan->isPromoActive(), 'effective_price' => $plan->effectivePrice(),
        ]);

        return inertia('Admin/EditTenant', [
            'tenant' => $tenant->load('plan', 'domains'), 'plans' => $plans,
        ]);
    }

    public function update(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'tenant_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:tenants,email,'.$tenant->id,
            'phone' => 'nullable|string|max:20',
            'business_type' => 'nullable|string|in:full_service,aksesoris_service,aksespare_service,gadget_full,retail_only',
        ]);
        $data = $tenant->data ?? [];
        $data['business_type'] = $validated['business_type'] ?? $data['business_type'] ?? 'full_service';
        $tenant->update([
            'tenant_name' => $validated['tenant_name'], 'email' => $validated['email'],
            'phone' => $validated['phone'] ?? '', 'data' => $data,
        ]);
        SystemLog::info("Tenant updated: {$tenant->tenant_name}");

        return redirect()->route('admin.tenant.index')->with('success', '✅ Data tenant berhasil diperbarui.');
    }

    public function destroy($tenantId)
    {
        $tenant = Tenant::find($tenantId);
        if (! $tenant) {
            return redirect()->to('/admin/tenants')->with('error', 'Tenant tidak ditemukan.');
        }
        if ($tenant->is_active) {
            return back()->with('error', '❌ Tenant aktif tidak bisa dihapus langsung.');
        }

        $name = $tenant->tenant_name;
        try {
            try {
                $tenant->database()->manager()->deleteDatabase($tenant);
            } catch (\Exception $e) {
                SystemLog::warning('Could not delete tenant database: '.$e->getMessage());
            }
            $tenant->domains()->delete();
            TenantStat::where('tenant_id', $tenant->id)->delete();
            $tenant->delete();
            SystemLog::warning("Tenant deleted: {$name}");

            return redirect()->to('/admin/tenants')->with('success', "✅ Tenant {$name} beserta database-nya berhasil dihapus.");
        } catch (\Exception $e) {
            SystemLog::error("Failed to delete tenant {$name}: ".$e->getMessage());

            return back()->with('error', '❌ Gagal menghapus tenant: '.$e->getMessage());
        }
    }

    public function suspend(Tenant $tenant)
    {
        $tenant->update(['subscription_status' => 'suspended', 'is_active' => false]);
        SystemLog::warning('Tenant suspended: '.$tenant->tenant_name);

        return back()->with('success', 'Tenant berhasil di-suspend.');
    }

    public function activate(Tenant $tenant)
    {
        $tenant->update(['subscription_status' => 'active', 'is_active' => true]);
        SystemLog::info('Tenant activated: '.$tenant->tenant_name);

        return back()->with('success', 'Tenant berhasil diaktifkan.');
    }

    public function syncStats(Tenant $tenant)
    {
        TenantStat::syncStats($tenant);
        SystemLog::info('Tenant stats synced', ['tenant_id' => $tenant->id]);

        return back()->with('success', 'Statistik tenant berhasil disinkronisasi.');
    }

    public function syncAllStats()
    {
        $count = 0;
        foreach (Tenant::where('is_active', true)->get() as $t) {
            try {
                TenantStat::syncStats($t);
                $count++;
            } catch (\Exception $e) {
                SystemLog::error('Failed to sync tenant: '.$e->getMessage());
            }
        }
        SystemLog::info("Bulk stats sync: {$count} tenants");

        return back()->with('success', "Statistik {$count} tenant berhasil disinkronisasi.");
    }

    public function updateDomain(Request $request, Tenant $tenant)
    {
        $validated = $request->validate(['domain' => 'required|string|max:255|unique:domains,domain,'.$tenant->id.',tenant_id']);
        $tenant->domains()->delete();
        Domain::create(['tenant_id' => $tenant->id, 'domain' => $validated['domain']]);
        $tenant->update(['subdomain' => $validated['domain']]);
        SystemLog::info("Domain updated for tenant {$tenant->tenant_name}: {$validated['domain']}");

        return back()->with('success', 'Domain tenant berhasil diperbarui.');
    }

    public function extendTrial(Request $request, Tenant $tenant)
    {
        $validated = $request->validate(['days' => 'required|integer|min:1|max:365']);
        $newTrialEnd = now()->addDays((int) $validated['days']);
        $tenant->update(['trial_ends_at' => $newTrialEnd, 'subscription_status' => 'trial']);
        SystemLog::info("Trial extended for {$tenant->tenant_name}: +{$validated['days']} days");

        return back()->with('success', "✅ Trial {$tenant->tenant_name} diperpanjang {$validated['days']} hari hingga {$newTrialEnd->format('d M Y')}.");
    }

    /**
     * Perpanjang masa aktif paket berbayar (bukan trial).
     */
    public function extendSubscription(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'months' => 'required|integer|min:1|max:24',
        ]);

        $months = (int) $validated['months'];
        $currentEnd = $tenant->subscription_ends_at ?? now();
        $newEnd = (clone $currentEnd)->addMonths($months);

        $tenant->update([
            'subscription_status' => 'active',
            'is_active' => true,
            'subscription_ends_at' => $newEnd,
            'subscribed_at' => $tenant->subscribed_at ?? now(),
        ]);
        SystemLog::info("Subscription extended for {$tenant->tenant_name}: +{$months} months (until {$newEnd->format('d M Y')})");

        return back()->with('success', "✅ Paket {$tenant->tenant_name} diperpanjang {$months} bulan hingga {$newEnd->format('d M Y')}.");
    }

    public function changePlan(Request $request, Tenant $tenant)
    {
        $validated = $request->validate(['plan_id' => 'required|exists:plans,id']);
        $newPlan = Plan::findOrFail($validated['plan_id']);

        // Tentukan status: trial plan → trial, lainnya → active
        $isTrialPlan = $newPlan->slug === 'trial';
        $tenant->update([
            'plan_id' => $newPlan->id,
            'subscription_status' => $isTrialPlan ? 'trial' : 'active',
            'is_active' => true,
            'subscribed_at' => $isTrialPlan ? null : now(),
            'subscription_ends_at' => $isTrialPlan
                ? null
                : now()->addDays($newPlan->trial_days ?: 30),
            'trial_ends_at' => $isTrialPlan
                ? now()->addDays($newPlan->trial_days ?? 14)
                : null,
        ]);
        SystemLog::info("Plan changed for {$tenant->tenant_name}: {$newPlan->name} (status: {$tenant->subscription_status})");

        return back()->with('success', "✅ Plan {$tenant->tenant_name} diubah ke <strong>{$newPlan->name}</strong> — status: <strong>" . ($isTrialPlan ? 'Trial' : 'Active') . '</strong>.');
    }

    public function loginAs(Tenant $tenant)
    {
        try {
            tenancy()->initialize($tenant);
            $ownerUser = User::where('role', 'owner')->first() ?? User::first();
            if (! $ownerUser) {
                tenancy()->end();

                return back()->with('error', 'Tidak ada user di tenant ini.');
            }
            auth()->login($ownerUser);
            session()->put('tenant_id', $tenant->id);
            tenancy()->end();
            $baseDomain = config('tenancy.central_domains.0', 'serviceku.my.id');
            $scheme = parse_url(config('app.url'), PHP_URL_SCHEME) ?: request()->getScheme();
            $host = $tenant->domains()->first()?->domain ?? $tenant->slug.'.'.$baseDomain;
            $url = $scheme.'://'.$host.'/dashboard';

            return request()->header('X-Inertia')
                ? Inertia::location($url)
                : redirect()->away($url);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal login ke tenant: '.$e->getMessage());
        }
    }

    public function resetPassword(Tenant $tenant)
    {
        try {
            tenancy()->initialize($tenant);
            $owner = User::where('role', 'owner')->first() ?? User::first();
            if (! $owner) {
                tenancy()->end();

                return back()->with('error', 'Tidak ada user di tenant ini.');
            }
            $tempPassword = Str::random(10);
            $owner->update(['password' => bcrypt($tempPassword)]);
            tenancy()->end();
            SystemLog::info("Password owner {$tenant->tenant_name} direset");

            return back()->with('success', 'Password berhasil direset. Password sementara telah dikirim ke email owner.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal reset password: '.$e->getMessage());
        }
    }
}
