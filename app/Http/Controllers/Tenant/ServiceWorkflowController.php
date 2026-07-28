<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\ServicePhoto;
use App\Services\GoogleDrivePhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceWorkflowController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('create', Service::class);

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'problem_description' => 'nullable|string', 'condition_note' => 'nullable|string',
            'checklist_template_id' => 'nullable|exists:checklist_templates,id',
            'checked_items' => 'nullable|array',
            'jalur_kedatangan_id' => 'nullable|exists:master_data,id',
            'kategori_perangkat_id' => 'nullable|exists:master_data,id',
            'merek_id' => 'nullable|exists:master_data,id',
            'tipe_unit' => 'nullable|string|max:100', 'imei_sn' => 'nullable|string|max:100',
            'sandi_pola' => 'nullable|string|max:50', 'kelengkapan' => 'nullable|array',
        ]);

        $user = Auth::user();
        $userCount = \App\Models\Tenant\User::count();
        $status = Service::STATUS_MENUNGGU_ALOKASI;
        $technicianId = null;

        if ($userCount <= 1 || $user->isOwner()) {
            $technicianId = $user->id;
            $status = Service::STATUS_DIKERJAKAN;
        } elseif ($user->canWorkOnServices()) {
            if (!\App\Models\Tenant\User::where('role', 'technician')->exists()) {
                $technicianId = $user->id;
                $status = Service::STATUS_DIKERJAKAN;
            }
        }

        $service = Service::create(array_merge($validated, [
            'branch_id' => $user->branch_id, 'created_by' => $user->id,
            'technician_id' => $technicianId, 'status' => $status,
            'problem_description' => $validated['problem_description'] ?? '',
            'condition_note' => $validated['condition_note'] ?? '',
        ]));

        if ($request->filled('checklist_template_id')) {
            $service->checklists()->create(['checklist_template_id' => $validated['checklist_template_id'], 'type' => 'masuk', 'checked_items' => $validated['checked_items'] ?? []]);
        }

        if ($request->hasFile('photos')) {
            $driveService = new GoogleDrivePhotoService(tenancy()->tenant->id);
            if (!$driveService->isConnected()) {
                return redirect()->route('services.show', $service->id)->with('error', 'Upload foto membutuhkan Google Drive.');
            }
            foreach ($request->file('photos') as $file) {
                $path = $file->store('services/' . $service->id, 'public');
                $driveUrl = $driveService->upload(storage_path('app/public/' . $path), 'service_' . $service->id . '_' . time() . '_' . uniqid() . '.jpg', 'services');
                ServicePhoto::create(['service_id' => $service->id, 'photo_path' => $driveUrl ?: $path, 'uploaded_by' => auth()->id()]);
            }
        }

        ActivityLog::log('created', 'Membuat servis baru #' . $service->id, $service, ['customer_id' => $service->customer_id, 'status' => $status]);
        return redirect()->route('services.show', $service->id)->with('success', 'Servis berhasil dibuat.');
    }

    public function accept(Service $service)
    {
        $this->authorize('accept', $service);
        $user = Auth::user();
        $service->update(['technician_id' => $user->id, 'status' => Service::STATUS_DITERIMA]);
        ActivityLog::log('accepted', $user->name . ' menerima servis #' . $service->id, $service);
        return back()->with('success', 'Anda menerima servis #' . $service->id);
    }

    public function start(Service $service)
    {
        $this->authorize('start', $service);
        $user = Auth::user();
        $service->update(['status' => Service::STATUS_DIKERJAKAN]);
        ActivityLog::log('started', $user->name . ' mulai mengerjakan servis #' . $service->id, $service);
        return back()->with('success', 'Pekerjaan dimulai.');
    }

    public function finish(Service $service)
    {
        $this->authorize('finish', $service);
        $user = Auth::user();
        $service->update(['status' => Service::STATUS_SELESAI]);
        ActivityLog::log('finished', $user->name . ' menyelesaikan pekerjaan servis #' . $service->id, $service);
        return back()->with('success', 'Pekerjaan selesai. Servis siap dibuatkan nota.');
    }

    public function cancel(Service $service)
    {
        $this->authorize('cancel', $service);
        $user = Auth::user();
        $allowedCancelStatuses = [
            Service::STATUS_MENUNGGU_ALOKASI,
            Service::STATUS_DITERIMA,
            Service::STATUS_DIAGNOSA,
            Service::STATUS_DIKERJAKAN,
            Service::STATUS_KONFIRMASI_PELANGGAN,
            Service::STATUS_KONFIRMASI_INTERNAL,
            Service::STATUS_INDENT,
            Service::STATUS_ONPARTNER,
        ];
        if (!in_array($service->status, $allowedCancelStatuses)) return back()->with('error', 'Servis tidak dapat dibatalkan dari status ini.');
        $service->update(['status' => Service::STATUS_CANCEL]);
        ActivityLog::log('cancelled', $user->name . ' membatalkan servis #' . $service->id, $service);
        return back()->with('success', 'Servis dibatalkan.');
    }

    public function requestReallocation(Service $service)
    {
        $this->authorize('reallocate', $service);
        $user = Auth::user();
        $service->update(['technician_id' => null, 'status' => Service::STATUS_MENUNGGU_ALOKASI]);
        ActivityLog::log('reallocated', $user->name . ' melepas servis #' . $service->id, $service);
        return back()->with('success', 'Servis dikembalikan ke antrian alokasi.');
    }

    public function confirmCustomer(Service $service)
    {
        $this->authorize('confirm', $service);
        $user = Auth::user();
        $service->update(['status' => Service::STATUS_KONFIRMASI_PELANGGAN]);
        ActivityLog::log('confirm_customer', 'Menunggu konfirmasi pelanggan servis #' . $service->id, $service);
        return back()->with('success', 'Menunggu konfirmasi pelanggan.');
    }

    public function confirmInternal(Service $service)
    {
        $this->authorize('confirm', $service);
        $user = Auth::user();
        $service->update(['status' => Service::STATUS_KONFIRMASI_INTERNAL]);
        ActivityLog::log('confirm_internal', 'Menunggu konfirmasi internal servis #' . $service->id, $service);
        return back()->with('success', 'Menunggu persetujuan owner.');
    }

    public function approveConfirmation(Service $service)
    {
        $this->authorize('approve', $service);
        $user = Auth::user();
        $service->update(['status' => Service::STATUS_DIKERJAKAN]);
        ActivityLog::log('confirmed', 'Konfirmasi disetujui untuk servis #' . $service->id, $service);
        return back()->with('success', 'Konfirmasi disetujui.');
    }

    public function takeOver(Service $service)
    {
        $this->authorize('takeOver', $service);
        $user = Auth::user();
        $service->update(['technician_id' => $user->id, 'status' => Service::STATUS_DIKERJAKAN]);
        ActivityLog::log('taken_over', $user->name . ' mengambil alih servis #' . $service->id, $service);
        return back()->with('success', 'Anda mengambil alih servis #' . $service->id);
    }

    public function partner(Request $request, Service $service)
    {
        $this->authorize('partner', $service);
        $user = Auth::user();
        $validated = $request->validate(['partner_note' => 'nullable|string|max:1000']);
        $note = $validated['partner_note'] ?? 'Dikerjakan oleh partner';
        $service->update(['status' => Service::STATUS_ONPARTNER, 'condition_note' => $service->condition_note ? $service->condition_note . "\n[Partner] " . $note : '[Partner] ' . $note]);
        ActivityLog::log('partnered', 'Servis #' . $service->id . ' dialokasikan ke partner', $service);
        return back()->with('success', 'Servis dialokasikan ke partner.');
    }

    public function completePartner(Service $service)
    {
        $this->authorize('partner', $service);
        $user = Auth::user();
        if ($service->status !== Service::STATUS_ONPARTNER) return back()->with('error', 'Servis tidak dalam status partner.');
        $service->update(['status' => Service::STATUS_SELESAI]);
        ActivityLog::log('partner_completed', 'Partner selesaikan servis #' . $service->id, $service);
        return back()->with('success', 'Pengerjaan partner selesai.');
    }

    public function assignTechnician(Request $request, Service $service)
    {
        $this->authorize('assign', $service);
        $user = Auth::user();
        $validated = $request->validate(['technician_id' => 'required|exists:users,id']);
        $service->update(['technician_id' => $validated['technician_id'], 'status' => Service::STATUS_DIKERJAKAN]);
        $newTech = \App\Models\Tenant\User::find($validated['technician_id']);
        ActivityLog::log('assigned', 'Menugaskan ' . ($newTech?->name ?? 'teknisi') . ' ke servis #' . $service->id, $service);
        return back()->with('success', 'Teknisi berhasil ditugaskan.');
    }

    public function setIndent(Service $service)
    {
        $this->authorize('update', $service);
        $user = Auth::user();
        if (!in_array($service->status, [Service::STATUS_DIKERJAKAN, Service::STATUS_MENUNGGU_ALOKASI])) return back()->with('error', 'Servis tidak dapat diindent dari status ini.');
        $service->update(['status' => Service::STATUS_INDENT]);
        ActivityLog::log('indent', 'Servis #' . $service->id . ' diindent (menunggu sparepart).', $service);
        return back()->with('success', 'Servis diindent, menunggu sparepart.');
    }

    public function resumeFromIndent(Service $service)
    {
        $this->authorize('update', $service);
        $user = Auth::user();
        if ($service->status !== Service::STATUS_INDENT) return back()->with('error', 'Servis tidak dalam status indent.');
        $service->update(['status' => Service::STATUS_DIKERJAKAN]);
        ActivityLog::log('resumed', 'Servis #' . $service->id . ' dilanjutkan dari indent.', $service);
        return back()->with('success', 'Servis dilanjutkan dari indent.');
    }

    public function bulkUpdateStatus(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:services,id',
            'status' => 'required|string',
        ]);

        $services = Service::whereIn('id', $validated['ids'])->get();
        $count = 0;
        foreach ($services as $service) {
            $service->update(['status' => $validated['status']]);
            ActivityLog::log('status_updated', 'Bulk update status ke ' . $validated['status'] . ' untuk servis #' . $service->id, $service);
            $count++;
        }

        return back()->with('success', "Berhasil memperbarui status {$count} servis.");
    }
}
