<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\Sale;
use App\Models\Tenant\Product;
use App\Models\Tenant\Customer;
use Illuminate\Http\Request;

/**
 * v1.4: Tenant Public API — read-only access via Bearer token.
 * Gated by the 'api' subscription feature.
 */
class TenantApiController extends Controller
{
    public function services(Request $request)
    {
        $query = Service::with('customer')->where('branch_id', auth()->user()->branch_id);
        if ($request->status) $query->where('status', $request->status);
        return response()->json($query->latest()->paginate(20));
    }

    public function serviceShow(Service $service)
    {
        return response()->json($service->load('customer', 'technician'));
    }

    public function sales(Request $request)
    {
        $query = Sale::with('customer')->where('branch_id', auth()->user()->branch_id);
        if ($request->status) $query->where('status', $request->status);
        return response()->json($query->latest()->paginate(20));
    }

    public function products(Request $request)
    {
        return response()->json(
            Product::where('branch_id', auth()->user()->branch_id)
                ->orderBy('name')->paginate(50)
        );
    }

    public function customers(Request $request)
    {
        $query = Customer::where('branch_id', auth()->user()->branch_id);
        if ($request->search) $query->where('name', 'like', "%{$request->search}%");
        return response()->json($query->latest()->paginate(30));
    }
}
