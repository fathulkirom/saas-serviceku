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
 * PortalDefinitions — ALL Enterprise definitions for Customer Portal & Technician Portal.
 * 
 * Covers: Customer Self-Service, Service Tracking, Appointment, Chat, Tickets,
 * Technician Job Management, Digital Signature, Photo Management, Warranty, Payment.
 * 
 * MODUL ERP KEENAM BELAS — ENTERPRISE CUSTOMER PORTAL & TECHNICIAN PORTAL
 * 
 * ⚠️ Portal is the CUSTOMER-FACING & TECHNICIAN-FACING layer on top of ALL existing modules.
 * Zero new database. All data from existing ServiceKU models.
 */
class PortalDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // CUSTOMER PORTAL WORKSPACE (14 tabs)
    // ═══════════════════════════════════════════════════════════

    public static function customerPortal(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'customer_portal',
            title: 'Customer Portal',
            icon: '👤',
            tabs: [
                ['id' => 'overview',         'label' => 'Overview',          'icon' => '📊'],
                ['id' => 'my_services',      'label' => 'My Services',       'icon' => '🔧'],
                ['id' => 'service_tracking', 'label' => 'Service Tracking',  'icon' => '📍'],
                ['id' => 'invoices',         'label' => 'Invoices',          'icon' => '🧾'],
                ['id' => 'payments',         'label' => 'Payments',          'icon' => '💳'],
                ['id' => 'warranty',         'label' => 'Warranty',          'icon' => '🛡️'],
                ['id' => 'devices',          'label' => 'My Devices',        'icon' => '📱'],
                ['id' => 'purchases',        'label' => 'Purchase History',  'icon' => '🛒'],
                ['id' => 'appointments',     'label' => 'Appointments',      'icon' => '📅'],
                ['id' => 'tickets',          'label' => 'Support Tickets',   'icon' => '🎫'],
                ['id' => 'messages',         'label' => 'Messages',          'icon' => '💬'],
                ['id' => 'notifications',    'label' => 'Notifications',     'icon' => '🔔'],
                ['id' => 'downloads',        'label' => 'Downloads',         'icon' => '⬇️'],
                ['id' => 'profile',          'label' => 'My Profile',        'icon' => '👤'],
            ],
            actions: [
                ['id' => 'book_appointment', 'label' => 'Book Appointment', 'roles' => ['customer']],
                ['id' => 'create_ticket',    'label' => 'Create Ticket',    'roles' => ['customer']],
                ['id' => 'send_message',     'label' => 'Send Message',     'roles' => ['customer']],
                ['id' => 'claim_warranty',   'label' => 'Claim Warranty',   'roles' => ['customer']],
                ['id' => 'make_payment',     'label' => 'Make Payment',     'roles' => ['customer']],
                ['id' => 'download_invoice', 'label' => 'Download Invoice', 'roles' => ['customer']],
                ['id' => 'edit_profile',     'label' => 'Edit Profile',     'roles' => ['customer']],
            ],
            sidebarWidgets: [
                ['id' => 'service_status',      'component' => 'ServiceStatus',     'priority' => 10],
                ['id' => 'loyalty_card',        'component' => 'LoyaltyCard',       'priority' => 20],
                ['id' => 'upcoming_appointments','component' => 'UpcomingAppointments','priority' => 30],
                ['id' => 'quick_actions',       'component' => 'QuickActions',      'priority' => 40],
            ],
            features: ['customer_portal'],
            permissions: ['access_customer_portal'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // TECHNICIAN PORTAL WORKSPACE (15 tabs)
    // ═══════════════════════════════════════════════════════════

    public static function technicianPortal(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'technician_portal',
            title: 'Technician Portal',
            icon: '🔧',
            tabs: [
                ['id' => 'overview',         'label' => 'Overview',           'icon' => '📊'],
                ['id' => 'today_jobs',       'label' => "Today's Jobs",       'icon' => '📋'],
                ['id' => 'assigned_jobs',    'label' => 'Assigned Jobs',      'icon' => '📌'],
                ['id' => 'job_detail',       'label' => 'Job Detail',         'icon' => '🔍'],
                ['id' => 'diagnosis',        'label' => 'Diagnosis',          'icon' => '🩺'],
                ['id' => 'checklist',        'label' => 'Repair Checklist',   'icon' => '✅'],
                ['id' => 'photos',           'label' => 'Photos',             'icon' => '📸'],
                ['id' => 'parts_used',       'label' => 'Parts Used',         'icon' => '🔩'],
                ['id' => 'work_timer',       'label' => 'Work Timer',         'icon' => '⏱️'],
                ['id' => 'quality_check',    'label' => 'Quality Check',      'icon' => '🔬'],
                ['id' => 'signature',        'label' => 'Customer Signature', 'icon' => '✍️'],
                ['id' => 'notes',            'label' => 'Notes',              'icon' => '📝'],
                ['id' => 'history',          'label' => 'History',            'icon' => '📜'],
                ['id' => 'notifications',    'label' => 'Notifications',      'icon' => '🔔'],
                ['id' => 'profile',          'label' => 'My Profile',         'icon' => '👤'],
            ],
            actions: [
                ['id' => 'start_job',        'label' => 'Start Job',         'roles' => ['technician']],
                ['id' => 'pause_job',        'label' => 'Pause Job',         'roles' => ['technician']],
                ['id' => 'resume_job',       'label' => 'Resume Job',        'roles' => ['technician']],
                ['id' => 'finish_job',       'label' => 'Finish Job',        'roles' => ['technician']],
                ['id' => 'upload_photo',     'label' => 'Upload Photo',      'roles' => ['technician']],
                ['id' => 'add_diagnosis',    'label' => 'Add Diagnosis',     'roles' => ['technician']],
                ['id' => 'request_parts',    'label' => 'Request Parts',     'roles' => ['technician']],
                ['id' => 'request_approval', 'label' => 'Request Approval',  'roles' => ['technician']],
                ['id' => 'get_signature',    'label' => 'Get Signature',     'roles' => ['technician', 'customer']],
                ['id' => 'escalate',         'label' => 'Escalate',          'roles' => ['technician']],
            ],
            sidebarWidgets: [
                ['id' => 'job_progress',       'component' => 'JobProgress',      'priority' => 10],
                ['id' => 'today_stats',        'component' => 'TodayStats',       'priority' => 20],
                ['id' => 'pending_approvals',  'component' => 'PendingApprovals', 'priority' => 30],
                ['id' => 'quick_actions',      'component' => 'QuickActions',     'priority' => 40],
            ],
            features: ['technician_portal'],
            permissions: ['access_technician_portal'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // CUSTOMER APPOINTMENTS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function appointmentTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'portal.appointment.index',
            title: 'My Appointments',
            modelClass: \App\Models\Tenant\Appointment::class,
            defaultSort: ['appointment_date' => 'desc'],
            perPage: 25,
            selectable: true,
            features: ['customer_portal'],
        ))
            ->addColumns([
                new ColumnDefinition('appointment_date', 'Date',          type:'date',    sortable:true, bold:true, width:'100px', order:1),
                new ColumnDefinition('time_slot',        'Time',          type:'text',    width:'90px', order:2),
                new ColumnDefinition('branch_name',      'Branch',        type:'text',    sortable:true, width:'100px', order:3),
                new ColumnDefinition('technician_name',  'Technician',    type:'text',    width:'100px', order:4),
                new ColumnDefinition('service_type',     'Service Type',  type:'badge',   sortable:true, width:'110px', order:5),
                new ColumnDefinition('device_name',      'Device',        type:'text',    order:6),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'90px', order:7),
                new ColumnDefinition('notes',            'Notes',         type:'text',    order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'scheduled','label'=>'Scheduled'],
                ['value'=>'confirmed','label'=>'Confirmed'],
                ['value'=>'completed','label'=>'Completed'],
                ['value'=>'cancelled','label'=>'Cancelled'],
                ['value'=>'no_show','label'=>'No Show'],
            ], order:1))
            ->addBulkAction(new BulkAction('reschedule', 'Reschedule', variant:'default'))
            ->addBulkAction(new BulkAction('cancel', 'Cancel', variant:'danger', confirm:true));
    }

    // ═══════════════════════════════════════════════════════════
    // CUSTOMER TICKETS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function ticketTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'portal.ticket.index',
            title: 'Support Tickets',
            modelClass: \App\Models\Tenant\SupportTicket::class,
            defaultSort: ['created_at' => 'desc'],
            perPage: 25,
            selectable: true,
            features: ['customer_portal'],
        ))
            ->addColumns([
                new ColumnDefinition('ticket_number',    'Ticket #',      type:'text',    sortable:true, bold:true, width:'100px', order:1),
                new ColumnDefinition('subject',          'Subject',       type:'text',    searchable:true, bold:true, order:2),
                new ColumnDefinition('category',         'Category',      type:'badge',   sortable:true, width:'90px', order:3),
                new ColumnDefinition('priority',         'Priority',      type:'badge',   sortable:true, width:'75px', order:4),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'85px', order:5),
                new ColumnDefinition('sla_deadline',     'SLA Deadline',  type:'date',    sortable:true, width:'100px', order:6),
                new ColumnDefinition('reply_count',      'Replies',       type:'number',  width:'60px', align:'center', order:7),
                new ColumnDefinition('last_reply_at',    'Last Reply',    type:'datetime',sortable:true, width:'130px', order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'open','label'=>'Open'],
                ['value'=>'in_progress','label'=>'In Progress'],
                ['value'=>'waiting_customer','label'=>'Waiting You'],
                ['value'=>'resolved','label'=>'Resolved'],
                ['value'=>'closed','label'=>'Closed'],
                ['value'=>'escalated','label'=>'Escalated'],
            ], order:1))
            ->addFilter(new FilterDefinition('category', 'Category', type:'select', options:[['value'=>'technical','label'=>'Technical'],['value'=>'billing','label'=>'Billing'],['value'=>'warranty','label'=>'Warranty'],['value'=>'general','label'=>'General']], order:2))
            ->addBulkAction(new BulkAction('close', 'Close Ticket', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // TECHNICIAN JOBS — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function technicianJobTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'portal.technician_job.index',
            title: 'My Jobs',
            modelClass: \App\Models\Tenant\Service::class,
            defaultSort: ['scheduled_date' => 'asc', 'priority_order' => 'asc'],
            perPage: 25,
            selectable: true,
            features: ['technician_portal'],
        ))
            ->addColumns([
                new ColumnDefinition('service_number',   'Service #',     type:'text',    sortable:true, bold:true, width:'110px', order:1),
                new ColumnDefinition('customer_name',    'Customer',      type:'text',    searchable:true, order:2),
                new ColumnDefinition('device_name',      'Device',        type:'text',    width:'120px', order:3),
                new ColumnDefinition('problem',          'Problem',       type:'text',    order:4),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'90px', order:5),
                new ColumnDefinition('priority',         'Priority',      type:'badge',   sortable:true, width:'75px', order:6),
                new ColumnDefinition('scheduled_date',   'Scheduled',     type:'date',    sortable:true, width:'100px', order:7),
                new ColumnDefinition('estimated_hours',  'Est (h)',       type:'number',  width:'55px', align:'center', order:8),
                new ColumnDefinition('actual_hours',     'Actual (h)',    type:'number',  width:'55px', align:'center', order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'assigned','label'=>'Assigned'],
                ['value'=>'diagnosing','label'=>'Diagnosing'],
                ['value'=>'waiting_parts','label'=>'Waiting Parts'],
                ['value'=>'repairing','label'=>'Repairing'],
                ['value'=>'testing','label'=>'Testing'],
                ['value'=>'completed','label'=>'Completed'],
            ], order:1))
            ->addFilter(new FilterDefinition('priority', 'Priority', type:'select', options:[['value'=>'critical','label'=>'Critical'],['value'=>'high','label'=>'High'],['value'=>'medium','label'=>'Medium'],['value'=>'low','label'=>'Low']], order:2))
            ->addBulkAction(new BulkAction('start', 'Start Job', variant:'primary'))
            ->addBulkAction(new BulkAction('finish', 'Finish Job', variant:'success'));
    }

    // ═══════════════════════════════════════════════════════════
    // APPOINTMENT BOOKING — Form
    // ═══════════════════════════════════════════════════════════

    public static function appointmentForm(): FormDefinition
    {
        return (new FormDefinition(
            id: 'portal.appointment.create',
            title: 'Book Appointment',
            method: 'POST',
            endpoint: '/portal/appointments',
            features: ['customer_portal'],
        ))
            ->addSection(new FormSection(id:'details',  label:'Appointment Details', icon:'📅', cols:2))
            ->addFields([
                new FormField('branch_id',        type:'select',   label:'Branch',              required:true, section:'details', cols:4),
                new FormField('technician_id',    type:'select',   label:'Preferred Technician', section:'details', cols:4),
                new FormField('service_type',     type:'select',   label:'Service Type',         required:true, section:'details', cols:4,
                    options:[['value'=>'repair','label'=>'Repair'],['value'=>'maintenance','label'=>'Maintenance'],['value'=>'diagnostic','label'=>'Diagnostic'],['value'=>'warranty','label'=>'Warranty Claim']]),
                new FormField('appointment_date', type:'date',     label:'Date',                 required:true, section:'details', cols:4),
                new FormField('time_slot',        type:'select',   label:'Time Slot',            required:true, section:'details', cols:4,
                    options:[['value'=>'09:00','label'=>'09:00'],['value'=>'10:00','label'=>'10:00'],['value'=>'11:00','label'=>'11:00'],['value'=>'13:00','label'=>'13:00'],['value'=>'14:00','label'=>'14:00'],['value'=>'15:00','label'=>'15:00']]),
                new FormField('device_id',        type:'select',   label:'Device',               section:'details', cols:8),
                new FormField('problem_description',type:'textarea',label:'Problem Description',   required:true, section:'details', cols:12),
                new FormField('photos',           type:'file',     label:'Photos (optional)',    section:'details', cols:6, multiple:true, accept:'image/*'),
            ])
            ->addAction(new FormAction('book', 'Book Appointment', variant:'primary', shortcut:'Ctrl+S'))
            ->addAction(new FormAction('cancel', 'Cancel', variant:'outline'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES — 12 Portal Rules
    // ═══════════════════════════════════════════════════════════

    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            (new AutomationDefinition('portal.appointment_reminder', 'Appointment Reminder',
                trigger: TriggerType::DATE_REACHED, module: 'portal'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['message' => '📅 Reminder: Anda memiliki janji servis besok pukul {{subject.time_slot}} di {{subject.branch_name}}.'])),

            (new AutomationDefinition('portal.service_status_changed', 'Service Status Changed',
                trigger: TriggerType::RECORD_UPDATED, module: 'portal'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['message' => '🔧 Status servis #{{subject.service_number}}: {{subject.status_label}}.']))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => 'Service Updated', 'body' => '#{{subject.service_number}} is now {{subject.status_label}}.'])),

            (new AutomationDefinition('portal.warranty_expiring', 'Warranty Expiring',
                trigger: TriggerType::DATE_REACHED, module: 'portal'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['message' => '⚠️ Garansi {{subject.device_name}} akan berakhir dalam {{subject.days_remaining}} hari.'])),

            (new AutomationDefinition('portal.invoice_created', 'Invoice Created',
                trigger: TriggerType::RECORD_CREATED, module: 'portal'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['message' => '🧾 Invoice #{{subject.invoice_number}} telah dibuat. Total: Rp {{format subject.total_amount}}.'])),

            (new AutomationDefinition('portal.payment_reminder', 'Payment Reminder',
                trigger: TriggerType::DATE_REACHED, module: 'portal'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['message' => '💳 Pembayaran invoice #{{subject.invoice_number}} sebesar Rp {{format subject.outstanding}} belum diterima.'])),
                
            (new AutomationDefinition('portal.ready_pickup', 'Ready Pickup',
                trigger: TriggerType::RECORD_UPDATED, module: 'portal'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['message' => '✅ Servis #{{subject.service_number}} selesai! Silakan ambil di {{subject.branch_name}}.'])),

            (new AutomationDefinition('portal.customer_feedback', 'Customer Feedback',
                trigger: TriggerType::RECORD_UPDATED, module: 'portal'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['message' => '⭐ Bagaimana pengalaman servis Anda? Beri rating: {{subject.feedback_link}}.', 'delayHours' => 24])),

            (new AutomationDefinition('portal.technician_assigned', 'Technician Assigned',
                trigger: TriggerType::RECORD_UPDATED, module: 'portal'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '🔧 Job Assigned', 'body' => 'Service #{{subject.service_number}} assigned to you.'])),

            (new AutomationDefinition('portal.technician_completed', 'Technician Completed',
                trigger: TriggerType::RECORD_UPDATED, module: 'portal'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, ['message' => '✅ Technician {{subject.technician_name}} completed #{{subject.service_number}}.'])),

            (new AutomationDefinition('portal.qc_failed', 'QC Failed',
                trigger: TriggerType::RECORD_UPDATED, module: 'portal'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, ['title' => '❌ QC Failed', 'body' => '#{{subject.service_number}} failed QC. Return to technician.', 'roles' => ['technician']])),

            (new AutomationDefinition('portal.customer_no_show', 'Customer No Show',
                trigger: TriggerType::DATE_REACHED, module: 'portal'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['message' => '📅 Anda melewatkan janji servis hari ini. Booking ulang: {{subject.booking_link}}.'])),

            (new AutomationDefinition('portal.follow_up', 'Follow Up After Service',
                trigger: TriggerType::DATE_REACHED, module: 'portal'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, ['message' => '👋 Halo {{subject.customer_name}}, semoga device Anda berfungsi baik. Ada pertanyaan? Hubungi kami.', 'delayDays' => 7])),
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // REPORTING DEFINITIONS — 12 Portal Reports
    // ═══════════════════════════════════════════════════════════

    /** @return ReportDefinition[] */
    public static function reports(): array
    {
        return [
            (new ReportDefinition('portal.customer_satisfaction', 'Customer Satisfaction',
                type:'summary', chartType:'kpi', features:['customer_portal']))
                ->addMetric(new MetricDefinition('avg_rating', 'Avg Rating', 'avg', 'rating', format:'number', color:'success', icon:'⭐'))
                ->addMetric(new MetricDefinition('nps_score', 'NPS', 'last', 'nps', format:'number', color:'primary', icon:'📊'))
                ->addMetric(new MetricDefinition('feedback_count', 'Feedbacks', 'count', 'id', format:'number', color:'info', icon:'💬'))
                ->addMetric(new MetricDefinition('response_rate', 'Response Rate %', 'avg', 'response_rate', format:'number', color:'success', icon:'📈')),

            (new ReportDefinition('portal.technician_productivity', 'Technician Productivity',
                type:'summary', chartType:'bar', features:['technician_portal']))
                ->addMetric(new MetricDefinition('completed_jobs', 'Completed', 'count', 'id', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('avg_repair_time', 'Avg Repair (h)', 'avg', 'repair_hours', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('first_fix_rate', 'First Fix %', 'avg', 'first_fix_pct', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('return_rate', 'Return Rate %', 'avg', 'return_pct', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('technician', 'Technician', 'technician_name', type:'string')),

            (new ReportDefinition('portal.warranty_report', 'Warranty Report',
                type:'summary', chartType:'table', features:['customer_portal']))
                ->addMetric(new MetricDefinition('active_warranties', 'Active', 'count', 'active', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('expiring_soon', 'Expiring (30d)', 'count', 'expiring', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('claims', 'Claims', 'count', 'claims', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'month', type:'date')),

            (new ReportDefinition('portal.appointment_report', 'Appointment Report',
                type:'summary', chartType:'table', features:['customer_portal']))
                ->addMetric(new MetricDefinition('total', 'Total', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('completed', 'Completed', 'count', 'completed', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('cancelled', 'Cancelled', 'count', 'cancelled', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('no_show', 'No Show', 'count', 'no_show', format:'number', color:'warning'))
                ->addDimension(new DimensionDefinition('branch', 'Branch', 'branch_name', type:'string')),

            (new ReportDefinition('portal.customer_activity', 'Customer Activity',
                type:'summary', chartType:'bar', features:['customer_portal']))
                ->addMetric(new MetricDefinition('logins', 'Logins', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('tickets', 'Tickets', 'count', 'tickets', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('messages', 'Messages', 'count', 'messages', format:'number', color:'info'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'month', type:'date')),

            (new ReportDefinition('portal.service_tracking', 'Service Tracking',
                type:'summary', chartType:'table', features:['customer_portal']))
                ->addMetric(new MetricDefinition('in_progress', 'In Progress', 'count', 'in_progress', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('completed', 'Completed', 'count', 'completed', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('avg_days', 'Avg Days', 'avg', 'completion_days', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('status', 'Status', 'status', type:'string')),

            (new ReportDefinition('portal.usage', 'Portal Usage',
                type:'summary', chartType:'bar', features:['customer_portal']))
                ->addMetric(new MetricDefinition('customer_logins', 'Customer Logins', 'count', 'customer_logins', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('technician_logins', 'Technician Logins', 'count', 'tech_logins', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('tracking_views', 'Tracking Views', 'count', 'tracking_views', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'month', type:'date')),

            (new ReportDefinition('portal.response_time', 'Response Time',
                type:'summary', chartType:'table', features:['customer_portal']))
                ->addMetric(new MetricDefinition('avg_first_reply', 'Avg First Reply (h)', 'avg', 'first_reply_hours', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('avg_resolution', 'Avg Resolution (h)', 'avg', 'resolution_hours', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('sla_met_pct', 'SLA Met %', 'avg', 'sla_met_pct', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('category', 'Category', 'category', type:'string')),

            (new ReportDefinition('portal.ticket_report', 'Ticket Report',
                type:'summary', chartType:'table', features:['customer_portal']))
                ->addMetric(new MetricDefinition('open', 'Open', 'count', 'open', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('resolved', 'Resolved', 'count', 'resolved', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('escalated', 'Escalated', 'count', 'escalated', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('avg_replies', 'Avg Replies', 'avg', 'reply_count', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('category', 'Category', 'category', type:'string')),

            (new ReportDefinition('portal.feedback_report', 'Feedback Report',
                type:'summary', chartType:'bar', features:['customer_portal']))
                ->addMetric(new MetricDefinition('positive', '😊 Positive', 'count', 'positive', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('neutral', '😐 Neutral', 'count', 'neutral', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('negative', '😞 Negative', 'count', 'negative', format:'number', color:'danger'))
                ->addDimension(new DimensionDefinition('category', 'Category', 'category', type:'string')),

            (new ReportDefinition('portal.customer_growth', 'Customer Portal Growth',
                type:'trend', chartType:'line', features:['customer_portal']))
                ->addMetric(new MetricDefinition('registered', 'Registered', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('active', 'Active', 'count', 'active', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'month', type:'date')),

            (new ReportDefinition('portal.technician_leaderboard', 'Technician Leaderboard',
                type:'summary', chartType:'bar', features:['technician_portal']))
                ->addMetric(new MetricDefinition('jobs_completed', 'Jobs Done', 'count', 'id', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('avg_rating', 'Rating', 'avg', 'rating', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('efficiency', 'Efficiency %', 'avg', 'efficiency_pct', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('revenue_generated', 'Revenue', 'sum', 'revenue', format:'currency', color:'success'))
                ->addDimension(new DimensionDefinition('technician', 'Technician', 'technician_name', type:'string')),
        ];
    }
}
