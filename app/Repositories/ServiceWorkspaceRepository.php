<?php

namespace App\Repositories;

use App\Models\Tenant\Service;
use App\Models\Tenant\Customer;
use Illuminate\Support\Collection;

/**
 * ServiceWorkspaceRepository
 * 
 * Aggregates ALL data needed for the Enterprise Service Workspace.
 * Single source of truth for workspace data. No business logic.
 */
class ServiceWorkspaceRepository
{
    /**
     * Load the full service with ALL relationships needed for workspace.
     */
    public function loadService(Service $service): Service
    {
        return $service->load([
            // Core relations
            'customer.tags',
            'customer.devices',
            'technician',
            'creator',
            'branch',

            // Service details
            'checklists.checklistTemplate.items',
            'checklistResults.item',
            'spareparts.product',
            'diagnosis',
            'requiredParts.product',
            'quotations',
            'qcChecks',

            // Media
            'photos.uploader',

            // Financial
            'sale.items',
            'sale.payments',

            // Workflow
            'worklogs.creator',
            'worklogs.user',

            // Related
            'indent',
            'parentService',
            'delivery',
        ]);
    }

    /**
     * Get customer summary for sidebar.
     */
    public function getCustomerSummary(Service $service): ?array
    {
        $customer = $service->customer;
        if (!$customer) return null;

        return [
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'email' => $customer->email ?? '',
            'is_member' => (bool) ($customer->is_member ?? false),
            'customer_code' => $customer->customer_code ?? '',
            'service_count' => Service::where('customer_id', $customer->id)->count(),
            'total_spending' => (float) Service::where('customer_id', $customer->id)
                ->whereIn('status', ['selesai', 'siap_diambil', 'close'])
                ->sum('total_cost'),
            'device_count' => $customer->devices?->count() ?? 0,
            'last_visit' => Service::where('customer_id', $customer->id)
                ->latest()->value('created_at')?->format('Y-m-d'),
            'risk' => $this->calculateCustomerRisk($customer->id),
            'tags' => $customer->tags?->pluck('name')?->toArray() ?? [],
        ];
    }

    /**
     * Get previous services for same customer.
     */
    public function getPreviousServices(Service $service): Collection
    {
        if (!$service->customer_id) return collect();

        return Service::where('customer_id', $service->customer_id)
            ->where('id', '!=', $service->id)
            ->whereIn('status', ['selesai', 'siap_diambil', 'close'])
            ->with('technician')
            ->latest('selesai_at')
            ->limit(10)
            ->get();
    }

    /**
     * Get available technicians for assignment.
     */
    public function getAvailableTechnicians(Service $service): Collection
    {
        return \App\Models\Tenant\User::whereIn('role', ['technician', 'owner', 'admin'])
            ->when($service->branch_id, fn($q) => $q->where('branch_id', $service->branch_id))
            ->get();
    }

    /**
     * Get workflow history (audit trail).
     */
    public function getWorkflowHistory(Service $service): Collection
    {
        return $service->worklogs()
            ->with('user')
            ->latest()
            ->limit(50)
            ->get();
    }

    /**
     * Get related services (same customer, different service).
     */
    public function getRelatedServices(Service $service): Collection
    {
        if (!$service->customer_id) return collect();

        return Service::where('customer_id', $service->customer_id)
            ->where('id', '!=', $service->id)
            ->with('technician')
            ->latest()
            ->limit(5)
            ->get();
    }

    // ── Private helpers ──

    private function calculateCustomerRisk(int $customerId): string
    {
        $services = Service::where('customer_id', $customerId)
            ->whereIn('status', ['selesai', 'siap_diambil', 'close'])
            ->count();

        $cancelled = Service::where('customer_id', $customerId)
            ->where('status', 'cancel')
            ->count();

        $total = $services + $cancelled;
        if ($total === 0) return 'new';

        $cancelRate = $cancelled / $total;
        if ($cancelRate > 0.3) return 'high';
        if ($cancelRate > 0.1) return 'medium';
        return 'low';
    }
}
