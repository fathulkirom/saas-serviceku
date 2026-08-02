<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\Customer;
use App\Models\Tenant\ChecklistTemplate;
use App\Models\Tenant\Product;
use App\Models\Tenant\MasterData;
use App\Models\Tenant\User;
use App\Services\GoogleDrivePhotoService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Service::class);
        $user = auth()->user();
        $userBranchId = $user?->branch_id;
        $restrictToTodayCompletedTransactions = $this->shouldRestrictToTodayCompletedTransactions($user);

        $query = Service::with(['customer', 'technician', 'creator', 'branch']);
        $this->applyServiceBranchScope($query, $userBranchId);
        $assignableUsersQuery = $this->buildAssignableUsersQuery($user);

        $statsBaseQuery = fn () => $this->applyServiceBranchScope(Service::query(), $userBranchId);

        if ($request->filled('status')) {
            $query->where('status', $request->status);

            if ($restrictToTodayCompletedTransactions && $this->isCompletedStatus($request->status)) {
                $query->whereDate('updated_at', today());
            }
        } else {
            $query->where('status', '!=', Service::STATUS_SELESAI);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q->where('id', 'like', "%{$search}%")->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%")));
        }
        if ($request->filled('date_from')) $query->whereDate('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->whereDate('created_at', '<=', $request->date_to);

        return inertia('Services/Index', [
            'services' => $query->latest()->paginate(15),
            'users' => $assignableUsersQuery->get(),
            'stats' => $restrictToTodayCompletedTransactions
                ? [
                    'all' => $statsBaseQuery()->where('status', '!=', Service::STATUS_SELESAI)->count(),
                    Service::STATUS_MENUNGGU_ALOKASI => $statsBaseQuery()->where('status', Service::STATUS_MENUNGGU_ALOKASI)->count(),
                    Service::STATUS_DIKERJAKAN => $statsBaseQuery()->whereIn('status', [Service::STATUS_DITERIMA, Service::STATUS_DIKERJAKAN])->count(),
                    Service::STATUS_INDENT => $statsBaseQuery()->where('status', Service::STATUS_INDENT)->count(),
                    Service::STATUS_ONPARTNER => $statsBaseQuery()->where('status', Service::STATUS_ONPARTNER)->count(),
                    Service::STATUS_SIAP_DIAMBIL => $statsBaseQuery()->where('status', Service::STATUS_SIAP_DIAMBIL)->count(),
                    Service::STATUS_SELESAI => $statsBaseQuery()->where('status', Service::STATUS_SELESAI)->whereDate('updated_at', today())->count(),
                    'cancel' => $statsBaseQuery()->whereIn('status', [Service::STATUS_CANCEL, Service::STATUS_VOID, Service::STATUS_CLOSE])->whereDate('updated_at', today())->count(),
                ]
                : [
                    'all' => $statsBaseQuery()->where('status', '!=', Service::STATUS_SELESAI)->count(),
                    Service::STATUS_MENUNGGU_ALOKASI => $statsBaseQuery()->where('status', Service::STATUS_MENUNGGU_ALOKASI)->count(),
                    Service::STATUS_DIKERJAKAN => $statsBaseQuery()->whereIn('status', [Service::STATUS_DITERIMA, Service::STATUS_DIKERJAKAN])->count(),
                    Service::STATUS_INDENT => $statsBaseQuery()->where('status', Service::STATUS_INDENT)->count(),
                    Service::STATUS_ONPARTNER => $statsBaseQuery()->where('status', Service::STATUS_ONPARTNER)->count(),
                    Service::STATUS_SIAP_DIAMBIL => $statsBaseQuery()->where('status', Service::STATUS_SIAP_DIAMBIL)->count(),
                    Service::STATUS_SELESAI => $statsBaseQuery()->where('status', Service::STATUS_SELESAI)->count(),
                    'cancel' => $statsBaseQuery()->whereIn('status', [Service::STATUS_CANCEL, Service::STATUS_VOID, Service::STATUS_CLOSE])->count(),
                ],
            'filters' => $request->only(['status', 'search', 'date_from', 'date_to']),
        ]);
    }

    private function shouldRestrictToTodayCompletedTransactions($user): bool
    {
        if (!$user) {
            return false;
        }

        if (in_array($user->role, ['cs', 'cashier'], true)) {
            return true;
        }

        if ($user->role !== 'custom') {
            return false;
        }

        $customRole = strtolower(trim((string) ($user->custom_role ?? '')));
        $customRole = preg_replace('/[_\-\s]+/', ' ', $customRole);

        return in_array($customRole, ['admin harian'], true);
    }

    private function isCompletedStatus(?string $status): bool
    {
        return in_array((string) $status, [Service::STATUS_SELESAI, Service::STATUS_CLOSE], true);
    }

    public function create()
    {
        $this->authorize('create', Service::class);
        $userBranchId = auth()->user()?->branch_id;

        $driveService = new GoogleDrivePhotoService(tenancy()->tenant->id);

        $customersQuery = Customer::query()->orderBy('name');
        $productsQuery = Product::query()->where('stock_quantity', '>', 0);

        $this->applyBranchOrGlobalScope($customersQuery, $userBranchId);
        $this->applyBranchOrGlobalScope($productsQuery, $userBranchId);

        return inertia('Services/Create', [
            'customers' => $customersQuery->get(),
            'templates' => ChecklistTemplate::where('type', 'masuk')->where('is_active', true)->with('items')->get(),
            'products' => $productsQuery->get(),
            'deviceCategories' => MasterData::getByCategory('device_category'),
            'brands' => MasterData::getByCategory('brand'),
            'arrivalMethods' => MasterData::getByCategory('arrival_method'),
            'equipment' => MasterData::getByCategory('equipment'),
            'driveConnected' => $driveService->isConnected(),
            'customFields' => \App\Models\Tenant\CustomField::where('module', 'service')
                ->where('is_active', true)->orderBy('ordering')->get(),
        ]);
    }

    public function show(Service $service)
    {
        $this->authorize('view', $service);
        $this->ensureServiceBranchAccess($service, auth()->user()?->branch_id);

        $userBranchId = auth()->user()?->branch_id;

        $productsQuery = Product::query()->where('stock_quantity', '>', 0);
        $usersQuery = $this->buildAssignableUsersQuery(auth()->user());
        $previousServicesQuery = Service::with('customer')
            ->where('status', Service::STATUS_SELESAI)
            ->where('payment_status', 'paid')
            ->where('id', '!=', $service->id)
            ->latest()
            ->take(10);

        $this->applyBranchOrGlobalScope($productsQuery, $userBranchId);
        $this->applyServiceBranchScope($previousServicesQuery, $userBranchId);

        // Sprint 7.5B — Unified Service Workspace: eager-load EVERYTHING
        $service->load([
            'customer.tags', 'customer.devices.healthHistory',
            'technician', 'creator', 'branch',
            'checklists.checklistTemplate.items', 'checklistResults.item',
            'spareparts.product', 'diagnosis', 'quotations',
            'requiredParts.product', 'qcChecks',
            'intakeSnapshot', 'delivery', 'warranty',
            'photos.uploader', 'workOrders.technician', 'workOrders.worklogs',
            'indent', 'sale.items', 'parentService',
        ]);

        // Customer summary for right sidebar
        $customerSummary = null;
        if ($service->customer) {
            $c = $service->customer;
            $customerSummary = [
                'id' => $c->id, 'name' => $c->name, 'phone' => $c->phone,
                'is_member' => $c->is_member, 'customer_code' => $c->customer_code,
                'service_count' => $c->serviceCount(), 'total_spending' => $c->totalSpending(),
                'device_count' => $c->devices->count(), 'last_visit' => $c->services()->latest()->first()?->created_at?->format('d M Y'),
                'risk' => $c->riskIndicator(), 'tags' => $c->tags->pluck('name'),
            ];
        }

        // Technician summary
        $techSummary = null;
        if ($service->technician) {
            $t = $service->technician;
            $techSummary = [
                'id' => $t->id, 'name' => $t->name,
                'active_work_orders' => \App\Models\Tenant\WorkOrder::forTechnician($t->id)->active()->count(),
            ];
        }

        return inertia('Services/Workspace', [
            'service' => $service,
            'customerSummary' => $customerSummary,
            'techSummary' => $techSummary,
            'templatesKeluar' => ChecklistTemplate::where('type', 'keluar')->where('is_active', true)->with('items')->get(),
            'templatesMasuk' => ChecklistTemplate::where('type', 'masuk')->where('is_active', true)->with('items')->get(),
            'products' => $productsQuery->get(),
            'users' => $usersQuery->get(),
            'previousServices' => $service->customer_id
                ? $previousServicesQuery->where('customer_id', $service->customer_id)->get()
                : [],
            'driveConnected' => (new GoogleDrivePhotoService(tenancy()->tenant->id))->isConnected(),
        ]);
    }

    public function edit(Service $service)
    {
        $this->authorize('update', $service);
        $this->ensureServiceBranchAccess($service, auth()->user()?->branch_id);

        $userBranchId = auth()->user()?->branch_id;

        $customersQuery = Customer::query()->orderBy('name');
        $productsQuery = Product::query()->where('stock_quantity', '>', 0);

        $this->applyBranchOrGlobalScope($customersQuery, $userBranchId);
        $this->applyBranchOrGlobalScope($productsQuery, $userBranchId);

        $service->load(['customer', 'checklists.checklistTemplate.items', 'spareparts.product']);
        $driveService = new GoogleDrivePhotoService(tenancy()->tenant->id);
        return inertia('Services/Edit', [
            'service' => $service,
            'customers' => $customersQuery->get(),
            'templates' => ChecklistTemplate::where('type', 'masuk')->where('is_active', true)->with('items')->get(),
            'products' => $productsQuery->get(),
            'deviceCategories' => MasterData::getByCategory('device_category'),
            'brands' => MasterData::getByCategory('brand'),
            'arrivalMethods' => MasterData::getByCategory('arrival_method'),
            'equipment' => MasterData::getByCategory('equipment'),
            'driveConnected' => $driveService->isConnected(),
        ]);
    }

    public function destroy(Service $service)
    {
        $this->authorize('delete', $service);
        $this->ensureServiceBranchAccess($service, auth()->user()?->branch_id);

        $service->delete();
        \App\Models\Tenant\ActivityLog::log('deleted', 'Menghapus servis #' . $service->id, $service);
        return redirect()->route('services.index')->with('success', 'Servis berhasil dihapus.');
    }

    public function update(Request $request, Service $service)
    {
        $this->authorize('update', $service);
        $this->ensureServiceBranchAccess($service, auth()->user()?->branch_id);

        if ($request->filled('status') && (string) $request->input('status') !== (string) $service->status) {
            throw ValidationException::withMessages([
                'status' => 'Perubahan status harus melalui aksi workflow resmi pada halaman detail servis.',
            ]);
        }

        $validated = $request->validate([
            'problem_description' => 'nullable|string', 'condition_note' => 'nullable|string',
            'service_charge' => 'nullable|numeric|min:0', 'tipe_unit' => 'nullable|string|max:100',
            'imei_sn' => 'nullable|string|max:100', 'sandi_pola' => 'nullable|string|max:50',
            'posisi_unit' => 'nullable|in:di_toko,dibawa_pelanggan', 'kelengkapan' => 'nullable|array',
        ]);

        $service->update($validated);
        \App\Models\Tenant\ActivityLog::log('updated', 'Update servis #' . $service->id, $service);

        return back()->with('success', 'Servis berhasil diperbarui.');
    }

    private function applyServiceBranchScope($query, $userBranchId)
    {
        if ($userBranchId) {
            $query->where('branch_id', $userBranchId);
        }

        return $query;
    }

    private function applyBranchOrGlobalScope($query, $userBranchId)
    {
        if ($userBranchId) {
            $query->where(function ($innerQuery) use ($userBranchId) {
                $innerQuery->where('branch_id', $userBranchId)
                    ->orWhereNull('branch_id');
            });
        }

        return $query;
    }

    private function buildAssignableUsersQuery($user)
    {
        $userBranchId = $user?->branch_id;
        $isCs = $user && $user->isCs();

        $query = User::query()
            ->select('id', 'name', 'role', 'active', 'branch_id')
            ->where('active', true)
            ->whereIn('role', ['technician', 'owner'])
            ->orderBy('name');

        if ($userBranchId) {
            $query->where(function ($innerQuery) use ($userBranchId, $isCs) {
                $innerQuery->where('branch_id', $userBranchId);

                if (!$isCs) {
                    $innerQuery->orWhereNull('branch_id');
                }
            });
        }

        return $query;
    }

    private function ensureServiceBranchAccess(Service $service, $userBranchId): void
    {
        if (!$userBranchId || !$service->branch_id) {
            return;
        }

        if ((string) $service->branch_id === (string) $userBranchId) {
            return;
        }

        throw ValidationException::withMessages([
            'service' => 'Servis tidak berada pada cabang aktif Anda.',
        ]);
    }
}
