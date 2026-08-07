<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;

class ServiceRequiredPart extends Model
{
    // Sprint 7.5F — Standardized status constants
    public const STATUS_REQUESTED       = 'requested';
    public const STATUS_APPROVED        = 'approved';
    public const STATUS_REJECTED        = 'rejected';
    public const STATUS_CANCELLED       = 'cancelled';
    public const STATUS_USED            = 'used';
    public const STATUS_RETURNED        = 'returned';
    public const STATUS_RESERVED        = 'reserved';
    public const SUPPLIER_WAITING_PURCHASE = 'waiting_purchase';
    public const SUPPLIER_INDENT        = 'indent';

    /**
     * States that hold a reservation (approved but not yet consumed).
     * These rows are the authoritative source of reserved stock per product.
     */
    public const RESERVED_STATES = [self::STATUS_APPROVED, self::STATUS_RESERVED];

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

    /**
     * Admin/authorized warehouse approves a request → RESERVES stock.
     *
     * BR-FIX-01 (BR-007/BR-009): Approval authorizes the part. Physical stock is
     * NOT reduced. Reserved quantity increases, available quantity decreases.
     * Idempotent — repeated approval has no extra side effects.
     *
     * @throws \RuntimeException when there is not enough AVAILABLE stock or the
     *                           part is not in a requestable state.
     */
    public function approve(): void
    {
        \DB::transaction(function () {
            // Idempotency: already approved/reserved → no-op.
            if (in_array($this->status, self::RESERVED_STATES, true)) {
                return;
            }

            if ($this->status !== self::STATUS_REQUESTED) {
                throw new \RuntimeException('Hanya part berstatus "requested" yang dapat disetujui.');
            }

            $product = Product::query()->lockForUpdate()->find($this->product_id);
            if (!$product) {
                throw new \RuntimeException('Produk tidak ditemukan untuk part ini.');
            }

            // Available = physical - reserved (excluding this request).
            $reservedByOthers = (int) static::query()
                ->where('product_id', $product->id)
                ->whereIn('status', self::RESERVED_STATES)
                ->where('id', '!=', $this->id)
                ->sum('qty');

            $available = (int) $product->stock_quantity - $reservedByOthers;
            if ($available < (int) $this->qty) {
                throw new \RuntimeException(
                    "Stok tidak cukup untuk reservasi. Tersedia: {$available}, diminta: {$this->qty}."
                );
            }

            $this->update([
                'status' => self::STATUS_APPROVED,
                'reserved_qty' => (int) $this->qty,
            ]);
            $this->refresh();
        });

        event(new \App\Events\Entity\StockReserved($this));
    }

    /** Admin rejects a request → never reserves stock. Idempotent. */
    public function reject(string $reason): void
    {
        if ($this->status === self::STATUS_REJECTED) {
            return;
        }

        if ($this->status !== self::STATUS_REQUESTED) {
            throw new \RuntimeException('Hanya part berstatus "requested" yang dapat ditolak.');
        }

        $this->update([
            'status' => self::STATUS_REJECTED,
            'cancel_reason' => $reason,
            'reserved_qty' => 0,
        ]);
        $this->refresh();
        event(new \App\Events\Entity\PartRequestCancelled($this));
    }

