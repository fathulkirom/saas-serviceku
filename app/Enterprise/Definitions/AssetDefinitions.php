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
 * AssetDefinitions — ALL Enterprise definitions for Enterprise Asset Management & Maintenance.
 * 
 * Covers: Fixed Asset Register, Maintenance (Preventive/Corrective/Emergency),
 * Depreciation, Warranty, Insurance, Asset Movement, Assignment, Vehicle, Tool,
 * Calibration, Inspection.
 * 
 * MODUL ERP KETUJUH — ENTERPRISE ASSET MANAGEMENT & MAINTENANCE (EAM/CMMS)
 */
class AssetDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // ASSET WORKSPACE (14 tabs)
    // ═══════════════════════════════════════════════════════════

    public static function workspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'asset',
            title: 'Asset Workspace',
            icon: '🏗️',
            tabs: [
                ['id' => 'overview',         'label' => 'Overview',            'icon' => '📊'],
                ['id' => 'profile',          'label' => 'Profile',             'icon' => '📋'],
                ['id' => 'maintenance',      'label' => 'Maintenance',         'icon' => '🔧'],
                ['id' => 'maintenance_history','label' => 'Maintenance History','icon' => '📜'],
                ['id' => 'depreciation',     'label' => 'Depreciation',        'icon' => '📉'],
                ['id' => 'movement',         'label' => 'Movement',            'icon' => '🔄'],
                ['id' => 'assignment',       'label' => 'Assignment',          'icon' => '👤'],
                ['id' => 'warranty',         'label' => 'Warranty',            'icon' => '🛡️'],
                ['id' => 'insurance',        'label' => 'Insurance',           'icon' => '🔒'],
                ['id' => 'calibration',      'label' => 'Calibration',         'icon' => '📐'],
                ['id' => 'inspection',       'label' => 'Inspection',          'icon' => '🔍'],
                ['id' => 'documents',        'label' => 'Documents',           'icon' => '📄'],
                ['id' => 'timeline',         'label' => 'Timeline',            'icon' => '🕐'],
                ['id' => 'activity',         'label' => 'Activity Log',        'icon' => '📊'],
            ],
            actions: [
                ['id' => 'edit',             'label' => 'Edit Asset',       'roles' => ['owner','manager','maintenance','admin']],
                ['id' => 'schedule_maintenance','label' => 'Schedule Maintenance','roles' => ['owner','manager','maintenance']],
                ['id' => 'record_maintenance','label' => 'Record Maintenance','roles' => ['owner','manager','maintenance','technician']],
                ['id' => 'transfer',         'label' => 'Transfer Asset',   'roles' => ['owner','manager','maintenance','warehouse']],
                ['id' => 'assign',           'label' => 'Assign Asset',     'roles' => ['owner','manager','hrd']],
                ['id' => 'post_depreciation','label' => 'Post Depreciation','roles' => ['owner','manager','finance']],
                ['id' => 'dispose',          'label' => 'Dispose Asset',    'roles' => ['owner','manager']],
                ['id' => 'export',           'label' => 'Export',           'roles' => ['owner','manager','maintenance','finance']],
            ],
            sidebarWidgets: [
                ['id' => 'asset_quick_info',    'component' => 'AssetQuickInfo',   'priority' => 10],
                ['id' => 'maintenance_alerts',  'component' => 'MaintenanceAlerts','priority' => 20],
                ['id' => 'asset_value',         'component' => 'AssetValueCard',   'priority' => 30],
                ['id' => 'quick_actions',       'component' => 'QuickActions',     'priority' => 40],
            ],
            features: ['assets'],
            permissions: ['manage_assets'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // FIXED ASSET REGISTER — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function assetTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'asset.index',
            title: 'Fixed Asset Register',
            modelClass: \App\Models\Tenant\Asset::class,
            defaultSort: ['asset_code' => 'asc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['assets'],
        ))
            ->addColumns([
                new ColumnDefinition('asset_code',       'Kode Asset',      type:'text',    sortable:true, bold:true, width:'110px', order:1),
                new ColumnDefinition('asset_name',       'Nama Asset',      type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('category',         'Kategori',        type:'badge',   sortable:true, filterable:true, width:'100px', order:3),
                new ColumnDefinition('brand',            'Merek',           type:'text',    sortable:true, width:'90px', order:4),
                new ColumnDefinition('model',            'Model',           type:'text',    width:'100px', order:5),
                new ColumnDefinition('serial_number',    'Serial No.',      type:'text',    searchable:true, width:'120px', order:6),
                new ColumnDefinition('purchase_date',    'Tgl Beli',        type:'date',    sortable:true, width:'100px', order:7),
                new ColumnDefinition('purchase_value',   'Nilai Beli',      type:'currency', sortable:true, align:'right', width:'130px', order:8),
                new ColumnDefinition('current_value',    'Nilai Sekarang',  type:'currency', sortable:true, align:'right', bold:true, width:'130px', aggregate:true, aggregateType:'sum', order:9),
                new ColumnDefinition('department',       'Department',      type:'text',    sortable:true, width:'110px', order:10),
                new ColumnDefinition('custodian_name',   'Penanggung Jawab',type:'text',    sortable:true, width:'120px', order:11),
                new ColumnDefinition('location',         'Lokasi',          type:'text',    width:'120px', order:12),
                new ColumnDefinition('status',           'Status',          type:'badge',   sortable:true, filterable:true, width:'90px', order:13),
                new ColumnDefinition('actions',          '',                type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('category', 'Kategori', type:'select', quick:true, options:[
                ['value'=>'building','label'=>'Building'],
                ['value'=>'office','label'=>'Office'],
                ['value'=>'workshop','label'=>'Workshop'],
                ['value'=>'machine','label'=>'Machine'],
                ['value'=>'equipment','label'=>'Equipment'],
                ['value'=>'computer','label'=>'Computer'],
                ['value'=>'laptop','label'=>'Laptop'],
                ['value'=>'printer','label'=>'Printer'],
                ['value'=>'vehicle','label'=>'Vehicle'],
                ['value'=>'furniture','label'=>'Furniture'],
                ['value'=>'tool','label'=>'Tool'],
                ['value'=>'network','label'=>'Network Device'],
                ['value'=>'server','label'=>'Server'],
                ['value'=>'pos','label'=>'POS Device'],
            ], order:1))
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'active','label'=>'Active'],
                ['value'=>'maintenance','label'=>'In Maintenance'],
                ['value'=>'disposed','label'=>'Disposed'],
                ['value'=>'written_off','label'=>'Written Off'],
                ['value'=>'lost','label'=>'Lost'],
            ], order:2))
            ->addFilter(new FilterDefinition('department', 'Department', type:'select', order:3))
            ->addFilter(new FilterDefinition('branch', 'Cabang', type:'select', order:4))
            ->addFilter(new FilterDefinition('purchase_date', 'Tgl Beli', type:'date_range', order:5))
            ->addBulkAction(new BulkAction('schedule_maintenance', 'Schedule Maintenance', variant:'default'))
            ->addBulkAction(new BulkAction('transfer', 'Transfer', variant:'default'))
            ->addBulkAction(new BulkAction('dispose', 'Dispose', variant:'danger', confirm:true))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // ASSET FORM — Create/Edit
    // ═══════════════════════════════════════════════════════════

    public static function assetForm(): FormDefinition
    {
        return (new FormDefinition(
            id: 'asset.create',
            title: 'Asset Registration',
            method: 'POST',
            endpoint: '/assets',
            features: ['assets'],
        ))
            ->addSection(new FormSection(id:'general',     label:'Informasi Umum',     icon:'📋', cols:2))
            ->addSection(new FormSection(id:'financial',   label:'Informasi Keuangan', icon:'💰', cols:2))
            ->addSection(new FormSection(id:'maintenance', label:'Maintenance',        icon:'🔧', cols:2))
            ->addSection(new FormSection(id:'assignment',  label:'Assignment',         icon:'👤', cols:2))
            ->addSection(new FormSection(id:'insurance',   label:'Asuransi',           icon:'🔒', cols:2))
            ->addSection(new FormSection(id:'warranty',    label:'Garansi',            icon:'🛡️', cols:2))
            ->addSection(new FormSection(id:'depreciation',label:'Penyusutan',         icon:'📉', cols:1))
            ->addSection(new FormSection(id:'location',    label:'Lokasi',             icon:'📍', cols:2))
            ->addSection(new FormSection(id:'photos',      label:'Foto',               icon:'📸', cols:1))
            ->addSection(new FormSection(id:'documents',   label:'Dokumen',            icon:'📎', cols:1))
            ->addSection(new FormSection(id:'notes',       label:'Catatan',            icon:'📝', cols:1))
            ->addFields([
                // General
                new FormField('asset_code',       type:'text',     label:'Kode Asset',          required:true, section:'general', cols:4, maxlength:30),
                new FormField('asset_name',       type:'text',     label:'Nama Asset',          required:true, section:'general', cols:8),
                new FormField('category',         type:'select',   label:'Kategori',            required:true, section:'general', cols:4,
                    options:[['value'=>'building','label'=>'Building'],['value'=>'machine','label'=>'Machine'],['value'=>'equipment','label'=>'Equipment'],['value'=>'computer','label'=>'Computer'],['value'=>'laptop','label'=>'Laptop'],['value'=>'vehicle','label'=>'Vehicle'],['value'=>'furniture','label'=>'Furniture'],['value'=>'tool','label'=>'Tool'],['value'=>'network','label'=>'Network'],['value'=>'server','label'=>'Server']]),
                new FormField('brand',            type:'text',     label:'Merek',               section:'general', cols:4),
                new FormField('model',            type:'text',     label:'Model',               section:'general', cols:4),
                new FormField('serial_number',    type:'text',     label:'Nomor Seri',          section:'general', cols:4),
                new FormField('asset_tag',        type:'text',     label:'Tag Asset / Barcode', section:'general', cols:4),
                new FormField('qr_code',          type:'text',     label:'QR Code',             section:'general', cols:4),
                new FormField('status',           type:'select',   label:'Status',              required:true, section:'general', cols:4,
                    options:[['value'=>'active','label'=>'Active'],['value'=>'maintenance','label'=>'In Maintenance'],['value'=>'idle','label'=>'Idle']]),
                // Financial
                new FormField('purchase_date',    type:'date',     label:'Tanggal Beli',        section:'financial', cols:4),
                new FormField('purchase_value',   type:'currency', label:'Nilai Pembelian',     required:true, section:'financial', cols:4),
                new FormField('current_value',    type:'currency', label:'Nilai Saat Ini',      section:'financial', cols:4),
                new FormField('residual_value',   type:'currency', label:'Nilai Residu',        section:'financial', cols:4),
                new FormField('vendor',           type:'text',     label:'Vendor',              section:'financial', cols:4),
                new FormField('invoice_number',   type:'text',     label:'No. Invoice',         section:'financial', cols:4),
                // Maintenance
                new FormField('maintenance_type', type:'select',   label:'Tipe Maintenance',    section:'maintenance', cols:4,
                    options:[['value'=>'preventive','label'=>'Preventive'],['value'=>'corrective','label'=>'Corrective'],['value'=>'predictive','label'=>'Predictive']]),
                new FormField('maintenance_interval',type:'number',label:'Interval (Hari)',     section:'maintenance', cols:4),
                new FormField('next_maintenance', type:'date',     label:'Next Maintenance',    section:'maintenance', cols:4),
                new FormField('maintenance_vendor',type:'text',    label:'Vendor Maintenance',  section:'maintenance', cols:4),
                // Assignment
                new FormField('department_id',    type:'select',   label:'Department',          section:'assignment', cols:4),
                new FormField('branch_id',        type:'select',   label:'Cabang',              required:true, section:'assignment', cols:4),
                new FormField('custodian_id',     type:'select',   label:'Penanggung Jawab',    section:'assignment', cols:4),
                // Insurance
                new FormField('insurance_company',type:'text',     label:'Perusahaan Asuransi', section:'insurance', cols:4),
                new FormField('policy_number',    type:'text',     label:'No. Polis',           section:'insurance', cols:4),
                new FormField('coverage_amount',  type:'currency', label:'Nilai Pertanggungan', section:'insurance', cols:4),
                new FormField('insurance_premium',type:'currency', label:'Premi',               section:'insurance', cols:4),
                new FormField('insurance_start',  type:'date',     label:'Mulai',               section:'insurance', cols:3),
                new FormField('insurance_end',    type:'date',     label:'Berakhir',            section:'insurance', cols:3),
                // Warranty
                new FormField('warranty_start',   type:'date',     label:'Mulai Garansi',       section:'warranty', cols:4),
                new FormField('warranty_end',     type:'date',     label:'Akhir Garansi',       section:'warranty', cols:4),
                new FormField('warranty_type',    type:'select',   label:'Tipe Garansi',        section:'warranty', cols:4,
                    options:[['value'=>'standard','label'=>'Standard'],['value'=>'extended','label'=>'Extended'],['value'=>'vendor','label'=>'Vendor']]),
                // Depreciation
                new FormField('depreciation_method',type:'select', label:'Metode Penyusutan',   section:'depreciation', cols:4,
                    options:[['value'=>'straight_line','label'=>'Garis Lurus'],['value'=>'declining_balance','label'=>'Saldo Menurun'],['value'=>'double_declining','label'=>'Double Declining'],['value'=>'units','label'=>'Unit Produksi']]),
                new FormField('useful_life_years',type:'number',   label:'Masa Manfaat (Tahun)',section:'depreciation', cols:4),
                new FormField('depreciation_rate',type:'number',   label:'Tarif Penyusutan (%)',section:'depreciation', cols:4),
                new FormField('depreciation_start',type:'date',    label:'Mulai Penyusutan',    section:'depreciation', cols:4),
                // Location
                new FormField('location',          type:'text',     label:'Lokasi Fisik',        section:'location', cols:6),
                new FormField('gps_coordinates',   type:'text',     label:'GPS (Lat, Lng)',      section:'location', cols:6),
                new FormField('room',              type:'text',     label:'Ruangan',             section:'location', cols:6),
                // Photos
                new FormField('photos',            type:'file',     label:'Foto Asset',          section:'photos', cols:6, multiple:true, accept:'image/*'),
                // Documents
                new FormField('documents',         type:'file',     label:'Dokumen',             section:'documents', cols:6, multiple:true),
                // Notes
                new FormField('internal_notes',    type:'textarea', label:'Catatan Internal',    section:'notes', cols:12),
            ])
            ->addAction(new FormAction('save_draft', 'Simpan Draft', variant:'outline'))
            ->addAction(new FormAction('save', 'Simpan', variant:'primary', shortcut:'Ctrl+S'))
            ->addAction(new FormAction('save_and_new', 'Simpan & Baru', variant:'secondary'));
    }

    // ═══════════════════════════════════════════════════════════
    // MAINTENANCE SCHEDULE — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function maintenanceTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'asset.maintenance.index',
            title: 'Maintenance Schedule',
            modelClass: \App\Models\Tenant\Maintenance::class,
            defaultSort: ['scheduled_date' => 'asc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['assets'],
        ))
            ->addColumns([
                new ColumnDefinition('asset_code',       'Kode Asset',   type:'text',    sortable:true, bold:true, width:'100px', order:1),
                new ColumnDefinition('asset_name',       'Nama Asset',   type:'text',    sortable:true, searchable:true, order:2),
                new ColumnDefinition('maintenance_type', 'Tipe',         type:'badge',   sortable:true, filterable:true, width:'100px', order:3),
                new ColumnDefinition('scheduled_date',   'Jadwal',       type:'date',    sortable:true, bold:true, width:'100px', order:4),
                new ColumnDefinition('priority',         'Prioritas',    type:'badge',   sortable:true, width:'80px', order:5),
                new ColumnDefinition('technician_name',  'Teknisi',      type:'text',    width:'110px', order:6),
                new ColumnDefinition('checklist_progress','Checklist',   type:'progress', width:'100px', order:7),
                new ColumnDefinition('estimated_cost',   'Estimasi Biaya',type:'currency',align:'right', width:'120px', order:8),
                new ColumnDefinition('status',           'Status',       type:'badge',   sortable:true, filterable:true, width:'90px', order:9),
                new ColumnDefinition('completed_date',   'Selesai',      type:'date',    sortable:true, width:'100px', order:10),
                new ColumnDefinition('actions',          '',             type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'scheduled','label'=>'Scheduled'],
                ['value'=>'in_progress','label'=>'In Progress'],
                ['value'=>'completed','label'=>'Completed'],
                ['value'=>'overdue','label'=>'Overdue'],
                ['value'=>'cancelled','label'=>'Cancelled'],
            ], order:1))
            ->addFilter(new FilterDefinition('maintenance_type', 'Tipe', type:'select', quick:true, options:[
                ['value'=>'preventive','label'=>'Preventive'],
                ['value'=>'corrective','label'=>'Corrective'],
                ['value'=>'emergency','label'=>'Emergency'],
                ['value'=>'inspection','label'=>'Inspection'],
                ['value'=>'calibration','label'=>'Calibration'],
                ['value'=>'cleaning','label'=>'Cleaning'],
                ['value'=>'repair','label'=>'Repair'],
                ['value'=>'replacement','label'=>'Replacement'],
            ], order:2))
            ->addFilter(new FilterDefinition('priority', 'Prioritas', type:'select', options:[['value'=>'critical','label'=>'Critical'],['value'=>'high','label'=>'High'],['value'=>'medium','label'=>'Medium'],['value'=>'low','label'=>'Low']], order:3))
            ->addFilter(new FilterDefinition('scheduled_date', 'Jadwal', type:'date_range', order:4))
            ->addBulkAction(new BulkAction('start', 'Start', variant:'primary'))
            ->addBulkAction(new BulkAction('complete', 'Mark Complete', variant:'success'))
            ->addBulkAction(new BulkAction('reschedule', 'Reschedule', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // ASSET MOVEMENT — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function movementTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'asset.movement.index',
            title: 'Asset Movements',
            modelClass: \App\Models\Tenant\AssetMovement::class,
            defaultSort: ['movement_date' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['assets'],
        ))
            ->addColumns([
                new ColumnDefinition('movement_date',    'Tanggal',       type:'date',    sortable:true, width:'100px', order:1),
                new ColumnDefinition('asset_code',       'Kode Asset',    type:'text',    sortable:true, bold:true, width:'100px', order:2),
                new ColumnDefinition('asset_name',       'Nama Asset',    type:'text',    sortable:true, searchable:true, order:3),
                new ColumnDefinition('movement_type',    'Tipe',          type:'badge',   sortable:true, filterable:true, width:'90px', order:4),
                new ColumnDefinition('from_location',    'Dari',          type:'text',    width:'120px', order:5),
                new ColumnDefinition('to_location',      'Ke',            type:'text',    width:'120px', order:6),
                new ColumnDefinition('from_custodian',   'Dari (PJ)',     type:'text',    width:'110px', order:7),
                new ColumnDefinition('to_custodian',     'Ke (PJ)',       type:'text',    width:'110px', order:8),
                new ColumnDefinition('reason',           'Alasan',        type:'text',    order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('movement_type', 'Tipe', type:'select', quick:true, options:[
                ['value'=>'purchase','label'=>'Purchase'],
                ['value'=>'transfer','label'=>'Transfer'],
                ['value'=>'assignment','label'=>'Assignment'],
                ['value'=>'return','label'=>'Return'],
                ['value'=>'maintenance','label'=>'To Maintenance'],
                ['value'=>'repair','label'=>'To Repair'],
                ['value'=>'disposal','label'=>'Disposal'],
                ['value'=>'sale','label'=>'Sale'],
                ['value'=>'write_off','label'=>'Write Off'],
            ], order:1))
            ->addFilter(new FilterDefinition('movement_date', 'Tanggal', type:'date_range', order:2))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // WARRANTY — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function warrantyTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'asset.warranty.index',
            title: 'Warranty Management',
            modelClass: \App\Models\Tenant\AssetWarranty::class,
            defaultSort: ['warranty_end' => 'asc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['assets'],
        ))
            ->addColumns([
                new ColumnDefinition('asset_code',       'Kode Asset',    type:'text',  sortable:true, bold:true, width:'100px', order:1),
                new ColumnDefinition('asset_name',       'Nama Asset',    type:'text',  sortable:true, searchable:true, order:2),
                new ColumnDefinition('warranty_type',    'Tipe',          type:'badge', sortable:true, width:'90px', order:3),
                new ColumnDefinition('warranty_start',   'Mulai',         type:'date',  sortable:true, width:'100px', order:4),
                new ColumnDefinition('warranty_end',     'Berakhir',      type:'date',  sortable:true, width:'100px', order:5),
                new ColumnDefinition('days_remaining',   'Sisa Hari',     type:'number',sortable:true, width:'80px', align:'center', order:6),
                new ColumnDefinition('vendor',           'Vendor',        type:'text',  width:'120px', order:7),
                new ColumnDefinition('claim_count',      'Claim',         type:'number', width:'60px', align:'center', order:8),
                new ColumnDefinition('status',           'Status',        type:'badge', sortable:true, width:'90px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'active','label'=>'Active'],
                ['value'=>'expiring','label'=>'Expiring Soon'],
                ['value'=>'expired','label'=>'Expired'],
                ['value'=>'claimed','label'=>'Claimed'],
            ], order:1))
            ->addFilter(new FilterDefinition('warranty_type', 'Tipe', type:'select', options:[['value'=>'standard','label'=>'Standard'],['value'=>'extended','label'=>'Extended'],['value'=>'vendor','label'=>'Vendor']], order:2))
            ->addFilter(new FilterDefinition('warranty_end', 'Expiry', type:'date_range', order:3))
            ->addBulkAction(new BulkAction('file_claim', 'File Claim', variant:'default'))
            ->addBulkAction(new BulkAction('extend', 'Extend Warranty', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // INSURANCE — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function insuranceTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'asset.insurance.index',
            title: 'Insurance Management',
            modelClass: \App\Models\Tenant\AssetInsurance::class,
            defaultSort: ['insurance_end' => 'asc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['assets'],
        ))
            ->addColumns([
                new ColumnDefinition('asset_code',       'Kode Asset',      type:'text',    sortable:true, bold:true, width:'100px', order:1),
                new ColumnDefinition('asset_name',       'Nama Asset',      type:'text',    sortable:true, searchable:true, order:2),
                new ColumnDefinition('insurance_company','Asuransi',        type:'text',    sortable:true, width:'130px', order:3),
                new ColumnDefinition('policy_number',    'No. Polis',       type:'text',    width:'120px', order:4),
                new ColumnDefinition('coverage_amount',  'Pertanggungan',   type:'currency', sortable:true, align:'right', width:'130px', order:5),
                new ColumnDefinition('premium',          'Premi',           type:'currency', sortable:true, align:'right', width:'110px', order:6),
                new ColumnDefinition('insurance_start',  'Mulai',           type:'date',    sortable:true, width:'100px', order:7),
                new ColumnDefinition('insurance_end',    'Berakhir',        type:'date',    sortable:true, width:'100px', order:8),
                new ColumnDefinition('days_remaining',   'Sisa Hari',       type:'number',  width:'80px', align:'center', order:9),
                new ColumnDefinition('status',           'Status',          type:'badge',   sortable:true, width:'90px', order:10),
                new ColumnDefinition('actions',          '',                type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'active','label'=>'Active'],
                ['value'=>'expiring','label'=>'Expiring Soon'],
                ['value'=>'expired','label'=>'Expired'],
                ['value'=>'claimed','label'=>'Claimed'],
            ], order:1))
            ->addFilter(new FilterDefinition('insurance_end', 'Expiry', type:'date_range', order:2))
            ->addBulkAction(new BulkAction('renew', 'Renew', variant:'primary'))
            ->addBulkAction(new BulkAction('file_claim', 'File Claim', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // VEHICLE — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function vehicleTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'asset.vehicle.index',
            title: 'Vehicle Management',
            modelClass: \App\Models\Tenant\Vehicle::class,
            defaultSort: ['vehicle_code' => 'asc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['assets'],
        ))
            ->addColumns([
                new ColumnDefinition('vehicle_code',     'Kode',          type:'text',    sortable:true, bold:true, width:'90px', order:1),
                new ColumnDefinition('vehicle_name',     'Nama Kendaraan',type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('license_plate',    'No. Polisi',    type:'text',    sortable:true, width:'110px', order:3),
                new ColumnDefinition('vehicle_type',     'Tipe',          type:'badge',   sortable:true, width:'80px', order:4),
                new ColumnDefinition('driver_name',      'Driver',        type:'text',    width:'110px', order:5),
                new ColumnDefinition('odometer',         'Odometer (KM)', type:'number',  sortable:true, width:'100px', align:'right', order:6),
                new ColumnDefinition('fuel_consumption', 'BBM (km/L)',    type:'number',  width:'80px', align:'center', order:7),
                new ColumnDefinition('next_service_km',  'Next Service',  type:'number',  width:'100px', align:'right', order:8),
                new ColumnDefinition('vehicle_tax_due',  'Pajak Due',     type:'date',    sortable:true, width:'100px', order:9),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, width:'90px', order:10),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'active','label'=>'Active'],
                ['value'=>'maintenance','label'=>'In Maintenance'],
                ['value'=>'idle','label'=>'Idle'],
            ], order:1))
            ->addFilter(new FilterDefinition('vehicle_type', 'Tipe', type:'select', options:[['value'=>'car','label'=>'Car'],['value'=>'motorcycle','label'=>'Motorcycle'],['value'=>'truck','label'=>'Truck'],['value'=>'van','label'=>'Van']], order:2))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // TOOL — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function toolTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'asset.tool.index',
            title: 'Tool & Equipment Management',
            modelClass: \App\Models\Tenant\Tool::class,
            defaultSort: ['tool_code' => 'asc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['assets'],
        ))
            ->addColumns([
                new ColumnDefinition('tool_code',        'Kode Tool',     type:'text',    sortable:true, bold:true, width:'90px', order:1),
                new ColumnDefinition('tool_name',        'Nama Tool',     type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('category',         'Kategori',      type:'badge',   sortable:true, width:'90px', order:3),
                new ColumnDefinition('current_user',     'Pengguna',      type:'text',    width:'110px', order:4),
                new ColumnDefinition('last_calibration', 'Kalibrasi Terakhir',type:'date',sortable:true, width:'120px', order:5),
                new ColumnDefinition('next_calibration', 'Kalibrasi Next',type:'date',    sortable:true, width:'120px', order:6),
                new ColumnDefinition('condition',        'Kondisi',       type:'badge',   sortable:true, width:'80px', order:7),
                new ColumnDefinition('usage_count',      'Usage',         type:'number',  sortable:true, width:'70px', align:'center', order:8),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, width:'90px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'available','label'=>'Available'],
                ['value'=>'borrowed','label'=>'Borrowed'],
                ['value'=>'maintenance','label'=>'In Maintenance'],
                ['value'=>'lost','label'=>'Lost'],
                ['value'=>'damaged','label'=>'Damaged'],
            ], order:1))
            ->addFilter(new FilterDefinition('condition', 'Kondisi', type:'select', options:[['value'=>'good','label'=>'Good'],['value'=>'fair','label'=>'Fair'],['value'=>'poor','label'=>'Poor']], order:2))
            ->addBulkAction(new BulkAction('calibrate', 'Calibrate', variant:'default'))
            ->addBulkAction(new BulkAction('return', 'Return', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES — 12 Rules
    // ═══════════════════════════════════════════════════════════

    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            // 1. Asset Created → Log
            (new AutomationDefinition('asset.created', 'Asset Created',
                trigger: TriggerType::RECORD_CREATED, module: 'asset'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                    'message' => '🆕 Asset {{subject.asset_code}} - {{subject.asset_name}} registered.',
                ])),

            // 2. Maintenance Due → Task
            (new AutomationDefinition('asset.maintenance_due', 'Maintenance Due',
                trigger: TriggerType::DATE_REACHED, module: 'asset'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Maintenance: {{subject.asset_name}} ({{subject.asset_code}})',
                    'assignee_role' => 'maintenance',
                ], delaySeconds: 1800))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '🔧 Maintenance Due',
                    'body' => '{{subject.asset_name}} due for maintenance.',
                    'roles' => ['maintenance', 'manager'],
                ])),

            // 3. Maintenance Overdue → Alert
            (new AutomationDefinition('asset.maintenance_overdue', 'Maintenance Overdue',
                trigger: TriggerType::DATE_REACHED, module: 'asset'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '⚠️ Maintenance Overdue',
                    'body' => '{{subject.asset_name}} is overdue for maintenance!',
                    'roles' => ['maintenance', 'manager', 'owner'],
                ])),

            // 4. Warranty Expiring → Alert (30 days)
            (new AutomationDefinition('asset.warranty_expiring', 'Warranty Expiring',
                trigger: TriggerType::DATE_REACHED, module: 'asset'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Review warranty: {{subject.asset_name}}',
                    'assignee_role' => 'maintenance',
                ]))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '🛡️ Warranty Expiring',
                    'body' => 'Warranty for {{subject.asset_name}} expires soon.',
                    'roles' => ['maintenance', 'manager'],
                ])),

            // 5. Insurance Expiring → Alert (30 days)
            (new AutomationDefinition('asset.insurance_expiring', 'Insurance Expiring',
                trigger: TriggerType::DATE_REACHED, module: 'asset'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Renew insurance: {{subject.asset_name}}',
                    'assignee_role' => 'manager',
                ]))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '🔒 Insurance Expiring',
                    'body' => 'Insurance for {{subject.asset_name}} expires soon.',
                    'roles' => ['manager', 'finance'],
                ])),

            // 6. Calibration Due → Task
            (new AutomationDefinition('asset.calibration_due', 'Calibration Due',
                trigger: TriggerType::DATE_REACHED, module: 'asset'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Calibrate: {{subject.tool_name}} ({{subject.tool_code}})',
                    'assignee_role' => 'technician',
                ])),

            // 7. Inspection Due → Task
            (new AutomationDefinition('asset.inspection_due', 'Inspection Due',
                trigger: TriggerType::DATE_REACHED, module: 'asset'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Inspect: {{subject.asset_name}}',
                    'assignee_role' => 'maintenance',
                ])),

            // 8. Asset Assigned → Notify Employee
            (new AutomationDefinition('asset.assigned', 'Asset Assigned',
                trigger: TriggerType::RECORD_UPDATED, module: 'asset'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '📋 Asset Assigned',
                    'body' => '{{subject.asset_name}} has been assigned to you.',
                ])),

            // 9. Asset Returned → Update
            (new AutomationDefinition('asset.returned', 'Asset Returned',
                trigger: TriggerType::RECORD_UPDATED, module: 'asset'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                    'message' => '🔄 {{subject.asset_name}} returned by {{subject.previous_custodian}}.',
                ])),

            // 10. Depreciation Posted → Auto Journal
            (new AutomationDefinition('asset.depreciation_posted', 'Depreciation Posted',
                trigger: TriggerType::RECORD_UPDATED, module: 'asset'))
                ->addStep(new AutomationStep(ActionType::CREATE_JOURNAL, [
                    'template' => 'depreciation_expense',
                ])),

            // 11. Asset Disposed → Log
            (new AutomationDefinition('asset.disposed', 'Asset Disposed',
                trigger: TriggerType::RECORD_UPDATED, module: 'asset'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                    'message' => '🗑️ {{subject.asset_name}} disposed.',
                ])),

            // 12. Vehicle Tax Due → Alert
            (new AutomationDefinition('asset.vehicle_tax_due', 'Vehicle Tax Due',
                trigger: TriggerType::DATE_REACHED, module: 'asset'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Pay vehicle tax: {{subject.license_plate}}',
                    'assignee_role' => 'manager',
                ]))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '🚗 Vehicle Tax Due',
                    'body' => 'Tax for {{subject.license_plate}} is due.',
                    'roles' => ['manager', 'finance'],
                ])),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // REPORTING DEFINITIONS — 12 Reports
    // ═══════════════════════════════════════════════════════════

    /** @return ReportDefinition[] */
    public static function reports(): array
    {
        return [
            // 1. Asset Register
            (new ReportDefinition('asset.register', 'Asset Register',
                type:'summary', chartType:'table', features:['assets']))
                ->addMetric(new MetricDefinition('purchase_value', 'Purchase Value', 'sum', 'purchase_value', format:'currency'))
                ->addMetric(new MetricDefinition('current_value', 'Current Value', 'sum', 'current_value', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('count', 'Count', 'count', 'id', format:'number'))
                ->addDimension(new DimensionDefinition('category', 'Category', 'category', type:'string'))
                ->addFilter(new ReportFilter('branch', 'Branch', 'select'))
                ->addFilter(new ReportFilter('status', 'Status', 'select')),

            // 2. Asset Value
            (new ReportDefinition('asset.value', 'Asset Value Analysis',
                type:'summary', chartType:'bar', features:['assets']))
                ->addMetric(new MetricDefinition('total_value', 'Total Value', 'sum', 'current_value', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('depreciated', 'Depreciated', 'sum', 'accumulated_depreciation', format:'currency', color:'danger'))
                ->addMetric(new MetricDefinition('book_value', 'Book Value', 'expression', 'total_value - depreciated', format:'currency', color:'success'))
                ->addDimension(new DimensionDefinition('category', 'Category', 'category', type:'string'))
                ->addFilter(new ReportFilter('branch', 'Branch', 'select')),

            // 3. Depreciation Schedule
            (new ReportDefinition('asset.depreciation', 'Depreciation Schedule',
                type:'summary', chartType:'table', features:['assets'], permissions:['manage_finance']))
                ->addMetric(new MetricDefinition('monthly_dep', 'Monthly Depreciation', 'sum', 'monthly_depreciation', format:'currency', color:'warning'))
                ->addMetric(new MetricDefinition('ytd_dep', 'YTD Depreciation', 'sum', 'ytd_depreciation', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('remaining_value', 'Remaining Value', 'sum', 'remaining_value', format:'currency'))
                ->addDimension(new DimensionDefinition('asset_name', 'Asset', 'asset_name', type:'string'))
                ->addFilter(new ReportFilter('year', 'Year', 'select')),

            // 4. Maintenance Cost
            (new ReportDefinition('asset.maintenance_cost', 'Maintenance Cost Analysis',
                type:'summary', chartType:'bar', features:['assets']))
                ->addMetric(new MetricDefinition('total_cost', 'Total Cost', 'sum', 'cost', format:'currency', color:'danger'))
                ->addMetric(new MetricDefinition('avg_cost', 'Avg Cost', 'avg', 'cost', format:'currency'))
                ->addMetric(new MetricDefinition('count', 'Count', 'count', 'id', format:'number'))
                ->addDimension(new DimensionDefinition('asset_name', 'Asset', 'asset_name', type:'string'))
                ->addDimension(new DimensionDefinition('maintenance_type', 'Type', 'maintenance_type', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            // 5. Maintenance Schedule
            (new ReportDefinition('asset.maintenance_schedule', 'Maintenance Schedule',
                type:'summary', chartType:'table', features:['assets']))
                ->addMetric(new MetricDefinition('scheduled', 'Scheduled', 'count', 'scheduled', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('completed', 'Completed', 'count', 'completed', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('overdue', 'Overdue', 'count', 'overdue', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'scheduled_date', type:'date'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            // 6. Asset Utilization
            (new ReportDefinition('asset.utilization', 'Asset Utilization',
                type:'summary', chartType:'kpi', features:['assets']))
                ->addMetric(new MetricDefinition('active_assets', 'Active Assets', 'count', 'active', format:'number', icon:'✅'))
                ->addMetric(new MetricDefinition('idle_assets', 'Idle Assets', 'count', 'idle', format:'number', color:'warning', icon:'⏸️'))
                ->addMetric(new MetricDefinition('maintenance_count', 'In Maintenance', 'count', 'maintenance', format:'number', color:'info', icon:'🔧'))
                ->addMetric(new MetricDefinition('utilization_rate', 'Utilization Rate %', 'expression', 'active / total * 100', format:'number', color:'primary', icon:'📊')),

            // 7. Warranty Report
            (new ReportDefinition('asset.warranty_report', 'Warranty Report',
                type:'summary', chartType:'table', features:['assets']))
                ->addMetric(new MetricDefinition('active_warranties', 'Active', 'count', 'active', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('expiring_soon', 'Expiring (30d)', 'count', 'expiring_soon', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('expired', 'Expired', 'count', 'expired', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('category', 'Category', 'category', type:'string'))
                ->addFilter(new ReportFilter('warranty_end', 'Expiry Date', 'date_range')),

            // 8. Insurance Report
            (new ReportDefinition('asset.insurance_report', 'Insurance Report',
                type:'summary', chartType:'table', features:['assets']))
                ->addMetric(new MetricDefinition('total_coverage', 'Total Coverage', 'sum', 'coverage_amount', format:'currency'))
                ->addMetric(new MetricDefinition('total_premium', 'Total Premium', 'sum', 'premium', format:'currency', color:'warning'))
                ->addMetric(new MetricDefinition('claim_count', 'Claims', 'count', 'claims', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('insurance_company', 'Company', 'insurance_company', type:'string')),

            // 9. Vehicle Report
            (new ReportDefinition('asset.vehicle_report', 'Vehicle Report',
                type:'summary', chartType:'table', features:['assets']))
                ->addMetric(new MetricDefinition('total_km', 'Total KM', 'sum', 'odometer', format:'number'))
                ->addMetric(new MetricDefinition('avg_fuel', 'Avg Fuel (km/L)', 'avg', 'fuel_consumption', format:'number'))
                ->addMetric(new MetricDefinition('service_due', 'Service Due', 'count', 'service_due', format:'number', color:'warning'))
                ->addDimension(new DimensionDefinition('vehicle_name', 'Vehicle', 'vehicle_name', type:'string'))
                ->addFilter(new ReportFilter('status', 'Status', 'select')),

            // 10. Tool Report
            (new ReportDefinition('asset.tool_report', 'Tool Report',
                type:'summary', chartType:'table', features:['assets']))
                ->addMetric(new MetricDefinition('total_tools', 'Total', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('borrowed', 'Borrowed', 'count', 'borrowed', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('calibration_due', 'Calibration Due', 'count', 'calibration_due', format:'number', color:'warning'))
                ->addDimension(new DimensionDefinition('category', 'Category', 'category', type:'string')),

            // 11. Asset Movement Report
            (new ReportDefinition('asset.movement_report', 'Asset Movement Report',
                type:'summary', chartType:'table', features:['assets']))
                ->addMetric(new MetricDefinition('movement_count', 'Movements', 'count', 'id', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('movement_type', 'Type', 'movement_type', type:'string'))
                ->addDimension(new DimensionDefinition('asset_name', 'Asset', 'asset_name', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            // 12. Disposed Assets
            (new ReportDefinition('asset.disposed', 'Disposed Assets',
                type:'summary', chartType:'table', features:['assets']))
                ->addMetric(new MetricDefinition('disposal_value', 'Disposal Value', 'sum', 'disposal_value', format:'currency'))
                ->addMetric(new MetricDefinition('book_value', 'Book Value', 'sum', 'book_value', format:'currency'))
                ->addMetric(new MetricDefinition('gain_loss', 'Gain/Loss', 'expression', 'disposal_value - book_value', format:'currency', color:'primary'))
                ->addDimension(new DimensionDefinition('asset_name', 'Asset', 'asset_name', type:'string'))
                ->addFilter(new ReportFilter('disposal_date', 'Date', 'date_range')),
        ];
    }
}
