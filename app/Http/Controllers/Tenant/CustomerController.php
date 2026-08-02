<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use App\Models\Tenant\CustomerComplaint;
use App\Models\Tenant\CustomerInteraction;
use App\Models\Tenant\CustomerNote;
use App\Models\Tenant\CustomerSegment;
use App\Models\Tenant\CustomerTag;
use App\Models\Tenant\Device;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Customer::class);
        $userBranchId = auth()->user()?->branch_id;

        $query = Customer::with('branch')->withCount('services', 'sales');

        // Branch isolation
        if ($userBranchId) {
            $query->where(fn($q) => $q->where('branch_id', $userBranchId)->orWhereNull('branch_id'));
        }

        // Sprint 7.3B: Server-side search (name, phone, email)
        if ($search = $request->query('search')) {
            $query->where(fn($q) => $q->where('name', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('card_number', 'like', "%{$search}%"));
        }

        // Sprint 7.3B: IMEI search (cross-table)
        if ($imei = $request->query('imei')) {
            $query->whereHas('devices', fn($q) => $q->where('imei', 'like', "%{$imei}%"));
        }

        // Sprint 7.3B: Filters
        if ($request->query('is_member') !== null) {
            $query->where('is_member', $request->boolean('is_member'));
        }
        if ($tag = $request->query('tag')) {
            $query->whereHas('tags', fn($q) => $q->where('name', $tag));
        }

        // Sprint 7.3B: Sort
        $sort = $request->query('sort', 'latest');
        match ($sort) {
            'spending'  => $query->withSum(['sales' => fn($q) => $q->where('status', 'paid')], 'total')->orderByDesc('sales_sum_total'),
            'services'  => $query->orderByDesc('services_count'),
            'last_visit'=> $query->orderByDesc('updated_at'),
            'name'      => $query->orderBy('name'),
            default     => $query->latest(),
        };

        $customers = $query->paginate($request->query('per_page', 20))->withQueryString();
        $tags = \App\Models\Tenant\CustomerTag::orderBy('name')->get();

        return inertia('Customers/Index', [
            'customers' => $customers,
            'tags' => $tags,
            'filters' => $request->only(['search', 'imei', 'is_member', 'tag', 'sort']),
        ]);
    }

    public function create()
    {
        $this->authorize('create', Customer::class);
        return inertia('Customers/Create', [
            'customFields' => \App\Models\Tenant\CustomField::where('module', 'customer')
                ->where('is_active', true)->orderBy('ordering')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Customer::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:255',
            'address' => 'nullable|string',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;

        $customer = Customer::create($validated);

        // Simpan custom field values
        $customer->saveCustomFieldValues($request->all());

        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil ditambahkan.');
    }

    public function storeApi(Request $request)
    {
        $this->authorize('create', Customer::class);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:255',
            'address' => 'nullable|string',
        ]);

        $validated['branch_id'] = auth()->user()->branch_id;
        $customer = Customer::create($validated);

        return response()->json([
            'success' => true,
            'customer' => $customer,
            'message' => 'Pelanggan berhasil ditambahkan.',
        ]);
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);
        $this->ensureCustomerBranchAccess($customer);

        // Sprint 7.3B — Customer Relationship Core
        $customer->load([
            'services' => fn($q) => $q->latest()->take(20),
            'sales' => fn($q) => $q->latest()->take(20),
            'devices' => fn($q) => $q->latest(),
            'requests' => fn($q) => $q->latest()->take(20),
            'interactions' => fn($q) => $q->latest()->take(30),
            'communications' => fn($q) => $q->latest()->take(30),
            'notes' => fn($q) => $q->latest()->take(30),
            'complaints' => fn($q) => $q->with('handler')->latest()->take(20),
            'tags',
        ]);

        // Build enriched timeline from all sources
        $timeline = collect()
            ->merge($customer->notes->map(fn($n) => [
                'type' => 'note', 'icon' => '📝', 'color' => 'border-gray-400',
                'id' => $n->id, 'title' => $n->title ?? $n->note, 'description' => $n->title ? $n->note : null,
                'subtitle' => CustomerNote::types()[$n->type] ?? $n->type,
                'status' => $n->priority, 'created_at' => $n->created_at,
            ]))
            ->merge($customer->complaints->map(fn($c) => [
                'type' => 'complaint', 'icon' => '🚨', 'color' => 'border-red-400',
                'id' => $c->id, 'title' => $c->title, 'description' => $c->description,
                'subtitle' => CustomerComplaint::statuses()[$c->status] ?? $c->status,
                'status' => $c->status, 'created_at' => $c->created_at,
            ]))
            ->merge($customer->communications->map(fn($c) => [
                'type' => 'communication', 'icon' => $c->type === 'whatsapp' ? '💬' : '📧', 'color' => 'border-teal-400',
                'id' => $c->id, 'title' => $c->message, 'description' => "Ke: {$c->recipient}",
                'subtitle' => ($c->type === 'whatsapp' ? 'WhatsApp' : 'Email') . ' · ' . $c->status,
                'status' => $c->status, 'created_at' => $c->created_at,
            ]))
            ->merge($customer->interactions->map(fn($i) => [
                'type' => 'interaction', 'icon' => '💬', 'color' => 'border-purple-400',
                'id' => $i->id, 'title' => $i->title, 'description' => $i->description,
                'subtitle' => CustomerInteraction::types()[$i->type] ?? $i->type,
                'status' => null, 'created_at' => $i->created_at,
            ]))
            ->merge($customer->requests->map(fn($r) => [
                'type' => 'request', 'icon' => '📋', 'color' => 'border-blue-400',
                'id' => $r->id, 'title' => "Request #{$r->request_number}",
                'description' => $r->customer_note, 'subtitle' => null,
                'status' => $r->status, 'created_at' => $r->created_at,
            ]))
            ->merge($customer->services->map(fn($s) => [
                'type' => 'service', 'icon' => '🔧', 'color' => 'border-amber-400',
                'id' => $s->id, 'title' => "Servis #{$s->id}",
                'description' => $s->problem_description, 'subtitle' => null,
                'status' => $s->status, 'created_at' => $s->created_at,
            ]))
            ->merge($customer->sales->map(fn($s) => [
                'type' => 'sale', 'icon' => '🛒', 'color' => 'border-green-400',
                'id' => $s->id, 'title' => "Penjualan #{$s->invoice_number}",
                'description' => null, 'subtitle' => 'Rp ' . number_format($s->total, 0, ',', '.'),
                'status' => $s->status, 'created_at' => $s->created_at,
            ]))
            ->sortByDesc('created_at')
            ->take(50)
            ->values()
            ->toArray();

        // Determine segments
        $segmentIds = \App\Models\Tenant\CustomerSegment::where('is_active', true)->get()
            ->filter(fn($seg) => in_array($customer->id, $seg->getCustomerIds()))
            ->pluck('name');

        return inertia('Customers/Show', [
            'customer' => $customer,
            'timeline' => $timeline,
            'devices' => $customer->devices,
            'interactions' => $customer->interactions,
            'communications' => $customer->communications,
            'notes' => $customer->notes,
            'complaints' => $customer->complaints,
            'risk' => $customer->riskIndicator(),
            'templates' => \App\Models\Tenant\CustomerMessageTemplate::where('is_active', true)->orderBy('name')->get(),
            'allTags' => \App\Models\Tenant\CustomerTag::orderBy('name')->get(),
            'segments' => $segmentIds,
            'noteTypes' => CustomerNote::types(),
            'complaintStatuses' => CustomerComplaint::statuses(),
            'stats' => [
                'total_spending' => $customer->totalSpending(),
                'service_count' => $customer->serviceCount(),
                'sales_count' => $customer->sales->count(),
                'device_count' => $customer->devices->count(),
                'interaction_count' => $customer->interactions->count(),
                'note_count' => $customer->notes->count(),
                'complaint_count' => $customer->complaints->count(),
                'open_complaints' => $customer->complaints()->open()->count(),
                'average_ticket' => $customer->averageTicket(),
                'repair_frequency' => $customer->repairFrequency(),
                'last_visit' => $customer->services()->latest()->first()?->created_at?->format('d M Y') ?? '-',
                'customer_since' => $customer->created_at->format('d M Y'),
                'recent_services' => $customer->services,
                'recent_sales' => $customer->sales,
            ],
        ]);
    }

    public function update(Request $request, Customer $customer)
    {
        $this->authorize('update', $customer);
        $this->ensureCustomerBranchAccess($customer);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|string|email|max:255',
            'address' => 'nullable|string',
            'is_member' => 'nullable|boolean',
        ]);

        $customer->update($validated);

        // Simpan custom field values
        $customer->saveCustomFieldValues($request->all());

        return back()->with('success', 'Pelanggan berhasil diperbarui.');
    }

    public function registerMember(Customer $customer)
    {
        $this->authorize('update', $customer);
        $this->ensureCustomerBranchAccess($customer);
        if (!$customer->card_number) {
            $year = date('y');
            $customer->update([
                'is_member' => true,
                'card_number' => 'ACS' . $year . str_pad((string)$customer->id, 6, '0', STR_PAD_LEFT),
            ]);
        } else {
            $customer->update(['is_member' => true]);
        }

        return back()->with('success', 'Kartu member ' . $customer->card_number . ' berhasil diaktifkan.');
    }

    public function destroy(Customer $customer)
    {
        $this->authorize('delete', $customer);
        $this->ensureCustomerBranchAccess($customer);
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Pelanggan berhasil dihapus.');
    }

    // Sprint 7.3B — Customer Interactions
    public function storeInteraction(Request $request, Customer $customer)
    {
        $this->authorize('update', $customer);
        $validated = $request->validate([
            'type' => 'required|string|in:note,call,whatsapp,complaint,follow_up,reminder,visit,internal_note',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $customer->interactions()->create([
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'actor_id' => auth()->id(),
            'branch_id' => auth()->user()?->branch_id,
        ]);

        return back()->with('success', 'Interaksi berhasil dicatat.');
    }

    // Sprint 7.3D — Customer Notes
    public function storeNote(Request $request, Customer $customer)
    {
        $this->authorize('update', $customer);
        $data = $request->validate([
            'type' => 'required|in:general,preference,warning,complaint',
            'title' => 'nullable|string|max:255',
            'note' => 'required|string',
            'priority' => 'required|in:low,medium,high',
        ]);
        $note = $customer->notes()->create($data + ['created_by' => auth()->id()]);
        event(new \App\Events\Entity\CustomerNoteCreated($note));
        return back()->with('success', 'Catatan ditambahkan.');
    }

    // Sprint 7.3D — Customer Complaints
    public function storeComplaint(Request $request, Customer $customer)
    {
        $this->authorize('update', $customer);
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high',
            'service_id' => 'nullable|exists:services,id',
            'request_id' => 'nullable|exists:requests,id',
        ]);
        $complaint = $customer->complaints()->create($data + ['status' => 'open']);
        event(new \App\Events\Entity\CustomerComplaintCreated($complaint));
        return back()->with('success', 'Komplain dicatat.');
    }

    public function resolveComplaint(Request $request, Customer $customer, CustomerComplaint $complaint)
    {
        $this->authorize('update', $customer);
        $data = $request->validate(['resolution' => 'required|string']);
        $complaint->resolve($data['resolution']);
        event(new \App\Events\Entity\CustomerComplaintResolved($complaint));
        return back()->with('success', 'Komplain diselesaikan.');
    }

    // Sprint 7.3D — Global Customer Search
    public function search(Request $request)
    {
        $this->authorize('viewAny', Customer::class);
        $term = $request->query('q', '');
        if (strlen($term) < 2) return response()->json(['results' => []]);

        $customers = Customer::with('devices')
            ->where(fn($q) => $q->where('name', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('card_number', 'like', "%{$term}%")
                ->orWhereHas('devices', fn($d) => $d->where('imei', 'like', "%{$term}%")->orWhere('serial_number', 'like', "%{$term}%")))
            ->limit(10)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'phone' => $c->phone,
                'email' => $c->email,
                'is_member' => $c->is_member,
                'risk' => $c->riskIndicator(),
                'devices' => $c->devices->map(fn($d) => ['brand' => $d->brand, 'model' => $d->model, 'imei' => $d->imei]),
                'last_service' => $c->services()->latest()->first()?->problem_description,
                'url' => route('customers.show', $c->id),
            ]);

        return response()->json(['results' => $customers]);
    }

    // Sprint 7.3E — Duplicate detection
    public function checkDuplicates(Request $request)
    {
        $this->authorize('viewAny', Customer::class);
        $duplicates = Customer::detectDuplicates(
            $request->query('name', ''),
            $request->query('phone', ''),
            $request->query('email', ''),
            $request->query('imei', ''),
        );

        if (!empty($duplicates)) {
            event(new \App\Events\Entity\CustomerDuplicateDetected($duplicates, $request->only(['name', 'phone', 'email'])));
        }

        return response()->json(['duplicates' => $duplicates]);
    }

    // Sprint 7.3E — Merge customer
    public function merge(Customer $customer, Customer $other)
    {
        $this->authorize('update', $customer);
        $customer->merge($other);
        return redirect()->route('customers.show', $customer)->with('success', 'Customer berhasil digabungkan.');
    }

    // Sprint 7.3E — Record device health
    public function recordDeviceHealth(Request $request, Device $device)
    {
        $this->authorize('update', $device->customer);
        $data = $request->validate([
            'metric' => 'required|string',
            'value' => 'required|string',
            'unit' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);
        $data['recorded_by'] = auth()->id();
        $device->healthHistory()->create($data);
        return back()->with('success', 'Data kesehatan perangkat tercatat.');
    }

    // Sprint 7.3B — Customer Tags
    public function attachTag(Request $request, Customer $customer)
    {
        $this->authorize('update', $customer);
        $tagId = $request->validate(['tag_id' => 'required|exists:customer_tags,id'])['tag_id'];
        $customer->tags()->syncWithoutDetaching([$tagId]);
        return back()->with('success', 'Tag ditambahkan.');
    }

    public function detachTag(Customer $customer, CustomerTag $tag)
    {
        $this->authorize('update', $customer);
        $customer->tags()->detach($tag->id);
        return back()->with('success', 'Tag dihapus.');
    }

    private function ensureCustomerBranchAccess(Customer $customer): void
    {
        $userBranchId = auth()->user()?->branch_id;

        if (!$userBranchId || !$customer->branch_id) {
            return;
        }

        if ((string) $customer->branch_id !== (string) $userBranchId) {
            throw ValidationException::withMessages([
                'customer' => 'Pelanggan tidak berada pada cabang aktif Anda.',
            ]);
        }
    }
}
