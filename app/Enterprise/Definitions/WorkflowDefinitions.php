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
 * WorkflowDefinitions — ALL Enterprise definitions for Workflow, Approval & SLA Center.
 * 
 * Covers: Workflow Designer, Approval Engine, SLA Engine, Escalation Engine,
 * Task Center, Delegation, Business Rules.
 * 
 * MODUL ERP KEDELAPAN BELAS — ENTERPRISE WORKFLOW, APPROVAL & SLA CENTER
 * 
 * ⚠️ ALL approvals MUST route through Workflow Center. No direct approval in any module.
 */
class WorkflowDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // WORKFLOW WORKSPACE (16 tabs)
    // ═══════════════════════════════════════════════════════════

    public static function workspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'workflow',
            title: 'Workflow, Approval & SLA Center',
            icon: '🔄',
            tabs: [
                ['id' => 'overview',            'label' => 'Overview',              'icon' => '📊'],
                ['id' => 'designer',            'label' => 'Workflow Designer',     'icon' => '🎨'],
                ['id' => 'approval_matrix',     'label' => 'Approval Matrix',       'icon' => '📋'],
                ['id' => 'approval_queue',      'label' => 'Approval Queue',        'icon' => '✅'],
                ['id' => 'sla_monitor',         'label' => 'SLA Monitor',           'icon' => '⏱️'],
                ['id' => 'escalation',          'label' => 'Escalation Center',     'icon' => '🚨'],
                ['id' => 'delegation',          'label' => 'Delegation',            'icon' => '🔄'],
                ['id' => 'templates',           'label' => 'Workflow Templates',    'icon' => '📝'],
                ['id' => 'history',             'label' => 'Approval History',      'icon' => '📜'],
                ['id' => 'pending_tasks',       'label' => 'Pending Tasks',         'icon' => '📥'],
                ['id' => 'completed_tasks',     'label' => 'Completed Tasks',       'icon' => '✅'],
                ['id' => 'exceptions',          'label' => 'Exception Handling',    'icon' => '⚠️'],
                ['id' => 'analytics',           'label' => 'Workflow Analytics',    'icon' => '📈'],
                ['id' => 'business_rules',      'label' => 'Business Rules',        'icon' => '📐'],
                ['id' => 'audit',               'label' => 'Audit Trail',           'icon' => '🛡️'],
                ['id' => 'settings',            'label' => 'Settings',              'icon' => '⚙️'],
            ],
            actions: [
                ['id' => 'create_workflow',     'label' => 'Create Workflow',      'roles' => ['super_admin','owner','admin']],
                ['id' => 'approve',             'label' => 'Approve',              'roles' => ['super_admin','owner','director','manager','supervisor']],
                ['id' => 'reject',              'label' => 'Reject',               'roles' => ['super_admin','owner','director','manager','supervisor']],
                ['id' => 'escalate',            'label' => 'Escalate',             'roles' => ['all']],
                ['id' => 'delegate',            'label' => 'Delegate',             'roles' => ['super_admin','owner','director','manager']],
                ['id' => 'define_sla',          'label' => 'Define SLA Policy',    'roles' => ['super_admin','owner','admin']],
                ['id' => 'create_business_rule','label' => 'Create Business Rule', 'roles' => ['super_admin','owner','admin']],
                ['id' => 'export',              'label' => 'Export Report',        'roles' => ['super_admin','owner','admin']],
            ],
            sidebarWidgets: [
                ['id' => 'workflow_health',     'component' => 'WorkflowHealth',   'priority' => 10],
                ['id' => 'my_tasks',            'component' => 'MyTasks',          'priority' => 20],
                ['id' => 'sla_alerts',          'component' => 'SLAAlerts',        'priority' => 30],
                ['id' => 'quick_actions',       'component' => 'QuickActions',     'priority' => 40],
            ],
            features: ['workflow'],
            permissions: ['manage_workflow'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // APPROVAL QUEUE — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function approvalQueueTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'workflow.approval.index',
            title: 'Approval Queue',
            modelClass: \App\Models\Tenant\ApprovalTask::class,
            defaultSort: ['priority_order' => 'asc', 'requested_at' => 'asc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['workflow'],
        ))
            ->addColumns([
                new ColumnDefinition('workflow_type',    'Type',          type:'badge',   sortable:true, filterable:true, width:'100px', order:1),
                new ColumnDefinition('title',            'Title',          type:'text',    searchable:true, bold:true, order:2),
                new ColumnDefinition('module_source',    'Module',         type:'badge',   sortable:true, width:'90px', order:3),
                new ColumnDefinition('requester_name',   'Requester',      type:'text',    sortable:true, width:'100px', order:4),
                new ColumnDefinition('approver_name',    'Approver',       type:'text',    sortable:true, width:'100px', order:5),
                new ColumnDefinition('approval_level',   'Level',          type:'number',  width:'55px', align:'center', order:6),
                new ColumnDefinition('sla_status',       'SLA',            type:'badge',   sortable:true, width:'85px', order:7),
                new ColumnDefinition('sla_deadline',     'SLA Deadline',   type:'datetime',sortable:true, width:'130px', order:8),
                new ColumnDefinition('requested_at',     'Requested',      type:'datetime',sortable:true, width:'130px', order:9),
                new ColumnDefinition('status',           'Status',         type:'badge',   sortable:true, filterable:true, width:'85px', order:10),
                new ColumnDefinition('actions',          '',               type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'pending','label'=>'Pending'],
                ['value'=>'in_review','label'=>'In Review'],
                ['value'=>'approved','label'=>'Approved'],
                ['value'=>'rejected','label'=>'Rejected'],
                ['value'=>'escalated','label'=>'Escalated'],
                ['value'=>'delegated','label'=>'Delegated'],
                ['value'=>'returned','label'=>'Returned'],
            ], order:1))
            ->addFilter(new FilterDefinition('sla_status', 'SLA', type:'select', quick:true, options:[['value'=>'ok','label'=>'✅ OK'],['value'=>'warning','label'=>'⚠️ Warning'],['value'=>'breached','label'=>'❌ Breached']], order:2))
            ->addFilter(new FilterDefinition('module_source', 'Module', type:'select', order:3))
            ->addFilter(new FilterDefinition('approver_id', 'Approver', type:'select', order:4))
            ->addBulkAction(new BulkAction('approve', 'Approve', variant:'primary'))
            ->addBulkAction(new BulkAction('reject', 'Reject', variant:'danger'))
            ->addBulkAction(new BulkAction('escalate', 'Escalate', variant:'warning'))
            ->addBulkAction(new BulkAction('delegate', 'Delegate', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // WORKFLOW INSTANCES — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function workflowInstanceTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'workflow.instance.index',
            title: 'Active Workflows',
            modelClass: \App\Models\Tenant\WorkflowInstance::class,
            defaultSort: ['started_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['workflow'],
        ))
            ->addColumns([
                new ColumnDefinition('workflow_name',    'Workflow',      type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('instance_id',       'Instance #',    type:'text',    sortable:true, width:'120px', order:2),
                new ColumnDefinition('module_source',    'Source Module', type:'badge',   sortable:true, width:'100px', order:3),
                new ColumnDefinition('current_step',     'Current Step',   type:'text',    sortable:true, order:4),
                new ColumnDefinition('total_steps',      'Steps',          type:'number',  width:'55px', align:'center', order:5),
                new ColumnDefinition('progress_pct',     'Progress',       type:'progress',sortable:true, width:'100px', order:6),
                new ColumnDefinition('started_at',       'Started',        type:'datetime',sortable:true, width:'130px', order:7),
                new ColumnDefinition('status',           'Status',         type:'badge',   sortable:true, filterable:true, width:'85px', order:8),
                new ColumnDefinition('sla_status',       'SLA',            type:'badge',   width:'70px', order:9),
                new ColumnDefinition('actions',          '',               type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'active','label'=>'Active'],
                ['value'=>'paused','label'=>'Paused'],
                ['value'=>'waiting','label'=>'Waiting'],
                ['value'=>'completed','label'=>'Completed'],
                ['value'=>'cancelled','label'=>'Cancelled'],
                ['value'=>'error','label'=>'Error'],
            ], order:1))
            ->addFilter(new FilterDefinition('sla_status', 'SLA', type:'select', options:[['value'=>'ok','label'=>'OK'],['value'=>'warning','label'=>'Warning'],['value'=>'breached','label'=>'Breached']], order:2))
            ->addBulkAction(new BulkAction('pause', 'Pause', variant:'default'))
            ->addBulkAction(new BulkAction('resume', 'Resume', variant:'primary'))
            ->addBulkAction(new BulkAction('cancel', 'Cancel', variant:'danger', confirm:true));
    }

    // ═══════════════════════════════════════════════════════════
    // SLA MONITOR — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function slaTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'workflow.sla.index',
            title: 'SLA Monitor',
            modelClass: \App\Models\Tenant\SLAMonitor::class,
            defaultSort: ['sla_deadline' => 'asc'],
            perPage: 50,
            selectable: true,
            features: ['workflow'],
        ))
            ->addColumns([
                new ColumnDefinition('sla_type',         'SLA Type',      type:'badge',   sortable:true, filterable:true, width:'90px', order:1),
                new ColumnDefinition('entity_reference', 'Reference',      type:'text',    sortable:true, bold:true, width:'120px', order:2),
                new ColumnDefinition('module',           'Module',         type:'badge',   sortable:true, width:'90px', order:3),
                new ColumnDefinition('sla_level',        'Level',          type:'badge',   sortable:true, width:'75px', order:4),
                new ColumnDefinition('started_at',       'Started',        type:'datetime',sortable:true, width:'130px', order:5),
                new ColumnDefinition('sla_deadline',     'Deadline',       type:'datetime',sortable:true, bold:true, width:'130px', order:6),
                new ColumnDefinition('remaining',        'Remaining',      type:'text',    width:'100px', order:7),
                new ColumnDefinition('status',           'Status',         type:'badge',   sortable:true, filterable:true, width:'85px', order:8),
                new ColumnDefinition('escalation_level', 'Escalation',     type:'number',  width:'70px', align:'center', order:9),
                new ColumnDefinition('actions',          '',               type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'running','label'=>'Running'],
                ['value'=>'warning','label'=>'⚠️ Warning'],
                ['value'=>'breached','label'=>'❌ Breached'],
                ['value'=>'escalated','label'=>'Escalated'],
                ['value'=>'completed','label'=>'Completed'],
            ], order:1))
            ->addFilter(new FilterDefinition('sla_level', 'Level', type:'select', options:[['value'=>'low','label'=>'Low'],['value'=>'normal','label'=>'Normal'],['value'=>'high','label'=>'High'],['value'=>'critical','label'=>'Critical']], order:2))
            ->addBulkAction(new BulkAction('escalate', 'Escalate', variant:'warning'))
            ->addBulkAction(new BulkAction('extend', 'Extend Deadline', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // BUSINESS RULES — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function businessRuleTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'workflow.rule.index',
            title: 'Business Rules',
            modelClass: \App\Models\Tenant\BusinessRule::class,
            defaultSort: ['module' => 'asc', 'rule_name' => 'asc'],
            perPage: 25,
            selectable: true,
            features: ['workflow'],
        ))
            ->addColumns([
                new ColumnDefinition('rule_name',        'Rule Name',     type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('module',           'Module',        type:'badge',   sortable:true, filterable:true, width:'100px', order:2),
                new ColumnDefinition('rule_type',        'Type',          type:'badge',   sortable:true, width:'100px', order:3),
                new ColumnDefinition('condition',        'Condition',     type:'text',    order:4),
                new ColumnDefinition('action',           'Action',        type:'text',    order:5),
                new ColumnDefinition('priority',         'Priority',      type:'number',  sortable:true, width:'65px', align:'center', order:6),
                new ColumnDefinition('trigger_count',    'Triggered',     type:'number',  sortable:true, width:'70px', align:'center', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, width:'80px', order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[['value'=>'active','label'=>'Active'],['value'=>'inactive','label'=>'Inactive']], order:1))
            ->addFilter(new FilterDefinition('module', 'Module', type:'select', order:2))
            ->addFilter(new FilterDefinition('rule_type', 'Type', type:'select', options:[['value'=>'amount','label'=>'Amount'],['value'=>'approval','label'=>'Approval'],['value'=>'auto','label'=>'Auto Action']], order:3))
            ->addBulkAction(new BulkAction('activate', 'Activate', variant:'primary'))
            ->addBulkAction(new BulkAction('deactivate', 'Deactivate', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // DELEGATION — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function delegationTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'workflow.delegation.index',
            title: 'Delegations',
            modelClass: \App\Models\Tenant\Delegation::class,
            defaultSort: ['start_date' => 'desc'],
            perPage: 25,
            selectable: true,
            features: ['workflow'],
        ))
            ->addColumns([
                new ColumnDefinition('delegator_name',   'Delegator',     type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('delegate_name',    'Delegate To',   type:'text',    sortable:true, order:2),
                new ColumnDefinition('delegation_type',  'Type',          type:'badge',   sortable:true, width:'90px', order:3),
                new ColumnDefinition('scope',            'Scope',         type:'text',    width:'120px', order:4),
                new ColumnDefinition('start_date',       'Start',         type:'date',    sortable:true, width:'100px', order:5),
                new ColumnDefinition('end_date',         'End',           type:'date',    sortable:true, width:'100px', order:6),
                new ColumnDefinition('auto_expire',      'Auto Expire',   type:'boolean', width:'70px', align:'center', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, width:'85px', order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[['value'=>'active','label'=>'Active'],['value'=>'expired','label'=>'Expired'],['value'=>'revoked','label'=>'Revoked']], order:1))
            ->addFilter(new FilterDefinition('delegation_type', 'Type', type:'select', options:[['value'=>'temporary','label'=>'Temporary'],['value'=>'vacation','label'=>'Vacation'],['value'=>'emergency','label'=>'Emergency']], order:2))
            ->addBulkAction(new BulkAction('revoke', 'Revoke', variant:'danger', confirm:true));
    }

    // ═══════════════════════════════════════════════════════════
    // WORKFLOW TEMPLATES — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function templateTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'workflow.template.index',
            title: 'Workflow Templates',
            modelClass: \App\Models\Tenant\WorkflowTemplate::class,
            defaultSort: ['category' => 'asc', 'template_name' => 'asc'],
            perPage: 25,
            selectable: true,
            features: ['workflow'],
        ))
            ->addColumns([
                new ColumnDefinition('template_name',    'Template Name',  type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('category',         'Category',       type:'badge',   sortable:true, filterable:true, width:'100px', order:2),
                new ColumnDefinition('steps_count',      'Steps',          type:'number',  width:'55px', align:'center', order:3),
                new ColumnDefinition('approval_type',    'Approval Type',  type:'badge',   width:'110px', order:4),
                new ColumnDefinition('avg_duration_h',   'Avg Duration',   type:'number',  width:'85px', align:'center', order:5),
                new ColumnDefinition('usage_count',      'Used',           type:'number',  sortable:true, width:'55px', align:'center', order:6),
                new ColumnDefinition('version',          'Ver',            type:'text',    width:'50px', align:'center', order:7),
                new ColumnDefinition('status',           'Status',         type:'badge',   sortable:true, width:'80px', order:8),
                new ColumnDefinition('actions',          '',               type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[['value'=>'active','label'=>'Active'],['value'=>'draft','label'=>'Draft'],['value'=>'archived','label'=>'Archived']], order:1))
            ->addFilter(new FilterDefinition('category', 'Category', type:'select', quick:true, options:[['value'=>'service','label'=>'Service'],['value'=>'finance','label'=>'Finance'],['value'=>'hr','label'=>'HR'],['value'=>'purchase','label'=>'Purchase'],['value'=>'project','label'=>'Project'],['value'=>'document','label'=>'Document']], order:2))
            ->addBulkAction(new BulkAction('activate', 'Activate', variant:'primary'))
            ->addBulkAction(new BulkAction('clone', 'Clone', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES — 15 Workflow Automation Rules
    // ═══════════════════════════════════════════════════════════

    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            (new AutomationDefinition('workflow.approval_started', 'Approval Started',
                trigger: TriggerType::RECORD_CREATED, module: 'workflow'))
                ->addStep(new AutomationStep(ActionType::START_WORKFLOW, []))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['template' => 'approval_requested', 'roles' => ['approver']])),

            (new AutomationDefinition('workflow.approval_completed', 'Approval Completed',
                trigger: TriggerType::RECORD_UPDATED, module: 'workflow'))
                ->addStep(new AutomationStep(ActionType::CONTINUE_WORKFLOW, [])),

            (new AutomationDefinition('workflow.sla_breach', 'SLA Breach',
                trigger: TriggerType::DATE_REACHED, module: 'workflow'))
                ->addStep(new AutomationStep(ActionType::ESCALATE_WORKFLOW, []))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '⚠️ SLA breached: {{subject.title}}', 'roles' => ['manager','supervisor']])),

            (new AutomationDefinition('workflow.auto_approve', 'Auto Approve Low Risk',
                trigger: TriggerType::RECORD_CREATED, module: 'workflow'))
                ->addStep(new AutomationStep(ActionType::AUTO_APPROVE, ['condition' => 'amount < 100000 AND risk_level = low'])),

            (new AutomationDefinition('workflow.auto_reject', 'Auto Reject Duplicate',
                trigger: TriggerType::RECORD_CREATED, module: 'workflow'))
                ->addStep(new AutomationStep(ActionType::AUTO_REJECT, ['condition' => 'is_duplicate'])),

            (new AutomationDefinition('workflow.escalation_auto', 'Auto Escalation',
                trigger: TriggerType::DATE_REACHED, module: 'workflow'))
                ->addStep(new AutomationStep(ActionType::ESCALATE_WORKFLOW, ['level' => 'next'])),

            (new AutomationDefinition('workflow.delegation_activated', 'Delegation Activated',
                trigger: TriggerType::DATE_REACHED, module: 'workflow'))
                ->addStep(new AutomationStep(ActionType::ACTIVATE_DELEGATION, [])),

            (new AutomationDefinition('workflow.workflow_paused', 'Workflow Paused',
                trigger: TriggerType::RECORD_UPDATED, module: 'workflow'))
                ->addStep(new AutomationStep(ActionType::PAUSE_WORKFLOW, [])),

            (new AutomationDefinition('workflow.workflow_resumed', 'Workflow Resumed',
                trigger: TriggerType::RECORD_UPDATED, module: 'workflow'))
                ->addStep(new AutomationStep(ActionType::RESUME_WORKFLOW, [])),

            (new AutomationDefinition('workflow.workflow_cancelled', 'Workflow Cancelled',
                trigger: TriggerType::RECORD_UPDATED, module: 'workflow'))
                ->addStep(new AutomationStep(ActionType::CANCEL_WORKFLOW, []))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '❌ Workflow {{subject.workflow_name}} cancelled.', 'roles' => ['requester']])),

            (new AutomationDefinition('workflow.notify_approver', 'Notify Approver',
                trigger: TriggerType::RECORD_CREATED, module: 'workflow'))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['template' => 'new_approval_task', 'roles' => ['approver']])),

            (new AutomationDefinition('workflow.notify_requester', 'Notify Requester',
                trigger: TriggerType::RECORD_UPDATED, module: 'workflow'))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '📋 Your request "{{subject.title}}" has been {{subject.status}}.', 'roles' => ['requester']])),

            (new AutomationDefinition('workflow.risk_assessment', 'AI Risk Assessment',
                trigger: TriggerType::RECORD_CREATED, module: 'workflow'))
                ->addStep(new AutomationStep(ActionType::AI_RISK_ASSESS, []))
                ->addStep(new AutomationStep(ActionType::SET_APPROVAL_PATH, ['based_on' => 'risk_level'])),

            (new AutomationDefinition('workflow.bottleneck_detect', 'Bottleneck Detection',
                trigger: TriggerType::SCHEDULED, module: 'workflow'))
                ->addStep(new AutomationStep(ActionType::AI_BOTTLENECK_ANALYSIS, []))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '⚠️ Workflow bottleneck detected: {{subject.details}}', 'roles' => ['admin']])),

            (new AutomationDefinition('workflow.daily_report', 'Daily Approval Report',
                trigger: TriggerType::SCHEDULED, module: 'workflow'))
                ->addStep(new AutomationStep(ActionType::GENERATE_REPORT, ['report' => 'approval_kpi']))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '📊 Daily Approval KPI ready.', 'roles' => ['manager']])),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // REPORTING DEFINITIONS — 12 Workflow Reports
    // ═══════════════════════════════════════════════════════════

    /** @return ReportDefinition[] */
    public static function reports(): array
    {
        return [
            (new ReportDefinition('workflow.performance', 'Workflow Performance',
                type:'summary', chartType:'bar', features:['workflow']))
                ->addMetric(new MetricDefinition('total_workflows', 'Total', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('completed', 'Completed', 'count', 'completed', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('failed', 'Failed', 'count', 'failed', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('avg_duration_h', 'Avg Duration (h)', 'avg', 'duration_hours', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('workflow_type', 'Type', 'workflow_type', type:'string')),

            (new ReportDefinition('workflow.approval_time', 'Approval Time Analysis',
                type:'summary', chartType:'bar', features:['workflow']))
                ->addMetric(new MetricDefinition('avg_approval_h', 'Avg Approval (h)', 'avg', 'approval_hours', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('median_approval_h', 'Median (h)', 'median', 'approval_hours', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('max_approval_h', 'Max (h)', 'max', 'approval_hours', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('approver', 'Approver', 'approver_name', type:'string')),

            (new ReportDefinition('workflow.sla_compliance', 'SLA Compliance',
                type:'summary', chartType:'table', features:['workflow']))
                ->addMetric(new MetricDefinition('sla_met', 'SLA Met %', 'avg', 'sla_met_pct', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('sla_breached', 'Breached', 'count', 'breached', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('avg_overdue_h', 'Avg Overdue (h)', 'avg', 'overdue_hours', format:'number', color:'warning'))
                ->addDimension(new DimensionDefinition('department', 'Department', 'department', type:'string')),

            (new ReportDefinition('workflow.escalation_report', 'Escalation Report',
                type:'summary', chartType:'table', features:['workflow']))
                ->addMetric(new MetricDefinition('escalations', 'Escalations', 'count', 'id', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('resolved_after_esc', 'Resolved After Esc', 'count', 'resolved', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('avg_esc_level', 'Avg Esc Level', 'avg', 'escalation_level', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('module', 'Module', 'module', type:'string')),

            (new ReportDefinition('workflow.bottleneck', 'Bottleneck Analysis',
                type:'summary', chartType:'bar', features:['workflow']))
                ->addMetric(new MetricDefinition('queue_depth', 'Queue Depth', 'avg', 'queue_depth', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('wait_time_h', 'Avg Wait (h)', 'avg', 'wait_hours', format:'number', color:'warning'))
                ->addDimension(new DimensionDefinition('step', 'Step', 'step_name', type:'string')),

            (new ReportDefinition('workflow.approval_kpi', 'Approval KPI',
                type:'summary', chartType:'kpi', features:['workflow']))
                ->addMetric(new MetricDefinition('pending', 'Pending', 'count', 'pending', format:'number', color:'warning', icon:'⏳'))
                ->addMetric(new MetricDefinition('approved_today', 'Approved Today', 'count', 'approved', format:'number', color:'success', icon:'✅'))
                ->addMetric(new MetricDefinition('sla_met', 'SLA Met %', 'avg', 'sla_met_pct', format:'number', color:'info', icon:'⏱️'))
                ->addMetric(new MetricDefinition('avg_time', 'Avg Time (h)', 'avg', 'approval_hours', format:'number', color:'primary', icon:'🕐')),

            (new ReportDefinition('workflow.department_workflow', 'Department Workflow',
                type:'summary', chartType:'bar', features:['workflow']))
                ->addMetric(new MetricDefinition('workflows', 'Workflows', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('approved', 'Approved', 'count', 'approved', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('rejected', 'Rejected', 'count', 'rejected', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('department', 'Department', 'department', type:'string')),

            (new ReportDefinition('workflow.branch_workflow', 'Branch Workflow',
                type:'summary', chartType:'bar', features:['workflow']))
                ->addMetric(new MetricDefinition('workflows', 'Workflows', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('avg_sla_met', 'Avg SLA %', 'avg', 'sla_met_pct', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('branch', 'Branch', 'branch_name', type:'string')),

            (new ReportDefinition('workflow.user_productivity', 'User Productivity',
                type:'summary', chartType:'bar', features:['workflow']))
                ->addMetric(new MetricDefinition('tasks_completed', 'Tasks Done', 'count', 'id', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('avg_approval_h', 'Avg Time (h)', 'avg', 'approval_hours', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('approval_rate', 'Approval Rate %', 'avg', 'approval_pct', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('user', 'User', 'user_name', type:'string')),

            (new ReportDefinition('workflow.risk_analysis', 'Risk Analysis',
                type:'summary', chartType:'heatmap', features:['workflow']))
                ->addMetric(new MetricDefinition('high_risk', 'High Risk', 'count', 'high_risk', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('medium_risk', 'Medium Risk', 'count', 'medium_risk', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('low_risk', 'Low Risk', 'count', 'low_risk', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('module', 'Module', 'module', type:'string')),

            (new ReportDefinition('workflow.ai_insight', 'AI Workflow Insight',
                type:'summary', chartType:'table', features:['workflow']))
                ->addMetric(new MetricDefinition('optimization_suggestions', 'Suggestions', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('efficiency_gain_pct', 'Efficiency Gain %', 'avg', 'efficiency_pct', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('recommendation', 'Recommendation', 'recommendation', type:'string')),

            (new ReportDefinition('workflow.scorecard', 'Workflow Scorecard',
                type:'summary', chartType:'kpi', features:['workflow']))
                ->addMetric(new MetricDefinition('efficiency_score', 'Efficiency', 'last', 'efficiency_score', format:'number', color:'primary', icon:'⚡'))
                ->addMetric(new MetricDefinition('sla_score', 'SLA', 'last', 'sla_score', format:'number', color:'success', icon:'⏱️'))
                ->addMetric(new MetricDefinition('quality_score', 'Quality', 'last', 'quality_score', format:'number', color:'info', icon:'✅'))
                ->addMetric(new MetricDefinition('overall', 'Overall', 'expression', '(efficiency_score + sla_score + quality_score) / 3', format:'number', color:'success', icon:'🏆')),
        ];
    }
}
