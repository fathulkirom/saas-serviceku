<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceTransfer;
use App\Models\Tenant\ActivityLog;
use App\Services\BranchAccessService;
use Illuminate\Http\Request;

/**
 * BR-FIX-02 (BR-004) — Cross-branch custody transfer.
 *
 * Origin ownership (service.branch_id) is PRESERVED — never rewritten.
 * Workflow: requested → sent → received (custody moves to the received branch),
 * or cancelled. Every transition is authorized by branch scope + audited.
 */
class ServiceTransferController extends Controller
{
    /** Legacy form route — kept as a redirect (transfer is handled via service tools / API). */
    public function create()
    {
        return redirect()->route('servis-tools.index')->with('info', 'Transfer servis dikelola melalui Servis Tools.');
    }

    /** Request a transfer from the current custody branch to a destination. */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|exists:services,id',
            'to_branch_id' => 'required|exists:branches,id',
            'note' => 'nullable|string',
        ]);

        $user = auth()->user();
        $service = Service::findOrFail($validated['service_id']);

        $fromBranchId = (int) ($service->currentCustodyBranchId() ?? $service->branch_id);
        $toBranchId = (int) $validated['to_branch_id'];

        if (!BranchAccessService::canAccess($user, $fromBranchId)) {
            abort(403, 'Anda hanya bisa mentransfer servis dari cabang yang berwenang.');
        }
        if (!BranchAccessService::canAccess($user, $toBranchId)) {
            abort(403, 'Cabang tujuan di luar jangkauan Anda.');
        }
        if ($fromBranchId === $toBranchId) {
            return back()->with('error', 'Cabang tujuan sama dengan cabang custody saat ini.');
        }

        $transfer = ServiceTransfer::create([
            'service_id' => $service->id,
            'from_branch_id' => $fromBranchId,
            'to_branch_id' => $toBranchId,
            'note' => $validated['note'] ?? null,
            'transferred_by' => $user->id,
            'status' => ServiceTransfer::STATUS_REQUESTED,
            'requested_by' => $user->id,
        ]);

        ActivityLog::log(
            'service_transfer_requested',
            "Transfer servis #{$service->tracking_code} dari cabang #{$fromBranchId} ke cabang #{$toBranchId}",
            $service,
            ['transfer_id' => $transfer->id, 'from_branch_id' => $fromBranchId, 'to_branch_id' => $toBranchId, 'by' => $user->id]
        );

        return back()->with('success', 'Transfer servis diminta. Origin cabang tidak berubah.');
    }

    /** Send the transfer (requested → sent). Only the origin/custody branch. */
    public function send(ServiceTransfer $transfer)
    {
        $user = auth()->user();
        if (!BranchAccessService::canAccess($user, $transfer->from_branch_id)) {
            abort(403, 'Hanya cabang asal yang dapat mengirim transfer.');
        }

        try {
            $transfer->send($user->id);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        ActivityLog::log(
            'service_transfer_sent',
            "Transfer servis #{$transfer->service_id} dikirim ke cabang #{$transfer->to_branch_id}",
            $transfer->service,
            ['transfer_id' => $transfer->id, 'from_branch_id' => $transfer->from_branch_id, 'to_branch_id' => $transfer->to_branch_id, 'by' => $user->id]
        );

        return back()->with('success', 'Transfer dikirim.');
    }

    /** Receive the transfer (sent → received). Only the destination branch. */
    public function receive(ServiceTransfer $transfer)
    {
        $user = auth()->user();
        if (!BranchAccessService::canAccess($user, $transfer->to_branch_id)) {
            abort(403, 'Hanya cabang tujuan yang dapat menerima transfer.');
        }

        // Idempotency: an already-received transfer is a no-op (no duplicate
        // audit / side effects).
        if ($transfer->status === ServiceTransfer::STATUS_RECEIVED) {
            return back()->with('info', 'Transfer sudah diterima sebelumnya.');
        }

        try {
            $transfer->receive($user->id);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        ActivityLog::log(
            'service_transfer_received',
            "Transfer servis #{$transfer->service_id} diterima di cabang #{$transfer->to_branch_id}. Custody pindah.",
            $transfer->service,
            ['transfer_id' => $transfer->id, 'from_branch_id' => $transfer->from_branch_id, 'to_branch_id' => $transfer->to_branch_id, 'by' => $user->id]
        );

        return back()->with('success', 'Transfer diterima. Servis kini dalam custody cabang tujuan.');
    }

    /** Cancel an open transfer. Only the origin/custody branch. */
    public function cancel(Request $request, ServiceTransfer $transfer)
    {
        $user = auth()->user();
        if (!BranchAccessService::canAccess($user, $transfer->from_branch_id)) {
            abort(403, 'Hanya cabang asal yang dapat membatalkan transfer.');
        }

        $reason = $request->input('reason');
        try {
            $transfer->cancel($user->id, $reason);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        ActivityLog::log(
            'service_transfer_cancelled',
            "Transfer servis #{$transfer->service_id} dibatalkan",
            $transfer->service,
            ['transfer_id' => $transfer->id, 'reason' => $reason, 'by' => $user->id]
        );

        return back()->with('success', 'Transfer dibatalkan.');
    }
}
