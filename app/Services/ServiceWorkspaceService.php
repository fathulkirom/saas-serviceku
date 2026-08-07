<?php

namespace App\Services;

use App\Models\Tenant\Service;
use App\Repositories\ServiceWorkspaceRepository;
use App\Services\FeatureEngine;
use Illuminate\Support\Facades\Event;

/**
 * ServiceWorkspaceService
 * 
 * Business logic layer for Service Workspace.
 * Orchestrates repository, permissions, workflow, and events.
 */
class ServiceWorkspaceService
{
    public function __construct(
        protected ServiceWorkspaceRepository $repository,
        protected FeatureEngine $featureEngine,
    ) {}

    /**
     * Build the complete workspace data payload.
     */
    public function build(Service $service): array
    {
        $service = $this->repository->loadService($service);

        return [
            'service' => $this->transformService($service),
            'customerSummary' => $this->repository->getCustomerSummary($service),
            'previousServices' => $this->repository->getPreviousServices($service),
            'relatedServices' => $this->repository->getRelatedServices($service),
            'workflowHistory' => $this->repository->getWorkflowHistory($service),
            'availableTechnicians' => $this->repository->getAvailableTechnicians($service),
            'availableTransitions' => $this->getAvailableTransitions($service),
            'featureAccess' => $this->getFeatureAccess(),
            'workspaceConfig' => $this->getWorkspaceConfig(),
        ];
    }

    /**
     * Get available status transitions for current user.
     */
    public function getAvailableTransitions(Service $service): array
    {
        $currentStatus = $service->status;
        $user = auth()->user();
        $role = $user?->role;

        // Get raw transitions from model
        $rawTransitions = $service->getAllowedTransitions() ?? [];

        // Filter by role
        return array_values(array_filter($rawTransitions, function ($toStatus) use ($role, $service, $currentStatus) {
            return $this->canUserTransition($role, $currentStatus, $toStatus, $service);
        }));
    }

    /**
     * Check if current user can perform a specific transition.
     */
    public function canUserTransition(?string $role, string $from, string $to, Service $service): bool
    {
        if (!$role) return false;

        // Owner/Admin can do all transitions
        if (in_array($role, ['owner', 'admin'])) return true;

        // Role-specific transition permissions
        $roleTransitions = [
            'cs' => [
                'menunggu_alokasi' => ['diterima', 'indent', 'onpartner', 'cancel'],
                'menunggu_konfirmasi_pelanggan' => ['dikerjakan', 'cancel'],
            ],
            'technician' => [
                'diterima' => ['diagnosa', 'dikerjakan'],
                'diagnosa' => ['dikerjakan', 'menunggu_konfirmasi_pelanggan', 'indent', 'cancel'],
                'dikerjakan' => ['menunggu_konfirmasi_pelanggan', 'selesai'],
                'indent' => ['dikerjakan'],
                'onpartner' => ['dikerjakan', 'selesai'],
            ],
            'manager' => [
                'menunggu_alokasi' => ['diterima', 'dikerjakan', 'cancel'],
                'selesai' => ['siap_diambil', 'close'],
                'siap_diambil' => ['close'],
            ],
            'cashier' => [
                'siap_diambil' => ['close'],
                'selesai' => ['siap_diambil'],
            ],
        ];

        $allowed = $roleTransitions[$role][$from] ?? [];
        return in_array($to, $allowed);
    }

    /**
     * Execute a status transition.
     */
    public function transition(Service $service, string $newStatus, array $metadata = []): Service
    {
        $oldStatus = $service->status;

        if (!$service->canTransitionTo($newStatus)) {
            throw new \InvalidArgumentException("Cannot transition from {$oldStatus} to {$newStatus}");
        }

        $service->status = $newStatus;

        // Set timestamp fields
        if ($newStatus === 'dikerjakan' && !$service->dikerjakan_at) {
            $service->dikerjakan_at = now();
        }
        if (in_array($newStatus, ['selesai', 'siap_diambil'])) {
            $service->selesai_at = $service->selesai_at ?? now();
        }

        $service->save();

        // Create worklog
        $service->worklogs()->create([
            'user_id' => auth()->id(),
            'action' => 'status_change',
            'description' => $metadata['note'] ?? "Status diubah dari {$oldStatus} menjadi {$newStatus}",
            'metadata' => [
                'from' => $oldStatus,
                'to' => $newStatus,
                ...$metadata,
            ],
        ]);

        // Dispatch event for automation/notification
        Event::dispatch('service.status-changed', [
            'service' => $service,
            'from' => $oldStatus,
            'to' => $newStatus,
        ]);

        return $service->fresh();
    }

    // ── Private ──

