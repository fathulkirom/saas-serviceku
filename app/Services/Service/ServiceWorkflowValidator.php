<?php

namespace App\Services\Service;

use App\Models\Tenant\Service;
use InvalidArgumentException;
use LogicException;

/**
 * ServiceWorkflowValidator — Enforces ALL service workflow rules.
 * 
 * SPRINT 36A: Production-grade status validation.
 * Every status transition is validated with business logic checks.
 * 
 * ⚠️ ZERO tolerance for invalid transitions. Backend-enforced.
 */
class ServiceWorkflowValidator
{
    /**
     * Complete refined status matrix — ALL allowed transitions.
     * Format: from_status => [allowed_to_status, ...]
     */
    public const ALLOWED_TRANSITIONS = [
        // ── Intake Phase ──
        Service::STATUS_MENUNGGU_ALOKASI => [
            Service::STATUS_DITERIMA,
            Service::STATUS_CANCEL,
        ],

        // ── Reception & Check-in ──
        Service::STATUS_DITERIMA => [
            Service::STATUS_DIAGNOSA,
            Service::STATUS_DIKERJAKAN,
            Service::STATUS_MENUNGGU_ALOKASI, // return to pending
            Service::STATUS_CANCEL,
        ],

        // ── Diagnosis Phase ──
        Service::STATUS_DIAGNOSA => [
            Service::STATUS_KONFIRMASI_PELANGGAN, // needs approval
            Service::STATUS_DIKERJAKAN,           // simple repair, no approval needed
            Service::STATUS_INDENT,               // needs parts
            Service::STATUS_CANCEL,
        ],

        // ── Repair Phase ──
        Service::STATUS_DIKERJAKAN => [
            Service::STATUS_KONFIRMASI_PELANGGAN, // needs customer decision
            Service::STATUS_KONFIRMASI_INTERNAL,  // needs internal approval
            Service::STATUS_INDENT,               // found additional parts needed
            Service::STATUS_ONPARTNER,            // escalate to partner
            Service::STATUS_SELESAI,              // repair done → QC
            Service::STATUS_CANCEL,
        ],

        // ── Confirmation Phases ──
        Service::STATUS_KONFIRMASI_PELANGGAN => [
            Service::STATUS_DIKERJAKAN,  // approved → proceed
            Service::STATUS_CANCEL,      // customer declines
        ],
        Service::STATUS_KONFIRMASI_INTERNAL => [
            Service::STATUS_DIKERJAKAN,  // approved → proceed
            Service::STATUS_CANCEL,
        ],

        // ── Parts Waiting ──
        Service::STATUS_INDENT => [
            Service::STATUS_DIKERJAKAN,  // parts arrived
            Service::STATUS_CANCEL,
        ],

        // ── Partner Handling ──
        Service::STATUS_ONPARTNER => [
            Service::STATUS_DIKERJAKAN,  // returned from partner
            Service::STATUS_SELESAI,     // partner completed
            Service::STATUS_CANCEL,
        ],

        // ── QC Phase (NEW — Sprint 36A) ──
        // After repair done (selesai), QC must pass before ready for pickup
        Service::STATUS_SELESAI => [
            Service::STATUS_SIAP_DIAMBIL, // QC passed
            Service::STATUS_DIKERJAKAN,   // QC failed → back to technician
            Service::STATUS_CLOSE,        // admin force close (with payment validation)
        ],

        // ── Ready for Pickup ──
        Service::STATUS_SIAP_DIAMBIL => [
            Service::STATUS_CLOSE, // paid + handed over
        ],

        // ── Terminal States ──
        Service::STATUS_CLOSE  => [], // fully closed
        Service::STATUS_CANCEL => [], // cancelled
        Service::STATUS_VOID   => [], // voided
    ];

    /**
     * Statuses that REQUIRE payment before transitioning to 'close'.
     */
    public const REQUIRE_PAYMENT_BEFORE_CLOSE = [
        Service::STATUS_SELESAI,
        Service::STATUS_SIAP_DIAMBIL,
    ];

