<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceChecklistResult;
use App\Models\Tenant\ServiceIntakeSnapshot;
use App\Models\Tenant\Device;
use App\Http\Requests\Tenant\StoreServiceRequest;
use App\Models\Tenant\Customer;
use App\Models\Tenant\ActivityLog;
use App\Models\Tenant\ServicePhoto;
use App\Models\Tenant\User;
use App\Services\GoogleDrivePhotoService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Request;

/**
 * Service Intake Controller — Sprint 7.3E-H.
 * Thin — delegates to models and engines.
 */
class ServiceIntakeController extends Controller
{
    public function store(StoreServiceRequest $request)
    {
        $this->authorize('create', Service::class);

        $user = Auth::user();

        // BR-FIX-03 (BR-001): intake is stamped to the user's primary branch,
        // so a GRANULAR delegation must cover that branch (role-based holders
        // of service.create must also have branch access — both are handled by
        // canViaPermissionInBranch). This is what makes a delegation branch-scoped.
        if (!$user->canViaPermissionInBranch('service.create', $user->branch_id)) {
            abort(403, 'Delegasi layanan tidak mencakup cabang ini.');
        }

        $validated = $request->validated();

        $customer = Customer::findOrFail($validated['customer_id']);
        $this->authorize('view', $customer);

        $service = DB::transaction(function () use ($validated, $user, $request) {

            // BR-FIX-02 — Customer binding guard: the customer must belong to a
            // branch the user may access (enforced above via CustomerPolicy →
            // BranchAccessService). A device matched by IMEI/SN may belong to a
            // DIFFERENT customer; reassigning it is a cross-branch side effect,
            // so the device's original customer must also be accessible.
            $device = null;
            if (!empty($validated['imei_sn'])) {
                $device = Device::where('imei', $validated['imei_sn'])
                    ->orWhere('serial_number', $validated['imei_sn'])
                    ->first();
            }

            if (!$device) {
                $device = Device::create([
                    'customer_id' => $validated['customer_id'],
                    'type' => $this->getMasterDataName($validated['kategori_perangkat_id'] ?? null),
                    'brand' => $this->getMasterDataName($validated['merek_id'] ?? null),
                    'model' => $validated['tipe_unit'] ?? 'Unknown',
                    'imei' => $validated['imei_sn'] ?? null,
                    'serial_number' => $validated['imei_sn'] ?? null,
                    'status' => 'active',
                ]);
            } else {
                if ($device->customer_id != $validated['customer_id']) {
                    $originalCustomer = $device->customer;
                    if ($originalCustomer && $originalCustomer->id !== $customer->id) {
                        // Authorize access to the device's current owner before
                        // reassigning it (prevents cross-branch device takeover).
                        $this->authorize('view', $originalCustomer);
                    }
                    $device->update(['customer_id' => $validated['customer_id']]);
                }
            }

            $userCount = User::count();
            $status = Service::STATUS_MENUNGGU_ALOKASI;
            $technicianId = null;

            if ($userCount <= 1 || $user->isOwner()) {
                $technicianId = $user->id;
                $status = Service::STATUS_DIKERJAKAN;
            } elseif ($user->canWorkOnServices()) {
                if (!User::where('role', 'technician')->exists()) {
                    $technicianId = $user->id;
                    $status = Service::STATUS_DIKERJAKAN;
                }
            }

            $service = Service::create(array_merge($validated, [
                'device_id' => $device->id,
                'branch_id' => $user->branch_id,
                'created_by' => $user->id,
                'technician_id' => $technicianId,
                'status' => $status,
                'problem_description' => $validated['problem_description'] ?? '',
                'condition_note' => $validated['condition_note'] ?? '',
                'dikerjakan_at' => $status === Service::STATUS_DIKERJAKAN ? now() : null,
            ]));

            if ($request->filled('checklist_template_id')) {
                $service->checklists()->create([
                    'checklist_template_id' => $validated['checklist_template_id'],
                    'type' => 'masuk',
                    'checked_items' => $validated['checked_items'] ?? []
                ]);
            }

            return $service;
        });

        if ($request->hasFile('photos')) {
            $driveService = new GoogleDrivePhotoService(tenancy()->tenant->id);
            if ($driveService->isConnected()) {
                foreach ($request->file('photos') as $file) {
                    $path = $file->store('services/' . $service->id, 'public');
                    $driveUrl = $driveService->upload(storage_path('app/public/' . $path), 'service_' . $service->id . '_' . time() . '_' . uniqid() . '.jpg', 'services');
                    ServicePhoto::create([
                        'service_id' => $service->id,
                        'photo_path' => $driveUrl ?: $path,
                        'uploaded_by' => auth()->id()
                    ]);
                }
            }
        }

        $service->saveCustomFieldValues($request->all());
        ServiceIntakeSnapshot::capture($service, false, null);
        event(new \App\Events\Entity\ServiceCreated($service));
        
        ActivityLog::log(
            'created',
            'Membuat servis baru (Intake) #' . $service->id,
            $service,
            ['customer_id' => $service->customer_id, 'device_id' => $service->device_id, 'status' => $service->status]
        );

        return redirect()->route('services.show', $service->id)->with('success', 'Servis berhasil dibuat.');
    }

