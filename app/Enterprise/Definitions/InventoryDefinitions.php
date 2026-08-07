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
 * InventoryDefinitions — ALL Enterprise definitions for Inventory Module.
 * 
 * One file contains: Workspace, Form, DataTable, Automation, Reporting.
 * Other modules follow this pattern.
 */
class InventoryDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // WORKSPACE DEFINITION
    // ═══════════════════════════════════════════════════════════
    
    public static function workspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'inventory',
            title: 'Inventory Workspace',
            icon: '📦',
            tabs: [
                ['id' => 'overview',   'label' => 'Overview',      'icon' => '📋'],
                ['id' => 'movement',   'label' => 'Stock Movement', 'icon' => '🔄'],
                ['id' => 'purchase',   'label' => 'Purchase',      'icon' => '🛒'],
                ['id' => 'sales',      'label' => 'Sales History',  'icon' => '💰'],
                ['id' => 'service',    'label' => 'Service Usage',  'icon' => '🔧'],
                ['id' => 'transfer',   'label' => 'Transfer',       'icon' => '🚚'],
                ['id' => 'supplier',   'label' => 'Supplier',       'icon' => '🏭'],
                ['id' => 'pricing',    'label' => 'Price History',  'icon' => '💲'],
                ['id' => 'serial',     'label' => 'Serial/IMEI',    'icon' => '🔢', 'roles' => ['owner','admin','manager','head_store']],
                ['id' => 'documents',  'label' => 'Documents',      'icon' => '📄'],
            ],
            actions: [
                ['id' => 'add_stock',    'label' => 'Tambah Stok',    'roles' => ['owner','admin','manager','head_store']],
                ['id' => 'adjust',       'label' => 'Adjustment',     'roles' => ['owner','admin','manager']],
                ['id' => 'transfer',     'label' => 'Transfer',       'roles' => ['owner','admin','manager','head_store']],
                ['id' => 'opname',       'label' => 'Stock Opname',   'roles' => ['owner','admin','manager']],
                ['id' => 'print_label',  'label' => 'Print Label',    'roles' => ['owner','admin','manager','head_store']],
                ['id' => 'export',       'label' => 'Export',         'roles' => ['owner','admin','manager']],
            ],
            sidebarWidgets: [
                ['id' => 'stock_summary',  'component' => 'StockSummary',  'priority' => 10],
                ['id' => 'warehouse',      'component' => 'WarehouseInfo', 'priority' => 20],
                ['id' => 'supplier',        'component' => 'SupplierCard',  'priority' => 30],
                ['id' => 'alerts',          'component' => 'StockAlerts',   'priority' => 40],
            ],
            features: ['products'],
            config: ['autoRefreshSeconds' => 60, 'showAuditTrail' => true],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // DATA TABLE DEFINITION
    // ═══════════════════════════════════════════════════════════
    
    public static function dataTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'inventory.index',
            title: 'Daftar Inventory',
            modelClass: \App\Models\Tenant\Product::class,
            defaultSort: ['updated_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
        ))
            ->addColumns([
                new ColumnDefinition('sku', 'SKU', type: 'text', sortable: true, bold: true, width: '100px', order: 1),
                new ColumnDefinition('name', 'Nama Produk', type: 'text', sortable: true, searchable: true, bold: true, order: 2),
                new ColumnDefinition('category', 'Kategori', type: 'text', sortable: true, filterable: true, order: 3),
                new ColumnDefinition('stock_quantity', 'Stok', type: 'number', sortable: true, align: 'center', width: '80px', order: 4),
                new ColumnDefinition('min_stock', 'Min Stok', type: 'number', sortable: true, align: 'center', width: '80px', order: 5),
                new ColumnDefinition('cost_price', 'Harga Beli', type: 'currency', sortable: true, align: 'right', permissions: ['manage_finance'], order: 6),
                new ColumnDefinition('selling_price', 'Harga Jual', type: 'currency', sortable: true, align: 'right', order: 7),
                new ColumnDefinition('stock_value', 'Nilai Stok', type: 'currency', sortable: true, align: 'right', aggregate: true, aggregateType: 'sum', permissions: ['manage_finance'], order: 8),
                new ColumnDefinition('warehouse', 'Gudang', type: 'text', sortable: true, filterable: true, order: 9),
                new ColumnDefinition('updated_at', 'Diperbarui', type: 'datetime', sortable: true, width: '140px', order: 10),
                new ColumnDefinition('actions', '', type: 'actions', align: 'center', width: '80px', order: 99),
            ])
            ->addFilter(new FilterDefinition('category', 'Kategori', type: 'select', quick: true, order: 1))
            ->addFilter(new FilterDefinition('warehouse_id', 'Gudang', type: 'select', quick: true, order: 2))
            ->addFilter(new FilterDefinition('stock_status', 'Status Stok', type: 'select', quick: true,
                options: [['value'=>'low','label'=>'Menipis'],['value'=>'dead','label'=>'Dead Stock'],['value'=>'fast','label'=>'Fast Moving']], order: 3))
            ->addFilter(new FilterDefinition('updated_at', 'Tanggal', type: 'date_range', order: 4))
            ->addBulkAction(new BulkAction('adjust', 'Adjustment', variant: 'default'))
            ->addBulkAction(new BulkAction('transfer', 'Transfer', variant: 'default', permissions: ['manage_products']))
            ->addBulkAction(new BulkAction('print_label', 'Print Label', variant: 'default'))
            ->addBulkAction(new BulkAction('delete', 'Hapus', variant: 'danger', confirm: true, permissions: ['delete_models']))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant: 'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // FORM DEFINITION (Create Product)
    // ═══════════════════════════════════════════════════════════
    
    public static function createForm(): FormDefinition
    {
        return (new FormDefinition(
            id: 'inventory.create',
            title: 'Tambah Produk Baru',
            method: 'POST',
            endpoint: '/inventaris',
            features: ['products'],
        ))
            ->addSection(new FormSection(id: 'basic', label: 'Informasi Dasar', icon: '📦', cols: 2))
            ->addSection(new FormSection(id: 'pricing', label: 'Harga', icon: '💰', cols: 2, permissions: ['manage_finance']))
            ->addSection(new FormSection(id: 'warehouse', label: 'Gudang & Stok', icon: '🏭', cols: 2))
            ->addFields([
                new FormField('name', type:'text', label:'Nama Produk', required:true, section:'basic', cols:12),
                new FormField('sku', type:'barcode', label:'SKU / Barcode', section:'basic', cols:6),
                new FormField('category_id', type:'select', label:'Kategori', section:'basic', cols:6),
                new FormField('brand', type:'text', label:'Merek', section:'basic', cols:6),
                new FormField('model', type:'text', label:'Model', section:'basic', cols:6),
                new FormField('unit', type:'select', label:'Satuan (UOM)', section:'basic', cols:4,
                    options: [['value'=>'pcs','label'=>'Pcs'],['value'=>'kg','label'=>'Kg'],['value'=>'box','label'=>'Box']]),
                new FormField('description', type:'textarea', label:'Deskripsi', section:'basic', cols:12),
                new FormField('cost_price', type:'currency', label:'Harga Beli', section:'pricing', cols:6),
                new FormField('selling_price', type:'currency', label:'Harga Jual', section:'pricing', cols:6),
                new FormField('wholesale_price', type:'currency', label:'Harga Grosir', section:'pricing', cols:6, permissions:['manage_finance']),
                new FormField('online_price', type:'currency', label:'Harga Online', section:'pricing', cols:6),
                new FormField('stock_quantity', type:'number', label:'Stok Awal', section:'warehouse', cols:4),
                new FormField('min_stock', type:'number', label:'Stok Minimum', section:'warehouse', cols:4),
                new FormField('max_stock', type:'number', label:'Stok Maksimum', section:'warehouse', cols:4),
                new FormField('warehouse_id', type:'select', label:'Gudang', section:'warehouse', cols:6),
                new FormField('rack_location', type:'text', label:'Rak / Bin', section:'warehouse', cols:6),
                new FormField('supplier_id', type:'autocomplete', label:'Supplier', section:'warehouse', cols:12,
                    asyncUrl:'/api/suppliers/search', optionLabel:'name', optionValue:'id'),
                new FormField('images', type:'gallery', label:'Foto Produk', section:'basic', cols:12, accept:'image/*', multiple:true),
            ])
            ->addAction(new FormAction('save', 'Simpan', variant:'primary', shortcut:'Ctrl+S'))
            ->addAction(new FormAction('save_and_new', 'Simpan & Baru', variant:'secondary'))
            ->addAction(new FormAction('save_draft', 'Draft', variant:'outline'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION DEFINITIONS
    // ═══════════════════════════════════════════════════════════
    
    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            // Low stock alert
            (new AutomationDefinition('inventory.low_stock', 'Stok Menipis — Peringatan',
                trigger: TriggerType::STOCK_LOW, module: 'inventory'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => 'Stok Menipis', 'body' => '{{subject.name}} tersisa {{subject.stock_quantity}}.',
                ]))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                    'message' => '⚠ Stok {{subject.name}} menipis.',
                ])),

            // Goods received
            (new AutomationDefinition('inventory.goods_received', 'Barang Diterima — Update',
                trigger: TriggerType::RECORD_UPDATED, module: 'inventory'))
                ->addStep(new AutomationStep(ActionType::ADD_TIMELINE, [
                    'message' => '📦 Barang diterima: {{subject.name}} (+{{changes.quantity}}).',
                ])),

            // Dead stock detection
            (new AutomationDefinition('inventory.dead_stock', 'Dead Stock — Peringatan',
                trigger: TriggerType::SCHEDULE, module: 'inventory', schedule: '0 8 * * 1'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                    'message' => '⚠ Dead stock terdeteksi untuk produk yang tidak bergerak > 90 hari.',
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
            // Stock value report
            (new ReportDefinition('inventory.stock_value', 'Nilai Stok',
                type:'summary', modelClass: \App\Models\Tenant\Product::class, chartType:'bar',
                features:['products'], permissions:['manage_products']))
                ->addMetric(new MetricDefinition('total_value', 'Nilai Total', 'sum', 'cost_price', format:'currency', color:'primary', icon:'💰'))
                ->addMetric(new MetricDefinition('item_count', 'Jumlah Item', 'count', 'id', format:'number'))
                ->addDimension(new DimensionDefinition('category', 'Kategori', 'category', type:'string'))
                ->addFilter(new ReportFilter('warehouse_id', 'Gudang', 'select')),

            // Fast moving products
            (new ReportDefinition('inventory.fast_moving', 'Produk Fast Moving',
                type:'summary', chartType:'bar', features:['products']))
                ->addMetric(new MetricDefinition('sold', 'Terjual', 'count', 'id', format:'number'))
                ->addMetric(new MetricDefinition('revenue', 'Pendapatan', 'sum', 'total', format:'currency', color:'success'))
                ->addDimension(new DimensionDefinition('name', 'Produk', 'name', type:'string')),

            // Stock mutation
            (new ReportDefinition('inventory.mutation', 'Mutasi Stok',
                type:'summary', chartType:'line', features:['products'], permissions:['manage_products']))
                ->addMetric(new MetricDefinition('qty_in', 'Masuk', 'sum', 'quantity_in', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('qty_out', 'Keluar', 'sum', 'quantity_out', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('date', 'Tanggal', 'created_at', type:'date'))
                ->addFilter(new ReportFilter('date_range', 'Periode', 'date_range')),

            // Supplier analysis
            (new ReportDefinition('inventory.supplier_analysis', 'Analisis Supplier',
                type:'summary', chartType:'bar', features:['products'], permissions:['manage_finance']))
                ->addMetric(new MetricDefinition('total_purchased', 'Total Pembelian', 'sum', 'amount', format:'currency', color:'primary'))
                ->addDimension(new DimensionDefinition('supplier', 'Supplier', 'supplier_name', type:'string')),

            // Margin analysis
            (new ReportDefinition('inventory.margin', 'Analisis Margin',
                type:'summary', chartType:'kpi', features:['products'], permissions:['manage_finance']))
                ->addMetric(new MetricDefinition('revenue', 'Pendapatan', 'sum', 'selling_price', format:'currency', color:'success', icon:'💰'))
                ->addMetric(new MetricDefinition('cost', 'HPP', 'sum', 'cost_price', format:'currency', color:'danger', icon:'📤'))
                ->addMetric(new MetricDefinition('margin', 'Margin', 'sum', 'margin', format:'currency', color:'primary', icon:'📈', trend:true)),
        ];
    }
}
