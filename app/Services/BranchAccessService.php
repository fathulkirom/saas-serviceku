<?php

namespace App\Services;

use App\Models\Tenant\User;
use App\Models\Tenant\Branch;
use App\Models\Tenant\BranchVisibility;
use Illuminate\Database\Eloquent\Builder;

/**
 * BR-FIX-02 — Centralized branch-scope resolver. The ONLY place branch access
 * is decided (policies delegate here; controllers MUST NOT re-implement branch
 * comparisons).
 *
 * Access chain: TENANT ∩ PLAN/FEATURE ∩ PERMISSION ∩ ASSIGNED BRANCH SCOPE ∩ POLICY.
 *
 * Canonical branch scope:
 *   OWNER   → every branch in the current tenant.
 *   MANAGER → primary branch + explicitly assigned pivot branches.
 *   ADMIN/CS/KASIR/TEKNISI → per existing rules (primary branch + any explicit
 *            pivot assignment; additional branch access is always explicit).
 *
 * `branch_visibility` (BR-005) is a separate READ-ONLY stock visibility scope —
 * it never grants mutation authority.
 */
class BranchAccessService
{
    /**
     * All branch IDs the user may access (cached on the User model).
     */
    public static function accessibleBranchIds(User $user): array
    {
        return $user->branchAccessIds();
    }

    /**
     * Can this user access the given branch ID?
     * Null branch = global record, accessible to all.
     */
    public static function canAccess(User $user, ?int $branchId): bool
    {
        if ($user->isOwner()) {
            return true;
        }

        if (!$branchId) {
            return true; // Global record
        }

        return in_array((int) $branchId, static::accessibleBranchIds($user), true);
    }

    /**
     * Can this user access the given branch (model or int)?
     */
    public static function canAccessBranch(User $user, $branch): bool
    {
        $id = is_object($branch) ? $branch->getKey() : $branch;

        return static::canAccess($user, $id ? (int) $id : null);
    }

    /**
     * Can this user access a record that carries a branch_id attribute?
     */
    public static function canAccessRecord(User $user, $record): bool
    {
        $branchId = $record->branch_id ?? null;

        return static::canAccess($user, $branchId !== null ? (int) $branchId : null);
    }

    /**
     * Scope a query to records from branches the user can access.
     */
    public static function scope(Builder $query, User $user, string $branchColumn = 'branch_id'): Builder
    {
        if ($user->isOwner()) {
            return $query;
        }

        $ids = static::accessibleBranchIds($user);
        if (empty($ids)) {
            return $query->whereNull($branchColumn);
        }

        return $query->whereIn($branchColumn, $ids);
    }

    // ────────────────────────────────────────────────────────────────
    // BR-005 — READ-ONLY stock visibility
    // ────────────────────────────────────────────────────────────────

    /**
     * Branch IDs whose stock this user may READ: own/accessible branches PLUS
     * any branches configured as visible via branch_visibility. This is READ
     * visibility only — it grants no mutation/transfer/financial authority.
     */
    public static function visibleBranchIds(User $user): array
    {
        $accessible = static::accessibleBranchIds($user);
        if (empty($accessible)) {
            return [];
        }

        if ($user->isOwner()) {
            // Owner already sees all branches.
            return $accessible;
        }

        $visible = BranchVisibility::query()
            ->whereIn('branch_id', $accessible)
            ->pluck('visible_branch_id')
            ->map(fn($id) => (int) $id)
            ->all();

        return array_values(array_unique(array_merge($accessible, $visible)));
    }

    /**
     * Scope a query to stock the user may READ (own + configured visible).
     * Never used for mutation.
     */
    public static function stockVisibilityScope(Builder $query, User $user, string $branchColumn = 'branch_id'): Builder
    {
        $ids = static::visibleBranchIds($user);
        if (empty($ids)) {
            return $query->whereNull($branchColumn);
        }

        return $query->whereIn($branchColumn, $ids);
    }
}
