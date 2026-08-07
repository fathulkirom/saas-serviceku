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
 * AIDefinitions — ALL Enterprise definitions for the AI Intelligence Layer.
 * 
 * Covers: AI Assistant, Workflow Intelligence, Decision Support,
 * Predictive Analytics, Natural Language ERP, Prompt Library,
 * Learning Engine, Business Insights.
 * 
 * MODUL ERP KETIGA BELAS — ENTERPRISE AI ASSISTANT, WORKFLOW INTELLIGENCE & DECISION SUPPORT
 * 
 * ⚠️ AI is an INTELLIGENCE LAYER on top of all 12 modules — not a standalone engine.
 */
class AIDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // AI WORKSPACE (16 tabs)
    // ═══════════════════════════════════════════════════════════

    public static function workspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'ai',
            title: 'AI Intelligence Workspace',
            icon: '🤖',
            tabs: [
                ['id' => 'overview',        'label' => 'Overview',           'icon' => '📊'],
                ['id' => 'chat',            'label' => 'Enterprise Chat',    'icon' => '💬'],
                ['id' => 'workflow',        'label' => 'Workflow Assistant', 'icon' => '🔄'],
                ['id' => 'recommendations', 'label' => 'Recommendations',    'icon' => '💡'],
                ['id' => 'insights',        'label' => 'Insights',           'icon' => '🔍'],
                ['id' => 'predictions',     'label' => 'Predictions',        'icon' => '📈'],
                ['id' => 'decisions',       'label' => 'Decision Center',    'icon' => '🧠'],
                ['id' => 'automations',     'label' => 'Smart Automations',  'icon' => '⚡'],
                ['id' => 'reports',         'label' => 'Smart Reports',      'icon' => '📋'],
                ['id' => 'tasks',           'label' => 'AI Tasks',           'icon' => '✅'],
                ['id' => 'notifications',   'label' => 'Notifications',      'icon' => '🔔'],
                ['id' => 'knowledge',       'label' => 'Knowledge',          'icon' => '📚'],
                ['id' => 'learning',        'label' => 'Learning',           'icon' => '🎓'],
                ['id' => 'conversations',   'label' => 'Conversations',      'icon' => '🗂️'],
                ['id' => 'prompts',         'label' => 'Prompt Library',     'icon' => '📝'],
                ['id' => 'audit',           'label' => 'Audit Trail',        'icon' => '🛡️'],
            ],
            actions: [
                ['id' => 'new_chat',        'label' => 'New Chat',          'roles' => ['all']],
                ['id' => 'ask_insight',     'label' => 'Ask for Insight',   'roles' => ['owner','manager','supervisor']],
                ['id' => 'run_prediction',  'label' => 'Run Prediction',    'roles' => ['owner','manager','finance']],
                ['id' => 'get_recommendation','label' => 'Get Recommendation','roles' => ['all']],
                ['id' => 'save_prompt',     'label' => 'Save Prompt',       'roles' => ['owner','admin','manager']],
                ['id' => 'daily_briefing',  'label' => 'Daily Briefing',    'roles' => ['owner','manager','supervisor']],
                ['id' => 'executive_summary','label' => 'Executive Summary','roles' => ['owner','manager']],
                ['id' => 'export',          'label' => 'Export',            'roles' => ['owner','admin','manager']],
            ],
            sidebarWidgets: [
                ['id' => 'ai_quick_actions',   'component' => 'AIQuickActions',  'priority' => 10],
                ['id' => 'context_summary',    'component' => 'ContextSummary',  'priority' => 20],
                ['id' => 'recent_chats',       'component' => 'RecentChats',     'priority' => 30],
                ['id' => 'saved_prompts',      'component' => 'SavedPrompts',    'priority' => 40],
            ],
            features: ['ai'],
            permissions: ['use_ai'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // AI INSIGHTS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function insightsTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'ai.insights.index',
            title: 'AI Business Insights',
            modelClass: \App\Models\Tenant\AIInsight::class,
            defaultSort: ['generated_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['ai'],
        ))
            ->addColumns([
                new ColumnDefinition('insight_type',     'Type',          type:'badge',   sortable:true, filterable:true, width:'100px', order:1),
                new ColumnDefinition('title',            'Title',          type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('module',           'Module',         type:'badge',   sortable:true, width:'100px', order:3),
                new ColumnDefinition('summary',          'Summary',        type:'text',    order:4),
                new ColumnDefinition('confidence',       'Confidence',     type:'number',  sortable:true, width:'80px', align:'center', order:5),
                new ColumnDefinition('business_impact',  'Impact',         type:'badge',   sortable:true, width:'80px', order:6),
                new ColumnDefinition('generated_at',     'Generated',      type:'datetime',sortable:true, width:'130px', order:7),
                new ColumnDefinition('generated_by',     'Generated By',   type:'text',    width:'100px', order:8),
                new ColumnDefinition('actions',          '',               type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('insight_type', 'Type', type:'select', quick:true, options:[
                ['value'=>'daily_summary','label'=>'Daily Summary'],
                ['value'=>'weekly_summary','label'=>'Weekly Summary'],
                ['value'=>'trend','label'=>'Trend Analysis'],
                ['value'=>'anomaly','label'=>'Anomaly Detection'],
                ['value'=>'risk','label'=>'Risk Alert'],
                ['value'=>'opportunity','label'=>'Opportunity'],
                ['value'=>'health','label'=>'Business Health'],
            ], order:1))
            ->addFilter(new FilterDefinition('module', 'Module', type:'select', quick:true, options:[
                ['value'=>'service','label'=>'Service'],['value'=>'inventory','label'=>'Inventory'],
                ['value'=>'purchasing','label'=>'Purchasing'],['value'=>'crm','label'=>'CRM'],
                ['value'=>'finance','label'=>'Finance'],['value'=>'hrm','label'=>'HRM'],
                ['value'=>'asset','label'=>'Asset'],['value'=>'project','label'=>'Project'],
                ['value'=>'pos','label'=>'POS'],['value'=>'manufacturing','label'=>'Manufacturing'],
                ['value'=>'warehouse','label'=>'Warehouse'],['value'=>'document','label'=>'Document'],
            ], order:2))
            ->addFilter(new FilterDefinition('generated_at', 'Date', type:'date_range', order:3))
            ->addBulkAction(new BulkAction('share', 'Share', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AI PREDICTIONS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function predictionsTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'ai.predictions.index',
            title: 'AI Predictions',
            modelClass: \App\Models\Tenant\AIPrediction::class,
            defaultSort: ['predicted_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['ai'],
        ))
            ->addColumns([
                new ColumnDefinition('prediction_type',  'Type',          type:'badge',   sortable:true, filterable:true, width:'110px', order:1),
                new ColumnDefinition('title',            'Prediction',     type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('predicted_value',  'Predicted Value',type:'text',   sortable:true, bold:true, width:'130px', order:3),
                new ColumnDefinition('confidence',       'Confidence',     type:'number',  sortable:true, width:'80px', align:'center', order:4),
                new ColumnDefinition('forecast_period',  'Period',         type:'text',    sortable:true, width:'100px', order:5),
                new ColumnDefinition('actual_value',     'Actual',         type:'text',    width:'130px', order:6),
                new ColumnDefinition('accuracy_pct',     'Accuracy',       type:'number',  sortable:true, width:'75px', align:'center', order:7),
                new ColumnDefinition('predicted_at',     'Predicted At',   type:'datetime',sortable:true, width:'130px', order:8),
                new ColumnDefinition('actions',          '',               type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('prediction_type', 'Type', type:'select', quick:true, options:[
                ['value'=>'sales','label'=>'Sales Forecast'],
                ['value'=>'revenue','label'=>'Revenue'],
                ['value'=>'profit','label'=>'Profit'],
                ['value'=>'demand','label'=>'Demand'],
                ['value'=>'stock_out','label'=>'Stock Out'],
                ['value'=>'late_payment','label'=>'Late Payment'],
                ['value'=>'churn','label'=>'Customer Churn'],
                ['value'=>'project_delay','label'=>'Project Delay'],
                ['value'=>'asset_failure','label'=>'Asset Failure'],
                ['value'=>'cash_flow','label'=>'Cash Flow'],
            ], order:1))
            ->addFilter(new FilterDefinition('forecast_period', 'Period', type:'select', order:2))
            ->addBulkAction(new BulkAction('compare', 'Compare Actual', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AI RECOMMENDATIONS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function recommendationsTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'ai.recommendations.index',
            title: 'AI Recommendations',
            modelClass: \App\Models\Tenant\AIRecommendation::class,
            defaultSort: ['priority_order' => 'asc', 'generated_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['ai'],
        ))
            ->addColumns([
                new ColumnDefinition('recommendation_type','Type',        type:'badge',   sortable:true, filterable:true, width:'100px', order:1),
                new ColumnDefinition('title',            'Recommendation', type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('module',           'Module',         type:'badge',   sortable:true, width:'100px', order:3),
                new ColumnDefinition('description',      'Description',    type:'text',    order:4),
                new ColumnDefinition('priority',         'Priority',       type:'badge',   sortable:true, width:'80px', order:5),
                new ColumnDefinition('estimated_impact', 'Est. Impact',    type:'text',    width:'120px', order:6),
                new ColumnDefinition('status',           'Status',         type:'badge',   sortable:true, width:'90px', order:7),
                new ColumnDefinition('generated_at',     'Generated',      type:'datetime',sortable:true, width:'130px', order:8),
                new ColumnDefinition('actions',          '',               type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('recommendation_type', 'Type', type:'select', quick:true, options:[
                ['value'=>'workflow','label'=>'Workflow'],
                ['value'=>'pricing','label'=>'Pricing'],
                ['value'=>'reorder','label'=>'Reorder'],
                ['value'=>'supplier','label'=>'Supplier'],
                ['value'=>'technician','label'=>'Technician'],
                ['value'=>'promotion','label'=>'Promotion'],
                ['value'=>'investment','label'=>'Investment'],
                ['value'=>'risk_mitigation','label'=>'Risk Mitigation'],
            ], order:1))
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'pending','label'=>'Pending'],
                ['value'=>'accepted','label'=>'Accepted'],
                ['value'=>'implemented','label'=>'Implemented'],
                ['value'=>'dismissed','label'=>'Dismissed'],
            ], order:2))
            ->addBulkAction(new BulkAction('accept', 'Accept', variant:'primary'))
            ->addBulkAction(new BulkAction('dismiss', 'Dismiss', variant:'default'))
            ->addBulkAction(new BulkAction('implement', 'Mark Implemented', variant:'success'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // PROMPT LIBRARY — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function promptTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'ai.prompts.index',
            title: 'Prompt Library',
            modelClass: \App\Models\Tenant\AIPrompt::class,
            defaultSort: ['updated_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['ai'],
        ))
            ->addColumns([
                new ColumnDefinition('prompt_name',      'Name',          type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('category',         'Category',      type:'badge',   sortable:true, filterable:true, width:'90px', order:2),
                new ColumnDefinition('prompt_preview',   'Prompt Preview',type:'text',   order:3),
                new ColumnDefinition('scope',            'Scope',         type:'badge',   width:'80px', order:4),
                new ColumnDefinition('usage_count',      'Used',          type:'number',  width:'55px', align:'center', order:5),
                new ColumnDefinition('avg_rating',       'Rating',        type:'number',  width:'55px', align:'center', order:6),
                new ColumnDefinition('is_favorite',      '★',             type:'boolean', width:'40px', align:'center', order:7),
                new ColumnDefinition('created_by',       'Created By',    type:'text',    width:'100px', order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('category', 'Category', type:'select', quick:true, options:[
                ['value'=>'general','label'=>'General'],
                ['value'=>'service','label'=>'Service'],
                ['value'=>'finance','label'=>'Finance'],
                ['value'=>'inventory','label'=>'Inventory'],
                ['value'=>'crm','label'=>'CRM'],
                ['value'=>'hrm','label'=>'HRM'],
                ['value'=>'project','label'=>'Project'],
                ['value'=>'manufacturing','label'=>'Manufacturing'],
            ], order:1))
            ->addFilter(new FilterDefinition('scope', 'Scope', type:'select', options:[['value'=>'personal','label'=>'Personal'],['value'=>'team','label'=>'Team'],['value'=>'department','label'=>'Department'],['value'=>'global','label'=>'Global']], order:2))
            ->addBulkAction(new BulkAction('favorite', 'Add to Favorites', variant:'default'))
            ->addBulkAction(new BulkAction('share', 'Share with Team', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AI DECISION LOG — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function decisionTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'ai.decisions.index',
            title: 'AI Decision History',
            modelClass: \App\Models\Tenant\AIDecision::class,
            defaultSort: ['decided_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['ai'],
        ))
            ->addColumns([
                new ColumnDefinition('decision_type',    'Type',          type:'badge',   sortable:true, width:'100px', order:1),
                new ColumnDefinition('question',         'Question',      type:'text',    searchable:true, bold:true, order:2),
                new ColumnDefinition('answer_summary',   'Answer',        type:'text',    order:3),
                new ColumnDefinition('confidence',       'Confidence',    type:'number',  sortable:true, width:'70px', align:'center', order:4),
                new ColumnDefinition('module_context',   'Module',        type:'badge',   width:'90px', order:5),
                new ColumnDefinition('user_name',        'User',          type:'text',    sortable:true, width:'100px', order:6),
                new ColumnDefinition('feedback',         'Feedback',      type:'badge',   width:'70px', order:7),
                new ColumnDefinition('decided_at',       'Decided At',    type:'datetime',sortable:true, width:'130px', order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('decision_type', 'Type', type:'select', quick:true, options:[
                ['value'=>'workflow','label'=>'Workflow'],
                ['value'=>'insight','label'=>'Insight'],
                ['value'=>'prediction','label'=>'Prediction'],
                ['value'=>'recommendation','label'=>'Recommendation'],
                ['value'=>'decision','label'=>'Decision Support'],
                ['value'=>'nl_query','label'=>'NL Query'],
            ], order:1))
            ->addFilter(new FilterDefinition('feedback', 'Feedback', type:'select', options:[['value'=>'positive','label'=>'👍'],['value'=>'negative','label'=>'👎'],['value'=>'none','label'=>'None']], order:2))
            ->addFilter(new FilterDefinition('decided_at', 'Date', type:'date_range', order:3))
            ->addBulkAction(new BulkAction('feedback_positive', '👍 Thumb Up', variant:'success'))
            ->addBulkAction(new BulkAction('feedback_negative', '👎 Thumb Down', variant:'danger'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES — 15 AI Rules
    // ═══════════════════════════════════════════════════════════

    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            (new AutomationDefinition('ai.daily_summary', 'Daily Business Summary',
                trigger: TriggerType::SCHEDULED, module: 'ai'))
                ->addStep(new AutomationStep(ActionType::GENERATE_INSIGHT, ['insight_type' => 'daily_summary', 'modules' => 'all']))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '📊 Daily Briefing Ready', 'body' => 'Your daily business summary is ready.', 'roles' => ['owner', 'manager']])),

            (new AutomationDefinition('ai.morning_briefing', 'Morning Briefing',
                trigger: TriggerType::SCHEDULED, module: 'ai'))
                ->addStep(new AutomationStep(ActionType::GENERATE_INSIGHT, ['insight_type' => 'morning_briefing']))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🌅 Morning Briefing', 'body' => 'Good morning! Here\'s what you need to know today.', 'roles' => ['owner', 'manager', 'supervisor']])),

            (new AutomationDefinition('ai.evening_summary', 'Evening Summary',
                trigger: TriggerType::SCHEDULED, module: 'ai'))
                ->addStep(new AutomationStep(ActionType::GENERATE_INSIGHT, ['insight_type' => 'evening_summary'])),

            (new AutomationDefinition('ai.risk_alert', 'Risk Alert',
                trigger: TriggerType::RECORD_UPDATED, module: 'ai'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '⚠️ Risk Detected', 'body' => '{{subject.title}} — {{subject.summary}}', 'roles' => ['owner', 'manager', 'supervisor']])),

            (new AutomationDefinition('ai.revenue_alert', 'Revenue Alert',
                trigger: TriggerType::RECORD_UPDATED, module: 'ai'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '💰 Revenue Alert', 'body' => '{{subject.title}}', 'roles' => ['owner', 'manager', 'finance']])),

            (new AutomationDefinition('ai.inventory_alert', 'Inventory Alert',
                trigger: TriggerType::RECORD_UPDATED, module: 'ai'))
                ->addStep(new AutomationStep(ActionType::CREATE_RECOMMENDATION, ['type' => 'reorder'])),

            (new AutomationDefinition('ai.project_alert', 'Project Alert',
                trigger: TriggerType::RECORD_UPDATED, module: 'ai'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '📁 Project Alert', 'body' => '{{subject.title}}', 'roles' => ['project_manager', 'manager']])),

            (new AutomationDefinition('ai.cash_flow_alert', 'Cash Flow Alert',
                trigger: TriggerType::RECORD_UPDATED, module: 'ai'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '💵 Cash Flow Alert', 'body' => '{{subject.title}}', 'roles' => ['owner', 'manager', 'finance']])),

            (new AutomationDefinition('ai.churn_alert', 'Customer Churn Alert',
                trigger: TriggerType::RECORD_UPDATED, module: 'ai'))
                ->addStep(new AutomationStep(ActionType::CREATE_RECOMMENDATION, ['type' => 'retention'])),

            (new AutomationDefinition('ai.employee_alert', 'Employee Performance Alert',
                trigger: TriggerType::RECORD_UPDATED, module: 'ai'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '👥 Performance Alert', 'body' => '{{subject.title}}', 'roles' => ['hrd', 'manager']])),

            (new AutomationDefinition('ai.document_expiry', 'Document Expiry Alert',
                trigger: TriggerType::DATE_REACHED, module: 'ai'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '📄 Document Expiring', 'body' => '{{subject.title}} expires soon.', 'roles' => ['document_controller']])),

            (new AutomationDefinition('ai.asset_prediction', 'Asset Maintenance Prediction',
                trigger: TriggerType::RECORD_UPDATED, module: 'ai'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'Predicted maintenance: {{subject.asset_name}}', 'assignee_role' => 'maintenance'])),

            (new AutomationDefinition('ai.purchase_recommendation', 'Purchase Recommendation',
                trigger: TriggerType::RECORD_UPDATED, module: 'ai'))
                ->addStep(new AutomationStep(ActionType::CREATE_RECOMMENDATION, ['type' => 'purchase'])),

            (new AutomationDefinition('ai.insight_ready', 'AI Insight Ready',
                trigger: TriggerType::RECORD_CREATED, module: 'ai'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '💡 New Insight', 'body' => '{{subject.title}}', 'roles' => ['owner', 'manager']])),

            (new AutomationDefinition('ai.forecast_updated', 'Forecast Updated',
                trigger: TriggerType::RECORD_UPDATED, module: 'ai'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '📈 Forecast updated: {{subject.title}}.'])),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // REPORTING DEFINITIONS — 15 AI Reports
    // ═══════════════════════════════════════════════════════════

    /** @return ReportDefinition[] */
    public static function reports(): array
    {
        return [
            (new ReportDefinition('ai.executive_summary', 'Executive AI Summary',
                type:'summary', chartType:'kpi', features:['ai'], permissions:['use_ai']))
                ->addMetric(new MetricDefinition('business_health', 'Health Score', 'last', 'health_score', format:'number', color:'primary', icon:'💚'))
                ->addMetric(new MetricDefinition('active_risks', 'Active Risks', 'count', 'active_risks', format:'number', color:'danger', icon:'⚠️'))
                ->addMetric(new MetricDefinition('open_opportunities', 'Opportunities', 'count', 'opportunities', format:'number', color:'success', icon:'💡'))
                ->addMetric(new MetricDefinition('ai_accuracy', 'AI Accuracy %', 'avg', 'accuracy_pct', format:'number', color:'info', icon:'🎯')),

            (new ReportDefinition('ai.business_health', 'Business Health',
                type:'summary', chartType:'kpi', features:['ai']))
                ->addMetric(new MetricDefinition('revenue_health', 'Revenue', 'last', 'revenue_score', format:'number', color:'success', icon:'💰'))
                ->addMetric(new MetricDefinition('operation_health', 'Operations', 'last', 'operation_score', format:'number', color:'primary', icon:'⚙️'))
                ->addMetric(new MetricDefinition('customer_health', 'Customer', 'last', 'customer_score', format:'number', color:'info', icon:'👥'))
                ->addMetric(new MetricDefinition('overall_health', 'Overall', 'last', 'health_score', format:'number', color:'success', icon:'💚')),

            (new ReportDefinition('ai.recommendations_report', 'AI Recommendations',
                type:'summary', chartType:'table', features:['ai']))
                ->addMetric(new MetricDefinition('total_recs', 'Total', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('accepted', 'Accepted', 'count', 'accepted', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('implemented', 'Implemented', 'count', 'implemented', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('type', 'Type', 'recommendation_type', type:'string')),

            (new ReportDefinition('ai.forecast_accuracy', 'Forecast Accuracy',
                type:'summary', chartType:'bar', features:['ai']))
                ->addMetric(new MetricDefinition('accuracy_pct', 'Accuracy %', 'avg', 'accuracy_pct', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('count', 'Predictions', 'count', 'id', format:'number'))
                ->addDimension(new DimensionDefinition('type', 'Type', 'prediction_type', type:'string')),

            (new ReportDefinition('ai.risk_analysis', 'Risk Analysis',
                type:'summary', chartType:'heatmap', features:['ai']))
                ->addMetric(new MetricDefinition('risk_count', 'Risks', 'count', 'id', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('module', 'Module', 'module', type:'string'))
                ->addDimension(new DimensionDefinition('severity', 'Severity', 'severity', type:'string')),

            (new ReportDefinition('ai.decision_history', 'Decision History',
                type:'summary', chartType:'table', features:['ai']))
                ->addMetric(new MetricDefinition('decisions', 'Decisions', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('positive_feedback', '👍', 'count', 'positive', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('negative_feedback', '👎', 'count', 'negative', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('type', 'Type', 'decision_type', type:'string')),

            (new ReportDefinition('ai.automation_intelligence', 'Automation Intelligence',
                type:'summary', chartType:'table', features:['ai']))
                ->addMetric(new MetricDefinition('suggestions', 'Suggestions', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('conflicts', 'Conflicts Detected', 'count', 'conflicts', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('optimizations', 'Optimizations', 'count', 'optimizations', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('module', 'Module', 'module', type:'string')),

            (new ReportDefinition('ai.conversation_analytics', 'Conversation Analytics',
                type:'summary', chartType:'bar', features:['ai']))
                ->addMetric(new MetricDefinition('total_chats', 'Chats', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('avg_satisfaction', 'Avg Satisfaction', 'avg', 'satisfaction', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('avg_response_time', 'Avg Response (s)', 'avg', 'response_time_ms', format:'number'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'created_at', type:'date')),

            (new ReportDefinition('ai.knowledge_usage', 'Knowledge Usage',
                type:'summary', chartType:'table', features:['ai']))
                ->addMetric(new MetricDefinition('queries', 'Queries', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('documents_used', 'Documents Used', 'count', 'documents', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('avg_relevance', 'Avg Relevance', 'avg', 'relevance_score', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('module', 'Module', 'module', type:'string')),

            (new ReportDefinition('ai.department_intelligence', 'Department Intelligence',
                type:'summary', chartType:'bar', features:['ai']))
                ->addMetric(new MetricDefinition('insights', 'Insights', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('actions_taken', 'Actions Taken', 'count', 'actions', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('department', 'Department', 'department', type:'string')),

            (new ReportDefinition('ai.productivity_intelligence', 'Productivity Intelligence',
                type:'summary', chartType:'bar', features:['ai']))
                ->addMetric(new MetricDefinition('workflow_suggestions', 'Suggestions', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('time_saved_hours', 'Time Saved (h)', 'sum', 'time_saved_hours', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('module', 'Module', 'module', type:'string')),

            (new ReportDefinition('ai.customer_intelligence', 'Customer Intelligence',
                type:'summary', chartType:'table', features:['ai']))
                ->addMetric(new MetricDefinition('churn_risk_count', 'Churn Risk', 'count', 'churn_risk', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('upsell_opportunity', 'Upsell', 'count', 'upsell', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('satisfaction_score', 'Satisfaction', 'avg', 'satisfaction', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('segment', 'Segment', 'segment', type:'string')),

            (new ReportDefinition('ai.financial_intelligence', 'Financial Intelligence',
                type:'summary', chartType:'kpi', features:['ai'], permissions:['manage_finance']))
                ->addMetric(new MetricDefinition('predicted_revenue', 'Predicted Revenue', 'last', 'predicted_revenue', format:'currency', color:'success', icon:'📈'))
                ->addMetric(new MetricDefinition('predicted_cashflow', 'Predicted Cash Flow', 'last', 'predicted_cashflow', format:'currency', color:'info', icon:'💵'))
                ->addMetric(new MetricDefinition('risk_exposure', 'Risk Exposure', 'last', 'risk_exposure', format:'currency', color:'danger', icon:'⚠️'))
                ->addMetric(new MetricDefinition('margin_forecast', 'Margin Forecast', 'last', 'margin_forecast', format:'number', color:'primary', icon:'🎯')),

            (new ReportDefinition('ai.operational_intelligence', 'Operational Intelligence',
                type:'summary', chartType:'kpi', features:['ai']))
                ->addMetric(new MetricDefinition('efficiency_score', 'Efficiency', 'last', 'efficiency_score', format:'number', color:'primary', icon:'⚡'))
                ->addMetric(new MetricDefinition('bottleneck_count', 'Bottlenecks', 'count', 'bottlenecks', format:'number', color:'danger', icon:'🚧'))
                ->addMetric(new MetricDefinition('optimization_count', 'Optimizations', 'count', 'optimizations', format:'number', color:'success', icon:'✨'))
                ->addMetric(new MetricDefinition('automation_coverage', 'Automation %', 'last', 'automation_pct', format:'number', color:'info', icon:'🤖')),

            (new ReportDefinition('ai.enterprise_scorecard', 'Enterprise Scorecard',
                type:'summary', chartType:'kpi', features:['ai']))
                ->addMetric(new MetricDefinition('financial', 'Financial', 'last', 'financial_score', format:'number', color:'success', icon:'💰'))
                ->addMetric(new MetricDefinition('operational', 'Operational', 'last', 'operational_score', format:'number', color:'primary', icon:'⚙️'))
                ->addMetric(new MetricDefinition('customer', 'Customer', 'last', 'customer_score', format:'number', color:'info', icon:'👥'))
                ->addMetric(new MetricDefinition('innovation', 'Innovation', 'last', 'innovation_score', format:'number', color:'warning', icon:'💡'))
                ->addMetric(new MetricDefinition('overall', 'Overall', 'expression', '(financial + operational + customer + innovation) / 4', format:'number', color:'primary', icon:'🏆')),
        ];
    }
}
