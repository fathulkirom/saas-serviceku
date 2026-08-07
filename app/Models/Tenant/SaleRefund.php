<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BR-FIX-04 — Auditable financial reversal (refund).
 *
 * A refund is a SEPARATE append-only financial event. The original Sale /
 * payment (JSON `payment_details`) is NEVER edited or deleted. Refunds only
 * accumulate; duplicate / over-refund are prevented by the controller
 * (refundable balance) and by the unique claim_id constraint.
 *
 * A refund NEVER automatically restores inventory — stock is restored only
 * through the canonical return/reversal rules (BR-FIX-01).
 */
class SaleRefund extends Model
{
    protected $table = 'sale_refunds';

    protected $fillable = [
        'claim_id',
        'sale_id',
        'service_id',
        'branch_id',
        'amount',
        'reason',
        'method',
        'authorized_by',
        'created_by',
        'refunded_at',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_at' => 'datetime',
    ];

    public function claim(): BelongsTo
    {
        return $this->belongsTo(ServiceWarrantyClaim::class, 'claim_id');
    }

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class, 'sale_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function authorizer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'authorized_by');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Total already refunded against a sale (for refundable-balance checks).
     */
    public static function totalRefundedForSale(Sale $sale): float
    {
        return (float) static::where('sale_id', $sale->id)->where('status', 'processed')->sum('amount');
    }

    /**
     * Remaining refundable balance for a paid sale.
     */
    public static function refundableForSale(Sale $sale): float
    {
        if ($sale->status !== Sale::STATUS_PAID) {
            return 0.0;
        }

        return max(0.0, (float) $sale->paid_amount - static::totalRefundedForSale($sale));
    }
}
