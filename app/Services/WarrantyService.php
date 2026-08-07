<?php

namespace App\Services;

use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceSparepart;
use App\Models\Tenant\ServiceWarranty;
use App\Models\Tenant\ServiceWarrantyClaim;
use App\Models\Tenant\User;
use Carbon\Carbon;

/**
 * BR-FIX-04 — Canonical warranty domain service (single source of truth).
 *
 * Centralizes:
 *  - STORE warranty eligibility (backend truth, never frontend text)
 *  - opening a warranty CLAIM + linking a NEW rework Service
 *  - upstream SUPPLIER/DISTRIBUTOR warranty (distinct from store warranty)
 *
 * The original completed Service is NEVER modified: a claim opens a new
 * rework Service (is_warranty_claim=true, parent_service_id=original).
 */
class WarrantyService
{
    // ═══════════════════════════════════════════════════════════════
    // STORE WARRANTY ELIGIBILITY
    // ═══════════════════════════════════════════════════════════════

    /**
     * Canonical store-warranty eligibility at $claimDate (default now).
     * True only when an active store warranty covers the claim date.
     * A void/cancelled service never has warranty. Store warranty is
     * independent from any upstream supplier warranty.
     */
    public static function isEligibleForStoreWarranty(Service $service, $claimDate = null): bool
    {
        $claimDate = $claimDate ? Carbon::parse($claimDate) : now();

        if (in_array($service->status, [Service::STATUS_VOID, Service::STATUS_CANCEL], true)) {
            return false;
        }

        // Service-level window (source of truth set at payment time).
        if ($service->warranty_expired_at && $service->warranty_expired_at->gte($claimDate)) {
            return true;
        }

        // ServiceWarranty row (auto-created at pickup).
        $warranty = static::storeWarrantyFor($service);
        if ($warranty && $warranty->status !== 'void' && $warranty->end_date) {
            if ($warranty->end_date->endOfDay()->gte($claimDate)) {
                return true;
            }
        }

        return false;
    }

    /** Latest store-warranty row for a service, if any. */
    public static function storeWarrantyFor(Service $service): ?ServiceWarranty
    {
        return ServiceWarranty::where('service_id', $service->id)->latest('id')->first();
    }

