<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\WorkOrder;
use App\Models\Tenant\ServiceDiagnosis;
use App\Models\Tenant\ServiceQuotation;
use App\Models\Tenant\ServiceRequiredPart;
use App\Models\Tenant\ServiceQcCheck;
use Illuminate\Http\Request;

/**
 * Technician Workflow Controller — Sprint 7.3F.
 * All methods delegate to existing models, WorkflowEngine, and Event Platform.
 */
class TechnicianWorkflowController extends Controller
{
    // ======== WORK ORDER ========

    /** Assign technician to a work order */
    public function assignTechnician(Request $request, WorkOrder $workOrder)
    {
        $this->authorize('update', $workOrder->service);
        $data = $request->validate(['technician_id' => 'required|exists:users,id']);
        $workOrder->assign($data['technician_id']);
        return back()->with('success', 'Teknisi berhasil ditugaskan.');
    }

    /** Technician accepts work order */
    public function accept(WorkOrder $workOrder)
    {
        $workOrder->accept();
        return back()->with('success', 'Work order diterima.');
    }

    /** Technician dashboard — list my work orders grouped by status */
    public function technicianDashboard()
    {
        $user = auth()->user();
        $workOrders = WorkOrder::with(['service.customer', 'service.device', 'service.intakeSnapshot'])
            ->forTechnician($user->id)
            ->active()
            ->latest()
            ->get()
            ->groupBy('status');

        return inertia('Technician/Dashboard', [
            'workOrders' => $workOrders,
            'waiting' => $workOrders->get('assigned', collect()),
            'inProgress' => $workOrders->get('in_progress', collect()),
            'waitingPart' => $workOrders->get('waiting_part', collect()),
            'qc' => $workOrders->get('qc', collect()),
        ]);
    }

    // ======== DIAGNOSIS ========

    public function storeDiagnosis(Request $request, Service $service)
    {
        $this->authorize('update', $service);
        $data = $request->validate([
            'customer_complaint' => 'nullable|string',
            'findings' => 'required|string',
            'cause' => 'nullable|string',
            'solution' => 'required|string',
            'estimated_cost' => 'nullable|numeric|min:0',
            'estimated_minutes' => 'nullable|integer|min:1',
        ]);

        $diagnosis = $service->diagnosis()->updateOrCreate(
            ['service_id' => $service->id],
            $data + ['diagnosed_by' => auth()->id()]
        );

        event(new \App\Events\Entity\DiagnosisCompleted($diagnosis));
        return back()->with('success', 'Diagnosis tersimpan.');
    }

    // ======== QUOTATION ========

    public function createQuotation(Request $request, Service $service)
    {
        $this->authorize('update', $service);
        $data = $request->validate([
            'total_cost' => 'required|numeric|min:0',
            'items' => 'nullable|array',
            'notes' => 'nullable|string',
        ]);

        $quotation = ServiceQuotation::create([
            'service_id' => $service->id,
            'total_cost' => $data['total_cost'],
            'items' => $data['items'] ?? null,
            'notes' => $data['notes'] ?? null,
            'status' => 'sent',
            'created_by' => auth()->id(),
        ]);

        $service->update(['estimated_cost' => $data['total_cost'], 'approval_status' => 'pending']);

        event(new \App\Events\Entity\QuotationCreated($quotation));
        return back()->with('success', 'Quotation dibuat. Menunggu approval.');
    }

    public function approveQuotation(Request $request, ServiceQuotation $quotation)
    {
        $this->authorize('update', $quotation->service);
        $method = $request->input('method', 'cs');
        $quotation->approve(auth()->id(), $method);
        $quotation->service->update(['approval_status' => 'approved', 'approved_by' => auth()->id(), 'approved_at' => now()]);

        event(new \App\Events\Entity\CustomerApprovedRepair($quotation));
        return back()->with('success', 'Quotation disetujui.');
    }

    // ======== PARTS ========

    public function requestPart(Request $request, Service $service)
    {
        $this->authorize('update', $service);
        $data = $request->validate([
            'part_name' => 'required|string',
            'qty' => 'required|integer|min:1',
            'product_id' => 'nullable|exists:products,id',
            'notes' => 'nullable|string',
        ]);

        $part = $service->requiredParts()->create($data + ['status' => 'requested']);
        return back()->with('success', 'Part requested.');
    }

    // ======== QC ========

    public function storeQcCheck(Request $request, Service $service)
    {
        $this->authorize('update', $service);
        $data = $request->validate([
            'checks' => 'required|array',
            'checks.*.item' => 'required|string',
            'checks.*.result' => 'required|in:pending,pass,fail',
            'checks.*.notes' => 'nullable|string',
        ]);

        foreach ($data['checks'] as $check) {
            ServiceQcCheck::updateOrCreate(
                ['service_id' => $service->id, 'item' => $check['item']],
                ['result' => $check['result'], 'notes' => $check['notes'] ?? null, 'checked_by' => auth()->id()]
            );
        }

        event(new \App\Events\Entity\QCCompleted($service));
        return back()->with('success', 'QC tersimpan.');
    }

    // ======== REPAIR ========

    public function startRepair(Service $service)
    {
        $this->authorize('update', $service);
        event(new \App\Events\Entity\RepairStarted($service));
        return back()->with('success', 'Perbaikan dimulai.');
    }

    public function completeRepair(Service $service)
    {
        $this->authorize('update', $service);
        event(new \App\Events\Entity\RepairCompleted($service));
        return back()->with('success', 'Perbaikan selesai. Lanjut ke QC.');
    }
}
