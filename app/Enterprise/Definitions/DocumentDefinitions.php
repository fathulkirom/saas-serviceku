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
 * DocumentDefinitions — ALL Enterprise definitions for Document Management, Knowledge Base & Collaboration.
 * 
 * Covers: DMS, Version Control, Knowledge Base, Collaboration, Digital Approval,
 * Digital Signature, OCR, Document Security.
 * 
 * MODUL ERP KEDUA BELAS — ENTERPRISE DOCUMENT MANAGEMENT, KNOWLEDGE BASE & COLLABORATION
 */
class DocumentDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // DOCUMENT WORKSPACE (16 tabs)
    // ═══════════════════════════════════════════════════════════

    public static function workspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'document',
            title: 'Document Workspace',
            icon: '📄',
            tabs: [
                ['id' => 'overview',      'label' => 'Overview',        'icon' => '📊'],
                ['id' => 'preview',       'label' => 'Preview',         'icon' => '👁️'],
                ['id' => 'versions',      'label' => 'Versions',        'icon' => '🔄'],
                ['id' => 'approval',      'label' => 'Approval',        'icon' => '✅'],
                ['id' => 'history',       'label' => 'History',         'icon' => '📜'],
                ['id' => 'metadata',      'label' => 'Metadata',        'icon' => '🏷️'],
                ['id' => 'sharing',       'label' => 'Sharing',         'icon' => '🔗'],
                ['id' => 'permissions',   'label' => 'Permissions',     'icon' => '🔐'],
                ['id' => 'comments',      'label' => 'Comments',        'icon' => '💬'],
                ['id' => 'attachments',   'label' => 'Attachments',     'icon' => '📎'],
                ['id' => 'ocr',           'label' => 'OCR',             'icon' => '🔍'],
                ['id' => 'timeline',      'label' => 'Timeline',        'icon' => '🕐'],
                ['id' => 'activity',      'label' => 'Activity Log',    'icon' => '📊'],
                ['id' => 'audit',         'label' => 'Audit Trail',     'icon' => '🛡️'],
                ['id' => 'history',       'label' => 'Full History',    'icon' => '📚'],
                ['id' => 'documents',     'label' => 'Related Docs',    'icon' => '📑'],
            ],
            actions: [
                ['id' => 'upload',         'label' => 'Upload Document',  'roles' => ['owner','admin','document_controller','department_manager','employee']],
                ['id' => 'new_version',    'label' => 'New Version',      'roles' => ['owner','admin','document_controller']],
                ['id' => 'request_approval','label' => 'Request Approval', 'roles' => ['owner','admin','document_controller','department_manager']],
                ['id' => 'share',          'label' => 'Share Document',   'roles' => ['owner','admin','document_controller','department_manager','employee']],
                ['id' => 'add_comment',    'label' => 'Add Comment',      'roles' => ['all']],
                ['id' => 'run_ocr',        'label' => 'Run OCR',          'roles' => ['owner','admin','document_controller']],
                ['id' => 'publish_knowledge','label' => 'Publish Knowledge','roles' => ['owner','admin','document_controller','knowledge_editor']],
                ['id' => 'export',         'label' => 'Export',           'roles' => ['owner','admin','document_controller','department_manager']],
            ],
            sidebarWidgets: [
                ['id' => 'document_info',     'component' => 'DocumentInfo',    'priority' => 10],
                ['id' => 'approval_status',   'component' => 'ApprovalStatus',  'priority' => 20],
                ['id' => 'related_docs',      'component' => 'RelatedDocs',     'priority' => 30],
                ['id' => 'quick_actions',     'component' => 'QuickActions',    'priority' => 40],
            ],
            features: ['documents'],
            permissions: ['manage_documents'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // DOCUMENT LIBRARY — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function documentTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'document.index',
            title: 'Document Library',
            modelClass: \App\Models\Tenant\Document::class,
            defaultSort: ['updated_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['documents'],
        ))
            ->addColumns([
                new ColumnDefinition('document_number',  'Doc #',         type:'text',    sortable:true, bold:true, width:'110px', order:1),
                new ColumnDefinition('title',            'Title',         type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('category',         'Category',      type:'badge',   sortable:true, filterable:true, width:'90px', order:3),
                new ColumnDefinition('folder',           'Folder',        type:'text',    sortable:true, width:'110px', order:4),
                new ColumnDefinition('owner_name',       'Owner',         type:'text',    sortable:true, width:'100px', order:5),
                new ColumnDefinition('department',       'Department',    type:'text',    sortable:true, width:'110px', order:6),
                new ColumnDefinition('version',          'Ver',           type:'text',    sortable:true, width:'55px', align:'center', order:7),
                new ColumnDefinition('file_type',        'Type',          type:'badge',   sortable:true, width:'70px', order:8),
                new ColumnDefinition('file_size_kb',     'Size (KB)',     type:'number',  width:'70px', align:'right', order:9),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'80px', order:10),
                new ColumnDefinition('approval_status',  'Approval',      type:'badge',   sortable:true, filterable:true, width:'90px', order:11),
                new ColumnDefinition('created_at',       'Created',       type:'date',    sortable:true, width:'100px', order:12),
                new ColumnDefinition('updated_at',       'Updated',       type:'date',    sortable:true, width:'100px', order:13),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('category', 'Category', type:'select', quick:true, options:[
                ['value'=>'sop','label'=>'SOP'],
                ['value'=>'policy','label'=>'Policy'],
                ['value'=>'manual','label'=>'Manual'],
                ['value'=>'report','label'=>'Report'],
                ['value'=>'contract','label'=>'Contract'],
                ['value'=>'invoice','label'=>'Invoice'],
                ['value'=>'certificate','label'=>'Certificate'],
                ['value'=>'drawing','label'=>'Drawing'],
                ['value'=>'other','label'=>'Other'],
            ], order:1))
            ->addFilter(new FilterDefinition('approval_status', 'Approval', type:'select', quick:true, options:[
                ['value'=>'draft','label'=>'Draft'],
                ['value'=>'pending','label'=>'Pending'],
                ['value'=>'approved','label'=>'Approved'],
                ['value'=>'rejected','label'=>'Rejected'],
                ['value'=>'expired','label'=>'Expired'],
            ], order:2))
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', options:[['value'=>'active','label'=>'Active'],['value'=>'archived','label'=>'Archived'],['value'=>'trashed','label'=>'Trashed']], order:3))
            ->addFilter(new FilterDefinition('file_type', 'File Type', type:'select', options:[['value'=>'pdf','label'=>'PDF'],['value'=>'docx','label'=>'Word'],['value'=>'xlsx','label'=>'Excel'],['value'=>'png','label'=>'Image'],['value'=>'cad','label'=>'CAD']], order:4))
            ->addFilter(new FilterDefinition('department', 'Department', type:'select', order:5))
            ->addFilter(new FilterDefinition('created_at', 'Date', type:'date_range', order:6))
            ->addBulkAction(new BulkAction('request_approval', 'Request Approval', variant:'primary'))
            ->addBulkAction(new BulkAction('archive', 'Archive', variant:'default'))
            ->addBulkAction(new BulkAction('delete', 'Move to Trash', variant:'danger', confirm:true))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // DOCUMENT FORM
    // ═══════════════════════════════════════════════════════════

    public static function documentForm(): FormDefinition
    {
        return (new FormDefinition(
            id: 'document.create',
            title: 'Upload Document',
            method: 'POST',
            endpoint: '/documents',
            features: ['documents'],
        ))
            ->addSection(new FormSection(id:'general',     label:'Informasi Umum',    icon:'📄', cols:2))
            ->addSection(new FormSection(id:'metadata',    label:'Metadata',           icon:'🏷️', cols:2))
            ->addSection(new FormSection(id:'classification',label:'Classification',   icon:'🔐', cols:2))
            ->addSection(new FormSection(id:'permissions', label:'Permissions',        icon:'🔑', cols:2))
            ->addSection(new FormSection(id:'approval',    label:'Approval',           icon:'✅', cols:2))
            ->addSection(new FormSection(id:'retention',   label:'Retention',          icon:'⏱️', cols:2))
            ->addSection(new FormSection(id:'security',    label:'Security',           icon:'🛡️', cols:2))
            ->addSection(new FormSection(id:'sharing',     label:'Sharing',            icon:'🔗', cols:1))
            ->addSection(new FormSection(id:'notes',       label:'Notes',              icon:'📝', cols:1))
            ->addFields([
                new FormField('title',            type:'text',     label:'Title',               required:true, section:'general', cols:12),
                new FormField('document_number',  type:'text',     label:'Document Number',     section:'general', cols:4),
                new FormField('category',         type:'select',   label:'Category',            required:true, section:'general', cols:4,
                    options:[['value'=>'sop','label'=>'SOP'],['value'=>'policy','label'=>'Policy'],['value'=>'manual','label'=>'Manual'],['value'=>'report','label'=>'Report'],['value'=>'contract','label'=>'Contract'],['value'=>'certificate','label'=>'Certificate']]),
                new FormField('folder',           type:'select',   label:'Folder',              section:'general', cols:4),
                new FormField('description',      type:'textarea', label:'Description',         section:'general', cols:12),
                new FormField('file',             type:'file',     label:'Upload File',         required:true, section:'general', cols:6),
                new FormField('tags',             type:'tags',     label:'Tags',                section:'general', cols:6),
                // Metadata
                new FormField('author',           type:'text',     label:'Author',              section:'metadata', cols:4),
                new FormField('department_id',    type:'select',   label:'Department',          section:'metadata', cols:4),
                new FormField('branch_id',        type:'select',   label:'Branch',              section:'metadata', cols:4),
                new FormField('reference_number', type:'text',     label:'Reference Number',    section:'metadata', cols:4),
                new FormField('effective_date',   type:'date',     label:'Effective Date',      section:'metadata', cols:4),
                new FormField('expiry_date',      type:'date',     label:'Expiry Date',         section:'metadata', cols:4),
                // Classification
                new FormField('classification',   type:'select',   label:'Classification',      section:'classification', cols:4,
                    options:[['value'=>'public','label'=>'Public'],['value'=>'internal','label'=>'Internal'],['value'=>'confidential','label'=>'Confidential'],['value'=>'restricted','label'=>'Restricted']]),
                new FormField('sensitivity',      type:'select',   label:'Sensitivity',          section:'classification', cols:4,
                    options:[['value'=>'normal','label'=>'Normal'],['value'=>'sensitive','label'=>'Sensitive'],['value'=>'critical','label'=>'Critical']]),
                // Permissions
                new FormField('owner_id',         type:'select',   label:'Document Owner',      required:true, section:'permissions', cols:6),
                new FormField('viewer_roles',     type:'select',   label:'Viewer Roles',        section:'permissions', cols:6, multiple:true),
                new FormField('editor_roles',     type:'select',   label:'Editor Roles',        section:'permissions', cols:6, multiple:true),
                // Approval
                new FormField('approval_required',type:'switch',   label:'Requires Approval',    section:'approval', cols:4),
                new FormField('approver_ids',     type:'select',   label:'Approvers',            section:'approval', cols:8, multiple:true),
                // Retention
                new FormField('retention_period', type:'select',   label:'Retention Period',     section:'retention', cols:4,
                    options:[['value'=>'1_year','label'=>'1 Year'],['value'=>'3_years','label'=>'3 Years'],['value'=>'5_years','label'=>'5 Years'],['value'=>'7_years','label'=>'7 Years'],['value'=>'permanent','label'=>'Permanent']]),
                new FormField('archive_after_expiry',type:'switch',label:'Auto-Archive After Expiry',section:'retention', cols:4),
                // Security
                new FormField('watermark',        type:'switch',   label:'Add Watermark',        section:'security', cols:4),
                new FormField('download_restricted',type:'switch', label:'Restrict Download',     section:'security', cols:4),
                new FormField('print_restricted', type:'switch',   label:'Restrict Print',        section:'security', cols:4),
                // Sharing
                new FormField('share_link',       type:'switch',   label:'Generate Share Link',  section:'sharing', cols:4),
                new FormField('share_expiry',     type:'date',     label:'Share Expiry',         section:'sharing', cols:4),
                // Notes
                new FormField('internal_notes',   type:'textarea', label:'Internal Notes',        section:'notes', cols:12),
            ])
            ->addAction(new FormAction('save_draft', 'Save Draft', variant:'outline'))
            ->addAction(new FormAction('publish', 'Publish', variant:'primary', shortcut:'Ctrl+S'))
            ->addAction(new FormAction('save_and_new', 'Save & New', variant:'secondary'));
    }

    // ═══════════════════════════════════════════════════════════
    // KNOWLEDGE BASE — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function knowledgeTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'document.knowledge.index',
            title: 'Knowledge Base',
            modelClass: \App\Models\Tenant\KnowledgeArticle::class,
            defaultSort: ['updated_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['documents'],
        ))
            ->addColumns([
                new ColumnDefinition('title',            'Title',         type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('category',         'Category',      type:'badge',   sortable:true, filterable:true, width:'90px', order:2),
                new ColumnDefinition('article_type',     'Type',          type:'badge',   sortable:true, width:'80px', order:3),
                new ColumnDefinition('author_name',      'Author',        type:'text',    width:'100px', order:4),
                new ColumnDefinition('revision',         'Rev',           type:'text',    width:'50px', align:'center', order:5),
                new ColumnDefinition('view_count',       'Views',         type:'number',  width:'60px', align:'center', order:6),
                new ColumnDefinition('rating',           'Rating',        type:'number',  width:'55px', align:'center', order:7),
                new ColumnDefinition('bookmark_count',   'Bookmarks',     type:'number',  width:'70px', align:'center', order:8),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'80px', order:9),
                new ColumnDefinition('created_at',       'Created',       type:'date',    sortable:true, width:'100px', order:10),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'draft','label'=>'Draft'],
                ['value'=>'published','label'=>'Published'],
                ['value'=>'archived','label'=>'Archived'],
                ['value'=>'under_review','label'=>'Under Review'],
            ], order:1))
            ->addFilter(new FilterDefinition('article_type', 'Type', type:'select', quick:true, options:[
                ['value'=>'wiki','label'=>'Wiki'],
                ['value'=>'faq','label'=>'FAQ'],
                ['value'=>'sop','label'=>'SOP'],
                ['value'=>'manual','label'=>'Manual'],
                ['value'=>'tutorial','label'=>'Tutorial'],
                ['value'=>'how_to','label'=>'How-To'],
            ], order:2))
            ->addFilter(new FilterDefinition('category', 'Category', type:'select', order:3))
            ->addBulkAction(new BulkAction('publish', 'Publish', variant:'primary'))
            ->addBulkAction(new BulkAction('archive', 'Archive', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // APPROVAL QUEUE — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function approvalTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'document.approval.index',
            title: 'Approval Queue',
            modelClass: \App\Models\Tenant\DocumentApproval::class,
            defaultSort: ['requested_at' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            features: ['documents'],
        ))
            ->addColumns([
                new ColumnDefinition('document_title',   'Document',      type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('requester_name',   'Requester',     type:'text',    sortable:true, width:'100px', order:2),
                new ColumnDefinition('approver_name',    'Approver',      type:'text',    sortable:true, width:'100px', order:3),
                new ColumnDefinition('approval_level',   'Level',         type:'number',  width:'55px', align:'center', order:4),
                new ColumnDefinition('requested_at',     'Requested',     type:'datetime',sortable:true, width:'130px', order:5),
                new ColumnDefinition('sla_deadline',     'SLA Deadline',  type:'datetime',sortable:true, width:'130px', order:6),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'90px', order:7),
                new ColumnDefinition('comment',          'Comment',       type:'text',    order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'pending','label'=>'Pending'],
                ['value'=>'approved','label'=>'Approved'],
                ['value'=>'rejected','label'=>'Rejected'],
                ['value'=>'revision','label'=>'Revision Requested'],
                ['value'=>'escalated','label'=>'Escalated'],
            ], order:1))
            ->addFilter(new FilterDefinition('requested_at', 'Date', type:'date_range', order:2))
            ->addBulkAction(new BulkAction('approve', 'Approve', variant:'primary'))
            ->addBulkAction(new BulkAction('reject', 'Reject', variant:'danger'))
            ->addBulkAction(new BulkAction('escalate', 'Escalate', variant:'warning'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES — 15 Rules
    // ═══════════════════════════════════════════════════════════

    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            (new AutomationDefinition('doc.uploaded', 'Document Uploaded',
                trigger: TriggerType::RECORD_CREATED, module: 'document'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '📄 Document {{subject.title}} uploaded by {{user.name}}.'])),

            (new AutomationDefinition('doc.updated', 'Document Updated',
                trigger: TriggerType::RECORD_UPDATED, module: 'document'))
                ->addStep(new AutomationStep(ActionType::CREATE_VERSION, ['type' => 'auto']))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '📝 Document {{subject.title}} updated.'])),

            (new AutomationDefinition('doc.version_published', 'Version Published',
                trigger: TriggerType::RECORD_UPDATED, module: 'document'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🔄 New Version', 'body' => 'v{{subject.version}} of {{subject.title}} published.', 'roles' => ['document_controller']])),

            (new AutomationDefinition('doc.approval_requested', 'Approval Requested',
                trigger: TriggerType::RECORD_UPDATED, module: 'document'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '📋 Approval Requested', 'body' => '{{subject.title}} needs your approval.']))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'Review: {{subject.title}}', 'assignee_id' => '{{subject.approver_id}}'])),

            (new AutomationDefinition('doc.approval_completed', 'Approval Completed',
                trigger: TriggerType::RECORD_UPDATED, module: 'document'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '✅ {{subject.title}} {{subject.approval_status}} by {{subject.approver_name}}.'])),

            (new AutomationDefinition('doc.expired', 'Document Expired',
                trigger: TriggerType::DATE_REACHED, module: 'document'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'Review expired: {{subject.title}}', 'assignee_role' => 'document_controller']))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '⏰ Document Expired', 'body' => '{{subject.title}} has expired.', 'roles' => ['document_controller']])),

            (new AutomationDefinition('doc.review_due', 'Review Due',
                trigger: TriggerType::DATE_REACHED, module: 'document'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, ['title' => 'Periodic review: {{subject.title}}', 'assignee_role' => 'document_controller'])),

            (new AutomationDefinition('doc.knowledge_published', 'Knowledge Published',
                trigger: TriggerType::RECORD_UPDATED, module: 'document'))
                ->addStep(new AutomationStep(ActionType::CREATE_ANNOUNCEMENT, ['title' => '📚 New Article', 'body' => '{{subject.title}} has been published!'])),

            (new AutomationDefinition('doc.knowledge_updated', 'Knowledge Updated',
                trigger: TriggerType::RECORD_UPDATED, module: 'document'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '📝 KB article {{subject.title}} updated.'])),

            (new AutomationDefinition('doc.announcement_published', 'Announcement Published',
                trigger: TriggerType::RECORD_CREATED, module: 'document'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '📢 {{subject.title}}', 'body' => '{{subject.summary}}', 'roles' => ['all']])),

            (new AutomationDefinition('doc.comment_mentioned', 'Comment Mentioned',
                trigger: TriggerType::RECORD_CREATED, module: 'document'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '💬 You were mentioned', 'body' => '{{user.name}} mentioned you in {{subject.document_title}}.'])),

            (new AutomationDefinition('doc.ocr_completed', 'OCR Completed',
                trigger: TriggerType::RECORD_UPDATED, module: 'document'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '🔍 OCR completed for {{subject.title}}.'])),

            (new AutomationDefinition('doc.retention_triggered', 'Retention Triggered',
                trigger: TriggerType::DATE_REACHED, module: 'document'))
                ->addStep(new AutomationStep(ActionType::ARCHIVE_DOCUMENT, []))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '📦 {{subject.title}} archived per retention policy.'])),

            (new AutomationDefinition('doc.archive_triggered', 'Archive Triggered',
                trigger: TriggerType::DATE_REACHED, module: 'document'))
                ->addStep(new AutomationStep(ActionType::ARCHIVE_DOCUMENT, []))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '📦 {{subject.title}} moved to archive.'])),

            (new AutomationDefinition('doc.restored', 'Document Restored',
                trigger: TriggerType::RECORD_UPDATED, module: 'document'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '♻️ {{subject.title}} restored from {{subject.previous_status}}.'])),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // REPORTING DEFINITIONS — 15 Reports
    // ═══════════════════════════════════════════════════════════

    /** @return ReportDefinition[] */
    public static function reports(): array
    {
        return [
            (new ReportDefinition('doc.usage', 'Document Usage',
                type:'summary', chartType:'bar', features:['documents']))
                ->addMetric(new MetricDefinition('view_count', 'Views', 'sum', 'view_count', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('download_count', 'Downloads', 'sum', 'download_count', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('category', 'Category', 'category', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            (new ReportDefinition('doc.approval_performance', 'Approval Performance',
                type:'summary', chartType:'table', features:['documents']))
                ->addMetric(new MetricDefinition('total_requests', 'Requests', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('approved', 'Approved', 'count', 'approved', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('avg_sla_hours', 'Avg SLA (h)', 'avg', 'sla_hours', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('approver', 'Approver', 'approver_name', type:'string')),

            (new ReportDefinition('doc.knowledge_activity', 'Knowledge Base Activity',
                type:'summary', chartType:'bar', features:['documents']))
                ->addMetric(new MetricDefinition('articles', 'Articles', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('total_views', 'Views', 'sum', 'view_count', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('avg_rating', 'Avg Rating', 'avg', 'rating', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('category', 'Category', 'category', type:'string')),

            (new ReportDefinition('doc.most_viewed', 'Most Viewed Documents',
                type:'summary', chartType:'bar', features:['documents']))
                ->addMetric(new MetricDefinition('views', 'Views', 'sum', 'view_count', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('title', 'Document', 'title', type:'string')),

            (new ReportDefinition('doc.search_analytics', 'Search Analytics',
                type:'summary', chartType:'table', features:['documents']))
                ->addMetric(new MetricDefinition('search_count', 'Searches', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('result_count', 'Results', 'avg', 'result_count', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('query', 'Query', 'query', type:'string')),

            (new ReportDefinition('doc.download_report', 'Download Report',
                type:'summary', chartType:'table', features:['documents']))
                ->addMetric(new MetricDefinition('downloads', 'Downloads', 'count', 'id', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('title', 'Document', 'title', type:'string'))
                ->addDimension(new DimensionDefinition('user', 'User', 'user_name', type:'string')),

            (new ReportDefinition('doc.version_history', 'Version History',
                type:'summary', chartType:'table', features:['documents']))
                ->addMetric(new MetricDefinition('version_count', 'Versions', 'count', 'id', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('title', 'Document', 'title', type:'string')),

            (new ReportDefinition('doc.department_usage', 'Department Usage',
                type:'summary', chartType:'bar', features:['documents']))
                ->addMetric(new MetricDefinition('doc_count', 'Documents', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('upload_count', 'Uploads', 'count', 'uploads', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('department', 'Department', 'department', type:'string')),

            (new ReportDefinition('doc.expiration', 'Document Expiration',
                type:'summary', chartType:'table', features:['documents']))
                ->addMetric(new MetricDefinition('expired_count', 'Expired', 'count', 'expired', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('expiring_soon', 'Expiring (30d)', 'count', 'expiring_soon', format:'number', color:'warning'))
                ->addDimension(new DimensionDefinition('category', 'Category', 'category', type:'string')),

            (new ReportDefinition('doc.compliance', 'Compliance Report',
                type:'summary', chartType:'table', features:['documents']))
                ->addMetric(new MetricDefinition('approved_docs', 'Approved', 'count', 'approved', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('expired_docs', 'Expired', 'count', 'expired', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('pending_review', 'Pending Review', 'count', 'pending_review', format:'number', color:'warning'))
                ->addDimension(new DimensionDefinition('department', 'Department', 'department', type:'string')),

            (new ReportDefinition('doc.collaboration_activity', 'Collaboration Activity',
                type:'summary', chartType:'bar', features:['documents']))
                ->addMetric(new MetricDefinition('comments', 'Comments', 'count', 'comments', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('mentions', 'Mentions', 'count', 'mentions', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('shares', 'Shares', 'count', 'shares', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'created_at', type:'date')),

            (new ReportDefinition('doc.approval_sla', 'Approval SLA',
                type:'summary', chartType:'table', features:['documents']))
                ->addMetric(new MetricDefinition('sla_met', 'SLA Met %', 'avg', 'sla_met_pct', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('avg_hours', 'Avg Hours', 'avg', 'approval_hours', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('escalated', 'Escalated', 'count', 'escalated', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('department', 'Department', 'department', type:'string')),

            (new ReportDefinition('doc.knowledge_contribution', 'Knowledge Contribution',
                type:'summary', chartType:'bar', features:['documents']))
                ->addMetric(new MetricDefinition('articles', 'Articles', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('views_received', 'Views', 'sum', 'view_count', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('avg_rating', 'Rating', 'avg', 'rating', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('author', 'Author', 'author_name', type:'string')),

            (new ReportDefinition('doc.audit_report', 'Audit Report',
                type:'summary', chartType:'table', features:['documents']))
                ->addMetric(new MetricDefinition('actions', 'Actions', 'count', 'id', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('document_title', 'Document', 'document_title', type:'string'))
                ->addDimension(new DimensionDefinition('action', 'Action', 'action', type:'string'))
                ->addDimension(new DimensionDefinition('user', 'User', 'user_name', type:'string')),

            (new ReportDefinition('doc.security_report', 'Security Report',
                type:'summary', chartType:'table', features:['documents']))
                ->addMetric(new MetricDefinition('restricted_docs', 'Restricted', 'count', 'restricted', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('download_blocked', 'DL Blocked', 'count', 'download_blocked', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('access_denied', 'Access Denied', 'count', 'access_denied', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('classification', 'Classification', 'classification', type:'string')),
        ];
    }
}
