<?php

namespace App\Enterprise\Definitions;

use App\Enterprise\Data\DataDefinition;
use App\Enterprise\Data\ColumnDefinition;
use App\Enterprise\Data\FilterDefinition;
use App\Enterprise\Data\BulkAction;
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
 * PlatformDefinitions — ALL Enterprise definitions for Platform Administration, Multi-Tenant Governance & Operations Center.
 * 
 * Covers: Multi-Tenant, Plans, Licenses, Feature Engine Admin, Role/Permission Center,
 * Platform Security, Monitoring, Operations, Billing, Tenant Analytics,
 * Platform AI, Backup/Recovery, Compliance.
 * 
 * MODUL ERP KELIMA BELAS — ENTERPRISE PLATFORM ADMINISTRATION, MULTI-TENANT GOVERNANCE & OPERATIONS CENTER
 * 
 * ⚠️ Platform Admin is the CONTROL PLANE above ALL 14 modules.
 */
class PlatformDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // PLATFORM WORKSPACE (18 tabs)
    // ═══════════════════════════════════════════════════════════

    public static function workspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'platform',
            title: 'Platform Administration',
            icon: '🛡️',
            tabs: [
                ['id' => 'overview',           'label' => 'Overview',            'icon' => '📊'],
                ['id' => 'tenants',            'label' => 'Tenants',             'icon' => '🏢'],
                ['id' => 'subscriptions',      'label' => 'Subscriptions',       'icon' => '💳'],
                ['id' => 'plans',              'label' => 'Plans',               'icon' => '📋'],
                ['id' => 'licenses',           'label' => 'Licenses',            'icon' => '🔑'],
                ['id' => 'users',              'label' => 'Users',               'icon' => '👥'],
                ['id' => 'roles',              'label' => 'Roles & Permissions', 'icon' => '🔐'],
                ['id' => 'features',           'label' => 'Feature Engine',      'icon' => '⚙️'],
                ['id' => 'business_types',     'label' => 'Business Types',      'icon' => '🏪'],
                ['id' => 'branches',           'label' => 'Branches',            'icon' => '📍'],
                ['id' => 'domains',            'label' => 'Domains',             'icon' => '🌐'],
                ['id' => 'monitoring',         'label' => 'Platform Monitoring', 'icon' => '📡'],
                ['id' => 'health',             'label' => 'Platform Health',     'icon' => '💚'],
                ['id' => 'security',           'label' => 'Security Center',     'icon' => '🛡️'],
                ['id' => 'audit',              'label' => 'Audit Center',        'icon' => '📜'],
                ['id' => 'settings',           'label' => 'System Settings',     'icon' => '⚡'],
                ['id' => 'billing',            'label' => 'Billing',             'icon' => '💰'],
                ['id' => 'operations',         'label' => 'Operations Center',   'icon' => '🔧'],
            ],
            actions: [
                ['id' => 'create_tenant',      'label' => 'Create Tenant',      'roles' => ['super_admin','platform_admin']],
                ['id' => 'create_plan',        'label' => 'Create Plan',        'roles' => ['super_admin','platform_admin']],
                ['id' => 'manage_license',     'label' => 'Manage License',     'roles' => ['super_admin','platform_admin']],
                ['id' => 'toggle_feature',     'label' => 'Toggle Feature',     'roles' => ['super_admin','platform_admin']],
                ['id' => 'run_backup',         'label' => 'Run Backup',         'roles' => ['super_admin','platform_admin','devops']],
                ['id' => 'view_audit',         'label' => 'View Audit Log',     'roles' => ['super_admin','platform_admin','security_officer','auditor']],
                ['id' => 'maintenance_mode',   'label' => 'Maintenance Mode',   'roles' => ['super_admin','platform_admin','devops']],
                ['id' => 'export',             'label' => 'Export',             'roles' => ['super_admin','platform_admin']],
            ],
            sidebarWidgets: [
                ['id' => 'platform_health_card',  'component' => 'PlatformHealthCard', 'priority' => 10],
                ['id' => 'tenant_summary',        'component' => 'TenantSummary',       'priority' => 20],
                ['id' => 'subscription_alerts',   'component' => 'SubscriptionAlerts',  'priority' => 30],
                ['id' => 'quick_actions',         'component' => 'QuickActions',        'priority' => 40],
            ],
            features: ['platform'],
            permissions: ['manage_platform'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // TENANTS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function tenantTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'platform.tenant.index',
            title: 'Tenant Management',
            modelClass: \App\Models\Tenant::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['platform'],
        ))
            ->addColumns([
                new ColumnDefinition('tenant_name',      'Tenant',          type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('domain',           'Domain',          type:'text',    sortable:true, width:'180px', order:2),
                new ColumnDefinition('plan_name',        'Plan',            type:'badge',   sortable:true, width:'90px', order:3),
                new ColumnDefinition('business_type',    'Business Type',   type:'badge',   sortable:true, width:'110px', order:4),
                new ColumnDefinition('user_count',       'Users',           type:'number',  sortable:true, width:'60px', align:'center', order:5),
                new ColumnDefinition('branch_count',     'Branches',        type:'number',  width:'65px', align:'center', order:6),
                new ColumnDefinition('storage_used_gb',  'Storage (GB)',    type:'number',  sortable:true, width:'80px', align:'right', order:7),
                new ColumnDefinition('subscription_status','Subscription',  type:'badge',   sortable:true, filterable:true, width:'110px', order:8),
                new ColumnDefinition('created_at',       'Created',         type:'date',    sortable:true, width:'100px', order:9),
                new ColumnDefinition('status',           'Status',          type:'badge',   sortable:true, filterable:true, width:'80px', order:10),
                new ColumnDefinition('actions',          '',                type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'active','label'=>'Active'],
                ['value'=>'suspended','label'=>'Suspended'],
                ['value'=>'trial','label'=>'Trial'],
                ['value'=>'archived','label'=>'Archived'],
            ], order:1))
            ->addFilter(new FilterDefinition('subscription_status', 'Subscription', type:'select', quick:true, options:[
                ['value'=>'active','label'=>'Active'],
                ['value'=>'past_due','label'=>'Past Due'],
                ['value'=>'cancelled','label'=>'Cancelled'],
                ['value'=>'expired','label'=>'Expired'],
            ], order:2))
            ->addFilter(new FilterDefinition('business_type', 'Business Type', type:'select', order:3))
            ->addFilter(new FilterDefinition('plan_id', 'Plan', type:'select', order:4))
            ->addFilter(new FilterDefinition('created_at', 'Date', type:'date_range', order:5))
            ->addBulkAction(new BulkAction('suspend', 'Suspend', variant:'warning'))
            ->addBulkAction(new BulkAction('activate', 'Activate', variant:'primary'))
            ->addBulkAction(new BulkAction('backup', 'Backup Now', variant:'default'))
            ->addBulkAction(new BulkAction('archive', 'Archive', variant:'danger', confirm:true));
    }

    // ═══════════════════════════════════════════════════════════
    // PLANS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function planTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'platform.plan.index',
            title: 'Plan Management',
            modelClass: \App\Models\Plan::class,
            defaultSort: ['sort_order' => 'asc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['platform'],
        ))
            ->addColumns([
                new ColumnDefinition('plan_name',        'Plan Name',      type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('price_monthly',    'Monthly Price',  type:'currency', sortable:true, align:'right', width:'120px', order:2),
                new ColumnDefinition('price_yearly',     'Yearly Price',   type:'currency', sortable:true, align:'right', width:'120px', order:3),
                new ColumnDefinition('max_users',        'Max Users',      type:'number',  sortable:true, width:'75px', align:'center', order:4),
                new ColumnDefinition('max_branches',     'Max Branches',   type:'number',  sortable:true, width:'85px', align:'center', order:5),
                new ColumnDefinition('storage_limit_gb', 'Storage (GB)',   type:'number',  sortable:true, width:'80px', align:'center', order:6),
                new ColumnDefinition('modules',          'Modules',        type:'tags',    width:'180px', order:7),
                new ColumnDefinition('tenant_count',     'Tenants',        type:'number',  sortable:true, width:'65px', align:'center', order:8),
                new ColumnDefinition('status',           'Status',         type:'badge',   sortable:true, filterable:true, width:'80px', order:9),
                new ColumnDefinition('actions',          '',               type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'active','label'=>'Active'],
                ['value'=>'inactive','label'=>'Inactive'],
                ['value'=>'draft','label'=>'Draft'],
                ['value'=>'deprecated','label'=>'Deprecated'],
            ], order:1))
            ->addBulkAction(new BulkAction('activate', 'Activate', variant:'primary'))
            ->addBulkAction(new BulkAction('deactivate', 'Deactivate', variant:'default'))
            ->addBulkAction(new BulkAction('clone', 'Clone Plan', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // LICENSES — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function licenseTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'platform.license.index',
            title: 'License Management',
            modelClass: \App\Models\LicenseKey::class,
            defaultSort: ['issued_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['platform'],
        ))
            ->addColumns([
                new ColumnDefinition('license_key_prefix','License Key',   type:'text',    sortable:true, bold:true, width:'160px', order:1),
                new ColumnDefinition('tenant_name',      'Tenant',          type:'text',    sortable:true, searchable:true, order:2),
                new ColumnDefinition('plan_name',        'Plan',            type:'badge',   sortable:true, width:'90px', order:3),
                new ColumnDefinition('device_limit',     'Device Limit',   type:'number',  width:'80px', align:'center', order:4),
                new ColumnDefinition('activated_at',     'Activated',       type:'date',    sortable:true, width:'100px', order:5),
                new ColumnDefinition('expires_at',       'Expires',         type:'date',    sortable:true, width:'100px', order:6),
                new ColumnDefinition('renewal_count',    'Renewals',        type:'number',  width:'65px', align:'center', order:7),
                new ColumnDefinition('status',           'Status',          type:'badge',   sortable:true, filterable:true, width:'90px', order:8),
                new ColumnDefinition('actions',          '',                type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'active','label'=>'Active'],
                ['value'=>'expired','label'=>'Expired'],
                ['value'=>'revoked','label'=>'Revoked'],
                ['value'=>'suspended','label'=>'Suspended'],
            ], order:1))
            ->addBulkAction(new BulkAction('renew', 'Renew', variant:'primary'))
            ->addBulkAction(new BulkAction('revoke', 'Revoke', variant:'danger', confirm:true))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // PLATFORM MONITORING — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function monitoringTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'platform.monitoring.index',
            title: 'Platform Monitoring',
            modelClass: \App\Models\PlatformMetric::class,
            defaultSort: ['recorded_at' => 'desc'],
            perPage: 50,
            selectable: true,
            features: ['platform'],
        ))
            ->addColumns([
                new ColumnDefinition('metric_name',      'Metric',          type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('metric_value',     'Value',           type:'text',    sortable:true, bold:true, width:'130px', order:2),
                new ColumnDefinition('threshold',        'Threshold',       type:'text',    width:'100px', order:3),
                new ColumnDefinition('status',           'Status',          type:'badge',   sortable:true, width:'80px', order:4),
                new ColumnDefinition('component',        'Component',       type:'badge',   sortable:true, width:'90px', order:5),
                new ColumnDefinition('recorded_at',      'Recorded',        type:'datetime',sortable:true, width:'130px', order:6),
                new ColumnDefinition('actions',          '',                type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'healthy','label'=>'✅ Healthy'],
                ['value'=>'warning','label'=>'⚠️ Warning'],
                ['value'=>'critical','label'=>'❌ Critical'],
            ], order:1))
            ->addFilter(new FilterDefinition('component', 'Component', type:'select', options:[
                ['value'=>'cpu','label'=>'CPU'],['value'=>'memory','label'=>'Memory'],
                ['value'=>'storage','label'=>'Storage'],['value'=>'database','label'=>'Database'],
                ['value'=>'redis','label'=>'Redis'],['value'=>'queue','label'=>'Queue'],
                ['value'=>'api','label'=>'API'],['value'=>'ai','label'=>'AI'],
                ['value'=>'webhook','label'=>'Webhook'],
            ], order:2))
            ->addBulkAction(new BulkAction('acknowledge', 'Acknowledge', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // PLATFORM AUDIT — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function auditTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'platform.audit.index',
            title: 'Audit Trail',
            modelClass: \App\Models\PlatformAudit::class,
            defaultSort: ['performed_at' => 'desc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            features: ['platform'],
        ))
            ->addColumns([
                new ColumnDefinition('action',           'Action',          type:'text',    searchable:true, bold:true, order:1),
                new ColumnDefinition('entity_type',      'Entity',          type:'badge',   sortable:true, width:'90px', order:2),
                new ColumnDefinition('entity_id',        'Entity ID',       type:'text',    width:'120px', order:3),
                new ColumnDefinition('performed_by',     'Performed By',    type:'text',    sortable:true, width:'110px', order:4),
                new ColumnDefinition('ip_address',       'IP',              type:'text',    width:'110px', order:5),
                new ColumnDefinition('changes',          'Changes',         type:'text',    order:6),
                new ColumnDefinition('performed_at',     'Date',            type:'datetime',sortable:true, width:'130px', order:7),
                new ColumnDefinition('actions',          '',                type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('entity_type', 'Entity', type:'select', quick:true, options:[
                ['value'=>'tenant','label'=>'Tenant'],['value'=>'plan','label'=>'Plan'],
                ['value'=>'license','label'=>'License'],['value'=>'user','label'=>'User'],
                ['value'=>'role','label'=>'Role'],['value'=>'feature','label'=>'Feature'],
                ['value'=>'billing','label'=>'Billing'],
            ], order:1))
            ->addFilter(new FilterDefinition('performed_at', 'Date', type:'date_range', order:2))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES — 15 Platform Rules
    // ═══════════════════════════════════════════════════════════

    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            (new AutomationDefinition('platform.subscription_expiry', 'Subscription Expiry',
                trigger: TriggerType::DATE_REACHED, module: 'platform'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '💳 Subscription Expiring', 'body' => 'Tenant {{subject.tenant_name}} subscription expires soon.', 'roles' => ['super_admin','platform_admin']])),

            (new AutomationDefinition('platform.trial_ending', 'Trial Ending',
                trigger: TriggerType::DATE_REACHED, module: 'platform'))
                ->addStep(new AutomationStep(ActionType::SEND_EMAIL, ['template' => 'trial_ending', 'to' => '{{subject.tenant_email}}'])),

            (new AutomationDefinition('platform.backup_reminder', 'Backup Reminder',
                trigger: TriggerType::SCHEDULED, module: 'platform'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '💾 Backup Reminder', 'body' => 'Scheduled backup due.', 'roles' => ['devops']])),

            (new AutomationDefinition('platform.backup_failure', 'Backup Failure',
                trigger: TriggerType::RECORD_UPDATED, module: 'platform'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '❌ Backup Failed', 'body' => 'Backup for {{subject.tenant_name}} failed.', 'roles' => ['super_admin','platform_admin','devops']])),

            (new AutomationDefinition('platform.alert', 'Platform Alert',
                trigger: TriggerType::RECORD_UPDATED, module: 'platform'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🚨 {{subject.metric_name}}', 'body' => '{{subject.metric_value}} (threshold: {{subject.threshold}})', 'roles' => ['super_admin','platform_admin','devops']])),

            (new AutomationDefinition('platform.cpu_alert', 'CPU Alert',
                trigger: TriggerType::RECORD_UPDATED, module: 'platform'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🔥 CPU Alert', 'body' => 'CPU usage at {{subject.metric_value}}%.', 'roles' => ['devops']])),

            (new AutomationDefinition('platform.storage_alert', 'Storage Alert',
                trigger: TriggerType::RECORD_UPDATED, module: 'platform'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '💿 Storage Alert', 'body' => 'Storage at {{subject.metric_value}}%.', 'roles' => ['devops']])),

            (new AutomationDefinition('platform.database_alert', 'Database Alert',
                trigger: TriggerType::RECORD_UPDATED, module: 'platform'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🗄️ Database Alert', 'body' => 'DB {{subject.metric_name}}: {{subject.metric_value}}.', 'roles' => ['devops']])),

            (new AutomationDefinition('platform.queue_failure', 'Queue Failure',
                trigger: TriggerType::RECORD_UPDATED, module: 'platform'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '📬 Queue Failure', 'body' => 'Queue has {{subject.failed_count}} failed jobs.', 'roles' => ['devops']])),

            (new AutomationDefinition('platform.tenant_suspension', 'Tenant Suspension',
                trigger: TriggerType::RECORD_UPDATED, module: 'platform'))
                ->addStep(new AutomationStep(ActionType::SEND_EMAIL, ['template' => 'tenant_suspended', 'to' => '{{subject.tenant_email}}'])),

            (new AutomationDefinition('platform.license_expiry', 'License Expiry',
                trigger: TriggerType::DATE_REACHED, module: 'platform'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🔑 License Expiring', 'body' => 'License for {{subject.tenant_name}} expires.', 'roles' => ['super_admin','platform_admin']])),

            (new AutomationDefinition('platform.security_alert', 'Security Alert',
                trigger: TriggerType::RECORD_CREATED, module: 'platform'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🔒 Security Alert', 'body' => '{{subject.message}}', 'roles' => ['super_admin','security_officer']])),

            (new AutomationDefinition('platform.compliance_reminder', 'Compliance Reminder',
                trigger: TriggerType::SCHEDULED, module: 'platform'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'Compliance Review Due', 'assignee_role' => 'security_officer'])),

            (new AutomationDefinition('platform.billing_reminder', 'Billing Reminder',
                trigger: TriggerType::DATE_REACHED, module: 'platform'))
                ->addStep(new AutomationStep(ActionType::SEND_EMAIL, ['template' => 'billing_reminder', 'to' => '{{subject.tenant_email}}'])),

            (new AutomationDefinition('platform.system_maintenance', 'System Maintenance',
                trigger: TriggerType::SCHEDULED, module: 'platform'))
                ->addStep(new AutomationStep(ActionType::ENABLE_MAINTENANCE_MODE, []))
                ->addStep(new AutomationStep(ActionType::RUN_MAINTENANCE_TASKS, []))
                ->addStep(new AutomationStep(ActionType::DISABLE_MAINTENANCE_MODE, []))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '🔧 System maintenance completed.'])),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // REPORTING DEFINITIONS — 15 Platform Reports
    // ═══════════════════════════════════════════════════════════

    /** @return ReportDefinition[] */
    public static function reports(): array
    {
        return [
            (new ReportDefinition('platform.health', 'Platform Health',
                type:'summary', chartType:'kpi', features:['platform'], permissions:['manage_platform']))
                ->addMetric(new MetricDefinition('health_score', 'Health Score', 'last', 'health_score', format:'number', color:'success', icon:'💚'))
                ->addMetric(new MetricDefinition('uptime_pct', 'Uptime %', 'last', 'uptime_pct', format:'number', color:'info', icon:'⏱️'))
                ->addMetric(new MetricDefinition('active_tenants', 'Active Tenants', 'count', 'active_tenants', format:'number', color:'primary', icon:'🏢'))
                ->addMetric(new MetricDefinition('alerts', 'Active Alerts', 'count', 'alerts', format:'number', color:'danger', icon:'🚨')),

            (new ReportDefinition('platform.tenant_analytics', 'Tenant Analytics',
                type:'trend', chartType:'line', features:['platform']))
                ->addMetric(new MetricDefinition('new_tenants', 'New', 'count', 'new', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('churned', 'Churned', 'count', 'churned', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('total', 'Total', 'count', 'id', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'created_at', type:'date')),

            (new ReportDefinition('platform.mrr', 'MRR Report',
                type:'trend', chartType:'line', features:['platform'], permissions:['manage_platform']))
                ->addMetric(new MetricDefinition('mrr', 'MRR', 'sum', 'mrr', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('arr', 'ARR', 'last', 'arr', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('arpu', 'ARPU', 'avg', 'arpu', format:'currency', color:'info'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'month', type:'date')),

            (new ReportDefinition('platform.revenue', 'Revenue Report',
                type:'summary', chartType:'bar', features:['platform'], permissions:['manage_platform']))
                ->addMetric(new MetricDefinition('revenue', 'Revenue', 'sum', 'revenue', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('refunds', 'Refunds', 'sum', 'refunds', format:'currency', color:'danger'))
                ->addMetric(new MetricDefinition('net', 'Net Revenue', 'expression', 'revenue - refunds', format:'currency', color:'primary'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'month', type:'date')),

            (new ReportDefinition('platform.churn', 'Churn Analysis',
                type:'summary', chartType:'table', features:['platform']))
                ->addMetric(new MetricDefinition('churn_rate', 'Churn Rate %', 'avg', 'churn_pct', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('churned_tenants', 'Churned', 'count', 'churned', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('reason', 'Reason', 'churn_reason', type:'string')),

            (new ReportDefinition('platform.usage', 'Platform Usage',
                type:'summary', chartType:'bar', features:['platform']))
                ->addMetric(new MetricDefinition('api_calls', 'API Calls', 'sum', 'api_calls', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('ai_tokens', 'AI Tokens', 'sum', 'ai_tokens', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('storage_gb', 'Storage (GB)', 'sum', 'storage_gb', format:'number', color:'warning'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'month', type:'date')),

            (new ReportDefinition('platform.feature_adoption', 'Feature Adoption',
                type:'summary', chartType:'bar', features:['platform']))
                ->addMetric(new MetricDefinition('adoption_pct', 'Adoption %', 'avg', 'adoption_pct', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('active_users', 'Active Users', 'count', 'active_users', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('module', 'Module', 'module', type:'string')),

            (new ReportDefinition('platform.license_usage', 'License Usage',
                type:'summary', chartType:'table', features:['platform']))
                ->addMetric(new MetricDefinition('active_licenses', 'Active', 'count', 'active', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('expired', 'Expired', 'count', 'expired', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('available', 'Available', 'count', 'available', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('plan', 'Plan', 'plan_name', type:'string')),

            (new ReportDefinition('platform.subscription_analytics', 'Subscription Analytics',
                type:'summary', chartType:'table', features:['platform']))
                ->addMetric(new MetricDefinition('total', 'Total', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('active', 'Active', 'count', 'active', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('trial', 'Trial', 'count', 'trial', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('cancelled', 'Cancelled', 'count', 'cancelled', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('plan', 'Plan', 'plan_name', type:'string')),

            (new ReportDefinition('platform.security_audit', 'Security Audit',
                type:'summary', chartType:'table', features:['platform'], permissions:['manage_platform']))
                ->addMetric(new MetricDefinition('events', 'Events', 'count', 'id', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('resolved', 'Resolved', 'count', 'resolved', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('severity', 'Severity', 'severity', type:'string'))
                ->addDimension(new DimensionDefinition('category', 'Category', 'category', type:'string')),

            (new ReportDefinition('platform.compliance_audit', 'Compliance Audit',
                type:'summary', chartType:'table', features:['platform']))
                ->addMetric(new MetricDefinition('compliant', 'Compliant', 'count', 'compliant', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('non_compliant', 'Non-Compliant', 'count', 'non_compliant', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('audits_completed', 'Audits Done', 'count', 'audits', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('standard', 'Standard', 'standard', type:'string')),

            (new ReportDefinition('platform.infrastructure', 'Infrastructure Report',
                type:'summary', chartType:'kpi', features:['platform']))
                ->addMetric(new MetricDefinition('cpu_usage', 'CPU Usage %', 'avg', 'cpu_pct', format:'number', color:'primary', icon:'🔥'))
                ->addMetric(new MetricDefinition('memory_usage', 'Memory Usage %', 'avg', 'memory_pct', format:'number', color:'info', icon:'💾'))
                ->addMetric(new MetricDefinition('storage_usage', 'Storage Usage %', 'avg', 'storage_pct', format:'number', color:'warning', icon:'💿'))
                ->addMetric(new MetricDefinition('db_connections', 'DB Connections', 'avg', 'db_connections', format:'number', color:'primary', icon:'🗄️')),

            (new ReportDefinition('platform.operations', 'Operations Report',
                type:'summary', chartType:'table', features:['platform']))
                ->addMetric(new MetricDefinition('failed_jobs', 'Failed Jobs', 'count', 'failed_jobs', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('queue_size', 'Queue Size', 'sum', 'queue_size', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('backup_status', 'Backup OK', 'count', 'backup_ok', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('date', 'Date', 'date', type:'date')),

            (new ReportDefinition('platform.executive_saas', 'Executive SaaS Report',
                type:'summary', chartType:'kpi', features:['platform'], permissions:['manage_platform']))
                ->addMetric(new MetricDefinition('mrr', 'MRR', 'last', 'mrr', format:'currency', color:'success', icon:'💰'))
                ->addMetric(new MetricDefinition('total_tenants', 'Total Tenants', 'last', 'total_tenants', format:'number', color:'primary', icon:'🏢'))
                ->addMetric(new MetricDefinition('churn_rate', 'Churn Rate', 'last', 'churn_rate', format:'number', color:'danger', icon:'📉'))
                ->addMetric(new MetricDefinition('platform_health', 'Platform Health', 'last', 'health_score', format:'number', color:'success', icon:'💚'))
                ->addMetric(new MetricDefinition('feature_adoption', 'Feature Adoption %', 'last', 'adoption_pct', format:'number', color:'info', icon:'⚙️')),
        ];
    }
}
