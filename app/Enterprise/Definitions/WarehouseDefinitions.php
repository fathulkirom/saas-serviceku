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
 * WarehouseDefinitions — ALL Enterprise definitions for Logistics, Warehouse Operations & Supply Chain.
 * 
 * Covers: Multi-Warehouse, Putaway, Picking, Packing, Shipping, Receiving,
 * Stock Transfer, Cycle Count, Inventory Operations, Supply Chain.
 * 
 * MODUL ERP KESEBELAS — ENTERPRISE LOGISTICS, WAREHOUSE OPS & SUPPLY CHAIN (WMS)
 */
class WarehouseDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // WAREHOUSE WORKSPACE (16 tabs)
    // ═══════════════════════════════════════════════════════════

    public static function workspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'warehouse',
            title: 'Warehouse Workspace',
            icon: '🏬',
            tabs: [
                ['id' => 'overview',        'label' => 'Overview',         'icon' => '📊'],
                ['id' => 'warehouse',       'label' => 'Warehouse',        'icon' => '🏬'],
                ['id' => 'locations',       'label' => 'Locations',        'icon' => '📍'],
                ['id' => 'putaway',         'label' => 'Putaway',          'icon' => '📥'],
                ['id' => 'picking',         'label' => 'Picking',          'icon' => '📤'],
                ['id' => 'packing',         'label' => 'Packing',          'icon' => '📦'],
                ['id' => 'shipping',        'label' => 'Shipping',         'icon' => '🚚'],
                ['id' => 'receiving',       'label' => 'Receiving',        'icon' => '📨'],
                ['id' => 'transfers',       'label' => 'Transfers',        'icon' => '🔄'],
                ['id' => 'cycle_count',     'label' => 'Cycle Count',      'icon' => '🔢'],
                ['id' => 'stock_movement',  'label' => 'Stock Movement',   'icon' => '📊'],
                ['id' => 'cross_dock',      'label' => 'Cross Dock',       'icon' => '🔀'],
                ['id' => 'timeline',        'label' => 'Timeline',         'icon' => '🕐'],
                ['id' => 'activity',        'label' => 'Activity Log',     'icon' => '📊'],
                ['id' => 'documents',       'label' => 'Documents',        'icon' => '📄'],
                ['id' => 'history',         'label' => 'History',          'icon' => '📜'],
            ],
            actions: [
                ['id' => 'create_warehouse',  'label' => 'New Warehouse',    'roles' => ['owner','warehouse_manager']],
                ['id' => 'receive_goods',     'label' => 'Receive Goods',    'roles' => ['owner','warehouse_manager','warehouse_supervisor','receiving_staff']],
                ['id' => 'start_picking',     'label' => 'Start Picking',    'roles' => ['owner','warehouse_manager','warehouse_supervisor','picking_staff']],
                ['id' => 'start_packing',     'label' => 'Start Packing',    'roles' => ['owner','warehouse_manager','warehouse_supervisor','packing_staff']],
                ['id' => 'create_shipment',   'label' => 'Create Shipment',  'roles' => ['owner','warehouse_manager','warehouse_supervisor','logistics']],
                ['id' => 'request_transfer',  'label' => 'Request Transfer', 'roles' => ['owner','warehouse_manager','warehouse_supervisor']],
                ['id' => 'schedule_cycle_count','label' => 'Schedule Count', 'roles' => ['owner','warehouse_manager','inventory_controller']],
                ['id' => 'export',            'label' => 'Export',           'roles' => ['owner','warehouse_manager','management']],
            ],
            sidebarWidgets: [
                ['id' => 'warehouse_status',   'component' => 'WarehouseStatus',  'priority' => 10],
                ['id' => 'pending_queues',     'component' => 'PendingQueues',    'priority' => 20],
                ['id' => 'capacity_alerts',    'component' => 'CapacityAlerts',   'priority' => 30],
                ['id' => 'quick_actions',      'component' => 'QuickActions',     'priority' => 40],
            ],
            features: ['warehouse'],
            permissions: ['manage_warehouse'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // WAREHOUSE MANAGEMENT — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function warehouseTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'warehouse.index',
            title: 'Warehouse Management',
            modelClass: \App\Models\Tenant\Warehouse::class,
            defaultSort: ['warehouse_code' => 'asc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['warehouse'],
        ))
            ->addColumns([
                new ColumnDefinition('warehouse_code',   'Kode',          type:'text',    sortable:true, bold:true, width:'90px', order:1),
                new ColumnDefinition('warehouse_name',   'Nama Gudang',   type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('branch',           'Cabang',        type:'text',    sortable:true, width:'100px', order:3),
                new ColumnDefinition('zone_count',       'Zone',          type:'number',  width:'55px', align:'center', order:4),
                new ColumnDefinition('bin_count',        'Bin',           type:'number',  width:'55px', align:'center', order:5),
                new ColumnDefinition('capacity_pct',     'Capacity %',    type:'progress', sortable:true, width:'100px', order:6),
                new ColumnDefinition('utilization_pct',  'Utilization %', type:'progress', sortable:true, width:'100px', order:7),
                new ColumnDefinition('total_sku',        'SKU',           type:'number',  width:'60px', align:'center', order:8),
                new ColumnDefinition('manager_name',     'Manager',       type:'text',    width:'110px', order:9),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, width:'80px', order:10),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'active','label'=>'Active'],
                ['value'=>'maintenance','label'=>'Maintenance'],
                ['value'=>'closed','label'=>'Closed'],
            ], order:1))
            ->addFilter(new FilterDefinition('branch', 'Cabang', type:'select', order:2))
            ->addBulkAction(new BulkAction('activate', 'Activate', variant:'primary'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // RECEIVING — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function receivingTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'warehouse.receiving.index',
            title: 'Receiving',
            modelClass: \App\Models\Tenant\Receiving::class,
            defaultSort: ['received_date' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['warehouse'],
        ))
            ->addColumns([
                new ColumnDefinition('receiving_number', 'Receiving #',   type:'text',    sortable:true, bold:true, width:'120px', order:1),
                new ColumnDefinition('asn_reference',    'ASN Ref',       type:'text',    width:'120px', order:2),
                new ColumnDefinition('supplier_name',    'Supplier',      type:'text',    sortable:true, searchable:true, order:3),
                new ColumnDefinition('warehouse_name',   'Warehouse',     type:'text',    sortable:true, width:'110px', order:4),
                new ColumnDefinition('expected_qty',     'Expected',      type:'number',  width:'70px', align:'center', order:5),
                new ColumnDefinition('received_qty',     'Received',      type:'number',  width:'70px', align:'center', bold:true, order:6),
                new ColumnDefinition('damaged_qty',      'Damaged',       type:'number',  width:'60px', align:'center', order:7),
                new ColumnDefinition('received_date',    'Received Date', type:'date',    sortable:true, width:'100px', order:8),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'90px', order:9),
                new ColumnDefinition('inspector_name',   'Inspector',     type:'text',    width:'100px', order:10),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'pending','label'=>'Pending'],
                ['value'=>'partial','label'=>'Partial'],
                ['value'=>'complete','label'=>'Complete'],
                ['value'=>'inspected','label'=>'Inspected'],
                ['value'=>'on_hold','label'=>'On Hold'],
            ], order:1))
            ->addFilter(new FilterDefinition('warehouse_id', 'Warehouse', type:'select', order:2))
            ->addFilter(new FilterDefinition('received_date', 'Tanggal', type:'date_range', order:3))
            ->addBulkAction(new BulkAction('inspect', 'Inspect', variant:'primary'))
            ->addBulkAction(new BulkAction('putaway', 'Putaway', variant:'success'))
            ->addBulkAction(new BulkAction('hold', 'Quality Hold', variant:'warning'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // PUTAWAY — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function putawayTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'warehouse.putaway.index',
            title: 'Putaway Tasks',
            modelClass: \App\Models\Tenant\PutawayTask::class,
            defaultSort: ['priority_order' => 'asc', 'created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['warehouse'],
        ))
            ->addColumns([
                new ColumnDefinition('task_number',      'Task #',        type:'text',    sortable:true, bold:true, width:'90px', order:1),
                new ColumnDefinition('item_name',        'Item',          type:'text',    sortable:true, searchable:true, order:2),
                new ColumnDefinition('qty',              'Qty',           type:'number',  width:'55px', align:'center', order:3),
                new ColumnDefinition('from_location',    'From',          type:'text',    width:'100px', order:4),
                new ColumnDefinition('to_location',      'To (Suggested)',type:'text',    width:'110px', order:5),
                new ColumnDefinition('zone_strategy',    'Zone Strategy', type:'badge',   width:'90px', order:6),
                new ColumnDefinition('assignee_name',    'Assignee',      type:'text',    width:'100px', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'90px', order:8),
                new ColumnDefinition('completed_at',     'Completed',     type:'datetime',sortable:true, width:'130px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'pending','label'=>'Pending'],
                ['value'=>'in_progress','label'=>'In Progress'],
                ['value'=>'completed','label'=>'Completed'],
            ], order:1))
            ->addFilter(new FilterDefinition('zone_strategy', 'Zone', type:'select', options:[['value'=>'abc','label'=>'ABC'],['value'=>'fifo','label'=>'FIFO'],['value'=>'fefo','label'=>'FEFO']], order:2))
            ->addBulkAction(new BulkAction('assign', 'Assign', variant:'primary'))
            ->addBulkAction(new BulkAction('complete', 'Complete', variant:'success'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // PICKING — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function pickingTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'warehouse.picking.index',
            title: 'Picking Tasks',
            modelClass: \App\Models\Tenant\PickingTask::class,
            defaultSort: ['priority_order' => 'asc', 'created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['warehouse'],
        ))
            ->addColumns([
                new ColumnDefinition('task_number',      'Task #',        type:'text',    sortable:true, bold:true, width:'90px', order:1),
                new ColumnDefinition('wave_number',      'Wave #',        type:'text',    sortable:true, width:'90px', order:2),
                new ColumnDefinition('item_name',        'Item',          type:'text',    sortable:true, searchable:true, order:3),
                new ColumnDefinition('qty',              'Qty',           type:'number',  width:'55px', align:'center', order:4),
                new ColumnDefinition('pick_location',    'Pick Location', type:'text',    width:'100px', order:5),
                new ColumnDefinition('picking_type',     'Type',          type:'badge',   sortable:true, width:'90px', order:6),
                new ColumnDefinition('assignee_name',    'Picker',        type:'text',    width:'100px', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'90px', order:8),
                new ColumnDefinition('completed_at',     'Completed',     type:'datetime',sortable:true, width:'130px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'pending','label'=>'Pending'],
                ['value'=>'assigned','label'=>'Assigned'],
                ['value'=>'in_progress','label'=>'In Progress'],
                ['value'=>'completed','label'=>'Completed'],
                ['value'=>'exception','label'=>'Exception'],
            ], order:1))
            ->addFilter(new FilterDefinition('picking_type', 'Type', type:'select', quick:true, options:[
                ['value'=>'wave','label'=>'Wave'],
                ['value'=>'batch','label'=>'Batch'],
                ['value'=>'zone','label'=>'Zone'],
                ['value'=>'cluster','label'=>'Cluster'],
                ['value'=>'single','label'=>'Single'],
            ], order:2))
            ->addBulkAction(new BulkAction('assign', 'Assign', variant:'primary'))
            ->addBulkAction(new BulkAction('complete', 'Complete', variant:'success'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // PACKING — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function packingTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'warehouse.packing.index',
            title: 'Packing Station',
            modelClass: \App\Models\Tenant\PackingTask::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['warehouse'],
        ))
            ->addColumns([
                new ColumnDefinition('packing_number',   'Packing #',     type:'text',    sortable:true, bold:true, width:'100px', order:1),
                new ColumnDefinition('sales_number',     'Order #',       type:'text',    sortable:true, width:'110px', order:2),
                new ColumnDefinition('item_count',       'Items',         type:'number',  width:'55px', align:'center', order:3),
                new ColumnDefinition('package_type',     'Package',       type:'badge',   width:'80px', order:4),
                new ColumnDefinition('weight_kg',        'Weight (kg)',   type:'number',  width:'80px', align:'center', order:5),
                new ColumnDefinition('carrier',          'Carrier',       type:'text',    width:'90px', order:6),
                new ColumnDefinition('tracking_number',  'Tracking #',    type:'text',    width:'130px', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'90px', order:8),
                new ColumnDefinition('packer_name',      'Packer',        type:'text',    width:'100px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'pending','label'=>'Pending'],
                ['value'=>'packing','label'=>'Packing'],
                ['value'=>'packed','label'=>'Packed'],
                ['value'=>'shipped','label'=>'Shipped'],
            ], order:1))
            ->addFilter(new FilterDefinition('carrier', 'Carrier', type:'select', order:2))
            ->addBulkAction(new BulkAction('print_label', 'Print Label', variant:'default'))
            ->addBulkAction(new BulkAction('mark_shipped', 'Mark Shipped', variant:'primary'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // SHIPPING — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function shippingTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'warehouse.shipping.index',
            title: 'Shipping & Deliveries',
            modelClass: \App\Models\Tenant\Shipment::class,
            defaultSort: ['shipment_date' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['warehouse'],
        ))
            ->addColumns([
                new ColumnDefinition('shipment_number',  'Shipment #',    type:'text',    sortable:true, bold:true, width:'110px', order:1),
                new ColumnDefinition('customer_name',    'Customer',      type:'text',    sortable:true, searchable:true, order:2),
                new ColumnDefinition('package_count',    'Packages',      type:'number',  width:'70px', align:'center', order:3),
                new ColumnDefinition('carrier',          'Carrier',       type:'text',    sortable:true, width:'90px', order:4),
                new ColumnDefinition('tracking_number',  'Tracking #',    type:'text',    width:'130px', order:5),
                new ColumnDefinition('route',            'Route',         type:'text',    width:'110px', order:6),
                new ColumnDefinition('shipment_date',    'Ship Date',     type:'date',    sortable:true, width:'100px', order:7),
                new ColumnDefinition('delivery_date',    'Delivered',     type:'date',    sortable:true, width:'100px', order:8),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'100px', order:9),
                new ColumnDefinition('proof_of_delivery','POD',           type:'boolean', width:'50px', align:'center', order:10),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'created','label'=>'Created'],
                ['value'=>'in_transit','label'=>'In Transit'],
                ['value'=>'delivered','label'=>'Delivered'],
                ['value'=>'failed','label'=>'Failed'],
                ['value'=>'returned','label'=>'Returned'],
            ], order:1))
            ->addFilter(new FilterDefinition('carrier', 'Carrier', type:'select', order:2))
            ->addFilter(new FilterDefinition('shipment_date', 'Date', type:'date_range', order:3))
            ->addBulkAction(new BulkAction('dispatch', 'Dispatch', variant:'primary'))
            ->addBulkAction(new BulkAction('mark_delivered', 'Mark Delivered', variant:'success'))
            ->addBulkAction(new BulkAction('print_manifest', 'Print Manifest', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // STOCK TRANSFER — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function transferTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'warehouse.transfer.index',
            title: 'Stock Transfers',
            modelClass: \App\Models\Tenant\StockTransfer::class,
            defaultSort: ['request_date' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['warehouse'],
        ))
            ->addColumns([
                new ColumnDefinition('transfer_number',  'Transfer #',    type:'text',    sortable:true, bold:true, width:'110px', order:1),
                new ColumnDefinition('from_warehouse',   'Dari',          type:'text',    sortable:true, width:'110px', order:2),
                new ColumnDefinition('to_warehouse',     'Ke',            type:'text',    sortable:true, width:'110px', order:3),
                new ColumnDefinition('item_count',       'Items',         type:'number',  width:'55px', align:'center', order:4),
                new ColumnDefinition('total_qty',        'Total Qty',     type:'number',  width:'70px', align:'center', order:5),
                new ColumnDefinition('request_date',     'Request Date',  type:'date',    sortable:true, width:'100px', order:6),
                new ColumnDefinition('eta_date',         'ETA',           type:'date',    sortable:true, width:'100px', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'100px', order:8),
                new ColumnDefinition('receiver_name',    'Receiver',      type:'text',    width:'100px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'requested','label'=>'Requested'],
                ['value'=>'approved','label'=>'Approved'],
                ['value'=>'in_transit','label'=>'In Transit'],
                ['value'=>'received','label'=>'Received'],
                ['value'=>'cancelled','label'=>'Cancelled'],
            ], order:1))
            ->addFilter(new FilterDefinition('from_warehouse', 'From', type:'select', order:2))
            ->addFilter(new FilterDefinition('to_warehouse', 'To', type:'select', order:3))
            ->addBulkAction(new BulkAction('approve', 'Approve', variant:'primary'))
            ->addBulkAction(new BulkAction('confirm_receipt', 'Confirm Receipt', variant:'success'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // CYCLE COUNT — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function cycleCountTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'warehouse.cycle_count.index',
            title: 'Cycle Counts',
            modelClass: \App\Models\Tenant\CycleCount::class,
            defaultSort: ['count_date' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['warehouse'],
        ))
            ->addColumns([
                new ColumnDefinition('count_number',     'Count #',       type:'text',    sortable:true, bold:true, width:'90px', order:1),
                new ColumnDefinition('warehouse_name',   'Warehouse',     type:'text',    sortable:true, width:'110px', order:2),
                new ColumnDefinition('zone',             'Zone',          type:'text',    width:'80px', order:3),
                new ColumnDefinition('count_type',       'Type',          type:'badge',   sortable:true, width:'90px', order:4),
                new ColumnDefinition('item_count',       'Items Counted', type:'number',  width:'90px', align:'center', order:5),
                new ColumnDefinition('variance_count',   'Variance Items',type:'number',  width:'90px', align:'center', order:6),
                new ColumnDefinition('accuracy_pct',     'Accuracy %',    type:'number',  sortable:true, width:'80px', align:'center', bold:true, order:7),
                new ColumnDefinition('count_date',       'Count Date',    type:'date',    sortable:true, width:'100px', order:8),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'90px', order:9),
                new ColumnDefinition('counter_name',     'Counter',       type:'text',    width:'100px', order:10),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'scheduled','label'=>'Scheduled'],
                ['value'=>'in_progress','label'=>'In Progress'],
                ['value'=>'completed','label'=>'Completed'],
                ['value'=>'reconciled','label'=>'Reconciled'],
            ], order:1))
            ->addFilter(new FilterDefinition('count_type', 'Type', type:'select', quick:true, options:[
                ['value'=>'abc','label'=>'ABC Count'],
                ['value'=>'scheduled','label'=>'Scheduled'],
                ['value'=>'blind','label'=>'Blind Count'],
                ['value'=>'physical','label'=>'Physical'],
            ], order:2))
            ->addBulkAction(new BulkAction('start', 'Start Count', variant:'primary'))
            ->addBulkAction(new BulkAction('reconcile', 'Reconcile', variant:'success'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES — 15 Rules
    // ═══════════════════════════════════════════════════════════

    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            (new AutomationDefinition('wms.goods_received', 'Goods Received',
                trigger: TriggerType::RECORD_CREATED, module: 'warehouse'))
                ->addStep(new AutomationStep(ActionType::CREATE_PUTAWAY_TASK, ['priority' => 'high']))
                ->addStep(new AutomationStep(ActionType::UPDATE_INVENTORY, []))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '📨 Receiving #{{subject.receiving_number}} completed.'])),

            (new AutomationDefinition('wms.putaway_completed', 'Putaway Completed',
                trigger: TriggerType::RECORD_UPDATED, module: 'warehouse'))
                ->addStep(new AutomationStep(ActionType::UPDATE_LOCATION, []))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '📥 Putaway #{{subject.task_number}} completed.'])),

            (new AutomationDefinition('wms.picking_assigned', 'Picking Assigned',
                trigger: TriggerType::RECORD_UPDATED, module: 'warehouse'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '📤 Picking Assigned', 'body' => 'Wave #{{subject.wave_number}} assigned to you.'])),

            (new AutomationDefinition('wms.picking_completed', 'Picking Completed',
                trigger: TriggerType::RECORD_UPDATED, module: 'warehouse'))
                ->addStep(new AutomationStep(ActionType::CREATE_PACKING_TASK, []))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '📤 Picking #{{subject.task_number}} completed.'])),

            (new AutomationDefinition('wms.packing_completed', 'Packing Completed',
                trigger: TriggerType::RECORD_UPDATED, module: 'warehouse'))
                ->addStep(new AutomationStep(ActionType::CREATE_SHIPMENT, []))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '📦 Packing #{{subject.packing_number}} completed.'])),

            (new AutomationDefinition('wms.shipment_created', 'Shipment Created',
                trigger: TriggerType::RECORD_CREATED, module: 'warehouse'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🚚 New Shipment', 'body' => 'Shipment #{{subject.shipment_number}} ready for dispatch.', 'roles' => ['logistics', 'courier']])),

            (new AutomationDefinition('wms.shipment_delivered', 'Shipment Delivered',
                trigger: TriggerType::RECORD_UPDATED, module: 'warehouse'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '✅ Shipment #{{subject.shipment_number}} delivered.'])),

            (new AutomationDefinition('wms.transfer_requested', 'Transfer Requested',
                trigger: TriggerType::RECORD_CREATED, module: 'warehouse'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🔄 Transfer Request', 'body' => 'Transfer #{{subject.transfer_number}} needs approval.', 'roles' => ['warehouse_manager']])),

            (new AutomationDefinition('wms.transfer_approved', 'Transfer Approved',
                trigger: TriggerType::RECORD_UPDATED, module: 'warehouse'))
                ->addStep(new AutomationStep(ActionType::UPDATE_INVENTORY, ['movement' => 'transfer_out']))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '✅ Transfer #{{subject.transfer_number}} approved.'])),

            (new AutomationDefinition('wms.cycle_count_scheduled', 'Cycle Count Scheduled',
                trigger: TriggerType::RECORD_CREATED, module: 'warehouse'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'Cycle Count: {{subject.count_number}}', 'assignee_role' => 'inventory_controller'])),

            (new AutomationDefinition('wms.stock_variance', 'Stock Variance Detected',
                trigger: TriggerType::RECORD_UPDATED, module: 'warehouse'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'Investigate variance: {{subject.count_number}}', 'assignee_role' => 'inventory_controller']))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '⚠️ Stock Variance', 'body' => 'Cycle count #{{subject.count_number}} found variances.', 'roles' => ['inventory_controller', 'warehouse_manager']])),

            (new AutomationDefinition('wms.warehouse_full', 'Warehouse Full',
                trigger: TriggerType::RECORD_UPDATED, module: 'warehouse'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🏬 Warehouse Full', 'body' => '{{subject.warehouse_name}} at {{subject.capacity_pct}}% capacity.', 'roles' => ['warehouse_manager', 'management']])),

            (new AutomationDefinition('wms.low_capacity', 'Low Capacity Alert',
                trigger: TriggerType::RECORD_UPDATED, module: 'warehouse'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '⚠️ Low Capacity', 'body' => '{{subject.warehouse_name}} available: {{subject.free_pct}}%.', 'roles' => ['warehouse_manager']])),

            (new AutomationDefinition('wms.asn_received', 'ASN Received',
                trigger: TriggerType::RECORD_CREATED, module: 'warehouse'))
                ->addStep(new AutomationStep(ActionType::CREATE_RECEIVING_TASK, []))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '📋 ASN {{subject.asn_reference}} received.'])),

            (new AutomationDefinition('wms.3pl_updated', '3PL Updated',
                trigger: TriggerType::RECORD_UPDATED, module: 'warehouse'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '🔗 3PL updated for shipment #{{subject.shipment_number}}.'])),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // REPORTING DEFINITIONS — 15 Reports
    // ═══════════════════════════════════════════════════════════

    /** @return ReportDefinition[] */
    public static function reports(): array
    {
        return [
            (new ReportDefinition('wms.warehouse_utilization', 'Warehouse Utilization',
                type:'summary', chartType:'bar', features:['warehouse']))
                ->addMetric(new MetricDefinition('capacity_pct', 'Capacity %', 'avg', 'capacity_pct', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('utilization_pct', 'Utilization %', 'avg', 'utilization_pct', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('warehouse', 'Warehouse', 'warehouse_name', type:'string')),

            (new ReportDefinition('wms.inventory_movement', 'Inventory Movement',
                type:'trend', chartType:'line', features:['warehouse']))
                ->addMetric(new MetricDefinition('in_qty', 'In', 'sum', 'in_qty', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('out_qty', 'Out', 'sum', 'out_qty', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('date', 'Date', 'movement_date', type:'date'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            (new ReportDefinition('wms.receiving_performance', 'Receiving Performance',
                type:'summary', chartType:'table', features:['warehouse']))
                ->addMetric(new MetricDefinition('total_received', 'Received', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('on_time_pct', 'On Time %', 'avg', 'on_time_pct', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('damage_pct', 'Damage %', 'avg', 'damage_pct', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('supplier', 'Supplier', 'supplier_name', type:'string')),

            (new ReportDefinition('wms.picking_performance', 'Picking Performance',
                type:'summary', chartType:'bar', features:['warehouse']))
                ->addMetric(new MetricDefinition('tasks_completed', 'Tasks', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('avg_time_min', 'Avg Time (min)', 'avg', 'completion_minutes', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('accuracy_pct', 'Accuracy %', 'avg', 'accuracy_pct', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('picker', 'Picker', 'picker_name', type:'string')),

            (new ReportDefinition('wms.packing_performance', 'Packing Performance',
                type:'summary', chartType:'table', features:['warehouse']))
                ->addMetric(new MetricDefinition('packages', 'Packages', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('avg_time_min', 'Avg Time (min)', 'avg', 'packing_minutes', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('packer', 'Packer', 'packer_name', type:'string')),

            (new ReportDefinition('wms.shipping_performance', 'Shipping Performance',
                type:'summary', chartType:'bar', features:['warehouse']))
                ->addMetric(new MetricDefinition('shipments', 'Shipments', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('delivered_on_time', 'On Time %', 'avg', 'on_time_pct', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('failed_pct', 'Failed %', 'avg', 'failed_pct', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('carrier', 'Carrier', 'carrier', type:'string')),

            (new ReportDefinition('wms.transfer_report', 'Transfer Report',
                type:'summary', chartType:'table', features:['warehouse']))
                ->addMetric(new MetricDefinition('transfers', 'Transfers', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('in_transit', 'In Transit', 'count', 'in_transit', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('avg_transit_days', 'Avg Transit Days', 'avg', 'transit_days', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('from_warehouse', 'From', 'from_warehouse', type:'string')),

            (new ReportDefinition('wms.cycle_count_accuracy', 'Cycle Count Accuracy',
                type:'summary', chartType:'bar', features:['warehouse']))
                ->addMetric(new MetricDefinition('accuracy_pct', 'Accuracy %', 'avg', 'accuracy_pct', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('variance_items', 'Variance Items', 'sum', 'variance_count', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('warehouse', 'Warehouse', 'warehouse_name', type:'string')),

            (new ReportDefinition('wms.stock_variance', 'Stock Variance',
                type:'summary', chartType:'table', features:['warehouse']))
                ->addMetric(new MetricDefinition('system_qty', 'System', 'sum', 'system_qty', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('physical_qty', 'Physical', 'sum', 'physical_qty', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('variance', 'Variance', 'expression', 'physical_qty - system_qty', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('item', 'Item', 'item_name', type:'string')),

            (new ReportDefinition('wms.warehouse_productivity', 'Warehouse Productivity',
                type:'summary', chartType:'bar', features:['warehouse']))
                ->addMetric(new MetricDefinition('receiving_lines', 'Receiving', 'count', 'receiving', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('picking_lines', 'Picking', 'count', 'picking', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('packing_lines', 'Packing', 'count', 'packing', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('total_lines', 'Total Lines/Hour', 'avg', 'lines_per_hour', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('staff', 'Staff', 'staff_name', type:'string')),

            (new ReportDefinition('wms.space_utilization', 'Space Utilization',
                type:'summary', chartType:'kpi', features:['warehouse']))
                ->addMetric(new MetricDefinition('total_capacity', 'Total Capacity (m³)', 'sum', 'capacity_m3', format:'number', icon:'🏬'))
                ->addMetric(new MetricDefinition('used_space', 'Used (m³)', 'sum', 'used_m3', format:'number', color:'primary', icon:'📦'))
                ->addMetric(new MetricDefinition('free_space', 'Free (m³)', 'sum', 'free_m3', format:'number', color:'success', icon:'🟢'))
                ->addMetric(new MetricDefinition('utilization_pct', 'Utilization %', 'expression', 'used_space / total_capacity * 100', format:'number', color:'info', icon:'📊')),

            (new ReportDefinition('wms.supply_chain_performance', 'Supply Chain Performance',
                type:'summary', chartType:'bar', features:['warehouse']))
                ->addMetric(new MetricDefinition('order_fulfillment_pct', 'Fulfillment %', 'avg', 'fulfillment_pct', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('lead_time_days', 'Lead Time (d)', 'avg', 'lead_time_days', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('stockout_count', 'Stockouts', 'count', 'stockouts', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('product', 'Product', 'product_name', type:'string')),

            (new ReportDefinition('wms.inventory_turnover', 'Inventory Turnover',
                type:'summary', chartType:'bar', features:['warehouse']))
                ->addMetric(new MetricDefinition('turnover_ratio', 'Turnover Ratio', 'avg', 'turnover_ratio', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('days_inventory', 'Days Inventory', 'avg', 'days_inventory', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('category', 'Category', 'category', type:'string')),

            (new ReportDefinition('wms.abc_analysis', 'ABC Analysis',
                type:'summary', chartType:'pie', features:['warehouse']))
                ->addMetric(new MetricDefinition('item_count', 'Items', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('value_pct', 'Value %', 'sum', 'value_pct', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('abc_class', 'Class', 'abc_class', type:'string')),

            (new ReportDefinition('wms.logistics_cost', 'Logistics Cost',
                type:'summary', chartType:'table', features:['warehouse'], permissions:['manage_finance']))
                ->addMetric(new MetricDefinition('receiving_cost', 'Receiving', 'sum', 'receiving_cost', format:'currency'))
                ->addMetric(new MetricDefinition('picking_cost', 'Picking', 'sum', 'picking_cost', format:'currency'))
                ->addMetric(new MetricDefinition('shipping_cost', 'Shipping', 'sum', 'shipping_cost', format:'currency', color:'warning'))
                ->addMetric(new MetricDefinition('total_cost', 'Total', 'sum', 'total_cost', format:'currency', color:'danger'))
                ->addDimension(new DimensionDefinition('warehouse', 'Warehouse', 'warehouse_name', type:'string')),
        ];
    }
}