    private function transformService(Service $service): array
    {
        return [
            'id' => $service->id,
            'tracking_code' => $service->tracking_code,
            'status' => $service->status,
            'status_label' => $service->getStatusLabel(),
            'status_color' => $service->getStatusColor(),
            'created_at' => $service->created_at?->toISOString(),
            'updated_at' => $service->updated_at?->toISOString(),
            'dikerjakan_at' => $service->dikerjakan_at?->toISOString(),
            'selesai_at' => $service->selesai_at?->toISOString(),

            'customer' => $service->customer?->only(['id', 'name', 'phone', 'email']),
            'technician' => $service->technician?->only(['id', 'name']),
            'creator' => $service->creator?->only(['id', 'name']),
            'branch' => $service->branch?->only(['id', 'name']),

            'device_type' => $service->tipe_unit,
            'imei_sn' => $service->imei_sn,
            'problem_description' => $service->problem_description,
            'condition_note' => $service->condition_note,
            'kelengkapan' => $service->kelengkapan,

            'service_charge' => (float) $service->service_charge,
            'total_cost' => (float) $service->total_cost,
            'payment_status' => $service->payment_status,
            'is_warranty_claim' => (bool) $service->is_warranty_claim,
            'warranty_days' => (int) $service->warranty_days,
            'warranty_expired_at' => $service->warranty_expired_at?->toISOString(),

            // BR-FIX-04 — real store warranty, claims (+ linked rework) and
            // upstream supplier/distributor warranty from the backend.
            'warranty' => $service->warranty ? [
                'start_date' => $service->warranty->start_date?->toDateString(),
                'end_date' => $service->warranty->end_date?->toDateString(),
                'duration_days' => (int) $service->warranty->duration_days,
                'status' => $service->warranty->status,
                'warranty_type' => $service->warranty->warranty_type,
            ] : null,

            'warranty_claims' => \App\Models\Tenant\ServiceWarrantyClaim::with(['reworkService:id,tracking_code,status', 'branch:id,name'])
                ->where('service_id', $service->id)
                ->latest()
                ->get()
                ->map(fn($c) => [
                    'id' => $c->id,
                    'claim_number' => $c->claim_number,
                    'status' => $c->status,
                    'status_label' => ucfirst($c->status),
                    'problem_description' => $c->problem_description,
                    'created_at' => $c->created_at?->toISOString(),
                    'resolved_at' => $c->completed_at?->toISOString(),
                    'resolution_note' => $c->resolution_note,
                    'branch_name' => $c->branch?->name,
                    'rework' => $c->reworkService ? [
                        'id' => $c->reworkService->id,
                        'tracking_code' => $c->reworkService->tracking_code,
                        'status' => $c->reworkService->status,
                    ] : null,
                    // BR-FIX-04.1: refund affordance (backend is source of truth).
                    'sale_id' => $c->service?->sale?->id,
                    'refundable' => (float) ($c->service?->sale
                        ? \App\Models\Tenant\SaleRefund::refundableForSale($c->service->sale)
                        : 0),
                ]),

            // BR-FIX-04.1: whether the current user may operate a refund from
            // the Workspace (finance authority + branch access). Backend
            // independently rejects direct HTTP calls.
            'can_refund' => (function () use ($service) {
                $u = auth()->user();
                return $u
                    && ($u->canManageFinance() || $u->canViaPermission('finance.manage'))
                    && \App\Services\BranchAccessService::canAccess($u, $service->branch_id);
            })(),

            'upstream_warranty' => \App\Services\WarrantyService::upstreamWarrantyFor($service),

            'checklists' => $service->checklists?->map(fn($c) => [
                'id' => $c->id,
                'name' => $c->checklistTemplate?->name,
                'completed' => (bool) $c->is_completed,
                'items' => $c->checklistItems?->map(fn($i) => [
                    'id' => $i->id,
                    'label' => $i->label,
                    'checked' => (bool) $i->is_checked,
                ]),
            ]),

            'spareparts' => $service->spareparts?->map(fn($s) => [
                'id' => $s->id,
                'product_name' => $s->product?->name,
                'product_sku' => $s->product?->sku,
                'quantity' => (int) $s->quantity,
                'price' => (float) $s->unit_price,
                'total' => (float) ($s->quantity * $s->unit_price),
                'status' => $s->status ?? 'used',
            ]),

            'photos' => $service->photos?->map(fn($p) => [
                'id' => $p->id,
                'url' => $p->url ?? $p->path,
                'thumbnail' => $p->thumbnail_url ?? $p->url,
                'category' => $p->category ?? 'general',
                'uploaded_by' => $p->uploader?->name,
                'created_at' => $p->created_at?->toISOString(),
            ]),

            'diagnosis' => $service->diagnosis ? [
                'id' => $service->diagnosis->id,
                'issue_category' => $service->diagnosis->issue_category,
                'analysis' => $service->diagnosis->analysis,
                'root_cause' => $service->diagnosis->root_cause,
                'solution' => $service->diagnosis->solution,
                'estimated_hours' => (float) $service->diagnosis->estimated_hours,
                'severity' => $service->diagnosis->severity,
            ] : null,

            // Sprint v3.0B: QC checks for QC section
            'qc_checks' => $service->qcChecks?->map(fn($c) => [
                'id' => $c->id,
                'item' => $c->item,
                'result' => $c->result,
                'notes' => $c->notes,
                'checked_by' => $c->checked_by,
                'created_at' => $c->created_at?->toISOString(),
            ])->values()->toArray() ?? [],

            // BR-FIX-01: branch-scoped products the technician can request.
            'available_products' => \App\Models\Tenant\Product::query()
                ->where(function ($q) use ($service) {
                    $q->where('branch_id', $service->branch_id)->orWhereNull('branch_id');
                })
                ->orderBy('name')
                ->take(200)
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'stock_quantity' => (int) $p->stock_quantity,
                    'reserved_quantity' => (int) $p->reserved_quantity,
                    'available_quantity' => (int) $p->available_quantity,
                    'selling_price' => (float) $p->selling_price,
                ])->values()->toArray(),

