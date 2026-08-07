<?php

namespace App\Enterprise\Definitions;

use App\Enterprise\Data\DataDefinition;
use App\Enterprise\Data\ColumnDefinition;
use App\Enterprise\Data\FilterDefinition;
use App\Enterprise\Data\BulkAction;
use App\Enterprise\Form\FormDefinition;
use App\Enterprise\Form\FormField;
use App\Enterprise\Form\FormSection;
use App\Enterprise\Form\FormAction;
use App\Workspace\WorkspaceDefinition;
use App\Enterprise\Reporting\ReportDefinition;
use App\Enterprise\Reporting\MetricDefinition;
use App\Enterprise\Reporting\DimensionDefinition;
use App\Enterprise\Reporting\ReportFilter;
use App\Enterprise\Automation\AutomationDefinition;
use App\Enterprise\Automation\AutomationStep;
use App\Enterprise\Automation\TriggerType;
use App\Enterprise\Automation\ActionType;

/**
 * ManufacturingDefinitions — ALL Enterprise definitions for Manufacturing, Assembly & Production.
 * 
 * Covers: Production Order, BOM (Bill of Materials), Routing, Work Center,
 * MRP (Material Requirement Planning), Shop Floor Control, Quality Control,
 * Production Costing, Production Planning.
 * 
 * MODUL ERP KESEPULUH — ENTERPRISE MANUFACTURING, ASSEMBLY & PRODUCTION
 */
class ManufacturingDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // MANUFACTURING WORKSPACE (15 tabs)
    // ═══════════════════════════════════════════════════════════

    public static function workspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'manufacturing',
            title: 'Manufacturing Workspace',
            icon: '🏭',
            tabs: [
                ['id' => 'overview',          'label' => 'Overview',           'icon' => '📊'],
                ['id' => 'production_order',  'label' => 'Production Order',   'icon' => '📋'],
                ['id' => 'bom',               'label' => 'BOM',                'icon' => '📦'],
                ['id' => 'routing',           'label' => 'Routing',            'icon' => '🗺️'],
                ['id' => 'work_center',       'label' => 'Work Center',        'icon' => '⚙️'],
                ['id' => 'materials',         'label' => 'Materials',          'icon' => '🧱'],
                ['id' => 'operations',        'label' => 'Operations',         'icon' => '🔧'],
                ['id' => 'quality_control',   'label' => 'Quality Control',    'icon' => '✅'],
                ['id' => 'output',            'label' => 'Output',             'icon' => '📤'],
                ['id' => 'costing',           'label' => 'Costing',            'icon' => '💰'],
                ['id' => 'maintenance',       'label' => 'Maintenance',        'icon' => '🔩'],
                ['id' => 'timeline',          'label' => 'Timeline',           'icon' => '🕐'],
                ['id' => 'activity',          'label' => 'Activity Log',       'icon' => '📊'],
                ['id' => 'documents',         'label' => 'Documents',          'icon' => '📄'],
                ['id' => 'history',           'label' => 'History',            'icon' => '📜'],
            ],
            actions: [
                ['id' => 'new_production',    'label' => 'New Production Order','roles' => ['owner','production_manager','production_supervisor']],
                ['id' => 'start_production',  'label' => 'Start Production',   'roles' => ['owner','production_manager','production_supervisor','operator']],
                ['id' => 'record_output',     'label' => 'Record Output',      'roles' => ['owner','production_manager','production_supervisor','operator']],
                ['id' => 'qc_inspection',     'label' => 'QC Inspection',      'roles' => ['owner','production_manager','qc']],
                ['id' => 'material_request',  'label' => 'Request Materials',  'roles' => ['owner','production_manager','production_supervisor']],
                ['id' => 'update_routing',    'label' => 'Update Routing',     'roles' => ['owner','production_manager']],
                ['id' => 'close_order',       'label' => 'Close Order',        'roles' => ['owner','production_manager']],
                ['id' => 'export',            'label' => 'Export',             'roles' => ['owner','production_manager','management']],
            ],
            sidebarWidgets: [
                ['id' => 'production_status',  'component' => 'ProductionStatus', 'priority' => 10],
                ['id' => 'material_alerts',    'component' => 'MaterialAlerts',   'priority' => 20],
                ['id' => 'machine_status',     'component' => 'MachineStatus',    'priority' => 30],
                ['id' => 'quick_actions',      'component' => 'QuickActions',     'priority' => 40],
            ],
            features: ['manufacturing'],
            permissions: ['manage_manufacturing'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // PRODUCTION ORDER — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function productionTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'manufacturing.production.index',
            title: 'Production Orders',
            modelClass: \App\Models\Tenant\ProductionOrder::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['manufacturing'],
        ))
            ->addColumns([
                new ColumnDefinition('production_number','No. Produksi',  type:'text',    sortable:true, bold:true, width:'130px', order:1),
                new ColumnDefinition('product_name',     'Produk',        type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('production_type',  'Tipe',          type:'badge',   sortable:true, filterable:true, width:'100px', order:3),
                new ColumnDefinition('bom_version',      'BOM Ver',       type:'text',    width:'70px', align:'center', order:4),
                new ColumnDefinition('work_center',      'Work Center',   type:'text',    sortable:true, width:'100px', order:5),
                new ColumnDefinition('planned_qty',      'Qty Rencana',   type:'number',  width:'80px', align:'center', order:6),
                new ColumnDefinition('produced_qty',     'Qty Hasil',     type:'number',  width:'70px', align:'center', bold:true, order:7),
                new ColumnDefinition('rejected_qty',     'Reject',        type:'number',  width:'60px', align:'center', order:8),
                new ColumnDefinition('start_date',       'Mulai',         type:'date',    sortable:true, width:'100px', order:9),
                new ColumnDefinition('finish_date',      'Selesai',       type:'date',    sortable:true, width:'100px', order:10),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'100px', order:11),
                new ColumnDefinition('supervisor_name',  'Supervisor',    type:'text',    width:'110px', order:12),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'draft','label'=>'Draft'],
                ['value'=>'planned','label'=>'Planned'],
                ['value'=>'in_progress','label'=>'In Progress'],
                ['value'=>'paused','label'=>'Paused'],
                ['value'=>'completed','label'=>'Completed'],
                ['value'=>'closed','label'=>'Closed'],
                ['value'=>'cancelled','label'=>'Cancelled'],
            ], order:1))
            ->addFilter(new FilterDefinition('production_type', 'Tipe', type:'select', quick:true, options:[
                ['value'=>'assembly','label'=>'Assembly'],
                ['value'=>'disassembly','label'=>'Disassembly'],
                ['value'=>'rework','label'=>'Rework'],
                ['value'=>'refurbishment','label'=>'Refurbishment'],
                ['value'=>'repair_build','label'=>'Repair Build'],
                ['value'=>'custom_assembly','label'=>'Custom Assembly'],
                ['value'=>'batch','label'=>'Batch'],
                ['value'=>'make_to_stock','label'=>'Make To Stock'],
                ['value'=>'make_to_order','label'=>'Make To Order'],
            ], order:2))
            ->addFilter(new FilterDefinition('work_center_id', 'Work Center', type:'select', order:3))
            ->addFilter(new FilterDefinition('start_date', 'Tanggal', type:'date_range', order:4))
            ->addFilter(new FilterDefinition('priority', 'Prioritas', type:'select', options:[['value'=>'critical','label'=>'Critical'],['value'=>'high','label'=>'High'],['value'=>'medium','label'=>'Medium'],['value'=>'low','label'=>'Low']], order:5))
            ->addBulkAction(new BulkAction('start', 'Start', variant:'primary'))
            ->addBulkAction(new BulkAction('complete', 'Complete', variant:'success'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'))
            ->addBulkAction(new BulkAction('cancel', 'Cancel', variant:'danger', confirm:true));
    }

    // ═══════════════════════════════════════════════════════════
    // PRODUCTION ORDER — Form
    // ═══════════════════════════════════════════════════════════

    public static function productionForm(): FormDefinition
    {
        return (new FormDefinition(
            id: 'manufacturing.production.create',
            title: 'Production Order',
            method: 'POST',
            endpoint: '/manufacturing/production',
            features: ['manufacturing'],
        ))
            ->addSection(new FormSection(id:'general',    label:'Informasi Umum',    icon:'📋', cols:2))
            ->addSection(new FormSection(id:'product',    label:'Product & BOM',     icon:'📦', cols:2))
            ->addSection(new FormSection(id:'routing',    label:'Routing',           icon:'🗺️', cols:2))
            ->addSection(new FormSection(id:'materials',  label:'Materials',         icon:'🧱', cols:1))
            ->addSection(new FormSection(id:'operations', label:'Operations',        icon:'🔧', cols:1))
            ->addSection(new FormSection(id:'resources',  label:'Resources',         icon:'👥', cols:2))
            ->addSection(new FormSection(id:'quality',    label:'Quality',           icon:'✅', cols:2))
            ->addSection(new FormSection(id:'costing',    label:'Costing',           icon:'💰', cols:2))
            ->addSection(new FormSection(id:'notes',      label:'Catatan',           icon:'📝', cols:1))
            ->addFields([
                // General
                new FormField('production_type',  type:'select',   label:'Tipe Produksi',       required:true, section:'general', cols:4,
                    options:[['value'=>'assembly','label'=>'Assembly'],['value'=>'batch','label'=>'Batch'],['value'=>'make_to_stock','label'=>'Make To Stock'],['value'=>'make_to_order','label'=>'Make To Order'],['value'=>'rework','label'=>'Rework'],['value'=>'refurbishment','label'=>'Refurbishment']]),
                new FormField('priority',         type:'select',   label:'Prioritas',           section:'general', cols:4, options:[['value'=>'critical','label'=>'Critical'],['value'=>'high','label'=>'High'],['value'=>'medium','label'=>'Medium'],['value'=>'low','label'=>'Low']]),
                new FormField('supervisor_id',    type:'select',   label:'Supervisor',          section:'general', cols:4),
                new FormField('team_members',     type:'select',   label:'Team',                section:'general', cols:8, multiple:true),
                new FormField('notes',            type:'textarea', label:'Notes',               section:'general', cols:12),
                // Product
                new FormField('product_id',       type:'select',   label:'Product',             required:true, section:'product', cols:6),
                new FormField('bom_version',      type:'select',   label:'BOM Version',         required:true, section:'product', cols:6),
                new FormField('planned_qty',      type:'number',   label:'Planned Quantity',    required:true, section:'product', cols:4),
                new FormField('batch_number',     type:'text',     label:'Batch Number',        section:'product', cols:4),
                new FormField('serial_start',     type:'text',     label:'Serial Start',        section:'product', cols:4),
                // Routing
                new FormField('routing_id',       type:'select',   label:'Routing',             required:true, section:'routing', cols:6),
                new FormField('work_center_id',   type:'select',   label:'Work Center',         required:true, section:'routing', cols:6),
                new FormField('start_date',       type:'date',     label:'Start Date',          required:true, section:'routing', cols:4),
                new FormField('finish_date',      type:'date',     label:'Finish Date',         required:true, section:'routing', cols:4),
                // Materials (repeater)
                new FormField('materials',        type:'repeater', label:'Material List',        section:'materials', cols:12, fields:[
                    ['name'=>'material_id','type'=>'select','label'=>'Material','cols'=>5],
                    ['name'=>'required_qty','type'=>'number','label'=>'Required','cols'=>3],
                    ['name'=>'available_qty','type'=>'number','label'=>'Available','cols'=>3],
                    ['name'=>'status','type'=>'text','label'=>'Status','cols'=>1],
                ]),
                // Operations (repeater)
                new FormField('operations',       type:'repeater', label:'Operation Sequence',   section:'operations', cols:12, fields:[
                    ['name'=>'seq','type'=>'number','label'=>'Seq','cols'=>1],
                    ['name'=>'operation','type'=>'text','label'=>'Operation','cols'=>4],
                    ['name'=>'machine','type'=>'select','label'=>'Machine','cols'=>3],
                    ['name'=>'setup_time','type'=>'number','label'=>'Setup (min)','cols'=>2],
                    ['name'=>'run_time','type'=>'number','label'=>'Run (min)','cols'=>2],
                ]),
                // Resources
                new FormField('machine_id',       type:'select',   label:'Machine',             section:'resources', cols:4),
                new FormField('operator_count',   type:'number',   label:'Operator Count',      section:'resources', cols:4),
                new FormField('shift_id',         type:'select',   label:'Shift',               section:'resources', cols:4),
                // Quality
                new FormField('qc_plan_id',       type:'select',   label:'QC Plan',             section:'quality', cols:6),
                new FormField('inspection_freq',  type:'select',   label:'Inspection Frequency',section:'quality', cols:6),
                // Costing
                new FormField('standard_cost',    type:'currency', label:'Standard Cost',        section:'costing', cols:4),
                new FormField('material_budget',  type:'currency', label:'Material Budget',      section:'costing', cols:4),
                new FormField('labor_budget',     type:'currency', label:'Labor Budget',         section:'costing', cols:4),
                new FormField('overhead_budget',  type:'currency', label:'Overhead Budget',      section:'costing', cols:4),
                // Notes
                new FormField('internal_notes',   type:'textarea', label:'Internal Notes',       section:'notes', cols:12),
            ])
            ->addAction(new FormAction('save_draft', 'Save Draft', variant:'outline'))
            ->addAction(new FormAction('plan', 'Plan Production', variant:'primary', shortcut:'Ctrl+S'))
            ->addAction(new FormAction('save_and_new', 'Save & New', variant:'secondary'));
    }

    // ═══════════════════════════════════════════════════════════
    // BOM — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function bomTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'manufacturing.bom.index',
            title: 'Bill of Materials',
            modelClass: \App\Models\Tenant\BillOfMaterial::class,
            defaultSort: ['product_name' => 'asc', 'version' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['manufacturing'],
        ))
            ->addColumns([
                new ColumnDefinition('bom_code',         'BOM Code',      type:'text',    sortable:true, bold:true, width:'100px', order:1),
                new ColumnDefinition('product_name',     'Product',       type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('version',          'Version',       type:'text',    sortable:true, width:'70px', align:'center', order:3),
                new ColumnDefinition('level',            'Level',         type:'number',  width:'55px', align:'center', order:4),
                new ColumnDefinition('component_count',  'Components',    type:'number',  width:'80px', align:'center', order:5),
                new ColumnDefinition('total_cost',       'Total Cost',    type:'currency', sortable:true, align:'right', width:'120px', order:6),
                new ColumnDefinition('effective_date',   'Effective',     type:'date',    sortable:true, width:'100px', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'80px', order:8),
                new ColumnDefinition('approved_by',      'Approved By',   type:'text',    width:'100px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'draft','label'=>'Draft'],
                ['value'=>'active','label'=>'Active'],
                ['value'=>'inactive','label'=>'Inactive'],
                ['value'=>'archived','label'=>'Archived'],
            ], order:1))
            ->addFilter(new FilterDefinition('product_id', 'Product', type:'select', order:2))
            ->addBulkAction(new BulkAction('approve', 'Approve', variant:'primary'))
            ->addBulkAction(new BulkAction('copy', 'Copy BOM', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // ROUTING — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function routingTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'manufacturing.routing.index',
            title: 'Routing Management',
            modelClass: \App\Models\Tenant\Routing::class,
            defaultSort: ['product_name' => 'asc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['manufacturing'],
        ))
            ->addColumns([
                new ColumnDefinition('routing_code',     'Routing Code',  type:'text',    sortable:true, bold:true, width:'110px', order:1),
                new ColumnDefinition('product_name',     'Product',       type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('operations_count', 'Operations',    type:'number',  width:'80px', align:'center', order:3),
                new ColumnDefinition('total_setup_time', 'Setup (min)',   type:'number',  width:'80px', align:'center', order:4),
                new ColumnDefinition('total_run_time',   'Run (min)',     type:'number',  width:'80px', align:'center', order:5),
                new ColumnDefinition('total_time',       'Total (min)',   type:'number',  width:'80px', align:'center', bold:true, order:6),
                new ColumnDefinition('version',          'Version',       type:'text',    width:'70px', align:'center', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, width:'80px', order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[['value'=>'active','label'=>'Active'],['value'=>'inactive','label'=>'Inactive']], order:1))
            ->addFilter(new FilterDefinition('product_id', 'Product', type:'select', order:2))
            ->addBulkAction(new BulkAction('activate', 'Activate', variant:'primary'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // WORK CENTER — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function workCenterTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'manufacturing.work_center.index',
            title: 'Work Centers',
            modelClass: \App\Models\Tenant\WorkCenter::class,
            defaultSort: ['work_center_name' => 'asc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['manufacturing'],
        ))
            ->addColumns([
                new ColumnDefinition('work_center_code', 'Code',          type:'text',    sortable:true, bold:true, width:'90px', order:1),
                new ColumnDefinition('work_center_name', 'Work Center',   type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('type',             'Tipe',          type:'badge',   sortable:true, width:'90px', order:3),
                new ColumnDefinition('capacity_per_day', 'Capacity/Day',  type:'number',  sortable:true, width:'90px', align:'center', order:4),
                new ColumnDefinition('efficiency_pct',   'Efficiency %',  type:'number',  sortable:true, width:'80px', align:'center', order:5),
                new ColumnDefinition('utilization_pct',  'Utilization %', type:'number',  sortable:true, width:'80px', align:'center', order:6),
                new ColumnDefinition('current_load',     'Current Load',  type:'number',  width:'80px', align:'center', order:7),
                new ColumnDefinition('shift',            'Shift',         type:'text',    width:'70px', order:8),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, width:'90px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'running','label'=>'Running'],
                ['value'=>'idle','label'=>'Idle'],
                ['value'=>'maintenance','label'=>'Maintenance'],
                ['value'=>'down','label'=>'Down'],
            ], order:1))
            ->addFilter(new FilterDefinition('type', 'Tipe', type:'select', options:[['value'=>'machine','label'=>'Machine'],['value'=>'line','label'=>'Production Line'],['value'=>'cell','label'=>'Work Cell']], order:2))
            ->addBulkAction(new BulkAction('start', 'Start', variant:'primary'))
            ->addBulkAction(new BulkAction('stop', 'Stop', variant:'danger'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // QUALITY CONTROL — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function qcTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'manufacturing.qc.index',
            title: 'Quality Control',
            modelClass: \App\Models\Tenant\QualityControl::class,
            defaultSort: ['inspection_date' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['manufacturing'],
        ))
            ->addColumns([
                new ColumnDefinition('inspection_number','QC #',         type:'text',    sortable:true, bold:true, width:'100px', order:1),
                new ColumnDefinition('production_number','Production #',  type:'text',    sortable:true, width:'120px', order:2),
                new ColumnDefinition('qc_type',          'Tipe',          type:'badge',   sortable:true, filterable:true, width:'90px', order:3),
                new ColumnDefinition('inspected_qty',    'Inspected',     type:'number',  width:'70px', align:'center', order:4),
                new ColumnDefinition('passed_qty',       'Passed',        type:'number',  width:'60px', align:'center', order:5),
                new ColumnDefinition('failed_qty',       'Failed',        type:'number',  width:'60px', align:'center', order:6),
                new ColumnDefinition('defect_type',      'Defect',        type:'text',    width:'120px', order:7),
                new ColumnDefinition('inspector_name',   'Inspector',     type:'text',    width:'100px', order:8),
                new ColumnDefinition('result',           'Result',        type:'badge',   sortable:true, width:'80px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('qc_type', 'Tipe', type:'select', quick:true, options:[
                ['value'=>'incoming','label'=>'Incoming QC'],
                ['value'=>'in_process','label'=>'In Process QC'],
                ['value'=>'final','label'=>'Final QC'],
            ], order:1))
            ->addFilter(new FilterDefinition('result', 'Result', type:'select', quick:true, options:[['value'=>'passed','label'=>'Passed'],['value'=>'failed','label'=>'Failed']], order:2))
            ->addFilter(new FilterDefinition('inspection_date', 'Tanggal', type:'date_range', order:3))
            ->addBulkAction(new BulkAction('approve', 'Approve', variant:'primary'))
            ->addBulkAction(new BulkAction('create_capa', 'Create CAPA', variant:'warning'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES — 15 Rules
    // ═══════════════════════════════════════════════════════════

    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            (new AutomationDefinition('mfg.production_created', 'Production Created',
                trigger: TriggerType::RECORD_CREATED, module: 'manufacturing'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🏭 New Production', 'body' => 'Production #{{subject.production_number}} created.'])),

            (new AutomationDefinition('mfg.production_approved', 'Production Approved',
                trigger: TriggerType::RECORD_UPDATED, module: 'manufacturing'))
                ->addStep(new AutomationStep(ActionType::RESERVE_MATERIALS, []))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '✅ Production Approved', 'body' => '#{{subject.production_number}} approved.', 'roles' => ['production_supervisor', 'operator']])),

            (new AutomationDefinition('mfg.production_started', 'Production Started',
                trigger: TriggerType::RECORD_UPDATED, module: 'manufacturing'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '▶️ Production #{{subject.production_number}} started.'])),

            (new AutomationDefinition('mfg.production_paused', 'Production Paused',
                trigger: TriggerType::RECORD_UPDATED, module: 'manufacturing'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '⏸️ Production Paused', 'body' => '#{{subject.production_number}} paused.', 'roles' => ['production_manager']])),

            (new AutomationDefinition('mfg.production_completed', 'Production Completed',
                trigger: TriggerType::RECORD_UPDATED, module: 'manufacturing'))
                ->addStep(new AutomationStep(ActionType::UPDATE_INVENTORY, ['movement' => 'production_output']))
                ->addStep(new AutomationStep(ActionType::CREATE_JOURNAL, ['template' => 'production_completion']))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '✅ Production #{{subject.production_number}} completed. Qty: {{subject.produced_qty}}.'])),

            (new AutomationDefinition('mfg.production_delayed', 'Production Delayed',
                trigger: TriggerType::DATE_REACHED, module: 'manufacturing'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '⚠️ Production Delayed', 'body' => '#{{subject.production_number}} is behind schedule.', 'roles' => ['production_manager', 'management']])),

            (new AutomationDefinition('mfg.material_shortage', 'Material Shortage',
                trigger: TriggerType::RECORD_UPDATED, module: 'manufacturing'))
                ->addStep(new AutomationStep(ActionType::CREATE_PURCHASE_SUGGESTION, ['type' => 'material_shortage']))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🧱 Material Shortage', 'body' => 'Production #{{subject.production_number}} has material shortage.', 'roles' => ['purchasing', 'production_manager']])),

            (new AutomationDefinition('mfg.qc_failed', 'QC Failed',
                trigger: TriggerType::RECORD_UPDATED, module: 'manufacturing'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'CAPA: QC Failed #{{subject.inspection_number}}', 'assignee_role' => 'qc']))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '❌ QC Failed', 'body' => 'Inspection #{{subject.inspection_number}} failed.', 'roles' => ['qc', 'production_manager']])),

            (new AutomationDefinition('mfg.qc_passed', 'QC Passed',
                trigger: TriggerType::RECORD_UPDATED, module: 'manufacturing'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '✅ QC #{{subject.inspection_number}} passed.'])),

            (new AutomationDefinition('mfg.machine_down', 'Machine Down',
                trigger: TriggerType::RECORD_UPDATED, module: 'manufacturing'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'Repair: {{subject.work_center_name}}', 'assignee_role' => 'maintenance']))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🔧 Machine Down', 'body' => '{{subject.work_center_name}} is down!', 'roles' => ['maintenance', 'production_manager']])),

            (new AutomationDefinition('mfg.maintenance_due', 'Machine Maintenance Due',
                trigger: TriggerType::DATE_REACHED, module: 'manufacturing'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'Maintenance: {{subject.work_center_name}}', 'assignee_role' => 'maintenance'])),

            (new AutomationDefinition('mfg.production_closed', 'Production Closed',
                trigger: TriggerType::RECORD_UPDATED, module: 'manufacturing'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '📦 Production #{{subject.production_number}} closed.'])),

            (new AutomationDefinition('mfg.bom_changed', 'BOM Changed',
                trigger: TriggerType::RECORD_UPDATED, module: 'manufacturing'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '📦 BOM Updated', 'body' => 'BOM {{subject.bom_code}} v{{subject.version}} updated.', 'roles' => ['production_manager']])),

            (new AutomationDefinition('mfg.routing_updated', 'Routing Updated',
                trigger: TriggerType::RECORD_UPDATED, module: 'manufacturing'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🗺️ Routing Updated', 'body' => 'Routing {{subject.routing_code}} updated.', 'roles' => ['production_manager']])),

            (new AutomationDefinition('mfg.mrp_generated', 'MRP Generated',
                trigger: TriggerType::RECORD_CREATED, module: 'manufacturing'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'Review MRP Recommendations', 'assignee_role' => 'production_manager'])),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // REPORTING DEFINITIONS — 14 Reports
    // ═══════════════════════════════════════════════════════════

    /** @return ReportDefinition[] */
    public static function reports(): array
    {
        return [
            (new ReportDefinition('mfg.production_summary', 'Production Summary',
                type:'summary', chartType:'table', features:['manufacturing']))
                ->addMetric(new MetricDefinition('total_orders', 'Orders', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('planned_qty', 'Planned', 'sum', 'planned_qty', format:'number'))
                ->addMetric(new MetricDefinition('produced_qty', 'Produced', 'sum', 'produced_qty', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('yield_pct', 'Yield %', 'expression', 'produced_qty / planned_qty * 100', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('product_name', 'Product', 'product_name', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            (new ReportDefinition('mfg.production_efficiency', 'Production Efficiency',
                type:'summary', chartType:'bar', features:['manufacturing']))
                ->addMetric(new MetricDefinition('efficiency_pct', 'Efficiency %', 'avg', 'efficiency_pct', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('on_time_pct', 'On Time %', 'avg', 'on_time_pct', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('delay_hours', 'Delay (h)', 'sum', 'delay_hours', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('work_center', 'Work Center', 'work_center_name', type:'string')),

            (new ReportDefinition('mfg.oee', 'OEE Analysis',
                type:'summary', chartType:'kpi', features:['manufacturing']))
                ->addMetric(new MetricDefinition('availability', 'Availability %', 'avg', 'availability_pct', format:'number', color:'primary', icon:'⏱️'))
                ->addMetric(new MetricDefinition('performance', 'Performance %', 'avg', 'performance_pct', format:'number', color:'info', icon:'⚡'))
                ->addMetric(new MetricDefinition('quality', 'Quality %', 'avg', 'quality_pct', format:'number', color:'success', icon:'✅'))
                ->addMetric(new MetricDefinition('oee', 'OEE %', 'expression', 'availability * performance * quality / 10000', format:'number', color:'primary', icon:'📊')),

            (new ReportDefinition('mfg.machine_utilization', 'Machine Utilization',
                type:'summary', chartType:'bar', features:['manufacturing']))
                ->addMetric(new MetricDefinition('utilization_pct', 'Utilization %', 'avg', 'utilization_pct', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('downtime_hours', 'Downtime (h)', 'sum', 'downtime_hours', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('machine', 'Machine', 'work_center_name', type:'string')),

            (new ReportDefinition('mfg.bom_cost', 'BOM Cost Analysis',
                type:'summary', chartType:'table', features:['manufacturing']))
                ->addMetric(new MetricDefinition('material_cost', 'Material', 'sum', 'material_cost', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('labor_cost', 'Labor', 'sum', 'labor_cost', format:'currency', color:'info'))
                ->addMetric(new MetricDefinition('overhead', 'Overhead', 'sum', 'overhead_cost', format:'currency', color:'warning'))
                ->addMetric(new MetricDefinition('total_cost', 'Total', 'sum', 'total_cost', format:'currency', color:'danger'))
                ->addDimension(new DimensionDefinition('product', 'Product', 'product_name', type:'string')),

            (new ReportDefinition('mfg.material_usage', 'Material Usage',
                type:'summary', chartType:'table', features:['manufacturing']))
                ->addMetric(new MetricDefinition('planned_qty', 'Planned', 'sum', 'planned_qty', format:'number'))
                ->addMetric(new MetricDefinition('actual_qty', 'Actual', 'sum', 'actual_qty', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('variance', 'Variance', 'expression', 'actual_qty - planned_qty', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('material', 'Material', 'material_name', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            (new ReportDefinition('mfg.scrap_analysis', 'Scrap Analysis',
                type:'summary', chartType:'bar', features:['manufacturing']))
                ->addMetric(new MetricDefinition('scrap_qty', 'Scrap Qty', 'sum', 'scrap_qty', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('scrap_cost', 'Scrap Cost', 'sum', 'scrap_cost', format:'currency', color:'warning'))
                ->addMetric(new MetricDefinition('scrap_rate', 'Scrap Rate %', 'expression', 'scrap_qty / produced_qty * 100', format:'number'))
                ->addDimension(new DimensionDefinition('product', 'Product', 'product_name', type:'string')),

            (new ReportDefinition('mfg.qc_analysis', 'QC Analysis',
                type:'summary', chartType:'table', features:['manufacturing']))
                ->addMetric(new MetricDefinition('inspected', 'Inspected', 'sum', 'inspected_qty', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('passed', 'Passed', 'sum', 'passed_qty', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('failed', 'Failed', 'sum', 'failed_qty', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('pass_rate', 'Pass Rate %', 'expression', 'passed / inspected * 100', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('defect_type', 'Defect', 'defect_type', type:'string')),

            (new ReportDefinition('mfg.production_cost', 'Production Cost',
                type:'summary', chartType:'bar', features:['manufacturing'], permissions:['manage_finance']))
                ->addMetric(new MetricDefinition('material', 'Material', 'sum', 'material_cost', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('labor', 'Labor', 'sum', 'labor_cost', format:'currency', color:'info'))
                ->addMetric(new MetricDefinition('machine', 'Machine', 'sum', 'machine_cost', format:'currency', color:'warning'))
                ->addMetric(new MetricDefinition('total', 'Total', 'sum', 'total_cost', format:'currency', color:'danger'))
                ->addDimension(new DimensionDefinition('production_number', 'Order', 'production_number', type:'string')),

            (new ReportDefinition('mfg.capacity_planning', 'Capacity Planning',
                type:'summary', chartType:'bar', features:['manufacturing']))
                ->addMetric(new MetricDefinition('capacity', 'Capacity', 'sum', 'capacity_per_day', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('load', 'Load', 'sum', 'current_load', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('available', 'Available %', 'expression', '(capacity - load) / capacity * 100', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('work_center', 'Work Center', 'work_center_name', type:'string')),

            (new ReportDefinition('mfg.production_delay', 'Production Delay Analysis',
                type:'summary', chartType:'table', features:['manufacturing']))
                ->addMetric(new MetricDefinition('delay_count', 'Delays', 'count', 'id', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('total_delay_hours', 'Total Delay (h)', 'sum', 'delay_hours', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('avg_delay_hours', 'Avg Delay (h)', 'avg', 'delay_hours', format:'number'))
                ->addDimension(new DimensionDefinition('reason', 'Reason', 'delay_reason', type:'string')),

            (new ReportDefinition('mfg.mrp_report', 'MRP Report',
                type:'summary', chartType:'table', features:['manufacturing']))
                ->addMetric(new MetricDefinition('required_qty', 'Required', 'sum', 'required_qty', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('available_qty', 'Available', 'sum', 'available_qty', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('shortage_qty', 'Shortage', 'expression', 'required_qty - available_qty', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('material', 'Material', 'material_name', type:'string')),

            (new ReportDefinition('mfg.variance_analysis', 'Variance Analysis',
                type:'summary', chartType:'bar', features:['manufacturing']))
                ->addMetric(new MetricDefinition('standard_cost', 'Standard', 'sum', 'standard_cost', format:'currency', color:'info'))
                ->addMetric(new MetricDefinition('actual_cost', 'Actual', 'sum', 'actual_cost', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('variance', 'Variance', 'expression', 'actual_cost - standard_cost', format:'currency', color:'danger'))
                ->addMetric(new MetricDefinition('variance_pct', 'Var %', 'expression', '(actual_cost - standard_cost) / standard_cost * 100', format:'number'))
                ->addDimension(new DimensionDefinition('production_number', 'Order', 'production_number', type:'string')),

            (new ReportDefinition('mfg.profitability', 'Production Profitability',
                type:'summary', chartType:'bar', features:['manufacturing'], permissions:['manage_finance']))
                ->addMetric(new MetricDefinition('revenue', 'Revenue', 'sum', 'revenue', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('cost', 'Cost', 'sum', 'total_cost', format:'currency', color:'danger'))
                ->addMetric(new MetricDefinition('profit', 'Profit', 'expression', 'revenue - cost', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('margin_pct', 'Margin %', 'expression', '(revenue - cost) / revenue * 100', format:'number'))
                ->addDimension(new DimensionDefinition('product', 'Product', 'product_name', type:'string')),
        ];
    }
}
