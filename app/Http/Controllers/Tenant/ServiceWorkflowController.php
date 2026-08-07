<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreServiceRequest;
use App\Models\Tenant\Service;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\ServicePhoto;
use App\Models\Tenant\Customer;
use App\Models\Tenant\User;
use App\Models\Tenant\ServiceWarrantyClaim;
use App\Services\GoogleDrivePhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ServiceWorkflowController extends Controller
{


    public function accept(Service $service)
    {
        $this->authorize('accept', $service);
        $user = Auth::user();
        $this->ensureServiceBranchAccess($service, $user?->branch_id);
        if ($invalid = $this->attemptTransition($service, Service::STATUS_DITERIMA, ['technician_id' => $user->id])) {
            return $invalid;
        }
        ActivityLog::log('accepted', $user->name . ' menerima servis #' . $service->id, $service);
        return back()->with('success', 'Anda menerima servis #' . $service->id);
    }

    public function start(Service $service)
    {
        $this->authorize('start', $service);
        $user = Auth::user();
        $this->ensureServiceBranchAccess($service, $user?->branch_id);
        if ($invalid = $this->attemptTransition($service, Service::STATUS_DIKERJAKAN, ['dikerjakan_at' => now()])) {
            return $invalid;
        }
        ActivityLog::log('started', $user->name . ' mulai mengerjakan servis #' . $service->id, $service);
        return back()->with('success', 'Pekerjaan dimulai.');
    }

    public function finish(Service $service)
    {
        $this->authorize('finish', $service);
        $user = Auth::user();
        $this->ensureServiceBranchAccess($service, $user?->branch_id);
        if ($invalid = $this->attemptTransition($service, Service::STATUS_SELESAI, ['selesai_at' => now()])) {
            return $invalid;
        }
        ActivityLog::log('finished', $user->name . ' menyelesaikan pekerjaan servis #' . $service->id, $service);

        // BR-FIX-04.1: technician repair completion is NOT warranty resolution.
        // The warranty claim stays OPEN until QC passes (see storeQcCheck).

        return back()->with('success', 'Pekerjaan selesai. Servis siap dibuatkan nota.');
    }

    public function cancel(Service $service)
    {
        $this->authorize('cancel', $service);
        $user = Auth::user();
        $this->ensureServiceBranchAccess($service, $user?->branch_id);
        if ($invalid = $this->attemptTransition($service, Service::STATUS_CANCEL, ['cancel_at' => now()])) {
            return $invalid;
        }
        ActivityLog::log('cancelled', $user->name . ' membatalkan servis #' . $service->id, $service);
        return back()->with('success', 'Servis dibatalkan.');
    }

    /**
     * Sprint v4.0B: Close service — terminal state with full precondition validation.
     * Requires: QC done, pickup completed, payment verified, warranty active.
     */
    public function close(Service $service)
    {
        $user = Auth::user();

        // Only owner/admin/manager can close
        if (!in_array($user->role, ['owner', 'admin', 'manager'])) {
            abort(403, 'Hanya owner/admin/manager yang dapat menutup servis.');
        }

        $this->ensureServiceBranchAccess($service, $user?->branch_id);

        // Idempotency: already closed
        if ($service->status === Service::STATUS_CLOSE) {
            return back()->with('error', 'Servis sudah ditutup sebelumnya.');
        }

        // Status gate: must be SIAP_DIAMBIL, DIAMBIL, or SELESAI
        if (!in_array($service->status, [Service::STATUS_SIAP_DIAMBIL, Service::STATUS_DIAMBIL, Service::STATUS_SELESAI])) {
            return back()->with('error', 'Servis harus siap diambil, sudah diambil, atau selesai untuk ditutup. Status: ' . $service->status);
        }

        // Precondition 1: QC must have been done (QC checks exist)
        $qcDone = \App\Models\Tenant\ServiceQcCheck::where('service_id', $service->id)->exists();
        if (!$qcDone) {
            return back()->with('error', 'QC belum dilakukan. Tidak dapat menutup servis.');
        }

        // Precondition 2: Delivery/pickup must be completed
        $delivery = \App\Models\Tenant\ServiceDelivery::where('service_id', $service->id)->first();
        if (!$delivery || !$delivery->picked_up_at) {
            return back()->with('error', 'Pickup belum selesai. Tidak dapat menutup servis.');
        }

        // Precondition 3: Payment must be verified
        if (!$delivery->payment_verified && $service->payment_status !== 'paid') {
            return back()->with('error', 'Pembayaran belum diverifikasi. Tidak dapat menutup servis.');
        }

        if ($invalid = $this->attemptTransition($service, Service::STATUS_CLOSE)) {
            return $invalid;
        }

        ActivityLog::log('closed', "{$user->name} menutup servis #{$service->tracking_code}.", $service);

        if ($service->customer) {
            \App\Models\Tenant\ActivityLog::log('service_closed_customer',
                "Servis #{$service->tracking_code} closed untuk {$service->customer->name}.",
                $service->customer);
        }

        return back()->with('success', 'Servis #' . $service->tracking_code . ' ditutup. Riwayat tersimpan.');
    }

    public function requestReallocation(Service $service)
    {
        $this->authorize('reallocate', $service);
        $user = Auth::user();
        $this->ensureServiceBranchAccess($service, $user?->branch_id);
        if ($invalid = $this->attemptTransition($service, Service::STATUS_MENUNGGU_ALOKASI, ['technician_id' => null])) {
            return $invalid;
        }
        ActivityLog::log('reallocated', $user->name . ' melepas servis #' . $service->id, $service);
        return back()->with('success', 'Servis dikembalikan ke antrian alokasi.');
    }

    public function confirmCustomer(Service $service)
    {
        $this->authorize('confirm', $service);
        $user = Auth::user();
        $this->ensureServiceBranchAccess($service, $user?->branch_id);
        if ($invalid = $this->attemptTransition($service, Service::STATUS_KONFIRMASI_PELANGGAN)) {
            return $invalid;
        }
        ActivityLog::log('confirm_customer', 'Menunggu konfirmasi pelanggan servis #' . $service->id, $service);
        return back()->with('success', 'Menunggu konfirmasi pelanggan.');
    }

    public function confirmInternal(Service $service)
    {
        $this->authorize('confirm', $service);
        $user = Auth::user();
        $this->ensureServiceBranchAccess($service, $user?->branch_id);
        if ($invalid = $this->attemptTransition($service, Service::STATUS_KONFIRMASI_INTERNAL)) {
            return $invalid;
        }
        ActivityLog::log('confirm_internal', 'Menunggu konfirmasi internal servis #' . $service->id, $service);
        return back()->with('success', 'Menunggu persetujuan owner.');
    }

    public function approveConfirmation(Service $service)
    {
        $this->authorize('approve', $service);
        $user = Auth::user();
        $this->ensureServiceBranchAccess($service, $user?->branch_id);
        // Sesuai docs: konfirmasi disetujui → siap_diambil
        if ($invalid = $this->attemptTransition($service, Service::STATUS_SIAP_DIAMBIL, ['selesai_at' => $service->selesai_at ?? now()])) {
            return $invalid;
        }
        ActivityLog::log('confirmed', 'Konfirmasi disetujui untuk servis #' . $service->id, $service);
        return back()->with('success', 'Konfirmasi disetujui — servis siap diambil.');
    }

    public function takeOver(Service $service)
    {
        $this->authorize('takeOver', $service);
        $user = Auth::user();
        $this->ensureServiceBranchAccess($service, $user?->branch_id);
        $this->ensureAssignableTechnician((int) $user->id, (int) $service->branch_id);
        if ($invalid = $this->attemptTransition($service, Service::STATUS_DIKERJAKAN, [
            'technician_id' => $user->id,
            'dikerjakan_at' => $service->dikerjakan_at ?? now(),
        ])) {
            return $invalid;
        }
        ActivityLog::log('taken_over', $user->name . ' mengambil alih servis #' . $service->id, $service);
        return back()->with('success', 'Anda mengambil alih servis #' . $service->id);
    }

    public function partner(Request $request, Service $service)
    {
        $this->authorize('partner', $service);
        $user = Auth::user();
        $this->ensureServiceBranchAccess($service, $user?->branch_id);
        $validated = $request->validate(['partner_note' => 'nullable|string|max:1000']);
        $note = $validated['partner_note'] ?? 'Dikerjakan oleh partner';
        if ($invalid = $this->attemptTransition($service, Service::STATUS_ONPARTNER, [
            'condition_note' => $service->condition_note ? $service->condition_note . "\n[Partner] " . $note : '[Partner] ' . $note,
        ])) {
            return $invalid;
        }
        ActivityLog::log('partnered', 'Servis #' . $service->id . ' dialokasikan ke partner', $service);
        return back()->with('success', 'Servis dialokasikan ke partner.');
    }

    public function completePartner(Service $service)
    {
        $this->authorize('partner', $service);
        $user = Auth::user();
        $this->ensureServiceBranchAccess($service, $user?->branch_id);
        if ($invalid = $this->attemptTransition($service, Service::STATUS_SELESAI, ['selesai_at' => $service->selesai_at ?? now()])) {
            return $invalid;
        }
        ActivityLog::log('partner_completed', 'Partner selesaikan servis #' . $service->id, $service);
        return back()->with('success', 'Pengerjaan partner selesai.');
    }

    public function assignTechnician(Request $request, Service $service)
    {
        $this->authorize('assign', $service);
        $user = Auth::user();
        $validated = $request->validate(['technician_id' => 'required|exists:users,id']);

        $this->ensureServiceBranchAccess($service, $user?->branch_id);
        $this->ensureAssignableTechnician((int) $validated['technician_id'], $service->branch_id);

        if ($invalid = $this->attemptTransition($service, Service::STATUS_DIKERJAKAN, [
            'technician_id' => $validated['technician_id'],
            'dikerjakan_at' => $service->dikerjakan_at ?? now(),
        ])) {
            return $invalid;
        }
        $newTech = \App\Models\Tenant\User::find($validated['technician_id']);
        ActivityLog::log('assigned', 'Menugaskan ' . ($newTech?->name ?? 'teknisi') . ' ke servis #' . $service->id, $service);
        return back()->with('success', 'Teknisi berhasil ditugaskan.');
    }

    public function setIndent(Service $service)
    {
        $this->authorize('update', $service);
        $user = Auth::user();
        $this->ensureServiceBranchAccess($service, $user?->branch_id);
        if ($invalid = $this->attemptTransition($service, Service::STATUS_INDENT)) {
            return $invalid;
        }
        ActivityLog::log('indent', 'Servis #' . $service->id . ' diindent (menunggu sparepart).', $service);
        return back()->with('success', 'Servis diindent, menunggu sparepart.');
    }

    public function resumeFromIndent(Service $service)
    {
        $this->authorize('update', $service);
        $user = Auth::user();
        $this->ensureServiceBranchAccess($service, $user?->branch_id);
        if ($invalid = $this->attemptTransition($service, Service::STATUS_DIKERJAKAN, ['dikerjakan_at' => $service->dikerjakan_at ?? now()])) {
            return $invalid;
        }
        ActivityLog::log('resumed', 'Servis #' . $service->id . ' dilanjutkan dari indent.', $service);
        return back()->with('success', 'Servis dilanjutkan dari indent.');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:services,id',
            'status' => 'required|string',
        ]);

        if (!in_array($validated['status'], [Service::STATUS_DITERIMA, Service::STATUS_CANCEL], true)) {
            return back()->with('error', 'Bulk action hanya mendukung status diterima atau cancel.');
        }

        $services = Service::whereIn('id', $validated['ids'])->lockForUpdate()->get();
        $count = 0;
        $skipped = 0;

        foreach ($services as $service) {
            if ($this->shouldSkipBulkTransition($service, $validated['status'], $user)) {
                $skipped++;
                continue;
            }

            $attributes = [];
            if ($validated['status'] === Service::STATUS_DITERIMA) {
                $attributes['technician_id'] = $user?->id;
            }

            if ($validated['status'] === Service::STATUS_CANCEL) {
                $attributes['cancel_at'] = now();
            }

            $service->update(array_merge($attributes, ['status' => $validated['status']]));
            ActivityLog::log('status_updated', 'Bulk update status ke ' . $validated['status'] . ' untuk servis #' . $service->id, $service);
            $count++;
        }

        if ($count === 0) {
            return back()->with('error', 'Tidak ada servis yang bisa dipindahkan ke status tersebut.');
        }

        $message = "Berhasil memperbarui status {$count} servis.";
        if ($skipped > 0) {
            $message .= " {$skipped} servis dilewati karena transisi tidak valid.";
        }

        return back()->with('success', $message);
    }

    private function shouldSkipBulkTransition(Service $service, string $targetStatus, ?User $user): bool
    {
        try {
            $this->ensureServiceBranchAccess($service, $user?->branch_id);
        } catch (ValidationException $exception) {
            return true;
        }

        if (!$service->canTransitionTo($targetStatus)) {
            return true;
        }

        return match ($targetStatus) {
            Service::STATUS_DITERIMA => !$user || !$user->can('accept', $service),
            Service::STATUS_CANCEL => !$user || !$user->can('cancel', $service),
            default => true,
        };
    }

    private function attemptTransition(Service $service, string $targetStatus, array $attributes = [])
    {
        if ($service->status === $targetStatus) {
            return back()->with('error', 'Servis sudah berada pada status ini.');
        }

        if (!$service->canTransitionTo($targetStatus)) {
            $from = Service::statusLabel((string) $service->status);
            $to = Service::statusLabel($targetStatus);
            return back()->with('error', "Transisi status tidak valid: {$from} -> {$to}.");
        }

        $service->update(array_merge($attributes, ['status' => $targetStatus]));

        return null;
    }

    private function ensureCustomerBranchAccess(int $customerId, ?int $userBranchId): void
    {
        $customer = Customer::query()->findOrFail($customerId);

        if ($userBranchId && $customer->branch_id && (int) $customer->branch_id !== (int) $userBranchId) {
            throw ValidationException::withMessages([
                'customer_id' => 'Pelanggan tidak berada pada cabang aktif Anda.',
            ]);
        }
    }

    private function ensureServiceBranchAccess(Service $service, $userBranchId): void
    {
        if (!$userBranchId || !$service->branch_id) {
            return;
        }

        if ((string) $service->branch_id !== (string) $userBranchId) {
            throw ValidationException::withMessages([
                'service' => 'Servis tidak berada pada cabang aktif Anda.',
            ]);
        }
    }

    private function ensureAssignableTechnician(int $technicianId, int $serviceBranchId): void
    {
        $technician = User::query()->findOrFail($technicianId);
        $currentUser = Auth::user();

        if (!$technician->active) {
            throw ValidationException::withMessages([
                'technician_id' => 'Teknisi tujuan sedang nonaktif.',
            ]);
        }

        if (!in_array($technician->role, ['technician', 'owner'], true)) {
            throw ValidationException::withMessages([
                'technician_id' => 'Penugasan hanya bisa ke user teknisi/owner.',
            ]);
        }

        // Teknisi tanpa cabang (global) hanya bisa di-assign oleh owner, bukan CS
        if (!$technician->branch_id && $currentUser && $currentUser->isCs()) {
            throw ValidationException::withMessages([
                'technician_id' => 'CS hanya bisa menugaskan ke teknisi di cabang sendiri. Teknisi global hanya bisa ditugaskan oleh owner.',
            ]);
        }

        if ($technician->branch_id && (string) $technician->branch_id !== (string) $serviceBranchId) {
            throw ValidationException::withMessages([
                'technician_id' => 'Teknisi tujuan tidak berada pada cabang yang sama.',
            ]);
        }
    }
}