    private function getMasterDataName($id): ?string
    {
        if (!$id) return null;
        $md = \App\Models\Tenant\MasterData::find($id);
        return $md ? $md->name : null;
    }



    /** Store checklist results for a service */
    public function storeChecklistResults(Request $request, Service $service)
    {
        $this->authorize('update', $service);
        $data = $request->validate([
            'results' => 'required|array',
            'results.*.checklist_item_id' => 'required|exists:checklist_items,id',
            'results.*.value' => 'required|string',
            'results.*.type' => 'nullable|string',
            'results.*.unit' => 'nullable|string',
            'results.*.notes' => 'nullable|string',
        ]);

        foreach ($data['results'] as $r) {
            ServiceChecklistResult::updateOrCreate(
                ['service_id' => $service->id, 'checklist_item_id' => $r['checklist_item_id']],
                ['value' => $r['value'], 'type' => $r['type'] ?? 'checkbox', 'unit' => $r['unit'] ?? null, 'notes' => $r['notes'] ?? null, 'created_by' => auth()->id()]
            );
        }

        event(new \App\Events\Entity\ChecklistResultCreated($service));
        return back()->with('success', 'Hasil checklist disimpan.');
    }

    /** Capture intake snapshot (freeze condition) */
    public function captureSnapshot(Request $request, Service $service)
    {
        $this->authorize('update', $service);
        $data = $request->validate([
            'customer_confirmed' => 'boolean',
            'signature_image' => 'nullable|string',
        ]);

        $snapshot = ServiceIntakeSnapshot::capture(
            $service,
            $data['customer_confirmed'] ?? false,
            $data['signature_image'] ?? null,
        );

        if ($data['customer_confirmed'] ?? false) {
            $service->update([
                'customer_confirmed' => true,
                'signature_image' => $data['signature_image'] ?? null,
                'confirmed_at' => now(),
            ]);
        }

        event(new \App\Events\Entity\ServiceSnapshotCreated($snapshot));
        return back()->with('success', 'Kondisi awal tersimpan permanen.');
    }

    /** Confirm condition by customer */
    public function confirmCondition(Request $request, Service $service)
    {
        $this->authorize('update', $service);
        $service->update([
            'customer_confirmed' => true,
            'confirmed_at' => now(),
        ]);

        // Also update snapshot if exists
        if ($snapshot = $service->intakeSnapshot) {
            $snapshot->update(['customer_confirmed' => true, 'confirmed_at' => now()]);
        }

        event(new \App\Events\Entity\CustomerApprovedCondition($service));
        return back()->with('success', 'Customer telah menyetujui kondisi awal.');
    }

    /** Check if device IMEI already exists */
    public function matchDevice(Request $request)
    {
        $imei = $request->query('imei', '');
        if (strlen($imei) < 5) return response()->json(['found' => false]);

        $device = Device::with('customer')->where('imei', $imei)->first();
        if (!$device) return response()->json(['found' => false]);

        $customer = $device->customer;
        event(new \App\Events\Entity\DeviceMatchedExisting($device));

        return response()->json([
            'found' => true,
            'device' => [
                'id' => $device->id,
                'brand' => $device->brand,
                'model' => $device->model,
                'imei' => $device->imei,
                'serial_number' => $device->serial_number,
                'repair_count' => $device->repair_count,
                'last_service_date' => $device->last_service_date,
            ],
            'customer' => [
                'id' => $customer?->id,
                'name' => $customer?->name,
                'customer_code' => $customer?->customer_code,
                'service_count' => $customer?->serviceCount(),
            ],
        ]);
    }

    /** Get device health history for technician view */
    public function deviceHealth(Device $device)
    {
        return response()->json([
            'device' => $device->load('healthHistory'),
            'metrics' => DeviceHealthHistory::metrics(),
        ]);
    }
}