    /**
     * Statuses that REQUIRE QC check before 'siap_diambil'.
     */
    public const REQUIRE_QC_BEFORE_READY = [
        Service::STATUS_SELESAI,
    ];

    /**
     * Statuses that REQUIRE diagnosis before repair.
     */
    public const REQUIRE_DIAGNOSIS_BEFORE_REPAIR = [
        Service::STATUS_DIKERJAKAN,
    ];

    /**
     * Statuses that REQUIRE checklist completion before diagnosis.
     */
    public const REQUIRE_CHECKLIST_BEFORE_DIAGNOSIS = true;

    /**
     * Statuses that REQUIRE photo (intake) before diagnosis.
     */
    public const REQUIRE_INTAKE_PHOTO_BEFORE_DIAGNOSIS = true;

    // ═══════════════════════════════════════════════════════════
    // VALIDATION METHODS
    // ═══════════════════════════════════════════════════════════

    /**
     * Validate a status transition. Throws on invalid transition.
     *
     * @throws LogicException if transition is not allowed
     * @throws InvalidArgumentException if business rules are violated
     */
    public static function validate(Service $service, string $newStatus, array $context = []): void
    {
        $currentStatus = $service->status;

        // 1. Check if transition is in the allowed matrix
        if (!self::isTransitionAllowed($currentStatus, $newStatus)) {
            throw new LogicException(
                "Transisi status dari '{$currentStatus}' ke '{$newStatus}' tidak diizinkan."
            );
        }

        // 2. Business rule: Close requires payment
        if ($newStatus === Service::STATUS_CLOSE && !self::hasPayment($service, $context)) {
            throw new InvalidArgumentException(
                'Servis tidak dapat ditutup sebelum pembayaran selesai.'
            );
        }

        // 3. Business rule: Ready for pickup requires QC
        if ($newStatus === Service::STATUS_SIAP_DIAMBIL && !self::hasQcPassed($service, $context)) {
            throw new InvalidArgumentException(
                'Servis tidak dapat disiapkan untuk diambil sebelum QC selesai.'
            );
        }

        // 4. Business rule: Repair requires diagnosis
        if ($newStatus === Service::STATUS_DIKERJAKAN && !self::hasDiagnosis($service, $context)) {
            throw new InvalidArgumentException(
                'Servis tidak dapat dikerjakan sebelum diagnosa dilakukan.'
            );
        }

        // 5. Business rule: Diagnosis requires intake checklist
        if ($newStatus === Service::STATUS_DIAGNOSA && !self::hasChecklist($service, $context)) {
            throw new InvalidArgumentException(
                'Checklist penerimaan wajib diisi sebelum diagnosa.'
            );
        }

        // 6. Business rule: Diagnosis requires intake photo
        if ($newStatus === Service::STATUS_DIAGNOSA && !self::hasIntakePhoto($service, $context)) {
            throw new InvalidArgumentException(
                'Foto unit saat masuk wajib diambil sebelum diagnosa.'
            );
        }
    }

    /**
     * Check if a specific transition is allowed in the matrix.
     */
    public static function isTransitionAllowed(string $from, string $to): bool
    {
        $allowed = self::ALLOWED_TRANSITIONS[$from] ?? [];
        return in_array($to, $allowed, true);
    }

    /**
     * Get all allowed next statuses for a given current status.
     */
    public static function getAllowedNextStatuses(string $currentStatus): array
    {
        return self::ALLOWED_TRANSITIONS[$currentStatus] ?? [];
    }

    // ═══════════════════════════════════════════════════════════
    // BUSINESS RULE CHECKS
    // ═══════════════════════════════════════════════════════════

    private static function hasPayment(Service $service, array $context): bool
    {
        // Check if service already has payment recorded
        if ($service->payment_status === 'paid' || $service->payment_status === 'lunas') {
            return true;
        }
        // Check context (for optimistic validation before save)
        if (!empty($context['payment_received']) || !empty($context['payment_id'])) {
            return true;
        }
        return false;
    }

