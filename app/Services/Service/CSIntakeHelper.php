<?php

namespace App\Services\Service;

/**
 * CSIntakeHelper — Complete Customer Service Intake Flow.
 * 
 * SERVICEKU v1.0 PRODUCTION: CS Reception Flow.
 * Customer search → device detection → service creation → checklist → photos → receipt → timeline → automation → notification.
 * 
 * ⚠️ Zero new engine. All uses existing Enterprise Platform.
 * ⚠️ Reference implementation for CS intake controllers.
 */
class CSIntakeHelper
{
    // ═══════════════════════════════════════════════════════════
    // STEP 1 — CUSTOMER SEARCH & DETECTION
    // ═══════════════════════════════════════════════════════════

    /**
     * Customer search methods supported at intake.
     */
    public const CUSTOMER_SEARCH_METHODS = [
        'name' => [
            'label'       => 'Nama',
            'field'       => 'name',
            'type'        => 'text',
            'search_type' => 'LIKE',
        ],
        'phone' => [
            'label'       => 'Nomor HP',
            'field'       => 'phone',
            'type'        => 'text',
            'search_type' => 'LIKE',
        ],
        'imei' => [
            'label'       => 'IMEI / Serial Number',
            'field'       => 'imei_sn',
            'type'        => 'text',
            'search_type' => 'EXACT',
            'auto_detect' => true, // Triggers device + customer lookup
        ],
        'service_number' => [
            'label'       => 'Nomor Service',
            'field'       => 'tracking_code',
            'type'        => 'text',
            'search_type' => 'EXACT',
        ],
        'invoice' => [
            'label'       => 'Nomor Invoice',
            'field'       => 'invoice_number',
            'type'        => 'text',
            'search_type' => 'EXACT',
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // STEP 2 — DEVICE DETECTION (Auto via IMEI)
    // ═══════════════════════════════════════════════════════════

    /**
     * What to display when IMEI matches an existing device.
     */
    public const DEVICE_HISTORY_SECTIONS = [
        'service_history' => [
            'label'  => 'Riwayat Service',
            'icon'   => '🔧',
            'source' => 'services.where(imei_sn, $imei)',
        ],
        'warranty_status' => [
            'label'  => 'Status Garansi',
            'icon'   => '🛡️',
            'source' => 'Device.warranty_until vs now()',
        ],
        'damage_history' => [
            'label'  => 'Histori Kerusakan',
            'icon'   => '🔍',
            'source' => 'ServiceDiagnosis for matching services',
        ],
        'part_history' => [
            'label'  => 'Histori Sparepart',
            'icon'   => '🔩',
            'source' => 'ServiceSparepart for matching services',
        ],
        'technician_history' => [
            'label'  => 'Histori Teknisi',
            'icon'   => '👤',
            'source' => 'Service.technician for matching services',
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // STEP 3 — CREATE SERVICE (Complete Field Set)
    // ═══════════════════════════════════════════════════════════

    /**
     * All fields available on the service intake form.
     * Organized by section for the UI.
     */
    public const INTAKE_FORM_SECTIONS = [
        'customer' => [
            'label'  => 'Customer',
            'icon'   => '👤',
            'fields' => ['customer_id'],
        ],
        'device' => [
            'label'  => 'Device',
            'icon'   => '📱',
            'fields' => ['kategori_perangkat_id', 'merek_id', 'tipe_unit', 'imei_sn', 'warna', 'storage'],
        ],
        'unlock' => [
            'label'  => 'Akses Device',
            'icon'   => '🔐',
            'fields' => ['sandi_pola', 'face_id_disabled'],
        ],
        'kelengkapan' => [
            'label'  => 'Kelengkapan',
            'icon'   => '📦',
            'fields' => ['kelengkapan_sim', 'kelengkapan_memory', 'kelengkapan_charger', 'kelengkapan_box', 'kelengkapan_nota'],
        ],
        'problem' => [
            'label'  => 'Keluhan',
            'icon'   => '🔍',
            'fields' => ['problem_description', 'diagnosa_awal', 'condition_note'],
        ],
        'service' => [
            'label'  => 'Service',
            'icon'   => '🔧',
            'fields' => ['prioritas', 'estimasi_selesai', 'service_charge', 'dp_minimum', 'jalur_kedatangan_id', 'technician_id'],
        ],
        'checklist' => [
            'label'  => 'Checklist',
            'icon'   => '✅',
            'fields' => ['checklist_template_id', 'checked_items'],
        ],
        'photos' => [
            'label'  => 'Foto',
            'icon'   => '📸',
            'fields' => ['photos'],
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // STEP 4 — CHECKLIST CATEGORIES (Intake)
    // ═══════════════════════════════════════════════════════════

    /**
     * Standard intake checklist categories for HP/Laptop.
     */
    public const INTAKE_CHECKLIST_CATEGORIES = [
        'body'        => ['label' => 'Body / Casing',       'icon' => '📱', 'mandatory' => true],
        'lcd'         => ['label' => 'LCD / Layar',          'icon' => '🖥️', 'mandatory' => true],
        'touchscreen' => ['label' => 'Touchscreen',          'icon' => '👆', 'mandatory' => false],
        'battery'     => ['label' => 'Battery',              'icon' => '🔋', 'mandatory' => true],
        'camera'      => ['label' => 'Camera',               'icon' => '📷', 'mandatory' => false],
        'speaker'     => ['label' => 'Speaker',              'icon' => '🔊', 'mandatory' => false],
        'mic'         => ['label' => 'Microphone',           'icon' => '🎤', 'mandatory' => false],
        'faceid'      => ['label' => 'Face ID',              'icon' => '👁️', 'mandatory' => false],
        'fingerprint' => ['label' => 'Fingerprint',          'icon' => '👆', 'mandatory' => false],
        'charging'    => ['label' => 'Charging Port',        'icon' => '🔌', 'mandatory' => true],
        'mainboard'   => ['label' => 'Mainboard / Komponen', 'icon' => '🔧', 'mandatory' => false],
    ];

    // ═══════════════════════════════════════════════════════════
    // STEP 5 — PHOTO CATEGORIES (Intake)
    // ═══════════════════════════════════════════════════════════

    public const INTAKE_PHOTO_CATEGORIES = [
        'front'       => ['label' => 'Depan',        'icon' => '📱', 'required' => true],
        'back'        => ['label' => 'Belakang',     'icon' => '📱', 'required' => true],
        'left'        => ['label' => 'Kiri',          'icon' => '⬅️', 'required' => true],
        'right'       => ['label' => 'Kanan',         'icon' => '➡️', 'required' => true],
        'top'         => ['label' => 'Atas',          'icon' => '⬆️', 'required' => false],
        'bottom'      => ['label' => 'Bawah',         'icon' => '⬇️', 'required' => false],
        'accessories' => ['label' => 'Aksesoris',     'icon' => '📦', 'required' => false],
        'damage'      => ['label' => 'Kerusakan',     'icon' => '💥', 'required' => true],
    ];

    // ═══════════════════════════════════════════════════════════
    // STEP 6 — RECEIPT DATA
    // ═══════════════════════════════════════════════════════════

    /**
     * Receipt preview data after service creation.
     */
    public const RECEIPT_SECTIONS = [
        'header'  => ['Nama Toko', 'Alamat', 'Telepon', 'Tanggal'],
        'service' => ['Nomor Service (tracking_code)', 'Status', 'Prioritas'],
        'customer'=> ['Nama Customer', 'Telepon'],
        'device'  => ['Tipe', 'Merek', 'IMEI/SN', 'Kelengkapan'],
        'problem' => ['Keluhan', 'Kondisi'],
        'footer'  => ['CS Name', 'Branch', 'Estimasi Selesai'],
    ];

    // ═══════════════════════════════════════════════════════════
    // STEP 7 — TIMELINE EVENTS (CS Intake)
    // ═══════════════════════════════════════════════════════════

    /**
     * Timeline events generated during CS intake.
     */
    public const INTAKE_TIMELINE_EVENTS = [
        ['type' => 'customer_created',  'label' => 'Customer Created',     'icon' => '👤'],
        ['type' => 'device_registered', 'label' => 'Device Registered',    'icon' => '📱'],
        ['type' => 'checklist_done',    'label' => 'Checklist Completed',  'icon' => '✅'],
        ['type' => 'photo_uploaded',    'label' => 'Photo Uploaded',       'icon' => '📸'],
        ['type' => 'receipt_printed',   'label' => 'Receipt Printed',      'icon' => '🧾'],
        ['type' => 'service_created',   'label' => 'Service Created',      'icon' => '📥'],
    ];

    // ═══════════════════════════════════════════════════════════
    // STEP 8 — AUTOMATION RULES FOR INTAKE
    // ═══════════════════════════════════════════════════════════

    /**
     * Automation rules triggered during CS intake.
     */
    public const INTAKE_AUTOMATIONS = [
        'service.created' => [
            'trigger' => 'RECORD_CREATED',
            'steps'   => ['ADD_TIMELINE', 'CREATE_ACTIVITY', 'SEND_INTERNAL'],
            'status'  => '✅ Defined in ServiceAutomations',
        ],
        'crm.customer_welcome' => [
            'trigger' => 'CUSTOMER_CREATED',
            'steps'   => ['CREATE_TASK (1hr delay)', 'CREATE_ACTIVITY'],
            'status'  => '✅ Defined in ServiceAutomations',
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // STEP 9 — NOTIFICATION EVENTS
    // ═══════════════════════════════════════════════════════════

    /**
     * Notifications generated during CS intake.
     */
    public const INTAKE_NOTIFICATIONS = [
        'SERVICE_CREATED' => [
            'event'      => 'ServiceCreated',
            'channel'    => 'Internal',
            'recipients' => ['cs', 'manager', 'admin'],
            'message'    => '📥 Servis baru: #{{tracking_code}} — {{customer.name}} — {{problem_description}}',
            'status'     => '✅ Event dispatched in ServiceWorkflowController::store()',
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // STEP 10 — VALIDATION RULES
    // ═══════════════════════════════════════════════════════════

    /**
     * Intake validation rules (backend-enforced).
     */
    public const INTAKE_VALIDATION = [
        'customer_id'         => 'required|exists:customers,id',
        'tipe_unit'           => 'required|string|max:100',
        'problem_description'  => 'required|string|min:5',
        'imei_sn'             => 'nullable|string|max:100',
        'checklist_template_id' => 'nullable|exists:checklist_templates,id',
        'checked_items'       => 'nullable|array',
        'photos'              => 'nullable|array|max:10',
        'photos.*'            => 'image|mimes:jpg,jpeg,png,webp|max:10240',
        'kategori_perangkat_id' => 'nullable|exists:master_data,id',
        'merek_id'            => 'nullable|exists:master_data,id',
        'jalur_kedatangan_id' => 'nullable|exists:master_data,id',
        'sandi_pola'          => 'nullable|string|max:50',
        'kelengkapan'         => 'nullable|array',
        'prioritas'           => 'nullable|in:normal,cepat,express',
        'estimasi_selesai'    => 'nullable|date|after:today',
    ];

    /**
     * Frontend validation messages (Indonesian).
     */
    public const INTAKE_VALIDATION_MESSAGES = [
        'customer_id.required'        => 'Customer wajib dipilih.',
        'tipe_unit.required'          => 'Tipe unit wajib diisi.',
        'problem_description.required' => 'Keluhan customer wajib diisi.',
        'problem_description.min'     => 'Keluhan minimal 5 karakter.',
        'photos.*.mimes'              => 'Foto harus dalam format JPG, PNG, atau WebP.',
        'photos.*.max'                => 'Ukuran foto maksimal 10MB.',
        'estimasi_selesai.after'      => 'Estimasi selesai harus setelah hari ini.',
    ];
}
