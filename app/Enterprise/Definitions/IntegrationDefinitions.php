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
 * IntegrationDefinitions — ALL Enterprise definitions for Integration Hub, API Gateway & External Ecosystem.
 * 
 * Covers: API Gateway, Webhook Engine, Marketplace Connectors, Payment Gateway,
 * Shipping, Communication Hub, AI Providers, File Storage, SSO, Developer Portal, Monitoring.
 * 
 * MODUL ERP KEEMPAT BELAS — ENTERPRISE INTEGRATION HUB, API GATEWAY & EXTERNAL ECOSYSTEM
 * 
 * ⚠️ Integration Hub is the UNIVERSAL CONNECTION FABRIC — not a standalone engine.
 */
class IntegrationDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // INTEGRATION WORKSPACE (16 tabs)
    // ═══════════════════════════════════════════════════════════

    public static function workspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'integration',
            title: 'Integration Hub',
            icon: '🔌',
            tabs: [
                ['id' => 'overview',        'label' => 'Overview',         'icon' => '📊'],
                ['id' => 'api_gateway',     'label' => 'API Gateway',      'icon' => '🌐'],
                ['id' => 'integrations',    'label' => 'Integrations',     'icon' => '🔗'],
                ['id' => 'webhooks',        'label' => 'Webhooks',         'icon' => '🪝'],
                ['id' => 'api_keys',        'label' => 'API Keys',         'icon' => '🔑'],
                ['id' => 'oauth',           'label' => 'OAuth Clients',    'icon' => '🔐'],
                ['id' => 'marketplace',     'label' => 'Marketplace',      'icon' => '🛍️'],
                ['id' => 'payment_gateway', 'label' => 'Payment Gateway',  'icon' => '💳'],
                ['id' => 'shipping',        'label' => 'Shipping',         'icon' => '🚚'],
                ['id' => 'messaging',       'label' => 'Messaging',        'icon' => '💬'],
                ['id' => 'email',           'label' => 'Email',            'icon' => '📧'],
                ['id' => 'ai_providers',    'label' => 'AI Providers',     'icon' => '🤖'],
                ['id' => 'logs',            'label' => 'Logs',             'icon' => '📜'],
                ['id' => 'monitoring',      'label' => 'Monitoring',       'icon' => '📡'],
                ['id' => 'developer',       'label' => 'Developer Portal', 'icon' => '👨‍💻'],
                ['id' => 'audit',           'label' => 'Audit Trail',      'icon' => '🛡️'],
            ],
            actions: [
                ['id' => 'create_api_key',    'label' => 'Create API Key',    'roles' => ['owner','super_admin','admin']],
                ['id' => 'register_webhook',  'label' => 'Register Webhook',  'roles' => ['owner','super_admin','admin','developer']],
                ['id' => 'add_connector',     'label' => 'Add Connector',     'roles' => ['owner','super_admin','admin']],
                ['id' => 'test_connection',   'label' => 'Test Connection',   'roles' => ['owner','super_admin','admin','developer']],
                ['id' => 'view_logs',         'label' => 'View Logs',         'roles' => ['owner','super_admin','admin','developer']],
                ['id' => 'rotate_secret',     'label' => 'Rotate Secret',     'roles' => ['owner','super_admin']],
                ['id' => 'generate_swagger',  'label' => 'Generate Swagger',  'roles' => ['owner','super_admin','admin','developer']],
                ['id' => 'export',            'label' => 'Export',            'roles' => ['owner','super_admin','admin','developer']],
            ],
            sidebarWidgets: [
                ['id' => 'integration_health',  'component' => 'IntegrationHealth', 'priority' => 10],
                ['id' => 'quick_actions',       'component' => 'QuickActions',       'priority' => 20],
                ['id' => 'connector_status',    'component' => 'ConnectorStatus',    'priority' => 30],
                ['id' => 'live_metrics',        'component' => 'LiveMetrics',        'priority' => 40],
            ],
            features: ['integration'],
            permissions: ['manage_integration'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // API KEYS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function apiKeyTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'integration.api_key.index',
            title: 'API Keys',
            modelClass: \App\Models\Tenant\ApiKey::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            features: ['integration'],
        ))
            ->addColumns([
                new ColumnDefinition('key_name',         'Name',          type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('key_prefix',       'Key',           type:'text',    bold:true, width:'130px', order:2),
                new ColumnDefinition('scopes',           'Scopes',        type:'tags',    width:'150px', order:3),
                new ColumnDefinition('ip_whitelist',     'IP Whitelist',  type:'text',    width:'130px', order:4),
                new ColumnDefinition('rate_limit',       'Rate Limit',    type:'number',  width:'80px', align:'center', order:5),
                new ColumnDefinition('usage_count',      'Usage',         type:'number',  sortable:true, width:'70px', align:'center', order:6),
                new ColumnDefinition('last_used_at',     'Last Used',     type:'datetime',width:'130px', order:7),
                new ColumnDefinition('expires_at',       'Expires',       type:'date',    sortable:true, width:'100px', order:8),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'80px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'active','label'=>'Active'],
                ['value'=>'revoked','label'=>'Revoked'],
                ['value'=>'expired','label'=>'Expired'],
            ], order:1))
            ->addBulkAction(new BulkAction('revoke', 'Revoke', variant:'danger', confirm:true))
            ->addBulkAction(new BulkAction('rotate', 'Rotate Secret', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // WEBHOOKS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function webhookTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'integration.webhook.index',
            title: 'Webhook Registry',
            modelClass: \App\Models\Tenant\Webhook::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            features: ['integration'],
        ))
            ->addColumns([
                new ColumnDefinition('webhook_name',     'Name',          type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('url',              'URL',           type:'text',    width:'200px', order:2),
                new ColumnDefinition('events',           'Events',        type:'tags',    width:'150px', order:3),
                new ColumnDefinition('direction',        'Dir',           type:'badge',   width:'70px', order:4),
                new ColumnDefinition('delivery_count',   'Delivered',     type:'number',  sortable:true, width:'70px', align:'center', order:5),
                new ColumnDefinition('failed_count',     'Failed',        type:'number',  sortable:true, width:'60px', align:'center', order:6),
                new ColumnDefinition('success_rate',     'Success %',     type:'number',  sortable:true, width:'70px', align:'center', order:7),
                new ColumnDefinition('last_delivery_at', 'Last Delivery', type:'datetime',width:'130px', order:8),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'80px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'active','label'=>'Active'],
                ['value'=>'paused','label'=>'Paused'],
                ['value'=>'failing','label'=>'Failing'],
                ['value'=>'disabled','label'=>'Disabled'],
            ], order:1))
            ->addFilter(new FilterDefinition('direction', 'Direction', type:'select', options:[['value'=>'incoming','label'=>'Incoming'],['value'=>'outgoing','label'=>'Outgoing']], order:2))
            ->addBulkAction(new BulkAction('pause', 'Pause', variant:'default'))
            ->addBulkAction(new BulkAction('retry_failed', 'Retry Failed', variant:'primary'))
            ->addBulkAction(new BulkAction('delete', 'Delete', variant:'danger', confirm:true));
    }

    // ═══════════════════════════════════════════════════════════
    // MARKETPLACE CONNECTORS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function connectorTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'integration.connector.index',
            title: 'Connectors Registry',
            modelClass: \App\Models\Tenant\Connector::class,
            defaultSort: ['connector_type' => 'asc', 'connector_name' => 'asc'],
            perPage: 50,
            selectable: true,
            features: ['integration'],
        ))
            ->addColumns([
                new ColumnDefinition('connector_name',   'Connector',     type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('connector_type',   'Type',          type:'badge',   sortable:true, filterable:true, width:'110px', order:2),
                new ColumnDefinition('provider',         'Provider',      type:'text',    sortable:true, width:'120px', order:3),
                new ColumnDefinition('last_sync_at',     'Last Sync',     type:'datetime',sortable:true, width:'130px', order:4),
                new ColumnDefinition('sync_status',      'Sync',          type:'badge',   sortable:true, width:'90px', order:5),
                new ColumnDefinition('error_count_24h',  'Errors (24h)',  type:'number',  sortable:true, width:'80px', align:'center', order:6),
                new ColumnDefinition('health_status',    'Health',        type:'badge',   sortable:true, width:'80px', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, width:'80px', order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('connector_type', 'Type', type:'select', quick:true, options:[
                ['value'=>'marketplace','label'=>'Marketplace'],
                ['value'=>'payment','label'=>'Payment Gateway'],
                ['value'=>'shipping','label'=>'Shipping'],
                ['value'=>'messaging','label'=>'Messaging'],
                ['value'=>'email','label'=>'Email'],
                ['value'=>'ai','label'=>'AI Provider'],
                ['value'=>'storage','label'=>'File Storage'],
                ['value'=>'sso','label'=>'SSO'],
            ], order:1))
            ->addFilter(new FilterDefinition('health_status', 'Health', type:'select', quick:true, options:[['value'=>'healthy','label'=>'✅ Healthy'],['value'=>'degraded','label'=>'⚠️ Degraded'],['value'=>'down','label'=>'❌ Down']], order:2))
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', options:[['value'=>'active','label'=>'Active'],['value'=>'inactive','label'=>'Inactive']], order:3))
            ->addBulkAction(new BulkAction('sync', 'Trigger Sync', variant:'primary'))
            ->addBulkAction(new BulkAction('test', 'Test Connection', variant:'default'))
            ->addBulkAction(new BulkAction('deactivate', 'Deactivate', variant:'warning'));
    }

    // ═══════════════════════════════════════════════════════════
    // API LOGS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function apiLogTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'integration.api_log.index',
            title: 'API Request Logs',
            modelClass: \App\Models\Tenant\ApiLog::class,
            defaultSort: ['requested_at' => 'desc'],
            perPage: 50,
            selectable: true,
            features: ['integration'],
        ))
            ->addColumns([
                new ColumnDefinition('request_method',   'Method',        type:'badge',   width:'60px', order:1),
                new ColumnDefinition('endpoint',         'Endpoint',      type:'text',    sortable:true, searchable:true, bold:true, width:'200px', order:2),
                new ColumnDefinition('api_key_name',     'Key',           type:'text',    width:'120px', order:3),
                new ColumnDefinition('status_code',      'Status',        type:'badge',   sortable:true, width:'70px', align:'center', order:4),
                new ColumnDefinition('response_time_ms', 'Latency (ms)',  type:'number',  sortable:true, width:'90px', align:'right', order:5),
                new ColumnDefinition('ip_address',       'IP',            type:'text',    width:'120px', order:6),
                new ColumnDefinition('user_agent',       'User Agent',    type:'text',    width:'150px', order:7),
                new ColumnDefinition('requested_at',     'Time',          type:'datetime',sortable:true, width:'130px', order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status_code', 'Status', type:'select', quick:true, options:[
                ['value'=>'200','label'=>'200 OK'],
                ['value'=>'401','label'=>'401 Unauthorized'],
                ['value'=>'403','label'=>'403 Forbidden'],
                ['value'=>'429','label'=>'429 Rate Limit'],
                ['value'=>'500','label'=>'500 Server Error'],
            ], order:1))
            ->addFilter(new FilterDefinition('request_method', 'Method', type:'select', options:[['value'=>'GET','label'=>'GET'],['value'=>'POST','label'=>'POST'],['value'=>'PUT','label'=>'PUT'],['value'=>'DELETE','label'=>'DELETE']], order:2))
            ->addFilter(new FilterDefinition('requested_at', 'Date', type:'date_range', order:3))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // OAUTH CLIENTS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function oauthTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'integration.oauth.index',
            title: 'OAuth Clients',
            modelClass: \App\Models\Tenant\OAuthClient::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            features: ['integration'],
        ))
            ->addColumns([
                new ColumnDefinition('client_name',      'Client Name',   type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('client_id',        'Client ID',     type:'text',    width:'140px', order:2),
                new ColumnDefinition('grant_type',       'Grant Type',    type:'badge',   sortable:true, width:'110px', order:3),
                new ColumnDefinition('redirect_uri',     'Redirect URI',  type:'text',    width:'180px', order:4),
                new ColumnDefinition('scopes',           'Scopes',        type:'tags',    width:'150px', order:5),
                new ColumnDefinition('token_count',      'Active Tokens', type:'number',  width:'90px', align:'center', order:6),
                new ColumnDefinition('last_used_at',     'Last Used',     type:'datetime',width:'130px', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, width:'80px', order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[['value'=>'active','label'=>'Active'],['value'=>'revoked','label'=>'Revoked']], order:1))
            ->addFilter(new FilterDefinition('grant_type', 'Grant Type', type:'select', options:[['value'=>'client_credentials','label'=>'Client Credentials'],['value'=>'authorization_code','label'=>'Auth Code'],['value'=>'personal_access','label'=>'Personal Access']], order:2))
            ->addBulkAction(new BulkAction('revoke', 'Revoke', variant:'danger', confirm:true))
            ->addBulkAction(new BulkAction('revoke_tokens', 'Revoke All Tokens', variant:'danger', confirm:true));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES — 15 Integration Rules
    // ═══════════════════════════════════════════════════════════

    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            (new AutomationDefinition('integration.sync_marketplace', 'Marketplace Sync',
                trigger: TriggerType::SCHEDULED, module: 'integration'))
                ->addStep(new AutomationStep(ActionType::SYNC_MARKETPLACE_ORDERS, []))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '🛍️ Marketplace orders synced.'])),

            (new AutomationDefinition('integration.sync_inventory', 'Inventory Sync',
                trigger: TriggerType::SCHEDULED, module: 'integration'))
                ->addStep(new AutomationStep(ActionType::SYNC_INVENTORY, ['target' => 'all_platforms'])),

            (new AutomationDefinition('integration.sync_price', 'Price Sync',
                trigger: TriggerType::RECORD_UPDATED, module: 'integration'))
                ->addStep(new AutomationStep(ActionType::SYNC_PRICE, ['target' => 'all_platforms'])),

            (new AutomationDefinition('integration.sync_customer', 'Customer Sync',
                trigger: TriggerType::RECORD_UPDATED, module: 'integration'))
                ->addStep(new AutomationStep(ActionType::SYNC_CUSTOMER, [])),

            (new AutomationDefinition('integration.sync_order', 'Order Sync',
                trigger: TriggerType::RECORD_CREATED, module: 'integration'))
                ->addStep(new AutomationStep(ActionType::SYNC_ORDER_STATUS, [])),

            (new AutomationDefinition('integration.webhook_retry', 'Webhook Retry',
                trigger: TriggerType::RECORD_UPDATED, module: 'integration'))
                ->addStep(new AutomationStep(ActionType::RETRY_WEBHOOK, ['max_retries' => 3, 'backoff' => 'exponential'])),

            (new AutomationDefinition('integration.payment_callback', 'Payment Callback',
                trigger: TriggerType::RECORD_CREATED, module: 'integration'))
                ->addStep(new AutomationStep(ActionType::PROCESS_PAYMENT_CALLBACK, []))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '💳 Payment callback processed.'])),

            (new AutomationDefinition('integration.shipment_tracking', 'Shipment Tracking',
                trigger: TriggerType::SCHEDULED, module: 'integration'))
                ->addStep(new AutomationStep(ActionType::UPDATE_TRACKING, [])),

            (new AutomationDefinition('integration.notification_retry', 'Notification Retry',
                trigger: TriggerType::RECORD_UPDATED, module: 'integration'))
                ->addStep(new AutomationStep(ActionType::RETRY_NOTIFICATION, ['max_retries' => 3])),

            (new AutomationDefinition('integration.api_health_alert', 'API Health Alert',
                trigger: TriggerType::RECORD_UPDATED, module: 'integration'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🔌 API Health Alert', 'body' => '{{subject.endpoint}} returned {{subject.status_code}}.', 'roles' => ['super_admin', 'admin', 'developer']])),

            (new AutomationDefinition('integration.connector_failure', 'Connector Failure',
                trigger: TriggerType::RECORD_UPDATED, module: 'integration'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '⚠️ Connector Failure', 'body' => '{{subject.connector_name}} is down.', 'roles' => ['super_admin', 'admin']])),

            (new AutomationDefinition('integration.token_expiry', 'Token Expiry',
                trigger: TriggerType::DATE_REACHED, module: 'integration'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'Renew: {{subject.key_name}}', 'assignee_role' => 'admin'])),

            (new AutomationDefinition('integration.ssl_expiry', 'SSL Expiry',
                trigger: TriggerType::DATE_REACHED, module: 'integration'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🔒 SSL Expiring', 'body' => 'SSL certificate expires in {{subject.days_remaining}} days.', 'roles' => ['super_admin']])),

            (new AutomationDefinition('integration.backup_sync', 'Backup Sync',
                trigger: TriggerType::SCHEDULED, module: 'integration'))
                ->addStep(new AutomationStep(ActionType::SYNC_BACKUP, ['target' => 's3_remote'])),

            (new AutomationDefinition('integration.external_alert', 'External Alert',
                trigger: TriggerType::RECORD_CREATED, module: 'integration'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '📡 External Alert', 'body' => '{{subject.message}}', 'roles' => ['super_admin', 'admin']])),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // REPORTING DEFINITIONS — 15 Integration Reports
    // ═══════════════════════════════════════════════════════════

    /** @return ReportDefinition[] */
    public static function reports(): array
    {
        return [
            (new ReportDefinition('integration.api_usage', 'API Usage',
                type:'summary', chartType:'bar', features:['integration']))
                ->addMetric(new MetricDefinition('request_count', 'Requests', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('avg_latency_ms', 'Avg Latency (ms)', 'avg', 'response_time_ms', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('error_rate', 'Error %', 'expression', 'errors / requests * 100', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('endpoint', 'Endpoint', 'endpoint', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            (new ReportDefinition('integration.webhook_performance', 'Webhook Performance',
                type:'summary', chartType:'table', features:['integration']))
                ->addMetric(new MetricDefinition('delivered', 'Delivered', 'sum', 'delivery_count', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('failed', 'Failed', 'sum', 'failed_count', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('success_rate', 'Success %', 'avg', 'success_rate', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('webhook', 'Webhook', 'webhook_name', type:'string')),

            (new ReportDefinition('integration.connector_health', 'Connector Health',
                type:'summary', chartType:'table', features:['integration']))
                ->addMetric(new MetricDefinition('healthy', 'Healthy', 'count', 'healthy', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('degraded', 'Degraded', 'count', 'degraded', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('down', 'Down', 'count', 'down', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('type', 'Type', 'connector_type', type:'string')),

            (new ReportDefinition('integration.marketplace_analytics', 'Marketplace Analytics',
                type:'summary', chartType:'bar', features:['integration']))
                ->addMetric(new MetricDefinition('orders', 'Orders', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('revenue', 'Revenue', 'sum', 'order_total', format:'currency', color:'success'))
                ->addDimension(new DimensionDefinition('platform', 'Platform', 'provider', type:'string')),

            (new ReportDefinition('integration.payment_analytics', 'Payment Analytics',
                type:'summary', chartType:'table', features:['integration']))
                ->addMetric(new MetricDefinition('transactions', 'Transactions', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('total_amount', 'Total', 'sum', 'amount', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('success_rate', 'Success %', 'avg', 'success_pct', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('gateway', 'Gateway', 'provider', type:'string')),

            (new ReportDefinition('integration.shipping_analytics', 'Shipping Analytics',
                type:'summary', chartType:'table', features:['integration']))
                ->addMetric(new MetricDefinition('shipments', 'Shipments', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('avg_delivery_days', 'Avg Delivery (d)', 'avg', 'delivery_days', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('carrier', 'Carrier', 'provider', type:'string')),

            (new ReportDefinition('integration.communication_analytics', 'Communication Analytics',
                type:'summary', chartType:'bar', features:['integration']))
                ->addMetric(new MetricDefinition('sent', 'Sent', 'count', 'sent', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('delivered', 'Delivered', 'count', 'delivered', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('failed', 'Failed', 'count', 'failed', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('channel', 'Channel', 'channel', type:'string')),

            (new ReportDefinition('integration.ai_provider_usage', 'AI Provider Usage',
                type:'summary', chartType:'bar', features:['integration']))
                ->addMetric(new MetricDefinition('tokens_used', 'Tokens', 'sum', 'tokens', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('cost', 'Cost', 'sum', 'cost', format:'currency', color:'warning'))
                ->addMetric(new MetricDefinition('avg_latency_ms', 'Avg Latency', 'avg', 'latency_ms', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('provider', 'Provider', 'provider', type:'string')),

            (new ReportDefinition('integration.performance', 'Integration Performance',
                type:'summary', chartType:'kpi', features:['integration']))
                ->addMetric(new MetricDefinition('total_requests', 'Total Requests', 'count', 'id', format:'number', color:'primary', icon:'🌐'))
                ->addMetric(new MetricDefinition('avg_latency', 'Avg Latency (ms)', 'avg', 'response_time_ms', format:'number', color:'info', icon:'⚡'))
                ->addMetric(new MetricDefinition('uptime_pct', 'Uptime %', 'last', 'uptime_pct', format:'number', color:'success', icon:'✅'))
                ->addMetric(new MetricDefinition('active_connectors', 'Active Connectors', 'count', 'active', format:'number', color:'primary', icon:'🔌')),

            (new ReportDefinition('integration.security_events', 'Security Events',
                type:'summary', chartType:'table', features:['integration']))
                ->addMetric(new MetricDefinition('events', 'Events', 'count', 'id', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('type', 'Type', 'event_type', type:'string'))
                ->addDimension(new DimensionDefinition('severity', 'Severity', 'severity', type:'string')),

            (new ReportDefinition('integration.api_errors', 'API Errors',
                type:'summary', chartType:'bar', features:['integration']))
                ->addMetric(new MetricDefinition('error_count', 'Errors', 'count', 'id', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('status_code', 'Status Code', 'status_code', type:'string'))
                ->addDimension(new DimensionDefinition('endpoint', 'Endpoint', 'endpoint', type:'string')),

            (new ReportDefinition('integration.webhook_errors', 'Webhook Errors',
                type:'summary', chartType:'table', features:['integration']))
                ->addMetric(new MetricDefinition('failed', 'Failed', 'count', 'id', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('in_dlq', 'In Dead Letter Queue', 'count', 'dlq', format:'number', color:'warning'))
                ->addDimension(new DimensionDefinition('webhook', 'Webhook', 'webhook_name', type:'string')),

            (new ReportDefinition('integration.external_services', 'External Services',
                type:'summary', chartType:'table', features:['integration']))
                ->addMetric(new MetricDefinition('active', 'Active', 'count', 'active', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('total_calls', 'Total Calls', 'sum', 'calls', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('avg_response_ms', 'Avg Response', 'avg', 'response_ms', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('service', 'Service', 'provider', type:'string')),

            (new ReportDefinition('integration.developer_usage', 'Developer Usage',
                type:'summary', chartType:'table', features:['integration']))
                ->addMetric(new MetricDefinition('api_calls', 'API Calls', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('swagger_views', 'Swagger Views', 'count', 'views', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('developer', 'Developer', 'developer_name', type:'string')),

            (new ReportDefinition('integration.enterprise_score', 'Enterprise Integration Score',
                type:'summary', chartType:'kpi', features:['integration']))
                ->addMetric(new MetricDefinition('api_health', 'API Health', 'last', 'api_health_score', format:'number', color:'primary', icon:'🌐'))
                ->addMetric(new MetricDefinition('connector_coverage', 'Connector Coverage %', 'last', 'connector_pct', format:'number', color:'success', icon:'🔌'))
                ->addMetric(new MetricDefinition('webhook_reliability', 'Webhook Reliability %', 'last', 'webhook_reliability', format:'number', color:'info', icon:'🪝'))
                ->addMetric(new MetricDefinition('overall', 'Overall Score', 'expression', '(api_health + connector_coverage + webhook_reliability) / 3', format:'number', color:'primary', icon:'🏆')),
        ];
    }
}