    private static function hasQcPassed(Service $service, array $context): bool
    {
        // Check if QC check exists and passed
        if ($service->relationLoaded('qcCheck') && $service->qcCheck && $service->qcCheck->passed) {
            return true;
        }
        // Check context
        if (!empty($context['qc_passed']) || !empty($context['qc_check_id'])) {
            return true;
        }
        return false;
    }

    private static function hasDiagnosis(Service $service, array $context): bool
    {
        if ($service->relationLoaded('diagnosis') && $service->diagnosis) {
            return true;
        }
        if (!empty($context['diagnosis_id']) || !empty($context['skip_diagnosis_reason'])) {
            return true;
        }
        return false;
    }

    private static function hasChecklist(Service $service, array $context): bool
    {
        if ($service->relationLoaded('checklists') && $service->checklists->isNotEmpty()) {
            return true;
        }
        if (!empty($context['checklist_completed'])) {
            return true;
        }
        return false;
    }

    private static function hasIntakePhoto(Service $service, array $context): bool
    {
        if ($service->relationLoaded('photos') && $service->photos->where('category', 'intake')->isNotEmpty()) {
            return true;
        }
        if (!empty($context['intake_photo_ids'])) {
            return true;
        }
        return false;
    }

    // ═══════════════════════════════════════════════════════════
    // CHECKLIST CATEGORIES & TEMPLATES (Sprint 36A)
    // ═══════════════════════════════════════════════════════════

    /**
     * Standard intake checklist categories for HP/Laptop service.
     */
    public const CHECKLIST_CATEGORIES = [
        'body' => [
            'label' => 'Body / Casing',
            'icon' => '📱',
            'items' => [
                'body_scratch'   => 'Goresan pada body',
                'body_dent'      => 'Penyok / dent pada body',
                'body_crack'     => 'Retak pada casing',
                'body_missing'   => 'Bagian hilang',
                'body_color_fade'=> 'Warna pudar',
            ],
            'mandatory' => true,
        ],
        'lcd' => [
            'label' => 'LCD / Layar',
            'icon' => '🖥️',
            'items' => [
                'lcd_crack'      => 'Retak pada LCD',
                'lcd_dead_pixel' => 'Dead pixel',
                'lcd_line'       => 'Garis pada layar',
                'lcd_bleed'      => 'Backlight bleed',
                'lcd_touch_issue'=> 'Touchscreen tidak responsif',
                'lcd_flicker'    => 'Layar berkedip',
            ],
            'mandatory' => true,
        ],
        'touchscreen' => [
            'label' => 'Touchscreen',
            'icon' => '👆',
            'items' => [
                'touch_dead_zone' => 'Dead zone pada touchscreen',
                'touch_ghost'     => 'Ghost touch',
                'touch_delay'     => 'Touch delay / lag',
                'touch_crack'     => 'Retak pada touchscreen',
            ],
            'mandatory' => false,
        ],
        'camera' => [
            'label' => 'Kamera',
            'icon' => '📷',
            'items' => [
                'cam_front_blur'   => 'Kamera depan blur',
                'cam_rear_blur'    => 'Kamera belakang blur',
                'cam_front_dead'   => 'Kamera depan mati',
                'cam_rear_dead'    => 'Kamera belakang mati',
                'cam_flash_dead'   => 'Flash mati',
                'cam_lens_scratch' => 'Goresan pada lensa',
            ],
            'mandatory' => false,
        ],
        'speaker' => [
            'label' => 'Speaker / Audio',
            'icon' => '🔊',
            'items' => [
                'spk_distorted'   => 'Suara pecah',
                'spk_dead'        => 'Speaker mati',
                'spk_low_volume'  => 'Volume rendah',
                'mic_dead'        => 'Mikrofon mati',
                'mic_noisy'       => 'Mikrofon berisik',
                'earpiece_dead'   => 'Earpiece mati',
            ],
            'mandatory' => false,
        ],
        'charging' => [
            'label' => 'Charging / Baterai',
            'icon' => '🔋',
            'items' => [
                'charge_port_loose' => 'Port charging longgar',
                'charge_port_dead'  => 'Port charging mati',
                'battery_swollen'   => 'Baterai kembung',
                'battery_drain'     => 'Baterai cepat habis',
                'battery_dead'      => 'Baterai mati total',
                'wireless_dead'     => 'Wireless charging mati',
            ],
            'mandatory' => true,
        ],
        'faceid_fingerprint' => [
            'label' => 'Face ID / Fingerprint',
            'icon' => '🔐',
            'items' => [
                'faceid_dead'      => 'Face ID tidak berfungsi',
                'fingerprint_dead' => 'Fingerprint tidak berfungsi',
                'fingerprint_loose'=> 'Sensor fingerprint longgar',
            ],
            'mandatory' => false,
        ],
        'keyboard_trackpad' => [
            'label' => 'Keyboard / Trackpad',
            'icon' => '⌨️',
            'items' => [
                'key_missing'     => 'Keycap hilang',
                'key_stuck'       => 'Tombol macet',
                'key_dead'        => 'Tombol mati',
                'backlight_dead'  => 'Backlight keyboard mati',
                'trackpad_dead'   => 'Trackpad mati',
                'trackpad_jumpy'  => 'Trackpad loncat-loncat',
            ],
            'mandatory' => false,
        ],
        'wifi_bluetooth' => [
            'label' => 'WiFi / Bluetooth',
            'icon' => '📶',
            'items' => [
                'wifi_dead'       => 'WiFi mati',
                'wifi_weak'       => 'WiFi lemah',
                'bt_dead'         => 'Bluetooth mati',
                'bt_pair_fail'    => 'Bluetooth gagal pairing',
            ],
            'mandatory' => false,
        ],
        'mainboard' => [
            'label' => 'Mainboard / Komponen',
            'icon' => '🔧',
            'items' => [
                'mb_corrosion'    => 'Korosi pada mainboard',
                'mb_short'        => 'Korsleting',
                'mb_water_damage' => 'Water damage',
                'mb_prior_repair' => 'Bekas perbaikan sebelumnya',
                'mb_missing_part' => 'Komponen hilang',
            ],
            'mandatory' => false,
        ],
    ];

