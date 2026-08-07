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
 * ProjectDefinitions — ALL Enterprise definitions for Project, Task & Job Management.
 * 
 * Covers: Project Management, Task Management, Job Management,
 * Kanban Board, Gantt Chart, Milestones, Resource Management,
 * Time Tracking, Project Costing, Risk & Issue Management.
 * 
 * MODUL ERP KEDELAPAN — ENTERPRISE PROJECT, TASK & JOB MANAGEMENT
 */
class ProjectDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // PROJECT WORKSPACE (14 tabs)
    // ═══════════════════════════════════════════════════════════

    public static function workspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'project',
            title: 'Project Workspace',
            icon: '📁',
            tabs: [
                ['id' => 'overview',      'label' => 'Overview',       'icon' => '📊'],
                ['id' => 'planning',      'label' => 'Planning',       'icon' => '📋'],
                ['id' => 'tasks',         'label' => 'Tasks',          'icon' => '✅'],
                ['id' => 'kanban',        'label' => 'Kanban',         'icon' => '📌'],
                ['id' => 'gantt',         'label' => 'Gantt',          'icon' => '📅'],
                ['id' => 'timeline',      'label' => 'Timeline',       'icon' => '🕐'],
                ['id' => 'milestones',    'label' => 'Milestones',     'icon' => '🎯'],
                ['id' => 'resources',     'label' => 'Resources',      'icon' => '👥'],
                ['id' => 'files',         'label' => 'Files',          'icon' => '📄'],
                ['id' => 'budget',        'label' => 'Budget',         'icon' => '💰'],
                ['id' => 'cost',          'label' => 'Cost',           'icon' => '💵'],
                ['id' => 'risks',         'label' => 'Risks',          'icon' => '⚠️'],
                ['id' => 'issues',        'label' => 'Issues',         'icon' => '🐛'],
                ['id' => 'activity',      'label' => 'Activity Log',   'icon' => '📊'],
            ],
            actions: [
                ['id' => 'edit',          'label' => 'Edit Project',    'roles' => ['owner','manager','project_manager']],
                ['id' => 'add_task',      'label' => 'Add Task',        'roles' => ['owner','manager','project_manager','supervisor']],
                ['id' => 'add_milestone', 'label' => 'Add Milestone',   'roles' => ['owner','manager','project_manager']],
                ['id' => 'allocate_resource','label' => 'Allocate Resource','roles' => ['owner','manager','project_manager']],
                ['id' => 'add_risk',      'label' => 'Add Risk',        'roles' => ['owner','manager','project_manager']],
                ['id' => 'add_issue',     'label' => 'Log Issue',       'roles' => ['owner','manager','project_manager','supervisor','technician']],
                ['id' => 'approve',       'label' => 'Approve',         'roles' => ['owner','manager']],
                ['id' => 'close',         'label' => 'Close Project',   'roles' => ['owner','manager','project_manager']],
                ['id' => 'export',        'label' => 'Export',          'roles' => ['owner','manager','project_manager']],
            ],
            sidebarWidgets: [
                ['id' => 'project_health',    'component' => 'ProjectHealth',   'priority' => 10],
                ['id' => 'quick_actions',     'component' => 'QuickActions',    'priority' => 20],
                ['id' => 'team_members',      'component' => 'TeamMembers',     'priority' => 30],
                ['id' => 'pending_decisions', 'component' => 'PendingDecisions','priority' => 40],
            ],
            features: ['projects'],
            permissions: ['manage_projects'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // PROJECT MASTER — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function projectTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'project.index',
            title: 'Project Portfolio',
            modelClass: \App\Models\Tenant\Project::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['projects'],
        ))
            ->addColumns([
                new ColumnDefinition('project_code',     'Kode',          type:'text',    sortable:true, bold:true, width:'100px', order:1),
                new ColumnDefinition('project_name',     'Nama Project',  type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('category',         'Kategori',      type:'badge',   sortable:true, filterable:true, width:'100px', order:3),
                new ColumnDefinition('client_name',      'Client',        type:'text',    sortable:true, width:'120px', order:4),
                new ColumnDefinition('priority',         'Prioritas',     type:'badge',   sortable:true, filterable:true, width:'80px', order:5),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'100px', order:6),
                new ColumnDefinition('progress_pct',     'Progress',      type:'progress', sortable:true, width:'100px', order:7),
                new ColumnDefinition('start_date',       'Mulai',         type:'date',    sortable:true, width:'100px', order:8),
                new ColumnDefinition('end_date',         'Selesai',       type:'date',    sortable:true, width:'100px', order:9),
                new ColumnDefinition('budget',           'Budget',        type:'currency', sortable:true, align:'right', width:'130px', order:10),
                new ColumnDefinition('actual_cost',      'Actual Cost',   type:'currency', sortable:true, align:'right', width:'130px', order:11),
                new ColumnDefinition('project_manager',  'PM',            type:'text',    sortable:true, width:'110px', order:12),
                new ColumnDefinition('branch',           'Cabang',        type:'text',    sortable:true, width:'90px', order:13),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'planning','label'=>'Planning'],
                ['value'=>'approved','label'=>'Approved'],
                ['value'=>'in_progress','label'=>'In Progress'],
                ['value'=>'on_hold','label'=>'On Hold'],
                ['value'=>'completed','label'=>'Completed'],
                ['value'=>'cancelled','label'=>'Cancelled'],
                ['value'=>'closed','label'=>'Closed'],
            ], order:1))
            ->addFilter(new FilterDefinition('category', 'Kategori', type:'select', quick:true, options:[
                ['value'=>'internal','label'=>'Internal'],
                ['value'=>'client','label'=>'Client'],
                ['value'=>'service','label'=>'Service'],
                ['value'=>'installation','label'=>'Installation'],
                ['value'=>'maintenance_contract','label'=>'Maintenance Contract'],
                ['value'=>'development','label'=>'Development'],
                ['value'=>'renovation','label'=>'Renovation'],
                ['value'=>'marketing','label'=>'Marketing'],
                ['value'=>'research','label'=>'Research'],
            ], order:2))
            ->addFilter(new FilterDefinition('priority', 'Prioritas', type:'select', options:[['value'=>'critical','label'=>'Critical'],['value'=>'high','label'=>'High'],['value'=>'medium','label'=>'Medium'],['value'=>'low','label'=>'Low']], order:3))
            ->addFilter(new FilterDefinition('start_date', 'Tanggal', type:'date_range', order:4))
            ->addFilter(new FilterDefinition('branch', 'Cabang', type:'select', order:5))
            ->addBulkAction(new BulkAction('approve', 'Approve', variant:'primary'))
            ->addBulkAction(new BulkAction('archive', 'Archive', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // PROJECT FORM — Create/Edit
    // ═══════════════════════════════════════════════════════════

    public static function projectForm(): FormDefinition
    {
        return (new FormDefinition(
            id: 'project.create',
            title: 'Project Registration',
            method: 'POST',
            endpoint: '/projects',
            features: ['projects'],
        ))
            ->addSection(new FormSection(id:'general',    label:'Informasi Umum',    icon:'📋', cols:2))
            ->addSection(new FormSection(id:'planning',   label:'Perencanaan',       icon:'📅', cols:2))
            ->addSection(new FormSection(id:'budget',     label:'Budget',            icon:'💰', cols:2))
            ->addSection(new FormSection(id:'resources',  label:'Resources',         icon:'👥', cols:2))
            ->addSection(new FormSection(id:'schedule',   label:'Schedule',          icon:'🕐', cols:2))
            ->addSection(new FormSection(id:'customer',   label:'Customer',          icon:'🏢', cols:2))
            ->addSection(new FormSection(id:'risk',       label:'Risk Assessment',   icon:'⚠️', cols:1))
            ->addSection(new FormSection(id:'documents',  label:'Documents',         icon:'📎', cols:1))
            ->addSection(new FormSection(id:'notes',      label:'Catatan',           icon:'📝', cols:1))
            ->addFields([
                // General
                new FormField('project_code',     type:'text',     label:'Kode Project',        required:true, section:'general', cols:4, maxlength:20),
                new FormField('project_name',     type:'text',     label:'Nama Project',        required:true, section:'general', cols:8),
                new FormField('category',         type:'select',   label:'Kategori',            required:true, section:'general', cols:4,
                    options:[['value'=>'internal','label'=>'Internal'],['value'=>'client','label'=>'Client'],['value'=>'service','label'=>'Service'],['value'=>'installation','label'=>'Installation'],['value'=>'maintenance_contract','label'=>'Maintenance Contract'],['value'=>'development','label'=>'Development']]),
                new FormField('priority',         type:'select',   label:'Prioritas',           section:'general', cols:4, options:[['value'=>'critical','label'=>'Critical'],['value'=>'high','label'=>'High'],['value'=>'medium','label'=>'Medium'],['value'=>'low','label'=>'Low']]),
                new FormField('status',           type:'select',   label:'Status',              section:'general', cols:4, options:[['value'=>'planning','label'=>'Planning'],['value'=>'approved','label'=>'Approved']]),
                new FormField('department_id',    type:'select',   label:'Department',          section:'general', cols:4),
                new FormField('branch_id',        type:'select',   label:'Cabang',              section:'general', cols:4),
                new FormField('tags',             type:'tags',     label:'Tags',                section:'general', cols:4),
                new FormField('description',      type:'textarea', label:'Deskripsi',           section:'general', cols:12),
                // Planning
                new FormField('objective',        type:'textarea', label:'Objective',           section:'planning', cols:12),
                new FormField('scope',            type:'textarea', label:'Scope',               section:'planning', cols:12),
                new FormField('deliverables',     type:'textarea', label:'Deliverables',        section:'planning', cols:12),
                new FormField('methodology',      type:'select',   label:'Methodology',         section:'planning', cols:4,
                    options:[['value'=>'waterfall','label'=>'Waterfall'],['value'=>'agile','label'=>'Agile'],['value'=>'hybrid','label'=>'Hybrid']]),
                // Budget
                new FormField('budget',           type:'currency', label:'Budget',              section:'budget', cols:4),
                new FormField('labor_budget',     type:'currency', label:'Labor Budget',        section:'budget', cols:4),
                new FormField('material_budget',  type:'currency', label:'Material Budget',     section:'budget', cols:4),
                new FormField('external_budget',  type:'currency', label:'External Budget',     section:'budget', cols:4),
                new FormField('contingency_pct',  type:'number',   label:'Contingency %',       section:'budget', cols:4),
                // Resources
                new FormField('project_manager_id',type:'select',  label:'Project Manager',     required:true, section:'resources', cols:4),
                new FormField('team_members',     type:'select',   label:'Team Members',        section:'resources', cols:8, multiple:true),
                // Schedule
                new FormField('start_date',       type:'date',     label:'Start Date',          required:true, section:'schedule', cols:4),
                new FormField('end_date',         type:'date',     label:'End Date',            required:true, section:'schedule', cols:4),
                new FormField('baseline_start',   type:'date',     label:'Baseline Start',      section:'schedule', cols:4),
                new FormField('baseline_end',     type:'date',     label:'Baseline End',        section:'schedule', cols:4),
                // Customer
                new FormField('client_id',        type:'select',   label:'Client',              section:'customer', cols:4),
                new FormField('client_po',        type:'text',     label:'Client PO #',         section:'customer', cols:4),
                new FormField('contract_value',   type:'currency', label:'Contract Value',      section:'customer', cols:4),
                new FormField('sla_terms',        type:'text',     label:'SLA Terms',           section:'customer', cols:6),
                // Risk
                new FormField('risk_register',    type:'repeater', label:'Risk Register',       section:'risk', cols:12, fields:[
                    ['name'=>'risk','type'=>'text','label'=>'Risk','cols'=>5],
                    ['name'=>'probability','type'=>'select','label'=>'Probability','cols'=>3],
                    ['name'=>'impact','type'=>'select','label'=>'Impact','cols'=>3],
                    ['name'=>'mitigation','type'=>'text','label'=>'Mitigation','cols'=>1],
                ]),
                // Documents
                new FormField('documents',        type:'file',     label:'Documents',           section:'documents', cols:6, multiple:true),
                // Notes
                new FormField('internal_notes',   type:'textarea', label:'Internal Notes',      section:'notes', cols:12),
            ])
            ->addAction(new FormAction('save_draft', 'Save Draft', variant:'outline'))
            ->addAction(new FormAction('save', 'Save Project', variant:'primary', shortcut:'Ctrl+S'))
            ->addAction(new FormAction('save_and_new', 'Save & New', variant:'secondary'));
    }

    // ═══════════════════════════════════════════════════════════
    // TASK MANAGEMENT — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function taskTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'project.task.index',
            title: 'Task Management',
            modelClass: \App\Models\Tenant\ProjectTask::class,
            defaultSort: ['priority_order' => 'asc', 'due_date' => 'asc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['projects'],
        ))
            ->addColumns([
                new ColumnDefinition('task_code',        'Kode',          type:'text',    sortable:true, bold:true, width:'90px', order:1),
                new ColumnDefinition('task_name',        'Task',          type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('project_name',     'Project',       type:'text',    sortable:true, width:'120px', order:3),
                new ColumnDefinition('assignee_name',    'Assignee',      type:'text',    sortable:true, width:'110px', order:4),
                new ColumnDefinition('priority',         'Prioritas',     type:'badge',   sortable:true, width:'80px', order:5),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'90px', order:6),
                new ColumnDefinition('due_date',         'Due Date',      type:'date',    sortable:true, width:'100px', order:7),
                new ColumnDefinition('estimate_hours',   'Est (h)',       type:'number',  width:'70px', align:'center', order:8),
                new ColumnDefinition('actual_hours',     'Actual (h)',    type:'number',  width:'70px', align:'center', order:9),
                new ColumnDefinition('progress_pct',     'Progress',      type:'progress',sortable:true, width:'90px', order:10),
                new ColumnDefinition('labels',           'Labels',        type:'tags',    width:'100px', order:11),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'backlog','label'=>'Backlog'],
                ['value'=>'todo','label'=>'To Do'],
                ['value'=>'in_progress','label'=>'In Progress'],
                ['value'=>'review','label'=>'Review'],
                ['value'=>'testing','label'=>'Testing'],
                ['value'=>'done','label'=>'Done'],
                ['value'=>'blocked','label'=>'Blocked'],
            ], order:1))
            ->addFilter(new FilterDefinition('priority', 'Prioritas', type:'select', options:[['value'=>'critical','label'=>'Critical'],['value'=>'high','label'=>'High'],['value'=>'medium','label'=>'Medium'],['value'=>'low','label'=>'Low']], order:2))
            ->addFilter(new FilterDefinition('assignee_id', 'Assignee', type:'select', order:3))
            ->addFilter(new FilterDefinition('project_id', 'Project', type:'select', order:4))
            ->addFilter(new FilterDefinition('due_date', 'Due Date', type:'date_range', order:5))
            ->addBulkAction(new BulkAction('assign', 'Assign', variant:'primary'))
            ->addBulkAction(new BulkAction('move_stage', 'Move Stage', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // JOB MANAGEMENT — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function jobTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'project.job.index',
            title: 'Job Management',
            modelClass: \App\Models\Tenant\Job::class,
            defaultSort: ['scheduled_date' => 'asc', 'priority_order' => 'asc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['projects'],
        ))
            ->addColumns([
                new ColumnDefinition('job_code',         'Job #',         type:'text',    sortable:true, bold:true, width:'90px', order:1),
                new ColumnDefinition('job_name',         'Job Name',      type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('job_type',         'Tipe',          type:'badge',   sortable:true, filterable:true, width:'90px', order:3),
                new ColumnDefinition('technician_name',  'Teknisi',       type:'text',    sortable:true, width:'110px', order:4),
                new ColumnDefinition('customer_name',    'Customer',      type:'text',    width:'110px', order:5),
                new ColumnDefinition('project_name',     'Project',       type:'text',    width:'110px', order:6),
                new ColumnDefinition('scheduled_date',   'Jadwal',        type:'date',    sortable:true, width:'100px', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'100px', order:8),
                new ColumnDefinition('completed_date',   'Selesai',       type:'date',    sortable:true, width:'100px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'assigned','label'=>'Assigned'],
                ['value'=>'en_route','label'=>'En Route'],
                ['value'=>'in_progress','label'=>'In Progress'],
                ['value'=>'completed','label'=>'Completed'],
                ['value'=>'verified','label'=>'Verified'],
                ['value'=>'cancelled','label'=>'Cancelled'],
            ], order:1))
            ->addFilter(new FilterDefinition('job_type', 'Tipe', type:'select', options:[['value'=>'internal','label'=>'Internal'],['value'=>'external','label'=>'External'],['value'=>'emergency','label'=>'Emergency'],['value'=>'scheduled','label'=>'Scheduled']], order:2))
            ->addFilter(new FilterDefinition('technician_id', 'Teknisi', type:'select', order:3))
            ->addFilter(new FilterDefinition('scheduled_date', 'Tanggal', type:'date_range', order:4))
            ->addBulkAction(new BulkAction('assign', 'Assign', variant:'primary'))
            ->addBulkAction(new BulkAction('complete', 'Mark Complete', variant:'success'))
            ->addBulkAction(new BulkAction('verify', 'Verify', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // MILESTONE — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function milestoneTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'project.milestone.index',
            title: 'Milestones',
            modelClass: \App\Models\Tenant\Milestone::class,
            defaultSort: ['deadline' => 'asc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            features: ['projects'],
        ))
            ->addColumns([
                new ColumnDefinition('milestone_name',   'Milestone',     type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('project_name',     'Project',       type:'text',    sortable:true, width:'120px', order:2),
                new ColumnDefinition('deliverable',      'Deliverable',   type:'text',    order:3),
                new ColumnDefinition('deadline',         'Deadline',      type:'date',    sortable:true, width:'100px', order:4),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, width:'100px', order:5),
                new ColumnDefinition('progress_pct',     'Progress',      type:'progress',sortable:true, width:'90px', order:6),
                new ColumnDefinition('completed_date',   'Completed',     type:'date',    sortable:true, width:'100px', order:7),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'upcoming','label'=>'Upcoming'],
                ['value'=>'in_progress','label'=>'In Progress'],
                ['value'=>'completed','label'=>'Completed'],
                ['value'=>'delayed','label'=>'Delayed'],
            ], order:1))
            ->addFilter(new FilterDefinition('deadline', 'Deadline', type:'date_range', order:2))
            ->addBulkAction(new BulkAction('complete', 'Mark Complete', variant:'success'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // RISK REGISTER — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function riskTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'project.risk.index',
            title: 'Risk Register',
            modelClass: \App\Models\Tenant\ProjectRisk::class,
            defaultSort: ['severity_score' => 'desc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            features: ['projects'],
        ))
            ->addColumns([
                new ColumnDefinition('risk_description', 'Risk',          type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('project_name',     'Project',       type:'text',    sortable:true, width:'110px', order:2),
                new ColumnDefinition('probability',      'Probability',   type:'badge',   sortable:true, width:'80px', align:'center', order:3),
                new ColumnDefinition('impact',           'Impact',        type:'badge',   sortable:true, width:'80px', align:'center', order:4),
                new ColumnDefinition('severity_score',   'Severity',      type:'number',  sortable:true, width:'70px', align:'center', bold:true, order:5),
                new ColumnDefinition('mitigation',       'Mitigation',    type:'text',    order:6),
                new ColumnDefinition('owner_name',       'Owner',         type:'text',    width:'100px', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, width:'90px', order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'identified','label'=>'Identified'],
                ['value'=>'active','label'=>'Active'],
                ['value'=>'mitigated','label'=>'Mitigated'],
                ['value'=>'closed','label'=>'Closed'],
                ['value'=>'triggered','label'=>'Triggered'],
            ], order:1))
            ->addFilter(new FilterDefinition('probability', 'Probability', type:'select', options:[['value'=>'low','label'=>'Low'],['value'=>'medium','label'=>'Medium'],['value'=>'high','label'=>'High'],['value'=>'very_high','label'=>'Very High']], order:2))
            ->addBulkAction(new BulkAction('mitigate', 'Mitigate', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // ISSUE MANAGEMENT — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function issueTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'project.issue.index',
            title: 'Issue Log',
            modelClass: \App\Models\Tenant\ProjectIssue::class,
            defaultSort: ['priority_order' => 'asc', 'created_at' => 'desc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            features: ['projects'],
        ))
            ->addColumns([
                new ColumnDefinition('issue_title',      'Issue',         type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('project_name',     'Project',       type:'text',    sortable:true, width:'110px', order:2),
                new ColumnDefinition('issue_type',       'Tipe',          type:'badge',   filterable:true, width:'80px', order:3),
                new ColumnDefinition('priority',         'Prioritas',     type:'badge',   sortable:true, width:'80px', order:4),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'100px', order:5),
                new ColumnDefinition('assigned_to',      'Assigned To',   type:'text',    width:'100px', order:6),
                new ColumnDefinition('resolution',       'Resolution',    type:'text',    order:7),
                new ColumnDefinition('created_at',       'Reported',      type:'date',    sortable:true, width:'100px', order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'open','label'=>'Open'],
                ['value'=>'investigating','label'=>'Investigating'],
                ['value'=>'in_progress','label'=>'In Progress'],
                ['value'=>'resolved','label'=>'Resolved'],
                ['value'=>'closed','label'=>'Closed'],
                ['value'=>'escalated','label'=>'Escalated'],
            ], order:1))
            ->addFilter(new FilterDefinition('issue_type', 'Tipe', type:'select', options:[['value'=>'bug','label'=>'Bug'],['value'=>'blocker','label'=>'Blocker'],['value'=>'risk','label'=>'Risk'],['value'=>'change','label'=>'Change']], order:2))
            ->addFilter(new FilterDefinition('priority', 'Prioritas', type:'select', options:[['value'=>'critical','label'=>'Critical'],['value'=>'high','label'=>'High'],['value'=>'medium','label'=>'Medium'],['value'=>'low','label'=>'Low']], order:3))
            ->addBulkAction(new BulkAction('assign', 'Assign', variant:'primary'))
            ->addBulkAction(new BulkAction('resolve', 'Resolve', variant:'success'))
            ->addBulkAction(new BulkAction('escalate', 'Escalate', variant:'warning'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // TIMESHEET — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function timesheetTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'project.timesheet.index',
            title: 'Timesheets',
            modelClass: \App\Models\Tenant\Timesheet::class,
            defaultSort: ['entry_date' => 'desc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            features: ['projects'],
        ))
            ->addColumns([
                new ColumnDefinition('entry_date',       'Tanggal',       type:'date',    sortable:true, width:'100px', order:1),
                new ColumnDefinition('employee_name',    'Karyawan',      type:'text',    sortable:true, searchable:true, order:2),
                new ColumnDefinition('project_name',     'Project',       type:'text',    sortable:true, width:'110px', order:3),
                new ColumnDefinition('task_name',        'Task',          type:'text',    width:'120px', order:4),
                new ColumnDefinition('hours',            'Hours',         type:'number',  sortable:true, width:'70px', align:'center', bold:true, order:5),
                new ColumnDefinition('billable_hours',   'Billable',      type:'number',  sortable:true, width:'70px', align:'center', order:6),
                new ColumnDefinition('overtime_hours',   'Overtime',      type:'number',  width:'70px', align:'center', order:7),
                new ColumnDefinition('description',      'Description',   type:'text',    order:8),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, width:'80px', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('entry_date', 'Tanggal', type:'date_range', quick:true, order:1))
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', options:[['value'=>'draft','label'=>'Draft'],['value'=>'submitted','label'=>'Submitted'],['value'=>'approved','label'=>'Approved'],['value'=>'rejected','label'=>'Rejected']], order:2))
            ->addFilter(new FilterDefinition('employee_id', 'Karyawan', type:'select', order:3))
            ->addFilter(new FilterDefinition('project_id', 'Project', type:'select', order:4))
            ->addBulkAction(new BulkAction('submit', 'Submit', variant:'primary'))
            ->addBulkAction(new BulkAction('approve', 'Approve', variant:'success'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES — 14 Rules
    // ═══════════════════════════════════════════════════════════

    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            // 1. Project Created → Notify PM
            (new AutomationDefinition('project.created', 'Project Created',
                trigger: TriggerType::RECORD_CREATED, module: 'project'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '📁 New Project',
                    'body' => 'Project {{subject.project_name}} created.',
                ]))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                    'message' => 'Project {{subject.project_name}} created.',
                ])),

            // 2. Project Approved → Notify Team
            (new AutomationDefinition('project.approved', 'Project Approved',
                trigger: TriggerType::RECORD_UPDATED, module: 'project'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '✅ Project Approved',
                    'body' => '{{subject.project_name}} has been approved.',
                    'roles' => ['project_manager', 'supervisor'],
                ])),

            // 3. Task Assigned → Notify
            (new AutomationDefinition('project.task_assigned', 'Task Assigned',
                trigger: TriggerType::RECORD_CREATED, module: 'project'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '📋 New Task',
                    'body' => 'You\'ve been assigned: {{subject.task_name}}.',
                ])),

            // 4. Task Due Today → Reminder
            (new AutomationDefinition('project.task_due_today', 'Task Due Today',
                trigger: TriggerType::DATE_REACHED, module: 'project'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '⏰ Task Due Today',
                    'body' => '{{subject.task_name}} is due today.',
                ])),

            // 5. Task Overdue → Alert
            (new AutomationDefinition('project.task_overdue', 'Task Overdue',
                trigger: TriggerType::DATE_REACHED, module: 'project'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '⚠️ Task Overdue',
                    'body' => '{{subject.task_name}} is overdue!',
                    'roles' => ['project_manager', 'supervisor'],
                ])),

            // 6. Milestone Reached → Celebrate
            (new AutomationDefinition('project.milestone_reached', 'Milestone Reached',
                trigger: TriggerType::RECORD_UPDATED, module: 'project'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '🎯 Milestone Reached!',
                    'body' => '{{subject.milestone_name}} completed!',
                    'roles' => ['project_manager', 'manager'],
                ])),

            // 7. Project Completed → Notify
            (new AutomationDefinition('project.completed', 'Project Completed',
                trigger: TriggerType::RECORD_UPDATED, module: 'project'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                    'message' => '🎉 Project {{subject.project_name}} completed!',
                ])),

            // 8. Job Assigned → Notify Technician
            (new AutomationDefinition('project.job_assigned', 'Job Assigned',
                trigger: TriggerType::RECORD_CREATED, module: 'project'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '🔧 New Job',
                    'body' => 'Job #{{subject.job_code}} assigned to you.',
                ])),

            // 9. Job Completed → Verify
            (new AutomationDefinition('project.job_completed', 'Job Completed',
                trigger: TriggerType::RECORD_UPDATED, module: 'project'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Verify Job #{{subject.job_code}}',
                    'assignee_role' => 'supervisor',
                ])),

            // 10. Budget Exceeded → Alert
            (new AutomationDefinition('project.budget_exceeded', 'Budget Exceeded',
                trigger: TriggerType::RECORD_UPDATED, module: 'project'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '💰 Budget Exceeded',
                    'body' => '{{subject.project_name}} exceeded budget by {{subject.variance_pct}}%.',
                    'roles' => ['owner', 'manager', 'project_manager'],
                ])),

            // 11. Risk Triggered → Alert
            (new AutomationDefinition('project.risk_triggered', 'Risk Triggered',
                trigger: TriggerType::RECORD_UPDATED, module: 'project'))
                ->addStep(new AutomationStep(ActionType::CREATE_ISSUE, [
                    'title' => 'Risk triggered: {{subject.risk_description}}',
                ]))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '⚠️ Risk Triggered',
                    'body' => '{{subject.risk_description}} has been triggered.',
                    'roles' => ['project_manager'],
                ])),

            // 12. Issue Escalated → Alert
            (new AutomationDefinition('project.issue_escalated', 'Issue Escalated',
                trigger: TriggerType::RECORD_UPDATED, module: 'project'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '🚨 Issue Escalated',
                    'body' => '{{subject.issue_title}} has been escalated!',
                    'roles' => ['manager', 'project_manager'],
                ])),

            // 13. Timesheet Submitted → Approve
            (new AutomationDefinition('project.timesheet_submitted', 'Timesheet Submitted',
                trigger: TriggerType::RECORD_UPDATED, module: 'project'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Review timesheet: {{subject.employee_name}}',
                    'assignee_role' => 'project_manager',
                ])),

            // 14. Project Closed → Archive
            (new AutomationDefinition('project.closed', 'Project Closed',
                trigger: TriggerType::RECORD_UPDATED, module: 'project'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                    'message' => '📦 Project {{subject.project_name}} closed & archived.',
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
            // 1. Project Summary
            (new ReportDefinition('project.summary', 'Project Summary',
                type:'summary', chartType:'table', features:['projects']))
                ->addMetric(new MetricDefinition('total_projects', 'Total', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('budget_total', 'Budget', 'sum', 'budget', format:'currency'))
                ->addMetric(new MetricDefinition('actual_total', 'Actual', 'sum', 'actual_cost', format:'currency', color:'danger'))
                ->addMetric(new MetricDefinition('variance', 'Variance', 'expression', 'budget_total - actual_total', format:'currency', color:'success'))
                ->addDimension(new DimensionDefinition('category', 'Category', 'category', type:'string')),

            // 2. Project Progress
            (new ReportDefinition('project.progress', 'Project Progress',
                type:'summary', chartType:'bar', features:['projects']))
                ->addMetric(new MetricDefinition('progress', 'Progress %', 'avg', 'progress_pct', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('task_count', 'Tasks', 'count', 'tasks', format:'number'))
                ->addMetric(new MetricDefinition('completed_tasks', 'Completed', 'count', 'completed_tasks', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('project_name', 'Project', 'project_name', type:'string'))
                ->addFilter(new ReportFilter('status', 'Status', 'select')),

            // 3. Task Performance
            (new ReportDefinition('project.task_performance', 'Task Performance',
                type:'summary', chartType:'bar', features:['projects']))
                ->addMetric(new MetricDefinition('total_tasks', 'Total', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('completed', 'Completed', 'count', 'completed', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('overdue', 'Overdue', 'count', 'overdue', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('on_time_pct', 'On Time %', 'expression', 'completed / total_tasks * 100', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('assignee_name', 'Assignee', 'assignee_name', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            // 4. Technician Productivity
            (new ReportDefinition('project.tech_productivity', 'Technician Productivity',
                type:'summary', chartType:'bar', features:['projects']))
                ->addMetric(new MetricDefinition('jobs_completed', 'Jobs Done', 'count', 'completed', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('avg_completion_time', 'Avg Time (h)', 'avg', 'completion_hours', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('satisfaction_score', 'Satisfaction', 'avg', 'satisfaction', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('technician_name', 'Technician', 'technician_name', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            // 5. Project Cost
            (new ReportDefinition('project.cost', 'Project Cost Analysis',
                type:'summary', chartType:'bar', features:['projects'], permissions:['manage_finance']))
                ->addMetric(new MetricDefinition('labor_cost', 'Labor', 'sum', 'labor_cost', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('material_cost', 'Material', 'sum', 'material_cost', format:'currency', color:'info'))
                ->addMetric(new MetricDefinition('external_cost', 'External', 'sum', 'external_cost', format:'currency', color:'warning'))
                ->addMetric(new MetricDefinition('total_cost', 'Total', 'sum', 'actual_cost', format:'currency', color:'danger'))
                ->addDimension(new DimensionDefinition('project_name', 'Project', 'project_name', type:'string')),

            // 6. Budget Variance
            (new ReportDefinition('project.budget_variance', 'Budget Variance',
                type:'summary', chartType:'bar', features:['projects']))
                ->addMetric(new MetricDefinition('budget', 'Budget', 'sum', 'budget', format:'currency', color:'info'))
                ->addMetric(new MetricDefinition('actual', 'Actual', 'sum', 'actual_cost', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('variance', 'Variance', 'expression', 'budget - actual', format:'currency', color:'danger'))
                ->addDimension(new DimensionDefinition('project_name', 'Project', 'project_name', type:'string'))
                ->addFilter(new ReportFilter('year', 'Year', 'select')),

            // 7. Resource Utilization
            (new ReportDefinition('project.resource_utilization', 'Resource Utilization',
                type:'summary', chartType:'kpi', features:['projects']))
                ->addMetric(new MetricDefinition('total_resources', 'Total Resources', 'count', 'id', format:'number', icon:'👥'))
                ->addMetric(new MetricDefinition('allocated', 'Allocated', 'count', 'allocated', format:'number', color:'primary', icon:'✅'))
                ->addMetric(new MetricDefinition('available', 'Available', 'count', 'available', format:'number', color:'success', icon:'🟢'))
                ->addMetric(new MetricDefinition('utilization_pct', 'Utilization %', 'expression', 'allocated / total_resources * 100', format:'number', color:'info', icon:'📊')),

            // 8. Timesheet Report
            (new ReportDefinition('project.timesheet_report', 'Timesheet Report',
                type:'summary', chartType:'table', features:['projects']))
                ->addMetric(new MetricDefinition('total_hours', 'Total Hours', 'sum', 'hours', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('billable_hours', 'Billable', 'sum', 'billable_hours', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('overtime_hours', 'Overtime', 'sum', 'overtime_hours', format:'number', color:'warning'))
                ->addDimension(new DimensionDefinition('employee_name', 'Employee', 'employee_name', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            // 9. Project Profitability
            (new ReportDefinition('project.profitability', 'Project Profitability',
                type:'summary', chartType:'bar', features:['projects'], permissions:['manage_finance']))
                ->addMetric(new MetricDefinition('revenue', 'Revenue', 'sum', 'contract_value', format:'currency', color:'success'))
                ->addMetric(new MetricDefinition('cost', 'Cost', 'sum', 'actual_cost', format:'currency', color:'danger'))
                ->addMetric(new MetricDefinition('profit', 'Profit', 'expression', 'revenue - cost', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('margin_pct', 'Margin %', 'expression', '(revenue - cost) / revenue * 100', format:'number'))
                ->addDimension(new DimensionDefinition('project_name', 'Project', 'project_name', type:'string')),

            // 10. Risk Analysis
            (new ReportDefinition('project.risk_analysis', 'Risk Analysis',
                type:'summary', chartType:'heatmap', features:['projects']))
                ->addMetric(new MetricDefinition('risk_count', 'Risks', 'count', 'id', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('probability', 'Probability', 'probability', type:'string'))
                ->addDimension(new DimensionDefinition('impact', 'Impact', 'impact', type:'string')),

            // 11. Issue Report
            (new ReportDefinition('project.issue_report', 'Issue Report',
                type:'summary', chartType:'table', features:['projects']))
                ->addMetric(new MetricDefinition('open_issues', 'Open', 'count', 'open', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('resolved_issues', 'Resolved', 'count', 'resolved', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('avg_resolution_time', 'Avg Resolution (h)', 'avg', 'resolution_hours', format:'number'))
                ->addDimension(new DimensionDefinition('project_name', 'Project', 'project_name', type:'string')),

            // 12. Milestone Report
            (new ReportDefinition('project.milestone_report', 'Milestone Report',
                type:'summary', chartType:'table', features:['projects']))
                ->addMetric(new MetricDefinition('total_milestones', 'Total', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('completed', 'Completed', 'count', 'completed', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('delayed', 'Delayed', 'count', 'delayed', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('project_name', 'Project', 'project_name', type:'string')),

            // 13. Project Portfolio
            (new ReportDefinition('project.portfolio', 'Project Portfolio',
                type:'summary', chartType:'kpi', features:['projects']))
                ->addMetric(new MetricDefinition('active_projects', 'Active', 'count', 'active', format:'number', color:'primary', icon:'📁'))
                ->addMetric(new MetricDefinition('total_budget', 'Total Budget', 'sum', 'budget', format:'currency', icon:'💰'))
                ->addMetric(new MetricDefinition('total_cost', 'Total Cost', 'sum', 'actual_cost', format:'currency', color:'danger', icon:'💵'))
                ->addMetric(new MetricDefinition('avg_progress', 'Avg Progress %', 'avg', 'progress_pct', format:'number', color:'success', icon:'📊')),
        ];
    }
}