    /**
     * Ensure a ServiceWarranty row exists (created from the service's actual
     * warranty window when missing) so a claim always has a warranty link.
     */
    public static function ensureWarrantyRow(Service $service): ServiceWarranty
    {
        $warranty = static::storeWarrantyFor($service);
        if ($warranty) {
            return $warranty;
        }

        $end = $service->warranty_expired_at?->toDateString() ?? now()->toDateString();

        return ServiceWarranty::create([
            'service_id' => $service->id,
            'warranty_type' => 'service',
            'start_date' => $service->created_at?->toDateString() ?? now()->toDateString(),
            'end_date' => $end,
            'duration_days' => $service->warranty_days ?? 0,
            'terms' => 'Garansi servis (klaim).',
            'status' => 'active',
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // CLAIM + REWORK
    // ═══════════════════════════════════════════════════════════════

    /**
     * Open a warranty CLAIM — records the customer complaint ONLY.
     * The claim is created as `submitted` (NOT approved): approved_by,
     * checked_by and rework_service_id stay NULL until an AUTHORIZED review
     * approves it. Opening a claim NEVER creates a rework Service and NEVER
     * implies warranty liability approval.
     *
     * Throws \InvalidArgumentException when the store warranty is not eligible.
     */
    public static function openClaim(Service $original, User $user, string $problem, ?int $branchId = null): ServiceWarrantyClaim
    {
        if (!static::isEligibleForStoreWarranty($original)) {
            throw new \InvalidArgumentException('Garansi servis ini sudah berakhir atau tidak berlaku.');
        }

        $warranty = static::ensureWarrantyRow($original);
        $handlingBranchId = $branchId
            ?? $original->currentCustodyBranchId()
            ?? $original->branch_id;

        // Duplicate protection: while an open (not rejected/completed) claim
        // exists for the same original service + warranty, block a new claim.
        $openClaim = ServiceWarrantyClaim::where('service_id', $original->id)
            ->where('service_warranty_id', $warranty->id)
            ->whereIn('status', ['submitted', 'checking', 'approved', 'repairing'])
            ->exists();
        if ($openClaim) {
            throw new \InvalidArgumentException('Klaim garansi untuk servis ini masih aktif.');
        }

        $claim = ServiceWarrantyClaim::create([
            'service_warranty_id' => $warranty->id,
            'customer_id' => $original->customer_id,
            'service_id' => $original->id,
            'branch_id' => $handlingBranchId,
            'problem_description' => $problem,
            'status' => 'submitted',
            // NOT approved: approval fields remain NULL until authorized review.
            'checked_by' => null,
            'approved_by' => null,
        ]);

        event(new \App\Events\Entity\WarrantyClaimCreated($claim));
        ActivityLog::log(
            'warranty_claim_opened',
            'Klaim garansi #' . $claim->claim_number . ' dibuka (menunggu persetujuan).',
            $claim,
            ['claim_id' => $claim->id, 'original_service_id' => $original->id, 'opened_by' => $user->id]
        );

        return $claim;
    }

    /**
     * Approve an open claim (BR-FIX-04.1). Stronger authority must be checked
     * by the caller (finance-level + branch access). Eligibility is RE-CHECKED
     * at approval time. Creates the NEW rework Service exactly ONCE and links
     * rework_service_id. Repeated approval never creates a second rework.
     */
    public static function approveClaim(ServiceWarrantyClaim $claim, User $approver, ?string $note = null): ServiceWarrantyClaim
    {
        if (!in_array($claim->status, ['submitted', 'checking'], true)) {
            throw new \InvalidArgumentException('Klaim tidak dalam status terbuka untuk disetujui.');
        }

        $original = $claim->service;
        if (!$original || !static::isEligibleForStoreWarranty($original)) {
            throw new \InvalidArgumentException('Garansi tidak lagi berlaku saat persetujuan.');
        }

        $claim->update([
            'status' => 'approved',
            'checked_by' => $approver->id,
            'approved_by' => $approver->id,
            'approval_note' => $note,
        ]);

        // Create the NEW rework Service exactly once (idempotent).
        if (!$claim->rework_service_id) {
            $rework = static::createReworkService($original, $approver, $claim->problem_description, $claim->branch_id);
            $claim->linkRework($rework);
        }

        event(new \App\Events\Entity\WarrantyClaimApproved($claim));
        ActivityLog::log(
            'warranty_claim_approved',
            'Klaim #' . $claim->claim_number . ' disetujui' . ($claim->rework_service_id ? ' → rework #' . $claim->rework_service_id : ''),
            $claim,
            ['approved_by' => $approver->id, 'rework_service_id' => $claim->rework_service_id]
        );

        return $claim;
    }

    /**
     * Reject an open claim. No rework is created; the original Service is
     * never modified. Reason is mandatory.
     */
    public static function rejectClaim(ServiceWarrantyClaim $claim, User $user, string $reason): ServiceWarrantyClaim
    {
        if (!in_array($claim->status, ['submitted', 'checking'], true)) {
            throw new \InvalidArgumentException('Klaim tidak dalam status terbuka untuk ditolak.');
        }

        $claim->update([
            'status' => 'rejected',
            'checked_by' => $user->id,
            'approved_by' => $user->id,
            'approval_note' => $reason,
        ]);

        event(new \App\Events\Entity\WarrantyClaimRejected($claim));
        ActivityLog::log(
            'warranty_claim_rejected',
            'Klaim #' . $claim->claim_number . ' ditolak: ' . $reason,
            $claim,
            ['rejected_by' => $user->id]
        );

        return $claim;
    }

    /**
     * Create the NEW rework Service for an approved claim. The original
     * completed Service is NEVER modified — only legitimate context is copied.
     */
    private static function createReworkService(Service $original, User $user, string $problem, ?int $handlingBranchId): Service
    {
        return Service::create([
            'branch_id' => $handlingBranchId,
            'customer_id' => $original->customer_id,
            'device_id' => $original->device_id,
            'created_by' => $user->id,
            'technician_id' => $original->technician_id,
            'status' => Service::STATUS_MENUNGGU_ALOKASI,
            'problem_description' => $problem,
            'is_warranty_claim' => true,
            'parent_service_id' => $original->id,
            'service_charge' => 0,
            'total_cost' => 0,
            'posisi_unit' => $original->posisi_unit,
            'kategori_perangkat_id' => $original->kategori_perangkat_id,
            'merek_id' => $original->merek_id,
            'tipe_unit' => $original->tipe_unit,
            'imei_sn' => $original->imei_sn,
        ]);
    }

    // ═══════════════════════════════════════════════════════════════
    // UPSTREAM SUPPLIER / DISTRIBUTOR WARRANTY
    // ═══════════════════════════════════════════════════════════════

    /**
     * Is the UPSTREAM (supplier/distributor) warranty for an installed part
     * active at $date? Lifetime flag wins; otherwise the supplier warranty
     * window starts from the service completion and lasts N days.
     */
    public static function isUpstreamWarrantyActive(ServiceSparepart $part, $date = null): bool
    {
        if ($part->supplier_warranty_lifetime) {
            return true;
        }

        $days = (int) ($part->supplier_warranty_days ?? 0);
        if ($days <= 0) {
            return false;
        }

        $start = $part->service?->selesai_at ?? $part->service?->completed_at ?? $part->updated_at ?? now();
        $expires = Carbon::parse($start)->addDays($days);

        return $expires->gte($date ? Carbon::parse($date) : now());
    }

    /**
     * Upstream warranty status for every installed part of a service.
     * Each entry: part_id, part_name, supplier_name, warranty_type
     * ('lifetime'|'duration'|'none'), status ('active'|'expired'|'none'),
     * expires_at.
     */
    public static function upstreamWarrantyFor(Service $service, $date = null): array
    {
        $result = [];
        foreach ($service->spareparts as $part) {
            $status = static::isUpstreamWarrantyActive($part, $date) ? 'active' : 'expired';
            $type = $part->supplier_warranty_lifetime ? 'lifetime' : ((int) ($part->supplier_warranty_days ?? 0) > 0 ? 'duration' : 'none');
            if ($type === 'none') {
                $status = 'none';
            }
            $result[] = [
                'part_id' => $part->id,
                'part_name' => $part->product?->name ?? 'Sparepart #' . $part->id,
                'supplier_id' => $part->supplier_id,
                'supplier_name' => $part->supplier?->name,
                'warranty_type' => $type,
                'status' => $status,
                'supplier_warranty_days' => $part->supplier_warranty_days,
                'supplier_warranty_lifetime' => (bool) $part->supplier_warranty_lifetime,
            ];
        }

        return $result;
    }
}
