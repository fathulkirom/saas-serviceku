<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\Request\CreateRequestAction;
use App\Actions\Request\ForkToServiceOrderAction;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Request;
use App\Models\Tenant\Customer;
use App\Models\Tenant\Device;
use Illuminate\Http\Request as HttpRequest;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Request Controller — ADR-001 Core Entry Point.
 * THIN controller. Business logic delegated to Actions.
 */
class RequestController extends Controller
{
    /**
     * List all requests.
     */
    public function index(HttpRequest $request): Response
    {
        $this->authorize('viewAny', Request::class);

        $requests = Request::with(['customer', 'branch', 'creator'])
            ->when($request->status, fn($q, $s) => $q->where('status', $s))
            ->when($request->search, fn($q, $s) => $q->where('request_number', 'like', "%{$s}%"))
            ->latest()
            ->paginate(25);

        return Inertia::render('Requests/Index', [
            'requests' => $requests,
            'statuses' => ['draft', 'waiting', 'confirmed', 'arrived', 'checking', 'processing', 'completed', 'delivered', 'cancelled'],
        ]);
    }

    /**
     * Show create form.
     */
    public function create(): Response
    {
        $this->authorize('create', Request::class);

        return Inertia::render('Requests/Create', [
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'phone']),
            'branches' => tenant()->branches ?? [],
            'types' => ['service' => 'Servis', 'sales' => 'Penjualan', 'service+sales' => 'Servis + Penjualan', 'warranty' => 'Garansi', 'complaint' => 'Komplain', 'inspection' => 'Inspeksi'],
            'channels' => ['store' => 'Walk In', 'phone' => 'Telepon', 'whatsapp' => 'WhatsApp', 'website' => 'Website'],
        ]);
    }

    /**
     * Store a new request.
     */
    public function store(HttpRequest $httpRequest, CreateRequestAction $action)
    {
        $this->authorize('create', Request::class);

        $validated = $httpRequest->validate([
            'customer_id' => 'nullable|exists:customers,id',
            'branch_id' => 'required|exists:branches,id',
            'type' => 'required|string',
            'channel' => 'required|string',
            'source' => 'nullable|string',
            'priority' => 'nullable|in:normal,high,urgent',
            'customer_note' => 'nullable|string',
            'devices' => 'nullable|array',
            'devices.*.device_id' => 'required_with:devices|exists:devices,id',
            'devices.*.issue_description' => 'nullable|string',
        ]);

        $request = $action->execute($validated);

        return redirect()->route('requests.show', $request->id)
            ->with('success', 'Request ' . $request->request_number . ' berhasil dibuat.');
    }

    /**
     * Show request detail.
     */
    public function show(Request $request): Response
    {
        $this->authorize('view', $request);

        $request->load(['customer', 'branch', 'devices', 'creator', 'history.actor', 'serviceOrders', 'salesOrders']);

        return Inertia::render('Requests/Show', [
            'request' => $request,
        ]);
    }

    /**
     * Fork request to service order.
     */
    public function forkToService(HttpRequest $httpRequest, Request $request, ForkToServiceOrderAction $action)
    {
        $this->authorize('update', $request);

        $validated = $httpRequest->validate([
            'device_id' => 'required|exists:devices,id',
        ]);

        $service = $action->execute($request, $validated['device_id']);

        return redirect()->route('services.show', $service->id)
            ->with('success', 'Service Order berhasil dibuat dari Request ' . $request->request_number);
    }
}
