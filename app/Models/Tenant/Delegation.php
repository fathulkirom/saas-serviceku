<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * BR-FIX-03 — Granular delegation of a single capability to an existing
 * tenant user, scoped (optionally) to one branch and bounded by time.
 *
 * This is NOT a second authorization system — it is an additive layer on
 * top of the existing Role + Permission + Branch Scope architecture. The
 * grantee's `role` column is never modified; a grant only makes
 * `User::canViaPermission()`/`canViaPermissionInBranch()` return true for
 * the delegated capability while the grant is active.
 *
 * Expiration and revocation are evaluated at REQUEST TIME by
 * `scopeActive()` / `isActive()` — no cron job required. Revocation is
 * immediate because delegated capabilities are never merged into the
 * cached role-permission list.
 */
class Delegation extends Model
{
    protected $table = 'delegations';

    protected $fillable = [
        'user_id',
        'permission',
        'branch_id',
        'granted_by',
        'starts_at',
        'expires_at',
        'revoked_at',
        'revoked_by',
        'reason',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /** The grantee (existing tenant user receiving the capability). */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /** Branch scope (nullable → grant applies at the grantee's role scope). */
    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function granter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'granted_by');
    }

    public function revoker(): BelongsTo
    {
        return $this->belongsTo(User::class, 'revoked_by');
    }

    /**
     * Active grants only — not revoked, not yet started excluded,
     * and not expired, evaluated against `now()` at REQUEST TIME.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->whereNull('revoked_at')
            ->where(function (Builder $q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function (Builder $q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });
    }

    /** Whether this grant is currently effective (request-time evaluation). */
    public function isActive(): bool
    {
        return $this->revoked_at === null
            && ($this->starts_at === null || $this->starts_at->lte(now()))
            && ($this->expires_at === null || $this->expires_at->gt(now()));
    }

    /**
     * Does this grant cover the given branch?
     * A null branch scope covers any branch the grantee can reach via role;
     * a concrete branch covers only that branch.
     */
    public function coversBranch(?int $branchId): bool
    {
        if ($this->branch_id === null) {
            return true;
        }

        return $branchId !== null && (int) $this->branch_id === (int) $branchId;
    }

    /** Revoke immediately and record who did it. */
    public function revoke(int $byUserId): void
    {
        $this->update([
            'revoked_at' => now(),
            'revoked_by' => $byUserId,
        ]);
        $this->refresh();
    }
}