    /**
     * QC checklist — must pass ALL before ready for pickup.
     */
    public const QC_CHECKLIST = [
        'qc_all_functions'   => 'Semua fungsi utama berjalan normal',
        'qc_charging'        => 'Charging berfungsi normal',
        'qc_camera'          => 'Kamera depan & belakang OK',
        'qc_mic_speaker'     => 'Mic & Speaker OK',
        'qc_wifi'            => 'WiFi terhubung normal',
        'qc_bluetooth'       => 'Bluetooth pairing OK',
        'qc_lcd'             => 'LCD tidak ada dead pixel / garis',
        'qc_touch'           => 'Touchscreen responsif seluruh area',
        'qc_fingerprint'     => 'Fingerprint / Face ID berfungsi',
        'qc_keyboard'        => 'Keyboard semua tombol berfungsi',
        'qc_trackpad'        => 'Trackpad responsif',
        'qc_stress_test'     => 'Stress test 30 menit lulus',
        'qc_burn_test'       => 'Burn test 1 jam lulus',
        'qc_battery_test'    => 'Baterai bertahan minimal 2 jam',
        'qc_physical'        => 'Tidak ada kerusakan fisik baru',
        'qc_cleaning'        => 'Unit bersih dari debu/sisa perbaikan',
    ];

    /**
     * Photo categories for service lifecycle.
     */
    public const PHOTO_CATEGORIES = [
        'intake'     => ['label' => 'Saat Masuk',       'icon' => '📥', 'required' => true],
        'disassembly'=> ['label' => 'Saat Dibongkar',    'icon' => '🔧', 'required' => false],
        'repair'     => ['label' => 'Saat Perbaikan',    'icon' => '🔩', 'required' => false],
        'completed'  => ['label' => 'Sesudah Selesai',   'icon' => '✅', 'required' => true],
        'qc'         => ['label' => 'Saat QC',            'icon' => '🔍', 'required' => true],
        'handover'   => ['label' => 'Saat Serah Terima',  'icon' => '🤝', 'required' => false],
    ];
}
