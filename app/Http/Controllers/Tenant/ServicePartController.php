<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceRequiredPart;
use App\Models\Tenant\ServicePartReturn;
use Illuminate\Http\Request;

/**
 * Service Part Controller — Sprint 7.4 Revision + BR-FIX-01.
 *
 * Canonical part lifecycle:
 *   TECHNICIAN REQUEST → ADMIN/WAREHOUSE APPROVE (reserve, no physical impact)
 *   → CS CONFIRMS / ADDS TO INVOICE (reservation consumed, stock reduced once)
 *   → usage + invoice + inventory mutation.
 *
 * Cancellation releases a reservation. Returns distinguish reserved-only
 * (release) from consumed (restore + reversal).
 *
 * Authorization is enforced via ServiceRequiredPartPolicy (backend policy).
 */
class ServicePartController extends Controller
{
    /** Tech requests a part (no stock impact) */
    public function request(Request $request, Service $service)
    {
        $user = auth()->user();
        if (!$user->isOwner() && !$user->isAdmin() && !$user->isManager() && $service->technician_id !== $user->id) {
            abort(403, 'Hanya teknisi yang ditugaskan atau admin/manager yang dapat meminta part.');
        }

        $data = $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'part_name' => 'required|string',
            'qty' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'unit_price' => 'nullable|numeric|min:0',
        ]);

        // Branch scoping: a part request may only reference a product available
        // in the service's branch (or a global product).
        if (!empty($data['product_id'])) {
            $product = Product::query()
                ->where('id', $data['product_id'])
                ->where(function ($q) use ($service) {
                    $q->where('branch_id', $service->branch_id)
                      ->orWhereNull('branch_id');
                })
                ->first();

            if (!$product) {
                abort(422, 'Produk tidak tersedia di cabang ini.');
            }
        }

        $part = ServiceRequiredPart::create([
            'service_id' => $service->id,
            'product_id' => $data['product_id'] ?? null,
            'part_name' => $data['part_name'],
            'qty' => $data['qty'],
            'unit_price' => $data['unit_price'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        $part->request(auth()->id());
        return back()->with('success', 'Part requested.');
    }

    /** Tech/admin cancels request (releases reservation if any, no stock impact) */
    public function cancelRequest(Request $request, ServiceRequiredPart $part)
    {
        $this->authorize('cancel', $part);
        $data = $request->validate(['reason' => 'required|string']);
        try {
            $part->cancel($data['reason']);
            return back()->with('success', 'Request dibatalkan. Reservasi dilepaskan.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Admin/authorized warehouse approves a request → creates reservation.
     * Physical stock unchanged; reserved increases; available decreases.
     */
    public function approveRequest(ServiceRequiredPart $part)
    {
        $this->authorize('approve', $part);
        try {
            $part->approve();
            return back()->with('success', 'Request disetujui. Stok di-reservasi.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** Admin/authorized warehouse rejects a request → never reserves stock. */
    public function rejectRequest(Request $request, ServiceRequiredPart $part)
    {
        $this->authorize('reject', $part);
        $data = $request->validate(['reason' => 'required|string']);
        try {
            $part->reject($data['reason']);
            return back()->with('success', 'Request ditolak.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * CS / billing actor confirms an approved part → consumes reservation and
     * reduces physical stock exactly once, creating usage + invoice + mutation.
     */
    public function usePart(Request $request, ServiceRequiredPart $part)
    {
        $this->authorize('consume', $part);

        $data = $request->validate([
            'selling_price' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
        ]);

        try {
            $part->use(auth()->id(), $data['selling_price'], $data['discount'] ?? 0);
            return back()->with('success', 'Part dikonfirmasi. Stok berkurang dan masuk ke invoice.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** CS/authorized actor requests return of a part (reserved-only or consumed). */
    public function requestReturn(Request $request, Service $service)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:1',
            'reason' => 'required|string',
            'service_required_part_id' => 'nullable|exists:service_required_parts,id',
        ]);

        ServicePartReturn::create([
            'service_id' => $service->id,
            'product_id' => $data['product_id'],
            'service_required_part_id' => $data['service_required_part_id'] ?? null,
            'quantity' => $data['quantity'],
            'reason' => $data['reason'],
            'requested_by' => auth()->id(),
        ]);

        return back()->with('success', 'Return requested.');
    }

    /**
     * Process a return.
     *
     *   Case A — reserved but never consumed → release reservation only.
     *   Case B — consumed but returned unused    → restore stock + reversal.
     *
     * Idempotent: an already-processed return is a no-op.
     * Blocked when the service has a finalized (PAID) invoice.
     */
    public function processReturn(ServicePartReturn $return)
    {
        $part = $return->service_required_part_id
            ? ServiceRequiredPart::find($return->service_required_part_id)
            : null;

        if ($part) {
            $this->authorize('returnPart', $part);
        } else {
            $user = auth()->user();
            if (!in_array($user->role, ['owner', 'admin', 'manager', 'cs', 'cashier'], true)) {
                abort(403, 'Tidak berhak memproses retur part.');
            }
        }

        if ($return->status === 'processed') {
            return back()->with('info', 'Return sudah diproses sebelumnya.');
        }

        try {
            if ($part && in_array($part->status, ServiceRequiredPart::RESERVED_STATES, true)) {
                // Case A: never consumed → release reservation, no stock change.
                $part->releaseReservation(auth()->id(), $return->reason);
                $return->update(['status' => 'processed', 'processed_by' => auth()->id()]);
                event(new \App\Events\Entity\PartReturned($return));
                return back()->with('success', 'Reservasi part dilepaskan. Stok fisik tidak berubah.');
            }

            if ($part && $part->status === ServiceRequiredPart::STATUS_USED) {
                // Case B: consumed → restore stock once + reversal.
                $part->returnToStock(auth()->id(), $return->reason);
                $return->update(['status' => 'processed', 'processed_by' => auth()->id()]);
                event(new \App\Events\Entity\PartReturned($return));
                return back()->with('success', 'Part dikembalikan. Stok dipulihkan dan invoice disesuaikan.');
            }

            // Legacy fallback (no linked part): generic consumed-part restore.
            if ($part && in_array($part->status, [ServiceRequiredPart::STATUS_REQUESTED, ServiceRequiredPart::STATUS_REJECTED, ServiceRequiredPart::STATUS_CANCELLED], true)) {
                $part->cancel('Retur tanpa konsumsi');
                $return->update(['status' => 'processed', 'processed_by' => auth()->id()]);
                event(new \App\Events\Entity\PartReturned($return));
                return back()->with('success', 'Request part dibatalkan. Tidak ada stok yang berubah.');
            }

            throw new \RuntimeException('Status part tidak dikenali untuk retur.');
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /** Service profit calculation */
    public function profit(Service $service)
    {
        $parts = ServiceRequiredPart::where('service_id', $service->id)->where('status', 'used')->get();
        $partRevenue = $parts->sum('subtotal');
        $partCost = $parts->sum(function ($p) {
            return ($p->unit_price ?? $p->product?->cost_price ?? 0) * $p->qty;
        });
        $serviceRevenue = (float) ($service->service_charge ?? 0);
        $totalRevenue = $serviceRevenue + $partRevenue;
        $totalCost = $partCost;
        $profit = $totalRevenue - $totalCost;

        event(new \App\Events\Entity\ServiceProfitCalculated($service, $profit));

        return response()->json([
            'service_revenue' => $serviceRevenue,
            'part_revenue' => $partRevenue,
            'total_revenue' => $totalRevenue,
            'part_cost' => $partCost,
            'profit' => $profit,
            'margin_pct' => $totalRevenue > 0 ? round(($profit / $totalRevenue) * 100) : 0,
        ]);
    }
}