    /**
     * Cancel a request. If the part was approved/reserved (not yet consumed),
     * the reservation is RELEASED — physical stock unchanged, no mutation.
     * Idempotent. Consumed parts must go through the return flow instead.
     */
    public function cancel(string $reason): void
    {
        if ($this->status === self::STATUS_CANCELLED) {
            return;
        }

        if ($this->status === self::STATUS_USED) {
            throw new \RuntimeException('Part sudah dikonsumsi — gunakan alur retur untuk mengembalikan stok.');
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'cancel_reason' => $reason,
            'reserved_qty' => 0,
        ]);
        $this->refresh();
        event(new \App\Events\Entity\PartRequestCancelled($this));
    }

    /**
     * CS confirms / adds an approved part to the service invoice → CONSUMES it.
     *
     * BR-FIX-01 (BR-007): This is the ONLY place a service part reduces physical
     * stock. It must run inside a transaction, validate the reservation, consume
     * it, deduct physical stock EXACTLY ONCE, and create the canonical usage,
     * invoice (ServiceSparepart) and inventory mutation records.
     *
     * Idempotency: a part already marked "used" cannot be consumed again.
     *
     * @throws \RuntimeException when not approved, reservation missing, qty invalid
     *                           or stock insufficient.
     */
    public function use(int $csUserId, float $sellingPrice, float $discount = 0): void
    {
        \DB::transaction(function () use ($csUserId, $sellingPrice, $discount) {
            if ($this->status === self::STATUS_USED) {
                throw new \RuntimeException('Part ini sudah dikonsumsi sebelumnya.');
            }

            if (!in_array($this->status, self::RESERVED_STATES, true)) {
                throw new \RuntimeException('Part belum disetujui/direservasi — konsumsi tidak diizinkan.');
            }

            if ((int) $this->qty <= 0) {
                throw new \RuntimeException('Jumlah part tidak valid.');
            }

            $product = Product::query()->lockForUpdate()->find($this->product_id);
            if (!$product) {
                throw new \RuntimeException('Produk tidak ditemukan untuk part ini.');
            }

            // Reservation must exist (status already proves it) and physical stock
            // must cover the quantity.
            if ($product->stock_quantity < $this->qty) {
                throw new \RuntimeException(
                    "Stok tidak mencukupi. Tersedia: {$product->stock_quantity}, diminta: {$this->qty}."
                );
            }

            $before = $product->stock_quantity;
            $costPrice = $this->unit_price ?? $product->cost_price;
            $subtotal = ($sellingPrice * $this->qty) - $discount;

            // 1. Consume reservation + reduce physical stock ONCE.
            $product->reduceStock($this->qty);
            $after = $product->fresh()->stock_quantity;

            // 2. Canonical usage record.
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

            // 3. Invoice source-of-truth record (read by draftFromService).
            ServiceSparepart::create([
                'service_id' => $this->service_id,
                'product_id' => $product->id,
                'quantity' => $this->qty,
                'unit_price' => $sellingPrice,
                'subtotal' => $subtotal,
            ]);

            // 4. Exactly one physical-deduction mutation.
            InventoryMutation::create([
                'branch_id' => $this->service?->branch_id ?? $product->branch_id,
                'product_id' => $product->id,
                'type' => 'keluar',
                'quantity' => $this->qty,
                'before_stock' => $before,
                'after_stock' => $after,
                'unit_cost' => $costPrice,
                'reference_type' => 'service_part_usage',
                'reference_id' => $this->id,
                'note' => "Service #{$this->service_id} — dipakai CS",
                'created_by' => $csUserId,
            ]);

            // 5. Mark consumed.
            $this->update([
                'status' => self::STATUS_USED,
                'reserved_qty' => 0,
                'selling_price' => $sellingPrice,
                'discount' => $discount,
                'subtotal' => $subtotal,
                'used_by' => $csUserId,
                'used_at' => now(),
            ]);
        });

        $this->refresh();
        event(new \App\Events\Entity\PartUsed($this));
        event(new \App\Events\Entity\PartAddedToInvoice($this));

        $product = $this->product;
        if ($product && $product->fresh()->isLowStock()) {
            event(new \App\Events\Entity\LowStockDetected($product, $product->fresh()->stock_quantity, $product->min_stock));
        }
    }

    /**
     * BR-008 Case A — Part APPROVED/RESERVED but never consumed.
     *
     * Physical stock was never reduced, so we only release the reservation.
     * No restore, no reversal mutation.
     */
    public function releaseReservation(int $processedBy, string $reason): void
    {
        if ($this->status === self::STATUS_RETURNED || $this->status === self::STATUS_CANCELLED) {
            return; // idempotent
        }

        if (!in_array($this->status, self::RESERVED_STATES, true)) {
            throw new \RuntimeException('Part tidak dalam status reservasi untuk dilepaskan.');
        }

        $this->update([
            'status' => self::STATUS_RETURNED,
            'reserved_qty' => 0,
            'cancel_reason' => $reason,
        ]);
        $this->refresh();
        event(new \App\Events\Entity\StockReleased($this));
    }

    /**
     * BR-008 Case B — Part already CONSUMED/ISSUED but returned unused.
     *
     * Restores physical stock ONCE, creates a single reversal InventoryMutation,
     * removes the billable ServiceSparepart record and adjusts any DRAFT invoice.
     *
     * A finalized/PAID invoice is NOT silently modified — the operation is
     * blocked (financial reversal is a documented P2 dependency).
     *
     * @throws \RuntimeException when part not consumed, or invoice already paid.
     */
    public function returnToStock(int $processedBy, string $reason): void
    {
        \DB::transaction(function () use ($processedBy, $reason) {
            if ($this->status === self::STATUS_RETURNED) {
                return; // idempotent — no double restore
            }

            if ($this->status !== self::STATUS_USED) {
                throw new \RuntimeException('Hanya part yang sudah dikonsumsi yang dapat dikembalikan ke stok.');
            }

            $product = Product::query()->lockForUpdate()->find($this->product_id);
            if (!$product) {
                throw new \RuntimeException('Produk tidak ditemukan untuk part ini.');
            }

            // Never silently mutate a finalized invoice.
            $sale = $this->service?->sale;
            if ($sale && $sale->isPaid()) {
                throw new \RuntimeException(
                    'Invoice servis sudah lunas — pengembalian part tidak dapat memodifikasi transaksi final. Gunakan kebijakan refund/adjustment.'
                );
            }

            // 1. Restore physical stock ONCE.
            $before = $product->stock_quantity;
            $product->increaseStock($this->qty);
            $after = $product->fresh()->stock_quantity;

            // 2. Single reversal mutation.
            // NOTE: inventory_mutations.type is an enum limited to
            // masuk|keluar|transfer. A return restores stock, so it is recorded
            // as "masuk" (incoming) with reference_type="service_part_return" to
            // identify the reversal. This keeps the migration additive/rollback
            // safe (no enum rebuild).
            InventoryMutation::create([
                'branch_id' => $this->service?->branch_id ?? $product->branch_id,
                'product_id' => $product->id,
                'type' => 'masuk',
                'quantity' => $this->qty,
                'before_stock' => $before,
                'after_stock' => $after,
                'unit_cost' => $this->unit_price ?? $product->cost_price,
                'reference_type' => 'service_part_return',
                'reference_id' => $this->id,
                'note' => "Return Service #{$this->service_id}: {$reason}",
                'created_by' => $processedBy,
            ]);

            // 3. Remove billable record so it is no longer invoiced.
            ServiceSparepart::where('service_id', $this->service_id)
                ->where('product_id', $product->id)
                ->where('quantity', $this->qty)
                ->delete();

            // 4. Adjust a DRAFT invoice (never a paid one — guarded above).
            if ($sale && $sale->isDraft()) {
                $sale->items()
                    ->where('product_id', $product->id)
                    ->where('item_type', 'sparepart')
                    ->delete();
                $newSubtotal = (float) $sale->items()->sum(\DB::raw('quantity * price'));
                $sale->update(['subtotal' => $newSubtotal, 'total' => max(0, $newSubtotal - $sale->discount)]);
            }

            // 5. Reverse usage + mark part returned.
            ServicePartUsage::where('service_id', $this->service_id)
                ->where('service_required_part_id', $this->id)
                ->update(['quantity' => 0]);
            $this->update([
                'status' => self::STATUS_RETURNED,
                'reserved_qty' => 0,
                'cancel_reason' => $reason,
            ]);
        });

        $this->refresh();

        $product = $this->product;
        if ($product) {
            event(new \App\Events\Entity\StockReturned($product, $this->qty));
        }
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
