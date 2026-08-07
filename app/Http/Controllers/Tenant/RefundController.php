<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\Expense;
use App\Models\Tenant\Sale;
use App\Models\Tenant\SaleRefund;
use App\Models\Tenant\ServiceWarrantyClaim;
use App\Services\BranchAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * BR-FIX-04 / BR-FIX-04.1 — Minimal auditable refund architecture with a REAL
 * financial cash-out effect.
 *
 * A refund is a SEPARATE append-only financial event (sale_refunds) PLUS a real
 * cash-out line in `expenses` (the only money-out ledger ServiceKU has), so
 * ReportController::finance profit (revenue − expenses) reflects the refund.
 * The original Sale / payment JSON is NEVER edited or deleted. Refunds never
 * automatically restore inventory.
 *
 * Guards: authorized (finance authority), amount <= refundable balance,
 * duplicate prevented (balance + idempotent re-approval), branch access,
 * tenant-local.
 */
class RefundController extends Controller
{
    /** Refund against a paid sale directly. */
    public function store(Request $request, Sale $sale)
    {
        $this->authorizeRefund($request->user(), $sale->branch_id);

        if ($sale->status !== Sale::STATUS_PAID) {
            return back()->with('error', 'Refund hanya untuk penjualan lunas.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:1000',
            'method' => 'nullable|string|max:50',
        ]);

        $refund = DB::transaction(function () use ($sale, $request, $validated) {
            $refundable = SaleRefund::refundableForSale($sale);
            if ((float) $validated['amount'] > $refundable) {
                throw new \InvalidArgumentException('Jumlah refund melebihi saldo yang dapat direfund (Rp ' . number_format($refundable, 0, ',', '.') . ').');
            }

            $refund = SaleRefund::create([
                'sale_id' => $sale->id,
                'service_id' => $sale->service_id,
                'branch_id' => $sale->branch_id,
                'amount' => $validated['amount'],
                'reason' => $validated['reason'] ?? null,
                'method' => $validated['method'] ?? null,
                'authorized_by' => $request->user()->id,
                'created_by' => $request->user()->id,
                'refunded_at' => now(),
                'status' => 'processed',
            ]);

            static::postCashOut($refund, $request->user()->id, $sale->branch_id);

            return $refund;
        });

        event(new \App\Events\Entity\WarrantyRefunded($refund));
        ActivityLog::log('sale_refunded', 'Refund Rp ' . number_format($refund->amount, 0, ',', '.') . ' untuk nota #' . $sale->id, $sale, [
            'refund_id' => $refund->id,
            'amount' => $refund->amount,
            'reason' => $refund->reason,
            'authorized_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Refund Rp ' . number_format($refund->amount, 0, ',', '.') . ' dicatat.');
    }

    /** Refund linked to a warranty claim (BR-012). */
    public function refundClaim(Request $request, ServiceWarrantyClaim $claim)
    {
        $sale = $claim->service?->sale;
        if (!$sale) {
            return back()->with('error', 'Klaim ini tidak memiliki nota asli yang lunas.');
        }

        $this->authorizeRefund($request->user(), $sale->branch_id);

        if ($sale->status !== Sale::STATUS_PAID) {
            return back()->with('error', 'Refund hanya untuk penjualan lunas.');
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reason' => 'nullable|string|max:1000',
            'method' => 'nullable|string|max:50',
        ]);

        try {
            $refund = DB::transaction(function () use ($claim, $sale, $request, $validated) {
                $refundable = SaleRefund::refundableForSale($sale);
                if ((float) $validated['amount'] > $refundable) {
                    throw new \InvalidArgumentException('Jumlah refund melebihi saldo yang dapat direfund (Rp ' . number_format($refundable, 0, ',', '.') . ').');
                }

                $refund = SaleRefund::create([
                    'claim_id' => $claim->id,
                    'sale_id' => $sale->id,
                    'service_id' => $sale->service_id,
                    'branch_id' => $sale->branch_id,
                    'amount' => $validated['amount'],
                    'reason' => $validated['reason'] ?? null,
                    'method' => $validated['method'] ?? null,
                    'authorized_by' => $request->user()->id,
                    'created_by' => $request->user()->id,
                    'refunded_at' => now(),
                    'status' => 'processed',
                ]);

                static::postCashOut($refund, $request->user()->id, $sale->branch_id);

                return $refund;
            });
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        }

        // Claim resolved via refund (uses existing completed status + audit).
        $claim->refresh();
        $claim->resolve($request->user()->id, 'Diresolusi via refund Rp ' . number_format($refund->amount, 0, ',', '.') . '.');

        event(new \App\Events\Entity\WarrantyRefunded($refund));
        ActivityLog::log('warranty_refunded', 'Refund klaim #' . $claim->claim_number . ' Rp ' . number_format($refund->amount, 0, ',', '.'), $claim, [
            'refund_id' => $refund->id,
            'amount' => $refund->amount,
            'sale_id' => $sale->id,
            'authorized_by' => $request->user()->id,
        ]);

        return back()->with('success', 'Refund klaim #' . $claim->claim_number . ' dicatat.');
    }

    /**
     * BR-FIX-04.1 — REAL cash-out: write the refund as an Expense line
     * (the only money-out ledger). Category uses the existing 'lainnya' value
     * (SQLite enum — not widened) with a descriptive prefix and a
     * `sale_refund_id` link for traceability. Original Payment untouched.
     */
    private static function postCashOut(SaleRefund $refund, int $byUserId, int $branchId): Expense
    {
        return Expense::create([
            'branch_id' => $branchId,
            'description' => 'Refund ' . ($refund->claim_id ? 'klaim #' . $refund->claim_id : 'nota #' . $refund->sale_id)
                . ' (Rp ' . number_format($refund->amount, 0, ',', '.') . ') — ' . ($refund->reason ?? 'refund'),
            'amount' => $refund->amount,
            'expense_date' => $refund->refunded_at?->toDateString() ?? now()->toDateString(),
            'category' => 'lainnya',
            'user_id' => $byUserId,
            'created_by' => $byUserId,
            'sale_refund_id' => $refund->id,
        ]);
    }

    /**
     * Refund requires finance-level authority (owner/admin/manager/head_store
     * via canManageFinance, or an explicit finance.manage grant/delegation) and
     * branch access. A delegated service.intake permission does NOT grant this.
     */
    private function authorizeRefund($user, $branchId): void
    {
        if (!$user->canManageFinance() && !$user->canViaPermission('finance.manage')) {
            abort(403, 'Tidak berwenang melakukan refund.');
        }

        if (!BranchAccessService::canAccess($user, $branchId)) {
            abort(403, 'Nota berada di luar jangkauan cabang Anda.');
        }
    }
}
