<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Service;
use App\Models\Tenant\WorkOrder;
use App\Models\Tenant\ServiceRequiredPart;
use App\Models\Tenant\ServiceDelivery;
use Illuminate\Http\Request;

/**
 * Operational Control Controller — Sprint 7.5C.
 * NO new models. Pure aggregation of existing data.
 */
class OperationalControlController extends Controller
{
    /** Kanban Board — services grouped by status */
    public function kanban()
    {
        $services = Service::with(['customer', 'technician', 'device'])
            ->whereNotIn('status', ['cancel', 'void', 'close', 'selesai'])
            ->latest()
            ->get()
            ->groupBy('status');

        $columns = [
            'menunggu_alokasi' => 'Masuk',
            'diterima' => 'Diterima',
            'diagnosa' => 'Diagnosa',
            'dikerjakan' => 'Dikerjakan',
            'menunggu_konfirmasi_pelanggan' => 'Menunggu Approval',
            'menunggu_konfirmasi_internal' => 'Approval Internal',
            'indent' => 'Menunggu Sparepart',
            'onpartner' => 'Partner',
            'siap_diambil' => 'Ready Pickup',
        ];

        return inertia('Services/Kanban', [
            'columns' => $columns,
            'services' => $services,
        ]);
    }

    /** Technician Performance */
    public function technicianPerformance()
    {
        $techs = \App\Models\Tenant\User::whereHas('workOrders')->withCount(['workOrders as total_jobs' => fn($q) => $q->where('work_status', 'done')])
            ->with(['workOrders' => fn($q) => $q->where('work_status', 'done')->selectRaw('technician_id, AVG(actual_minutes) as avg_minutes, COUNT(*) as count')->groupBy('technician_id')])
            ->get()
            ->map(function ($t) {
                $doneOrders = WorkOrder::where('technician_id', $t->id)->where('work_status', 'done');
                return [
                    'id' => $t->id, 'name' => $t->name,
                    'total_jobs' => $doneOrders->count(),
                    'avg_repair_minutes' => round($doneOrders->avg('actual_minutes') ?? 0),
                    'active_jobs' => WorkOrder::forTechnician($t->id)->active()->count(),
                    'revenue' => ServiceRequiredPart::whereHas('service', fn($q) => $q->where('technician_id', $t->id))->where('status', 'used')->sum('subtotal'),
                ];
            });

        return response()->json(['technicians' => $techs]);
    }

    /** CS Daily Dashboard */
    public function csDashboard()
    {
        return response()->json([
            'today_in' => Service::whereDate('created_at', today())->count(),
            'today_out' => Service::whereDate('updated_at', today())->whereIn('status', ['selesai', 'siap_diambil'])->count(),
            'pending' => Service::whereNotIn('status', ['selesai', 'cancel', 'void', 'close', 'siap_diambil'])->count(),
            'quotation_pending' => Service::where('approval_status', 'pending')->count(),
            'pickup_pending' => Service::where('status', 'siap_diambil')->count(),
            'customer_waiting' => Service::whereIn('status', ['menunggu_konfirmasi_pelanggan'])->count(),
        ]);
    }

    /** Owner Dashboard */
    public function ownerDashboard()
    {
        $today = today();
        return response()->json([
            'today_profit' => ServiceRequiredPart::where('status', 'used')->whereDate('used_at', $today)->sum(\DB::raw('subtotal - (unit_price * qty)')),
            'today_revenue' => ServiceRequiredPart::where('status', 'used')->whereDate('used_at', $today)->sum('subtotal'),
            'outstanding_parts' => ServiceRequiredPart::where('supplier_status', 'waiting_purchase')->count(),
            'outstanding_pickup' => ServiceDelivery::whereNull('picked_up_at')->whereNotNull('ready_at')->count(),
            'outstanding_payment' => Service::where('payment_status', 'pending')->count(),
            'active_warranties' => \App\Models\Tenant\ServiceWarranty::active()->count(),
        ]);
    }

    /** Pickup Queue */
    public function pickupQueue()
    {
        $pickups = ServiceDelivery::with(['service.customer', 'service.device'])
            ->whereNull('picked_up_at')->whereNotNull('ready_at')
            ->get()
            ->map(fn($d) => [
                'service_id' => $d->service_id,
                'customer' => $d->service->customer?->name,
                'device' => $d->service->device?->brand . ' ' . $d->service->device?->model,
                'ready_since' => $d->ready_at->diffInDays(now()),
                'days_waiting' => (int) $d->ready_at->diffInDays(now()),
                'payment_verified' => $d->payment_verified,
            ]);

        return inertia('Services/PickupQueue', ['pickups' => $pickups]);
    }

    /** Approval Center — all pending approvals */
    public function approvalCenter()
    {
        return inertia('Services/ApprovalCenter', [
            'quotations' => \App\Models\Tenant\ServiceQuotation::with('service.customer')->where('status', 'sent')->get(),
            'priceChanges' => \App\Models\Tenant\PriceChangeRequest::with('service.customer')->where('status', 'pending')->get(),
            'reopens' => \App\Models\Tenant\ServiceReopen::with('service.customer')->where('status', 'requested')->get(),
            'returns' => \App\Models\Tenant\SaleReturn::with('sale.customer')->where('status', 'pending')->get(),
            'stockAdjustments' => \App\Models\Tenant\StockAdjustment::with('product')->whereNull('approved_by')->get(),
        ]);
    }

    /** Technician Load Balancer */
    public function loadBalancer()
    {
        $techs = \App\Models\Tenant\User::whereHas('workOrders', fn($q) => $q->active())
            ->withCount(['workOrders as active_count' => fn($q) => $q->active()])
            ->get()
            ->map(fn($t) => ['id' => $t->id, 'name' => $t->name, 'active_count' => $t->active_count]);

        $avg = $techs->avg('active_count');
        $suggestions = $techs->filter(fn($t) => $t['active_count'] < $avg)->values();

        return response()->json([
            'technicians' => $techs,
            'average_load' => round($avg, 1),
            'suggested_assignee' => $suggestions->first(),
        ]);
    }

    /** SLA Overview */
    public function slaOverview()
    {
        $services = Service::with('customer')
            ->whereIn('status', ['diterima', 'diagnosa', 'dikerjakan'])
            ->get()
            ->map(function ($s) {
                $sla = app(\App\Services\SlaEngine::class)->getConfig('service', 'normal');
                $elapsedHours = $s->created_at->diffInHours(now());
                $targetHours = ($sla?->target_checking_minutes ?? 1440) / 60;

                return [
                    'id' => $s->id, 'customer' => $s->customer?->name,
                    'status' => $s->status,
                    'elapsed_hours' => $elapsedHours,
                    'target_hours' => $targetHours,
                    'breached' => $elapsedHours > $targetHours,
                ];
            });

        return response()->json(['services' => $services]);
    }
}
