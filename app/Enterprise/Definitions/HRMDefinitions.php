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
 * HRMDefinitions — ALL Enterprise definitions for HRM & Employee Management.
 * 
 * Covers: Employee Master, Organization Structure, Attendance, Shift,
 * Leave, Payroll, Performance, Training, Recruitment, Employee Assets.
 * 
 * MODUL ERP KEENAM — ENTERPRISE HRM & EMPLOYEE MANAGEMENT
 */
class HRMDefinitions
{
    // ═══════════════════════════════════════════════════════════
    // EMPLOYEE WORKSPACE (14 tabs)
    // ═══════════════════════════════════════════════════════════

    public static function workspace(): WorkspaceDefinition
    {
        return new WorkspaceDefinition(
            id: 'employee',
            title: 'Employee Workspace',
            icon: '👤',
            tabs: [
                ['id' => 'overview',      'label' => 'Overview',       'icon' => '📊'],
                ['id' => 'profile',       'label' => 'Profile',        'icon' => '👤'],
                ['id' => 'attendance',    'label' => 'Attendance',     'icon' => '🕐'],
                ['id' => 'schedule',      'label' => 'Schedule',       'icon' => '📅'],
                ['id' => 'leave',         'label' => 'Leave',          'icon' => '🏖️'],
                ['id' => 'payroll',       'label' => 'Payroll',        'icon' => '💰'],
                ['id' => 'performance',   'label' => 'Performance',    'icon' => '📈'],
                ['id' => 'training',      'label' => 'Training',       'icon' => '🎓'],
                ['id' => 'recruitment',   'label' => 'Recruitment',    'icon' => '🤝'],
                ['id' => 'assets',        'label' => 'Assets',         'icon' => '💻'],
                ['id' => 'documents',     'label' => 'Documents',      'icon' => '📄'],
                ['id' => 'timeline',      'label' => 'Timeline',       'icon' => '🕐'],
                ['id' => 'activity',      'label' => 'Activity Log',   'icon' => '📊'],
                ['id' => 'notes',         'label' => 'Notes',          'icon' => '📝'],
            ],
            actions: [
                ['id' => 'edit',           'label' => 'Edit Profile',     'roles' => ['owner','hrd','admin']],
                ['id' => 'record_attendance','label' => 'Record Attendance','roles' => ['owner','hrd','manager']],
                ['id' => 'apply_leave',    'label' => 'Apply Leave',      'roles' => ['owner','hrd','manager','technician','cs','cashier','warehouse','purchasing','finance']],
                ['id' => 'add_training',   'label' => 'Add Training',     'roles' => ['owner','hrd','manager']],
                ['id' => 'start_review',   'label' => 'Performance Review','roles' => ['owner','hrd','manager']],
                ['id' => 'assign_asset',   'label' => 'Assign Asset',     'roles' => ['owner','hrd','admin']],
                ['id' => 'export',         'label' => 'Export',           'roles' => ['owner','hrd','manager']],
            ],
            sidebarWidgets: [
                ['id' => 'employee_summary',   'component' => 'EmployeeSummary',  'priority' => 10],
                ['id' => 'quick_actions',      'component' => 'QuickActions',     'priority' => 20],
                ['id' => 'org_hierarchy',      'component' => 'OrgHierarchy',     'priority' => 30],
                ['id' => 'pending_approvals',  'component' => 'HRApprovals',      'priority' => 40],
            ],
            features: ['employees'],
            permissions: ['manage_employees'],
        );
    }

