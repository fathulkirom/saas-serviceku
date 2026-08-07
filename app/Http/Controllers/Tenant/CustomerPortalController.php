<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ServiceQuotation;
use Illuminate\Http\Request;

/**
 * CustomerPortalController
 * 
 * Sprint v2.0E: Lightweight controller for Customer Portal data supply.
 * Customer-facing endpoints that return data scoped to the authenticated customer.
 */
class CustomerPortalController extends Controller
{
    /**
     * GET /api/customer/pending-quotations
     * 
     * Returns pending quotations for the authenticated customer.
     * Used by CustomerPortal Overview to show Pending Approvals card.
     */
    public function pendingQuotations(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return response()->json(['quotations' => []], 401);
        }

        // Match customer by email or phone
        $customerId = \App\Models\Tenant\Customer::where('email', $user->email)
            ->orWhere('phone', $user->phone)
            ->value('id');

        if (!$customerId) {
            return response()->json(['quotations' => []], 200);
        }

        $quotations = ServiceQuotation::with('service')
            ->whereHas('service', function ($q) use ($customerId) {
                $q->where('customer_id', $customerId);
            })
            ->where('status', 'sent')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($q) => [
                'id' => $q->id,
                'service_id' => $q->service_id,
                'service_tracking_code' => $q->service->tracking_code ?? '#' . $q->service_id,
                'device_name' => $q->service->tipe_unit ?? $q->service->device_name ?? null,
                'total_cost' => (float) $q->total_cost,
                'status' => $q->status,
                'created_at' => $q->created_at?->toISOString(),
                'notes' => $q->notes,
            ]);

        return response()->json(['quotations' => $quotations]);
    }
}
