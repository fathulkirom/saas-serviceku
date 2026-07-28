<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::with(['customer', 'technician'])
            ->where('branch_id', auth()->user()->branch_id)
            ->latest()->paginate(20);
        return response()->json(['data' => $services]);
    }

    public function show(Service $service)
    {
        $service->load(['customer', 'technician', 'photos', 'spareparts.product', 'checklists']);
        return response()->json(['data' => $service]);
    }
}