    // ═══════════════════════════════════════════════════════════
    // EMPLOYEE MASTER — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function employeeTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'hrm.employee.index',
            title: 'Daftar Karyawan',
            modelClass: \App\Models\Tenant\Employee::class,
            defaultSort: ['full_name' => 'asc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['employees'],
        ))
            ->addColumns([
                new ColumnDefinition('employee_id',      'NIK',                type:'text',    sortable:true, bold:true, width:'100px', order:1),
                new ColumnDefinition('full_name',        'Nama Lengkap',       type:'text',    sortable:true, searchable:true, bold:true, order:2),
                new ColumnDefinition('nickname',         'Panggilan',          type:'text',    searchable:true, width:'110px', order:3),
                new ColumnDefinition('position',         'Jabatan',            type:'badge',   sortable:true, filterable:true, width:'120px', order:4),
                new ColumnDefinition('department',       'Department',         type:'text',    sortable:true, filterable:true, width:'110px', order:5),
                new ColumnDefinition('branch',           'Cabang',             type:'text',    sortable:true, filterable:true, width:'100px', order:6),
                new ColumnDefinition('supervisor_name',  'Atasan',             type:'text',    sortable:true, width:'120px', order:7),
                new ColumnDefinition('employment_status','Status',             type:'badge',   sortable:true, filterable:true, width:'100px', order:8),
                new ColumnDefinition('join_date',        'Tgl Masuk',          type:'date',    sortable:true, width:'100px', order:9),
                new ColumnDefinition('phone',            'Telepon',            type:'text',    width:'110px', order:10),
                new ColumnDefinition('email',            'Email',              type:'text',    width:'140px', order:11),
                new ColumnDefinition('avatar',           'Foto',               type:'avatar',  width:'60px', align:'center', order:12),
                new ColumnDefinition('actions',          '',                   type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('employment_status', 'Status', type:'select', quick:true, options:[
                ['value'=>'active','label'=>'Active'],
                ['value'=>'probation','label'=>'Probation'],
                ['value'=>'contract','label'=>'Contract'],
                ['value'=>'intern','label'=>'Intern'],
                ['value'=>'resigned','label'=>'Resigned'],
                ['value'=>'terminated','label'=>'Terminated'],
            ], order:1))
            ->addFilter(new FilterDefinition('position', 'Jabatan', type:'select', quick:true, order:2))
            ->addFilter(new FilterDefinition('department', 'Department', type:'select', quick:true, order:3))
            ->addFilter(new FilterDefinition('branch', 'Cabang', type:'select', quick:true, order:4))
            ->addFilter(new FilterDefinition('join_date', 'Tgl Masuk', type:'date_range', order:5))
            ->addBulkAction(new BulkAction('send_message', 'Send Message', variant:'default'))
            ->addBulkAction(new BulkAction('change_status', 'Change Status', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'))
            ->addBulkAction(new BulkAction('delete', 'Hapus', variant:'danger', confirm:true, permissions:['delete_models']));
    }

    // ═══════════════════════════════════════════════════════════
    // EMPLOYEE FORM — Create/Edit
    // ═══════════════════════════════════════════════════════════

    public static function employeeForm(): FormDefinition
    {
        return (new FormDefinition(
            id: 'hrm.employee.create',
            title: 'Data Karyawan',
            method: 'POST',
            endpoint: '/hrm/employees',
            features: ['employees'],
        ))
            ->addSection(new FormSection(id:'personal',    label:'Data Pribadi',     icon:'👤', cols:2))
            ->addSection(new FormSection(id:'employment',  label:'Kepegawaian',      icon:'💼', cols:2))
            ->addSection(new FormSection(id:'salary',      label:'Gaji & Tunjangan', icon:'💰', cols:2, roles:['owner','hrd']))
            ->addSection(new FormSection(id:'bank',        label:'Bank & Asuransi',  icon:'🏦', cols:2))
            ->addSection(new FormSection(id:'tax',         label:'Pajak',            icon:'🧾', cols:2))
            ->addSection(new FormSection(id:'emergency',   label:'Kontak Darurat',   icon:'🚨', cols:2))
            ->addSection(new FormSection(id:'education',   label:'Pendidikan & Skill',icon:'🎓', cols:1))
            ->addSection(new FormSection(id:'attachments', label:'Lampiran',         icon:'📎', cols:1))
            ->addSection(new FormSection(id:'notes',       label:'Catatan',          icon:'📝', cols:1))
            ->addFields([
                // Personal
                new FormField('full_name',        type:'text',     label:'Nama Lengkap',        required:true, section:'personal', cols:6),
                new FormField('nickname',         type:'text',     label:'Nama Panggilan',      section:'personal', cols:3),
                new FormField('gender',           type:'select',   label:'Gender',              section:'personal', cols:3, options:[['value'=>'male','label'=>'Laki-laki'],['value'=>'female','label'=>'Perempuan']]),
                new FormField('birth_date',       type:'date',     label:'Tanggal Lahir',       section:'personal', cols:3),
                new FormField('birth_place',      type:'text',     label:'Tempat Lahir',        section:'personal', cols:3),
                new FormField('identity_number',  type:'text',     label:'No. KTP',             section:'personal', cols:6),
                new FormField('phone',            type:'phone',    label:'Telepon',             required:true, section:'personal', cols:3),
                new FormField('email',            type:'email',    label:'Email',               section:'personal', cols:3),
                new FormField('address',          type:'textarea', label:'Alamat',              section:'personal', cols:6),
                new FormField('marital_status',   type:'select',   label:'Status',              section:'personal', cols:3, options:[['value'=>'single','label'=>'Lajang'],['value'=>'married','label'=>'Menikah'],['value'=>'divorced','label'=>'Cerai']]),
                new FormField('avatar',           type:'photo',    label:'Foto',                section:'personal', cols:3),
                // Employment
                new FormField('employee_id',      type:'text',     label:'NIK',                 required:true, section:'employment', cols:3),
                new FormField('position',         type:'select',   label:'Jabatan',             required:true, section:'employment', cols:3),
                new FormField('department',       type:'select',   label:'Department',          required:true, section:'employment', cols:3),
                new FormField('branch_id',        type:'select',   label:'Cabang',              required:true, section:'employment', cols:3),
                new FormField('supervisor_id',    type:'select',   label:'Atasan',              section:'employment', cols:4),
                new FormField('employment_status',type:'select',   label:'Status Kepegawaian',  required:true, section:'employment', cols:4, options:[['value'=>'active','label'=>'Aktif'],['value'=>'probation','label'=>'Probation'],['value'=>'contract','label'=>'Kontrak'],['value'=>'intern','label'=>'Magang']]),
                new FormField('join_date',        type:'date',     label:'Tanggal Masuk',       required:true, section:'employment', cols:4),
                new FormField('contract_start',   type:'date',     label:'Mulai Kontrak',       section:'employment', cols:3),
                new FormField('contract_end',     type:'date',     label:'Akhir Kontrak',       section:'employment', cols:3),
                // Salary (HR only)
                new FormField('basic_salary',     type:'currency', label:'Gaji Pokok',          section:'salary', cols:4),
                new FormField('allowance',        type:'currency', label:'Tunjangan',           section:'salary', cols:4),
                new FormField('other_allowance',  type:'currency', label:'Tunjangan Lain',      section:'salary', cols:4),
                // Bank
                new FormField('bank_name',        type:'text',     label:'Nama Bank',           section:'bank', cols:4),
                new FormField('bank_account',     type:'text',     label:'No. Rekening',        section:'bank', cols:4),
                new FormField('npwp',             type:'text',     label:'NPWP',                section:'bank', cols:4),
                new FormField('bpjs_health',      type:'text',     label:'BPJS Kesehatan',      section:'bank', cols:3),
                new FormField('bpjs_labor',       type:'text',     label:'BPJS Ketenagakerjaan',section:'bank', cols:3),
                // Tax
                new FormField('tax_status',       type:'select',   label:'Status Pajak',        section:'tax', cols:4, options:[['value'=>'tk0','label'=>'TK/0'],['value'=>'k0','label'=>'K/0'],['value'=>'k1','label'=>'K/1'],['value'=>'k2','label'=>'K/2'],['value'=>'k3','label'=>'K/3']]),
                new FormField('ptkp_code',        type:'text',     label:'Kode PTKP',           section:'tax', cols:4),
                // Emergency
                new FormField('emergency_name',   type:'text',     label:'Nama',                section:'emergency', cols:6),
                new FormField('emergency_relation',type:'select',  label:'Hubungan',            section:'emergency', cols:6),
                new FormField('emergency_phone',  type:'phone',    label:'Telepon Darurat',     section:'emergency', cols:6),
                // Education & Skills
                new FormField('education',        type:'repeater', label:'Pendidikan',          section:'education', cols:12, fields:[
                    ['name'=>'level','type'=>'select','label'=>'Jenjang','cols'=>4],
                    ['name'=>'institution','type'=>'text','label'=>'Institusi','cols'=>4],
                    ['name'=>'year','type'=>'text','label'=>'Tahun','cols'=>2],
                ]),
                new FormField('skills',           type:'tags',     label:'Skills',             section:'education', cols:6),
                new FormField('certifications',   type:'repeater', label:'Sertifikasi',         section:'education', cols:12, fields:[
                    ['name'=>'name','type'=>'text','label'=>'Sertifikasi','cols'=>5],
                    ['name'=>'issuer','type'=>'text','label'=>'Penerbit','cols'=>4],
                    ['name'=>'expiry','type'=>'date','label'=>'Expiry','cols'=>3],
                ]),
                // Attachments
                new FormField('documents',        type:'file',     label:'Dokumen',            section:'attachments', cols:6, multiple:true),
                new FormField('contract_file',    type:'file',     label:'Kontrak',            section:'attachments', cols:6),
                // Notes
                new FormField('internal_notes',   type:'textarea', label:'Catatan Internal',    section:'notes', cols:12),
            ])
            ->addAction(new FormAction('save_draft', 'Simpan Draft', variant:'outline'))
            ->addAction(new FormAction('save', 'Simpan', variant:'primary', shortcut:'Ctrl+S'))
            ->addAction(new FormAction('save_and_new', 'Simpan & Baru', variant:'secondary'));
    }

    // ═══════════════════════════════════════════════════════════
    // ATTENDANCE — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function attendanceTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'hrm.attendance.index',
            title: 'Attendance Records',
            modelClass: \App\Models\Tenant\Attendance::class,
            defaultSort: ['attendance_date' => 'desc', 'clock_in' => 'desc'],
            perPage: 50,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['employees'],
        ))
            ->addColumns([
                new ColumnDefinition('attendance_date',  'Tanggal',       type:'date',    sortable:true, bold:true, width:'100px', order:1),
                new ColumnDefinition('employee_name',    'Nama',          type:'text',    sortable:true, searchable:true, order:2),
                new ColumnDefinition('shift',            'Shift',         type:'badge',   sortable:true, width:'80px', order:3),
                new ColumnDefinition('clock_in',         'Jam Masuk',     type:'time',    sortable:true, width:'90px', order:4),
                new ColumnDefinition('clock_out',        'Jam Keluar',    type:'time',    sortable:true, width:'90px', order:5),
                new ColumnDefinition('working_hours',    'Jam Kerja',     type:'number',  sortable:true, width:'80px', align:'center', order:6),
                new ColumnDefinition('overtime_hours',   'Lembur',        type:'number',  sortable:true, width:'70px', align:'center', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'90px', order:8),
                new ColumnDefinition('late_minutes',     'Terlambat',     type:'number',  sortable:true, width:'80px', align:'center', order:9),
                new ColumnDefinition('location',         'Lokasi',        type:'text',    width:'120px', order:10),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('attendance_date', 'Tanggal', type:'date_range', quick:true, order:1))
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'present','label'=>'Present'],
                ['value'=>'late','label'=>'Late'],
                ['value'=>'absent','label'=>'Absent'],
                ['value'=>'leave','label'=>'On Leave'],
                ['value'=>'holiday','label'=>'Holiday'],
            ], order:2))
            ->addFilter(new FilterDefinition('shift', 'Shift', type:'select', order:3))
            ->addFilter(new FilterDefinition('employee_id', 'Karyawan', type:'select', order:4))
            ->addBulkAction(new BulkAction('correct', 'Correction', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // LEAVE — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function leaveTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'hrm.leave.index',
            title: 'Leave Requests',
            modelClass: \App\Models\Tenant\LeaveRequest::class,
            defaultSort: ['start_date' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['employees'],
        ))
            ->addColumns([
                new ColumnDefinition('employee_name',    'Nama',          type:'text',    sortable:true, searchable:true, order:1),
                new ColumnDefinition('leave_type',       'Tipe Cuti',     type:'badge',   sortable:true, filterable:true, width:'100px', order:2),
                new ColumnDefinition('start_date',       'Mulai',         type:'date',    sortable:true, width:'100px', order:3),
                new ColumnDefinition('end_date',         'Selesai',       type:'date',    sortable:true, width:'100px', order:4),
                new ColumnDefinition('total_days',       'Total Hari',    type:'number',  sortable:true, width:'80px', align:'center', order:5),
                new ColumnDefinition('remaining_balance','Sisa Cuti',     type:'number',  sortable:true, width:'80px', align:'center', order:6),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'100px', order:7),
                new ColumnDefinition('approved_by',      'Approved By',   type:'text',    width:'110px', order:8),
                new ColumnDefinition('reason',           'Alasan',        type:'text',    order:9),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'pending','label'=>'Pending'],
                ['value'=>'approved','label'=>'Approved'],
                ['value'=>'rejected','label'=>'Rejected'],
                ['value'=>'cancelled','label'=>'Cancelled'],
            ], order:1))
            ->addFilter(new FilterDefinition('leave_type', 'Tipe', type:'select', quick:true, options:[
                ['value'=>'annual','label'=>'Annual'],
                ['value'=>'sick','label'=>'Sick'],
                ['value'=>'permission','label'=>'Permission'],
                ['value'=>'maternity','label'=>'Maternity'],
                ['value'=>'paternity','label'=>'Paternity'],
                ['value'=>'unpaid','label'=>'Unpaid'],
                ['value'=>'special','label'=>'Special'],
            ], order:2))
            ->addFilter(new FilterDefinition('start_date', 'Tanggal', type:'date_range', order:3))
            ->addBulkAction(new BulkAction('approve', 'Approve', variant:'primary'))
            ->addBulkAction(new BulkAction('reject', 'Reject', variant:'danger'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // PAYROLL — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function payrollTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'hrm.payroll.index',
            title: 'Payroll Records',
            modelClass: \App\Models\Tenant\Payroll::class,
            defaultSort: ['payroll_period' => 'desc', 'employee_name' => 'asc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['employees'],
            permissions: ['manage_payroll'],
        ))
            ->addColumns([
                new ColumnDefinition('payroll_period',   'Period',        type:'text',    sortable:true, bold:true, width:'100px', order:1),
                new ColumnDefinition('employee_name',    'Nama',          type:'text',    sortable:true, searchable:true, order:2),
                new ColumnDefinition('basic_salary',     'Gaji Pokok',    type:'currency', sortable:true, align:'right', width:'130px', order:3),
                new ColumnDefinition('allowances',       'Tunjangan',     type:'currency', sortable:true, align:'right', width:'120px', order:4),
                new ColumnDefinition('overtime_pay',     'Lembur',        type:'currency', sortable:true, align:'right', width:'110px', order:5),
                new ColumnDefinition('bonus',            'Bonus',         type:'currency', sortable:true, align:'right', width:'110px', order:6),
                new ColumnDefinition('deductions',       'Potongan',      type:'currency', sortable:true, align:'right', width:'120px', order:7),
                new ColumnDefinition('tax_amount',       'PPh 21',        type:'currency', sortable:true, align:'right', width:'110px', order:8),
                new ColumnDefinition('net_salary',       'Take Home Pay', type:'currency', sortable:true, align:'right', bold:true, width:'140px', aggregate:true, aggregateType:'sum', order:9),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'90px', order:10),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('payroll_period', 'Period', type:'select', quick:true, order:1))
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'draft','label'=>'Draft'],
                ['value'=>'approved','label'=>'Approved'],
                ['value'=>'paid','label'=>'Paid'],
            ], order:2))
            ->addFilter(new FilterDefinition('department', 'Department', type:'select', order:3))
            ->addFilter(new FilterDefinition('branch', 'Cabang', type:'select', order:4))
            ->addBulkAction(new BulkAction('approve', 'Approve', variant:'primary'))
            ->addBulkAction(new BulkAction('mark_paid', 'Mark Paid', variant:'success'))
            ->addBulkAction(new BulkAction('print_slip', 'Print Slip', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // PERFORMANCE — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function performanceTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'hrm.performance.index',
            title: 'Performance Evaluations',
            modelClass: \App\Models\Tenant\PerformanceReview::class,
            defaultSort: ['review_period' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['employees'],
        ))
            ->addColumns([
                new ColumnDefinition('review_period',    'Period',        type:'text',    sortable:true, bold:true, width:'100px', order:1),
                new ColumnDefinition('employee_name',    'Nama',          type:'text',    sortable:true, searchable:true, order:2),
                new ColumnDefinition('kpi_score',        'KPI Score',     type:'number',  sortable:true, width:'80px', align:'center', order:3),
                new ColumnDefinition('target_achievement','Target %',     type:'number',  sortable:true, width:'80px', align:'center', order:4),
                new ColumnDefinition('attendance_score',  'Attendance',   type:'number',  sortable:true, width:'80px', align:'center', order:5),
                new ColumnDefinition('overall_score',     'Overall',      type:'number',  sortable:true, bold:true, width:'80px', align:'center', order:6),
                new ColumnDefinition('grade',             'Grade',        type:'badge',   sortable:true, filterable:true, width:'70px', align:'center', order:7),
                new ColumnDefinition('evaluator_name',    'Evaluator',    type:'text',    width:'110px', order:8),
                new ColumnDefinition('recommendation',    'Rekomendasi',  type:'badge',   width:'110px', order:9),
                new ColumnDefinition('actions',           '',             type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('grade', 'Grade', type:'select', quick:true, options:[
                ['value'=>'A','label'=>'A — Excellent'],
                ['value'=>'B','label'=>'B — Good'],
                ['value'=>'C','label'=>'C — Average'],
                ['value'=>'D','label'=>'D — Below'],
                ['value'=>'E','label'=>'E — Poor'],
            ], order:1))
            ->addFilter(new FilterDefinition('review_period', 'Period', type:'select', order:2))
            ->addFilter(new FilterDefinition('department', 'Department', type:'select', order:3))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // TRAINING — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function trainingTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'hrm.training.index',
            title: 'Training Records',
            modelClass: \App\Models\Tenant\Training::class,
            defaultSort: ['start_date' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['employees'],
        ))
            ->addColumns([
                new ColumnDefinition('program_name',     'Program',        type:'text',  sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('category',         'Kategori',       type:'badge', sortable:true, filterable:true, width:'90px', order:2),
                new ColumnDefinition('instructor',       'Instruktur',     type:'text',  width:'120px', order:3),
                new ColumnDefinition('start_date',       'Mulai',          type:'date',  sortable:true, width:'100px', order:4),
                new ColumnDefinition('end_date',         'Selesai',        type:'date',  width:'100px', order:5),
                new ColumnDefinition('duration_days',    'Durasi (Hari)',  type:'number', width:'80px', align:'center', order:6),
                new ColumnDefinition('participant_count','Peserta',        type:'number', width:'70px', align:'center', order:7),
                new ColumnDefinition('completion_rate',  'Completion %',   type:'number', width:'90px', align:'center', order:8),
                new ColumnDefinition('status',           'Status',         type:'badge', sortable:true, filterable:true, width:'90px', order:9),
                new ColumnDefinition('actions',          '',               type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'planned','label'=>'Planned'],
                ['value'=>'in_progress','label'=>'In Progress'],
                ['value'=>'completed','label'=>'Completed'],
                ['value'=>'cancelled','label'=>'Cancelled'],
            ], order:1))
            ->addFilter(new FilterDefinition('category', 'Kategori', type:'select', order:2))
            ->addFilter(new FilterDefinition('start_date', 'Tanggal', type:'date_range', order:3))
            ->addBulkAction(new BulkAction('start', 'Start', variant:'primary'))
            ->addBulkAction(new BulkAction('complete', 'Mark Complete', variant:'success'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // RECRUITMENT — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function recruitmentTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'hrm.recruitment.index',
            title: 'Recruitment Pipeline',
            modelClass: \App\Models\Tenant\Recruitment::class,
            defaultSort: ['application_date' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['employees'],
        ))
            ->addColumns([
                new ColumnDefinition('job_position',      'Posisi',        type:'text',  sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('applicant_name',    'Pelamar',       type:'text',  sortable:true, searchable:true, order:2),
                new ColumnDefinition('application_date',  'Tgl Lamar',     type:'date',  sortable:true, width:'100px', order:3),
                new ColumnDefinition('source',            'Source',        type:'text',  width:'90px', order:4),
                new ColumnDefinition('stage',             'Tahap',         type:'badge', sortable:true, filterable:true, width:'100px', order:5),
                new ColumnDefinition('interview_score',   'Interview',     type:'number', width:'80px', align:'center', order:6),
                new ColumnDefinition('test_score',        'Test',          type:'number', width:'70px', align:'center', order:7),
                new ColumnDefinition('status',            'Status',        type:'badge', sortable:true, filterable:true, width:'100px', order:8),
                new ColumnDefinition('expected_salary',   'Expected Salary',type:'currency', width:'130px', align:'right', order:9),
                new ColumnDefinition('actions',           '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('stage', 'Tahap', type:'select', quick:true, options:[
                ['value'=>'screening','label'=>'Screening'],
                ['value'=>'interview','label'=>'Interview'],
                ['value'=>'test','label'=>'Test'],
                ['value'=>'offering','label'=>'Offering'],
                ['value'=>'hired','label'=>'Hired'],
                ['value'=>'rejected','label'=>'Rejected'],
            ], order:1))
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[['value'=>'open','label'=>'Open'],['value'=>'closed','label'=>'Closed']], order:2))
            ->addFilter(new FilterDefinition('job_position', 'Posisi', type:'select', order:3))
            ->addBulkAction(new BulkAction('move_stage', 'Move Stage', variant:'default'))
            ->addBulkAction(new BulkAction('send_offer', 'Send Offer', variant:'primary'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // EMPLOYEE ASSET — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function assetTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'hrm.asset.index',
            title: 'Employee Assets',
            modelClass: \App\Models\Tenant\EmployeeAsset::class,
            defaultSort: ['assigned_date' => 'desc'],
            perPage: 25,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['employees'],
        ))
            ->addColumns([
                new ColumnDefinition('asset_name',       'Asset',         type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('asset_type',       'Tipe',          type:'badge',   sortable:true, filterable:true, width:'90px', order:2),
                new ColumnDefinition('asset_code',       'Kode',          type:'text',    width:'110px', order:3),
                new ColumnDefinition('employee_name',    'Karyawan',      type:'text',    sortable:true, searchable:true, order:4),
                new ColumnDefinition('assigned_date',    'Tgl Assign',    type:'date',    sortable:true, width:'100px', order:5),
                new ColumnDefinition('return_date',      'Tgl Kembali',   type:'date',    sortable:true, width:'100px', order:6),
                new ColumnDefinition('condition',        'Kondisi',       type:'badge',   width:'90px', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, filterable:true, width:'90px', order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[
                ['value'=>'assigned','label'=>'Assigned'],
                ['value'=>'returned','label'=>'Returned'],
                ['value'=>'lost','label'=>'Lost'],
                ['value'=>'damaged','label'=>'Damaged'],
            ], order:1))
            ->addFilter(new FilterDefinition('asset_type', 'Tipe', type:'select', quick:true, options:[
                ['value'=>'laptop','label'=>'Laptop'],
                ['value'=>'phone','label'=>'Phone'],
                ['value'=>'tools','label'=>'Tools'],
                ['value'=>'uniform','label'=>'Uniform'],
                ['value'=>'access_card','label'=>'Access Card'],
                ['value'=>'sim_card','label'=>'SIM Card'],
                ['value'=>'vehicle','label'=>'Vehicle'],
            ], order:2))
            ->addFilter(new FilterDefinition('employee_id', 'Karyawan', type:'select', order:3))
            ->addBulkAction(new BulkAction('return', 'Return', variant:'default'))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // ORGANIZATION — Data Table
    // ═══════════════════════════════════════════════════════════

    public static function organizationTable(): DataDefinition
    {
        return (new DataDefinition(
            id: 'hrm.organization.index',
            title: 'Organization Structure',
            modelClass: \App\Models\Tenant\OrganizationUnit::class,
            defaultSort: ['level' => 'asc', 'name' => 'asc'],
            perPage: 100,
            selectable: true,
            exportable: true,
            searchable: true,
            features: ['employees'],
        ))
            ->addColumns([
                new ColumnDefinition('name',             'Unit',          type:'text',    sortable:true, searchable:true, bold:true, order:1),
                new ColumnDefinition('type',             'Tipe',          type:'badge',   sortable:true, filterable:true, width:'90px', order:2),
                new ColumnDefinition('level',            'Level',         type:'number',  sortable:true, width:'60px', align:'center', order:3),
                new ColumnDefinition('parent_name',      'Parent',        type:'text',    sortable:true, width:'140px', order:4),
                new ColumnDefinition('head_name',        'Kepala',        type:'text',    width:'120px', order:5),
                new ColumnDefinition('employee_count',   'Karyawan',      type:'number',  width:'80px', align:'center', order:6),
                new ColumnDefinition('branch',           'Cabang',        type:'text',    sortable:true, width:'100px', order:7),
                new ColumnDefinition('status',           'Status',        type:'badge',   sortable:true, width:'80px', align:'center', order:8),
                new ColumnDefinition('actions',          '',              type:'actions', align:'center', width:'80px', order:99),
            ])
            ->addFilter(new FilterDefinition('type', 'Tipe', type:'select', quick:true, options:[
                ['value'=>'company','label'=>'Company'],
                ['value'=>'branch','label'=>'Branch'],
                ['value'=>'division','label'=>'Division'],
                ['value'=>'department','label'=>'Department'],
                ['value'=>'position','label'=>'Position'],
            ], order:1))
            ->addFilter(new FilterDefinition('status', 'Status', type:'select', quick:true, options:[['value'=>'active','label'=>'Active'],['value'=>'inactive','label'=>'Inactive']], order:2))
            ->addBulkAction(new BulkAction('export', 'Export CSV', variant:'default'));
    }

    // ═══════════════════════════════════════════════════════════
    // AUTOMATION RULES — 12 Rules
    // ═══════════════════════════════════════════════════════════

    /** @return AutomationDefinition[] */
    public static function automations(): array
    {
        return [
            // 1. Employee Created → Welcome + task
            (new AutomationDefinition('hrm.employee_created', 'Employee Created',
                trigger: TriggerType::RECORD_CREATED, module: 'hrm'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Onboarding: {{subject.full_name}}',
                    'assignee_role' => 'hrd',
                ], delaySeconds: 3600)),

            // 2. Birthday Reminder
            (new AutomationDefinition('hrm.birthday', 'Birthday Reminder',
                trigger: TriggerType::DATE_REACHED, module: 'hrm'))
                ->addStep(new AutomationStep(ActionType::SEND_WHATSAPP, [
                    'message' => '🎂 Selamat ulang tahun {{subject.full_name}}! Semoga sukses selalu.',
                ]))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '🎂 Birthday Today',
                    'body' => '{{subject.full_name}} berulang tahun hari ini!',
                    'roles' => ['hrd', 'manager'],
                ])),

            // 3. Contract Expiring → Alert
            (new AutomationDefinition('hrm.contract_expiring', 'Contract Expiring',
                trigger: TriggerType::DATE_REACHED, module: 'hrm'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Review kontrak {{subject.full_name}} (expires {{subject.contract_end}})',
                    'assignee_role' => 'hrd',
                ], delaySeconds: 1800))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '📋 Contract Expiring',
                    'body' => 'Kontrak {{subject.full_name}} akan berakhir.',
                    'roles' => ['hrd', 'manager'],
                ])),

            // 4. Leave Waiting Approval
            (new AutomationDefinition('hrm.leave_pending', 'Leave Waiting Approval',
                trigger: TriggerType::RECORD_CREATED, module: 'hrm'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '🏖️ Leave Request',
                    'body' => '{{subject.employee_name}} mengajukan {{subject.leave_type}}.',
                    'roles' => ['hrd', 'manager'],
                ])),

            // 5. Attendance Missing → Alert
            (new AutomationDefinition('hrm.attendance_missing', 'Attendance Missing',
                trigger: TriggerType::DATE_REACHED, module: 'hrm'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '⚠️ No Attendance',
                    'body' => '{{subject.employee_name}} belum clock-in hari ini.',
                    'roles' => ['hrd', 'manager'],
                ])),

            // 6. Late Arrival → Log
            (new AutomationDefinition('hrm.late_arrival', 'Late Arrival',
                trigger: TriggerType::RECORD_UPDATED, module: 'hrm'))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                    'message' => '⏰ {{subject.employee_name}} terlambat {{subject.late_minutes}} menit.',
                ])),

            // 7. Payroll Ready → Notify
            (new AutomationDefinition('hrm.payroll_ready', 'Payroll Ready',
                trigger: TriggerType::RECORD_UPDATED, module: 'hrm'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '💰 Payroll Ready',
                    'body' => 'Payroll {{subject.payroll_period}} siap direview.',
                    'roles' => ['hrd', 'finance', 'owner'],
                ])),

            // 8. Payroll Approved → Auto Journal
            (new AutomationDefinition('hrm.payroll_approved', 'Payroll Approved',
                trigger: TriggerType::RECORD_UPDATED, module: 'hrm'))
                ->addStep(new AutomationStep(ActionType::CREATE_JOURNAL, [
                    'template' => 'payroll_expense',
                ])),

            // 9. Training Reminder
            (new AutomationDefinition('hrm.training_reminder', 'Training Reminder',
                trigger: TriggerType::DATE_REACHED, module: 'hrm'))
                ->addStep(new AutomationStep(ActionType::PUSH_NOTIFICATION, [
                    'title' => '🎓 Training Reminder',
                    'body' => 'Training {{subject.program_name}} besok!',
                ])),

            // 10. Performance Review Due
            (new AutomationDefinition('hrm.performance_due', 'Performance Review Due',
                trigger: TriggerType::DATE_REACHED, module: 'hrm'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Performance Review: {{subject.employee_name}}',
                    'assignee_role' => 'manager',
                ])),

            // 11. Probation Ending
            (new AutomationDefinition('hrm.probation_ending', 'Probation Ending',
                trigger: TriggerType::DATE_REACHED, module: 'hrm'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Evaluasi Probation: {{subject.full_name}}',
                    'assignee_role' => 'hrd',
                ])),

            // 12. Employee Resigned → Offboarding
            (new AutomationDefinition('hrm.employee_resigned', 'Employee Resigned',
                trigger: TriggerType::RECORD_UPDATED, module: 'hrm'))
                ->addStep(new AutomationStep(ActionType::CREATE_TASK, [
                    'title' => 'Offboarding: {{subject.full_name}}',
                    'assignee_role' => 'hrd',
                ]))
                ->addStep(new AutomationStep(ActionType::CREATE_ACTIVITY, [
                    'message' => '👋 {{subject.full_name}} resigned. Initiate offboarding.',
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
            // 1. Attendance Report
            (new ReportDefinition('hrm.attendance', 'Attendance Report',
                type:'summary', chartType:'table', features:['employees']))
                ->addMetric(new MetricDefinition('present', 'Present', 'count', 'present', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('late', 'Late', 'count', 'late', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('absent', 'Absent', 'count', 'absent', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('attendance_rate', 'Rate %', 'expression', 'present / total * 100', format:'number', color:'primary'))
                ->addDimension(new DimensionDefinition('employee_name', 'Employee', 'employee_name', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            // 2. Late Report
            (new ReportDefinition('hrm.late_report', 'Late Arrival Report',
                type:'summary', chartType:'bar', features:['employees']))
                ->addMetric(new MetricDefinition('late_count', 'Late Count', 'count', 'late', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('avg_minutes', 'Avg Minutes', 'avg', 'late_minutes', format:'number'))
                ->addDimension(new DimensionDefinition('employee_name', 'Employee', 'employee_name', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            // 3. Leave Report
            (new ReportDefinition('hrm.leave_report', 'Leave Report',
                type:'summary', chartType:'table', features:['employees']))
                ->addMetric(new MetricDefinition('annual_used', 'Annual Used', 'sum', 'annual_days', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('sick_used', 'Sick Used', 'sum', 'sick_days', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('total_used', 'Total Used', 'sum', 'total_days', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('remaining', 'Remaining', 'last', 'remaining_balance', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('employee_name', 'Employee', 'employee_name', type:'string'))
                ->addFilter(new ReportFilter('year', 'Year', 'select')),

            // 4. Payroll Report
            (new ReportDefinition('hrm.payroll_report', 'Payroll Report',
                type:'summary', chartType:'table', features:['employees'], permissions:['manage_payroll']))
                ->addMetric(new MetricDefinition('basic', 'Basic Salary', 'sum', 'basic_salary', format:'currency'))
                ->addMetric(new MetricDefinition('allowances', 'Allowances', 'sum', 'allowances', format:'currency'))
                ->addMetric(new MetricDefinition('overtime', 'Overtime', 'sum', 'overtime_pay', format:'currency'))
                ->addMetric(new MetricDefinition('deductions', 'Deductions', 'sum', 'deductions', format:'currency', color:'danger'))
                ->addMetric(new MetricDefinition('tax', 'Tax', 'sum', 'tax_amount', format:'currency', color:'warning'))
                ->addMetric(new MetricDefinition('net', 'Net Pay', 'sum', 'net_salary', format:'currency', color:'success'))
                ->addDimension(new DimensionDefinition('payroll_period', 'Period', 'payroll_period', type:'string'))
                ->addFilter(new ReportFilter('period', 'Period', 'select')),

            // 5. Overtime Report
            (new ReportDefinition('hrm.overtime', 'Overtime Report',
                type:'summary', chartType:'bar', features:['employees']))
                ->addMetric(new MetricDefinition('overtime_hours', 'Hours', 'sum', 'overtime_hours', format:'number', color:'warning'))
                ->addMetric(new MetricDefinition('overtime_pay', 'Pay', 'sum', 'overtime_pay', format:'currency'))
                ->addDimension(new DimensionDefinition('employee_name', 'Employee', 'employee_name', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            // 6. Employee Performance
            (new ReportDefinition('hrm.employee_performance', 'Employee Performance',
                type:'summary', chartType:'bar', features:['employees']))
                ->addMetric(new MetricDefinition('kpi_score', 'KPI', 'avg', 'kpi_score', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('attendance', 'Attendance', 'avg', 'attendance_score', format:'number'))
                ->addMetric(new MetricDefinition('overall', 'Overall', 'avg', 'overall_score', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('employee_name', 'Employee', 'employee_name', type:'string'))
                ->addFilter(new ReportFilter('review_period', 'Period', 'select')),
                
            // 7. Department Performance
            (new ReportDefinition('hrm.dept_performance', 'Department Performance',
                type:'summary', chartType:'bar', features:['employees']))
                ->addMetric(new MetricDefinition('avg_kpi', 'Avg KPI', 'avg', 'kpi_score', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('employee_count', 'Employees', 'count', 'id', format:'number'))
                ->addDimension(new DimensionDefinition('department', 'Department', 'department', type:'string'))
                ->addFilter(new ReportFilter('review_period', 'Period', 'select')),

            // 8. Training Report
            (new ReportDefinition('hrm.training_report', 'Training Report',
                type:'summary', chartType:'table', features:['employees']))
                ->addMetric(new MetricDefinition('total_programs', 'Programs', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('total_participants', 'Participants', 'sum', 'participant_count', format:'number'))
                ->addMetric(new MetricDefinition('avg_completion', 'Avg Completion %', 'avg', 'completion_rate', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('category', 'Category', 'category', type:'string'))
                ->addFilter(new ReportFilter('year', 'Year', 'select')),

            // 9. Recruitment Report
            (new ReportDefinition('hrm.recruitment_report', 'Recruitment Report',
                type:'summary', chartType:'funnel', features:['employees']))
                ->addMetric(new MetricDefinition('applicants', 'Applicants', 'count', 'id', format:'number', color:'info'))
                ->addMetric(new MetricDefinition('interviewed', 'Interviewed', 'count', 'interviewed', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('hired', 'Hired', 'count', 'hired', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('job_position', 'Position', 'job_position', type:'string'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            // 10. Turnover Report
            (new ReportDefinition('hrm.turnover', 'Turnover Report',
                type:'trend', chartType:'line', features:['employees']))
                ->addMetric(new MetricDefinition('joined', 'Joined', 'count', 'joined', format:'number', color:'success'))
                ->addMetric(new MetricDefinition('resigned', 'Resigned', 'count', 'resigned', format:'number', color:'danger'))
                ->addMetric(new MetricDefinition('turnover_rate', 'Turnover %', 'expression', 'resigned / avg_employees * 100', format:'number', color:'warning'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'month', type:'date'))
                ->addFilter(new ReportFilter('year', 'Year', 'select')),

            // 11. Employee Growth
            (new ReportDefinition('hrm.employee_growth', 'Employee Growth',
                type:'trend', chartType:'line', features:['employees']))
                ->addMetric(new MetricDefinition('total', 'Total', 'count', 'id', format:'number', color:'primary'))
                ->addMetric(new MetricDefinition('new', 'New', 'count', 'new', format:'number', color:'success'))
                ->addDimension(new DimensionDefinition('month', 'Month', 'join_date', type:'date'))
                ->addFilter(new ReportFilter('date_range', 'Period', 'date_range')),

            // 12. Salary Analysis
            (new ReportDefinition('hrm.salary_analysis', 'Salary Analysis',
                type:'summary', chartType:'bar', features:['employees'], permissions:['manage_payroll']))
                ->addMetric(new MetricDefinition('avg_salary', 'Avg Salary', 'avg', 'basic_salary', format:'currency', color:'primary'))
                ->addMetric(new MetricDefinition('min_salary', 'Min', 'min', 'basic_salary', format:'currency'))
                ->addMetric(new MetricDefinition('max_salary', 'Max', 'max', 'basic_salary', format:'currency'))
                ->addMetric(new MetricDefinition('total_payroll', 'Total Payroll', 'sum', 'net_salary', format:'currency', color:'success'))
                ->addDimension(new DimensionDefinition('department', 'Department', 'department', type:'string'))
                ->addFilter(new ReportFilter('period', 'Period', 'select')),

            // 13. Organization Summary
            (new ReportDefinition('hrm.org_summary', 'Organization Summary',
                type:'summary', chartType:'kpi', features:['employees']))
                ->addMetric(new MetricDefinition('total_employees', 'Total Employees', 'count', 'id', format:'number', icon:'👥'))
                ->addMetric(new MetricDefinition('departments', 'Departments', 'count_distinct', 'department', format:'number', icon:'🏢'))
                ->addMetric(new MetricDefinition('branches', 'Branches', 'count_distinct', 'branch', format:'number', icon:'📍'))
                ->addMetric(new MetricDefinition('avg_tenure', 'Avg Tenure (Years)', 'avg', 'tenure_years', format:'number', icon:'📅')),
        ];
    }
}
