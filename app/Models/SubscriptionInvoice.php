<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * UPGRADE-08: Subscription Invoice.
 *
 * Platform billing — ONLY for tenant ↔ ServiceKU relationship.
 * NOT for tenant customer invoices (those are in tenant DBs).
 */
class SubscriptionInvoice extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'tenant_id', 'invoice_number', 'type', 'status',
        'subtotal', 'discount', 'total',
        'billing_period', 'due_at', 'paid_at',
        'line_items', 'metadata',
    ];

    protected $casts = [
        'subtotal'    => 'decimal:2',
        'discount'    => 'decimal:2',
        'total'       => 'decimal:2',
        'due_at'      => 'datetime',
        'paid_at'     => 'datetime',
        'line_items'  => 'json',
        'metadata'    => 'json',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public static function generateNumber(): string
    {
        return 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }
}
