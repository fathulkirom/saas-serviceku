<?php

namespace App\Services\Service;

/**
 * ServiceOperationsHelper — Production-Grade Service Reception & Operations Toolkit.
 * 
 * SERVICEKU v1.0 PRODUCTION IMPLEMENTATION — Phase 1-3.
 * 
 * Comprehensive guide for CS reception (< 1 minute target),
 * complete workflow validation, and technician workspace optimization.
 * 
 * ⚠️ Zero new engine. All patterns use existing Enterprise Platform.
 * ⚠️ Zero database changes required. All fields already exist on models.
 */
class ServiceOperationsHelper
{
    // ═══════════════════════════════════════════════════════════
    // PHASE 1 — SERVICE RECEPTION (< 1 MINUTE TARGET)
    // ═══════════════════════════════════════════════════════════

    /**
     * Enhanced Service Create form — all fields for rapid intake.
     * Target: CS completes in < 60 seconds.
     */
    public const SERVICE_INTAKE_FIELDS = [
        // ── Device Identification (auto-detect via IMEI) ──
        'imei_sn' => [
            'label'       => 'IMEI / Serial Number',
            'type'        => 'text',
            'required'    => true,
            'auto_detect' => true,       // Triggers: customer lookup, device lookup, history
            'scan'        => ['barcode', 'qr', 'camera_imei'],
            'placeholder' => 'Scan atau ketik IMEI/SN',
            'priority'    => 1,
        ],
        'device_type' => [
            'label'       => 'Tipe Perangkat',
            'type'        => 'select',
            'required'    => true,
            'options'     => ['smartphone' => '📱 Smartphone', 'tablet' => '📋 Tablet', 'laptop' => '💻 Laptop', 'smartwatch' => '⌚ Smartwatch', 'airpods' => '🎧 AirPods/Earbuds', 'other' => '🔧 Lainnya'],
            'priority'    => 2,
        ],
        'merek_id' => [
            'label'       => 'Merek',
            'type'        => 'autocomplete',
            'required'    => true,
            'source'      => 'master_data.brand',
            'priority'    => 3,
        ],
        'tipe_unit' => [
            'label'       => 'Model / Tipe',
            'type'        => 'text',
            'required'    => true,
            'placeholder' => 'Contoh: iPhone 14 Pro Max',
            'priority'    => 4,
        ],
        'warna' => [
            'label'       => 'Warna',
            'type'        => 'select',
            'options'     => ['hitam' => 'Hitam', 'putih' => 'Putih', 'silver' => 'Silver', 'gold' => 'Gold', 'blue' => 'Biru', 'red' => 'Merah', 'green' => 'Hijau', 'purple' => 'Ungu', 'other' => 'Lainnya'],
            'priority'    => 5,
        ],

        // ── Device Unlock Info (CRITICAL for technician) ──
        'sandi_pola' => [
            'label'       => 'Password / Pola / PIN',
            'type'        => 'text',
            'required'    => false,
            'sensitive'   => true,       // Encrypted display, technician-only visibility
            'placeholder' => 'Kosongkan jika tidak ada',
            'priority'    => 6,
        ],
        'face_id_disabled' => [
            'label' => 'Face ID / Fingerprint dinonaktifkan?',
            'type'  => 'boolean',
            'default' => false,
            'priority' => 7,
        ],

        // ── Kelengkapan (Structured) ──
        'kelengkapan_sim' => [
            'label'    => 'SIM Card',
            'type'     => 'select',
            'options'  => ['ada' => '✅ Ada', 'tidak_ada' => '❌ Tidak Ada', 'n_a' => 'N/A'],
            'default'  => 'ada',
            'priority' => 10,
        ],
        'kelengkapan_memory' => [
            'label'    => 'Memory Card',
            'type'     => 'select',
            'options'  => ['ada' => '✅ Ada', 'tidak_ada' => '❌ Tidak Ada', 'n_a' => 'N/A'],
            'default'  => 'tidak_ada',
            'priority' => 11,
        ],
        'kelengkapan_charger' => [
            'label'    => 'Charger / Kabel',
            'type'     => 'select',
            'options'  => ['ada' => '✅ Ada', 'tidak_ada' => '❌ Tidak Ada'],
            'default'  => 'tidak_ada',
            'priority' => 12,
        ],
        'kelengkapan_box' => [
            'label'    => 'Box / Dus',
            'type'     => 'select',
            'options'  => ['ada' => '✅ Ada', 'tidak_ada' => '❌ Tidak Ada'],
            'default'  => 'tidak_ada',
            'priority' => 13,
        ],
        'kelengkapan_nota' => [
            'label'    => 'Nota Pembelian',
            'type'     => 'select',
            'options'  => ['ada' => '✅ Ada', 'tidak_ada' => '❌ Tidak Ada'],
            'default'  => 'tidak_ada',
            'priority' => 14,
        ],

        // ── Customer (auto-detected via IMEI) ──
        'customer_id' => [
            'label'       => 'Customer',
            'type'        => 'autocomplete',
            'required'    => true,
            'source'      => 'customers.search',
            'auto_fill'   => true,       // Filled automatically if IMEI matches existing customer
            'priority'    => 20,
        ],

        // ── Service Details ──
        'kategori_perangkat_id' => [
            'label'    => 'Kategori Perangkat',
            'type'     => 'select',
            'source'   => 'master_data.device_category',
            'priority' => 30,
        ],
        'problem_description' => [
            'label'       => 'Keluhan Customer',
            'type'        => 'textarea',
            'required'    => true,
            'placeholder' => 'Tulis keluhan customer sedetail mungkin...',
            'rows'        => 3,
            'priority'    => 31,
        ],
        'diagnosa_awal' => [
            'label'       => 'Diagnosa Awal (CS)',
            'type'        => 'textarea',
            'required'    => false,
            'placeholder' => 'Observasi awal CS: layar retak? tidak menyala? kena air?',
            'rows'        => 2,
            'priority'    => 32,
        ],
        'condition_note' => [
            'label'       => 'Kondisi Fisik',
            'type'        => 'textarea',
            'required'    => true,
            'placeholder' => 'Kondisi fisik unit saat diterima...',
            'rows'        => 2,
            'priority'    => 33,
        ],

        // ── Priority & SLA ──
        'prioritas' => [
            'label'   => 'Prioritas',
            'type'    => 'select',
            'options' => ['normal' => '🟢 Normal (3-5 hari)', 'cepat' => '🟡 Cepat (1-2 hari)', 'express' => '🔴 Express (< 24 jam)'],
            'default' => 'normal',
            'priority' => 40,
        ],
        'estimasi_selesai' => [
            'label'    => 'Estimasi Selesai',
            'type'     => 'date',
            'required' => false,
            'priority' => 41,
        ],

        // ── Service Charge (optional at intake) ──
        'service_charge' => [
            'label'         => 'Biaya Servis (Estimasi)',
            'type'          => 'currency',
            'required'      => false,
            'permission'    => 'manage_finance',
            'priority'      => 50,
        ],
        'dp_minimum' => [
            'label'      => 'DP Minimum',
            'type'       => 'currency',
            'required'   => false,
            'permission' => 'manage_finance',
            'priority'   => 51,
        ],

        // ── Photos (direct camera) ──
        'photos' => [
            'label'       => 'Foto Unit',
            'type'        => 'file',
            'multiple'    => true,
            'max_files'   => 10,
            'accept'      => 'image/*',
            'capture'     => 'environment', // Direct camera on mobile
            'category'    => 'intake',
            'required'    => true,
            'min_files'   => 2,
            'priority'    => 60,
        ],

        // ── Internal Notes (CS only, not visible to customer) ──
        'catatan_internal' => [
            'label'       => 'Catatan Internal',
            'type'        => 'textarea',
            'required'    => false,
            'placeholder' => 'Catatan untuk teknisi, tidak terlihat customer...',
            'rows'        => 2,
            'priority'    => 70,
        ],
    ];

