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
 * PurchasingDefinitions — ALL Enterprise definitions for Purchasing Module.
 * 
 * Covers: Purchase Request → RFQ → Quotation → PO → Goods Receipt → Invoice → Payment.
 * Integrated with: Inventory, Supplier, Finance, Automation, Reporting.
 */
class PurchasingDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // PURCHASE WORKSPACE
    // ═══════════════════════════════════════════════════════════
    
    public static function workspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'purchasing',
            title: 'Purchasing Workspace',
            icon: '🛒',
            tabs: [
                ['id' => 'overview',   'label' => 'Overview',       'icon' => '📋'],
                ['id' => 'workflow',   'label' => 'Workflow',       'icon' => '🔄'],
                ['id' => 'items',      'label' => 'Items',          'icon' => '📦'],
                ['id' => 'supplier',   'label' => 'Supplier',       'icon' => '🏭'],
                ['id' => 'quotation',  'label' => 'Quotation',      'icon' => '💬'],
                ['id' => 'approval',   'label' => 'Approval',       'icon' => '✅'],
                ['id' => 'goods_receipt','label' => 'Goods Receipt','icon' => '📥'],
                ['id' => 'invoice',    'label' => 'Invoice',        'icon' => '💰'],
                ['id' => 'payment',    'label' => 'Payment',        'icon' => '💳'],
                ['id' => 'timeline',   'label' => 'Timeline',       'icon' => '🕐'],
                ['id' => 'documents',  'label' => 'Documents',      'icon' => '📄'],
                ['id' => 'activity',   'label' => 'Activity',       'icon' => '📊'],
            ],
            actions: [
                ['id' => 'approve',       'label' => 'Approve',        'roles' => ['owner','admin','manager']],
                ['id' => 'reject',        'label' => 'Reject',         'roles' => ['owner','admin','manager']],
                ['id' => 'send_po',       'label' => 'Send PO',        'roles' => ['owner','admin','manager']],
                ['id' => 'receive',       'label' => 'Goods Receipt',  'roles' => ['owner','admin','manager','head_store']],
                ['id' => 'create_invoice','label' => 'Create Invoice', 'roles' => ['owner','admin','manager']],
                ['id' => 'pay',           'label' => 'Payment',        'roles' => ['owner','admin','manager']],
                ['id' => 'print',         'label' => 'Print PO',       'roles' => ['owner','admin','manager']],
                ['id' => 'cancel',        'label' => 'Cancel',         'roles' => ['owner','admin']],
            ],
            features: ['purchases'],
            permissions: ['manage_purchases'],
            config: ['autoRefreshSeconds' => 60, 'showApprovalChain' => true],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // SUPPLIER WORKSPACE
    // ═══════════════════════════════════════════════════════════
    
    public static function supplierWorkspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'supplier',
            title: 'Supplier Workspace',
            icon: '🏭',
            tabs: [
                ['id' => 'overview',   'label' => 'Overview',         'icon' => '📋'],
                ['id' => 'contacts',   'label' => 'Contacts',         'icon' => '👤'],
                ['id' => 'purchases',  'label' => 'Purchase History', 'icon' => '🛒'],
                ['id' => 'outstanding','label' => 'Outstanding PO',   'icon' => '📋'],
                ['id' => 'invoices',   'label' => 'Invoices',         'icon' => '💰'],
                ['id' => 'payments',   'label' => 'Payments',         'icon' => '💳'],
                ['id' => 'performance','label' => 'Performance',      'icon' => '⭐', 'roles' => ['owner','admin','manager']],
                ['id' => 'documents',  'label' => 'Documents',        'icon' => '📄'],
                ['id' => 'timeline',   'label' => 'Timeline',         'icon' => '🕐'],
            ],
            actions: [
                ['id' => 'new_po',   'label' => 'New PO',    'roles' => ['owner','admin','manager']],
                ['id' => 'contact',  'label' => 'Contact',   'roles' => ['owner','admin','manager']],
                ['id' => 'evaluate', 'label' => 'Evaluate',  'roles' => ['owner','admin']],
            ],
            features: ['purchases'],
            permissions: ['manage_purchases'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // DATA TABLE — Purchase Order List
    // ═══════════════════════════════════════════════════════════
    
    public static function dataTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'purchasing.index',
            title: 'Purchase Orders',
            modelClass: \App\Models\Tenant\Sale::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['purchases'],
        ))
            ->addColumns([
                new ColumnDefinition('po_number', 'PO Number', type:'text', sortable:true, bold:true, width:'120px', order:1),
                new ColumnDefinition('supplier_name', 'Supplier', type:'text', sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('status', 'Status', type:'badge', sortable:true, filterable:true, align:'center', width:'130px', order:3),
                new ColumnDefinition('total', 'Total', type:'currency', sortable:true, align:'right', width:'130px', order:4),
                new ColumnDefinition('items_count', 'Items', type:'number', sortable:true, align:'center', width:'60px', order:5),
                new ColumnDefinition('received', 'Received', type:'badge', align:'center', width:'100px', order:6),
                new ColumnDefinition('expected_date', 'Expected', type:'date', sortable:true, width:'110px', order:7),
                new ColumnDefinition('warehouse', 'Warehouse', type:'text', sortable:true, filterable:true, order:8),
                new ColumnDefinition('created_by', 'Created By', type:'text', sortable:true, order:9),
                new ColumnDefinition('created_at', 'Created', type:'datetime', sortable:true, width:'140px', order:10),
                new ColumnDefinition('actions', '', type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true,
                options: [
                    ['value'=>'draft','label'=>'Draft'],['value'=>'pending_approval','label'=>'Waiting Approval'],
                    ['value'=>'approved','label'=>'Approved'],['value'=>'sent','label'=>'Sent'],
                    ['value'=>'partial_received','label'=>'Partial Received'],['value'=>'completed','label'=>'Completed'],
                    ['value'=>'cancelled','label'=>'Cancelled'],
                ], order:1))
            ->addFilter(new FilterDefinition('supplier_id', 'Supplier', type:'select', quick:true, order:2))
            ->addFilter(new FilterDefinition('warehouse_id', 'Warehouse', type:'select', order:3))
            ->addFilter(new FilterDefinition('created_at', 'Date', type:'date_range', quick:true, order:4))
            ->addBulkAction(new BulkAction('approve', 'Approve', variant:'success', permissions:['manage_purchases']))
            ->addBulkAction(new BulkAction('print', 'Print', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'))
            ->addBulkAction(new BulkAction('cancel', 'Cancel', variant:'danger', confirm:true, permissions:['manage_purchases']));
    }

    // ═══════════════════════════════════════════════════════════
    // FORM — Purchase Order Create
    // ═══════════════════════════════════════════════════════════
    
    public static function createForm(): FormDefinition
    {
        return (new FormDefinition(
            id: 'purchasing.create',
            title: 'Buat Purchase Order',
            method: 'POST',
            endpoint: '/purchasing',
            features: ['purchases'],
            permissions: ['manage_purchases'],
        ))
            ->addSection(new FormSection(id:'header', label:'Informasi PO', icon:'📋', cols:2))
            ->addSection(new FormSection(id:'items', label:'Items', icon:'📦', cols:1))
            ->addSection(new FormSection(id:'finance', label:'Keuangan', icon:'💰', cols:2, permissions:['manage_finance']))
            ->addSection(new FormSection(id:'shipping', label:'Pengiriman', icon:'🚚', cols:2))
            ->addFields([
                new FormField('supplier_id', type:'autocomplete', label:'Supplier', required:true,
                    asyncUrl:'/api/suppliers/search', optionLabel:'name', optionValue:'id', section:'header', cols:6),
                new FormField('warehouse_id', type:'select', label:'Gudang', required:true, section:'header', cols:6),
                new FormField('expected_date', type:'date', label:'Estimasi Tiba', section:'header', cols:4),
                new FormField('currency', type:'select', label:'Mata Uang', section:'header', cols:3,
                    options:[['value'=>'IDR','label'=>'IDR'],['value'=>'USD','label'=>'USD']]),
                new FormField('reference', type:'text', label:'Referensi', section:'header', cols:5),
                new FormField('items', type:'repeater', label:'Items', section:'items', cols:12,
                    meta:['fields'=>[
                        ['key'=>'product','type'=>'autocomplete','label'=>'Produk'],
                        ['key'=>'qty','type'=>'number','label'=>'Qty'],
                        ['key'=>'cost','type'=>'currency','label'=>'Harga'],
                        ['key'=>'discount','type'=>'number','label'=>'Disc %'],
                    ]]),
                new FormField('tax', type:'number', label:'Pajak (%)', default:11, section:'finance', cols:4),
                new FormField('discount_total', type:'currency', label:'Diskon Total', section:'finance', cols:4),
                new FormField('shipping_cost', type:'currency', label:'Biaya Kirim', section:'finance', cols:4),
                new FormField('payment_terms', type:'select', label:'Term Pembayaran', section:'finance', cols:6,
                    options:[['value'=>'cod','label'=>'COD'],['value'=>'n15','label'=>'Net 15'],['value'=>'n30','label'=>'Net 30'],['value'=>'n60','label'=>'Net 60']]),
                new FormField('shipping_method', type:'select', label:'Metode Kirim', section:'shipping', cols:6,
                    options:[['value'=>'pickup','label'=>'Pickup'],['value'=>'delivery','label'=>'Delivery'],['value'=>'courier','label'=>'Courier']]),
                new FormField('shipping_address', type:'textarea', label:'Alamat Kirim', section:'shipping', cols:12),
                new FormField('internal_notes', type:'textarea', label:'Catatan Internal', section:'header', cols:12),
                new FormField('attachments', type:'file', label:'Lampiran', section:'header', cols:12, multiple:true),
            ])
            ->addAction(new FormAction('save', 'Simpan', variant:'primary', shortcut:'Ctrl+S'))
            ->addAction(new FormAction('save_and_send', 'Simpan & Kirim', variant:'success'))
            ->addAction(new FormAction('save_draft', 'Draft', variant:'outline'));
    }

    // ═══════════════════════════════════════════════════════════
    // APPROVAL ENGINE CONFIG
    // ═══════════════════════════════════════════════════════════
    
    public static function approvalLevels(): array
    {
        return [
            ['level' => 1, 'role' => 'manager',    'label' => 'Manager Approval',   'can_skip' => false],
            ['level' => 2, 'role' => 'admin',      'label' => 'Admin Approval',     'can_skip' => false],
            ['level' => 3, 'role' => 'owner',      'label' => 'Owner Approval',     'can_skip' => true, 'min_amount' => 10000000],
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES
    // ═══════════════════════════════════════════════════════════
    
    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            // PO requires approval
            (new AutomationDefinition('purchasing.po_created', 'PO Dibuat — Butuh Approval',
                trigger: TriggerType::RECORD_CREATED, module: 'purchasing'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => 'Approval Required', 'body' => 'PO #{{subject.po_number}} butuh approval.',
                ])),

            // PO approved → send to supplier
            (new AutomationDefinition('purchasing.po_approved', 'PO Approved — Send to Supplier',
                trigger: TriggerType::STATUS_CHANGED, module: 'purchasing'))
                ->addStep(new AutomationStep(ActionType::SEND_EMAIL, [
                    'to' => '{{subject.supplier.email}}', 'subject' => 'Purchase Order #{{subject.po_number}}',
                ]))
                ->addStep(new AutomationStep(ActionType::ADD_TIMELINE, [
                    'message' => '✅ PO approved & sent to supplier.',
                ])),

            // Goods received → update inventory
            (new AutomationDefinition('purchasing.goods_received', 'Goods Received — Update Inventory',
                trigger: TriggerType::RECORD_UPDATED, module: 'purchasing'))
                ->addStep(new AutomationStep(ActionType::ADD_TIMELINE, [
                    'message' => '📦 Goods received: {{changes.received_qty}} items.',
                ]))
                ->addStep(new AutomationStep(ActionType::UPDATE_RECORD, [
                    'model' => 'Product', 'field' => 'stock_quantity', 'operation' => 'increment',
                ])),

            // Invoice due reminder
            (new AutomationDefinition('purchasing.invoice_due', 'Invoice Due — Reminder',
                trigger: TriggerType::DATE_REACHED, module: 'purchasing'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => 'Invoice Due', 'body' => 'Invoice #{{subject.invoice_number}} is due.',
                ])),

            // Purchase completed
            (new AutomationDefinition('purchasing.completed', 'Purchase Completed',
                trigger: TriggerType::STATUS_CHANGED, module: 'purchasing'))
                ->addStep(new AutomationStep(ActionType::ADD_TIMELINE, [
                    'message' => '🎉 Purchase #{{subject.po_number}} completed.',
                ]))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                    'message' => 'Purchase completed: {{subject.po_number}} — Rp {{subject.total}}.',
                ])),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // REPORTING DEFINITIONS
    // ═══════════════════════════════════════════════════════════
    
    /** @return ReportDefinition[] */
    public static function reports(): array
    {
        return [
            // Purchase summary
            (new ReportDefinition('purchasing.summary', 'Ringkasan Pembelian',
                type:'summary', chartType:'bar', features:['purchases'], permissions:['manage_purchases']))
                ->addMetric(new MetricDefinition('total', 'Total', 'sum', 'total', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('count', 'Jumlah PO', 'count', 'id', format:'number'))
                ->addDimension(new DimensionDefinition('date', 'Tanggal', 'created_at', type:'date'))
                ->addFilter(new ReportFilter('date_range', 'Periode', 'date_range')),

            // By supplier
            (new ReportDefinition('purchasing.by_supplier', 'Pembelian per Supplier',
                type:'summary', chartType:'pie', features:['purchases']))
                ->addMetric(new MetricDefinition('total', 'Total', 'sum', 'total', format:'currency'))
                ->addDimension(new DimensionDefinition('supplier', 'Supplier', 'supplier_name', type:'string')),

            // Outstanding PO
            (new ReportDefinition('purchasing.outstanding', 'Outstanding PO',
                type:'summary', chartType:'table', features:['purchases']))
                ->addMetric(new MetricDefinition('outstanding', 'Outstanding', 'sum', 'remaining', format:'currency', color:'warning'))
                ->addDimension(new DimensionDefinition('po', 'PO', 'po_number', type:'string')),

            // Lead time analysis
            (new ReportDefinition('purchasing.lead_time', 'Lead Time Analysis',
                type:'summary', chartType:'bar', features:['purchases'], permissions:['manage_purchases']))
                ->addMetric(new MetricDefinition('avg_lead_time', 'Avg Lead Time (days)', 'avg', 'lead_time_days', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('supplier', 'Supplier', 'supplier_name', type:'string')),

            // Supplier performance
            (new ReportDefinition('purchasing.supplier_performance', 'Kinerja Supplier',
                type:'summary', chartType:'kpi', features:['purchases'], permissions:['manage_purchases']))
                ->addMetric(new MetricDefinition('on_time', 'On Time %', 'avg', 'on_time_rate', format:'percent', color:'success', icon:'✅'))
                ->addMetric(new MetricDefinition('quality', 'Quality %', 'avg', 'quality_rate', format:'percent', color:'primary', icon:'⭐'))
                ->addMetric(new MetricDefinition('total_orders', 'Total Orders', 'count', 'id', format:'number', icon:'📦')),
        ];
    }
}
