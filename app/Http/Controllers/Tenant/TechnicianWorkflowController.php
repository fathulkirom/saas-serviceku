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

    /** Assign technician to a service */
    public function assignTechnician(Request $request, Service $service)
    {
        $this->authorize('update', $service);
        $data = $request->validate(['technician_id' => 'required|exists:users,id']);
        
        $service->update([
            'technician_id' => $data['technician_id']
        ]);
        
        if ($service->status === Service::STATUS_MENUNGGU_ALOKASI) {
            $this->transitionServiceStatus($service, Service::STATUS_DITERIMA);
        }

        // Keep WorkOrder in sync if exists
        $workOrder = WorkOrder::where('service_id', $service->id)->latest()->first();
        if ($workOrder) {
            $workOrder->assign($data['technician_id']);
        }

        \App\Models\Tenant\ActivityLog::log('technician_assigned', "Teknisi ditugaskan ke: " . \App\Models\Tenant\User::find($data['technician_id'])->name, $service);

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

        // Sprint v1.0: Transition service status to diagnosa
        if ($service->status === Service::STATUS_DITERIMA || $service->status === Service::STATUS_MENUNGGU_ALOKASI) {
            $this->transitionServiceStatus($service, Service::STATUS_DIAGNOSA);
        }

        event(new \App\Events\Entity\DiagnosisCompleted($diagnosis));
        return back()->with('success', 'Diagnosis tersimpan.');
    }

    // ======== QUOTATION ========

    public function createQuotation(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        // Sprint v2.0D: Authorization — only assigned technician or admin/owner/manager can create quotation
        $user = auth()->user();
        if (!in_array($user->role, ['owner', 'admin', 'manager']) && $service->technician_id !== $user->id) {
            abort(403, 'Hanya teknisi yang ditugaskan atau manager yang dapat membuat estimasi.');
        }

        $data = $request->validate([
            'items' => 'nullable|array',
            'items.*.product_id' => 'required_with:items|integer',
            'items.*.qty' => 'required_with:items|integer|min:1|max:999',
            'labor_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $items = $data['items'] ?? [];
        $serviceBranchId = $service->branch_id;

        // Sprint v2.0F: Branch-scoped product validation — reject products not accessible from service's branch.
        // Rule: product.branch_id = service.branch_id OR product.branch_id IS NULL (global product).
        if (!empty($items)) {
            $productIds = array_column($items, 'product_id');
            $validProductIds = \App\Models\Tenant\Product::query()
                ->whereIn('id', $productIds)
                ->where(function ($q) use ($serviceBranchId) {
                    $q->where('branch_id', $serviceBranchId)
                      ->orWhereNull('branch_id');
                })
                ->pluck('id')
                ->toArray();

            $invalidIds = array_diff($productIds, $validProductIds);
            if (!empty($invalidIds)) {
                abort(422, 'Produk tidak tersedia di cabang ini: ' . implode(', ', $invalidIds));
            }
        }

        // Sprint v2.0F: Server-side idempotency guard (5 min window)
        $existingQuotation = ServiceQuotation::where('service_id', $service->id)
            ->where('status', 'sent')
            ->where('created_by', $user->id)
            ->where('created_at', '>=', now()->subMinutes(5))
            ->first();
        if ($existingQuotation) {
            return response()->json(['message' => 'Estimasi sudah dibuat.', 'quotation' => $existingQuotation], 200);
        }

        // Sprint v2.0F: DB transaction for atomic quotation creation.
        // NOTE: Stock is NOT reserved at quotation stage (no reservation system exists in ServiceKU).
        // Stock is CHECKED but not HELD — availability is confirmed/reserved at repair time.
        // lockForUpdate() is NOT used because we do not modify stock_quantity (it would be misleading).
        $quotation = \Illuminate\Support\Facades\DB::transaction(function () use ($items, $service, $user, $data, $serviceBranchId) {
            $calculatedItems = [];
            $partsSubtotal = 0;
            $stockCheckedAt = now()->toISOString();

            foreach ($items as $item) {
                $productId = (int) $item['product_id'];
                $qty = (int) $item['qty'];

                $product = \App\Models\Tenant\Product::query()
                    ->where('id', $productId)
                    ->where(function ($q) use ($serviceBranchId) {
                        $q->where('branch_id', $serviceBranchId)
                          ->orWhereNull('branch_id');
                    })
                    ->first();

                if (!$product) {
                    throw new \Illuminate\Validation\ValidationException(
                        \Illuminate\Support\Facades\Validator::make([], []),
                        response()->json(['message' => "Produk ID {$productId} tidak ditemukan, tidak aktif, atau bukan milik cabang ini."], 422)
                    );
                }

                // Best-effort stock check — does NOT reserve. Real check at repair time.
                if ($product->stock_quantity < $qty) {
                    throw new \Illuminate\Validation\ValidationException(
                        \Illuminate\Support\Facades\Validator::make([], []),
                        response()->json(['message' => "Stok '{$product->name}' tidak mencukupi. Tersedia: {$product->stock_quantity}, Dibutuhkan: {$qty}."], 422)
                    );
                }

                $unitPrice = (float) $product->selling_price;
                $lineTotal = $unitPrice * $qty;
                $partsSubtotal += $lineTotal;

                $calculatedItems[] = [
                    'product_id'       => $product->id,
                    'sku'              => $product->sku ?? (string) $product->id,
                    'name'             => $product->name,
                    'qty'              => $qty,
                    'price'            => $unitPrice,
                    'line_total'       => $lineTotal,
                    'stock_before'     => $product->stock_quantity,
                    'stock_checked_at' => $stockCheckedAt,
                    '_disclaimer'      => 'Stok diperiksa saat estimasi dibuat; ketersediaan dikonfirmasi saat teknisi memulai perbaikan.',
                ];
            }

            $laborCost = (float) ($data['labor_cost'] ?? 0);
            $totalCost = $partsSubtotal + $laborCost;

            $q = ServiceQuotation::create([
                'service_id' => $service->id,
                'total_cost' => $totalCost,
                'items' => $calculatedItems,
                'notes' => ($data['notes'] ?? '') . "\n\n⚠ Stok diperiksa saat estimasi, tidak dijamin tersedia hingga teknisi memulai perbaikan.",
                'status' => 'sent',
                'created_by' => $user->id,
            ]);

            $service->update(['estimated_cost' => $totalCost, 'approval_status' => 'pending']);
            return $q;
        });

        $this->transitionServiceStatus($service, Service::STATUS_KONFIRMASI_PELANGGAN);
        event(new \App\Events\Entity\QuotationCreated($quotation));

        return back()->with('success', 'Estimasi dibuat. Total: Rp' . number_format($quotation->total_cost, 0, ',', '.'));
    }

    public function approveQuotation(Request $request, ServiceQuotation $quotation)
    {
        // Sprint v2.0E: Only pending quotations can be approved
        if ($quotation->status !== 'sent') {
            abort(409, 'Estimasi ini sudah diproses sebelumnya.');
        }

        $user = auth()->user();
        $method = $request->input('method', 'cs');
        $service = $quotation->service;

        // Sprint v2.0E: SEPARATE AUTH — customer portal vs internal
        if ($method === 'customer_portal') {
            // Customer Portal: verify customer owns this service
            $linkedCustomer = \App\Models\Tenant\Customer::where('email', $user->email)
                ->orWhere('phone', $user->phone)->first();
            if (!$linkedCustomer || $linkedCustomer->id !== $service->customer_id) {
                abort(403, 'Anda tidak memiliki akses ke estimasi ini.');
            }
        } else {
            // Internal: must have proper role
            $this->authorize('update', $service);
            if (!in_array($user->role, ['owner', 'admin', 'manager', 'cs'])) {
                abort(403, 'Anda tidak memiliki izin untuk menyetujui estimasi.');
            }
        }

        $quotation->approve($user->id, $method);
        $service->update(['approval_status' => 'approved', 'approved_by' => $user->id, 'approved_at' => now()]);
        $this->transitionServiceStatus($service, Service::STATUS_DIKERJAKAN);
        event(new \App\Events\Entity\CustomerApprovedRepair($quotation));
        return back()->with('success', 'Estimasi disetujui. Servis siap dikerjakan.');
    }

    public function rejectQuotation(Request $request, ServiceQuotation $quotation)
    {
        if ($quotation->status !== 'sent') {
            abort(409, 'Estimasi ini sudah diproses sebelumnya.');
        }

        $user = auth()->user();
        $method = $request->input('method', 'cs');
        $service = $quotation->service;
        $reason = $request->input('reason', '');
        if (empty(trim($reason))) abort(422, 'Alasan penolakan wajib diisi.');

        // Sprint v2.0E: SEPARATE AUTH — customer portal vs internal
        if ($method === 'customer_portal') {
            $linkedCustomer = \App\Models\Tenant\Customer::where('email', $user->email)
                ->orWhere('phone', $user->phone)->first();
            if (!$linkedCustomer || $linkedCustomer->id !== $service->customer_id) {
                abort(403, 'Anda tidak memiliki akses ke estimasi ini.');
            }
        } else {
            $this->authorize('update', $service);
            if (!in_array($user->role, ['owner', 'admin', 'manager', 'cs'])) {
                abort(403, 'Anda tidak memiliki izin untuk menolak estimasi.');
            }
        }

        $quotation->reject();
        $service->update(['approval_status' => 'rejected']);
        \App\Models\Tenant\ActivityLog::log('quotation_rejected', "Estimasi ditolak oleh {$user->name}: {$reason}", $service);
        event(new \App\Events\Entity\QuotationRejected($quotation, $reason));
        return back()->with('info', 'Estimasi ditolak. Alasan: ' . $reason);
    }

    /**
     * Sprint v3.0: Safe status transition with validation — COMPLETE lifecycle.
     */
    protected function transitionServiceStatus(Service $service, string $newStatus): void
    {
        $allowed = [
            Service::STATUS_DITERIMA => [Service::STATUS_DIAGNOSA, Service::STATUS_DIKERJAKAN],
            Service::STATUS_MENUNGGU_ALOKASI => [Service::STATUS_DITERIMA, Service::STATUS_DIAGNOSA],
            Service::STATUS_DIAGNOSA => [Service::STATUS_KONFIRMASI_PELANGGAN, Service::STATUS_DIKERJAKAN],
            Service::STATUS_KONFIRMASI_PELANGGAN => [Service::STATUS_DIKERJAKAN],
            Service::STATUS_KONFIRMASI_INTERNAL => [Service::STATUS_DIKERJAKAN],
            Service::STATUS_DIKERJAKAN => [Service::STATUS_SELESAI],
            Service::STATUS_SELESAI => [Service::STATUS_SIAP_DIAMBIL, Service::STATUS_DIKERJAKAN],
            Service::STATUS_SIAP_DIAMBIL => [Service::STATUS_CLOSE],
        ];

        $current = $service->status;
        if (in_array($newStatus, $allowed[$current] ?? [])) {
            $service->update(['status' => $newStatus]);
            \App\Models\Tenant\ActivityLog::log('status_changed', "Status: {$current} → {$newStatus}", $service);
        }
    }

    // ======== REPAIR NOTES (stored as ActivityLog — no new note system) ========

    /**
     * Sprint v3.0C: Add repair progress note on a service.
     * Uses ActivityLog as the backing store — avoids WorkOrder dependencies.
     */
    public function addRepairNote(Request $request, Service $service)
    {
        $user = auth()->user();

        // Only assigned technician or owner/admin/manager can add notes
        if (!in_array($user->role, ['owner', 'admin', 'manager']) && $service->technician_id !== $user->id) {
            abort(403, 'Hanya teknisi yang ditugaskan yang dapat menambah catatan.');
        }

        // Status gate: only allow notes during active repair
        if (!in_array($service->status, [Service::STATUS_DIKERJAKAN, Service::STATUS_DIAGNOSA])) {
            abort(409, 'Catatan hanya dapat ditambah saat perbaikan aktif. Status: ' . $service->status);
        }

        $data = $request->validate([
            'description' => 'required|string|max:2000',
        ]);

        \App\Models\Tenant\ActivityLog::log('repair_note', $data['description'], $service, [
            'note_type' => 'repair_progress',
            'created_by' => $user->id,
            'created_by_name' => $user->name,
        ]);

        return response()->json([
            'success' => true,
            'note' => [
                'description' => $data['description'],
                'created_by' => $user->name,
                'created_at' => now()->toISOString(),
            ],
        ]);
    }

    // ======== PARTS ========

    public function requestPart(Request $request, Service $service)
    {
        $this->authorize('update', $service);

        // Sprint v3.0: Use proper part request flow with branch scoping and event dispatch.
        $data = $request->validate([
            'product_id' => 'required|integer',
            'qty' => 'required|integer|min:1',
            'part_name' => 'nullable|string',
            'notes' => 'nullable|string',
            'priority' => 'nullable|in:normal,urgent,vip,warranty',
        ]);

        $user = auth()->user();
        $product = \App\Models\Tenant\Product::query()
            ->where('id', $data['product_id'])
            ->where(function ($q) use ($service) {
                $q->where('branch_id', $service->branch_id)
                  ->orWhereNull('branch_id');
            })
            ->first();

        if (!$product) {
            abort(422, 'Produk tidak tersedia di cabang ini.');
        }

        $part = $service->requiredParts()->create([
            'product_id'   => $product->id,
            'part_name'    => $data['part_name'] ?? $product->name,
            'qty'          => $data['qty'],
            'status'       => 'requested',
            'priority'     => $data['priority'] ?? 'normal',
            'notes'        => $data['notes'] ?? null,
            'selling_price'=> $product->selling_price,
            'unit_price'   => $product->cost_price,
        ]);

        $part->request($user->id);

        return back()->with('success', 'Part requested: ' . $product->name);
    }

    // ======== QC ========

    public function storeQcCheck(Request $request, Service $service)
    {
        $user = auth()->user();

        // Sprint v3.0: QC now enforces role, status gate, idempotency, and does pass/fail transitions.
        if (!in_array($user->role, ['owner', 'admin', 'manager'])) {
            abort(403, 'Hanya manager/owner/admin yang dapat melakukan QC.');
        }

        if ($service->technician_id === $user->id && !in_array($user->role, ['owner', 'admin'])) {
            abort(403, 'Teknisi tidak dapat meng-QC servis miliknya sendiri.');
        }

        if ($service->status !== Service::STATUS_SELESAI) {
            abort(409, 'Servis harus selesai dikerjakan sebelum QC. Status saat ini: ' . $service->status);
        }

        $data = $request->validate([
            'checks' => 'required|array|min:1',
            'checks.*.item' => 'required|string',
            'checks.*.result' => 'required|in:pending,pass,fail',
            'checks.*.notes' => 'nullable|string',
            'qc_decision' => 'required|in:pass,fail',
            'qc_notes' => 'nullable|string|max:1000',
        ]);

        $qcDecision = $data['qc_decision'];
        $qcNotes = $data['qc_notes'] ?? '';

        // Idempotency: if QC already decided today, reject
        $alreadyDecided = ServiceQcCheck::where('service_id', $service->id)
            ->whereDate('created_at', today())
            ->where('result', '!=', 'pending')
            ->exists();
        if ($alreadyDecided) {
            abort(409, 'QC untuk servis ini sudah diproses hari ini.');
        }

        foreach ($data['checks'] as $check) {
            ServiceQcCheck::updateOrCreate(
                ['service_id' => $service->id, 'item' => $check['item']],
                [
                    'result' => $check['result'],
                    'notes' => $check['notes'] ?? null,
                    'checked_by' => $user->id,
                ]
            );
        }

        // Store QC decision via activity log (no dedicated DB columns — audit trail is sufficient)
        \App\Models\Tenant\ActivityLog::log(
            $qcDecision === 'pass' ? 'qc_passed' : 'qc_failed',
            ($qcDecision === 'pass' ? 'QC PASS' : 'QC FAIL') . " oleh {$user->name}. {$qcNotes}",
            $service,
            ['qc_decision' => $qcDecision, 'qc_notes' => $qcNotes, 'qc_by' => $user->id, 'qc_at' => now()->toISOString()]
        );

        if ($qcDecision === 'pass') {
            $this->transitionServiceStatus($service, Service::STATUS_SIAP_DIAMBIL);
            \App\Models\Tenant\ActivityLog::log('qc_passed', "QC PASS oleh {$user->name}. Servis siap diambil. {$qcNotes}", $service);
            event(new \App\Events\Entity\QCCompleted($service));

            // BR-FIX-04.1: a warranty rework resolves its claim at the canonical
            // QC-PASS / READY-PICKUP point (NOT at repair finish). On QC FAIL
            // the claim stays open and the rework returns to repair.
            if ($service->is_warranty_claim) {
                $claim = \App\Models\Tenant\ServiceWarrantyClaim::where('rework_service_id', $service->id)->first();
                if ($claim && $claim->status !== 'completed') {
                    $claim->resolve($user->id, 'QC PASS — rework servis #' . $service->id . ' siap diambil.');
                }
            }

            return back()->with('success', 'QC LULUS. Servis siap diambil pelanggan.');
        } else {
            $this->transitionServiceStatus($service, Service::STATUS_DIKERJAKAN);
            \App\Models\Tenant\ActivityLog::log('qc_failed', "QC FAIL oleh {$user->name}. Dikembalikan ke perbaikan. {$qcNotes}", $service);
            event(new \App\Events\Entity\QCCompleted($service));
            return back()->with('success', 'QC GAGAL. Servis dikembalikan ke teknisi untuk perbaikan ulang.');
        }
    }

    // ======== REPAIR ========

    public function startRepair(Request $request, Service $service)
    {
        $this->authorize('update', $service);
        $user = auth()->user();

        // Sprint v3.0: Full validation — role, status gate, quotation approval, idempotency, stock warning.
        if (!in_array($user->role, ['owner', 'admin', 'manager']) && $service->technician_id !== $user->id) {
            abort(403, 'Hanya teknisi yang ditugaskan yang dapat memulai perbaikan.');
        }

        $allowedStatuses = [Service::STATUS_DITERIMA, Service::STATUS_DIAGNOSA, Service::STATUS_DIKERJAKAN, Service::STATUS_KONFIRMASI_PELANGGAN];
        if (!in_array($service->status, $allowedStatuses)) {
            abort(409, 'Servis tidak dapat dimulai. Status: ' . $service->status);
        }

        // If post-quotation approval: verify quotation is approved before starting
        if ($service->status === Service::STATUS_KONFIRMASI_PELANGGAN) {
            $hasApproved = $service->quotations()->where('status', 'approved')->exists();
            if (!$hasApproved) {
                abort(409, 'Estimasi belum disetujui. Tidak dapat memulai perbaikan.');
            }
        }

        // Idempotency: prevent double-start on same calendar day
        if ($service->dikerjakan_at && $service->dikerjakan_at->isToday()) {
            abort(409, 'Perbaikan sudah dimulai hari ini pada ' . $service->dikerjakan_at->format('H:i') . '.');
        }

        // Best-effort stock warning for quoted parts (no reservation — Phase 2F)
        $quotedParts = $service->requiredParts()->where('status', 'requested')->get();
        $stockWarnings = [];
        foreach ($quotedParts as $part) {
            if ($part->product_id && $part->product) {
                if ($part->product->stock_quantity < $part->qty) {
                    $stockWarnings[] = "{$part->product->name}: butuh {$part->qty}, tersedia {$part->product->stock_quantity}";
                }
            }
        }

        if ($service->status !== Service::STATUS_DIKERJAKAN) {
            $this->transitionServiceStatus($service, Service::STATUS_DIKERJAKAN);
        }

        $service->update(['dikerjakan_at' => $service->dikerjakan_at ?? now()]);

        \App\Models\Tenant\ActivityLog::log('repair_started',
            "{$user->name} memulai perbaikan servis #{$service->id}." .
            (empty($stockWarnings) ? '' : ' ⚠ Stok: ' . implode('; ', $stockWarnings)),
            $service);

        event(new \App\Events\Entity\RepairStarted($service));

        $msg = 'Perbaikan dimulai.';
        if (!empty($stockWarnings)) {
            $msg .= ' ⚠ Peringatan stok: ' . implode('; ', $stockWarnings);
        }
        return back()->with('success', $msg);
    }

    public function completeRepair(Request $request, Service $service)
    {
        $this->authorize('update', $service);
        $user = auth()->user();

        // Sprint v3.0: Status gate, transition to SELESAI, QC handoff.
        if (!in_array($user->role, ['owner', 'admin', 'manager']) && $service->technician_id !== $user->id) {
            abort(403, 'Hanya teknisi yang ditugaskan yang dapat menyelesaikan perbaikan.');
        }

        if ($service->status !== Service::STATUS_DIKERJAKAN) {
            abort(409, 'Servis harus dalam status dikerjakan. Status saat ini: ' . $service->status);
        }

        $data = $request->validate([
            'repair_notes' => 'nullable|string|max:2000',
            // 'parts_used' is accepted for backward compatibility with the UI,
            // but BR-FIX-01 (BR-007) makes it a NO-OP for inventory: finishing a
            // repair NEVER consumes stock. Consumption happens only when CS
            // confirms the approved part onto the invoice.
            'parts_used' => 'nullable|array',
            'parts_used.*.product_id' => 'required_with:parts_used|integer',
            'parts_used.*.qty' => 'required_with:parts_used|integer|min:1',
        ]);

        $repairNotes = $data['repair_notes'] ?? '';

        // Transition to SELESAI → QC (work completion only — no inventory effect).
        $this->transitionServiceStatus($service, Service::STATUS_SELESAI);
        $service->update(['selesai_at' => now()]);

        // Sprint v3.0C: Persist repair notes as ActivityLog entry
        if (!empty(trim($repairNotes))) {
            \App\Models\Tenant\ActivityLog::log('repair_note', $repairNotes, $service, [
                'note_type' => 'repair_completion',
                'created_by' => $user->id,
                'created_by_name' => $user->name,
            ]);
        }

        \App\Models\Tenant\ActivityLog::log('repair_completed',
            "{$user->name} menyelesaikan perbaikan servis #{$service->id}. (Stok sparepart dikonfirmasi oleh CS saat invoice).",
            $service);

        event(new \App\Events\Entity\RepairCompleted($service));

        return back()->with('success',
            'Perbaikan selesai. Servis siap untuk QC. Part dikonfirmasi oleh CS saat invoice.');
    }
}
