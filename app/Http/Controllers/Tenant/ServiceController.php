<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\ChecklistTemplate;
use App\Models\Tenant\Customer;
use App\Models\Tenant\CustomField;
use App\Models\Tenant\MasterData;
use App\Models\Tenant\Product;
use App\Models\Tenant\Service;
use App\Models\Tenant\User;
use App\Models\Tenant\WorkOrder;
use App\Services\GoogleDrivePhotoService;
use App\Services\ServiceWorkspaceService;
use App\Services\WorkspaceMetaPresenter;
use App\Workspace\WorkspaceService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    public function __construct(
        protected WorkspaceService $workspaceService,
        protected ServiceWorkspaceService $serviceWorkspaceService,
    ) {}
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
            $query->where(fn ($q) => $q->where('id', 'like', "%{$search}%")->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%")));
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

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
        if (! $user) {
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
            'customFields' => CustomField::where('module', 'service')
                ->where('is_active', true)->orderBy('ordering')->get(),
        ]);
    }

    public function show(Service $service)
    {
        $this->authorize('view', $service);
        $this->ensureServiceBranchAccess($service, auth()->user()?->branch_id);

        // Rich workspace payload — single source of truth for all tab sections
        // (Overview / Timeline / Sparepart / Foto / Invoice / Diagnosa / QC / Garansi).
        $rich = $this->serviceWorkspaceService->build($service);
        $svc = $rich['service'];

        $role = auth()->user()?->role;

        $dataContext = [
            // ── Lean top-level keys used by the Workspace Engine + toolbar handlers ──
            'id' => $service->id,
            'tracking_code' => $service->tracking_code,
            'status' => $service->status,
            'status_label' => $service->getStatusLabel(),
            'device_type' => $service->tipe_unit,
            'imei_sn' => $service->imei_sn,
            'problem_description' => $service->problem_description,
            'total_cost' => (float) $service->total_cost,
            'service_charge' => (float) $service->service_charge,
            'customer' => $service->customer?->only(['id', 'name', 'phone']),
            'technician' => $service->technician?->only(['id', 'name']),
            'sale' => $svc['sale'] ?? null,
            'spareparts' => $svc['spareparts'] ?? [],
            'photos' => $svc['photos'] ?? [],
            'diagnosis' => $svc['diagnosis'] ?? null,
            'worklogs' => $svc['worklogs'] ?? [],
            'qc_checks' => $svc['qc_checks'] ?? [],
            'required_parts' => $svc['required_parts'] ?? [],
            'checklists' => $svc['checklists'] ?? [],
            'checklist_results' => $service->checklistResults?->toArray() ?? [],

            // ── Rich section props (spread by the engine onto each tab component) ──
            'service' => $svc,
            'customerSummary' => $rich['customerSummary'],
            'previousServices' => $rich['previousServices']->toArray(),
            'relatedServices' => $rich['relatedServices']->toArray(),
            'serviceId' => $service->id,
            'availableProducts' => $svc['available_products'] ?? [],
            'serviceCharge' => (float) $service->service_charge,
            'totalCost' => (float) $service->total_cost,
            'paymentStatus' => $service->payment_status,
            'quotations' => $service->quotations?->map(fn ($q) => $q->toArray())->values()->toArray() ?? [],
            'qcChecks' => $svc['qc_checks'] ?? [],
            'canQC' => in_array($role, ['owner', 'admin', 'manager'], true),
            'canRequestPart' => in_array($role, ['owner', 'admin', 'manager', 'technician'], true),
            'canManageParts' => in_array($role, ['owner', 'admin', 'manager'], true),
            'canConsumeParts' => in_array($role, ['owner', 'admin', 'manager', 'cs', 'cashier'], true),
            'canUpload' => true,
            'canDelete' => true,
        ];

        $meta = WorkspaceMetaPresenter::for($service)->toArray();

        $workspace = $this->workspaceService->build('service', $dataContext);
        $workspace['meta'] = array_merge($workspace['meta'] ?? [], $meta);

        return inertia('Enterprise/Workspace/Index', [
            'workspaceConfig' => $workspace,
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
        ActivityLog::log('deleted', 'Menghapus servis #'.$service->id, $service);

        return redirect()->route('services.index')->with('success', 'Servis berhasil dihapus.');
    }

    public function update(Request $request, Service $service)
    {
        $this->authorize('update', $service);
        $this->ensureServiceBranchAccess($service, auth()->user()?->branch_id);

        if ($request->filled('status') && (string) $request->input('status') !== (string) $service->status) {
            return redirect()->route('services.edit', $service)->withErrors([
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
        ActivityLog::log('updated', 'Update servis #'.$service->id, $service);

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

                if (! $isCs) {
                    $innerQuery->orWhereNull('branch_id');
                }
            });
        }

        return $query;
    }

    private function ensureServiceBranchAccess(Service $service, $userBranchId): void
    {
        if (! $userBranchId || ! $service->branch_id) {
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
