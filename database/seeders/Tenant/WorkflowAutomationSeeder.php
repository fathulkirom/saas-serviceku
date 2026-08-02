<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use App\Models\Tenant\Workflow;
use App\Models\Tenant\WorkflowState;
use App\Models\Tenant\WorkflowTransition;
use App\Models\Tenant\WorkflowAction;
use App\Models\Tenant\AutomationRule;
use App\Models\Tenant\SlaConfig;
use Illuminate\Support\Facades\DB;

/**
 * Sprint 7.2C — Workflow & Automation Engine Seeder.
 * DEFINES ALL BUSINESS PROCESSES AS DATA.
 * No hardcoded status transitions in code.
 */
class WorkflowAutomationSeeder extends Seeder
{
    public function run(): void
    {
        // ============================================================
        // SECTION 1: WORKFLOW DEFINITIONS
        // ============================================================

        $serviceWf = Workflow::create([
            'key' => 'service',
            'label' => 'Service Workflow',
            'model' => \App\Models\Tenant\Service::class,
            'initial_state' => 'menunggu_alokasi',
            'terminal_states' => json_encode(['selesai', 'cancel', 'void', 'close']),
            'module_key' => 'service',
            'is_active' => true,
        ]);

        $requestWf = Workflow::create([
            'key' => 'request',
            'label' => 'Request Workflow',
            'model' => \App\Models\Tenant\Request::class,
            'initial_state' => 'draft',
            'terminal_states' => json_encode(['completed', 'delivered', 'cancelled', 'rejected', 'expired', 'closed']),
            'module_key' => 'service',
            'is_active' => true,
        ]);

        $woWf = Workflow::create([
            'key' => 'work_order',
            'label' => 'Work Order Workflow',
            'model' => \App\Models\Tenant\WorkOrder::class,
            'initial_state' => 'pending',
            'terminal_states' => json_encode(['done', 'cancelled']),
            'module_key' => 'service',
            'is_active' => true,
        ]);

        // Placeholder — full impl in later sprint
        foreach (['warranty', 'complaint'] as $k) {
            Workflow::create([
                'key' => $k,
                'label' => ucfirst($k) . ' Workflow',
                'initial_state' => 'draft',
                'terminal_states' => json_encode(['completed', 'cancelled']),
                'module_key' => $k,
                'is_active' => false, // Future
            ]);
        }

        // ============================================================
        // SECTION 2: SERVICE WORKFLOW STATES
        // ============================================================
        $svcStates = [
            ['workflow_id' => $serviceWf->id, 'key' => 'menunggu_alokasi',   'label' => 'Menunggu Alokasi',  'color' => '#6B7280', 'category' => 'waiting',    'sort_order' => 1],
            ['workflow_id' => $serviceWf->id, 'key' => 'diterima',           'label' => 'Diterima',         'color' => '#3B82F6', 'category' => 'active',     'sort_order' => 2],
            ['workflow_id' => $serviceWf->id, 'key' => 'diagnosa',           'label' => 'Diagnosa',         'color' => '#8B5CF6', 'category' => 'active',     'sort_order' => 3],
            ['workflow_id' => $serviceWf->id, 'key' => 'dikerjakan',         'label' => 'Dikerjakan',       'color' => '#F59E0B', 'category' => 'active',     'sort_order' => 4],
            ['workflow_id' => $serviceWf->id, 'key' => 'menunggu_konfirmasi_pelanggan', 'label' => 'Konfirmasi Pelanggan', 'color' => '#EF4444', 'category' => 'waiting', 'sort_order' => 5],
            ['workflow_id' => $serviceWf->id, 'key' => 'menunggu_konfirmasi_internal',  'label' => 'Konfirmasi Internal',  'color' => '#EF4444', 'category' => 'waiting', 'sort_order' => 6],
            ['workflow_id' => $serviceWf->id, 'key' => 'indent',             'label' => 'Indent',           'color' => '#EC4899', 'category' => 'waiting',    'sort_order' => 7],
            ['workflow_id' => $serviceWf->id, 'key' => 'onpartner',          'label' => 'Partner',          'color' => '#14B8A6', 'category' => 'active',     'sort_order' => 8],
            ['workflow_id' => $serviceWf->id, 'key' => 'siap_diambil',       'label' => 'Siap Diambil',     'color' => '#10B981', 'category' => 'done',       'sort_order' => 9],
            ['workflow_id' => $serviceWf->id, 'key' => 'selesai',            'label' => 'Selesai',          'color' => '#059669', 'category' => 'done',       'sort_order' => 10, 'is_terminal' => true],
            ['workflow_id' => $serviceWf->id, 'key' => 'cancel',             'label' => 'Batal',            'color' => '#DC2626', 'category' => 'cancelled',  'sort_order' => 11, 'is_terminal' => true],
            ['workflow_id' => $serviceWf->id, 'key' => 'void',               'label' => 'Void',             'color' => '#991B1B', 'category' => 'cancelled',  'sort_order' => 12, 'is_terminal' => true],
            ['workflow_id' => $serviceWf->id, 'key' => 'close',              'label' => 'Close',            'color' => '#374151', 'category' => 'cancelled',  'sort_order' => 13, 'is_terminal' => true],
        ];
        foreach ($svcStates as $s) WorkflowState::create($s);

        // ============================================================
        // SECTION 3: REQUEST WORKFLOW STATES
        // ============================================================
        $reqStates = [
            ['workflow_id' => $requestWf->id, 'key' => 'draft',       'label' => 'Draft',              'color' => '#9CA3AF', 'category' => 'active',   'sort_order' => 1],
            ['workflow_id' => $requestWf->id, 'key' => 'waiting',     'label' => 'Menunggu',           'color' => '#F59E0B', 'category' => 'waiting',  'sort_order' => 2],
            ['workflow_id' => $requestWf->id, 'key' => 'confirmed',   'label' => 'Dikonfirmasi',       'color' => '#3B82F6', 'category' => 'active',   'sort_order' => 3],
            ['workflow_id' => $requestWf->id, 'key' => 'arrived',     'label' => 'Barang Tiba',        'color' => '#8B5CF6', 'category' => 'active',   'sort_order' => 4],
            ['workflow_id' => $requestWf->id, 'key' => 'checking',    'label' => 'Checking',           'color' => '#06B6D4', 'category' => 'active',   'sort_order' => 5],
            ['workflow_id' => $requestWf->id, 'key' => 'quotation',   'label' => 'Menunggu Quotation', 'color' => '#E11D48', 'category' => 'waiting',  'sort_order' => 6],
            ['workflow_id' => $requestWf->id, 'key' => 'waiting_approval', 'label' => 'Menunggu Approval', 'color' => '#E11D48', 'category' => 'waiting', 'sort_order' => 7],
            ['workflow_id' => $requestWf->id, 'key' => 'approved',    'label' => 'Disetujui',          'color' => '#10B981', 'category' => 'active',   'sort_order' => 8],
            ['workflow_id' => $requestWf->id, 'key' => 'rejected',    'label' => 'Ditolak',            'color' => '#DC2626', 'category' => 'cancelled','sort_order' => 9, 'is_terminal' => true],
            ['workflow_id' => $requestWf->id, 'key' => 'need_part',   'label' => 'Butuh Part',         'color' => '#EC4899', 'category' => 'waiting',  'sort_order' => 10],
            ['workflow_id' => $requestWf->id, 'key' => 'waiting_part','label' => 'Menunggu Part',      'color' => '#EC4899', 'category' => 'waiting',  'sort_order' => 11],
            ['workflow_id' => $requestWf->id, 'key' => 'repair',      'label' => 'Perbaikan',          'color' => '#F59E0B', 'category' => 'active',   'sort_order' => 12],
            ['workflow_id' => $requestWf->id, 'key' => 'qc',          'label' => 'QC',                 'color' => '#8B5CF6', 'category' => 'active',   'sort_order' => 13],
            ['workflow_id' => $requestWf->id, 'key' => 'ready_pickup','label' => 'Siap Diambil',       'color' => '#10B981', 'category' => 'done',     'sort_order' => 14],
            ['workflow_id' => $requestWf->id, 'key' => 'delivered',   'label' => 'Terkirim',           'color' => '#059669', 'category' => 'done',     'sort_order' => 15, 'is_terminal' => true],
            ['workflow_id' => $requestWf->id, 'key' => 'completed',   'label' => 'Selesai',            'color' => '#047857', 'category' => 'done',     'sort_order' => 16, 'is_terminal' => true],
            ['workflow_id' => $requestWf->id, 'key' => 'cancelled',   'label' => 'Dibatalkan',         'color' => '#DC2626', 'category' => 'cancelled','sort_order' => 17, 'is_terminal' => true],
            ['workflow_id' => $requestWf->id, 'key' => 'expired',     'label' => 'Kadaluarsa',         'color' => '#991B1B', 'category' => 'cancelled','sort_order' => 18, 'is_terminal' => true],
            ['workflow_id' => $requestWf->id, 'key' => 'closed',      'label' => 'Tertutup',           'color' => '#374151', 'category' => 'cancelled','sort_order' => 19, 'is_terminal' => true],
        ];
        foreach ($reqStates as $s) WorkflowState::create($s);

        // ============================================================
        // SECTION 4: WORK ORDER STATES
        // ============================================================
        $woStates = [
            ['workflow_id' => $woWf->id, 'key' => 'pending',    'label' => 'Pending',     'color' => '#9CA3AF', 'category' => 'waiting', 'sort_order' => 1],
            ['workflow_id' => $woWf->id, 'key' => 'assigned',   'label' => 'Ditugaskan',  'color' => '#3B82F6', 'category' => 'active',  'sort_order' => 2],
            ['workflow_id' => $woWf->id, 'key' => 'accepted',   'label' => 'Diterima',    'color' => '#6366F1', 'category' => 'active',  'sort_order' => 3],
            ['workflow_id' => $woWf->id, 'key' => 'in_progress','label' => 'Dikerjakan',  'color' => '#F59E0B', 'category' => 'active',  'sort_order' => 4],
            ['workflow_id' => $woWf->id, 'key' => 'paused',     'label' => 'Ditunda',     'color' => '#EF4444', 'category' => 'waiting', 'sort_order' => 5],
            ['workflow_id' => $woWf->id, 'key' => 'waiting_part','label' => 'Menunggu Part','color' => '#EC4899', 'category' => 'waiting','sort_order' => 6],
            ['workflow_id' => $woWf->id, 'key' => 'qc',         'label' => 'QC',          'color' => '#8B5CF6', 'category' => 'active',  'sort_order' => 7],
            ['workflow_id' => $woWf->id, 'key' => 'done',       'label' => 'Selesai',     'color' => '#10B981', 'category' => 'done',    'sort_order' => 8, 'is_terminal' => true],
            ['workflow_id' => $woWf->id, 'key' => 'cancelled',  'label' => 'Dibatalkan',  'color' => '#DC2626', 'category' => 'cancelled','sort_order' => 9, 'is_terminal' => true],
        ];
        foreach ($woStates as $s) WorkflowState::create($s);

        // ============================================================
        // SECTION 5: SERVICE TRANSITIONS (Backward compatible with existing const)
        // ============================================================
        $svcTransitions = [
            // menunggu_alokasi →
            ['workflow_id' => $serviceWf->id, 'from_state' => 'menunggu_alokasi', 'to_state' => 'diterima',    'label' => 'Terima',        'permission' => 'service.accept'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'menunggu_alokasi', 'to_state' => 'dikerjakan',  'label' => 'Langsung Kerjakan', 'permission' => 'service.start'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'menunggu_alokasi', 'to_state' => 'indent',      'label' => 'Indent',        'permission' => 'service.indent'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'menunggu_alokasi', 'to_state' => 'onpartner',   'label' => 'Partner',       'permission' => 'service.partner'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'menunggu_alokasi', 'to_state' => 'cancel',      'label' => 'Batal',         'permission' => 'service.cancel'],

            // diterima →
            ['workflow_id' => $serviceWf->id, 'from_state' => 'diterima',  'to_state' => 'dikerjakan',         'label' => 'Mulai Kerjakan', 'permission' => 'service.start'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'diterima',  'to_state' => 'menunggu_alokasi',   'label' => 'Lepas',          'permission' => 'service.reallocate'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'diterima',  'to_state' => 'indent',             'label' => 'Indent',         'permission' => 'service.indent'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'diterima',  'to_state' => 'cancel',             'label' => 'Batal',          'permission' => 'service.cancel'],

            // diagnosa →
            ['workflow_id' => $serviceWf->id, 'from_state' => 'diagnosa',  'to_state' => 'dikerjakan',                  'label' => 'Kerjakan',          'permission' => 'service.start'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'diagnosa',  'to_state' => 'menunggu_konfirmasi_pelanggan','label' => 'Konfirmasi Pelanggan','permission' => 'service.confirm'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'diagnosa',  'to_state' => 'menunggu_konfirmasi_internal', 'label' => 'Konfirmasi Internal', 'permission' => 'service.confirm'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'diagnosa',  'to_state' => 'indent',                      'label' => 'Indent',            'permission' => 'service.indent'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'diagnosa',  'to_state' => 'cancel',                      'label' => 'Batal',             'permission' => 'service.cancel'],

            // dikerjakan →
            ['workflow_id' => $serviceWf->id, 'from_state' => 'dikerjakan', 'to_state' => 'menunggu_konfirmasi_pelanggan','label' => 'Konfirmasi Pelanggan','permission' => 'service.confirm'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'dikerjakan', 'to_state' => 'menunggu_konfirmasi_internal', 'label' => 'Konfirmasi Internal', 'permission' => 'service.confirm'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'dikerjakan', 'to_state' => 'indent',                      'label' => 'Indent',            'permission' => 'service.indent'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'dikerjakan', 'to_state' => 'onpartner',                   'label' => 'Partner',           'permission' => 'service.partner'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'dikerjakan', 'to_state' => 'selesai',                      'label' => 'Selesai',           'permission' => 'service.finish'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'dikerjakan', 'to_state' => 'cancel',                      'label' => 'Batal',             'permission' => 'service.cancel'],

            // konfirmasi pelanggan →
            ['workflow_id' => $serviceWf->id, 'from_state' => 'menunggu_konfirmasi_pelanggan', 'to_state' => 'dikerjakan', 'label' => 'Lanjutkan', 'permission' => 'service.approve'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'menunggu_konfirmasi_pelanggan', 'to_state' => 'cancel',     'label' => 'Batal',     'permission' => 'service.cancel'],

            // konfirmasi internal →
            ['workflow_id' => $serviceWf->id, 'from_state' => 'menunggu_konfirmasi_internal',  'to_state' => 'dikerjakan', 'label' => 'Setujui',  'permission' => 'service.approve'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'menunggu_konfirmasi_internal',  'to_state' => 'cancel',     'label' => 'Batal',     'permission' => 'service.cancel'],

            // indent →
            ['workflow_id' => $serviceWf->id, 'from_state' => 'indent',    'to_state' => 'dikerjakan', 'label' => 'Lanjutkan', 'permission' => 'service.resume_indent'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'indent',    'to_state' => 'cancel',     'label' => 'Batal',     'permission' => 'service.cancel'],

            // onpartner →
            ['workflow_id' => $serviceWf->id, 'from_state' => 'onpartner', 'to_state' => 'dikerjakan', 'label' => 'Selesai Partner', 'permission' => 'service.complete_partner'],
            ['workflow_id' => $serviceWf->id, 'from_state' => 'onpartner', 'to_state' => 'selesai',    'label' => 'Langsung Selesai','permission' => 'service.finish'],

            // siap_diambil → (terminal, but can transition to selesai)
            ['workflow_id' => $serviceWf->id, 'from_state' => 'siap_diambil', 'to_state' => 'selesai', 'label' => 'Diambil Customer', 'permission' => 'service.deliver'],
        ];
        foreach ($svcTransitions as $t) WorkflowTransition::create($t);

        // ============================================================
        // SECTION 6: REQUEST TRANSITIONS
        // ============================================================
        $reqTransitions = [
            ['workflow_id' => $requestWf->id, 'from_state' => 'draft',         'to_state' => 'waiting',          'label' => 'Submit',            'permission' => 'request.create'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'waiting',       'to_state' => 'confirmed',        'label' => 'Konfirmasi',        'permission' => 'request.update'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'confirmed',     'to_state' => 'arrived',          'label' => 'Barang Tiba',       'permission' => 'request.update'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'confirmed',     'to_state' => 'checking',         'label' => 'Mulai Checking',    'permission' => 'request.update', 'is_auto' => true],
            ['workflow_id' => $requestWf->id, 'from_state' => 'arrived',       'to_state' => 'checking',         'label' => 'Mulai Checking',    'permission' => 'request.update'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'checking',      'to_state' => 'quotation',        'label' => 'Buat Quotation',    'permission' => 'request.update'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'checking',      'to_state' => 'repair',           'label' => 'Langsung Repair',   'permission' => 'request.update'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'checking',      'to_state' => 'need_part',        'label' => 'Butuh Part',        'permission' => 'request.update'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'quotation',     'to_state' => 'waiting_approval', 'label' => 'Minta Approval',    'permission' => 'request.assign'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'waiting_approval','to_state' => 'approved',       'label' => 'Setujui',           'permission' => 'request.approve', 'role' => 'owner'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'waiting_approval','to_state' => 'rejected',       'label' => 'Tolak',             'permission' => 'request.approve', 'role' => 'owner'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'approved',      'to_state' => 'repair',           'label' => 'Mulai Perbaikan',   'permission' => 'request.update'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'rejected',      'to_state' => 'checking',         'label' => 'Revisi Checking',   'permission' => 'request.update'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'need_part',     'to_state' => 'waiting_part',     'label' => 'Order Part',        'permission' => 'request.update', 'is_auto' => true],
            ['workflow_id' => $requestWf->id, 'from_state' => 'waiting_part',  'to_state' => 'repair',           'label' => 'Part Tiba',         'permission' => 'request.update'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'repair',        'to_state' => 'qc',               'label' => 'Mulai QC',          'permission' => 'request.update'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'qc',            'to_state' => 'ready_pickup',     'label' => 'QC Lulus',          'permission' => 'request.update'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'qc',            'to_state' => 'repair',           'label' => 'QC Gagal — Revisi', 'permission' => 'request.update'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'ready_pickup',  'to_state' => 'delivered',        'label' => 'Diambil Customer',  'permission' => 'request.update'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'ready_pickup',  'to_state' => 'completed',        'label' => 'Selesai',           'permission' => 'request.update'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'delivered',     'to_state' => 'completed',        'label' => 'Tutup Request',     'permission' => 'request.override'],
            // Cancellation from any non-terminal state
            ['workflow_id' => $requestWf->id, 'from_state' => 'draft',         'to_state' => 'cancelled',        'label' => 'Batal',             'permission' => 'request.cancel'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'waiting',       'to_state' => 'cancelled',        'label' => 'Batal',             'permission' => 'request.cancel'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'checking',      'to_state' => 'cancelled',        'label' => 'Batal',             'permission' => 'request.cancel'],
            ['workflow_id' => $requestWf->id, 'from_state' => 'quotation',     'to_state' => 'cancelled',        'label' => 'Batal',             'permission' => 'request.cancel'],
        ];
        foreach ($reqTransitions as $t) WorkflowTransition::create($t);

        // ============================================================
        // SECTION 7: WORK ORDER TRANSITIONS
        // ============================================================
        $woTransitions = [
            ['workflow_id' => $woWf->id, 'from_state' => 'pending',     'to_state' => 'assigned',   'label' => 'Assign'],
            ['workflow_id' => $woWf->id, 'from_state' => 'assigned',    'to_state' => 'accepted',   'label' => 'Terima'],
            ['workflow_id' => $woWf->id, 'from_state' => 'assigned',    'to_state' => 'cancelled',  'label' => 'Tolak'],
            ['workflow_id' => $woWf->id, 'from_state' => 'accepted',    'to_state' => 'in_progress','label' => 'Mulai'],
            ['workflow_id' => $woWf->id, 'from_state' => 'in_progress', 'to_state' => 'paused',     'label' => 'Tunda'],
            ['workflow_id' => $woWf->id, 'from_state' => 'in_progress', 'to_state' => 'waiting_part','label' => 'Butuh Part'],
            ['workflow_id' => $woWf->id, 'from_state' => 'in_progress', 'to_state' => 'qc',         'label' => 'Selesai Pengerjaan'],
            ['workflow_id' => $woWf->id, 'from_state' => 'paused',      'to_state' => 'in_progress','label' => 'Lanjutkan'],
            ['workflow_id' => $woWf->id, 'from_state' => 'waiting_part','to_state' => 'in_progress','label' => 'Part Tiba'],
            ['workflow_id' => $woWf->id, 'from_state' => 'qc',          'to_state' => 'done',       'label' => 'QC Lulus'],
            ['workflow_id' => $woWf->id, 'from_state' => 'qc',          'to_state' => 'in_progress','label' => 'Revisi'],
            ['workflow_id' => $woWf->id, 'from_state' => 'paused',      'to_state' => 'cancelled',  'label' => 'Batal'],
            ['workflow_id' => $woWf->id, 'from_state' => 'waiting_part','to_state' => 'cancelled',  'label' => 'Batal'],
        ];
        foreach ($woTransitions as $t) WorkflowTransition::create($t);

        // ============================================================
        // SECTION 8: WORKFLOW ACTIONS (Side Effects)
        // ============================================================
        $actions = [
            ['workflow_id' => $serviceWf->id, 'name' => 'activity_log',    'label' => 'Activity Log',     'trigger' => 'on_transition', 'is_active' => true],
            ['workflow_id' => $requestWf->id, 'name' => 'create_timeline', 'label' => 'Create Timeline',  'trigger' => 'on_transition', 'is_active' => true],
            ['workflow_id' => $requestWf->id, 'name' => 'create_audit',    'label' => 'Audit Log',        'trigger' => 'on_transition', 'is_active' => true],
        ];
        foreach ($actions as $a) WorkflowAction::create($a);

        // ============================================================
        // SECTION 9: AUTOMATION RULES (BUILT-IN TEMPLATES)
        // ============================================================
        $automationTemplates = [
            // Service completed → WhatsApp customer
            [
                'name' => 'WhatsApp: Servis Selesai → Customer',
                'event' => 'service.selesai',
                'entity_type' => \App\Models\Tenant\Service::class,
                'workflow_key' => 'service',
                'conditions' => json_encode([['field' => 'status', 'operator' => '=', 'value' => 'selesai']]),
                'action_type' => 'send_whatsapp',
                'action_config' => json_encode([
                    'recipient' => 'customer',
                    'message' => 'Halo {customer_name}, servis #{id} ({tracking_code}) sudah SELESAI. Silakan ambil di cabang kami. Terima kasih!',
                    'template' => 'service_complete',
                ]),
                'is_template' => true, 'template_key' => 'whatsapp_service_complete',
            ],
            // Request created → WhatsApp customer
            [
                'name' => 'WhatsApp: Request Dibuat → Customer',
                'event' => 'request.draft_to_waiting',
                'entity_type' => \App\Models\Tenant\Request::class,
                'workflow_key' => 'request',
                'conditions' => null,
                'action_type' => 'send_whatsapp',
                'action_config' => json_encode([
                    'recipient' => 'customer',
                    'message' => 'Request #{id} ({tracking_code}) telah dibuat. Kami akan segera memproses. Info: {date}',
                    'template' => 'request_created',
                ]),
                'is_template' => true, 'template_key' => 'whatsapp_request_created',
            ],
            // Need approval → WhatsApp owner
            [
                'name' => 'WhatsApp: Menunggu Approval → Owner',
                'event' => 'request.quotation_to_waiting_approval',
                'entity_type' => \App\Models\Tenant\Request::class,
                'workflow_key' => 'request',
                'conditions' => null,
                'action_type' => 'send_whatsapp',
                'action_config' => json_encode([
                    'recipient' => 'owner',
                    'message' => 'Request #{id} ({tracking_code}) menunggu APPROVAL. Mohon segera direview.',
                    'template' => 'request_approval',
                ]),
                'is_template' => true, 'template_key' => 'whatsapp_request_approval',
            ],
            // Request ready pickup → WhatsApp customer
            [
                'name' => 'WhatsApp: Siap Diambil → Customer',
                'event' => 'request.qc_to_ready_pickup',
                'entity_type' => \App\Models\Tenant\Request::class,
                'workflow_key' => 'request',
                'conditions' => null,
                'action_type' => 'send_whatsapp',
                'action_config' => json_encode([
                    'recipient' => 'customer',
                    'message' => 'Request #{id} ({tracking_code}) sudah SIAP DIAMBIL. Silakan datang ke cabang kami.',
                    'template' => 'request_ready',
                ]),
                'is_template' => true, 'template_key' => 'whatsapp_request_ready',
            ],
            // Completed → Generate Review Link (3 days later)
            [
                'name' => 'Review Link: 3 Hari Setelah Selesai',
                'event' => 'request.ready_pickup_to_delivered',
                'entity_type' => \App\Models\Tenant\Request::class,
                'workflow_key' => 'request',
                'conditions' => null,
                'action_type' => 'generate_review',
                'action_config' => json_encode([
                    'recipient' => 'customer',
                    'message' => 'Terima kasih telah menggunakan ServiceKU! Beri kami review di Google Maps.',
                    'review_url' => 'https://g.page/r/REPLACE_WITH_YOUR_PLACE_ID/review',
                ]),
                'delay_minutes' => 4320, // 3 days
                'is_template' => true, 'template_key' => 'review_3days',
            ],
            // Timeline for every service status change
            [
                'name' => 'Timeline: Setiap Perubahan Status Service',
                'event' => 'service.status_changed',
                'entity_type' => \App\Models\Tenant\Service::class,
                'workflow_key' => 'service',
                'conditions' => null,
                'action_type' => 'create_timeline',
                'action_config' => json_encode([
                    'event' => 'service_status_changed',
                    'label' => 'Status Service berubah menjadi {status}',
                    'description' => 'Service #{id} ({tracking_code}) status updated.',
                ]),
                'is_template' => true, 'template_key' => 'timeline_service_status',
            ],
            // Timeline for every request status change
            [
                'name' => 'Timeline: Setiap Perubahan Status Request',
                'event' => 'request.status_changed',
                'entity_type' => \App\Models\Tenant\Request::class,
                'workflow_key' => 'request',
                'conditions' => null,
                'action_type' => 'create_timeline',
                'action_config' => json_encode([
                    'event' => 'request_status_changed',
                    'label' => 'Status Request berubah menjadi {status}',
                    'description' => 'Request #{id} ({tracking_code}) status updated.',
                ]),
                'is_template' => true, 'template_key' => 'timeline_request_status',
            ],
            // Escalation: Repair > 3 days → notify manager
            [
                'name' => 'Escalation: Repair > 3 Hari → Manager',
                'event' => 'service.dikerjakan',
                'entity_type' => \App\Models\Tenant\Service::class,
                'workflow_key' => 'service',
                'conditions' => null,
                'action_type' => 'send_whatsapp',
                'action_config' => json_encode([
                    'recipient' => 'owner',
                    'message' => '⚠️ Service #{id} ({tracking_code}) sudah 3+ hari dalam status DIKERJAKAN. Mohon perhatian.',
                    'template' => 'escalation_repair',
                ]),
                'delay_minutes' => 4320, // 3 days
                'is_template' => true, 'template_key' => 'escalation_repair_3days',
            ],
        ];

        foreach ($automationTemplates as $rule) {
            AutomationRule::create($rule);
        }

        // ============================================================
        // SECTION 10: SLA CONFIGURATIONS
        // ============================================================
        $slaConfigs = [
            // Normal SLA
            [
                'workflow_key' => 'service', 'priority' => 'normal',
                'target_checking_minutes' => 1440,  // 1 day = 24h
                'target_repair_minutes' => 4320,     // 3 days
                'target_qc_minutes' => 720,          // 0.5 day
                'target_delivery_minutes' => 1440,   // 1 day
                'escalation_level1_minutes' => 4320,  // 3 days → manager
                'escalation_level2_minutes' => 10080, // 7 days → owner
                'escalation_level1_role' => 'manager',
                'escalation_level2_role' => 'owner',
            ],
            // Priority SLA
            [
                'workflow_key' => 'service', 'priority' => 'priority',
                'target_checking_minutes' => 480,   // 8h
                'target_repair_minutes' => 1440,    // 1 day
                'target_qc_minutes' => 360,          // 6h
                'target_delivery_minutes' => 480,    // 8h
                'escalation_level1_minutes' => 1440, // 1 day → manager
                'escalation_level2_minutes' => 2880, // 2 days → owner
                'escalation_level1_role' => 'manager',
                'escalation_level2_role' => 'owner',
            ],
            // Express SLA
            [
                'workflow_key' => 'service', 'priority' => 'express',
                'target_checking_minutes' => 120,    // 2h
                'target_repair_minutes' => 480,      // 8h
                'target_qc_minutes' => 120,           // 2h
                'target_delivery_minutes' => 240,     // 4h
                'escalation_level1_minutes' => 480,   // 8h → manager
                'escalation_level2_minutes' => 960,   // 16h → owner
                'escalation_level1_role' => 'manager',
                'escalation_level2_role' => 'owner',
            ],
            // Request workflow SLA
            [
                'workflow_key' => 'request', 'priority' => 'normal',
                'target_checking_minutes' => 1440,
                'target_repair_minutes' => 4320,
                'target_qc_minutes' => 720,
                'target_delivery_minutes' => 1440,
                'escalation_level1_minutes' => 4320,
                'escalation_level2_minutes' => 10080,
                'escalation_level1_role' => 'manager',
                'escalation_level2_role' => 'owner',
            ],
        ];

        foreach ($slaConfigs as $cfg) {
            SlaConfig::create($cfg);
        }
    }
}
