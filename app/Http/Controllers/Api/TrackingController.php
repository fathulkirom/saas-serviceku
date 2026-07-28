<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use Illuminate\Http\Request;

class TrackingController extends Controller
{
    public function show($trackingCode)
    {
        $host = request()->getHost();
        $domain = \Stancl\Tenancy\Database\Models\Domain::where('domain', $host)->first();

        if (!$domain) {
            return response()->json(['message' => 'Tenant not found'], 404);
        }

        try {
            $tenant = \App\Models\Tenant::find($domain->tenant_id);
            if (!$tenant || !$tenant->is_active) {
                return response()->json(['message' => 'Tenant not active'], 404);
            }

            tenancy()->initialize($tenant);
            $service = Service::with(['customer', 'technician', 'photos', 'kategoriPerangkat', 'merek'])
                ->where('tracking_code', $trackingCode)
                ->first();

            if (!$service) {
                return response()->json(['message' => 'Service not found'], 404);
            }

            return response()->json(['data' => $service]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Error: ' . $e->getMessage()], 500);
        }
    }
}
