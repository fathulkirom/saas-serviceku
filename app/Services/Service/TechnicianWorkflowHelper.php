<?php

namespace App\Services\Service;

/**
 * TechnicianWorkflowHelper — Complete technician operations toolkit.
 * 
 * SPRINT 36B: Production-grade technician workflow support.
 * Diagnosis templates, measurement tracking, repair timer, KPI calculations,
 * knowledge assist data, QC workflows.
 * 
 * ⚠️ ALL technician workflows MUST route through this helper.
 */
class TechnicianWorkflowHelper
{
    // ═══════════════════════════════════════════════════════════
    // DIAGNOSIS TEMPLATES — HP & Laptop Common Issues
    // ═══════════════════════════════════════════════════════════

    /**
     * Common symptom → probable cause → solution mapping for HP/Laptop.
     * Used by AI Knowledge Assist and diagnosis templates.
     */
    public const DIAGNOSIS_TEMPLATES = [
        // ── Power Issues ──
        'power_no_turn_on' => [
            'symptoms'        => ['Tidak mau menyala', 'Tidak ada respon saat tombol power ditekan'],
            'probable_causes' => ['Baterai habis total', 'IC Power rusak', 'Short pada mainboard', 'Tombol power rusak', 'Konektor baterai longgar'],
            'checks'          => ['Ukur tegangan baterai', 'Cek arus saat charger terhubung', 'Ukur jalur power button', 'Cek konsumsi arus (amp meter)'],
            'solutions'       => ['Ganti baterai', 'Reball/replace IC Power', 'Perbaiki short', 'Ganti flex power button'],
            'estimated_hours' => [1, 3],
            'difficulty'      => 'medium',
        ],
        'power_restart_loop' => [
            'symptoms'        => ['Restart terus-menerus', 'Boot loop'],
            'probable_causes' => ['Software corruption', 'IC Power tidak stabil', 'RAM issue', 'Storage corruption', 'Overheating'],
            'checks'          => ['Cek log crash', 'Tes tanpa baterai (charger only)', 'Cek suhu', 'Tes dengan known-good RAM'],
            'solutions'       => ['Flash ulang firmware', 'Ganti IC Power', 'Ganti RAM', 'Ganti storage'],
            'estimated_hours' => [0.5, 2],
            'difficulty'      => 'medium',
        ],

        // ── Display Issues ──
        'lcd_no_display' => [
            'symptoms'        => ['Layar gelap total', 'Tidak ada tampilan'],
            'probable_causes' => ['Flex LCD putus/rusak', 'LCD panel rusak', 'IC Backlight mati', 'GPU issue', 'Konektor LCD longgar'],
            'checks'          => ['Cek flex LCD dengan multimeter', 'Tes dengan LCD known-good', 'Cek backlight dengan senter', 'Cek tegangan VCC LCD'],
            'solutions'       => ['Ganti flex LCD', 'Ganti LCD panel', 'Reball IC Backlight', 'Ganti konektor LCD'],
            'estimated_hours' => [0.5, 2],
            'difficulty'      => 'medium',
        ],
        'lcd_flicker' => [
            'symptoms'        => ['Layar berkedip', 'Flickering'],
            'probable_causes' => ['Flex LCD longgar/kotor', 'IC Display tidak stabil', 'Setting refresh rate salah', 'LCD panel mulai rusak'],
            'checks'          => ['Bersihkan dan pasang ulang flex LCD', 'Cek di safe mode', 'Cek refresh rate', 'Tes dengan LCD known-good'],
            'solutions'       => ['Pasang ulang flex LCD', 'Ganti IC Display', 'Reset setting display', 'Ganti LCD panel'],
            'estimated_hours' => [0.3, 1],
            'difficulty'      => 'easy',
        ],
        'lcd_lines' => [
            'symptoms'        => ['Garis vertikal/horizontal pada LCD'],
            'probable_causes' => ['Flex LCD rusak', 'LCD panel rusak', 'Kerusakan pada COF (Chip on Film)', 'Tekanan fisik pada panel'],
            'checks'          => ['Cek fisik flex', 'Tes dengan LCD known-good', 'Visual inspection COF'],
            'solutions'       => ['Ganti LCD panel', 'Jika COF: ganti LCD assembly'],
            'estimated_hours' => [0.5, 1],
            'difficulty'      => 'medium',
        ],

        // ── Touch Issues ──
        'touch_not_working' => [
            'symptoms'        => ['Touchscreen tidak merespon', 'Touch tidak akurat'],
            'probable_causes' => ['Digitizer rusak', 'Flex touchscreen putus', 'IC Touch rusak', 'Software glitch'],
            'checks'          => ['Cek flex digitizer', 'Restart device', 'Tes di safe mode', 'Cek dengan touch tester'],
            'solutions'       => ['Ganti digitizer', 'Ganti flex touchscreen', 'Reball IC Touch', 'Kalibrasi touch'],
            'estimated_hours' => [0.5, 2],
            'difficulty'      => 'medium',
        ],

        // ── Charging Issues ──
        'charging_not_working' => [
            'symptoms'        => ['Tidak charging', 'Charging lambat', 'Charging on-off'],
            'probable_causes' => ['Port charging kotor/rusak', 'Flex charging putus', 'IC Charging rusak', 'Baterai rusak', 'Kabel/charger rusak'],
            'checks'          => ['Cek port charging dengan kaca pembesar', 'Ukur tegangan charger', 'Ukur jalur charging', 'Tes dengan charger known-good'],
            'solutions'       => ['Bersihkan port charging', 'Ganti port charging', 'Ganti flex charging', 'Reball IC Charging', 'Ganti baterai'],
            'estimated_hours' => [0.3, 1.5],
            'difficulty'      => 'easy',
        ],
        'battery_drain' => [
            'symptoms'        => ['Baterai cepat habis', 'Baterai drop tiba-tiba'],
            'probable_causes' => ['Baterai sudah aus', 'Aplikasi background boros', 'Short minor pada mainboard', 'IC Power tidak efisien'],
            'checks'          => ['Cek battery health', 'Cek konsumsi arus idle', 'Cek aplikasi background', 'Cek suhu idle'],
            'solutions'       => ['Ganti baterai', 'Optimasi software', 'Perbaiki short', 'Ganti IC Power'],
            'estimated_hours' => [0.5, 2],
            'difficulty'      => 'easy',
        ],

        // ── Audio Issues ──
        'no_sound' => [
            'symptoms'        => ['Tidak ada suara', 'Speaker mati'],
            'probable_causes' => ['Speaker rusak', 'Flex speaker putus', 'IC Audio rusak', 'Kotoran di grill speaker', 'Software mute'],
            'checks'          => ['Cek setting volume', 'Cek dengan headphone', 'Ukur resistansi speaker', 'Cek fisik speaker'],
            'solutions'       => ['Ganti speaker', 'Ganti flex speaker', 'Reball IC Audio', 'Bersihkan grill'],
            'estimated_hours' => [0.3, 1],
            'difficulty'      => 'easy',
        ],

        // ── Camera Issues ──
        'camera_not_working' => [
            'symptoms'        => ['Kamera hitam', 'Kamera error'],
            'probable_causes' => ['Flex kamera putus', 'Modul kamera rusak', 'IC Kamera rusak', 'Software crash'],
            'checks'          => ['Restart device', 'Cek flex kamera', 'Tes dengan kamera known-good', 'Cek di safe mode'],
            'solutions'       => ['Ganti modul kamera', 'Ganti flex kamera', 'Reball IC Kamera'],
            'estimated_hours' => [0.3, 1],
            'difficulty'      => 'easy',
        ],

        // ── Network Issues ──
        'wifi_not_working' => [
            'symptoms'        => ['WiFi tidak bisa connect', 'WiFi tidak terdeteksi'],
            'probable_causes' => ['IC WiFi rusak', 'Antenna WiFi lepas', 'Software issue', 'MAC address corrupted'],
            'checks'          => ['Cek antenna WiFi', 'Reset network settings', 'Cek di safe mode', 'Cek MAC address'],
            'solutions'       => ['Pasang ulang antenna', 'Reball IC WiFi', 'Reset network', 'Restore MAC'],
            'estimated_hours' => [0.5, 2],
            'difficulty'      => 'medium',
        ],
        'bluetooth_not_working' => [
            'symptoms'        => ['Bluetooth tidak connect', 'BT tidak terdeteksi'],
            'probable_causes' => ['IC BT/WiFi combo rusak', 'Antenna issue', 'Software glitch'],
            'checks'          => ['Cek antenna', 'Reset network', 'Tes pairing'],
            'solutions'       => ['Reball IC WiFi/BT', 'Reset network'],
            'estimated_hours' => [0.5, 1.5],
            'difficulty'      => 'medium',
        ],

        // ── Biometric Issues ──
        'faceid_not_working' => [
            'symptoms'        => ['Face ID tidak berfungsi', 'TrueDepth error'],
            'probable_causes' => ['Flex TrueDepth putus', 'Modul TrueDepth rusak', 'Sensor ambient light issue', 'Water damage'],
            'checks'          => ['Cek flex TrueDepth', 'Cek sensor ambient light', 'Cek water damage indicator'],
            'solutions'       => ['Ganti flex TrueDepth', 'Ganti modul TrueDepth', 'Bersihkan sensor'],
            'estimated_hours' => [0.5, 2],
            'difficulty'      => 'hard',
        ],
        'fingerprint_not_working' => [
            'symptoms'        => ['Fingerprint tidak terdeteksi', 'Touch ID error'],
            'probable_causes' => ['Sensor fingerprint rusak', 'Flex fingerprint putus', 'Kotor di sensor', 'Software issue'],
            'checks'          => ['Bersihkan sensor', 'Cek flex', 'Tes setelah restart'],
            'solutions'       => ['Ganti sensor fingerprint', 'Ganti flex', 'Bersihkan sensor'],
            'estimated_hours' => [0.3, 1],
            'difficulty'      => 'medium',
        ],

        // ── Keyboard/Trackpad Issues ──
        'keyboard_not_working' => [
            'symptoms'        => ['Keyboard sebagian/seluruhnya mati', 'Tombol macet'],
            'probable_causes' => ['Flex keyboard putus', 'Liquid damage', 'Debu di bawah keycap', 'IC Keyboard rusak'],
            'checks'          => ['Cek flex keyboard', 'Cek liquid damage indicator', 'Bersihkan keyboard'],
            'solutions'       => ['Ganti flex keyboard', 'Ganti keyboard assembly', 'Bersihkan keyboard'],
            'estimated_hours' => [0.3, 2],
            'difficulty'      => 'medium',
        ],

        // ── Mainboard Issues ──
        'water_damage' => [
            'symptoms'        => ['Terkena air/cairan', 'Korosi pada komponen'],
            'probable_causes' => ['Liquid damage', 'Short circuit', 'Komponen korosi'],
            'checks'          => ['Visual inspection dengan mikroskop', 'Cek water damage indicator', 'Ukur short ke ground', 'Ultrasonic cleaning'],
            'solutions'       => ['Ultrasonic cleaning', 'Ganti komponen korosi', 'Jumper jalur putus', 'Reball IC yang terkena'],
            'estimated_hours' => [1, 5],
            'difficulty'      => 'hard',
        ],
        'short_circuit' => [
            'symptoms'        => ['Tidak menyala', 'Konsumsi arus tinggi', 'Komponen panas'],
            'probable_causes' => ['Kapasitor short', 'IC short', 'Jalur short ke ground', 'Liquid damage'],
            'checks'          => ['Cek short ke ground dengan multimeter', 'Thermal camera untuk cari komponen panas', 'Injeksi tegangan (power supply)', 'Cek satu per satu komponen'],
            'solutions'       => ['Ganti kapasitor short', 'Ganti IC short', 'Perbaiki jalur', 'Jumper jika perlu'],
            'estimated_hours' => [1, 4],
            'difficulty'      => 'hard',
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // DEVICE CATEGORIES
    // ═══════════════════════════════════════════════════════════

    public const DEVICE_CATEGORIES = [
        'smartphone'    => ['label' => 'Smartphone',        'icon' => '📱'],
        'tablet'        => ['label' => 'Tablet',            'icon' => '📋'],
        'laptop'        => ['label' => 'Laptop/Notebook',   'icon' => '💻'],
        'smartwatch'    => ['label' => 'Smartwatch',        'icon' => '⌚'],
        'airpods'       => ['label' => 'AirPods/Earbuds',   'icon' => '🎧'],
        'other'         => ['label' => 'Lainnya',           'icon' => '🔧'],
    ];

    // ═══════════════════════════════════════════════════════════
    // DAMAGE CATEGORIES
    // ═══════════════════════════════════════════════════════════

    public const DAMAGE_CATEGORIES = [
        'physical'      => ['label' => 'Kerusakan Fisik',     'icon' => '💥'],
        'liquid'        => ['label' => 'Kena Air/Cairan',     'icon' => '💧'],
        'software'      => ['label' => 'Software/OS',         'icon' => '💿'],
        'component'     => ['label' => 'Komponen Rusak',      'icon' => '🔩'],
        'wear_tear'     => ['label' => 'Aus/Pemakaian',       'icon' => '⏳'],
        'unknown'       => ['label' => 'Belum Diketahui',     'icon' => '❓'],
    ];

    // ═══════════════════════════════════════════════════════════
    // REPAIR DIFFICULTY LEVELS
    // ═══════════════════════════════════════════════════════════

    public const REPAIR_DIFFICULTY = [
        'easy'    => ['label' => 'Mudah',       'color' => 'success', 'multiplier' => 1.0],
        'medium'  => ['label' => 'Sedang',      'color' => 'warning', 'multiplier' => 1.5],
        'hard'    => ['label' => 'Sulit',       'color' => 'danger',  'multiplier' => 2.5],
        'extreme' => ['label' => 'Sangat Sulit','color' => 'danger',  'multiplier' => 4.0],
    ];

    // ═══════════════════════════════════════════════════════════
    // MEASUREMENT POINTS — Common Test Points
    // ═══════════════════════════════════════════════════════════

    public const MEASUREMENT_TEMPLATES = [
        // ── Power Section ──
        'vbat_battery' => [
            'label'        => 'VBAT (Tegangan Baterai)',
            'test_point'   => 'Konektor baterai pin +',
            'expected_min' => 3.6,
            'expected_max' => 4.4,
            'unit'         => 'V',
            'mode'         => 'voltage',
            'category'     => 'power',
        ],
        'vbat_charger' => [
            'label'        => 'VBUS (Tegangan Charger Masuk)',
            'test_point'   => 'Konektor charging pin VCC',
            'expected_min' => 4.8,
            'expected_max' => 5.2,
            'unit'         => 'V',
            'mode'         => 'voltage',
            'category'     => 'power',
        ],
        'vcc_main' => [
            'label'        => 'VCC Main (Tegangan Utama)',
            'test_point'   => 'Output IC Power',
            'expected_min' => 3.2,
            'expected_max' => 4.2,
            'unit'         => 'V',
            'mode'         => 'voltage',
            'category'     => 'power',
        ],
        'vcc_core' => [
            'label'        => 'VCC Core (CPU Core Voltage)',
            'test_point'   => 'Kapasitor dekat CPU',
            'expected_min' => 0.8,
            'expected_max' => 1.2,
            'unit'         => 'V',
            'mode'         => 'voltage',
            'category'     => 'power',
        ],
        'current_consumption' => [
            'label'        => 'Konsumsi Arus (Idle)',
            'test_point'   => 'Power supply / Amp meter',
            'expected_min' => 0.01,
            'expected_max' => 0.10,
            'unit'         => 'A',
            'mode'         => 'current',
            'category'     => 'power',
        ],

        // ── Display Section ──
        'vcc_lcd' => [
            'label'        => 'VCC LCD',
            'test_point'   => 'Konektor LCD pin VCC',
            'expected_min' => 1.7,
            'expected_max' => 3.3,
            'unit'         => 'V',
            'mode'         => 'voltage',
            'category'     => 'display',
        ],
        'backlight_voltage' => [
            'label'        => 'Tegangan Backlight',
            'test_point'   => 'Konektor LCD pin BL',
            'expected_min' => 15.0,
            'expected_max' => 25.0,
            'unit'         => 'V',
            'mode'         => 'voltage',
            'category'     => 'display',
        ],

        // ── Resistance Checks ──
        'short_to_ground' => [
            'label'        => 'Short ke Ground (Diode Mode)',
            'test_point'   => 'Jalur VCC Main ke Ground',
            'expected_min' => 0.3,
            'expected_max' => 0.8,
            'unit'         => 'V',
            'mode'         => 'diode',
            'category'     => 'mainboard',
        ],
        'coil_resistance' => [
            'label'        => 'Resistansi Coil',
            'test_point'   => 'Coil induktor',
            'expected_min' => 0.1,
            'expected_max' => 5.0,
            'unit'         => 'Ω',
            'mode'         => 'resistance',
            'category'     => 'mainboard',
        ],

        // ── Temperature ──
        'ic_temp' => [
            'label'        => 'Suhu IC Power',
            'test_point'   => 'Permukaan IC Power',
            'expected_min' => 30,
            'expected_max' => 60,
            'unit'         => '°C',
            'mode'         => 'temperature',
            'category'     => 'thermal',
        ],
        'cpu_temp' => [
            'label'        => 'Suhu CPU',
            'test_point'   => 'Permukaan CPU/SoC',
            'expected_min' => 35,
            'expected_max' => 75,
            'unit'         => '°C',
            'mode'         => 'temperature',
            'category'     => 'thermal',
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // TECHNICIAN KPI CALCULATIONS
    // ═══════════════════════════════════════════════════════════

    /**
     * KPI metric definitions for technician performance.
     */
    public const KPI_METRICS = [
        'jobs_completed' => [
            'label'       => 'Jobs Completed',
            'icon'        => '✅',
            'description' => 'Total services completed in period',
            'target'      => 10,  // per day
            'unit'        => 'jobs',
        ],
        'avg_repair_time' => [
            'label'       => 'Average Repair Time',
            'icon'        => '⏱️',
            'description' => 'Average effective working minutes per job',
            'target'      => 60,  // minutes
            'unit'        => 'min',
        ],
        'first_time_fix_rate' => [
            'label'       => 'First Time Fix Rate',
            'icon'        => '🎯',
            'description' => '% of jobs fixed without rework',
            'target'      => 90,  // percent
            'unit'        => '%',
        ],
        'warranty_return_rate' => [
            'label'       => 'Warranty Return Rate',
            'icon'        => '🔄',
            'description' => '% of completed jobs returned under warranty',
            'target'      => 3,   // percent (lower is better)
            'unit'        => '%',
        ],
        'productivity_score' => [
            'label'       => 'Productivity Score',
            'icon'        => '📈',
            'description' => 'Composite score based on all KPIs',
            'target'      => 80,  // percent
            'unit'        => '%',
        ],
        'utilization_rate' => [
            'label'       => 'Utilization Rate',
            'icon'        => '⚡',
            'description' => '% of working time spent on repairs',
            'target'      => 70,  // percent
            'unit'        => '%',
        ],
        'customer_rating' => [
            'label'       => 'Customer Rating',
            'icon'        => '⭐',
            'description' => 'Average customer satisfaction rating',
            'target'      => 4.5, // out of 5
            'unit'        => '/5',
        ],
        'revenue_generated' => [
            'label'       => 'Revenue Generated',
            'icon'        => '💰',
            'description' => 'Total service charge + parts revenue',
            'target'      => 5000000, // IDR per month
            'unit'        => 'Rp',
        ],
        'parts_usage_accuracy' => [
            'label'       => 'Parts Usage Accuracy',
            'icon'        => '🔩',
            'description' => '% of parts used that match diagnosis',
            'target'      => 95,  // percent
            'unit'        => '%',
        ],
        'rework_count' => [
            'label'       => 'Rework Count',
            'icon'        => '🔧',
            'description' => 'Number of services that needed rework',
            'target'      => 0,   // lower is better
            'unit'        => 'jobs',
        ],
    ];

    // ═══════════════════════════════════════════════════════════
    // SPAREPART USAGE CATEGORIES
    // ═══════════════════════════════════════════════════════════

    public const SPAREPART_USAGE_TYPES = [
        'replace'       => ['label' => 'Replace (Ganti)',        'icon' => '🔄'],
        'new_install'   => ['label' => 'New Install (Pasang)',   'icon' => '➕'],
        'return'        => ['label' => 'Return (Kembalikan)',    'icon' => '↩️'],
        'swap'          => ['label' => 'Swap (Tukar)',           'icon' => '🔀'],
    ];

    // ═══════════════════════════════════════════════════════════
    // KNOWLEDGE ASSIST — AI Prompt Templates
    // ═══════════════════════════════════════════════════════════

    public const AI_ASSIST_PROMPTS = [
        'diagnose' => 'Berdasarkan gejala "{symptoms}" pada perangkat {device_type} {brand} {model}, apa kemungkinan penyebab dan langkah diagnosa yang disarankan?',
        'solution' => 'Untuk kerusakan {damage_type} pada {device_type} dengan penyebab {root_cause}, apa solusi perbaikan yang direkomendasikan?',
        'parts'    => 'Sparepart apa yang dibutuhkan untuk mengganti {component} pada {device_type} {brand} {model}?',
        'estimate' => 'Berapa estimasi waktu pengerjaan untuk {repair_type} pada {device_type} dengan tingkat kesulitan {difficulty}?',
        'similar'  => 'Tampilkan riwayat servis dengan gejala serupa: {symptoms} pada perangkat {device_type}.',
    ];
}
