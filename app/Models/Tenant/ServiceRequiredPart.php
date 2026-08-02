<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ServiceRequiredPart extends Model
{
    // Sprint 7.5F — Standardized status constants
    public const STATUS_REQUESTED       = 'requested';
    public const STATUS_APPROVED        = 'approved';
    public const STATUS_CANCELLED       = 'cancelled';
    public const STATUS_USED            = 'used';
    public const STATUS_RETURNED        = 'returned';
    public const STATUS_RESERVED        = 'reserved';
    public const SUPPLIER_WAITING_PURCHASE = 'waiting_purchase';
    public const SUPPLIER_INDENT        = 'indent';

    protected $fillable = ['service_id', 'product_id', 'location_id', 'part_name', 'qty', 'reserved_qty', 'status', 'priority', 'cancel_reason', 'supplier_status', 'unit_price', 'selling_price', 'discount', 'subtotal', 'notes', 'requested_by', 'used_by', 'used_at'];

    protected $casts = ['unit_price' => 'decimal:2', 'selling_price' => 'decimal:2', 'discount' => 'decimal:2', 'subtotal' => 'decimal:2', 'used_at' => 'datetime'];

    public function service() { return $this->belongsTo(Service::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function requester() { return $this->belongsTo(User::class, 'requested_by'); }
    public function user() { return $this->belongsTo(User::class, 'used_by'); }

    // Sprint 7.4 Revision — Real Service Center Flow

    /** Tech requests part → no stock impact */
    public function request(int $techId): void
    {
        $this->update(['status' => self::STATUS_REQUESTED, 'requested_by' => $techId]);
        event(new \App\Events\Entity\PartRequested($this));
    }

    /** Admin approves request → no stock impact, just confirms need */
    public function approve(): void
    {
        $this->update(['status' => self::STATUS_APPROVED]);
    }

    /** Tech cancels request → requires reason, no stock impact */
    public function cancel(string $reason): void
    {
        $this->update(['status' => 'cancelled', 'cancel_reason' => $reason]);
        event(new \App\Events\Entity\PartRequestCancelled($this));
    }

    /** CS puts part on invoice → THIS reduces stock */
    public function use(int $csUserId, float $sellingPrice, float $discount = 0): void
    {
        $product = $this->product;
        if (!$product || $product->stock_quantity < $this->qty) {
            throw new \RuntimeException('Stok tidak mencukupi.');
        }

        $before = $product->stock_quantity;
        $costPrice = $this->unit_price ?? $product->cost_price;
        $subtotal = ($sellingPrice * $this->qty) - $discount;

        \DB::transaction(function () use ($product, $before, $csUserId, $sellingPrice, $discount, $subtotal, $costPrice) {
            // Reduce stock
            $product->reduceStock($this->qty);

            // Record usage
            ServicePartUsage::create([
                'service_id' => $this->service_id,
                'product_id' => $product->id,
                'service_required_part_id' => $this->id,
                'quantity' => $this->qty,
                'cost_price' => $costPrice,
                'selling_price' => $sellingPrice,
                'discount' => $discount,
                'subtotal' => $subtotal,
                'created_by' => $csUserId,
            ]);

            // Mutation log
            InventoryMutation::create([
                'product_id' => $product->id,
                'type' => 'service_usage',
                'quantity' => -$this->qty,
                'before_stock' => $before,
                'after_stock' => $product->fresh()->stock_quantity,
                'unit_cost' => $costPrice,
                'reference_type' => 'service_part_usage',
                'reference_id' => $this->id,
                'note' => "Service #{$this->service_id} — used by CS",
                'created_by' => $csUserId,
            ]);

            // Update self
            $this->update([
                'status' => 'used',
                'selling_price' => $sellingPrice,
                'discount' => $discount,
                'subtotal' => $subtotal,
                'used_by' => $csUserId,
                'used_at' => now(),
            ]);
        });

        event(new \App\Events\Entity\PartUsed($this));
        if ($product->fresh()->isLowStock()) {
            event(new \App\Events\Entity\LowStockDetected($product, $product->stock_quantity, $product->min_stock));
        }
    }

    /** Return used part to stock */
    public function returnToStock(int $processedBy, string $reason): void
    {
        $product = $this->product;
        $before = $product->stock_quantity;
        $product->increaseStock($this->qty);

        InventoryMutation::create([
            'product_id' => $product->id,
            'type' => 'return',
            'quantity' => $this->qty,
            'before_stock' => $before,
            'after_stock' => $product->fresh()->stock_quantity,
            'reference_type' => 'service_part_return',
            'reference_id' => $this->id,
            'note' => "Return Service #{$this->service_id}: {$reason}",
            'created_by' => $processedBy,
        ]);

        $this->update(['status' => 'returned']);
        event(new \App\Events\Entity\PartReturned($this));
    }

    public function reserve(): void  { $this->update(['status' => 'reserved']); }
    public function return(): void   { $this->update(['status' => 'returned']); }

    // Sprint 7.4A — Edit while mutable
    public function canEdit(): bool
    {
        return in_array($this->status, ['requested', 'approved']);
    }

    public function edit(array $data): void
    {
        if (!$this->canEdit()) {
            throw new \RuntimeException('Part sudah digunakan — tidak bisa diedit.');
        }
        $this->update($data);
        event(new \App\Events\Entity\PartEdited($this));
    }

    // Sprint 7.4A — Set priority
    public function setPriority(string $priority): void
    {
        $this->update(['priority' => $priority]);
        event(new \App\Events\Entity\PriorityChanged($this));
    }

    // Sprint 7.4A — Mark as waiting purchase
    public function markWaitingPurchase(): void
    {
        $this->update(['supplier_status' => 'waiting_purchase']);
        event(new \App\Events\Entity\WaitingPurchase($this));
    }

    public function markIndent(): void
    {
        $this->update(['supplier_status' => 'indent']);
    }

    // Sprint 7.4A — Scope for CS panel (approved parts ready for invoice)
    public function scopeApproved($q) { return $q->where('status', 'approved'); }
    public function scopeByPriority($q) { return $q->orderByRaw("FIELD(priority, 'warranty', 'vip', 'urgent', 'normal')"); }
}
