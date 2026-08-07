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
 * FinanceDefinitions — ALL Enterprise definitions for Finance & Accounting.
 * 
 * Covers: COA, Journal Entries, General Ledger, Trial Balance,
 * Profit & Loss, Balance Sheet, Cash Flow, AR, AP, Cash & Bank,
 * Tax, Budget, Multi-Currency, Automatic Accounting.
 * 
 * MODUL ERP KELIMA — ENTERPRISE FINANCE & ACCOUNTING
 */
class FinanceDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // CHART OF ACCOUNTS (COA) — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function coaTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'finance.coa.index',
            title: 'Chart of Accounts',
            modelClass: \App\Models\Tenant\ChartOfAccount::class,
            defaultSort: ['account_code' => 'asc'],
            perPage: 100,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['finance'],
        ))
            ->addColumns([
                new ColumnDefinition('account_code',    'Kode Akun',   type:'text',    sortable:true, bold:true, width:'120px', order:1),
                new ColumnDefinition('account_name',    'Nama Akun',   type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('parent_code',     'Parent',      type:'text',    sortable:true, width:'100px', order:3),
                new ColumnDefinition('level',           'Level',       type:'number',  sortable:true, width:'60px', align:'center', order:4),
                new ColumnDefinition('account_type',    'Tipe Akun',   type:'badge',   sortable:true, filterable:true, width:'110px', order:5),
                new ColumnDefinition('normal_balance',  'Normal Bal.', type:'badge',   sortable:true, width:'90px', align:'center', order:6),
                new ColumnDefinition('current_balance', 'Saldo',       type:'currency', sortable:true, align:'right', width:'130px', order:7),
                new ColumnDefinition('status',          'Status',      type:'badge',   sortable:true, filterable:true, width:'80px', align:'center', order:8),
                new ColumnDefinition('branch',          'Cabang',      type:'text',    sortable:true, width:'100px', order:9),
                new ColumnDefinition('notes',           'Catatan',     type:'text',    order:10),
                new ColumnDefinition('actions',         '',            type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('account_type', 'Tipe Akun', type:'select', quick:true, options:[
                ['value'=>'asset','label'=>'Asset'],
                ['value'=>'liability','label'=>'Liability'],
                ['value'=>'equity','label'=>'Equity'],
                ['value'=>'revenue','label'=>'Revenue'],
                ['value'=>'cogs','label'=>'COGS'],
                ['value'=>'expense','label'=>'Expense'],
                ['value'=>'other_income','label'=>'Other Income'],
                ['value'=>'other_expense','label'=>'Other Expense'],
            ], order:1))
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[['value'=>'active','label'=>'Active'],['value'=>'inactive','label'=>'Inactive']], order:2))
            ->addFilter(new FilterDefinition('level', 'Level', type:'select', options:[['value'=>'1','label'=>'1 - Header'],['value'=>'2','label'=>'2 - Sub'],['value'=>'3','label'=>'3 - Detail'],['value'=>'4','label'=>'4 - Item']], order:3))
            ->addFilter(new FilterDefinition('branch', 'Cabang', type:'select', order:4))
            ->addBulkAction(new BulkAction('activate', 'Activate', variant:'default'))
            ->addBulkAction(new BulkAction('deactivate', 'Deactivate', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // FINANCE WORKSPACE (16 tabs)
    // ═══════════════════════════════════════════════════════════

    public static function workspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'finance',
            title: 'Finance & Accounting',
            icon: '💰',
            tabs: [
                ['id' => 'overview',          'label' => 'Overview',              'icon' => '📊'],
                ['id' => 'journal_entries',   'label' => 'Journal Entries',       'icon' => '📝'],
                ['id' => 'ledger',            'label' => 'General Ledger',        'icon' => '📒'],
                ['id' => 'trial_balance',     'label' => 'Trial Balance',         'icon' => '⚖️'],
                ['id' => 'profit_loss',       'label' => 'Profit & Loss',         'icon' => '📈'],
                ['id' => 'balance_sheet',     'label' => 'Balance Sheet',         'icon' => '🏦'],
                ['id' => 'cash_flow',         'label' => 'Cash Flow',             'icon' => '💵'],
                ['id' => 'accounts_receivable','label' => 'Accounts Receivable',  'icon' => '📥'],
                ['id' => 'accounts_payable',  'label' => 'Accounts Payable',      'icon' => '📤'],
                ['id' => 'bank',              'label' => 'Cash & Bank',           'icon' => '🏧'],
                ['id' => 'tax',               'label' => 'Tax',                   'icon' => '🧾'],
                ['id' => 'budget',            'label' => 'Budget',                'icon' => '🎯'],
                ['id' => 'timeline',          'label' => 'Timeline',              'icon' => '🕐'],
                ['id' => 'documents',         'label' => 'Documents',             'icon' => '📄'],
                ['id' => 'activity',          'label' => 'Activity Log',          'icon' => '📊'],
            ],
            actions: [
                ['id' => 'new_journal',       'label' => 'New Journal',        'roles' => ['owner','finance','accounting']],
                ['id' => 'new_coa',           'label' => 'New Account',        'roles' => ['owner','finance','accounting']],
                ['id' => 'new_invoice',       'label' => 'New Invoice',        'roles' => ['owner','finance','accounting']],
                ['id' => 'new_payment',       'label' => 'Record Payment',     'roles' => ['owner','finance','accounting','cashier']],
                ['id' => 'new_expense',       'label' => 'Record Expense',     'roles' => ['owner','finance','accounting']],
                ['id' => 'bank_reconcile',    'label' => 'Bank Reconcile',     'roles' => ['owner','finance','accounting']],
                ['id' => 'close_period',      'label' => 'Close Period',       'roles' => ['owner','finance']],
                ['id' => 'export',            'label' => 'Export',             'roles' => ['owner','finance','accounting','manager']],
            ],
            sidebarWidgets: [
                ['id' => 'finance_summary',     'component' => 'FinanceSummary',   'priority' => 10],
                ['id' => 'quick_journal',       'component' => 'QuickJournal',     'priority' => 20],
                ['id' => 'pending_approvals',   'component' => 'PendingApprovals', 'priority' => 30],
                ['id' => 'alerts',              'component' => 'FinanceAlerts',    'priority' => 40],
            ],
            features: ['finance'],
            permissions: ['manage_finance'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // COA FORM — Create/Edit Account
    // ═══════════════════════════════════════════════════════════

    public static function coaForm(): FormDefinition
    {
        return (new FormDefinition(
            id: 'finance.coa.create',
            title: 'Chart of Account',
            method: 'POST',
            endpoint: '/finance/coa',
            features: ['finance'],
        ))
            ->addSection(new FormSection(id:'basic',   label:'Informasi Akun', icon:'📋', cols:2))
            ->addSection(new FormSection(id:'balance',  label:'Saldo',          icon:'💰', cols:2))
            ->addSection(new FormSection(id:'settings', label:'Pengaturan',     icon:'⚙️', cols:1))
            ->addFields([
                new FormField('account_code',   type:'text',     label:'Kode Akun',          required:true, section:'basic', cols:6, maxlength:20),
                new FormField('account_name',   type:'text',     label:'Nama Akun',          required:true, section:'basic', cols:6),
                new FormField('parent_code',    type:'text',     label:'Kode Parent',        section:'basic', cols:6),
                new FormField('account_type',   type:'select',   label:'Tipe Akun',          required:true, section:'basic', cols:6,
                    options:[
                        ['value'=>'asset','label'=>'Asset'],
                        ['value'=>'liability','label'=>'Liability'],
                        ['value'=>'equity','label'=>'Equity'],
                        ['value'=>'revenue','label'=>'Revenue'],
                        ['value'=>'cogs','label'=>'Cost of Goods Sold'],
                        ['value'=>'expense','label'=>'Expense'],
                        ['value'=>'other_income','label'=>'Other Income'],
                        ['value'=>'other_expense','label'=>'Other Expense'],
                    ]),
                new FormField('level',          type:'select',   label:'Level',              required:true, section:'basic', cols:6,
                    options:[['value'=>'1','label'=>'1 - Header'],['value'=>'2','label'=>'2 - Sub'],['value'=>'3','label'=>'3 - Detail'],['value'=>'4','label'=>'4 - Item']]),
                new FormField('normal_balance', type:'select',   label:'Normal Balance',     required:true, section:'basic', cols:6,
                    options:[['value'=>'debit','label'=>'Debit'],['value'=>'credit','label'=>'Credit']]),
                new FormField('opening_balance',type:'currency', label:'Saldo Awal',         section:'balance', cols:6),
                new FormField('current_balance',type:'currency', label:'Saldo Saat Ini',     section:'balance', cols:6, disabled:true),
                new FormField('branch_id',      type:'select',   label:'Cabang',             section:'settings', cols:6),
                new FormField('currency_code',  type:'select',   label:'Mata Uang',          section:'settings', cols:6,
                    options:[['value'=>'IDR','label'=>'IDR - Rupiah'],['value'=>'USD','label'=>'USD - Dollar']]),
                new FormField('status',         type:'select',   label:'Status',             required:true, section:'settings', cols:6,
                    options:[['value'=>'active','label'=>'Active'],['value'=>'inactive','label'=>'Inactive']]),
                new FormField('notes',          type:'textarea', label:'Catatan',            section:'settings', cols:12),
            ])
            ->addAction(new FormAction('save', 'Simpan', variant:'primary', shortcut:'Ctrl+S'))
            ->addAction(new FormAction('save_and_new', 'Simpan & Baru', variant:'secondary'));
    }

    // ═══════════════════════════════════════════════════════════
    // JOURNAL ENTRY — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function journalTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'finance.journal.index',
            title: 'Journal Entries',
            modelClass: \App\Models\Tenant\JournalEntry::class,
            defaultSort: ['journal_date' => 'desc', 'created_at' => 'desc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['finance'],
        ))
            ->addColumns([
                new ColumnDefinition('journal_number',   'No. Jurnal',  type:'text',    sortable:true, bold:true, width:'130px', order:1),
                new ColumnDefinition('journal_date',     'Tanggal',     type:'date',    sortable:true, width:'100px', order:2),
                new ColumnDefinition('journal_type',     'Tipe',        type:'badge',   sortable:true, filterable:true, width:'100px', order:3),
                new ColumnDefinition('reference',        'Referensi',   type:'text',    sortable:true, width:'130px', order:4),
                new ColumnDefinition('description',      'Deskripsi',   type:'text',    searchable:true, order:5),
                new ColumnDefinition('total_debit',      'Total Debit', type:'currency', sortable:true, align:'right', width:'130px', order:6),
                new ColumnDefinition('total_credit',     'Total Kredit',type:'currency', sortable:true, align:'right', width:'130px', order:7),
                new ColumnDefinition('status',           'Status',      type:'badge',   sortable:true, filterable:true, width:'90px', align:'center', order:8),
                new ColumnDefinition('posted_by',        'Posted By',   type:'text',    width:'100px', order:9),
                new ColumnDefinition('created_at',       'Created',     type:'datetime', sortable:true, width:'130px', order:10),
                new ColumnDefinition('actions',          '',            type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('journal_type', 'Tipe', type:'select', quick:true, options:[
                ['value'=>'manual','label'=>'Manual'],
                ['value'=>'automatic','label'=>'Automatic'],
                ['value'=>'recurring','label'=>'Recurring'],
                ['value'=>'adjustment','label'=>'Adjustment'],
                ['value'=>'closing','label'=>'Closing'],
                ['value'=>'reversing','label'=>'Reversing'],
                ['value'=>'opening','label'=>'Opening Balance'],
            ], order:1))
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[['value'=>'draft','label'=>'Draft'],['value'=>'posted','label'=>'Posted'],['value'=>'void','label'=>'Void']], order:2))
            ->addFilter(new FilterDefinition('journal_date', 'Tanggal', type:'date_range', quick:true, order:3))
            ->addBulkAction(new BulkAction('post', 'Post', variant:'primary'))
            ->addBulkAction(new BulkAction('void', 'Void', variant:'danger', confirm:true, permissions:['manage_finance']))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'))
            ->addBulkAction(new BulkAction('print', 'Print', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // JOURNAL ENTRY — Form
    // ═══════════════════════════════════════════════════════════

    public static function journalForm(): FormDefinition
    {
        return (new FormDefinition(
            id: 'finance.journal.create',
            title: 'Create Journal Entry',
            method: 'POST',
            endpoint: '/finance/journals',
            features: ['finance'],
        ))
            ->addSection(new FormSection(id:'header',  label:'Header',        icon:'📋', cols:2))
            ->addSection(new FormSection(id:'entries', label:'Journal Lines', icon:'📝', cols:1))
            ->addFields([
                new FormField('journal_date',     type:'date',      label:'Tanggal',           required:true, section:'header', cols:4),
                new FormField('journal_type',     type:'select',    label:'Tipe',              required:true, section:'header', cols:4,
                    options:[
                        ['value'=>'manual','label'=>'Manual'],
                        ['value'=>'adjustment','label'=>'Adjustment'],
                        ['value'=>'opening','label'=>'Opening Balance'],
                    ]),
                new FormField('reference',        type:'text',      label:'Referensi',         section:'header', cols:4),
                new FormField('description',      type:'textarea',  label:'Deskripsi',         required:true, section:'header', cols:12),
                new FormField('branch_id',        type:'select',    label:'Cabang',            section:'header', cols:4),
                new FormField('source_module',    type:'select',    label:'Source Module',     section:'header', cols:4,
                    options:[['value'=>'manual','label'=>'Manual'],['value'=>'service','label'=>'Service'],['value'=>'sales','label'=>'Sales'],['value'=>'purchasing','label'=>'Purchasing'],['value'=>'inventory','label'=>'Inventory']]),
                new FormField('source_id',        type:'text',      label:'Source ID',         section:'header', cols:4),
                // Journal lines handled as dynamic repeater via Form Engine
                new FormField('lines',            type:'repeater',  label:'Journal Lines',     required:true, section:'entries', cols:12, 
                    fields:[
                        ['name'=>'account_code','type'=>'text','label'=>'Kode Akun','required'=>true,'cols'=>3],
                        ['name'=>'account_name','type'=>'text','label'=>'Nama Akun','cols'=>3],
                        ['name'=>'debit','type'=>'currency','label'=>'Debit','cols'=>3],
                        ['name'=>'credit','type'=>'currency','label'=>'Credit','cols'=>2],
                        ['name'=>'description','type'=>'text','label'=>'Deskripsi','cols'=>1],
                    ]),
            ])
            ->addAction(new FormAction('save_draft', 'Save Draft', variant:'outline'))
            ->addAction(new FormAction('post', 'Post Journal', variant:'primary', shortcut:'Ctrl+S'));
    }

    // ═══════════════════════════════════════════════════════════
    // AR AGING — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function arAgingTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'finance.ar.aging',
            title: 'Accounts Receivable Aging',
            modelClass: \App\Models\Tenant\Receivable::class,
            defaultSort: ['due_date' => 'asc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['finance'],
        ))
            ->addColumns([
                new ColumnDefinition('invoice_number',    'Invoice No.', type:'text',  sortable:true, bold:true, width:'130px', order:1),
                new ColumnDefinition('customer_name',     'Customer',    type:'text',  sortable:true, searchable:true, order:2),
                new ColumnDefinition('invoice_date',      'Tgl Invoice', type:'date',  sortable:true, width:'100px', order:3),
                new ColumnDefinition('due_date',          'Due Date',    type:'date',  sortable:true, width:'100px', order:4),
                new ColumnDefinition('total_amount',      'Total',       type:'currency', sortable:true, align:'right', width:'130px', order:5),
                new ColumnDefinition('paid_amount',       'Dibayar',     type:'currency', sortable:true, align:'right', width:'130px', order:6),
                new ColumnDefinition('outstanding',       'Outstanding', type:'currency', sortable:true, align:'right', bold:true, width:'130px', aggregate:true, aggregateType:'sum', order:7),
                new ColumnDefinition('aging_bucket',      'Aging',       type:'badge',  sortable:true, width:'100px', order:8),
                new ColumnDefinition('days_overdue',      'Days Overdue',type:'number', sortable:true, width:'90px', align:'center', order:9),
                new ColumnDefinition('collection_status', 'Status',      type:'badge',  sortable:true, filterable:true, width:'100px', order:10),
                new ColumnDefinition('actions',           '',            type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('aging_bucket', 'Aging', type:'select', quick:true, options:[
                ['value'=>'current','label'=>'Current'],
                ['value'=>'1_30','label'=>'1-30 Days'],
                ['value'=>'31_60','label'=>'31-60 Days'],
                ['value'=>'61_90','label'=>'61-90 Days'],
                ['value'=>'90_plus','label'=>'90+ Days'],
            ], order:1))
            ->addFilter(new FilterDefinition('collection_status', 'Status', type:'select', quick:true, options:[
                ['value'=>'open','label'=>'Open'],
                ['value'=>'partial','label'=>'Partial'],
                ['value'=>'collection','label'=>'In Collection'],
                ['value'=>'paid','label'=>'Paid'],
            ], order:2))
            ->addFilter(new FilterDefinition('customer_id', 'Customer', type:'select', order:3))
            ->addFilter(new FilterDefinition('due_date', 'Due Date', type:'date_range', order:4))
            ->addBulkAction(new BulkAction('send_reminder', 'Send Reminder', variant:'default'))
            ->addBulkAction(new BulkAction('mark_collection', 'Mark Collection', variant:'warning'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AP AGING — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function apAgingTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'finance.ap.aging',
            title: 'Accounts Payable Aging',
            modelClass: \App\Models\Tenant\Payable::class,
            defaultSort: ['due_date' => 'asc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['finance'],
        ))
            ->addColumns([
                new ColumnDefinition('invoice_number',    'Invoice No.',  type:'text',  sortable:true, bold:true, width:'130px', order:1),
                new ColumnDefinition('supplier_name',     'Supplier',     type:'text',  sortable:true, searchable:true, order:2),
                new ColumnDefinition('invoice_date',      'Tgl Invoice',  type:'date',  sortable:true, width:'100px', order:3),
                new ColumnDefinition('due_date',          'Due Date',     type:'date',  sortable:true, width:'100px', order:4),
                new ColumnDefinition('total_amount',      'Total',        type:'currency', sortable:true, align:'right', width:'130px', order:5),
                new ColumnDefinition('paid_amount',       'Dibayar',      type:'currency', sortable:true, align:'right', width:'130px', order:6),
                new ColumnDefinition('outstanding',       'Outstanding',  type:'currency', sortable:true, align:'right', bold:true, width:'130px', aggregate:true, aggregateType:'sum', order:7),
                new ColumnDefinition('days_until_due',    'Days Until',   type:'number', sortable:true, width:'90px', align:'center', order:8),
                new ColumnDefinition('payment_status',    'Status',       type:'badge',  sortable:true, filterable:true, width:'100px', order:9),
                new ColumnDefinition('actions',           '',             type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('payment_status', 'Status', type:'select', quick:true, options:[
                ['value'=>'pending','label'=>'Pending'],
                ['value'=>'partial','label'=>'Partial'],
                ['value'=>'paid','label'=>'Paid'],
                ['value'=>'overdue','label'=>'Overdue'],
            ], order:1))
            ->addFilter(new FilterDefinition('supplier_id', 'Supplier', type:'select', order:2))
            ->addFilter(new FilterDefinition('due_date', 'Due Date', type:'date_range', order:3))
            ->addBulkAction(new BulkAction('record_payment', 'Record Payment', variant:'primary'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // CASH & BANK — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function cashBankTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'finance.bank.index',
            title: 'Cash & Bank Accounts',
            modelClass: \App\Models\Tenant\BankAccount::class,
            defaultSort: ['account_name' => 'asc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['finance'],
        ))
            ->addColumns([
                new ColumnDefinition('account_name',      'Nama Rekening', type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('account_number',    'No. Rekening',  type:'text',    sortable:true, width:'140px', order:2),
                new ColumnDefinition('bank_name',         'Bank',          type:'text',    sortable:true, width:'100px', order:3),
                new ColumnDefinition('account_type',      'Tipe',          type:'badge',   sortable:true, filterable:true, width:'90px', order:4),
                new ColumnDefinition('currency_code',     'Currency',      type:'text',    width:'70px', align:'center', order:5),
                new ColumnDefinition('current_balance',   'Saldo',         type:'currency', sortable:true, align:'right', bold:true, width:'140px', order:6),
                new ColumnDefinition('last_reconciled_at','Last Reconciled', type:'date',  sortable:true, width:'120px', order:7),
                new ColumnDefinition('last_reconciled_balance','Reconciled Saldo', type:'currency', sortable:true, align:'right', width:'140px', order:8),
                new ColumnDefinition('status',            'Status',        type:'badge',   sortable:true, width:'80px', align:'center', order:9),
                new ColumnDefinition('actions',           '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('account_type', 'Tipe', type:'select', quick:true, options:[
                ['value'=>'cash','label'=>'Cash'],
                ['value'=>'bank','label'=>'Bank'],
                ['value'=>'savings','label'=>'Savings'],['value'=>'deposit','label'=>'Deposit'],
            ], order:1))
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[['value'=>'active','label'=>'Active'],['value'=>'inactive','label'=>'Inactive']], order:2))
            ->addBulkAction(new BulkAction('reconcile', 'Reconcile', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // TAX — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function taxTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'finance.tax.index',
            title: 'Tax Management',
            modelClass: \App\Models\Tenant\Tax::class,
            defaultSort: ['tax_period' => 'desc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['finance'],
        ))
            ->addColumns([
                new ColumnDefinition('tax_period',        'Period',       type:'text',    sortable:true, bold:true, width:'100px', order:1),
                new ColumnDefinition('tax_type',          'Tipe Pajak',   type:'badge',   sortable:true, filterable:true, width:'90px', order:2),
                new ColumnDefinition('tax_base',          'DPP',          type:'currency', sortable:true, align:'right', width:'130px', order:3),
                new ColumnDefinition('tax_amount',        'Pajak',        type:'currency', sortable:true, align:'right', bold:true, width:'130px', aggregate:true, aggregateType:'sum', order:4),
                new ColumnDefinition('tax_inclusive',     'Incl/Excl',    type:'badge',   width:'80px', align:'center', order:5),
                new ColumnDefinition('tax_group',         'Group',        type:'text',    filterable:true, width:'80px', order:6),
                new ColumnDefinition('reference',         'Referensi',    type:'text',    width:'130px', order:7),
                new ColumnDefinition('filing_status',     'Filing',       type:'badge',   filterable:true, width:'100px', order:8),
                new ColumnDefinition('created_at',        'Created',      type:'datetime', sortable:true, width:'130px', order:9),
                new ColumnDefinition('actions',           '',             type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('tax_type', 'Tipe', type:'select', quick:true, options:[
                ['value'=>'ppn','label'=>'PPN'],
                ['value'=>'pph21','label'=>'PPh 21'],
                ['value'=>'pph23','label'=>'PPh 23'],
                ['value'=>'pph25','label'=>'PPh 25'],
                ['value'=>'pph29','label'=>'PPh 29'],
            ], order:1))
            ->addFilter(new FilterDefinition('filing_status', 'Filing', type:'select', quick:true, options:[
                ['value'=>'pending','label'=>'Pending'],
                ['value'=>'filed','label'=>'Filed'],
                ['value'=>'paid','label'=>'Paid'],
            ], order:2))
            ->addFilter(new FilterDefinition('tax_period', 'Period', type:'date_range', order:3))
            ->addBulkAction(new BulkAction('file_tax', 'File Tax', variant:'primary'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // BUDGET — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function budgetTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'finance.budget.index',
            title: 'Budget Management',
            modelClass: \App\Models\Tenant\Budget::class,
            defaultSort: ['fiscal_year' => 'desc', 'account_code' => 'asc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['finance'],
        ))
            ->addColumns([
                new ColumnDefinition('fiscal_year',       'Tahun',        type:'text',    sortable:true, bold:true, width:'80px', align:'center', order:1),
                new ColumnDefinition('account_code',      'Kode Akun',    type:'text',    sortable:true, width:'110px', order:2),
                new ColumnDefinition('account_name',      'Nama Akun',    type:'text',    sortable:true, searchable:true, order:3),
                new ColumnDefinition('budget_amount',     'Budget',       type:'currency', sortable:true, align:'right', width:'140px', order:4),
                new ColumnDefinition('actual_amount',     'Actual',       type:'currency', sortable:true, align:'right', width:'140px', aggregate:true, aggregateType:'sum', order:5),
                new ColumnDefinition('variance',          'Variance',     type:'currency', sortable:true, align:'right', width:'140px', order:6),
                new ColumnDefinition('variance_pct',      'Var %',        type:'number',  sortable:true, width:'80px', align:'center', order:7),
                new ColumnDefinition('branch',            'Cabang',       type:'text',    sortable:true, width:'100px', order:8),
                new ColumnDefinition('department',        'Department',   type:'text',    sortable:true, width:'110px', order:9),
                new ColumnDefinition('status',            'Status',       type:'badge',   sortable:true, filterable:true, width:'90px', align:'center', order:10),
                new ColumnDefinition('actions',           '',             type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('fiscal_year', 'Year', type:'select', quick:true, order:1))
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'draft','label'=>'Draft'],
                ['value'=>'approved','label'=>'Approved'],
                ['value'=>'revised','label'=>'Revised'],
                ['value'=>'closed','label'=>'Closed'],
            ], order:2))
            ->addFilter(new FilterDefinition('department', 'Department', type:'select', order:3))
            ->addFilter(new FilterDefinition('branch', 'Cabang', type:'select', order:4))
            ->addBulkAction(new BulkAction('approve', 'Approve', variant:'primary'))
            ->addBulkAction(new BulkAction('revise', 'Revise', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES — 10 Rules
    // ═══════════════════════════════════════════════════════════

    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            // 1. Invoice Created → notify customer
            (new AutomationDefinition('finance.invoice_created', 'Invoice Created',
                trigger: TriggerType::RECORD_CREATED, module: 'finance'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, [
                    'message' => '🧾 Invoice #{{subject.invoice_number}} telah dibuat. Total: Rp {{format subject.total_amount}}.',
                ], conditionField: 'customer.whatsapp'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                    'message' => 'Invoice #{{subject.invoice_number}} created.',
                ])),

            // 2. Invoice Overdue → reminder
            (new AutomationDefinition('finance.invoice_overdue', 'Invoice Overdue',
                trigger: TriggerType::DATE_REACHED, module: 'finance'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, [
                    'message' => '⚠️ Invoice #{{subject.invoice_number}} overdue {{subject.days_overdue}} hari. Mohon segera diselesaikan.',
                ]))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Follow-up Overdue Invoice #{{subject.invoice_number}}',
                    'assignee_role' => 'finance',
                ], delaySeconds: 3600)),

            // 3. Payment Received → auto journal
            (new AutomationDefinition('finance.payment_received', 'Payment Received',
                trigger: TriggerType::RECORD_UPDATED, module: 'finance'))
                ->addStep(new AutomationStep(ActionType::CREATE_JOURNAL, [
                    'template' => 'payment_receipt',
                ])),

            // 4. Payment Due → reminder
            (new AutomationDefinition('finance.payment_due', 'Supplier Payment Due',
                trigger: TriggerType::DATE_REACHED, module: 'finance'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Bayar Invoice #{{subject.invoice_number}} ke {{subject.supplier_name}}',
                    'assignee_role' => 'finance',
                ], delaySeconds: 1800)),

            // 5. Journal Posted → audit log
            (new AutomationDefinition('finance.journal_posted', 'Journal Posted',
                trigger: TriggerType::RECORD_UPDATED, module: 'finance'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                    'message' => '📋 Journal #{{subject.journal_number}} posted by {{user.name}}.',
                ])),

            // 6. Budget Exceeded → alert
            (new AutomationDefinition('finance.budget_exceeded', 'Budget Exceeded',
                trigger: TriggerType::RECORD_UPDATED, module: 'finance'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '⚠️ Budget Exceeded',
                    'body' => 'Budget {{subject.account_name}} exceeded {{subject.variance_pct}}%.',
                    'roles' => ['owner', 'finance'],
                ])),

            // 7. Bank Reconciliation Required
            (new AutomationDefinition('finance.bank_reconcile_required', 'Bank Reconciliation Required',
                trigger: TriggerType::DATE_REACHED, module: 'finance'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Bank Reconciliation: {{subject.account_name}}',
                    'assignee_role' => 'finance',
                ])),

            // 8. Month End Closing Reminder
            (new AutomationDefinition('finance.month_end', 'Month End Closing',
                trigger: TriggerType::DATE_REACHED, module: 'finance'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Month End Closing — {{date.now}}',
                    'assignee_role' => 'finance',
                ]))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '📅 Month End Closing',
                    'body' => 'Jangan lupa closing buku bulan ini.',
                    'roles' => ['owner', 'finance', 'accounting'],
                ])),

            // 9. Year End Closing Reminder
            (new AutomationDefinition('finance.year_end', 'Year End Closing',
                trigger: TriggerType::DATE_REACHED, module: 'finance'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Year End Closing — {{date.now}}',
                    'assignee_role' => 'finance',
                ]))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, [
                    'message' => '📅 Year End Closing! Siapkan laporan keuangan tahunan.',
                ])),

            // 10. Cash Balance Below Minimum
            (new AutomationDefinition('finance.cash_below_min', 'Cash Below Minimum',
                trigger: TriggerType::RECORD_UPDATED, module: 'finance'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '💰 Cash Balance Low',
                    'body' => 'Cash {{subject.account_name}} below minimum: Rp {{format subject.current_balance}}.',
                    'roles' => ['owner', 'finance'],
                ])),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // REPORTING DEFINITIONS — 13 Reports
    // ═══════════════════════════════════════════════════════════

    /** @return ReportDefinition[] */
    public static function reports(): array
    {
        return [
            // 1. General Ledger
            (new ReportDefinition('finance.general_ledger', 'General Ledger',
                type:'summary', chartType:'table', features:['finance']))
                ->addMetric(new MetricDefinition('debit', 'Debit', 'sum', 'debit', format:'currency'))
                ->addMetric(new MetricDefinition('credit', 'Credit', 'sum', 'credit', format:'currency'))
                ->addMetric(new MetricDefinition('balance', 'Balance', 'last', 'balance', format:'currency', color:'primary'))
                ->addDimension(new DimensionDefinition('account_code', 'Account', 'account_code', type:'string'))
                ->addDimension(new DimensionDefinition('account_name', 'Name', 'account_name', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range'))
                ->addFilter(new ReportFilter('account_code', 'Account', 'select')),

            // 2. Trial Balance
            (new ReportDefinition('finance.trial_balance', 'Trial Balance',
                type:'summary', chartType:'table', features:['finance']))
                ->addMetric(new MetricDefinition('debit', 'Debit', 'sum', 'debit', format:'currency'))
                ->addMetric(new MetricDefinition('credit', 'Credit', 'sum', 'credit', format:'currency'))
                ->addMetric(new MetricDefinition('net', 'Net', 'expression', 'debit - credit', format:'currency', color:'primary'))
                ->addDimension(new DimensionDefinition('account_code', 'Account', 'account_code', type:'string'))
                ->addFilter(new ReportFilter('as_of_date', 'As Of', 'date')),

            // 3. Profit & Loss
            (new ReportDefinition('finance.profit_loss', 'Profit & Loss',
                type:'summary', chartType:'bar', features:['finance']))
                ->addMetric(new MetricDefinition('revenue', 'Revenue', 'sum', 'revenue', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('cogs', 'COGS', 'sum', 'cogs', format:'currency', color:'danger'))
                ->addMetric(new MetricDefinition('gross_profit', 'Gross Profit', 'expression', 'revenue - cogs', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('expenses', 'Expenses', 'sum', 'expenses', format:'currency', color:'danger'))
                ->addMetric(new MetricDefinition('net_profit', 'Net Profit', 'expression', 'gross_profit - expenses', format:'currency', color:'success'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'entry_date', type:'date'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range'))
                ->addFilter(new ReportFilter('branch', 'Cabang', 'select')),

            // 4. Balance Sheet
            (new ReportDefinition('finance.balance_sheet', 'Balance Sheet',
                type:'summary', chartType:'table', features:['finance'], permissions:['manage_finance']))
                ->addMetric(new MetricDefinition('total_assets', 'Total Assets', 'sum', 'assets', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('total_liabilities', 'Total Liabilities', 'sum', 'liabilities', format:'currency', color:'danger'))
                ->addMetric(new MetricDefinition('total_equity', 'Total Equity', 'sum', 'equity', format:'currency', color:'success'))
                ->addDimension(new DimensionDefinition('category', 'Category', 'account_type', type:'string'))
                ->addFilter(new ReportFilter('as_of_date', 'As Of', 'date')),

            // 5. Cash Flow
            (new ReportDefinition('finance.cash_flow', 'Cash Flow',
                type:'trend', chartType:'waterfall', features:['finance']))
                ->addMetric(new MetricDefinition('operating', 'Operating', 'sum', 'operating_cf', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('investing', 'Investing', 'sum', 'investing_cf', format:'currency', color:'info'))
                ->addMetric(new MetricDefinition('financing', 'Financing', 'sum', 'financing_cf', format:'currency', color:'warning'))
                ->addMetric(new MetricDefinition('net_cf', 'Net Cash Flow', 'expression', 'operating + investing + financing', format:'currency', color:'success'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'entry_date', type:'date'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            // 6. AR Aging
            (new ReportDefinition('finance.ar_aging', 'AR Aging',
                type:'summary', chartType:'bar', features:['finance']))
                ->addMetric(new MetricDefinition('current', 'Current', 'sum', 'current', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('days_1_30', '1-30 Days', 'sum', 'days_1_30', format:'currency', color:'info'))
                ->addMetric(new MetricDefinition('days_31_60', '31-60 Days', 'sum', 'days_31_60', format:'currency', color:'warning'))
                ->addMetric(new MetricDefinition('days_61_90', '61-90 Days', 'sum', 'days_61_90', format:'currency', color:'warning'))
                ->addMetric(new MetricDefinition('days_90_plus', '90+ Days', 'sum', 'days_90_plus', format:'currency', color:'danger'))
                ->addDimension(new DimensionDefinition('customer_name', 'Customer', 'customer_name', type:'string'))
                ->addFilter(new ReportFilter('as_of_date', 'As Of', 'date')),

            // 7. AP Aging
            (new ReportDefinition('finance.ap_aging', 'AP Aging',
                type:'summary', chartType:'bar', features:['finance']))
                ->addMetric(new MetricDefinition('current', 'Current', 'sum', 'current', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('days_1_30', '1-30 Days', 'sum', 'days_1_30', format:'currency', color:'info'))
                ->addMetric(new MetricDefinition('days_31_60', '31-60 Days', 'sum', 'days_31_60', format:'currency', color:'warning'))
                ->addMetric(new MetricDefinition('days_61_90', '61-90 Days', 'sum', 'days_61_90', format:'currency', color:'danger'))
                ->addDimension(new DimensionDefinition('supplier_name', 'Supplier', 'supplier_name', type:'string'))
                ->addFilter(new ReportFilter('as_of_date', 'As Of', 'date')),

            // 8. Tax Summary
            (new ReportDefinition('finance.tax_summary', 'Tax Summary',
                type:'summary', chartType:'table', features:['finance']))
                ->addMetric(new MetricDefinition('tax_base', 'DPP', 'sum', 'tax_base', format:'currency'))
                ->addMetric(new MetricDefinition('tax_amount', 'Tax', 'sum', 'tax_amount', format:'currency', color:'warning'))
                ->addDimension(new DimensionDefinition('tax_type', 'Type', 'tax_type', type:'string'))
                ->addDimension(new DimensionDefinition('period', 'Period', 'tax_period', type:'date'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            // 9. Expense Analysis
            (new ReportDefinition('finance.expense_analysis', 'Expense Analysis',
                type:'summary', chartType:'treemap', features:['finance']))
                ->addMetric(new MetricDefinition('total', 'Total', 'sum', 'amount', format:'currency', color:'danger'))
                ->addMetric(new MetricDefinition('budget', 'Budget', 'sum', 'budget_amount', format:'currency'))
                ->addMetric(new MetricDefinition('variance', 'Variance', 'expression', 'budget - total', format:'currency'))
                ->addDimension(new DimensionDefinition('account_name', 'Account', 'account_name', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range'))
                ->addFilter(new ReportFilter('branch', 'Cabang', 'select')),

            // 10. Revenue Analysis
            (new ReportDefinition('finance.revenue_analysis', 'Revenue Analysis',
                type:'trend', chartType:'area', features:['finance']))
                ->addMetric(new MetricDefinition('revenue', 'Revenue', 'sum', 'revenue', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('cogs', 'COGS', 'sum', 'cogs', format:'currency', color:'danger'))
                ->addMetric(new MetricDefinition('margin', 'Margin %', 'expression', '(revenue - cogs) / revenue * 100', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'entry_date', type:'date'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range'))
                ->addFilter(new ReportFilter('branch', 'Cabang', 'select')),

            // 11. Budget vs Actual
            (new ReportDefinition('finance.budget_vs_actual', 'Budget vs Actual',
                type:'summary', chartType:'bar', features:['finance']))
                ->addMetric(new MetricDefinition('budget', 'Budget', 'sum', 'budget_amount', format:'currency', color:'info'))
                ->addMetric(new MetricDefinition('actual', 'Actual', 'sum', 'actual_amount', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('variance', 'Variance', 'expression', 'budget - actual', format:'currency', color:'danger'))
                ->addDimension(new DimensionDefinition('account_name', 'Account', 'account_name', type:'string'))
                ->addFilter(new ReportFilter('fiscal_year', 'Year', 'select'))
                ->addFilter(new ReportFilter('branch', 'Cabang', 'select')),

            // 12. Top Expense
            (new ReportDefinition('finance.top_expense', 'Top Expense',
                type:'summary', chartType:'bar', features:['finance']))
                ->addMetric(new MetricDefinition('total', 'Total', 'sum', 'amount', format:'currency', color:'danger'))
                ->addDimension(new DimensionDefinition('account_name', 'Account', 'account_name', type:'string'))
                ->addDimension(new DimensionDefinition('vendor', 'Vendor', 'vendor_name', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            // 13. Branch Profitability
            (new ReportDefinition('finance.branch_profitability', 'Branch Profitability',
                type:'summary', chartType:'bar', features:['finance'], permissions:['manage_finance']))
                ->addMetric(new MetricDefinition('revenue', 'Revenue', 'sum', 'revenue', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('expenses', 'Expenses', 'sum', 'expenses', format:'currency', color:'danger'))
                ->addMetric(new MetricDefinition('profit', 'Profit', 'expression', 'revenue - expenses', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('margin', 'Margin %', 'expression', '(revenue - expenses) / revenue * 100', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('branch', 'Branch', 'branch_name', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // MULTI CURRENCY — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function currencyTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'finance.currency.index',
            title: 'Currencies & Exchange Rates',
            modelClass: \App\Models\Tenant\Currency::class,
            defaultSort: ['currency_code' => 'asc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['finance'],
        ))
            ->addColumns([
                new ColumnDefinition('currency_code',  'Code',        type:'text',    sortable:true, bold:true, width:'80px', align:'center', order:1),
                new ColumnDefinition('currency_name',  'Currency',    type:'text',    sortable:true, searchable:true, order:2),
                new ColumnDefinition('symbol',         'Symbol',      type:'text',    width:'60px', align:'center', order:3),
                new ColumnDefinition('exchange_rate',  'Rate (to IDR)',type:'number', sortable:true, align:'right', bold:true, width:'120px', order:4),
                new ColumnDefinition('last_updated',   'Last Updated',type:'datetime',sortable:true, width:'130px', order:5),
                new ColumnDefinition('is_base',        'Base',        type:'boolean', width:'60px', align:'center', order:6),
                new ColumnDefinition('status',         'Status',      type:'badge',   sortable:true, width:'80px', align:'center', order:7),
                new ColumnDefinition('actions',        '',            type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[['value'=>'active','label'=>'Active'],['value'=>'inactive','label'=>'Inactive']], order:1))
            ->addBulkAction(new BulkAction('update_rates', 'Update Rates', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }
}
