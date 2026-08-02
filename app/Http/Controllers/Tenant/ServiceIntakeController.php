<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\ServiceChecklistResult;
use App\Models\Tenant\ServiceIntakeSnapshot;
use App\Models\Tenant\Device;
use Illuminate\Http\Request;

/**
 * Service Intake Controller — Sprint 7.3E-H.
 * Thin — delegates to models and engines.
 */
class ServiceIntakeController extends Controller
{
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
