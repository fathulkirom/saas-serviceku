<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\Tenant\Service;
use App\Models\Tenant\Product;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Customer;
use App\Models\Tenant\CashRegister;
use App\Models\Tenant\TenantSetting;
use App\Models\Tenant\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login');
        }

        return match ($user->role) {
            'technician' => $this->technicianDashboard($user),
            'cs'         => $this->csDashboard(),
            'cashier'    => $this->cashierDashboard(),
            'courier'    => $this->courierDashboard(),
            default      => $this->ownerDashboard(),
        };
    }

    // ========================================================================
    //  OWNER / ADMIN / MANAGER / HEAD STORE
    //  Dashboard bisnis penuh: revenue, stok, servis, laporan
    // ========================================================================
    private function ownerDashboard()
    {
        $tenantId = tenancy()->tenant->id;
        $user = auth()->user();
        $branchId = $user?->branch_id;

        // Sprint 7.5F Final — NO auto-redirect.
        // Welcome Card shown on Dashboard; Owner chooses "Continue Setup" or "Later".

        $stats = Cache::remember("dashboard_stats_{$tenantId}_{$branchId}", 300, function () use ($branchId) {
            return [
                'services_today' => $this->scopeBranch(Service::class, $branchId)->whereDate('created_at', today())->count(),
                'revenue_today'  => $this->scopeBranch(Sale::class, $branchId)->whereDate('created_at', today())->sum('total'),
                'low_stock'       => $this->scopeBranchOrGlobal(Product::class, $branchId)->whereColumn('stock_quantity', '<=', 'min_stock')->count(),
                'active_services' => $this->scopeBranch(Service::class, $branchId)->active()->count(),
            ];
        });

        $recentServices = $this->scopeBranch(Service::class, $branchId)->with(['customer', 'technician'])
            ->latest()
            ->take(5)
            ->get()
            ->map(function ($service) {
                $service->created_at_formatted = $service->created_at->format('d/m/Y H:i');
                return $service;
            });

        // Onboarding redirect if new tenant
        $storeName = TenantSetting::getValue('store_name', '');
        $hasNoData = $this->scopeBranchOrGlobal(Customer::class, $branchId)->count() === 0
            && $this->scopeBranch(Service::class, $branchId)->count() === 0;
        if ($hasNoData && !$storeName && auth()->user()->isOwner()) {
            return redirect()->route('onboarding.index');
        }

        // Hitung breakdown status untuk sidebar
        $statusCounts = $this->scopeBranch(Service::class, $branchId)->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return inertia('Dashboard', [
            'stats'          => $stats,
            'recentServices' => $recentServices,
            'statusCounts'   => $statusCounts,
            'setupSummary'   => $this->getSetupSummary(),
        ]);
    }

    // ========================================================================
    //  TEKNISI
    //  Hanya melihat servis yang ditugaskan ke dirinya
    // ========================================================================
    private function technicianDashboard(User $user)
    {
        $myServices = Service::with(['customer', 'technician'])
            ->where('technician_id', $user->id)
            ->when($user->branch_id, fn($q) => $q->where('branch_id', $user->branch_id))
            ->whereIn('status', [
                Service::STATUS_MENUNGGU_ALOKASI,
                Service::STATUS_DITERIMA,
                Service::STATUS_DIKERJAKAN,
                Service::STATUS_KONFIRMASI_PELANGGAN,
                Service::STATUS_KONFIRMASI_INTERNAL,
                Service::STATUS_INDENT,
                Service::STATUS_ONPARTNER,
                Service::STATUS_SIAP_DIAMBIL,
            ])
            ->latest()
            ->take(10)
            ->get();

        $branchId = $user->branch_id;

        $stats = [
            'assigned_to_me' => Service::where('technician_id', $user->id)
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereIn('status', [
                    Service::STATUS_MENUNGGU_ALOKASI, Service::STATUS_DITERIMA,
                    Service::STATUS_DIKERJAKAN, Service::STATUS_INDENT,
                ])->count(),
            'waiting' => Service::where('technician_id', $user->id)
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->where('status', Service::STATUS_MENUNGGU_ALOKASI)->count(),
            'in_progress' => Service::where('technician_id', $user->id)
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->whereIn('status', [Service::STATUS_DITERIMA, Service::STATUS_DIKERJAKAN])->count(),
            'completed_today' => Service::where('technician_id', $user->id)
                ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
                ->where('status', Service::STATUS_SELESAI)
                ->whereDate('updated_at', today())->count(),
        ];

        return inertia('TechnicianDashboard', [
            'stats'      => $stats,
            'myServices' => $myServices,
        ]);
    }

    // ========================================================================
    //  CS (CUSTOMER SERVICE)
    //  Fokus: penerimaan servis, pelanggan, alokasi teknisi
    // ========================================================================
    private function csDashboard()
    {
        $user = auth()->user();
        $branchId = $user?->branch_id;

        $stats = [
            'services_today'     => $this->scopeBranch(Service::class, $branchId)->whereDate('created_at', today())->count(),
            'new_customers_today'=> $this->scopeBranchOrGlobal(Customer::class, $branchId)->whereDate('created_at', today())->count(),
            'pending_allocation' => $this->scopeBranch(Service::class, $branchId)->where('status', Service::STATUS_MENUNGGU_ALOKASI)->count(),
            'active_services'    => $this->scopeBranch(Service::class, $branchId)->active()->count(),
        ];

        $recentServices = $this->scopeBranch(Service::class, $branchId)->with(['customer'])
            ->whereDate('created_at', today())
            ->latest()
            ->take(8)
            ->get()
            ->map(function (Service $service) {
                return [
                    'id' => $service->id,
                    'customer_name' => $service->customer?->name ?? 'Umum',
                    'device_type' => $service->tipe_unit ?: '-',
                    'status' => $service->status,
                ];
            })
            ->values();

        $pendingServices = $this->scopeBranch(Service::class, $branchId)->with(['customer'])
            ->where('status', Service::STATUS_MENUNGGU_ALOKASI)
            ->latest()
            ->take(8)
            ->get()
            ->map(function (Service $service) {
                return [
                    'id' => $service->id,
                    'customer_name' => $service->customer?->name ?? 'Umum',
                    'device_type' => $service->tipe_unit ?: '-',
                    'status' => $service->status,
                ];
            })
            ->values();

        return inertia('CsDashboard', [
            'stats'          => $stats,
            'recentServices' => $recentServices,
            'unallocatedServices' => $pendingServices,
            // Backward-compatible alias for older frontend builds.
            'pendingServices' => $pendingServices,
        ]);
    }

    // ========================================================================
    //  KASIR
    //  Fokus: penjualan, pembayaran, kas register
    // ========================================================================
    private function cashierDashboard()
    {
        $user = auth()->user();
        $branchId = $user?->branch_id;

        $stats = [
            'revenue_today'  => $this->scopeBranch(Sale::class, $branchId)->whereDate('created_at', today())->sum('total'),
            'paid_sales'     => $this->scopeBranch(Sale::class, $branchId)->whereDate('created_at', today())->paid()->count(),
            'draft_sales'    => $this->scopeBranch(Sale::class, $branchId)->draft()->count(),
            'ready_for_pickup'=> $this->scopeBranch(Service::class, $branchId)->readyForPayment()->count(),
        ];

        $recentSales = $this->scopeBranch(Sale::class, $branchId)->with(['customer'])
            ->whereDate('created_at', today())
            ->latest()
            ->take(8)
            ->get();

        $pickupServices = $this->scopeBranch(Service::class, $branchId)->with(['customer'])
            ->readyForPayment()
            ->latest()
            ->take(8)
            ->get();

        $cashRegister = CashRegister::where('user_id', auth()->id())
            ->where('status', 'open')
            ->latest()
            ->first();

        return inertia('CashierDashboard', [
            'stats'          => $stats,
            'recentSales'    => $recentSales,
            'pickupServices' => $pickupServices,
            'cashRegister'   => $cashRegister,
            // PILOT-READY-01: CashierDashboard.vue reads these names.
            'readyServices'  => $pickupServices,
            'cashRegisterOpen' => (bool) $cashRegister,
        ]);
    }

    // ========================================================================
    //  KURIR
    //  Fokus: pengambilan, pengiriman, servis siap diambil
    // ========================================================================
    private function courierDashboard()
    {
        $user = auth()->user();
        $branchId = $user?->branch_id;

        $stats = [
            'ready_for_pickup' => $this->scopeBranch(Service::class, $branchId)->readyForPayment()->count(),
            'in_progress'      => $this->scopeBranch(Service::class, $branchId)->active()->count(),
            'completed_today'  => $this->scopeBranch(Service::class, $branchId)->where('status', Service::STATUS_SELESAI)
                                    ->whereDate('updated_at', today())->count(),
            'waiting_parts'    => $this->scopeBranch(Service::class, $branchId)->where('status', Service::STATUS_INDENT)->count(),
        ];

        $pickupServices = $this->scopeBranch(Service::class, $branchId)->with(['customer'])
            ->readyForPayment()
            ->latest()
            ->take(10)
            ->get();

        $completedServices = $this->scopeBranch(Service::class, $branchId)->with(['customer', 'technician'])
            ->where('status', Service::STATUS_SELESAI)
            ->whereDate('updated_at', today())
            ->latest()
            ->take(10)
            ->get();

        return inertia('CourierDashboard', [
            'stats'             => $stats,
            'pickupServices'    => $pickupServices,
            'completedServices' => $completedServices,
        ]);
    }

    // ========================================================================
    //  HELPERS
    // ========================================================================
    private function scopeBranch(string $model, $branchId)
    {
        $query = $model::query();

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        return $query;
    }

    private function scopeBranchOrGlobal(string $model, $branchId)
    {
        $query = $model::query();

        if ($branchId) {
            $query->where(function ($inner) use ($branchId) {
                $inner->where('branch_id', $branchId)
                    ->orWhereNull('branch_id');
            });
        }

        return $query;
    }

    /**
     * Sprint 7.5F Final — Setup summary for Dashboard Welcome Card.
     *
     * Visibility: permission-based via existing PermissionEngine.
     *   Default: Owner, Manager
     *   Optional: Admin (TenantSetting: setup_card_show_admin = true)
     *   Never: CS, Technician, Cashier, Warehouse (head_store), Courier
     *
     * Card shown when: setup not fully complete OR health has warnings.
     * Owner chooses "Continue Setup" or "Later" — NO auto-redirect.
     */
    private function getSetupSummary(): ?array
    {
        $user = auth()->user();
        if (!$user) return null;

        if (!$this->canViewSetupCard($user)) return null;

        $summary = app(\App\Http\Controllers\Tenant\SetupController::class)->summary();

        // Hidden explicitly by user
        if ($summary['isHidden']) return null;

        // Fully complete AND healthy → no card needed
        if ($summary['configComplete'] && $summary['healthStatus'] === 'ready') return null;

        return $summary;
    }

    /**
     * Check if user is allowed to see the Setup Progress Card.
     * Uses role-based check + PermissionEngine fallback for custom roles.
     */
    private function canViewSetupCard($user): bool
    {
        $role = $user->role;

        // ── Never show to operational roles ──
        $blocked = ['cs', 'technician', 'cashier', 'head_store', 'courier'];
        if (in_array($role, $blocked)) return false;

        // ── Default: Owner, Manager always see it ──
        if (in_array($role, ['owner', 'manager'])) return true;

        // ── Admin: configurable via TenantSetting ──
        if ($role === 'admin') {
            return \App\Models\Tenant\TenantSetting::getValue('setup_card_show_admin', 'false') === 'true';
        }

        // ── Custom roles: check via PermissionEngine ──
        return $user->canViaPermission('manage_settings');
    }
}
