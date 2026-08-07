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
 * POSDefinitions — ALL Enterprise definitions for POS, Sales & Omnichannel Commerce.
 * 
 * Covers: Sales Management, POS Engine, Payment Engine, Promotion Engine,
 * Loyalty Engine, Delivery, Returns, Marketplace Integration, E-Commerce.
 * 
 * MODUL ERP KESEMBILAN — ENTERPRISE POS, SALES & OMNICHANNEL COMMERCE
 */
class POSDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // SALES WORKSPACE (12 tabs)
    // ═══════════════════════════════════════════════════════════

    public static function workspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'sales',
            title: 'POS Sales Workspace',
            icon: '🛒',
            tabs: [
                ['id' => 'overview',      'label' => 'Overview',       'icon' => '📊'],
                ['id' => 'items',         'label' => 'Items',          'icon' => '🛍️'],
                ['id' => 'payments',      'label' => 'Payments',       'icon' => '💳'],
                ['id' => 'customer',      'label' => 'Customer',       'icon' => '👤'],
                ['id' => 'promotion',     'label' => 'Promotion',      'icon' => '🎫'],
                ['id' => 'delivery',      'label' => 'Delivery',       'icon' => '🚚'],
                ['id' => 'invoice',       'label' => 'Invoice',        'icon' => '🧾'],
                ['id' => 'returns',       'label' => 'Returns',        'icon' => '↩️'],
                ['id' => 'timeline',      'label' => 'Timeline',       'icon' => '🕐'],
                ['id' => 'activity',      'label' => 'Activity Log',   'icon' => '📊'],
                ['id' => 'documents',     'label' => 'Documents',      'icon' => '📄'],
                ['id' => 'history',       'label' => 'History',        'icon' => '📜'],
            ],
            actions: [
                ['id' => 'new_sale',      'label' => 'New Sale',        'roles' => ['owner','manager','cashier','sales']],
                ['id' => 'new_quote',     'label' => 'New Quotation',   'roles' => ['owner','manager','sales']],
                ['id' => 'add_payment',   'label' => 'Add Payment',     'roles' => ['owner','manager','cashier']],
                ['id' => 'add_delivery',  'label' => 'Arrange Delivery','roles' => ['owner','manager','warehouse','courier']],
                ['id' => 'process_return','label' => 'Process Return',  'roles' => ['owner','manager','cashier']],
                ['id' => 'apply_promo',   'label' => 'Apply Promotion', 'roles' => ['owner','manager','cashier']],
                ['id' => 'export',        'label' => 'Export',          'roles' => ['owner','manager','sales']],
            ],
            sidebarWidgets: [
                ['id' => 'cart_summary',     'component' => 'CartSummary',    'priority' => 10],
                ['id' => 'quick_products',   'component' => 'QuickProducts',  'priority' => 20],
                ['id' => 'customer_info',    'component' => 'CustomerInfo',   'priority' => 30],
                ['id' => 'today_stats',      'component' => 'TodayStats',     'priority' => 40],
            ],
            features: ['sales'],
            permissions: ['manage_sales'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // SALES MANAGEMENT — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function salesTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'sales.index',
            title: 'Sales Management',
            modelClass: \App\Models\Tenant\Sale::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['sales'],
        ))
            ->addColumns([
                new ColumnDefinition('sales_number',     'No. Sales',      type:'text',    sortable:true, bold:true, width:'120px', order:1),
                new ColumnDefinition('sales_type',       'Tipe',           type:'badge',   sortable:true, filterable:true, width:'90px', order:2),
                new ColumnDefinition('customer_name',    'Customer',       type:'text',    sortable:true, searchable:true, bold:true, order:3),
                new ColumnDefinition('channel',          'Channel',        type:'badge',   sortable:true, filterable:true, width:'90px', order:4),
                new ColumnDefinition('salesman_name',    'Salesman',       type:'text',    sortable:true, width:'100px', order:5),
                new ColumnDefinition('branch',           'Cabang',         type:'text',    sortable:true, width:'90px', order:6),
                new ColumnDefinition('total_items',      'Items',          type:'number',  width:'60px', align:'center', order:7),
                new ColumnDefinition('grand_total',      'Total',          type:'currency', sortable:true, align:'right', bold:true, width:'130px', aggregate:true, aggregateType:'sum', order:8),
                new ColumnDefinition('profit',           'Profit',         type:'currency', sortable:true, align:'right', width:'110px', order:9),
                new ColumnDefinition('margin_pct',       'Margin',         type:'number',  sortable:true, width:'70px', align:'center', order:10),
                new ColumnDefinition('payment_status',   'Payment',        type:'badge',   sortable:true, filterable:true, width:'90px', order:11),
                new ColumnDefinition('status',           'Status',         type:'badge',   sortable:true, filterable:true, width:'90px', order:12),
                new ColumnDefinition('created_at',       'Tanggal',        type:'datetime', sortable:true, width:'130px', order:13),
                new ColumnDefinition('actions',          '',               type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'draft','label'=>'Draft'],
                ['value'=>'confirmed','label'=>'Confirmed'],
                ['value'=>'paid','label'=>'Paid'],
                ['value'=>'delivered','label'=>'Delivered'],
                ['value'=>'completed','label'=>'Completed'],
                ['value'=>'cancelled','label'=>'Cancelled'],
                ['value'=>'returned','label'=>'Returned'],
            ], order:1))
            ->addFilter(new FilterDefinition('sales_type', 'Tipe', type:'select', quick:true, options:[
                ['value'=>'pos','label'=>'POS'],
                ['value'=>'quotation','label'=>'Quotation'],
                ['value'=>'sales_order','label'=>'Sales Order'],
                ['value'=>'delivery_order','label'=>'Delivery Order'],
                ['value'=>'invoice','label'=>'Invoice'],
                ['value'=>'return','label'=>'Return'],
                ['value'=>'layaway','label'=>'Layaway'],
                ['value'=>'consignment','label'=>'Consignment'],
            ], order:2))
            ->addFilter(new FilterDefinition('channel', 'Channel', type:'select', quick:true, options:[
                ['value'=>'pos','label'=>'POS'],
                ['value'=>'website','label'=>'Website'],
                ['value'=>'whatsapp','label'=>'WhatsApp'],
                ['value'=>'marketplace','label'=>'Marketplace'],
                ['value'=>'instagram','label'=>'Instagram'],
                ['value'=>'facebook','label'=>'Facebook'],
                ['value'=>'tiktok','label'=>'TikTok Shop'],
            ], order:3))
            ->addFilter(new FilterDefinition('payment_status', 'Payment', type:'select', options:[['value'=>'unpaid','label'=>'Unpaid'],['value'=>'partial','label'=>'Partial'],['value'=>'paid','label'=>'Paid'],['value'=>'refunded','label'=>'Refunded']], order:4))
            ->addFilter(new FilterDefinition('created_at', 'Tanggal', type:'date_range', order:5))
            ->addFilter(new FilterDefinition('branch', 'Cabang', type:'select', order:6))
            ->addBulkAction(new BulkAction('confirm', 'Confirm', variant:'primary'))
            ->addBulkAction(new BulkAction('print_invoice', 'Print Invoice', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'))
            ->addBulkAction(new BulkAction('cancel', 'Cancel', variant:'danger', confirm:true));
    }

    // ═══════════════════════════════════════════════════════════
    // SALES FORM — Create/Edit
    // ═══════════════════════════════════════════════════════════

    public static function salesForm(): FormDefinition
    {
        return (new FormDefinition(
            id: 'sales.create',
            title: 'Create Sale',
            method: 'POST',
            endpoint: '/sales',
            features: ['sales'],
        ))
            ->addSection(new FormSection(id:'general',    label:'Informasi Umum',    icon:'📋', cols:2))
            ->addSection(new FormSection(id:'customer',   label:'Customer',          icon:'👤', cols:2))
            ->addSection(new FormSection(id:'items',      label:'Items',             icon:'🛍️', cols:1))
            ->addSection(new FormSection(id:'discount',   label:'Diskon & Promo',    icon:'🎫', cols:2))
            ->addSection(new FormSection(id:'tax',        label:'Pajak',             icon:'🧾', cols:2))
            ->addSection(new FormSection(id:'shipping',   label:'Pengiriman',        icon:'🚚', cols:2))
            ->addSection(new FormSection(id:'payment',    label:'Pembayaran',        icon:'💳', cols:2))
            ->addSection(new FormSection(id:'notes',      label:'Catatan',           icon:'📝', cols:1))
            ->addFields([
                // General
                new FormField('sales_type',       type:'select',   label:'Tipe',                required:true, section:'general', cols:4,
                    options:[['value'=>'pos','label'=>'POS'],['value'=>'quotation','label'=>'Quotation'],['value'=>'sales_order','label'=>'Sales Order']]),
                new FormField('channel',          type:'select',   label:'Channel',             required:true, section:'general', cols:4,
                    options:[['value'=>'pos','label'=>'POS'],['value'=>'website','label'=>'Website'],['value'=>'whatsapp','label'=>'WhatsApp'],['value'=>'marketplace','label'=>'Marketplace']]),
                new FormField('branch_id',        type:'select',   label:'Cabang',              required:true, section:'general', cols:4),
                new FormField('salesman_id',      type:'select',   label:'Salesman',            section:'general', cols:4),
                new FormField('warehouse_id',     type:'select',   label:'Gudang',              section:'general', cols:4),
                new FormField('notes',            type:'textarea', label:'Notes',               section:'general', cols:4),
                // Customer
                new FormField('customer_id',      type:'select',   label:'Customer',            section:'customer', cols:6),
                new FormField('customer_phone',   type:'phone',    label:'Phone',               section:'customer', cols:3),
                new FormField('customer_email',   type:'email',    label:'Email',               section:'customer', cols:3),
                // Items (repeater)
                new FormField('items',            type:'repeater', label:'Sales Items',          required:true, section:'items', cols:12, fields:[
                    ['name'=>'product_id','type'=>'select','label'=>'Product','cols'=>4],
                    ['name'=>'qty','type'=>'number','label'=>'Qty','cols'=>2],
                    ['name'=>'unit_price','type'=>'currency','label'=>'Price','cols'=>3],
                    ['name'=>'discount_pct','type'=>'number','label'=>'Disc %','cols'=>2],
                    ['name'=>'subtotal','type'=>'currency','label'=>'Subtotal','cols'=>1],
                ]),
                // Discount
                new FormField('discount_type',    type:'select',   label:'Tipe Diskon',         section:'discount', cols:4, options:[['value'=>'percentage','label'=>'Percentage'],['value'=>'nominal','label'=>'Nominal']]),
                new FormField('discount_value',   type:'currency', label:'Nilai Diskon',         section:'discount', cols:4),
                new FormField('promotion_id',     type:'select',   label:'Promosi',             section:'discount', cols:4),
                new FormField('voucher_code',     type:'text',     label:'Kode Voucher',         section:'discount', cols:4),
                // Tax
                new FormField('tax_inclusive',    type:'switch',   label:'Tax Inclusive',        section:'tax', cols:4),
                new FormField('tax_rate',         type:'number',   label:'Tax Rate (%)',         section:'tax', cols:4),
                new FormField('tax_amount',       type:'currency', label:'Tax Amount',           section:'tax', cols:4),
                // Shipping
                new FormField('delivery_method',  type:'select',   label:'Delivery Method',      section:'shipping', cols:4, options:[['value'=>'pickup','label'=>'Pickup'],['value'=>'delivery','label'=>'Delivery'],['value'=>'courier','label'=>'Courier']]),
                new FormField('shipping_address', type:'textarea', label:'Shipping Address',     section:'shipping', cols:8),
                new FormField('shipping_cost',    type:'currency', label:'Shipping Cost',        section:'shipping', cols:4),
                new FormField('courier_id',       type:'select',   label:'Courier',             section:'shipping', cols:4),
                // Payment
                new FormField('payment_method',   type:'select',   label:'Payment Method',       required:true, section:'payment', cols:4,
                    options:[['value'=>'cash','label'=>'Cash'],['value'=>'transfer','label'=>'Transfer'],['value'=>'qris','label'=>'QRIS'],['value'=>'debit','label'=>'Debit'],['value'=>'credit','label'=>'Credit Card'],['value'=>'ewallet','label'=>'E-Wallet']]),
                new FormField('payment_amount',   type:'currency', label:'Amount Paid',          section:'payment', cols:4),
                new FormField('payment_reference',type:'text',     label:'Reference',            section:'payment', cols:4),
                // Notes
                new FormField('internal_notes',   type:'textarea', label:'Internal Notes',       section:'notes', cols:12),
            ])
            ->addAction(new FormAction('save_draft', 'Save Draft', variant:'outline'))
            ->addAction(new FormAction('confirm', 'Confirm Order', variant:'primary', shortcut:'Ctrl+S'))
            ->addAction(new FormAction('save_and_new', 'Save & New', variant:'secondary'));
    }

    // ═══════════════════════════════════════════════════════════
    // PAYMENT TRANSACTIONS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function paymentTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'sales.payment.index',
            title: 'Payment Transactions',
            modelClass: \App\Models\Tenant\Payment::class,
            defaultSort: ['payment_date' => 'desc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            features: ['sales'],
        ))
            ->addColumns([
                new ColumnDefinition('payment_date',     'Tanggal',       type:'datetime',sortable:true, width:'130px', order:1),
                new ColumnDefinition('sales_number',     'No. Sales',     type:'text',    sortable:true, bold:true, width:'110px', order:2),
                new ColumnDefinition('customer_name',    'Customer',      type:'text',    sortable:true, order:3),
                new ColumnDefinition('payment_method',   'Method',        type:'badge',   sortable:true, filterable:true, width:'90px', order:4),
                new ColumnDefinition('amount',           'Amount',        type:'currency', sortable:true, align:'right', bold:true, width:'120px', aggregate:true, aggregateType:'sum', order:5),
                new ColumnDefinition('reference',        'Reference',     type:'text',    width:'120px', order:6),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, width:'80px', order:7),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('payment_method', 'Method', type:'select', quick:true, options:[
                ['value'=>'cash','label'=>'Cash'],['value'=>'transfer','label'=>'Transfer'],
                ['value'=>'qris','label'=>'QRIS'],['value'=>'debit','label'=>'Debit'],
                ['value'=>'credit','label'=>'Credit Card'],['value'=>'ewallet','label'=>'E-Wallet'],
                ['value'=>'split','label'=>'Split'],['value'=>'installment','label'=>'Installment'],
                ['value'=>'store_credit','label'=>'Store Credit'],['value'=>'gift_card','label'=>'Gift Card'],
            ], order:1))
            ->addFilter(new FilterDefinition('payment_date', 'Tanggal', type:'date_range', order:2))
            ->addBulkAction(new BulkAction('refund', 'Refund', variant:'warning'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // PROMOTION — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function promotionTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'sales.promotion.index',
            title: 'Promotions',
            modelClass: \App\Models\Tenant\Promotion::class,
            defaultSort: ['start_date' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['sales'],
        ))
            ->addColumns([
                new ColumnDefinition('promotion_name',   'Nama Promo',    type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('promotion_type',   'Tipe',          type:'badge',   sortable:true, filterable:true, width:'100px', order:2),
                new ColumnDefinition('discount_value',   'Nilai',         type:'text',    width:'100px', order:3),
                new ColumnDefinition('start_date',       'Mulai',         type:'date',    sortable:true, width:'100px', order:4),
                new ColumnDefinition('end_date',         'Berakhir',      type:'date',    sortable:true, width:'100px', order:5),
                new ColumnDefinition('usage_count',      'Used',          type:'number',  width:'60px', align:'center', order:6),
                new ColumnDefinition('max_usage',        'Limit',         type:'number',  width:'60px', align:'center', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'90px', order:8),
                new ColumnDefinition('channel',          'Channel',       type:'badge',   width:'90px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'active','label'=>'Active'],
                ['value'=>'scheduled','label'=>'Scheduled'],
                ['value'=>'ended','label'=>'Ended'],
                ['value'=>'paused','label'=>'Paused'],
            ], order:1))
            ->addFilter(new FilterDefinition('promotion_type', 'Tipe', type:'select', quick:true, options:[
                ['value'=>'percentage','label'=>'% Discount'],
                ['value'=>'nominal','label'=>'Nominal Discount'],
                ['value'=>'buy_x_get_y','label'=>'Buy X Get Y'],
                ['value'=>'bundle','label'=>'Bundle'],
                ['value'=>'flash_sale','label'=>'Flash Sale'],
                ['value'=>'coupon','label'=>'Coupon'],
                ['value'=>'membership','label'=>'Membership Price'],
            ], order:2))
            ->addFilter(new FilterDefinition('channel', 'Channel', type:'select', order:3))
            ->addBulkAction(new BulkAction('activate', 'Activate', variant:'primary'))
            ->addBulkAction(new BulkAction('pause', 'Pause', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // LOYALTY — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function loyaltyTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'sales.loyalty.index',
            title: 'Loyalty & Rewards',
            modelClass: \App\Models\Tenant\Loyalty::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['sales'],
        ))
            ->addColumns([
                new ColumnDefinition('customer_name',    'Customer',      type:'text',    sortable:true, searchable:true, order:1),
                new ColumnDefinition('member_level',     'Level',         type:'badge',   sortable:true, width:'90px', order:2),
                new ColumnDefinition('total_points',     'Points',        type:'number',  sortable:true, width:'80px', align:'center', bold:true, order:3),
                new ColumnDefinition('points_redeemed',  'Redeemed',      type:'number',  width:'80px', align:'center', order:4),
                new ColumnDefinition('points_expiring',  'Expiring',      type:'number',  width:'80px', align:'center', order:5),
                new ColumnDefinition('total_spent',      'Total Spent',   type:'currency', sortable:true, align:'right', width:'130px', order:6),
                new ColumnDefinition('transaction_count','Transactions',  type:'number',  width:'80px', align:'center', order:7),
                new ColumnDefinition('last_transaction', 'Last Purchase', type:'date',    sortable:true, width:'110px', order:8),
                new ColumnDefinition('wallet_balance',   'Wallet',        type:'currency', width:'110px', align:'right', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('member_level', 'Level', type:'select', quick:true, options:[
                ['value'=>'regular','label'=>'Regular'],
                ['value'=>'silver','label'=>'Silver'],
                ['value'=>'gold','label'=>'Gold'],
                ['value'=>'platinum','label'=>'Platinum'],
            ], order:1))
            ->addBulkAction(new BulkAction('add_points', 'Add Points', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // DELIVERY — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function deliveryTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'sales.delivery.index',
            title: 'Delivery Management',
            modelClass: \App\Models\Tenant\Delivery::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['sales'],
        ))
            ->addColumns([
                new ColumnDefinition('delivery_number',  'Delivery #',    type:'text',    sortable:true, bold:true, width:'110px', order:1),
                new ColumnDefinition('sales_number',     'Sales #',       type:'text',    sortable:true, width:'110px', order:2),
                new ColumnDefinition('customer_name',    'Customer',      type:'text',    sortable:true, order:3),
                new ColumnDefinition('delivery_method',  'Method',        type:'badge',   sortable:true, width:'90px', order:4),
                new ColumnDefinition('courier_name',     'Courier',       type:'text',    width:'100px', order:5),
                new ColumnDefinition('tracking_number',  'Tracking #',    type:'text',    width:'130px', order:6),
                new ColumnDefinition('delivery_date',    'Delivery Date', type:'date',    sortable:true, width:'100px', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'100px', order:8),
                new ColumnDefinition('proof_of_delivery','Proof',         type:'boolean', width:'60px', align:'center', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'pending','label'=>'Pending'],
                ['value'=>'packing','label'=>'Packing'],
                ['value'=>'shipped','label'=>'Shipped'],
                ['value'=>'in_transit','label'=>'In Transit'],
                ['value'=>'delivered','label'=>'Delivered'],
                ['value'=>'failed','label'=>'Failed'],
            ], order:1))
            ->addFilter(new FilterDefinition('delivery_method', 'Method', type:'select', options:[['value'=>'pickup','label'=>'Pickup'],['value'=>'courier','label'=>'Courier'],['value'=>'internal','label'=>'Internal']], order:2))
            ->addBulkAction(new BulkAction('mark_shipped', 'Mark Shipped', variant:'primary'))
            ->addBulkAction(new BulkAction('mark_delivered', 'Mark Delivered', variant:'success'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // RETURNS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function returnTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'sales.return.index',
            title: 'Returns & Refunds',
            modelClass: \App\Models\Tenant\SalesReturn::class,
            defaultSort: ['return_date' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['sales'],
        ))
            ->addColumns([
                new ColumnDefinition('return_number',    'Return #',      type:'text',    sortable:true, bold:true, width:'110px', order:1),
                new ColumnDefinition('sales_number',     'Original Sales#',type:'text',   sortable:true, width:'110px', order:2),
                new ColumnDefinition('customer_name',    'Customer',      type:'text',    sortable:true, order:3),
                new ColumnDefinition('return_type',      'Tipe',          type:'badge',   filterable:true, width:'90px', order:4),
                new ColumnDefinition('total_items',      'Items',         type:'number',  width:'60px', align:'center', order:5),
                new ColumnDefinition('return_amount',    'Amount',        type:'currency', sortable:true, align:'right', bold:true, width:'120px', order:6),
                new ColumnDefinition('refund_method',    'Refund',        type:'badge',   width:'90px', order:7),
                new ColumnDefinition('reason',           'Reason',        type:'text',    order:8),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, width:'100px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'pending','label'=>'Pending'],
                ['value'=>'inspected','label'=>'Inspected'],
                ['value'=>'approved','label'=>'Approved'],
                ['value'=>'refunded','label'=>'Refunded'],
                ['value'=>'rejected','label'=>'Rejected'],
            ], order:1))
            ->addFilter(new FilterDefinition('return_type', 'Tipe', type:'select', options:[['value'=>'return','label'=>'Return'],['value'=>'exchange','label'=>'Exchange'],['value'=>'refund','label'=>'Refund'],['value'=>'warranty','label'=>'Warranty']], order:2))
            ->addFilter(new FilterDefinition('return_date', 'Tanggal', type:'date_range', order:3))
            ->addBulkAction(new BulkAction('approve', 'Approve', variant:'primary'))
            ->addBulkAction(new BulkAction('refund', 'Process Refund', variant:'warning'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // MARKETPLACE ORDERS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function marketplaceTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'sales.marketplace.index',
            title: 'Marketplace Orders',
            modelClass: \App\Models\Tenant\MarketplaceOrder::class,
            defaultSort: ['order_date' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['sales'],
        ))
            ->addColumns([
                new ColumnDefinition('marketplace_order_id','Order ID',   type:'text',    sortable:true, bold:true, width:'140px', order:1),
                new ColumnDefinition('platform',         'Platform',      type:'badge',   sortable:true, filterable:true, width:'90px', order:2),
                new ColumnDefinition('customer_name',    'Customer',      type:'text',    sortable:true, order:3),
                new ColumnDefinition('total_items',      'Items',         type:'number',  width:'60px', align:'center', order:4),
                new ColumnDefinition('order_total',      'Order Total',   type:'currency', sortable:true, align:'right', width:'120px', order:5),
                new ColumnDefinition('shipping_fee',     'Shipping',      type:'currency', width:'100px', align:'right', order:6),
                new ColumnDefinition('order_date',       'Order Date',    type:'date',    sortable:true, width:'100px', order:7),
                new ColumnDefinition('sync_status',      'Sync',          type:'badge',   sortable:true, width:'80px', order:8),
                new ColumnDefinition('fulfillment_status','Fulfillment',  type:'badge',   width:'100px', order:9),
                new ColumnDefinition('settlement_status', 'Settlement',   type:'badge',   width:'90px', order:10),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('platform', 'Platform', type:'select', quick:true, options:[
                ['value'=>'shopee','label'=>'Shopee'],['value'=>'tokopedia','label'=>'Tokopedia'],
                ['value'=>'lazada','label'=>'Lazada'],['value'=>'blibli','label'=>'Blibli'],
                ['value'=>'tiktok','label'=>'TikTok Shop'],
            ], order:1))
            ->addFilter(new FilterDefinition('sync_status', 'Sync', type:'select', options:[['value'=>'synced','label'=>'Synced'],['value'=>'pending','label'=>'Pending'],['value'=>'error','label'=>'Error']], order:2))
            ->addFilter(new FilterDefinition('fulfillment_status', 'Fulfillment', type:'select', options:[['value'=>'pending','label'=>'Pending'],['value'=>'packed','label'=>'Packed'],['value'=>'shipped','label'=>'Shipped'],['value'=>'delivered','label'=>'Delivered']], order:3))
            ->addBulkAction(new BulkAction('sync', 'Sync Orders', variant:'primary'))
            ->addBulkAction(new BulkAction('fulfill', 'Fulfill', variant:'success'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES — 15 Rules
    // ═══════════════════════════════════════════════════════════

    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            (new AutomationDefinition('sales.quotation_approved', 'Quotation Approved',
                trigger: TriggerType::RECORD_UPDATED, module: 'sales'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '📋 Quotation Approved', 'body' => 'Quotation #{{subject.sales_number}} approved.',
                ])),

            (new AutomationDefinition('sales.created', 'Sales Created',
                trigger: TriggerType::RECORD_CREATED, module: 'sales'))
                ->addStep(new AutomationStep(ActionType::CREATE_JOURNAL, ['template' => 'sales_revenue']))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '🛒 Sale #{{subject.sales_number}} created.'])),

            (new AutomationDefinition('sales.paid', 'Sales Paid',
                trigger: TriggerType::RECORD_UPDATED, module: 'sales'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['message' => '✅ Pembayaran #{{subject.sales_number}} diterima. Terima kasih!'])),

            (new AutomationDefinition('sales.cancelled', 'Sales Cancelled',
                trigger: TriggerType::RECORD_UPDATED, module: 'sales'))
                ->addStep(new AutomationStep(ActionType::RESTORE_STOCK, []))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '❌ Sale #{{subject.sales_number}} cancelled.'])),

            (new AutomationDefinition('sales.invoice_due', 'Invoice Due',
                trigger: TriggerType::DATE_REACHED, module: 'sales'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['message' => '⏰ Invoice #{{subject.sales_number}} jatuh tempo. Mohon diselesaikan.'])),

            (new AutomationDefinition('sales.invoice_paid', 'Invoice Paid',
                trigger: TriggerType::RECORD_UPDATED, module: 'sales'))
                ->addStep(new AutomationStep(ActionType::CREATE_JOURNAL, ['template' => 'payment_receipt'])),

            (new AutomationDefinition('sales.delivery_completed', 'Delivery Completed',
                trigger: TriggerType::RECORD_UPDATED, module: 'sales'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '📦 Delivery #{{subject.delivery_number}} completed.'])),

            (new AutomationDefinition('sales.return_created', 'Return Created',
                trigger: TriggerType::RECORD_CREATED, module: 'sales'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'Inspect Return #{{subject.return_number}}', 'assignee_role' => 'warehouse'])),

            (new AutomationDefinition('sales.refund_approved', 'Refund Approved',
                trigger: TriggerType::RECORD_UPDATED, module: 'sales'))
                ->addStep(new AutomationStep(ActionType::CREATE_JOURNAL, ['template' => 'sales_refund'])),

            (new AutomationDefinition('sales.promotion_started', 'Promotion Started',
                trigger: TriggerType::DATE_REACHED, module: 'sales'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🎫 Promo Started', 'body' => '{{subject.promotion_name}} is now active!', 'roles' => ['sales', 'cashier']])),

            (new AutomationDefinition('sales.promotion_ended', 'Promotion Ended',
                trigger: TriggerType::DATE_REACHED, module: 'sales'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🎫 Promo Ended', 'body' => '{{subject.promotion_name}} has ended.'])),

            (new AutomationDefinition('sales.low_margin', 'Low Margin Warning',
                trigger: TriggerType::RECORD_UPDATED, module: 'sales'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '⚠️ Low Margin', 'body' => 'Sale #{{subject.sales_number}} margin below threshold.', 'roles' => ['manager', 'owner']])),

            (new AutomationDefinition('sales.marketplace_imported', 'Marketplace Order Imported',
                trigger: TriggerType::RECORD_CREATED, module: 'sales'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'Fulfill marketplace order {{subject.marketplace_order_id}}', 'assignee_role' => 'warehouse'])),

            (new AutomationDefinition('sales.points_earned', 'Customer Earned Points',
                trigger: TriggerType::RECORD_UPDATED, module: 'sales'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['message' => '⭐ Anda mendapatkan {{subject.points_earned}} poin! Total: {{subject.total_points}}.'])),

            (new AutomationDefinition('sales.points_redeemed', 'Customer Redeemed Points',
                trigger: TriggerType::RECORD_UPDATED, module: 'sales'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '🎁 {{subject.customer_name}} redeemed {{subject.points_redeemed}} points.'])),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // REPORTING DEFINITIONS — 15 Reports
    // ═══════════════════════════════════════════════════════════

    /** @return ReportDefinition[] */
    public static function reports(): array
    {
        return [
            (new ReportDefinition('sales.summary', 'Sales Summary',
                type:'summary', chartType:'table', features:['sales']))
                ->addMetric(new MetricDefinition('total_sales', 'Total Sales', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('revenue', 'Revenue', 'sum', 'grand_total', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('profit', 'Profit', 'sum', 'profit', format:'currency', color:'primary'))
                ->addDimension(new DimensionDefinition('branch', 'Branch', 'branch', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            (new ReportDefinition('sales.detail', 'Sales Detail',
                type:'summary', chartType:'table', features:['sales']))
                ->addMetric(new MetricDefinition('count', 'Qty', 'sum', 'qty', format:'number'))
                ->addMetric(new MetricDefinition('revenue', 'Revenue', 'sum', 'total', format:'currency', color:'success'))
                ->addDimension(new DimensionDefinition('product_name', 'Product', 'product_name', type:'string'))
                ->addDimension(new DimensionDefinition('branch', 'Branch', 'branch', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            (new ReportDefinition('sales.daily', 'Daily Sales',
                type:'trend', chartType:'line', features:['sales']))
                ->addMetric(new MetricDefinition('revenue', 'Revenue', 'sum', 'grand_total', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('transactions', 'Transactions', 'count', 'id', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('date', 'Date', 'created_at', type:'date'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            (new ReportDefinition('sales.pos_closing', 'POS Closing',
                type:'summary', chartType:'table', features:['sales']))
                ->addMetric(new MetricDefinition('cash', 'Cash', 'sum', 'cash_amount', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('transfer', 'Transfer', 'sum', 'transfer_amount', format:'currency'))
                ->addMetric(new MetricDefinition('qris', 'QRIS', 'sum', 'qris_amount', format:'currency'))
                ->addMetric(new MetricDefinition('ewallet', 'E-Wallet', 'sum', 'ewallet_amount', format:'currency'))
                ->addDimension(new DimensionDefinition('cashier', 'Cashier', 'cashier_name', type:'string'))
                ->addFilter(new ReportFilter('date', 'Date', 'date')),

            (new ReportDefinition('sales.cash_register', 'Cash Register',
                type:'summary', chartType:'table', features:['sales']))
                ->addMetric(new MetricDefinition('opening', 'Opening', 'sum', 'opening_balance', format:'currency'))
                ->addMetric(new MetricDefinition('sales_in', 'Sales In', 'sum', 'sales_amount', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('expenses', 'Expenses', 'sum', 'expenses', format:'currency', color:'danger'))
                ->addMetric(new MetricDefinition('closing', 'Closing', 'last', 'closing_balance', format:'currency', color:'primary'))
                ->addDimension(new DimensionDefinition('cashier', 'Cashier', 'cashier_name', type:'string')),

            (new ReportDefinition('sales.product_performance', 'Product Performance',
                type:'summary', chartType:'bar', features:['sales']))
                ->addMetric(new MetricDefinition('qty_sold', 'Qty Sold', 'sum', 'qty', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('revenue', 'Revenue', 'sum', 'total', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('profit', 'Profit', 'sum', 'profit', format:'currency', color:'info'))
                ->addDimension(new DimensionDefinition('product_name', 'Product', 'product_name', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            (new ReportDefinition('sales.salesman_performance', 'Salesman Performance',
                type:'summary', chartType:'bar', features:['sales']))
                ->addMetric(new MetricDefinition('sales_count', 'Sales', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('revenue', 'Revenue', 'sum', 'grand_total', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('avg_ticket', 'Avg Ticket', 'avg', 'grand_total', format:'currency', color:'info'))
                ->addDimension(new DimensionDefinition('salesman', 'Salesman', 'salesman_name', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            (new ReportDefinition('sales.branch_performance', 'Branch Performance',
                type:'summary', chartType:'bar', features:['sales']))
                ->addMetric(new MetricDefinition('revenue', 'Revenue', 'sum', 'grand_total', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('profit', 'Profit', 'sum', 'profit', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('margin_pct', 'Margin %', 'avg', 'margin_pct', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('branch', 'Branch', 'branch', type:'string')),

            (new ReportDefinition('sales.promotion_performance', 'Promotion Performance',
                type:'summary', chartType:'table', features:['sales']))
                ->addMetric(new MetricDefinition('usage_count', 'Used', 'count', 'used', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('revenue_with_promo', 'Revenue w/ Promo', 'sum', 'revenue_with_promo', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('discount_given', 'Discount Given', 'sum', 'discount_amount', format:'currency', color:'warning'))
                ->addDimension(new DimensionDefinition('promotion_name', 'Promotion', 'promotion_name', type:'string')),

            (new ReportDefinition('sales.marketplace_sales', 'Marketplace Sales',
                type:'summary', chartType:'bar', features:['sales']))
                ->addMetric(new MetricDefinition('orders', 'Orders', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('revenue', 'Revenue', 'sum', 'order_total', format:'currency', color:'success'))
                ->addDimension(new DimensionDefinition('platform', 'Platform', 'platform', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            (new ReportDefinition('sales.customer_loyalty', 'Customer Loyalty',
                type:'summary', chartType:'table', features:['sales']))
                ->addMetric(new MetricDefinition('total_points', 'Points', 'sum', 'total_points', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('redeemed', 'Redeemed', 'sum', 'points_redeemed', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('retention_rate', 'Retention %', 'avg', 'retention_pct', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('member_level', 'Level', 'member_level', type:'string')),

            (new ReportDefinition('sales.profit_analysis', 'Profit Analysis',
                type:'summary', chartType:'bar', features:['sales'], permissions:['manage_finance']))
                ->addMetric(new MetricDefinition('revenue', 'Revenue', 'sum', 'grand_total', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('cost', 'Cost', 'sum', 'cost_total', format:'currency', color:'danger'))
                ->addMetric(new MetricDefinition('profit', 'Profit', 'sum', 'profit', format:'currency', color:'primary'))
                ->addDimension(new DimensionDefinition('category', 'Category', 'product_category', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            (new ReportDefinition('sales.margin_analysis', 'Margin Analysis',
                type:'summary', chartType:'table', features:['sales'], permissions:['manage_finance']))
                ->addMetric(new MetricDefinition('avg_margin', 'Avg Margin %', 'avg', 'margin_pct', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('high_margin_count', 'High Margin (>30%)', 'count', 'high_margin', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('low_margin_count', 'Low Margin (<10%)', 'count', 'low_margin', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('product_name', 'Product', 'product_name', type:'string')),

            (new ReportDefinition('sales.tax', 'Sales Tax Report',
                type:'summary', chartType:'table', features:['sales']))
                ->addMetric(new MetricDefinition('dpp', 'DPP', 'sum', 'dpp', format:'currency'))
                ->addMetric(new MetricDefinition('ppn', 'PPN', 'sum', 'tax_amount', format:'currency', color:'warning'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'created_at', type:'date'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            (new ReportDefinition('sales.returns_analysis', 'Returns Analysis',
                type:'summary', chartType:'table', features:['sales']))
                ->addMetric(new MetricDefinition('return_count', 'Returns', 'count', 'id', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('return_amount', 'Amount', 'sum', 'return_amount', format:'currency', color:'warning'))
                ->addMetric(new MetricDefinition('return_rate', 'Rate %', 'expression', 'return_count / total_sales * 100', format:'number'))
                ->addDimension(new DimensionDefinition('reason', 'Reason', 'reason', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),
        ];
    }
}
