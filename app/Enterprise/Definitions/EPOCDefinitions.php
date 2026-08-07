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
use App\Enterprise\Automation\AutomationDefinition;
use App\Enterprise\Automation\AutomationStep;
use App\Enterprise\Automation\TriggerType;
use App\Enterprise\Automation\ActionType;

/**
 * EPOCDefinitions — ALL Enterprise definitions for Enterprise Platform Operations Center.
 * 
 * Covers: Platform Health, Performance Monitoring, Observability, Queue Management,
 * Deployment Center, Backup & Recovery, Disaster Recovery, Security Operations,
 * AI Operations, Infrastructure Monitoring.
 * 
 * MODUL ERP KEDUA PULUH — ENTERPRISE PLATFORM OPERATIONS CENTER (EPOC)
 * 
 * ⚠️ ALL platform operations, monitoring, observability, DevOps MUST route through EPOC.
 * ⚠️ NOT for business users. For: Super Admin, Platform Admin, DevOps, SRE, Infra, Security.
 */
class EPOCDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // EPOC WORKSPACE (16 tabs)
    // ═══════════════════════════════════════════════════════════

    public static function workspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'epoc',
            title: 'Enterprise Platform Operations Center',
            icon: '🖥️',
            tabs: [
                ['id' => 'overview',            'label' => 'Executive Overview',      'icon' => '📊'],
                ['id' => 'platform_health',     'label' => 'Platform Health',         'icon' => '💚'],
                ['id' => 'app_performance',     'label' => 'Application Performance', 'icon' => '⚡'],
                ['id' => 'infrastructure',      'label' => 'Infrastructure Monitoring','icon' => '🖥️'],
                ['id' => 'database',            'label' => 'Database Monitoring',     'icon' => '🗄️'],
                ['id' => 'queue_jobs',          'label' => 'Queue & Jobs',            'icon' => '📬'],
                ['id' => 'cache_session',       'label' => 'Cache & Session',         'icon' => '💾'],
                ['id' => 'api_monitoring',      'label' => 'API Monitoring',          'icon' => '🔌'],
                ['id' => 'integration_mon',     'label' => 'Integration Monitoring',  'icon' => '🔗'],
                ['id' => 'deployment',          'label' => 'Deployment Center',       'icon' => '🚀'],
                ['id' => 'backup_recovery',     'label' => 'Backup & Recovery',       'icon' => '💿'],
                ['id' => 'disaster_recovery',   'label' => 'Disaster Recovery',       'icon' => '🏥'],
                ['id' => 'security_ops',        'label' => 'Security Monitoring',     'icon' => '🛡️'],
                ['id' => 'ai_advisor',          'label' => 'AI Operations Advisor',   'icon' => '🤖'],
                ['id' => 'audit_activity',      'label' => 'Audit & Activity',        'icon' => '📝'],
                ['id' => 'settings',            'label' => 'Settings',                'icon' => '⚙️'],
            ],
            actions: [
                ['id' => 'run_health_check',    'label' => 'Run Health Check',    'roles' => ['super_admin','platform_admin','devops','sre']],
                ['id' => 'trigger_backup',      'label' => 'Trigger Backup',      'roles' => ['super_admin','platform_admin','devops']],
                ['id' => 'deploy',              'label' => 'Deploy',              'roles' => ['super_admin','platform_admin','devops']],
                ['id' => 'rollback',            'label' => 'Rollback',            'roles' => ['super_admin','platform_admin']],
                ['id' => 'flush_cache',         'label' => 'Flush Cache',         'roles' => ['super_admin','platform_admin','devops','sre']],
                ['id' => 'restart_queue',       'label' => 'Restart Queue',       'roles' => ['super_admin','platform_admin','devops','sre']],
                ['id' => 'toggle_maintenance',  'label' => 'Toggle Maintenance',  'roles' => ['super_admin','platform_admin']],
                ['id' => 'retry_failed_jobs',   'label' => 'Retry Failed Jobs',   'roles' => ['super_admin','platform_admin','devops','sre']],
                ['id' => 'generate_report',     'label' => 'Generate Report',     'roles' => ['super_admin','platform_admin','devops','sre','security_engineer']],
                ['id' => 'export',              'label' => 'Export',              'roles' => ['super_admin','platform_admin','devops']],
            ],
            sidebarWidgets: [
                ['id' => 'health_score_mini',   'component' => 'HealthScoreMini',   'priority' => 10],
                ['id' => 'active_alerts',       'component' => 'ActiveAlerts',      'priority' => 20],
                ['id' => 'quick_ops_actions',   'component' => 'QuickOpsActions',   'priority' => 30],
                ['id' => 'ai_ops_insight',      'component' => 'AIOpsInsightMini',  'priority' => 40],
            ],
            features: ['epoc'],
            permissions: ['manage_platform'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // PLATFORM METRICS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function platformMetricsTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'epoc.metrics.index',
            title: 'Platform Metrics',
            modelClass: \App\Models\Tenant\PlatformMetric::class,
            defaultSort: ['recorded_at' => 'desc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['epoc'],
        ))
            ->addColumns([
                new ColumnDefinition('metric_name',      'Metric',         type:'text',    sortable:true, bold:true, width:'140px', order:1),
                new ColumnDefinition('metric_value',     'Value',          type:'text',    bold:true, width:'100px', align:'right', order:2),
                new ColumnDefinition('metric_unit',      'Unit',           type:'text',    width:'60px', align:'center', order:3),
                new ColumnDefinition('category',         'Category',       type:'badge',   sortable:true, filterable:true, width:'100px', order:4),
                new ColumnDefinition('source',           'Source',         type:'text',    width:'90px', order:5),
                new ColumnDefinition('threshold_status', 'Threshold',      type:'badge',   sortable:true, width:'85px', order:6),
                new ColumnDefinition('recorded_at',      'Recorded',       type:'datetime',sortable:true, width:'140px', order:7),
                new ColumnDefinition('actions',          '',               type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('category', 'Category', type:'select', quick:true, options:[
                ['value'=>'cpu','label'=>'CPU'],['value'=>'memory','label'=>'Memory'],
                ['value'=>'storage','label'=>'Storage'],['value'=>'network','label'=>'Network'],
                ['value'=>'database','label'=>'Database'],['value'=>'cache','label'=>'Cache'],
                ['value'=>'queue','label'=>'Queue'],['value'=>'api','label'=>'API'],
            ], order:1))
            ->addFilter(new FilterDefinition('threshold_status', 'Threshold', type:'select', quick:true, options:[['value'=>'ok','label'=>'OK'],['value'=>'warning','label'=>'Warning'],['value'=>'critical','label'=>'Critical']], order:2))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // QUEUE JOBS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function queueJobsTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'epoc.queue.index',
            title: 'Queue Jobs',
            modelClass: \App\Models\Tenant\QueueJob::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 50,
            selectable: true,
            features: ['epoc'],
        ))
            ->addColumns([
                new ColumnDefinition('job_id',           'Job ID',        type:'text',    sortable:true, width:'200px', order:1),
                new ColumnDefinition('queue',            'Queue',         type:'badge',   sortable:true, filterable:true, width:'100px', order:2),
                new ColumnDefinition('payload_display',  'Job Name',      type:'text',    searchable:true, bold:true, order:3),
                new ColumnDefinition('attempts',         'Attempts',      type:'number',  sortable:true, width:'70px', align:'center', order:4),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'85px', order:5),
                new ColumnDefinition('queued_at',        'Queued',        type:'datetime',sortable:true, width:'140px', order:6),
                new ColumnDefinition('reserved_at',      'Reserved',      type:'datetime',width:'140px', order:7),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'110px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[['value'=>'pending','label'=>'Pending'],['value'=>'running','label'=>'Running'],['value'=>'failed','label'=>'Failed'],['value'=>'completed','label'=>'Completed']], order:1))
            ->addFilter(new FilterDefinition('queue', 'Queue', type:'select', quick:true, options:[['value'=>'default','label'=>'Default'],['value'=>'high','label'=>'High'],['value'=>'low','label'=>'Low'],['value'=>'notifications','label'=>'Notifications'],['value'=>'integrations','label'=>'Integrations']], order:2))
            ->addBulkAction(new BulkAction('retry', 'Retry', variant:'primary'))
            ->addBulkAction(new BulkAction('cancel', 'Cancel', variant:'warning'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // FAILED JOBS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function failedJobsTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'epoc.failed_jobs.index',
            title: 'Failed Jobs — Dead Letter Queue',
            modelClass: \App\Models\Tenant\FailedJob::class,
            defaultSort: ['failed_at' => 'desc'],
            perPage: 25,
            selectable: true,
            features: ['epoc'],
        ))
            ->addColumns([
                new ColumnDefinition('job_id',           'Job ID',        type:'text',    width:'200px', order:1),
                new ColumnDefinition('queue',            'Queue',         type:'badge',   sortable:true, width:'90px', order:2),
                new ColumnDefinition('payload_display',  'Job Name',      type:'text',    bold:true, order:3),
                new ColumnDefinition('exception',        'Exception',     type:'text',    width:'150px', order:4),
                new ColumnDefinition('failed_at',        'Failed At',     type:'datetime',sortable:true, width:'140px', order:5),
                new ColumnDefinition('retry_count',      'Retries',       type:'number',  width:'60px', align:'center', order:6),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'110px', order:99),
            ])
            ->addFilter(new FilterDefinition('queue', 'Queue', type:'select', quick:true, options:[['value'=>'default','label'=>'Default'],['value'=>'high','label'=>'High'],['value'=>'low','label'=>'Low']], order:1))
            ->addBulkAction(new BulkAction('retry_all', 'Retry All', variant:'primary'))
            ->addBulkAction(new BulkAction('forget', 'Forget', variant:'danger'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // DEPLOYMENT HISTORY — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function deploymentTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'epoc.deployment.index',
            title: 'Deployment History',
            modelClass: \App\Models\Tenant\Deployment::class,
            defaultSort: ['deployed_at' => 'desc'],
            perPage: 25,
            selectable: true,
            features: ['epoc'],
        ))
            ->addColumns([
                new ColumnDefinition('version',          'Version',       type:'badge',   sortable:true, bold:true, width:'90px', order:1),
                new ColumnDefinition('release_tag',      'Release Tag',   type:'text',    width:'100px', order:2),
                new ColumnDefinition('environment',      'Environment',   type:'badge',   sortable:true, filterable:true, width:'85px', order:3),
                new ColumnDefinition('deployed_by',      'Deployed By',   type:'text',    width:'100px', order:4),
                new ColumnDefinition('deployed_at',      'Deployed At',   type:'datetime',sortable:true, width:'140px', order:5),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'85px', order:6),
                new ColumnDefinition('rollback_available','Rollback',     type:'boolean', width:'70px', align:'center', order:7),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'100px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[['value'=>'success','label'=>'Success'],['value'=>'failed','label'=>'Failed'],['value'=>'rolling_back','label'=>'Rolling Back'],['value'=>'rolled_back','label'=>'Rolled Back']], order:1))
            ->addFilter(new FilterDefinition('environment', 'Env', type:'select', quick:true, options:[['value'=>'production','label'=>'Production'],['value'=>'staging','label'=>'Staging'],['value'=>'development','label'=>'Development']], order:2))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // BACKUP HISTORY — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function backupTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'epoc.backup.index',
            title: 'Backup History',
            modelClass: \App\Models\Tenant\BackupRecord::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            features: ['epoc'],
        ))
            ->addColumns([
                new ColumnDefinition('backup_name',      'Backup Name',   type:'text',    bold:true, width:'180px', order:1),
                new ColumnDefinition('backup_type',      'Type',          type:'badge',   sortable:true, filterable:true, width:'90px', order:2),
                new ColumnDefinition('size_bytes',       'Size',          type:'number',  format:'filesize', width:'80px', align:'right', order:3),
                new ColumnDefinition('encryption',       'Encryption',    type:'boolean', width:'75px', align:'center', order:4),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'85px', order:5),
                new ColumnDefinition('retention_days',   'Retention',     type:'number',  width:'70px', align:'center', order:6),
                new ColumnDefinition('created_at',       'Created',       type:'datetime',sortable:true, width:'140px', order:7),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'110px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[['value'=>'completed','label'=>'Completed'],['value'=>'in_progress','label'=>'In Progress'],['value'=>'failed','label'=>'Failed'],['value'=>'expired','label'=>'Expired']], order:1))
            ->addFilter(new FilterDefinition('backup_type', 'Type', type:'select', quick:true, options:[['value'=>'full','label'=>'Full'],['value'=>'database','label'=>'Database'],['value'=>'storage','label'=>'Storage'],['value'=>'config','label'=>'Config']], order:2))
            ->addBulkAction(new BulkAction('restore', 'Restore', variant:'primary'))
            ->addBulkAction(new BulkAction('validate', 'Validate', variant:'success'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // RECOVERY HISTORY — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function recoveryTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'epoc.recovery.index',
            title: 'Recovery History',
            modelClass: \App\Models\Tenant\RecoveryRecord::class,
            defaultSort: ['started_at' => 'desc'],
            perPage: 25,
            selectable: true,
            features: ['epoc'],
        ))
            ->addColumns([
                new ColumnDefinition('recovery_plan',    'Recovery Plan', type:'text',    bold:true, width:'150px', order:1),
                new ColumnDefinition('recovery_type',    'Type',          type:'badge',   sortable:true, width:'90px', order:2),
                new ColumnDefinition('rpo_minutes',      'RPO (min)',     type:'number',  width:'70px', align:'center', order:3),
                new ColumnDefinition('rto_minutes',      'RTO (min)',     type:'number',  width:'70px', align:'center', order:4),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'85px', order:5),
                new ColumnDefinition('started_at',       'Started',       type:'datetime',sortable:true, width:'140px', order:6),
                new ColumnDefinition('completed_at',     'Completed',     type:'datetime',width:'140px', order:7),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[['value'=>'success','label'=>'Success'],['value'=>'failed','label'=>'Failed'],['value'=>'in_progress','label'=>'In Progress']], order:1))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // SECURITY EVENTS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function securityEventsTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'epoc.security.index',
            title: 'Security Events',
            modelClass: \App\Models\Tenant\SecurityEvent::class,
            defaultSort: ['detected_at' => 'desc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            features: ['epoc'],
        ))
            ->addColumns([
                new ColumnDefinition('event_type',       'Event Type',    type:'badge',   sortable:true, filterable:true, width:'110px', order:1),
                new ColumnDefinition('severity',         'Severity',      type:'badge',   sortable:true, filterable:true, width:'80px', order:2),
                new ColumnDefinition('description',      'Description',   type:'text',    searchable:true, bold:true, order:3),
                new ColumnDefinition('source_ip',        'Source IP',     type:'text',    width:'120px', order:4),
                new ColumnDefinition('actor',            'Actor',         type:'text',    width:'100px', order:5),
                new ColumnDefinition('detected_at',      'Detected',      type:'datetime',sortable:true, width:'140px', order:6),
                new ColumnDefinition('resolution',       'Resolution',    type:'badge',   sortable:true, width:'90px', order:7),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('severity', 'Severity', type:'select', quick:true, options:[['value'=>'critical','label'=>'🔴 Critical'],['value'=>'high','label'=>'🟠 High'],['value'=>'medium','label'=>'🟡 Medium'],['value'=>'low','label'=>'🟢 Low']], order:1))
            ->addFilter(new FilterDefinition('event_type', 'Type', type:'select', quick:true, options:[['value'=>'failed_login','label'=>'Failed Login'],['value'=>'suspicious_activity','label'=>'Suspicious'],['value'=>'api_abuse','label'=>'API Abuse'],['value'=>'rate_limit','label'=>'Rate Limit'],['value'=>'token_leak','label'=>'Token Leak']], order:2))
            ->addBulkAction(new BulkAction('investigate', 'Investigate', variant:'primary'))
            ->addBulkAction(new BulkAction('resolve', 'Resolve', variant:'success'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // API METRICS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function apiMetricsTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'epoc.api_metrics.index',
            title: 'API Metrics',
            modelClass: \App\Models\Tenant\ApiMetric::class,
            defaultSort: ['recorded_at' => 'desc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            features: ['epoc'],
        ))
            ->addColumns([
                new ColumnDefinition('endpoint',         'Endpoint',      type:'text',    sortable:true, bold:true, width:'200px', order:1),
                new ColumnDefinition('method',           'Method',        type:'badge',   sortable:true, filterable:true, width:'65px', order:2),
                new ColumnDefinition('response_time_ms', 'Response (ms)', type:'number',  sortable:true, width:'95px', align:'right', order:3),
                new ColumnDefinition('status_code',      'Status',        type:'badge',   sortable:true, width:'65px', align:'center', order:4),
                new ColumnDefinition('request_count',    'Requests',      type:'number',  sortable:true, width:'75px', align:'right', order:5),
                new ColumnDefinition('error_rate_pct',   'Error %',       type:'number',  sortable:true, width:'65px', align:'right', order:6),
                new ColumnDefinition('recorded_at',      'Recorded',      type:'datetime',sortable:true, width:'140px', order:7),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('method', 'Method', type:'select', quick:true, options:[['value'=>'GET','label'=>'GET'],['value'=>'POST','label'=>'POST'],['value'=>'PUT','label'=>'PUT'],['value'=>'DELETE','label'=>'DELETE']], order:1))
            ->addFilter(new FilterDefinition('status_code', 'Status', type:'select', options:[['value'=>'2xx','label'=>'2xx Success'],['value'=>'4xx','label'=>'4xx Client Error'],['value'=>'5xx','label'=>'5xx Server Error']], order:2))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // INFRASTRUCTURE EVENTS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function infrastructureEventsTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'epoc.infra.index',
            title: 'Infrastructure Events',
            modelClass: \App\Models\Tenant\InfrastructureEvent::class,
            defaultSort: ['occurred_at' => 'desc'],
            perPage: 50,
            selectable: true,
            features: ['epoc'],
        ))
            ->addColumns([
                new ColumnDefinition('event_name',       'Event',         type:'text',    bold:true, order:1),
                new ColumnDefinition('component',        'Component',     type:'badge',   sortable:true, filterable:true, width:'100px', order:2),
                new ColumnDefinition('event_type',       'Type',          type:'badge',   sortable:true, width:'90px', order:3),
                new ColumnDefinition('severity',         'Severity',      type:'badge',   sortable:true, filterable:true, width:'80px', order:4),
                new ColumnDefinition('detail',           'Detail',        type:'text',    order:5),
                new ColumnDefinition('occurred_at',      'Occurred',      type:'datetime',sortable:true, width:'140px', order:6),
                new ColumnDefinition('acknowledged',     'Ack',           type:'boolean', width:'50px', align:'center', order:7),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('severity', 'Severity', type:'select', quick:true, options:[['value'=>'critical','label'=>'Critical'],['value'=>'warning','label'=>'Warning'],['value'=>'info','label'=>'Info']], order:1))
            ->addFilter(new FilterDefinition('component', 'Component', type:'select', quick:true, options:[['value'=>'server','label'=>'Server'],['value'=>'database','label'=>'Database'],['value'=>'redis','label'=>'Redis'],['value'=>'queue','label'=>'Queue'],['value'=>'storage','label'=>'Storage'],['value'=>'network','label'=>'Network']], order:2))
            ->addBulkAction(new BulkAction('acknowledge', 'Acknowledge', variant:'primary'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // PERFORMANCE LOGS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function performanceLogsTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'epoc.performance.index',
            title: 'Performance Logs',
            modelClass: \App\Models\Tenant\PerformanceLog::class,
            defaultSort: ['recorded_at' => 'desc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            features: ['epoc'],
        ))
            ->addColumns([
                new ColumnDefinition('query_or_route',   'Query / Route', type:'text',    bold:true, width:'250px', order:1),
                new ColumnDefinition('type',             'Type',          type:'badge',   sortable:true, filterable:true, width:'90px', order:2),
                new ColumnDefinition('duration_ms',      'Duration (ms)', type:'number',  sortable:true, width:'95px', align:'right', order:3),
                new ColumnDefinition('memory_mb',        'Memory (MB)',   type:'number',  sortable:true, width:'85px', align:'right', order:4),
                new ColumnDefinition('n_plus_one',       'N+1',           type:'boolean', width:'50px', align:'center', order:5),
                new ColumnDefinition('source',           'Source',        type:'text',    width:'120px', order:6),
                new ColumnDefinition('recorded_at',      'Recorded',      type:'datetime',sortable:true, width:'140px', order:7),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('type', 'Type', type:'select', quick:true, options:[['value'=>'slow_query','label'=>'Slow Query'],['value'=>'slow_api','label'=>'Slow API'],['value'=>'memory_spike','label'=>'Memory Spike'],['value'=>'n_plus_one','label'=>'N+1 Detected']], order:1))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES — 11 EPOC Rules
    // ═══════════════════════════════════════════════════════════

    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            (new AutomationDefinition('epoc.queue_stalled', 'Queue Stalled',
                trigger: TriggerType::THRESHOLD_BREACH, module: 'epoc'))
                ->addStep(new AutomationStep(ActionType::RESTART_QUEUE, []))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '⚠️ Queue restarted due to stall detection.', 'roles' => ['platform_admin','devops','sre']])),

            (new AutomationDefinition('epoc.worker_down', 'Worker Down',
                trigger: TriggerType::THRESHOLD_BREACH, module: 'epoc'))
                ->addStep(new AutomationStep(ActionType::RESTART_WORKER, []))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '⚠️ Worker restarted.', 'roles' => ['platform_admin','devops','sre']])),

            (new AutomationDefinition('epoc.cache_overflow', 'Cache Overflow',
                trigger: TriggerType::THRESHOLD_BREACH, module: 'epoc'))
                ->addStep(new AutomationStep(ActionType::FLUSH_CACHE, ['strategy' => 'oldest']))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '⚠️ Cache flushed due to overflow.', 'roles' => ['platform_admin','devops']])),

            (new AutomationDefinition('epoc.session_cleanup', 'Session Cleanup',
                trigger: TriggerType::SCHEDULED, module: 'epoc'))
                ->addStep(new AutomationStep(ActionType::CLEAR_SESSIONS, ['older_than_days' => 7])),

            (new AutomationDefinition('epoc.daily_backup', 'Daily Backup',
                trigger: TriggerType::SCHEDULED, module: 'epoc'))
                ->addStep(new AutomationStep(ActionType::TRIGGER_BACKUP, ['type' => 'full']))
                ->addStep(new AutomationStep(ActionType::VALIDATE_BACKUP, []))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '✅ Daily backup completed & validated.', 'roles' => ['platform_admin','devops']])),

            (new AutomationDefinition('epoc.health_degraded', 'Health Degraded',
                trigger: TriggerType::THRESHOLD_BREACH, module: 'epoc'))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '🔴 Platform health degraded. Immediate attention required.', 'roles' => ['platform_admin','devops','sre','security_engineer']]))
                ->addStep(new AutomationStep(ActionType::CREATE_INCIDENT, ['severity' => 'critical'])),

            (new AutomationDefinition('epoc.maintenance_window', 'Maintenance Window',
                trigger: TriggerType::SCHEDULED, module: 'epoc'))
                ->addStep(new AutomationStep(ActionType::TOGGLE_MAINTENANCE, ['mode' => 'on']))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '🔧 Maintenance mode activated.', 'roles' => ['platform_admin']])),

            (new AutomationDefinition('epoc.security_incident', 'Security Incident',
                trigger: TriggerType::THRESHOLD_BREACH, module: 'epoc'))
                ->addStep(new AutomationStep(ActionType::CREATE_INCIDENT, ['severity' => 'high']))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '🛡️ Security incident detected: {{subject.event_type}}.', 'roles' => ['security_engineer','platform_admin']])),

            (new AutomationDefinition('epoc.recovery_workflow', 'Recovery Workflow',
                trigger: TriggerType::MANUAL, module: 'epoc'))
                ->addStep(new AutomationStep(ActionType::EXECUTE_RECOVERY, ['plan' => '{{subject.recovery_plan}}']))
                ->addStep(new AutomationStep(ActionType::VALIDATE_RECOVERY, []))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '🏥 Recovery workflow completed.', 'roles' => ['platform_admin','devops','sre']])),

            (new AutomationDefinition('epoc.auto_escalation', 'Auto Escalation',
                trigger: TriggerType::THRESHOLD_BREACH, module: 'epoc'))
                ->addStep(new AutomationStep(ActionType::ESCALATE, ['target_role' => 'platform_admin']))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '🚨 Auto-escalated: {{subject.event_name}}.', 'roles' => ['platform_admin']])),

            (new AutomationDefinition('epoc.daily_ops_report', 'Daily Operations Report',
                trigger: TriggerType::SCHEDULED, module: 'epoc'))
                ->addStep(new AutomationStep(ActionType::GENERATE_REPORT, ['report' => 'executive_operations_dashboard']))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '📊 Daily operations report ready.', 'roles' => ['platform_admin','devops','sre']])),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // REPORTING DEFINITIONS — 12 EPOC Reports
    // ═══════════════════════════════════════════════════════════

    /** @return ReportDefinition[] */
    public static function reports(): array
    {
        return [
            (new ReportDefinition('epoc.platform_health', 'Platform Health',
                type:'summary', chartType:'kpi', features:['epoc']))
                ->addMetric(new MetricDefinition('health_score', 'Health Score', 'last', 'health_score', format:'number', color:'primary', icon:'💚'))
                ->addMetric(new MetricDefinition('uptime_pct', 'Uptime %', 'last', 'uptime_pct', format:'number', color:'success', icon:'⏱️'))
                ->addMetric(new MetricDefinition('cpu_avg', 'CPU Avg %', 'avg', 'cpu_pct', format:'number', color:'info', icon:'⚡'))
                ->addMetric(new MetricDefinition('memory_avg', 'Memory Avg %', 'avg', 'memory_pct', format:'number', color:'info', icon:'🧠')),

            (new ReportDefinition('epoc.performance_report', 'Performance Report',
                type:'summary', chartType:'table', features:['epoc']))
                ->addMetric(new MetricDefinition('slow_queries', 'Slow Queries', 'count', 'id', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('slow_apis', 'Slow APIs', 'count', 'id', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('n_plus_one', 'N+1 Detected', 'count', 'n_plus_one', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('type', 'Type', 'type', type:'string')),

            (new ReportDefinition('epoc.queue_report', 'Queue Report',
                type:'summary', chartType:'table', features:['epoc']))
                ->addMetric(new MetricDefinition('pending', 'Pending', 'count', 'pending', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('running', 'Running', 'count', 'running', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('failed', 'Failed', 'count', 'failed', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('completed', 'Completed', 'count', 'completed', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('queue', 'Queue', 'queue', type:'string')),

            (new ReportDefinition('epoc.deployment_report', 'Deployment Report',
                type:'summary', chartType:'table', features:['epoc']))
                ->addMetric(new MetricDefinition('deployments', 'Deployments', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('success_rate', 'Success Rate %', 'avg', 'success_rate', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('rollbacks', 'Rollbacks', 'count', 'rollbacks', format:'number', color:'warning'))
                ->addDimension(new DimensionDefinition('environment', 'Env', 'environment', type:'string')),

            (new ReportDefinition('epoc.backup_report', 'Backup Report',
                type:'summary', chartType:'table', features:['epoc']))
                ->addMetric(new MetricDefinition('total_backups', 'Backups', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('total_size', 'Total Size', 'sum', 'size_bytes', format:'filesize', color:'info'))
                ->addMetric(new MetricDefinition('validated', 'Validated', 'count', 'validated', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('type', 'Type', 'backup_type', type:'string')),

            (new ReportDefinition('epoc.recovery_report', 'Recovery Report',
                type:'summary', chartType:'kpi', features:['epoc']))
                ->addMetric(new MetricDefinition('rpo_avg', 'Avg RPO (min)', 'avg', 'rpo_minutes', format:'number', color:'warning', icon:'📉'))
                ->addMetric(new MetricDefinition('rto_avg', 'Avg RTO (min)', 'avg', 'rto_minutes', format:'number', color:'info', icon:'⏱️'))
                ->addMetric(new MetricDefinition('success_rate', 'Success Rate %', 'avg', 'success_rate', format:'number', color:'success', icon:'✅')),

            (new ReportDefinition('epoc.security_report', 'Security Report',
                type:'summary', chartType:'bar', features:['epoc'], permissions:['manage_platform']))
                ->addMetric(new MetricDefinition('events', 'Events', 'count', 'id', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('resolved', 'Resolved', 'count', 'resolved', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('false_positive', 'False Positive', 'count', 'false_positive', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('type', 'Type', 'event_type', type:'string')),

            (new ReportDefinition('epoc.api_report', 'API Report',
                type:'summary', chartType:'table', features:['epoc']))
                ->addMetric(new MetricDefinition('total_requests', 'Requests', 'count', 'request_count', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('avg_response_ms', 'Avg Response', 'avg', 'response_time_ms', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('error_rate', 'Error Rate %', 'avg', 'error_rate_pct', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('endpoint', 'Endpoint', 'endpoint', type:'string')),

            (new ReportDefinition('epoc.infrastructure_capacity', 'Infrastructure Capacity',
                type:'summary', chartType:'bar', features:['epoc']))
                ->addMetric(new MetricDefinition('cpu_used', 'CPU Used %', 'avg', 'cpu_pct', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('memory_used', 'Memory Used %', 'avg', 'memory_pct', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('storage_used', 'Storage Used %', 'avg', 'storage_pct', format:'number', color:'warning'))
                ->addDimension(new DimensionDefinition('component', 'Component', 'component', type:'string')),

            (new ReportDefinition('epoc.sla_report', 'SLA Report',
                type:'summary', chartType:'kpi', features:['epoc']))
                ->addMetric(new MetricDefinition('uptime_sla', 'Uptime SLA %', 'last', 'uptime_sla_pct', format:'number', color:'success', icon:'✅'))
                ->addMetric(new MetricDefinition('api_sla', 'API SLA %', 'last', 'api_sla_pct', format:'number', color:'success', icon:'🔌'))
                ->addMetric(new MetricDefinition('backup_sla', 'Backup SLA %', 'last', 'backup_sla_pct', format:'number', color:'success', icon:'💿'))
                ->addMetric(new MetricDefinition('incident_sla', 'Incident SLA %', 'last', 'incident_sla_pct', format:'number', color:'warning', icon:'🚨')),

            (new ReportDefinition('epoc.ai_ops_insight', 'AI Operations Insight',
                type:'summary', chartType:'table', features:['epoc']))
                ->addMetric(new MetricDefinition('predictions', 'Predictions', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('accuracy', 'Accuracy %', 'avg', 'accuracy_pct', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('insight_type', 'Type', 'insight_type', type:'string')),

            (new ReportDefinition('epoc.executive_ops_dashboard', 'Executive Operations Dashboard',
                type:'summary', chartType:'kpi', features:['epoc']))
                ->addMetric(new MetricDefinition('health_score', 'Health', 'last', 'health_score', format:'number', color:'primary', icon:'💚'))
                ->addMetric(new MetricDefinition('deployments', 'Deployments', 'count', 'deployments', format:'number', color:'info', icon:'🚀'))
                ->addMetric(new MetricDefinition('open_incidents', 'Open Incidents', 'count', 'open_incidents', format:'number', color:'danger', icon:'🚨'))
                ->addMetric(new MetricDefinition('cost_efficiency', 'Cost Score', 'last', 'cost_efficiency_score', format:'number', color:'success', icon:'💰')),
        ];
    }
}
