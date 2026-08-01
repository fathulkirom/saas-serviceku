<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ChecklistTemplate;
use App\Models\Tenant\PartnerTeknisi;
use App\Models\Tenant\PickupDelivery;
use App\Models\Tenant\Indent;
use App\Models\Tenant\Service;
use App\Models\Tenant\Branch;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceToolsController extends Controller
{
    public function index(Request $request): Response
    {
        $tab = $request->get('tab', 'ceklis');

        return Inertia::render('ServisTools/Index', [
            'activeTab' => $tab,

            'templates' => fn() => ChecklistTemplate::with('items')->latest()->paginate(15),

            'partners' => fn() => PartnerTeknisi::where('branch_id', auth()->user()->branch_id)
                ->orderBy('name')
                ->paginate(20),

            'pickups' => fn() => PickupDelivery::with(['service.customer', 'pic', 'creator'])
                ->where('branch_id', auth()->user()->branch_id)
                ->latest()
                ->paginate(20),

            'indents' => fn() => Indent::with(['customer', 'branch', 'service'])
                ->where('branch_id', auth()->user()->branch_id)
                ->when($request->indent_status, fn($q) => $q->where('status', $request->indent_status))
                ->when($request->indent_search, fn($q) => $q->where(function ($q) use ($request) {
                    $q->whereHas('customer', fn($c) => $c->where('name', 'like', "%{$request->indent_search}%"))
                      ->orWhere('product_name', 'like', "%{$request->indent_search}%");
                }))
                ->latest()
                ->paginate(15),

            'indentFilters' => fn() => $request->only(['indent_status', 'indent_search']),

            'transferServices' => fn() => Service::with(['customer', 'branch'])
                ->where('branch_id', auth()->user()->branch_id)
                ->whereIn('status', [Service::STATUS_SIAP_DIAMBIL, Service::STATUS_SELESAI])
                ->latest()
                ->get(),

            'transferBranches' => fn() => Branch::where('id', '!=', auth()->user()->branch_id)
                ->where('is_active', true)
                ->get(),
        ]);
    }
}
