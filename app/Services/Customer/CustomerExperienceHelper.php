<?php

namespace App\Services\Customer;

/**
 * CustomerExperienceHelper — Complete customer journey & digital experience toolkit.
 * 
 * SPRINT 36C: Production-grade customer experience for HP & Laptop service.
 * Service tracking, digital approval, digital warranty, device history,
 * booking, after-sales, feedback, AI insights.
 * 
 * ⚠️ ALL customer-facing workflows MUST route through this helper.
 * ⚠️ Zero new database — all data from existing ServiceKU models.
 */
class CustomerExperienceHelper
{
    // ═══════════════════════════════════════════════════════════
    // COMPLETE CUSTOMER JOURNEY — 12 Stages
    // ═══════════════════════════════════════════════════════════

    /**
     * Full customer journey map for HP/Laptop service.
     * Each stage maps to a service status, notification, and customer action.
     */
    public const CUSTOMER_JOURNEY = [
        [
            'stage'     => 'booking',
            'label'     => 'Booking',
            'icon'      => '📅',
            'description' => 'Customer books service online or walks in',
            'status'     => null, // pre-service
            'notification' => 'booking_confirmed',
            'customer_action' => 'Book appointment or walk in',
            'visible_to_customer' => true,
        ],
        [
            'stage'     => 'intake',
            'label'     => 'Penerimaan Unit',
            'icon'      => '📥',
            'description' => 'CS receives device, completes checklist, takes photos',
            'status'     => 'diterima',
            'notification' => 'service_received',
            'customer_action' => 'Hand over device, sign checklist',
            'visible_to_customer' => true,
        ],
        [
            'stage'     => 'diagnosis',
            'label'     => 'Diagnosa',
            'icon'      => '🔍',
            'description' => 'Technician diagnoses the issue',
            'status'     => 'diagnosa',
            'notification' => 'diagnosis_in_progress',
            'customer_action' => 'Wait for diagnosis result',
            'visible_to_customer' => true,
        ],
        [
            'stage'     => 'estimation',
            'label'     => 'Estimasi Biaya',
            'icon'      => '💰',
            'description' => 'Quotation sent to customer for approval',
            'status'     => 'menunggu_konfirmasi_pelanggan',
            'notification' => 'estimation_ready',
            'customer_action' => 'Approve / Reject / Request revision',
            'visible_to_customer' => true,
        ],
        [
            'stage'     => 'repair',
            'label'     => 'Perbaikan',
            'icon'      => '🔧',
            'description' => 'Technician repairs the device',
            'status'     => 'dikerjakan',
            'notification' => 'repair_in_progress',
            'customer_action' => 'Track progress',
            'visible_to_customer' => true,
        ],
        [
            'stage'     => 'waiting_parts',
            'label'     => 'Menunggu Sparepart',
            'icon'      => '📦',
            'description' => 'Waiting for spare parts to arrive',
            'status'     => 'indent',
            'notification' => 'waiting_parts',
            'customer_action' => 'Wait (automatic update when parts arrive)',
            'visible_to_customer' => true,
        ],
        [
            'stage'     => 'qc',
            'label'     => 'Quality Control',
            'icon'      => '🔬',
            'description' => 'Device undergoing quality check',
            'status'     => 'selesai',
            'notification' => 'qc_in_progress',
            'customer_action' => 'Wait for QC completion',
            'visible_to_customer' => true,
        ],
        [
            'stage'     => 'ready_pickup',
            'label'     => 'Siap Diambil',
            'icon'      => '📦',
            'description' => 'Device ready for pickup',
            'status'     => 'siap_diambil',
            'notification' => 'ready_pickup',
            'customer_action' => 'Come to store for pickup',
            'visible_to_customer' => true,
        ],
        [
            'stage'     => 'payment',
            'label'     => 'Pembayaran',
            'icon'      => '💳',
            'description' => 'Customer pays service fee',
            'status'     => 'siap_diambil', // payment can happen at ready or pickup
            'notification' => 'payment_received',
            'customer_action' => 'Pay via cash/transfer/QRIS',
            'visible_to_customer' => true,
        ],
        [
            'stage'     => 'handover',
            'label'     => 'Serah Terima',
            'icon'      => '🤝',
            'description' => 'Device handed back to customer',
            'status'     => 'diambil',
            'notification' => 'handover_complete',
            'customer_action' => 'Receive device, sign delivery',
            'visible_to_customer' => true,
        ],
        [
            'stage'     => 'warranty',
            'label'     => 'Garansi Aktif',
            'icon'      => '🛡️',
            'description' => 'Warranty period active',
            'status'     => 'close',
            'notification' => 'warranty_active',
            'customer_action' => 'Use device normally, claim warranty if needed',
            'visible_to_customer' => true,
        ],
        [
            'stage'     => 'after_sales',
            'label'     => 'After Sales',
            'icon'      => '💚',
            'description' => 'Follow-up, feedback, maintenance reminders',
            'status'     => null, // post-service
            'notification' => 'follow_up',
            'customer_action' => 'Provide feedback, schedule maintenance',
            'visible_to_customer' => true,
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // SERVICE TRACKING — Progress Points
    // ═══════════════════════════════════════════════════════════

    /**
     * Tracking progress bar stages (customer-facing).
     * Simplified from the 14 internal statuses to 7 customer-visible stages.
     */
    public const TRACKING_PROGRESS = [
        ['key' => 'received',    'label' => 'Diterima',       'icon' => '📥', 'pct' => 10],
        ['key' => 'diagnosed',   'label' => 'Diagnosa',       'icon' => '🔍', 'pct' => 25],
        ['key' => 'approved',    'label' => 'Disetujui',      'icon' => '✅', 'pct' => 40],
        ['key' => 'in_progress', 'label' => 'Dikerjakan',     'icon' => '🔧', 'pct' => 60],
        ['key' => 'qc_done',     'label' => 'QC Selesai',     'icon' => '🔬', 'pct' => 80],
        ['key' => 'ready',       'label' => 'Siap Diambil',   'icon' => '📦', 'pct' => 95],
        ['key' => 'completed',   'label' => 'Selesai',        'icon' => '🎉', 'pct' => 100],
    ];

    /**
     * Map internal service status to tracking progress key.
     */
    public const STATUS_TO_TRACKING = [
        'menunggu_alokasi'           => 'received',
        'diterima'                   => 'received',
        'diagnosa'                   => 'diagnosed',
        'menunggu_konfirmasi_pelanggan' => 'diagnosed', // waiting approval
        'menunggu_konfirmasi_internal'  => 'diagnosed',
        'indent'                     => 'approved',     // approved, waiting parts
        'onpartner'                  => 'in_progress',
        'dikerjakan'                 => 'in_progress',
        'selesai'                    => 'qc_done',
        'siap_diambil'               => 'ready',
        'diambil'                    => 'completed',
        'close'                      => 'completed',
        'cancel'                     => null, // not tracked
        'void'                       => null,
    ];

    // ═══════════════════════════════════════════════════════════
    // TRACKING LOOKUP METHODS
    // ═══════════════════════════════════════════════════════════

    /**
     * Lookup service by tracking code, IMEI, serial number, or service number.
     * Returns null if not found or not accessible.
     */
    public static function getTrackingMethods(): array
    {
        return [
            'service_number' => [
                'label'       => 'Nomor Servis',
                'icon'        => '🔢',
                'placeholder' => 'Contoh: SVC-20260803-0001',
                'field'       => 'tracking_code',
            ],
            'imei' => [
                'label'       => 'IMEI / Serial Number',
                'icon'        => '📱',
                'placeholder' => 'Masukkan IMEI atau Serial Number',
                'field'       => 'imei_sn',
            ],
            'qr_code' => [
                'label'       => 'QR Code',
                'icon'        => '📷',
                'placeholder' => 'Scan QR code pada nota',
                'field'       => 'tracking_code', // QR encodes tracking code
            ],
        ];
    }

    // ═══════════════════════════════════════════════════════════
    // DIGITAL APPROVAL — Estimation Approval Flow
    // ═══════════════════════════════════════════════════════════

    /**
     * Customer approval actions for estimation.
     */
    public const APPROVAL_ACTIONS = [
        'approve' => [
            'label'       => 'Setujui Estimasi',
            'icon'        => '✅',
            'color'       => 'success',
            'description' => 'Saya setuju dengan estimasi biaya dan ingin melanjutkan perbaikan',
        ],
        'reject' => [
            'label'       => 'Tolak Estimasi',
            'icon'        => '❌',
            'color'       => 'danger',
            'description' => 'Saya tidak setuju dan membatalkan perbaikan',
        ],
        'revise' => [
            'label'       => 'Minta Revisi',
            'icon'        => '📝',
            'color'       => 'warning',
            'description' => 'Saya ingin revisi estimasi (misal: ganti sparepart lebih murah)',
        ],
    ];

    /**
     * Additional approval scenarios.
     */
    public const ADDITIONAL_APPROVAL_TYPES = [
        'additional_cost'  => 'Tambahan Biaya',
        'additional_parts' => 'Tambahan Sparepart',
        'extended_time'    => 'Perpanjangan Waktu',
    ];

    // ═══════════════════════════════════════════════════════════
    // DIGITAL WARRANTY — Customer-Facing Warranty Info
    // ═══════════════════════════════════════════════════════════

    public const WARRANTY_TERMS = [
        'service' => [
            'label'       => 'Garansi Jasa',
            'duration'    => 30, // days
            'covers'      => 'Pengerjaan teknisi (solder, pasang, rakit)',
            'excludes'    => 'Kerusakan fisik setelah serah terima, terkena air, dibuka pihak lain',
            'claim_process' => 'Bawa unit + nota servis ke cabang tempat servis',
        ],
        'sparepart' => [
            'label'       => 'Garansi Sparepart',
            'duration'    => 90, // days
            'covers'      => 'Sparepart yang diganti (LCD, baterai, flex, IC, dll)',
            'excludes'    => 'Kerusakan akibat pemakaian tidak wajar, jatuh, terkena air',
            'claim_process' => 'Bawa unit + nota servis + sparepart yang bermasalah',
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // DIGITAL DOCUMENTS — Customer Document Types
    // ═══════════════════════════════════════════════════════════

    public const DIGITAL_DOCUMENTS = [
        'invoice'        => ['label' => 'Invoice',         'icon' => '🧾', 'qr_verification' => true],
        'receipt'        => ['label' => 'Nota Pembayaran',  'icon' => '💰', 'qr_verification' => true],
        'warranty_card'  => ['label' => 'Kartu Garansi',    'icon' => '🛡️', 'qr_verification' => true],
        'checklist'      => ['label' => 'Checklist Masuk',  'icon' => '✅', 'qr_verification' => false],
        'estimation'     => ['label' => 'Estimasi Biaya',   'icon' => '📋', 'qr_verification' => false],
        'payment_proof'  => ['label' => 'Bukti Pembayaran', 'icon' => '💳', 'qr_verification' => false],
    ];

    // ═══════════════════════════════════════════════════════════
    // BOOKING SERVICE — Appointment Types
    // ═══════════════════════════════════════════════════════════

    public const BOOKING_SERVICE_TYPES = [
        'screen_repair'    => ['label' => 'Perbaikan LCD/Layar',    'icon' => '🖥️', 'estimated_hours' => [1, 3]],
        'battery_replace'  => ['label' => 'Ganti Baterai',          'icon' => '🔋', 'estimated_hours' => [0.5, 1]],
        'water_damage'     => ['label' => 'Kena Air',               'icon' => '💧', 'estimated_hours' => [2, 48]],
        'no_power'         => ['label' => 'Tidak Menyala',          'icon' => '🔌', 'estimated_hours' => [1, 4]],
        'charging_issue'   => ['label' => 'Masalah Charging',       'icon' => '⚡', 'estimated_hours' => [0.5, 2]],
        'camera_issue'     => ['label' => 'Kamera Bermasalah',      'icon' => '📷', 'estimated_hours' => [0.5, 2]],
        'speaker_mic'      => ['label' => 'Speaker/Mic Bermasalah', 'icon' => '🔊', 'estimated_hours' => [0.5, 1.5]],
        'software_issue'   => ['label' => 'Masalah Software',       'icon' => '💿', 'estimated_hours' => [0.5, 2]],
        'data_recovery'    => ['label' => 'Recovery Data',          'icon' => '💾', 'estimated_hours' => [2, 72]],
        'general_checkup'  => ['label' => 'General Checkup',        'icon' => '🔍', 'estimated_hours' => [0.5, 1]],
        'other'            => ['label' => 'Lainnya',                'icon' => '🔧', 'estimated_hours' => [0.5, 4]],
    ];

    public const BOOKING_TIME_SLOTS = [
        '09:00', '10:00', '11:00', '12:00',
        '13:00', '14:00', '15:00', '16:00', '17:00',
    ];

    // ═══════════════════════════════════════════════════════════
    // CUSTOMER FEEDBACK — Rating Categories
    // ═══════════════════════════════════════════════════════════

    public const FEEDBACK_CATEGORIES = [
        'cs_service'       => ['label' => 'Pelayanan CS',          'icon' => '💁', 'weight' => 25],
        'technician_skill' => ['label' => 'Keahlian Teknisi',      'icon' => '🔧', 'weight' => 30],
        'speed'            => ['label' => 'Kecepatan Perbaikan',   'icon' => '⏱️', 'weight' => 20],
        'price'            => ['label' => 'Kesesuaian Harga',      'icon' => '💰', 'weight' => 15],
        'communication'    => ['label' => 'Komunikasi',            'icon' => '💬', 'weight' => 10],
    ];

    public const FEEDBACK_RATING_SCALE = [
        1 => 'Sangat Tidak Puas',
        2 => 'Tidak Puas',
        3 => 'Cukup',
        4 => 'Puas',
        5 => 'Sangat Puas',
    ];

    // ═══════════════════════════════════════════════════════════
    // AFTER SALES — Follow-up Campaigns
    // ═══════════════════════════════════════════════════════════

    public const AFTER_SALES_ACTIONS = [
        'maintenance_reminder' => [
            'label'       => 'Reminder Maintenance',
            'icon'        => '🔧',
            'delay_days'  => 90,
            'message'     => 'Saatnya maintenance rutin untuk {device_type} Anda. Kunjungi ServiceKU cabang terdekat!',
        ],
        'cleaning_reminder' => [
            'label'       => 'Reminder Cleaning',
            'icon'        => '🧹',
            'delay_days'  => 60,
            'message'     => 'Jaga {device_type} Anda tetap bersih! Layanan cleaning tersedia di ServiceKU.',
        ],
        'battery_check' => [
            'label'       => 'Reminder Cek Baterai',
            'icon'        => '🔋',
            'delay_days'  => 180,
            'message'     => 'Baterai {device_type} Anda mungkin sudah perlu dicek. Gratis pengecekan di ServiceKU!',
        ],
        'satisfaction_survey' => [
            'label'       => 'Survey Kepuasan',
            'icon'        => '⭐',
            'delay_days'  => 7,
            'message'     => 'Bagaimana pengalaman servis Anda di ServiceKU? Beri rating dan dapatkan diskon 10%!',
        ],
        'promo' => [
            'label'       => 'Promo Berkala',
            'icon'        => '🎁',
            'delay_days'  => 30,
            'message'     => 'Hi {customer_name}! Ada promo spesial untuk Anda: diskon servis berikutnya!',
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // CUSTOMER PROFILE — Preference Options
    // ═══════════════════════════════════════════════════════════

    public const CUSTOMER_PREFERENCES = [
        'preferred_branch'       => 'Cabang Favorit',
        'preferred_technician'   => 'Teknisi Favorit',
        'preferred_contact'      => 'Kontak via (WA/Email/SMS)',
        'language'               => 'Bahasa (ID/EN)',
        'notification_enabled'   => 'Notifikasi Aktif',
        'marketing_consent'      => 'Terima Info Promo',
    ];

    // ═══════════════════════════════════════════════════════════
    // AI CUSTOMER INSIGHT — Prompt Templates
    // ═══════════════════════════════════════════════════════════

    public const AI_CUSTOMER_PROMPTS = [
        'satisfaction_predict' => 'Berdasarkan riwayat servis {customer_name} ({service_count} servis, rating rata-rata {avg_rating}), prediksi tingkat kepuasan setelah servis ini.',
        'repeat_prediction' => 'Berdasarkan {customer_name} ({repair_frequency} servis/bulan, total spending {total_spending}), berapa kemungkinan repeat service dalam 30 hari?',
        'review_analysis' => 'Analisis sentimen dari review: "{review_text}". Rating: {rating}/5. Kategori: {categories}.',
        'complaint_analysis' => 'Analisis pola komplain dari pelanggan: {complaint_summary}. Apakah ada pola sistematis?',
        'promo_recommendation' => 'Berdasarkan profil {customer_name} (segment: {segment}, device: {device_types}), rekomendasi promo yang paling relevan.',
        'loyalty_recommendation' => 'Berdasarkan {customer_name} (points: {points}, service_count: {count}, member_since: {since}), rekomendasi tier loyalty.',
        'follow_up_recommendation' => 'Berdasarkan servis terakhir {customer_name} ({last_service_type}, {days_since} hari lalu), rekomendasi follow-up yang tepat.',
    ];

    // ═══════════════════════════════════════════════════════════
    // CUSTOMER HEALTH SCORE — Composite Metric
    // ═══════════════════════════════════════════════════════════

    /**
     * Calculate customer health score (0-100).
     * Factors: recency, frequency, monetary value, satisfaction, engagement.
     */
    public static function getHealthScoreFactors(): array
    {
        return [
            'recency' => [
                'label'  => 'Recency (R)',
                'weight' => 25,
                'description' => 'Days since last service (lower is better)',
            ],
            'frequency' => [
                'label'  => 'Frequency (F)',
                'weight' => 20,
                'description' => 'Services per year (higher is better)',
            ],
            'monetary' => [
                'label'  => 'Monetary (M)',
                'weight' => 20,
                'description' => 'Total lifetime spending',
            ],
            'satisfaction' => [
                'label'  => 'Satisfaction (S)',
                'weight' => 25,
                'description' => 'Average rating from feedback',
            ],
            'engagement' => [
                'label'  => 'Engagement (E)',
                'weight' => 10,
                'description' => 'Response rate to notifications & promos',
            ],
        ];
    }
}