            // Sprint v3.0C: Required parts for repair section
            // BR-FIX-01: expose reservation + stock info so the UI can clearly
            // distinguish Requested / Approved-Reserved / Consumed / Returned.
            'required_parts' => $service->requiredParts?->map(fn($p) => [
                'id' => $p->id,
                'product_id' => $p->product_id,
                'product_name' => $p->product?->name ?? $p->part_name,
                'qty' => (int) $p->qty,
                'reserved_qty' => (int) $p->reserved_qty,
                'status' => $p->status,
                'priority' => $p->priority,
                'notes' => $p->notes,
                'unit_price' => (float) ($p->unit_price ?? $p->product?->cost_price ?? 0),
                'selling_price' => (float) ($p->selling_price ?? $p->product?->selling_price ?? 0),
                'requested_by' => $p->requester?->name,
                'stock_info' => $p->product ? [
                    'physical' => (int) $p->product->stock_quantity,
                    'reserved' => (int) $p->product->reserved_quantity,
                    'available' => (int) $p->product->available_quantity,
                    'selling_price' => (float) $p->product->selling_price,
                ] : null,
            ])->values()->toArray() ?? [],

            // Sprint v3.0C: Worklogs for repair notes display (from ActivityLog)
            'worklogs' => $service->worklogs?->map(fn($w) => [
                'id' => $w->id,
                'description' => $w->description,
                'created_by' => $w->creator?->name,
                'created_at' => $w->created_at?->toISOString(),
            ])->values()->toArray() ?? [],

            // Sprint v3.0C: Repair notes from ActivityLog (action = repair_note)
            'repair_notes' => \App\Models\Tenant\ActivityLog::where('subject_type', Service::class)
                ->where('subject_id', $service->id)
                ->where('action', 'repair_note')
                ->orderBy('created_at')
                ->get()
                ->map(fn($log) => [
                    'id' => $log->id,
                    'description' => $log->description,
                    'created_by' => $log->properties['created_by_name'] ?? ($log->user?->name),
                    'created_at' => $log->created_at?->toISOString(),
                ])->values()->toArray(),

            'sale' => $service->sale ? [
                'id' => $service->sale->id,
                'invoice_number' => $service->sale->invoice_number,
                'total' => (float) $service->sale->total,
                'status' => $service->sale->status,
                'items' => $service->sale->items?->map(fn($i) => [
                    'name' => $i->product?->name ?? $i->description,
                    'quantity' => (int) $i->quantity,
                    'price' => (float) $i->price,
                    'total' => (float) ($i->quantity * $i->price),
                ]),
                'payments' => $service->sale->payments?->map(fn($p) => [
                    'id' => $p->id,
                    'amount' => (float) $p->amount,
                    'method' => $p->method,
                    'created_at' => $p->created_at?->toISOString(),
                ]),
            ] : null,
        ];
    }

    private function getFeatureAccess(): array
    {
        $tenant = tenant();
        if (!$tenant) return [];

        return [
            'can_assign' => $this->featureEngine->can($tenant, 'services'),
            'can_manage_parts' => $this->featureEngine->can($tenant, 'products'),
            'can_invoice' => $this->featureEngine->can($tenant, 'sales'),
            'can_indent' => $this->featureEngine->can($tenant, 'indents'),
        ];
    }

    private function getWorkspaceConfig(): array
    {
        return [
            'tabs' => ['overview', 'timeline', 'spareparts', 'photos', 'invoice'],
            'auto_refresh_seconds' => 30,
            'show_audit_trail' => true,
            'allow_file_upload' => true,
        ];
    }
}
