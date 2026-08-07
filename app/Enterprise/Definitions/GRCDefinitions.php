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
 * GRCDefinitions — ALL Enterprise definitions for Governance, Risk, Compliance & Audit Center.
 * 
 * Covers: Risk Register, Risk Assessment, Compliance Matrix, Internal/External Audit,
 * CAPA, Incident Management, Internal Controls, Governance, AI Risk Analysis.
 * 
 * MODUL ERP KESEMBILAN BELAS — ENTERPRISE GOVERNANCE, RISK, COMPLIANCE & AUDIT (GRC) CENTER
 * 
 * ⚠️ ALL audit, compliance, risk, incident, CAPA MUST route through GRC Center.
 */
class GRCDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // GRC WORKSPACE (16 tabs)
    // ═══════════════════════════════════════════════════════════

    public static function workspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'grc',
            title: 'Governance, Risk, Compliance & Audit Center',
            icon: '🛡️',
            tabs: [
                ['id' => 'overview',            'label' => 'Executive Overview',     'icon' => '📊'],
                ['id' => 'risk_register',       'label' => 'Risk Register',          'icon' => '⚠️'],
                ['id' => 'risk_assessment',     'label' => 'Risk Assessment',        'icon' => '📐'],
                ['id' => 'compliance',          'label' => 'Compliance Matrix',      'icon' => '✅'],
                ['id' => 'internal_audit',      'label' => 'Internal Audit',         'icon' => '🔍'],
                ['id' => 'external_audit',      'label' => 'External Audit',         'icon' => '🏛️'],
                ['id' => 'findings',            'label' => 'Findings',               'icon' => '📋'],
                ['id' => 'capa',                'label' => 'CAPA',                   'icon' => '🔧'],
                ['id' => 'policies',            'label' => 'SOP & Policies',         'icon' => '📜'],
                ['id' => 'regulatory',          'label' => 'Regulatory Requirements','icon' => '⚖️'],
                ['id' => 'controls',            'label' => 'Internal Controls',      'icon' => '🔒'],
                ['id' => 'incidents',           'label' => 'Incident Management',    'icon' => '🚨'],
                ['id' => 'governance',          'label' => 'Governance Dashboard',   'icon' => '🏛️'],
                ['id' => 'ai_advisor',          'label' => 'AI Risk Advisor',        'icon' => '🤖'],
                ['id' => 'audit_trail',         'label' => 'Audit Trail',            'icon' => '🛡️'],
                ['id' => 'settings',            'label' => 'Settings',               'icon' => '⚙️'],
            ],
            actions: [
                ['id' => 'create_risk',        'label' => 'Create Risk',         'roles' => ['super_admin','owner','risk_officer','internal_auditor']],
                ['id' => 'create_audit',       'label' => 'Create Audit Plan',   'roles' => ['super_admin','owner','internal_auditor','compliance_officer']],
                ['id' => 'create_finding',     'label' => 'Create Finding',      'roles' => ['super_admin','owner','internal_auditor','external_auditor']],
                ['id' => 'create_capa',        'label' => 'Create CAPA',         'roles' => ['super_admin','owner','risk_officer','compliance_officer']],
                ['id' => 'report_incident',    'label' => 'Report Incident',     'roles' => ['all']],
                ['id' => 'assess_risk',        'label' => 'Assess Risk',         'roles' => ['super_admin','owner','risk_officer']],
                ['id' => 'generate_report',    'label' => 'Generate Report',     'roles' => ['super_admin','owner','director','internal_auditor','compliance_officer']],
                ['id' => 'export',             'label' => 'Export',              'roles' => ['super_admin','owner','internal_auditor']],
            ],
            sidebarWidgets: [
                ['id' => 'risk_heatmap_mini',  'component' => 'RiskHeatmapMini', 'priority' => 10],
                ['id' => 'compliance_score',   'component' => 'ComplianceScore',  'priority' => 20],
                ['id' => 'open_findings',      'component' => 'OpenFindings',     'priority' => 30],
                ['id' => 'quick_actions',      'component' => 'QuickActions',     'priority' => 40],
            ],
            features: ['grc'],
            permissions: ['manage_grc'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // RISK REGISTER — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function riskTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'grc.risk.index',
            title: 'Enterprise Risk Register',
            modelClass: \App\Models\Tenant\Risk::class,
            defaultSort: ['risk_score' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['grc'],
        ))
            ->addColumns([
                new ColumnDefinition('risk_code',        'Risk ID',       type:'text',    sortable:true, bold:true, width:'90px', order:1),
                new ColumnDefinition('risk_title',       'Risk Title',    type:'text',    searchable:true, bold:true, order:2),
                new ColumnDefinition('category',         'Category',      type:'badge',   sortable:true, filterable:true, width:'110px', order:3),
                new ColumnDefinition('likelihood',       'Likelihood',    type:'badge',   sortable:true, width:'80px', align:'center', order:4),
                new ColumnDefinition('impact',           'Impact',        type:'badge',   sortable:true, width:'80px', align:'center', order:5),
                new ColumnDefinition('risk_score',       'Score',         type:'number',  sortable:true, bold:true, width:'55px', align:'center', order:6),
                new ColumnDefinition('risk_level',       'Level',         type:'badge',   sortable:true, filterable:true, width:'80px', order:7),
                new ColumnDefinition('risk_owner',       'Owner',         type:'text',    sortable:true, width:'100px', order:8),
                new ColumnDefinition('mitigation_status','Mitigation',    type:'badge',   sortable:true, width:'100px', order:9),
                new ColumnDefinition('review_date',      'Next Review',   type:'date',    sortable:true, width:'100px', order:10),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('risk_level', 'Level', type:'select', quick:true, options:[
                ['value'=>'critical','label'=>'🔴 Critical (15-25)'],
                ['value'=>'high','label'=>'🟠 High (10-15)'],
                ['value'=>'medium','label'=>'🟡 Medium (5-10)'],
                ['value'=>'low','label'=>'🟢 Low (1-5)'],
            ], order:1))
            ->addFilter(new FilterDefinition('category', 'Category', type:'select', quick:true, options:[
                ['value'=>'operational','label'=>'Operational'],['value'=>'financial','label'=>'Financial'],
                ['value'=>'it','label'=>'IT'],['value'=>'security','label'=>'Security'],
                ['value'=>'legal','label'=>'Legal'],['value'=>'hr','label'=>'HR'],
                ['value'=>'supply_chain','label'=>'Supply Chain'],['value'=>'compliance','label'=>'Compliance'],
                ['value'=>'strategic','label'=>'Strategic'],['value'=>'reputation','label'=>'Reputation'],
            ], order:2))
            ->addFilter(new FilterDefinition('mitigation_status', 'Mitigation', type:'select', options:[['value'=>'none','label'=>'None'],['value'=>'planned','label'=>'Planned'],['value'=>'in_progress','label'=>'In Progress'],['value'=>'completed','label'=>'Completed']], order:3))
            ->addBulkAction(new BulkAction('assess', 'Re-Assess', variant:'primary'))
            ->addBulkAction(new BulkAction('mitigate', 'Create Mitigation', variant:'default'))
            ->addBulkAction(new BulkAction('escalate', 'Escalate', variant:'warning'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUDIT FINDINGS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function findingTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'grc.finding.index',
            title: 'Audit Findings',
            modelClass: \App\Models\Tenant\AuditFinding::class,
            defaultSort: ['finding_date' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['grc'],
        ))
            ->addColumns([
                new ColumnDefinition('finding_number',   'Finding #',     type:'text',    sortable:true, bold:true, width:'100px', order:1),
                new ColumnDefinition('audit_type',       'Audit Type',    type:'badge',   sortable:true, width:'90px', order:2),
                new ColumnDefinition('finding_title',    'Title',         type:'text',    searchable:true, bold:true, order:3),
                new ColumnDefinition('severity',         'Severity',      type:'badge',   sortable:true, filterable:true, width:'80px', order:4),
                new ColumnDefinition('module',           'Module',        type:'badge',   sortable:true, width:'90px', order:5),
                new ColumnDefinition('auditor_name',     'Auditor',       type:'text',    width:'100px', order:6),
                new ColumnDefinition('finding_date',     'Date',          type:'date',    sortable:true, width:'100px', order:7),
                new ColumnDefinition('capa_status',      'CAPA',          type:'badge',   sortable:true, width:'100px', order:8),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'85px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'open','label'=>'Open'],
                ['value'=>'in_progress','label'=>'In Progress'],
                ['value'=>'capa_created','label'=>'CAPA Created'],
                ['value'=>'verified','label'=>'Verified'],
                ['value'=>'closed','label'=>'Closed'],
            ], order:1))
            ->addFilter(new FilterDefinition('severity', 'Severity', type:'select', quick:true, options:[['value'=>'critical','label'=>'Critical'],['value'=>'major','label'=>'Major'],['value'=>'minor','label'=>'Minor'],['value'=>'observation','label'=>'Observation']], order:2))
            ->addFilter(new FilterDefinition('audit_type', 'Type', type:'select', options:[['value'=>'internal','label'=>'Internal'],['value'=>'external','label'=>'External']], order:3))
            ->addBulkAction(new BulkAction('create_capa', 'Create CAPA', variant:'primary'))
            ->addBulkAction(new BulkAction('verify', 'Verify', variant:'success'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // CAPA — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function capaTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'grc.capa.index',
            title: 'Corrective & Preventive Actions',
            modelClass: \App\Models\Tenant\CAPA::class,
            defaultSort: ['due_date' => 'asc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['grc'],
        ))
            ->addColumns([
                new ColumnDefinition('capa_number',      'CAPA #',        type:'text',    sortable:true, bold:true, width:'90px', order:1),
                new ColumnDefinition('capa_type',        'Type',          type:'badge',   sortable:true, width:'90px', order:2),
                new ColumnDefinition('finding_ref',      'Finding Ref',   type:'text',    width:'110px', order:3),
                new ColumnDefinition('action_plan',      'Action Plan',   type:'text',    searchable:true, bold:true, order:4),
                new ColumnDefinition('pic_name',         'PIC',           type:'text',    sortable:true, width:'100px', order:5),
                new ColumnDefinition('due_date',         'Due Date',      type:'date',    sortable:true, width:'100px', order:6),
                new ColumnDefinition('days_remaining',   'Remaining',     type:'number',  width:'70px', align:'center', order:7),
                new ColumnDefinition('effectiveness',    'Effectiveness', type:'badge',   width:'100px', order:8),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'85px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'open','label'=>'Open'],
                ['value'=>'in_progress','label'=>'In Progress'],
                ['value'=>'implemented','label'=>'Implemented'],
                ['value'=>'verified','label'=>'Verified'],
                ['value'=>'closed','label'=>'Closed'],
                ['value'=>'overdue','label'=>'Overdue'],
            ], order:1))
            ->addFilter(new FilterDefinition('capa_type', 'Type', type:'select', quick:true, options:[['value'=>'corrective','label'=>'Corrective'],['value'=>'preventive','label'=>'Preventive']], order:2))
            ->addBulkAction(new BulkAction('implement', 'Mark Implemented', variant:'primary'))
            ->addBulkAction(new BulkAction('verify', 'Verify Effectiveness', variant:'success'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // INCIDENTS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function incidentTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'grc.incident.index',
            title: 'Incident Management',
            modelClass: \App\Models\Tenant\Incident::class,
            defaultSort: ['reported_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['grc'],
        ))
            ->addColumns([
                new ColumnDefinition('incident_number',  'Incident #',    type:'text',    sortable:true, bold:true, width:'100px', order:1),
                new ColumnDefinition('incident_type',    'Type',          type:'badge',   sortable:true, filterable:true, width:'100px', order:2),
                new ColumnDefinition('incident_title',   'Title',         type:'text',    searchable:true, bold:true, order:3),
                new ColumnDefinition('severity',         'Severity',      type:'badge',   sortable:true, width:'80px', order:4),
                new ColumnDefinition('reporter_name',    'Reporter',      type:'text',    width:'100px', order:5),
                new ColumnDefinition('reported_at',      'Reported',      type:'datetime',sortable:true, width:'130px', order:6),
                new ColumnDefinition('root_cause',       'Root Cause',    type:'text',    order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'85px', order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'reported','label'=>'Reported'],
                ['value'=>'investigating','label'=>'Investigating'],
                ['value'=>'resolved','label'=>'Resolved'],
                ['value'=>'closed','label'=>'Closed'],
                ['value'=>'escalated','label'=>'Escalated'],
            ], order:1))
            ->addFilter(new FilterDefinition('incident_type', 'Type', type:'select', quick:true, options:[
                ['value'=>'customer_complaint','label'=>'Customer Complaint'],
                ['value'=>'data_breach','label'=>'Data Breach'],
                ['value'=>'security','label'=>'Security'],
                ['value'=>'fraud','label'=>'Fraud'],
                ['value'=>'asset_loss','label'=>'Asset Loss'],
                ['value'=>'operational','label'=>'Operational Failure'],
                ['value'=>'near_miss','label'=>'Near Miss'],
            ], order:2))
            ->addBulkAction(new BulkAction('investigate', 'Investigate', variant:'primary'))
            ->addBulkAction(new BulkAction('resolve', 'Resolve', variant:'success'))
            ->addBulkAction(new BulkAction('escalate', 'Escalate', variant:'warning'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // INTERNAL CONTROLS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function controlTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'grc.control.index',
            title: 'Internal Controls',
            modelClass: \App\Models\Tenant\InternalControl::class,
            defaultSort: ['module' => 'asc', 'control_name' => 'asc'],
            perPage: 25,
            selectable: true,
            features: ['grc'],
        ))
            ->addColumns([
                new ColumnDefinition('control_name',     'Control Name',  type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('control_type',     'Type',          type:'badge',   sortable:true, filterable:true, width:'100px', order:2),
                new ColumnDefinition('module',           'Module',        type:'badge',   sortable:true, width:'90px', order:3),
                new ColumnDefinition('control_category', 'Category',      type:'badge',   width:'100px', order:4),
                new ColumnDefinition('effectiveness',    'Effectiveness', type:'badge',   sortable:true, width:'100px', order:5),
                new ColumnDefinition('last_tested_at',   'Last Tested',   type:'date',    sortable:true, width:'100px', order:6),
                new ColumnDefinition('test_frequency',   'Frequency',     type:'text',    width:'90px', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, width:'85px', order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('effectiveness', 'Effectiveness', type:'select', quick:true, options:[['value'=>'effective','label'=>'✅ Effective'],['value'=>'weak','label'=>'⚠️ Weak'],['value'=>'failed','label'=>'❌ Failed'],['value'=>'under_review','label'=>'🔍 Under Review']], order:1))
            ->addFilter(new FilterDefinition('control_type', 'Type', type:'select', options:[['value'=>'preventive','label'=>'Preventive'],['value'=>'detective','label'=>'Detective'],['value'=>'corrective','label'=>'Corrective'],['value'=>'manual','label'=>'Manual'],['value'=>'automated','label'=>'Automated']], order:2))
            ->addBulkAction(new BulkAction('test', 'Test Control', variant:'primary'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // COMPLIANCE MATRIX — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function complianceTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'grc.compliance.index',
            title: 'Compliance Matrix',
            modelClass: \App\Models\Tenant\ComplianceRequirement::class,
            defaultSort: ['standard' => 'asc', 'requirement' => 'asc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            features: ['grc'],
        ))
            ->addColumns([
                new ColumnDefinition('standard',         'Standard',      type:'badge',   sortable:true, filterable:true, width:'110px', order:1),
                new ColumnDefinition('requirement',      'Requirement',   type:'text',    searchable:true, bold:true, order:2),
                new ColumnDefinition('clause',           'Clause',        type:'text',    width:'100px', order:3),
                new ColumnDefinition('compliance_status','Status',        type:'badge',   sortable:true, filterable:true, width:'90px', order:4),
                new ColumnDefinition('evidence_count',   'Evidence',      type:'number',  width:'65px', align:'center', order:5),
                new ColumnDefinition('last_reviewed_at', 'Last Reviewed', type:'date',    sortable:true, width:'100px', order:6),
                new ColumnDefinition('next_review_at',   'Next Review',   type:'date',    sortable:true, width:'100px', order:7),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('compliance_status', 'Status', type:'select', quick:true, options:[['value'=>'compliant','label'=>'✅ Compliant'],['value'=>'partially_compliant','label'=>'⚠️ Partial'],['value'=>'non_compliant','label'=>'❌ Non-Compliant'],['value'=>'not_applicable','label'=>'N/A']], order:1))
            ->addFilter(new FilterDefinition('standard', 'Standard', type:'select', quick:true, options:[['value'=>'iso_9001','label'=>'ISO 9001'],['value'=>'iso_27001','label'=>'ISO 27001'],['value'=>'psak','label'=>'PSAK'],['value'=>'tax','label'=>'Tax'],['value'=>'bpjs','label'=>'BPJS'],['value'=>'labor','label'=>'Labor Law'],['value'=>'internal','label'=>'Internal Policy']], order:2))
            ->addBulkAction(new BulkAction('review', 'Mark Reviewed', variant:'primary'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES — 15 GRC Rules
    // ═══════════════════════════════════════════════════════════

    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            (new AutomationDefinition('grc.risk_created', 'Risk Created',
                trigger: TriggerType::RECORD_CREATED, module: 'grc'))
                ->addStep(new AutomationStep(ActionType::AI_RISK_ASSESS, []))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '⚠️ New risk identified: {{subject.risk_title}} (Score: {{subject.risk_score}})', 'roles' => ['risk_officer','manager']])),

            (new AutomationDefinition('grc.risk_escalated', 'Risk Escalated',
                trigger: TriggerType::RECORD_UPDATED, module: 'grc'))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '🚨 Risk escalated: {{subject.risk_title}} to {{subject.risk_level}}.', 'roles' => ['director','owner']])),

            (new AutomationDefinition('grc.audit_scheduled', 'Audit Scheduled',
                trigger: TriggerType::RECORD_CREATED, module: 'grc'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'Prepare for audit: {{subject.audit_name}}', 'assignee_role' => 'internal_auditor'])),

            (new AutomationDefinition('grc.finding_created', 'Finding Created',
                trigger: TriggerType::RECORD_CREATED, module: 'grc'))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '📋 New finding: {{subject.finding_title}} ({{subject.severity}})', 'roles' => ['compliance_officer','manager']])),

            (new AutomationDefinition('grc.capa_created', 'CAPA Created',
                trigger: TriggerType::RECORD_CREATED, module: 'grc'))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '🔧 CAPA assigned: {{subject.action_plan}} to {{subject.pic_name}}.', 'roles' => ['pic']])),

            (new AutomationDefinition('grc.capa_overdue', 'CAPA Overdue',
                trigger: TriggerType::DATE_REACHED, module: 'grc'))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '⚠️ CAPA overdue: {{subject.capa_number}} — {{subject.action_plan}}', 'roles' => ['compliance_officer','manager']])),

            (new AutomationDefinition('grc.incident_reported', 'Incident Reported',
                trigger: TriggerType::RECORD_CREATED, module: 'grc'))
                ->addStep(new AutomationStep(ActionType::AI_INCIDENT_CLASSIFY, []))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '🚨 Incident reported: {{subject.incident_title}} ({{subject.incident_type}})', 'roles' => ['risk_officer','manager']])),

            (new AutomationDefinition('grc.incident_escalated', 'Incident Escalated',
                trigger: TriggerType::RECORD_UPDATED, module: 'grc'))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '🚨 Incident escalated: {{subject.incident_title}} requires immediate attention.', 'roles' => ['director','owner']])),

            (new AutomationDefinition('grc.control_failed', 'Control Failed',
                trigger: TriggerType::RECORD_UPDATED, module: 'grc'))
                ->addStep(new AutomationStep(ActionType::CREATE_FINDING, ['severity' => 'major', 'source' => 'control_failure']))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '❌ Internal control failed: {{subject.control_name}}.', 'roles' => ['compliance_officer','risk_officer']])),

            (new AutomationDefinition('grc.policy_review_due', 'Policy Review Due',
                trigger: TriggerType::DATE_REACHED, module: 'grc'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'Review policy: {{subject.policy_name}}', 'assignee_role' => 'compliance_officer'])),

            (new AutomationDefinition('grc.compliance_reminder', 'Compliance Reminder',
                trigger: TriggerType::DATE_REACHED, module: 'grc'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'Compliance review due: {{subject.requirement}}', 'assignee_role' => 'compliance_officer'])),

            (new AutomationDefinition('grc.fraud_detection', 'AI Fraud Detection',
                trigger: TriggerType::SCHEDULED, module: 'grc'))
                ->addStep(new AutomationStep(ActionType::AI_FRAUD_SCAN, ['modules' => 'all']))
                ->addStep(new AutomationStep(ActionType::CREATE_ALERT, ['condition' => 'suspicious_pattern_detected'])),

            (new AutomationDefinition('grc.risk_review_due', 'Risk Review Due',
                trigger: TriggerType::DATE_REACHED, module: 'grc'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'Review risk: {{subject.risk_title}}', 'assignee_role' => 'risk_officer'])),

            (new AutomationDefinition('grc.daily_risk_report', 'Daily Risk Report',
                trigger: TriggerType::SCHEDULED, module: 'grc'))
                ->addStep(new AutomationStep(ActionType::GENERATE_REPORT, ['report' => 'risk_dashboard']))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '📊 Daily risk dashboard ready.', 'roles' => ['risk_officer','director']])),

            (new AutomationDefinition('grc.governance_report', 'Executive Governance Report',
                trigger: TriggerType::SCHEDULED, module: 'grc'))
                ->addStep(new AutomationStep(ActionType::GENERATE_REPORT, ['report' => 'executive_governance']))
                ->addStep(new AutomationStep(ActionType::SEND_INTERNAL, ['message' => '📊 Monthly executive governance report ready.', 'roles' => ['director','owner']])),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // REPORTING DEFINITIONS — 12 GRC Reports
    // ═══════════════════════════════════════════════════════════

    /** @return ReportDefinition[] */
    public static function reports(): array
    {
        return [
            (new ReportDefinition('grc.risk_dashboard', 'Risk Dashboard',
                type:'summary', chartType:'heatmap', features:['grc']))
                ->addMetric(new MetricDefinition('risk_count', 'Risks', 'count', 'id', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('avg_score', 'Avg Score', 'avg', 'risk_score', format:'number', color:'warning'))
                ->addDimension(new DimensionDefinition('category', 'Category', 'category', type:'string'))
                ->addDimension(new DimensionDefinition('level', 'Level', 'risk_level', type:'string')),

            (new ReportDefinition('grc.compliance_dashboard', 'Compliance Dashboard',
                type:'summary', chartType:'bar', features:['grc']))
                ->addMetric(new MetricDefinition('compliant', 'Compliant', 'count', 'compliant', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('partial', 'Partial', 'count', 'partial', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('non_compliant', 'Non-Compliant', 'count', 'non_compliant', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('standard', 'Standard', 'standard', type:'string')),

            (new ReportDefinition('grc.audit_dashboard', 'Audit Dashboard',
                type:'summary', chartType:'table', features:['grc']))
                ->addMetric(new MetricDefinition('total_audits', 'Audits', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('findings', 'Findings', 'count', 'findings', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('closed', 'Closed', 'count', 'closed', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('audit_type', 'Type', 'audit_type', type:'string')),

            (new ReportDefinition('grc.incident_dashboard', 'Incident Dashboard',
                type:'summary', chartType:'bar', features:['grc']))
                ->addMetric(new MetricDefinition('reported', 'Reported', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('resolved', 'Resolved', 'count', 'resolved', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('avg_resolution_h', 'Avg Resolution (h)', 'avg', 'resolution_hours', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('type', 'Type', 'incident_type', type:'string')),

            (new ReportDefinition('grc.capa_performance', 'CAPA Performance',
                type:'summary', chartType:'table', features:['grc']))
                ->addMetric(new MetricDefinition('open', 'Open', 'count', 'open', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('overdue', 'Overdue', 'count', 'overdue', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('effectiveness', 'Effectiveness %', 'avg', 'effectiveness_pct', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('type', 'Type', 'capa_type', type:'string')),

            (new ReportDefinition('grc.governance_scorecard', 'Governance Scorecard',
                type:'summary', chartType:'kpi', features:['grc']))
                ->addMetric(new MetricDefinition('risk_score', 'Risk Management', 'last', 'risk_management_score', format:'number', color:'warning', icon:'⚠️'))
                ->addMetric(new MetricDefinition('compliance_score', 'Compliance', 'last', 'compliance_score', format:'number', color:'success', icon:'✅'))
                ->addMetric(new MetricDefinition('control_score', 'Internal Control', 'last', 'control_score', format:'number', color:'info', icon:'🔒'))
                ->addMetric(new MetricDefinition('overall', 'Governance Score', 'expression', '(risk_score + compliance_score + control_score) / 3', format:'number', color:'primary', icon:'🏆')),

            (new ReportDefinition('grc.control_effectiveness', 'Internal Control Effectiveness',
                type:'summary', chartType:'bar', features:['grc']))
                ->addMetric(new MetricDefinition('effective', 'Effective', 'count', 'effective', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('weak', 'Weak', 'count', 'weak', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('failed', 'Failed', 'count', 'failed', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('module', 'Module', 'module', type:'string')),

            (new ReportDefinition('grc.regulatory_compliance', 'Regulatory Compliance',
                type:'summary', chartType:'table', features:['grc']))
                ->addMetric(new MetricDefinition('requirements', 'Requirements', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('met', 'Met', 'count', 'met', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('gap', 'Gap', 'count', 'gap', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('standard', 'Standard', 'standard', type:'string')),

            (new ReportDefinition('grc.fraud_analysis', 'Fraud Analysis',
                type:'summary', chartType:'table', features:['grc'], permissions:['manage_grc']))
                ->addMetric(new MetricDefinition('alerts', 'Alerts', 'count', 'id', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('confirmed', 'Confirmed', 'count', 'confirmed', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('value', 'Value (Rp)', 'sum', 'fraud_value', format:'currency', color:'danger'))
                ->addDimension(new DimensionDefinition('module', 'Module', 'module', type:'string')),

            (new ReportDefinition('grc.ai_risk_insight', 'AI Risk Insight',
                type:'summary', chartType:'table', features:['grc']))
                ->addMetric(new MetricDefinition('predictions', 'Predictions', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('accuracy', 'Accuracy %', 'avg', 'accuracy_pct', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('insight_type', 'Type', 'insight_type', type:'string')),

            (new ReportDefinition('grc.executive_report', 'Executive Governance Report',
                type:'summary', chartType:'kpi', features:['grc']))
                ->addMetric(new MetricDefinition('governance_score', 'Governance', 'last', 'governance_score', format:'number', color:'primary', icon:'🏛️'))
                ->addMetric(new MetricDefinition('critical_risks', 'Critical Risks', 'count', 'critical_risks', format:'number', color:'danger', icon:'⚠️'))
                ->addMetric(new MetricDefinition('open_findings', 'Open Findings', 'count', 'open_findings', format:'number', color:'warning', icon:'📋'))
                ->addMetric(new MetricDefinition('compliance_pct', 'Compliance %', 'last', 'compliance_pct', format:'number', color:'success', icon:'✅')),

            (new ReportDefinition('grc.audit_kpi', 'Audit KPI',
                type:'summary', chartType:'kpi', features:['grc']))
                ->addMetric(new MetricDefinition('audits_completed', 'Audits Done', 'count', 'id', format:'number', color:'success', icon:'🔍'))
                ->addMetric(new MetricDefinition('findings_closed', 'Findings Closed', 'count', 'closed', format:'number', color:'primary', icon:'✅'))
                ->addMetric(new MetricDefinition('capa_on_time', 'CAPA On Time %', 'avg', 'on_time_pct', format:'number', color:'info', icon:'⏱️'))
                ->addMetric(new MetricDefinition('audit_score', 'Audit Score', 'last', 'audit_score', format:'number', color:'primary', icon:'🏆')),
        ];
    }
}