    /**
     * Auto-detect rules: when IMEI is scanned/entered, what happens?
     */
    public const AUTO_DETECT_RULES = [
        'imei_match_device' => [
            'trigger'  => 'imei_sn_changed',
            'action'   => 'lookup_device',
            'result'   => 'Pre-fill: device_type, merek_id, tipe_unit, warna, customer_id',
            'fallback' => 'If no match → manual entry',
        ],
        'imei_match_customer' => [
            'trigger'  => 'imei_sn_changed',
            'action'   => 'lookup_customer_by_device',
            'result'   => 'Pre-fill: customer_id, show service history, show warranty status',
            'fallback' => 'If no match → new customer form',
        ],
        'customer_history' => [
            'trigger'  => 'customer_selected',
            'action'   => 'fetch_service_history',
            'result'   => 'Show: last 5 services, active warranties, blacklist status, total spending',
        ],
        'blacklist_check' => [
            'trigger'  => 'customer_selected',
            'action'   => 'check_blacklist',
            'result'   => 'If blacklisted: show warning banner with reason',
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // PHASE 2 — SERVICE WORKFLOW (CONSOLIDATED)
    // ═══════════════════════════════════════════════════════════

    /**
     * Production workflow — simplified, validated, enforced.
     * All transitions validated by ServiceWorkflowValidator (Sprint 36A).
     */
    public const PRODUCTION_WORKFLOW = [
        ['status' => 'menunggu_alokasi', 'label' => 'Masuk',              'actor' => 'CS',        'action' => 'CS creates service',                          'auto' => false],
        ['status' => 'diterima',         'label' => 'Diterima / Assigned', 'actor' => 'CS/Manager', 'action' => 'Assign technician. Checklist + photos done', 'auto' => false],
        ['status' => 'diagnosa',         'label' => 'Diagnosa',            'actor' => 'Teknisi',   'action' => 'Technician diagnoses issue',                  'auto' => false],
        ['status' => 'menunggu_konfirmasi_pelanggan', 'label' => 'Waiting Approval', 'actor' => 'Customer', 'action' => 'Customer approves/rejects estimate',   'auto' => true],
        ['status' => 'dikerjakan',       'label' => 'Dikerjakan',          'actor' => 'Teknisi',   'action' => 'Technician repairs device',                   'auto' => false],
        ['status' => 'selesai',          'label' => 'QC',                  'actor' => 'QC',        'action' => 'QC check (22 items)',                         'auto' => false],
        ['status' => 'siap_diambil',     'label' => 'Ready Pickup',        'actor' => 'System',    'action' => 'QC passed → notify customer',                'auto' => true],
        ['status' => 'diambil',          'label' => 'Diambil / Paid',      'actor' => 'Kasir',     'action' => 'Payment received + handover',                 'auto' => false],
        ['status' => 'close',            'label' => 'Closed',              'actor' => 'System',    'action' => 'Warranty activated',                          'auto' => true],
    ];

    /**
     * Status checklist — what MUST be done at each stage.
     */
    public const STATUS_REQUIREMENTS = [
        'diterima' => [
            'checklist_completed' => true,
            'intake_photos_min'   => 2,
            'technician_assigned' => true,
            'customer_notified'   => true,
        ],
        'diagnosa' => [
            'diagnosis_saved'     => true,
            'estimated_cost'      => true,
            'estimated_time'      => true,
        ],
        'menunggu_konfirmasi_pelanggan' => [
            'quotation_sent'      => true,
            'customer_notified'   => true,
        ],
        'dikerjakan' => [
            'diagnosis_completed' => true,
            'approval_received'   => true,
        ],
        'selesai' => [
            'repair_completed'    => true,
            'all_photos_uploaded' => true,
            'parts_recorded'      => true,
        ],
        'siap_diambil' => [
            'qc_passed'           => true,
            'customer_notified'   => true,
        ],
        'diambil' => [
            'payment_received'    => true,
            'delivery_signed'     => true,
            'signature_captured'  => true,
            'handover_photo'      => true,
        ],
        'close' => [
            'warranty_activated'  => true,
            'invoice_generated'   => true,
            'feedback_requested'  => true,
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // PHASE 3 — TECHNICIAN WORKSPACE (OPTIMIZED)
    // ═══════════════════════════════════════════════════════════

    /**
     * Technician workspace optimized for speed.
     * Single-page workflow — no tab switching needed for core actions.
     */
    public const TECHNICIAN_QUICK_ACTIONS = [
        ['id' => 'accept_job',       'label' => 'Terima',        'icon' => '✅', 'color' => 'success', 'transition' => 'diterima→diagnosa'],
        ['id' => 'start_diagnosis',  'label' => 'Diagnosa',      'icon' => '🔍', 'color' => 'primary', 'transition' => 'diagnosa'],
        ['id' => 'request_approval', 'label' => 'Minta Approval', 'icon' => '📞', 'color' => 'warning', 'transition' => 'diagnosa→konfirmasi'],
        ['id' => 'indent_part',      'label' => 'Indent Part',   'icon' => '📦', 'color' => 'info',    'transition' => 'diagnosa→indent'],
        ['id' => 'start_repair',     'label' => 'Mulai Servis',  'icon' => '🔧', 'color' => 'primary', 'transition' => 'konfirmasi→dikerjakan'],
        ['id' => 'pause_repair',     'label' => 'Pause',          'icon' => '⏸️', 'color' => 'warning', 'action' => 'pause_timer'],
        ['id' => 'resume_repair',    'label' => 'Lanjutkan',      'icon' => '▶️', 'color' => 'success', 'action' => 'resume_timer'],
        ['id' => 'finish_repair',    'label' => 'Selesai',        'icon' => '🏁', 'color' => 'success', 'transition' => 'dikerjakan→selesai'],
        ['id' => 'add_photo',        'label' => 'Foto',           'icon' => '📸', 'color' => 'default', 'action' => 'upload_photo'],
        ['id' => 'add_part',         'label' => '+ Sparepart',    'icon' => '🔩', 'color' => 'default', 'action' => 'add_part'],
        ['id' => 'add_note',         'label' => 'Catatan',        'icon' => '📝', 'color' => 'default', 'action' => 'add_internal_note'],
    ];

    /**
     * Technician dashboard metrics — at-a-glance.
     */
    public const TECHNICIAN_DASHBOARD_METRICS = [
        ['key' => 'my_queue',          'label' => 'My Queue',           'icon' => '📋', 'source' => 'services.assigned_to_me.pending'],
        ['key' => 'in_progress',       'label' => 'Sedang Dikerjakan',  'icon' => '🔧', 'source' => 'services.assigned_to_me.in_progress'],
        ['key' => 'waiting_parts',     'label' => 'Menunggu Part',      'icon' => '📦', 'source' => 'services.assigned_to_me.indent'],
        ['key' => 'ready_qc',          'label' => 'Siap QC',            'icon' => '🔍', 'source' => 'services.assigned_to_me.selesai'],
        ['key' => 'completed_today',   'label' => 'Selesai Hari Ini',   'icon' => '✅', 'source' => 'services.assigned_to_me.completed_today'],
        ['key' => 'sla_remaining',     'label' => 'SLA Tersisa',        'icon' => '⏱️', 'source' => 'services.assigned_to_me.sla_remaining'],
        ['key' => 'avg_repair_time',   'label' => 'Rata² Waktu Servis', 'icon' => '📊', 'source' => 'technician.stats.avg_minutes'],
        ['key' => 'productivity',      'label' => 'Produktivitas',      'icon' => '⭐', 'source' => 'technician.stats.productivity_score'],
    ];

    // ═══════════════════════════════════════════════════════════
    // PHASE 4 — CUSTOMER EXPERIENCE FEATURES
    // ═══════════════════════════════════════════════════════════

    public const CUSTOMER_TOUCHPOINTS = [
        ['channel' => 'QR Code',         'feature' => 'Scan QR di nota → tracking real-time',                    'auth_required' => false],
        ['channel' => 'WhatsApp',        'feature' => 'Notifikasi otomatis: status, approval, ready pickup',     'auth_required' => false],
        ['channel' => 'Customer Portal', 'feature' => 'Full access: tracking, invoice, warranty, history',       'auth_required' => true],
        ['channel' => 'Email',           'feature' => 'Invoice PDF, warranty card, feedback survey',             'auth_required' => false],
    ];

    // ═══════════════════════════════════════════════════════════
    // PHASE 5 — PAYMENT WORKFLOW
    // ═══════════════════════════════════════════════════════════

    public const PAYMENT_METHODS = [
        ['key' => 'tunai',       'label' => 'Tunai',         'icon' => '💵'],
        ['key' => 'transfer',    'label' => 'Transfer Bank', 'icon' => '🏦'],
        ['key' => 'qris',        'label' => 'QRIS',          'icon' => '📱'],
        ['key' => 'ewallet',     'label' => 'E-Wallet',      'icon' => '💳'],
        ['key' => 'debit',       'label' => 'Kartu Debit',   'icon' => '💳'],
    ];

    public const PAYMENT_TYPES = [
        ['key' => 'dp',          'label' => 'DP / Uang Muka',     'icon' => '💰'],
        ['key' => 'pelunasan',   'label' => 'Pelunasan',          'icon' => '✅'],
        ['key' => 'full',        'label' => 'Pembayaran Penuh',   'icon' => '💵'],
        ['key' => 'piutang',     'label' => 'Piutang',            'icon' => '📋'],
    ];

    // ═══════════════════════════════════════════════════════════
    // PHASE 6 — OWNER DASHBOARD METRICS
    // ═══════════════════════════════════════════════════════════

    public const OWNER_DASHBOARD_METRICS = [
        ['key' => 'services_today',      'label' => 'Servis Masuk Hari Ini',    'icon' => '📥', 'color' => 'primary'],
        ['key' => 'units_in_progress',   'label' => 'Dalam Pengerjaan',         'icon' => '🔧', 'color' => 'info'],
        ['key' => 'waiting_approval',    'label' => 'Menunggu Approval',        'icon' => '📞', 'color' => 'warning'],
        ['key' => 'waiting_parts',       'label' => 'Menunggu Sparepart',       'icon' => '📦', 'color' => 'warning'],
        ['key' => 'ready_pickup',        'label' => 'Siap Diambil',             'icon' => '📦', 'color' => 'success'],
        ['key' => 'paid_today',          'label' => 'Sudah Dibayar',            'icon' => '💵', 'color' => 'success'],
        ['key' => 'revenue_today',       'label' => 'Pendapatan Hari Ini',      'icon' => '💰', 'color' => 'success', 'format' => 'currency'],
        ['key' => 'revenue_month',       'label' => 'Pendapatan Bulan Ini',     'icon' => '📊', 'color' => 'success', 'format' => 'currency'],
        ['key' => 'top_technician',      'label' => 'Teknisi Terproduktif',     'icon' => '🏆', 'color' => 'primary'],
        ['key' => 'top_damage',          'label' => 'Kerusakan Terbanyak',      'icon' => '🔍', 'color' => 'danger'],
        ['key' => 'top_part',            'label' => 'Sparepart Terlaris',       'icon' => '🔩', 'color' => 'info'],
        ['key' => 'satisfaction',        'label' => 'Kepuasan Pelanggan',       'icon' => '⭐', 'color' => 'warning'],
    ];

    // ═══════════════════════════════════════════════════════════
    // PHASE 7 — MASTER DATA CATALOG (UI-Managed)
    // ═══════════════════════════════════════════════════════════

    /**
     * Master data that MUST be manageable via UI (not just seeder).
     * Each entry maps to a MasterData category or dedicated model.
     */
    public const MASTER_DATA_CATALOG = [
        // ── Device ──
        ['category' => 'brand',              'label' => 'Merk HP / Laptop',     'model' => 'MasterData',   'priority' => 'critical', 'ui_ready' => false],
        ['category' => 'device_category',    'label' => 'Kategori Perangkat',   'model' => 'MasterData',   'priority' => 'critical', 'ui_ready' => false],
        ['category' => 'model',              'label' => 'Model / Tipe',         'model' => 'MasterData',   'priority' => 'high',     'ui_ready' => false],
        ['category' => 'color',              'label' => 'Warna',                'model' => 'MasterData',   'priority' => 'medium',   'ui_ready' => false],
        ['category' => 'storage',            'label' => 'Storage',              'model' => 'MasterData',   'priority' => 'low',      'ui_ready' => false],
        ['category' => 'processor',          'label' => 'Processor',            'model' => 'MasterData',   'priority' => 'low',      'ui_ready' => false],

        // ── Service ──
        ['category' => 'damage_type',        'label' => 'Jenis Kerusakan',      'model' => 'MasterData',   'priority' => 'critical', 'ui_ready' => false],
        ['category' => 'service_type',       'label' => 'Jenis Servis',         'model' => 'MasterData',   'priority' => 'critical', 'ui_ready' => false],
        ['category' => 'arrival_method',     'label' => 'Jalur Kedatangan',    'model' => 'MasterData',   'priority' => 'high',     'ui_ready' => true],
        ['category' => 'unit',               'label' => 'Satuan',               'model' => 'MasterData',   'priority' => 'low',      'ui_ready' => true],

        // ── Parts ──
        ['category' => 'sparepart',          'label' => 'Sparepart',            'model' => 'Product',      'priority' => 'critical', 'ui_ready' => true],
        ['category' => 'supplier',           'label' => 'Supplier',             'model' => 'Supplier',     'priority' => 'critical', 'ui_ready' => true],

        // ── HR ──
        ['category' => 'technician',         'label' => 'Teknisi',              'model' => 'User',         'priority' => 'critical', 'ui_ready' => true],

        // ── Finance ──
        ['category' => 'tax',                'label' => 'Pajak (PPN)',          'model' => 'MasterData',   'priority' => 'critical', 'ui_ready' => false],
        ['category' => 'bank',               'label' => 'Bank / Rekening',      'model' => 'MasterData',   'priority' => 'high',     'ui_ready' => false],
        ['category' => 'cash_register',      'label' => 'Kas',                  'model' => 'CashRegister', 'priority' => 'critical', 'ui_ready' => true],

        // ── Output ──
        ['category' => 'printer',            'label' => 'Printer',              'model' => 'MasterData',   'priority' => 'medium',   'ui_ready' => false],
        ['category' => 'whatsapp_template',  'label' => 'Template WhatsApp',    'model' => 'CustomerMessageTemplate', 'priority' => 'critical', 'ui_ready' => true],
        ['category' => 'nota_template',      'label' => 'Template Nota',        'model' => 'MasterData',   'priority' => 'high',     'ui_ready' => false],
        ['category' => 'warranty_template',  'label' => 'Template Garansi',    'model' => 'MasterData',   'priority' => 'high',     'ui_ready' => false],
        ['category' => 'label_template',     'label' => 'Template Label',       'model' => 'MasterData',   'priority' => 'medium',   'ui_ready' => false],

        // ── Warranty ──
        ['category' => 'warranty_policy',    'label' => 'Kebijakan Garansi',    'model' => 'MasterData',   'priority' => 'critical', 'ui_ready' => false],
        ['category' => 'payment_method',     'label' => 'Metode Pembayaran',    'model' => 'MasterData',   'priority' => 'critical', 'ui_ready' => true],
    ];

    // ═══════════════════════════════════════════════════════════
    // PHASE 8 — UI/UX AUDIT CHECKLIST
    // ═══════════════════════════════════════════════════════════

    public const UX_AUDIT_CHECKLIST = [
        ['area' => 'Navigation',       'check' => 'Maksimal 3 klik ke aksi utama',                     'target' => 'critical'],
        ['area' => 'Navigation',       'check' => 'Keyboard shortcut untuk aksi umum',                  'target' => 'high'],
        ['area' => 'Navigation',       'check' => 'Breadcrumb di setiap halaman',                       'target' => 'medium'],
        ['area' => 'Loading',          'check' => 'Skeleton loader (bukan spinner kosong)',             'target' => 'critical'],
        ['area' => 'Loading',          'check' => 'Optimistic UI untuk status transition',               'target' => 'high'],
        ['area' => 'Empty State',      'check' => 'Ilustrasi + CTA informatif (bukan halaman kosong)',   'target' => 'critical'],
        ['area' => 'Error State',      'check' => 'Error message mudah dipahami + tombol retry',         'target' => 'critical'],
        ['area' => 'Success State',    'check' => 'Toast sukses setelah aksi penting',                   'target' => 'high'],
        ['area' => 'Consistency',      'check' => 'Warna status konsisten di seluruh aplikasi',          'target' => 'critical'],
        ['area' => 'Consistency',      'check' => 'Icon konsisten (pakai set yang sama)',                'target' => 'high'],
        ['area' => 'Mobile',           'check' => 'Semua halaman responsif di HP',                       'target' => 'critical'],
        ['area' => 'Tablet',           'check' => 'Layout optimal di tablet (CS counter)',               'target' => 'high'],
        ['area' => 'Accessibility',    'check' => 'ARIA labels, focus indicators, warna kontras cukup',  'target' => 'medium'],
        ['area' => 'Dark Mode',        'check' => 'Semua halaman support dark mode',                      'target' => 'medium'],
        ['area' => 'Confirmation',     'check' => 'Konfirmasi dialog untuk aksi destruktif',              'target' => 'critical'],
        ['area' => 'Tooltip',          'check' => 'Tooltip pada icon dan tombol tanpa label',             'target' => 'medium'],
    ];

    // ═══════════════════════════════════════════════════════════
    // PHASE 9 — PERFORMANCE TARGETS
    // ═══════════════════════════════════════════════════════════

    public const PRODUCTION_PERFORMANCE_TARGETS = [
        ['page' => 'Dashboard',           'metric' => 'First Contentful Paint', 'target' => '< 1 detik',  'current' => '✅'],
        ['page' => 'Service Workspace',   'metric' => 'Time to Interactive',   'target' => '< 500 ms',   'current' => '✅'],
        ['page' => 'Service Create Form', 'metric' => 'Form Render',           'target' => '< 300 ms',   'current' => '⚠️ Perlu auto-detect optimization'],
        ['page' => 'Search (Global)',     'metric' => 'Response Time',         'target' => '< 300 ms',   'current' => '✅'],
        ['page' => 'Data Table (List)',   'metric' => 'Server Response',       'target' => '< 300 ms',   'current' => '✅'],
        ['page' => 'Photo Upload (5MB)',  'metric' => 'Upload Duration',       'target' => '< 3 detik',  'current' => '✅'],
        ['page' => 'Report Generation',   'metric' => 'Server Response',       'target' => '< 3 detik',  'current' => '⚠️ Cache needed'],
        ['page' => 'Notification',        'metric' => 'E2E Delivery',          'target' => '< 5 detik',  'current' => '✅'],
    ];

    // ═══════════════════════════════════════════════════════════
    // PHASE 10 — QA & UAT SIMULATION
    // ═══════════════════════════════════════════════════════════

    public const UAT_SIMULATION = [
        ['step' => 1,  'actor' => 'CS',       'action' => 'Scan IMEI → auto-detect customer → create service',   'expected' => 'Service created. Tracking code generated.'],
        ['step' => 2,  'actor' => 'CS',       'action' => 'Complete intake checklist + take 2 photos',           'expected' => 'Checklist saved. Photos uploaded.'],
        ['step' => 3,  'actor' => 'CS',       'action' => 'Assign technician',                                   'expected' => 'Status: diterima. Technician notified.'],
        ['step' => 4,  'actor' => 'Teknisi',  'action' => 'Accept job → Diagnose → Save findings',               'expected' => 'Diagnosis saved. Estimation auto-generated.'],
        ['step' => 5,  'actor' => 'Teknisi',  'action' => 'Request customer approval',                            'expected' => 'Status: menunggu_konfirmasi. WA sent to customer.'],
        ['step' => 6,  'actor' => 'Customer', 'action' => 'Approve via WA link',                                  'expected' => 'Status: dikerjakan. Technician notified.'],
        ['step' => 7,  'actor' => 'Teknisi',  'action' => 'Start repair → Add sparepart → Finish repair',        'expected' => 'Timer tracked. Parts deducted. Status: selesai.'],
        ['step' => 8,  'actor' => 'QC',       'action' => 'Run 22-point QC → All PASS',                           'expected' => 'QC passed. Status: siap_diambil. Customer notified.'],
        ['step' => 9,  'actor' => 'Kasir',    'action' => 'Process payment → Print invoice',                      'expected' => 'Payment recorded. Invoice printed.'],
        ['step' => 10, 'actor' => 'Kasir',    'action' => 'Handover: signature + photo → Close',                  'expected' => 'Status: close. Warranty activated.'],
    ];
}
