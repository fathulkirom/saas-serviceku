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
 * NotificationDefinitions — ALL Enterprise definitions for Notification & Communication Center.
 * 
 * Covers: WhatsApp Center, Email Center, SMS Center, Push Notification,
 * Internal Communication, Broadcast, Campaign, Templates, Customer Journey,
 * Delivery Reports, Channel Management.
 * 
 * MODUL ERP KETUJUH BELAS — ENTERPRISE NOTIFICATION & COMMUNICATION CENTER
 * 
 * ⚠️ ALL modules MUST route through this Notification Hub. No direct sending.
 */
class NotificationDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // NOTIFICATION WORKSPACE (16 tabs)
    // ═══════════════════════════════════════════════════════════

    public static function workspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'notification',
            title: 'Notification & Communication Center',
            icon: '🔔',
            tabs: [
                ['id' => 'overview',            'label' => 'Overview',              'icon' => '📊'],
                ['id' => 'queue',               'label' => 'Notification Queue',    'icon' => '📬'],
                ['id' => 'whatsapp',            'label' => 'WhatsApp',              'icon' => '💬'],
                ['id' => 'email',               'label' => 'Email',                 'icon' => '📧'],
                ['id' => 'sms',                 'label' => 'SMS',                   'icon' => '📱'],
                ['id' => 'push',                'label' => 'Push Notification',     'icon' => '📲'],
                ['id' => 'inbox',               'label' => 'Internal Inbox',        'icon' => '📥'],
                ['id' => 'broadcast',           'label' => 'Broadcast',             'icon' => '📢'],
                ['id' => 'campaign',            'label' => 'Campaign',              'icon' => '🎯'],
                ['id' => 'templates',           'label' => 'Templates',             'icon' => '📝'],
                ['id' => 'automation_messages', 'label' => 'Automation Messages',   'icon' => '⚡'],
                ['id' => 'delivery',            'label' => 'Delivery Report',       'icon' => '✅'],
                ['id' => 'failed',              'label' => 'Failed Messages',       'icon' => '❌'],
                ['id' => 'channels',            'label' => 'Channels',              'icon' => '🔌'],
                ['id' => 'analytics',           'label' => 'Analytics',             'icon' => '📈'],
                ['id' => 'audit',               'label' => 'Audit Log',             'icon' => '🛡️'],
            ],
            actions: [
                ['id' => 'send_message',       'label' => 'Send Message',       'roles' => ['owner','admin','cs','marketing']],
                ['id' => 'create_template',    'label' => 'Create Template',    'roles' => ['owner','admin','marketing']],
                ['id' => 'create_campaign',    'label' => 'Create Campaign',    'roles' => ['owner','admin','marketing']],
                ['id' => 'broadcast',          'label' => 'Send Broadcast',     'roles' => ['owner','admin','marketing']],
                ['id' => 'retry_failed',       'label' => 'Retry Failed',       'roles' => ['owner','admin']],
                ['id' => 'configure_channel',  'label' => 'Configure Channel',  'roles' => ['owner','super_admin','admin']],
                ['id' => 'view_analytics',     'label' => 'View Analytics',     'roles' => ['owner','admin','marketing','manager']],
                ['id' => 'export',             'label' => 'Export Report',      'roles' => ['owner','admin']],
            ],
            sidebarWidgets: [
                ['id' => 'queue_summary',      'component' => 'QueueSummary',     'priority' => 10],
                ['id' => 'channel_status',     'component' => 'ChannelStatus',    'priority' => 20],
                ['id' => 'delivery_rate',      'component' => 'DeliveryRate',     'priority' => 30],
                ['id' => 'quick_actions',      'component' => 'QuickActions',     'priority' => 40],
            ],
            features: ['notifications'],
            permissions: ['manage_notifications'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // NOTIFICATION QUEUE — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function queueTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'notification.queue.index',
            title: 'Notification Queue',
            modelClass: \App\Models\Tenant\Notification::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['notifications'],
        ))
            ->addColumns([
                new ColumnDefinition('channel',          'Channel',       type:'badge',   sortable:true, filterable:true, width:'80px', order:1),
                new ColumnDefinition('recipient',        'Recipient',     type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('subject',          'Subject',       type:'text',    searchable:true, order:3),
                new ColumnDefinition('module_source',    'Source',        type:'badge',   sortable:true, width:'90px', order:4),
                new ColumnDefinition('priority',         'Priority',      type:'badge',   sortable:true, width:'70px', order:5),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'85px', order:6),
                new ColumnDefinition('attempt_count',    'Attempts',      type:'number',  width:'60px', align:'center', order:7),
                new ColumnDefinition('scheduled_at',     'Scheduled',     type:'datetime',sortable:true, width:'130px', order:8),
                new ColumnDefinition('delivered_at',     'Delivered',     type:'datetime',sortable:true, width:'130px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'queued','label'=>'Queued'],
                ['value'=>'sending','label'=>'Sending'],
                ['value'=>'delivered','label'=>'Delivered'],
                ['value'=>'failed','label'=>'Failed'],
                ['value'=>'read','label'=>'Read'],
                ['value'=>'cancelled','label'=>'Cancelled'],
            ], order:1))
            ->addFilter(new FilterDefinition('channel', 'Channel', type:'select', quick:true, options:[
                ['value'=>'whatsapp','label'=>'WhatsApp'],
                ['value'=>'email','label'=>'Email'],
                ['value'=>'sms','label'=>'SMS'],
                ['value'=>'push','label'=>'Push'],
                ['value'=>'internal','label'=>'Internal'],
            ], order:2))
            ->addFilter(new FilterDefinition('priority', 'Priority', type:'select', options:[['value'=>'critical','label'=>'Critical'],['value'=>'high','label'=>'High'],['value'=>'normal','label'=>'Normal'],['value'=>'low','label'=>'Low']], order:3))
            ->addFilter(new FilterDefinition('created_at', 'Date', type:'date_range', order:4))
            ->addBulkAction(new BulkAction('retry', 'Retry', variant:'primary'))
            ->addBulkAction(new BulkAction('cancel', 'Cancel', variant:'danger', confirm:true))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // TEMPLATES — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function templateTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'notification.template.index',
            title: 'Message Templates',
            modelClass: \App\Models\Tenant\NotificationTemplate::class,
            defaultSort: ['category' => 'asc', 'template_name' => 'asc'],
            perPage: 25,
            selectable: true,
            features: ['notifications'],
        ))
            ->addColumns([
                new ColumnDefinition('template_name',    'Template Name',  type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('category',         'Category',       type:'badge',   sortable:true, filterable:true, width:'90px', order:2),
                new ColumnDefinition('channel',          'Channel',        type:'badge',   sortable:true, width:'80px', order:3),
                new ColumnDefinition('language',         'Lang',           type:'text',    width:'55px', align:'center', order:4),
                new ColumnDefinition('version',          'Ver',            type:'text',    width:'50px', align:'center', order:5),
                new ColumnDefinition('usage_count',      'Used',           type:'number',  sortable:true, width:'60px', align:'center', order:6),
                new ColumnDefinition('approval_status',  'Approval',       type:'badge',   sortable:true, width:'80px', order:7),
                new ColumnDefinition('last_used_at',     'Last Used',      type:'datetime',sortable:true, width:'130px', order:8),
                new ColumnDefinition('actions',          '',               type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('channel', 'Channel', type:'select', quick:true, options:[['value'=>'whatsapp','label'=>'WhatsApp'],['value'=>'email','label'=>'Email'],['value'=>'sms','label'=>'SMS'],['value'=>'push','label'=>'Push'],['value'=>'internal','label'=>'Internal']], order:1))
            ->addFilter(new FilterDefinition('category', 'Category', type:'select', quick:true, options:[['value'=>'service','label'=>'Service'],['value'=>'payment','label'=>'Payment'],['value'=>'appointment','label'=>'Appointment'],['value'=>'warranty','label'=>'Warranty'],['value'=>'promo','label'=>'Promo'],['value'=>'birthday','label'=>'Birthday'],['value'=>'system','label'=>'System']], order:2))
            ->addFilter(new FilterDefinition('approval_status', 'Approval', type:'select', options:[['value'=>'draft','label'=>'Draft'],['value'=>'pending','label'=>'Pending'],['value'=>'approved','label'=>'Approved']], order:3))
            ->addBulkAction(new BulkAction('approve', 'Approve', variant:'primary'))
            ->addBulkAction(new BulkAction('test_send', 'Test Send', variant:'default'))
            ->addBulkAction(new BulkAction('clone', 'Clone', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // CAMPAIGNS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function campaignTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'notification.campaign.index',
            title: 'Campaign Management',
            modelClass: \App\Models\Tenant\Campaign::class,
            defaultSort: ['scheduled_at' => 'desc'],
            perPage: 25,
            selectable: true,
            features: ['notifications'],
        ))
            ->addColumns([
                new ColumnDefinition('campaign_name',    'Campaign Name',  type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('campaign_type',    'Type',           type:'badge',   sortable:true, filterable:true, width:'100px', order:2),
                new ColumnDefinition('channel',          'Channel',        type:'badge',   width:'80px', order:3),
                new ColumnDefinition('target_audience',  'Target',         type:'text',    width:'120px', order:4),
                new ColumnDefinition('recipient_count',  'Recipients',     type:'number',  sortable:true, width:'75px', align:'center', order:5),
                new ColumnDefinition('delivery_rate',    'Delivery %',     type:'number',  sortable:true, width:'75px', align:'center', order:6),
                new ColumnDefinition('response_rate',    'Response %',     type:'number',  width:'75px', align:'center', order:7),
                new ColumnDefinition('scheduled_at',     'Scheduled',      type:'datetime',sortable:true, width:'130px', order:8),
                new ColumnDefinition('status',           'Status',         type:'badge',   sortable:true, filterable:true, width:'85px', order:9),
                new ColumnDefinition('actions',          '',               type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'draft','label'=>'Draft'],
                ['value'=>'scheduled','label'=>'Scheduled'],
                ['value'=>'sending','label'=>'Sending'],
                ['value'=>'completed','label'=>'Completed'],
                ['value'=>'paused','label'=>'Paused'],
            ], order:1))
            ->addFilter(new FilterDefinition('campaign_type', 'Type', type:'select', quick:true, options:[
                ['value'=>'birthday','label'=>'Birthday'],
                ['value'=>'promo','label'=>'Promo'],
                ['value'=>'follow_up','label'=>'Follow Up'],
                ['value'=>'reactivation','label'=>'Reactivation'],
                ['value'=>'holiday','label'=>'Holiday Greeting'],
                ['value'=>'warranty','label'=>'Warranty Reminder'],
            ], order:2))
            ->addBulkAction(new BulkAction('start', 'Start', variant:'primary'))
            ->addBulkAction(new BulkAction('pause', 'Pause', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // DELIVERY REPORT — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function deliveryTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'notification.delivery.index',
            title: 'Delivery Report',
            modelClass: \App\Models\Tenant\NotificationDelivery::class,
            defaultSort: ['delivered_at' => 'desc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            features: ['notifications'],
        ))
            ->addColumns([
                new ColumnDefinition('channel',          'Channel',       type:'badge',   sortable:true, width:'80px', order:1),
                new ColumnDefinition('recipient',        'Recipient',     type:'text',    sortable:true, searchable:true, order:2),
                new ColumnDefinition('subject',          'Subject',       type:'text',    order:3),
                new ColumnDefinition('delivery_status',  'Status',        type:'badge',   sortable:true, filterable:true, width:'85px', order:4),
                new ColumnDefinition('sent_at',          'Sent',          type:'datetime',sortable:true, width:'130px', order:5),
                new ColumnDefinition('delivered_at',     'Delivered',     type:'datetime',sortable:true, width:'130px', order:6),
                new ColumnDefinition('read_at',          'Read',          type:'datetime',sortable:true, width:'130px', order:7),
                new ColumnDefinition('error_message',    'Error',         type:'text',    order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('delivery_status', 'Status', type:'select', quick:true, options:[
                ['value'=>'delivered','label'=>'Delivered'],
                ['value'=>'read','label'=>'Read'],
                ['value'=>'failed','label'=>'Failed'],
                ['value'=>'bounced','label'=>'Bounced'],
            ], order:1))
            ->addFilter(new FilterDefinition('channel', 'Channel', type:'select', quick:true, options:[['value'=>'whatsapp','label'=>'WhatsApp'],['value'=>'email','label'=>'Email'],['value'=>'sms','label'=>'SMS'],['value'=>'push','label'=>'Push']], order:2))
            ->addFilter(new FilterDefinition('delivered_at', 'Date', type:'date_range', order:3))
            ->addBulkAction(new BulkAction('retry', 'Retry Failed', variant:'primary'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // FAILED MESSAGES — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function failedTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'notification.failed.index',
            title: 'Failed Messages',
            modelClass: \App\Models\Tenant\FailedNotification::class,
            defaultSort: ['failed_at' => 'desc'],
            perPage: 50,
            selectable: true,
            features: ['notifications'],
        ))
            ->addColumns([
                new ColumnDefinition('channel',          'Channel',       type:'badge',   sortable:true, width:'80px', order:1),
                new ColumnDefinition('recipient',        'Recipient',     type:'text',    sortable:true, searchable:true, order:2),
                new ColumnDefinition('subject',          'Subject',       type:'text',    order:3),
                new ColumnDefinition('error_code',       'Error Code',    type:'text',    sortable:true, width:'90px', order:4),
                new ColumnDefinition('error_message',    'Error',         type:'text',    order:5),
                new ColumnDefinition('retry_count',      'Retries',       type:'number',  width:'60px', align:'center', order:6),
                new ColumnDefinition('failed_at',        'Failed At',     type:'datetime',sortable:true, width:'130px', order:7),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('channel', 'Channel', type:'select', quick:true, options:[['value'=>'whatsapp','label'=>'WhatsApp'],['value'=>'email','label'=>'Email'],['value'=>'sms','label'=>'SMS'],['value'=>'push','label'=>'Push']], order:1))
            ->addFilter(new FilterDefinition('error_code', 'Error Code', type:'select', order:2))
            ->addBulkAction(new BulkAction('retry_all', 'Retry All', variant:'primary'))
            ->addBulkAction(new BulkAction('discard', 'Discard', variant:'danger', confirm:true));
    }

    // ═══════════════════════════════════════════════════════════
    // INTERNAL MESSAGES — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function internalMessageTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'notification.internal.index',
            title: 'Internal Messages',
            modelClass: \App\Models\Tenant\InternalMessage::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            features: ['notifications'],
        ))
            ->addColumns([
                new ColumnDefinition('sender_name',      'From',          type:'text',    sortable:true, width:'100px', order:1),
                new ColumnDefinition('message_type',     'Type',          type:'badge',   sortable:true, width:'90px', order:2),
                new ColumnDefinition('subject',          'Subject',       type:'text',    searchable:true, bold:true, order:3),
                new ColumnDefinition('content_preview',  'Preview',       type:'text',    order:4),
                new ColumnDefinition('target_scope',     'To',            type:'badge',   width:'100px', order:5),
                new ColumnDefinition('read_count',       'Read',          type:'number',  width:'55px', align:'center', order:6),
                new ColumnDefinition('total_recipients', 'Sent To',       type:'number',  width:'55px', align:'center', order:7),
                new ColumnDefinition('created_at',       'Sent',          type:'datetime',sortable:true, width:'130px', order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('message_type', 'Type', type:'select', quick:true, options:[['value'=>'announcement','label'=>'Announcement'],['value'=>'mention','label'=>'@Mention'],['value'=>'department','label'=>'Department'],['value'=>'branch','label'=>'Branch'],['value'=>'direct','label'=>'Direct']], order:1))
            ->addBulkAction(new BulkAction('pin', 'Pin', variant:'default'))
            ->addBulkAction(new BulkAction('archive', 'Archive', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // CAMPAIGN BUILDER — Form
    // ═══════════════════════════════════════════════════════════

    public static function campaignForm(): FormDefinition
    {
        return (new FormDefinition(
            id: 'notification.campaign.create',
            title: 'Create Campaign',
            method: 'POST',
            endpoint: '/notifications/campaigns',
            features: ['notifications'],
        ))
            ->addSection(new FormSection(id:'basic',    label:'Campaign Info',     icon:'📋', cols:2))
            ->addSection(new FormSection(id:'target',   label:'Target Audience',   icon:'👥', cols:2))
            ->addSection(new FormSection(id:'message',  label:'Message Content',   icon:'📝', cols:1))
            ->addSection(new FormSection(id:'schedule', label:'Schedule',          icon:'📅', cols:2))
            ->addFields([
                new FormField('campaign_name',    type:'text',     label:'Campaign Name',       required:true, section:'basic', cols:8),
                new FormField('campaign_type',    type:'select',   label:'Campaign Type',       required:true, section:'basic', cols:4,
                    options:[['value'=>'promo','label'=>'Promo'],['value'=>'birthday','label'=>'Birthday'],['value'=>'follow_up','label'=>'Follow Up'],['value'=>'reactivation','label'=>'Reactivation'],['value'=>'holiday','label'=>'Holiday']]),
                new FormField('channel',          type:'select',   label:'Channel',              required:true, section:'basic', cols:4,
                    options:[['value'=>'whatsapp','label'=>'WhatsApp'],['value'=>'email','label'=>'Email'],['value'=>'sms','label'=>'SMS'],['value'=>'push','label'=>'Push']]),
                new FormField('template_id',      type:'select',   label:'Template',             required:true, section:'message', cols:6),
                new FormField('ai_enhance',       type:'switch',   label:'AI Enhance Message',    section:'message', cols:6),
                new FormField('audience_type',    type:'select',   label:'Target Type',          required:true, section:'target', cols:4,
                    options:[['value'=>'all_customers','label'=>'All Customers'],['value'=>'segment','label'=>'Customer Segment'],['value'=>'branch','label'=>'By Branch'],['value'=>'role','label'=>'By Role']]),
                new FormField('target_segment',   type:'select',   label:'Target Segment',       section:'target', cols:4),
                new FormField('target_branch',    type:'select',   label:'Target Branch',        section:'target', cols:4),
                new FormField('scheduled_at',     type:'datetime', label:'Schedule Date/Time',    section:'schedule', cols:4),
                new FormField('send_now',         type:'switch',   label:'Send Immediately',      section:'schedule', cols:4),
            ])
            ->addAction(new FormAction('save_draft', 'Save Draft', variant:'outline'))
            ->addAction(new FormAction('schedule', 'Schedule Campaign', variant:'primary', shortcut:'Ctrl+S'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES — 15 Notification Rules
    // ═══════════════════════════════════════════════════════════

    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            (new AutomationDefinition('notif.service_status', 'Service Status Notification',
                trigger: TriggerType::RECORD_UPDATED, module: 'notification'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['template' => 'service_status_update'])),

            (new AutomationDefinition('notif.appointment_reminder', 'Appointment Reminder',
                trigger: TriggerType::DATE_REACHED, module: 'notification'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['template' => 'appointment_reminder']))
                ->addStep(new AutomationStep(ActionType::SEND_EMAIL, ['template' => 'appointment_reminder_email'])),

            (new AutomationDefinition('notif.payment_reminder', 'Payment Reminder',
                trigger: TriggerType::DATE_REACHED, module: 'notification'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['template' => 'payment_reminder'])),

            (new AutomationDefinition('notif.warranty_reminder', 'Warranty Reminder',
                trigger: TriggerType::DATE_REACHED, module: 'notification'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['template' => 'warranty_expiring'])),

            (new AutomationDefinition('notif.birthday_greeting', 'Birthday Greeting',
                trigger: TriggerType::DATE_REACHED, module: 'notification'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['template' => 'birthday_greeting']))
                ->addStep(new AutomationStep(ActionType::CREATE_INTERNAL_MSG, ['message' => '🎂 Today is {{subject.full_name}}\'s birthday!'])),

            (new AutomationDefinition('notif.loyalty_upgrade', 'Loyalty Upgrade',
                trigger: TriggerType::RECORD_UPDATED, module: 'notification'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['template' => 'loyalty_upgrade'])),

            (new AutomationDefinition('notif.promotion_broadcast', 'Promotion Broadcast',
                trigger: TriggerType::RECORD_UPDATED, module: 'notification'))
                ->addStep(new AutomationStep(ActionType::SEND_BROADCAST, ['template' => 'promotion_announcement', 'channel' => 'whatsapp'])),

            (new AutomationDefinition('notif.follow_up_service', 'Follow Up Service',
                trigger: TriggerType::DATE_REACHED, module: 'notification'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['template' => 'service_follow_up', 'delayDays' => 7])),

            (new AutomationDefinition('notif.customer_feedback', 'Customer Feedback Request',
                trigger: TriggerType::RECORD_UPDATED, module: 'notification'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['template' => 'feedback_request', 'delayHours' => 24])),

            (new AutomationDefinition('notif.internal_assignment', 'Internal Assignment',
                trigger: TriggerType::RECORD_UPDATED, module: 'notification'))
                ->addStep(new AutomationStep(ActionType::CREATE_INTERNAL_MSG, ['message' => '📋 {{subject.service_number}} assigned to you.'])),

            (new AutomationDefinition('notif.purchase_notification', 'Purchase Notification',
                trigger: TriggerType::RECORD_CREATED, module: 'notification'))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['template' => 'purchase_order_created', 'roles' => ['purchasing','manager']])),

            (new AutomationDefinition('notif.inventory_alert', 'Inventory Alert',
                trigger: TriggerType::RECORD_UPDATED, module: 'notification'))
                ->addStep(new AutomationStep(ActionType::CREATE_INTERNAL_MSG, ['message' => '⚠️ {{subject.product_name}} stock below minimum.', 'roles' => ['warehouse','purchasing']])),

            (new AutomationDefinition('notif.finance_reminder', 'Finance Reminder',
                trigger: TriggerType::DATE_REACHED, module: 'notification'))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['template' => 'invoice_due_reminder', 'roles' => ['finance']])),

            (new AutomationDefinition('notif.hr_announcement', 'HR Announcement',
                trigger: TriggerType::RECORD_CREATED, module: 'notification'))
                ->addStep(new AutomationStep(ActionType::CREATE_INTERNAL_MSG, ['message' => '📢 {{subject.title}}', 'scope' => 'all_employees'])),

            (new AutomationDefinition('notif.system_notification', 'System Notification',
                trigger: TriggerType::RECORD_CREATED, module: 'notification'))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['template' => 'system_alert', 'roles' => ['super_admin','admin']])),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // REPORTING DEFINITIONS — 12 Notification Reports
    // ═══════════════════════════════════════════════════════════

    /** @return ReportDefinition[] */
    public static function reports(): array
    {
        return [
            (new ReportDefinition('notif.whatsapp_performance', 'WhatsApp Performance',
                type:'summary', chartType:'bar', features:['notifications']))
                ->addMetric(new MetricDefinition('sent', 'Sent', 'count', 'sent', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('delivered', 'Delivered', 'count', 'delivered', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('read', 'Read', 'count', 'read', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('failed', 'Failed', 'count', 'failed', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'month', type:'date')),

            (new ReportDefinition('notif.email_performance', 'Email Performance',
                type:'summary', chartType:'bar', features:['notifications']))
                ->addMetric(new MetricDefinition('sent', 'Sent', 'count', 'sent', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('opened', 'Opened', 'count', 'opened', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('clicked', 'Clicked', 'count', 'clicked', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('bounced', 'Bounced', 'count', 'bounced', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'month', type:'date')),

            (new ReportDefinition('notif.sms_performance', 'SMS Performance',
                type:'summary', chartType:'table', features:['notifications']))
                ->addMetric(new MetricDefinition('sent', 'Sent', 'count', 'sent', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('delivered', 'Delivered', 'count', 'delivered', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('failed', 'Failed', 'count', 'failed', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'month', type:'date')),

            (new ReportDefinition('notif.campaign_analytics', 'Campaign Analytics',
                type:'summary', chartType:'table', features:['notifications']))
                ->addMetric(new MetricDefinition('recipients', 'Recipients', 'count', 'recipients', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('delivery_rate', 'Delivery %', 'avg', 'delivery_pct', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('response_rate', 'Response %', 'avg', 'response_pct', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('campaign', 'Campaign', 'campaign_name', type:'string')),

            (new ReportDefinition('notif.customer_engagement', 'Customer Engagement',
                type:'summary', chartType:'bar', features:['notifications']))
                ->addMetric(new MetricDefinition('messages_received', 'Received', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('messages_read', 'Read', 'count', 'read', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('responses', 'Responses', 'count', 'responses', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'month', type:'date')),

            (new ReportDefinition('notif.delivery_analytics', 'Delivery Analytics',
                type:'summary', chartType:'table', features:['notifications']))
                ->addMetric(new MetricDefinition('total', 'Total', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('delivered', 'Delivered', 'count', 'delivered', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('failed', 'Failed', 'count', 'failed', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('avg_delivery_sec', 'Avg Delivery (s)', 'avg', 'delivery_seconds', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('channel', 'Channel', 'channel', type:'string')),

            (new ReportDefinition('notif.queue_analytics', 'Queue Analytics',
                type:'summary', chartType:'bar', features:['notifications']))
                ->addMetric(new MetricDefinition('queued', 'Queued', 'count', 'id', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('processed', 'Processed', 'count', 'processed', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('avg_wait_sec', 'Avg Wait (s)', 'avg', 'wait_seconds', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('channel', 'Channel', 'channel', type:'string')),

            (new ReportDefinition('notif.failed_delivery', 'Failed Delivery Report',
                type:'summary', chartType:'table', features:['notifications']))
                ->addMetric(new MetricDefinition('failed_count', 'Failed', 'count', 'id', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('retry_success', 'Retry OK', 'count', 'retry_success', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('error_code', 'Error', 'error_code', type:'string')),

            (new ReportDefinition('notif.channel_usage', 'Channel Usage',
                type:'summary', chartType:'pie', features:['notifications']))
                ->addMetric(new MetricDefinition('message_count', 'Messages', 'count', 'id', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('channel', 'Channel', 'channel', type:'string')),

            (new ReportDefinition('notif.automation_messages', 'Automation Messages',
                type:'summary', chartType:'table', features:['notifications']))
                ->addMetric(new MetricDefinition('triggered', 'Triggered', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('sent', 'Sent', 'count', 'sent', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('rule', 'Rule', 'rule_name', type:'string')),

            (new ReportDefinition('notif.kpi', 'Notification KPI',
                type:'summary', chartType:'kpi', features:['notifications']))
                ->addMetric(new MetricDefinition('total_sent', 'Total Sent', 'count', 'id', format:'number', color:'primary', icon:'📤'))
                ->addMetric(new MetricDefinition('delivery_rate', 'Delivery Rate %', 'avg', 'delivery_pct', format:'number', color:'success', icon:'✅'))
                ->addMetric(new MetricDefinition('read_rate', 'Read Rate %', 'avg', 'read_pct', format:'number', color:'info', icon:'👁️'))
                ->addMetric(new MetricDefinition('response_rate', 'Response Rate %', 'avg', 'response_pct', format:'number', color:'primary', icon:'💬')),

            (new ReportDefinition('notif.scorecard', 'Communication Scorecard',
                type:'summary', chartType:'kpi', features:['notifications']))
                ->addMetric(new MetricDefinition('delivery_score', 'Delivery', 'last', 'delivery_score', format:'number', color:'success', icon:'✅'))
                ->addMetric(new MetricDefinition('engagement_score', 'Engagement', 'last', 'engagement_score', format:'number', color:'info', icon:'👥'))
                ->addMetric(new MetricDefinition('reliability_score', 'Reliability', 'last', 'reliability_score', format:'number', color:'primary', icon:'🔌'))
                ->addMetric(new MetricDefinition('overall', 'Overall', 'expression', '(delivery_score + engagement_score + reliability_score) / 3', format:'number', color:'success', icon:'🏆')),
        ];
    }
}
