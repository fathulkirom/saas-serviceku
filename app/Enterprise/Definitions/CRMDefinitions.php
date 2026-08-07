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
 * CRMDefinitions — ALL Enterprise definitions for CRM & Customer Management.
 * 
 * Covers: Customer Master, Customer 360° Workspace, Device Management,
 * Membership Engine, Communication, Automation, Reporting.
 */
class CRMDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // CUSTOMER WORKSPACE (13 tabs)
    // ═══════════════════════════════════════════════════════════
    
    public static function customerWorkspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'customer',
            title: 'Customer Workspace',
            icon: '👤',
            tabs: [
                ['id' => 'overview',   'label' => 'Overview',          'icon' => '📋'],
                ['id' => 'profile',    'label' => 'Profile',           'icon' => '👤'],
                ['id' => 'timeline',   'label' => 'Timeline',          'icon' => '🕐'],
                ['id' => 'services',   'label' => 'Service History',   'icon' => '🔧'],
                ['id' => 'purchases',  'label' => 'Purchase History',  'icon' => '🛒'],
                ['id' => 'invoices',   'label' => 'Invoices',          'icon' => '💰'],
                ['id' => 'payments',   'label' => 'Payments',          'icon' => '💳'],
                ['id' => 'devices',    'label' => 'Devices',           'icon' => '📱'],
                ['id' => 'warranty',   'label' => 'Warranty',          'icon' => '🛡️'],
                ['id' => 'communication','label' => 'Communication',   'icon' => '💬'],
                ['id' => 'notes',      'label' => 'Notes',             'icon' => '📝'],
                ['id' => 'documents',  'label' => 'Documents',         'icon' => '📄'],
                ['id' => 'activity',   'label' => 'Activity',          'icon' => '📊'],
            ],
            actions: [
                ['id' => 'new_service',  'label' => 'New Service',  'roles' => ['owner','admin','cs']],
                ['id' => 'new_sale',     'label' => 'New Sale',     'roles' => ['owner','admin','cashier','manager']],
                ['id' => 'send_wa',      'label' => 'Send WA',      'roles' => ['owner','admin','cs']],
                ['id' => 'add_note',     'label' => 'Add Note',     'roles' => ['owner','admin','cs']],
                ['id' => 'add_device',   'label' => 'Add Device',   'roles' => ['owner','admin','cs']],
                ['id' => 'edit',         'label' => 'Edit',         'roles' => ['owner','admin']],
                ['id' => 'export',       'label' => 'Export',       'roles' => ['owner','admin','manager']],
            ],
            sidebarWidgets: [
                ['id' => 'customer_360',   'component' => 'Customer360',  'priority' => 10],
                ['id' => 'member_card',    'component' => 'MemberCard',   'priority' => 20],
                ['id' => 'quick_actions',  'component' => 'QuickActions', 'priority' => 30],
                ['id' => 'tags',           'component' => 'TagsPanel',    'priority' => 40],
            ],
            features: ['customers'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // DATA TABLE — Customer List
    // ═══════════════════════════════════════════════════════════
    
    public static function dataTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'customer.index',
            title: 'Daftar Pelanggan',
            modelClass: \App\Models\Tenant\Customer::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['customers'],
        ))
            ->addColumns([
                new ColumnDefinition('customer_code', 'Kode', type:'text', sortable:true, bold:true, width:'100px', order:1),
                new ColumnDefinition('name', 'Nama', type:'text', sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('phone', 'Telepon', type:'text', sortable:true, searchable:true, order:3),
                new ColumnDefinition('email', 'Email', type:'text', sortable:true, order:4),
                new ColumnDefinition('type', 'Tipe', type:'badge', sortable:true, filterable:true, align:'center', width:'90px', order:5),
                new ColumnDefinition('member_level', 'Member', type:'badge', sortable:true, filterable:true, align:'center', width:'100px', order:6),
                new ColumnDefinition('service_count', 'Servis', type:'number', sortable:true, align:'center', width:'70px', order:7),
                new ColumnDefinition('total_spending', 'Total Spending', type:'currency', sortable:true, align:'right', width:'130px', aggregate:true, aggregateType:'sum', order:8),
                new ColumnDefinition('last_visit', 'Last Visit', type:'date', sortable:true, width:'110px', order:9),
                new ColumnDefinition('tags', 'Tags', type:'tags', order:10),
                new ColumnDefinition('created_at', 'Terdaftar', type:'date', sortable:true, width:'110px', order:11),
                new ColumnDefinition('actions', '', type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('type', 'Tipe', type:'select', quick:true,
                options:[['value'=>'personal','label'=>'Personal'],['value'=>'corporate','label'=>'Corporate'],['value'=>'walkin','label'=>'Walk In']], order:1))
            ->addFilter(new FilterDefinition('member_level', 'Member', type:'select', quick:true,
                options:[['value'=>'regular','label'=>'Regular'],['value'=>'silver','label'=>'Silver'],['value'=>'gold','label'=>'Gold'],['value'=>'platinum','label'=>'Platinum']], order:2))
            ->addFilter(new FilterDefinition('source', 'Source', type:'select', order:3))
            ->addFilter(new FilterDefinition('created_at', 'Tanggal', type:'date_range', quick:true, order:4))
            ->addBulkAction(new BulkAction('send_wa', 'Send WA', variant:'default'))
            ->addBulkAction(new BulkAction('add_tag', 'Add Tag', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'))
            ->addBulkAction(new BulkAction('delete', 'Hapus', variant:'danger', confirm:true, permissions:['delete_models']));
    }

    // ═══════════════════════════════════════════════════════════
    // FORM — Customer Create
    // ═══════════════════════════════════════════════════════════
    
    public static function createForm(): FormDefinition
    {
        return (new FormDefinition(
            id: 'customer.create',
            title: 'Tambah Pelanggan Baru',
            method: 'POST',
            endpoint: '/customers',
            features: ['customers'],
        ))
            ->addSection(new FormSection(id:'personal', label:'Informasi Pribadi', icon:'👤', cols:2))
            ->addSection(new FormSection(id:'contact', label:'Kontak', icon:'📞', cols:2))
            ->addSection(new FormSection(id:'address', label:'Alamat', icon:'📍', cols:1))
            ->addSection(new FormSection(id:'member', label:'Membership', icon:'⭐', cols:2, roles:['owner','admin'], features:['customers']))
            ->addFields([
                new FormField('name', type:'text', label:'Nama Lengkap', required:true, section:'personal', cols:12),
                new FormField('type', type:'select', label:'Tipe', section:'personal', cols:6,
                    options:[['value'=>'personal','label'=>'Personal'],['value'=>'corporate','label'=>'Corporate'],['value'=>'walkin','label'=>'Walk In']]),
                new FormField('customer_code', type:'text', label:'Kode', section:'personal', cols:6),
                new FormField('phone', type:'phone', label:'Telepon', required:true, section:'contact', cols:6),
                new FormField('whatsapp', type:'phone', label:'WhatsApp', section:'contact', cols:6),
                new FormField('email', type:'email', label:'Email', section:'contact', cols:6),
                new FormField('social_media', type:'text', label:'Social Media', section:'contact', cols:6),
                new FormField('emergency_contact', type:'phone', label:'Kontak Darurat', section:'contact', cols:6),
                new FormField('address', type:'textarea', label:'Alamat', section:'address', cols:12),
                new FormField('city', type:'text', label:'Kota', section:'address', cols:4),
                new FormField('province', type:'text', label:'Provinsi', section:'address', cols:4),
                new FormField('postal_code', type:'text', label:'Kode Pos', section:'address', cols:4),
                new FormField('gps_coordinates', type:'text', label:'GPS (Lat, Lng)', section:'address', cols:6),
                new FormField('source', type:'select', label:'Sumber', section:'personal', cols:6,
                    options:[['value'=>'walkin','label'=>'Walk In'],['value'=>'referral','label'=>'Referral'],['value'=>'social_media','label'=>'Social Media'],['value'=>'google','label'=>'Google'],['value'=>'other','label'=>'Other']]),
                new FormField('tags', type:'tags', label:'Tags', section:'personal', cols:6),
                new FormField('member_level', type:'select', label:'Level Member', section:'member', cols:4,
                    options:[['value'=>'regular','label'=>'Regular'],['value'=>'silver','label'=>'Silver'],['value'=>'gold','label'=>'Gold'],['value'=>'platinum','label'=>'Platinum']]),
                new FormField('points', type:'number', label:'Points', section:'member', cols:4),
                new FormField('member_since', type:'date', label:'Member Since', section:'member', cols:4),
                new FormField('marketing_consent', type:'switch', label:'Marketing Consent', section:'member', cols:6),
                new FormField('internal_notes', type:'textarea', label:'Catatan Internal', section:'personal', cols:12),
                new FormField('photo', type:'photo', label:'Foto', section:'personal', cols:6, accept:'image/*'),
                new FormField('documents', type:'file', label:'Dokumen', section:'personal', cols:6, multiple:true),
            ])
            ->addAction(new FormAction('save', 'Simpan', variant:'primary', shortcut:'Ctrl+S'))
            ->addAction(new FormAction('save_and_new', 'Simpan & Baru', variant:'secondary'))
            ->addAction(new FormAction('save_draft', 'Draft', variant:'outline'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES
    // ═══════════════════════════════════════════════════════════
    
    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            // New customer welcome
            (new AutomationDefinition('crm.new_customer', 'Pelanggan Baru — Welcome',
                trigger: TriggerType::CUSTOMER_CREATED, module: 'crm'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Follow-up: Pelanggan Baru {{subject.name}}',
                ], delaySeconds: 3600)),

            // Birthday reminder
            (new AutomationDefinition('crm.birthday', 'Ulang Tahun Pelanggan',
                trigger: TriggerType::DATE_REACHED, module: 'crm'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, [
                    'message' => '🎂 Selamat ulang tahun {{subject.name}}! Semoga sukses selalu.',
                ])),

            // No visit 30 days
            (new AutomationDefinition('crm.no_visit_30', 'No Visit 30 Hari',
                trigger: TriggerType::DATE_REACHED, module: 'crm'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => 'Follow-up Required', 'body' => '{{subject.name}} hasn\'t visited in 30 days.',
                ])),

            // VIP upgrade
            (new AutomationDefinition('crm.vip_upgrade', 'VIP Upgrade',
                trigger: TriggerType::RECORD_UPDATED, module: 'crm'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, [
                    'message' => '🌟 Selamat {{subject.name}}! Anda telah di-upgrade ke {{subject.member_level}}!',
                ]))
                ->addStep(new AutomationStep(ActionType::ADD_TIMELINE, [
                    'message' => '⭐ Member upgraded to {{subject.member_level}}.',
                ])),

            // Customer reactivated
            (new AutomationDefinition('crm.reactivated', 'Customer Reactivated',
                trigger: TriggerType::RECORD_UPDATED, module: 'crm'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                    'message' => '🔄 Customer reactivated: {{subject.name}}.',
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
            // Customer growth
            (new ReportDefinition('crm.growth', 'Pertumbuhan Pelanggan',
                type:'trend', modelClass:\App\Models\Tenant\Customer::class, chartType:'line',
                features:['customers']))
                ->addMetric(new MetricDefinition('new', 'Pelanggan Baru', 'count', 'id', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('date', 'Tanggal', 'created_at', type:'date'))
                ->addFilter(new ReportFilter('date_range', 'Periode', 'date_range')),

            // Customer segmentation
            (new ReportDefinition('crm.segmentation', 'Segmentasi Pelanggan',
                type:'summary', chartType:'pie', features:['customers']))
                ->addMetric(new MetricDefinition('count', 'Jumlah', 'count', 'id', format:'number'))
                ->addDimension(new DimensionDefinition('type', 'Tipe', 'type', type:'string')),

            // Top customers
            (new ReportDefinition('crm.top_customers', 'Top Customers',
                type:'summary', chartType:'bar', features:['customers']))
                ->addMetric(new MetricDefinition('total', 'Total Spending', 'sum', 'total_spending', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('services', 'Jumlah Servis', 'sum', 'service_count', format:'number'))
                ->addDimension(new DimensionDefinition('name', 'Customer', 'name', type:'string')),

            // Lifetime value
            (new ReportDefinition('crm.lifetime_value', 'Lifetime Value',
                type:'summary', chartType:'kpi', features:['customers'], permissions:['manage_customers']))
                ->addMetric(new MetricDefinition('avg_ltv', 'Avg LTV', 'avg', 'total_spending', format:'currency', color:'primary', icon:'💰'))
                ->addMetric(new MetricDefinition('avg_services', 'Avg Services', 'avg', 'service_count', format:'number', icon:'🔧'))
                ->addMetric(new MetricDefinition('total_customers', 'Total Customers', 'count', 'id', format:'number', icon:'👥')),

            // Inactive customers
            (new ReportDefinition('crm.inactive', 'Inactive Customers',
                type:'summary', chartType:'table', features:['customers']))
                ->addMetric(new MetricDefinition('days_inactive', 'Days Inactive', 'max', 'days_since_last_visit', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('name', 'Customer', 'name', type:'string'))
                ->addFilter(new ReportFilter('min_days', 'Min Days', 'number')),
        ];
    }
}
