/**
 * Helper terpusat untuk halaman & komponen detail Servis.
 * Memuat helper status/label/warna dan formatter angka/tanggal/inisial
 * agar tidak diduplikasi di beberapa komponen Services.
 * 
 * SPRINT 36A — Refined: complete 14-status lifecycle, checklist categories,
 * QC checklist, photo categories, warranty flows.
 */

// ═══════════════════════════════════════════════════════════
// COMPOSABLE — useServiceStatus
// ═══════════════════════════════════════════════════════════

export function useServiceStatus() {
    return {
        statusLabel,
        statusStyle,
        statusPhase,
        statusDot,
        isTerminalStatus,
        isActiveStatus,
        getNextStatuses,
        isTransitionAllowed,
        requiresPaymentBeforeClose,
        requiresQcBeforeReady,
        requiresDiagnosisBeforeRepair,
        formatNumber,
        formatDate,
        getInitials,
        getChecklistItemName,
    };
}

// ═══════════════════════════════════════════════════════════
// COMPLETE STATUS TIMELINE (14 status — Sprint 36A Refined)
// ═══════════════════════════════════════════════════════════

export const statusTimeline = [
    { key: 'menunggu_alokasi', label: 'Masuk',         phase: 'intake',   icon: '📥' },
    { key: 'diterima',         label: 'Diterima',       phase: 'intake',   icon: '✅' },
    { key: 'diagnosa',         label: 'Diagnosa',       phase: 'diagnosis',icon: '🔍' },
    { key: 'menunggu_konfirmasi_pelanggan', label: 'Konfirmasi', phase: 'approval', icon: '📞' },
    { key: 'menunggu_konfirmasi_internal',  label: 'Konfirmasi Internal', phase: 'approval', icon: '🏢' },
    { key: 'indent',           label: 'Indent Part',    phase: 'parts',    icon: '📦' },
    { key: 'onpartner',        label: 'Di Partner',     phase: 'external', icon: '🤝' },
    { key: 'dikerjakan',       label: 'Dikerjakan',     phase: 'repair',   icon: '🔧' },
    { key: 'selesai',          label: 'QC / Selesai',   phase: 'qc',       icon: '🔍' },
    { key: 'siap_diambil',     label: 'Siap Diambil',   phase: 'pickup',   icon: '📦' },
    { key: 'diambil',          label: 'Diambil',        phase: 'pickup',   icon: '🤝' },
    { key: 'close',            label: 'Closed',         phase: 'closed',   icon: '🔒' },
    { key: 'cancel',           label: 'Cancel',         phase: 'terminal', icon: '❌' },
    { key: 'void',             label: 'Void',           phase: 'terminal', icon: '🚫' },
];

// ═══════════════════════════════════════════════════════════
// ALLOWED TRANSITIONS — Backend-synced
// ═══════════════════════════════════════════════════════════

export const allowedTransitions = {
    menunggu_alokasi:           ['diterima', 'cancel'],
    diterima:                   ['diagnosa', 'dikerjakan', 'menunggu_alokasi', 'cancel'],
    diagnosa:                   ['menunggu_konfirmasi_pelanggan', 'menunggu_konfirmasi_internal', 'dikerjakan', 'indent', 'cancel'],
    dikerjakan:                 ['menunggu_konfirmasi_pelanggan', 'menunggu_konfirmasi_internal', 'indent', 'onpartner', 'selesai', 'cancel'],
    menunggu_konfirmasi_pelanggan: ['dikerjakan', 'siap_diambil', 'cancel'],
    menunggu_konfirmasi_internal:  ['dikerjakan', 'siap_diambil', 'cancel'],
    indent:                     ['dikerjakan', 'cancel'],
    onpartner:                  ['dikerjakan', 'selesai', 'cancel'],
    selesai:                    ['siap_diambil', 'diambil', 'close'],
    siap_diambil:               ['selesai', 'diambil', 'close'],
    diambil:                    ['close'],
    close:                      [],
    cancel:                     ['close'],
    void:                       ['close'],
};

export function getNextStatuses(currentStatus) {
    return allowedTransitions[currentStatus] || [];
}

export function isTransitionAllowed(from, to) {
    return (allowedTransitions[from] || []).includes(to);
}

export function statusLabel(status) {
    return ({
        menunggu_alokasi: 'Pending',
        diterima: 'Diterima',
        diagnosa: 'Diagnosa',
        dikerjakan: 'On Progress',
        menunggu_konfirmasi_pelanggan: 'Konfirmasi',
        menunggu_konfirmasi_internal: 'Konfirmasi Internal',
        siap_diambil: 'Siap Diambil',
        diambil: 'Diambil',
        indent: 'Waiting Parts',
        onpartner: 'Partner',
        selesai: 'QC / Selesai',
        cancel: 'Cancel',
        void: 'Void',
        close: 'Close',
    }[status] || status);
}

export function statusPhase(status) {
    return ({
        menunggu_alokasi: 'intake', diterima: 'intake', diagnosa: 'diagnosis',
        menunggu_konfirmasi_pelanggan: 'approval', menunggu_konfirmasi_internal: 'approval',
        indent: 'parts', onpartner: 'external', dikerjakan: 'repair',
        selesai: 'qc', siap_diambil: 'pickup', diambil: 'pickup',
        close: 'closed', cancel: 'terminal', void: 'terminal',
    }[status] || 'unknown');
}

export function isTerminalStatus(status) {
    return ['close', 'cancel', 'void'].includes(status);
}

export function isActiveStatus(status) {
    return status && !isTerminalStatus(status);
}

export function requiresPaymentBeforeClose(status) {
    return ['selesai', 'siap_diambil'].includes(status);
}

export function requiresQcBeforeReady(status) {
    return status === 'selesai';
}

export function requiresDiagnosisBeforeRepair(status) {
    return status === 'dikerjakan';
}

export function isStepDone(currentStatus, stepKey) {
    const timelineOrder = statusTimeline.map(s => s.key);
    const currentIdx = timelineOrder.indexOf(currentStatus);
    const stepIdx = timelineOrder.indexOf(stepKey);
    if (currentIdx === -1 || stepIdx === -1) return false;
    return stepIdx <= currentIdx;
}

export function statusDot(status) {
    return ({
        menunggu_alokasi: 'var(--warning)', diterima: 'var(--info)', dikerjakan: 'var(--info)',
        menunggu_konfirmasi_pelanggan: 'var(--danger)', menunggu_konfirmasi_internal: 'var(--danger)',
        siap_diambil: 'var(--success)', indent: 'var(--primary)', onpartner: 'var(--primary)',
        selesai: 'var(--success)', cancel: 'var(--danger)', void: 'var(--danger)', close: 'var(--text-muted)', diambil: 'var(--success)',
    }[status] || '#8e8ea0');
}

export function statusStyle(status) {
    const colors = {
        menunggu_alokasi: { bg: 'rgba(243,156,18,0.12)', color: '#b87c0e' },
        diterima: { bg: 'var(--info-soft)', color: 'var(--info-text)' },
        dikerjakan: { bg: 'var(--info-soft)', color: 'var(--info-text)' },
        menunggu_konfirmasi_pelanggan: { bg: 'var(--danger-soft)', color: 'var(--danger-text)' },
        menunggu_konfirmasi_internal: { bg: 'var(--danger-soft)', color: 'var(--danger-text)' },
        indent: { bg: 'var(--primary-soft)', color: 'var(--primary)' },
        onpartner: { bg: 'var(--primary-soft)', color: 'var(--primary)' },
        selesai: { bg: 'var(--success-soft)', color: 'var(--success-text)' },
        cancel: { bg: 'var(--danger-soft)', color: 'var(--danger-text)' },
        void: { bg: 'var(--danger-soft)', color: 'var(--danger-text)' },
        diambil: { bg: 'var(--success-soft)', color: 'var(--success-text)' },
    };
    const c = colors[status] || { bg: 'rgba(142,142,160,0.12)', color: '#71717a' };
    return `background: ${c.bg}; color: ${c.color};`;
}

export function getChecklistItemName(itemId, templatesMasuk = [], templatesKeluar = []) {
    const allItems = [
        ...(templatesMasuk || []).flatMap(t => t.items || []),
        ...(templatesKeluar || []).flatMap(t => t.items || []),
    ];
    const found = allItems.find(i => String(i.id) === String(itemId) || String(i.sort_order) === String(itemId));
    return found?.item_name || itemId;
}

export function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num || 0);
}

export function formatDate(date) {
    if (!date) return '-';
    return new Date(date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

export function getInitials(name) {
    if (!name) return '?';
    return name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
}

// ═══════════════════════════════════════════════════════════
// CHECKLIST CATEGORIES — Sprint 36A (10 categories, 55 items)
// ═══════════════════════════════════════════════════════════

export const checklistCategories = [
    { key: 'body',       label: 'Body / Casing',       icon: '📱', mandatory: true,  items: ['body_scratch','body_dent','body_crack','body_missing','body_color_fade'] },
    { key: 'lcd',        label: 'LCD / Layar',          icon: '🖥️', mandatory: true,  items: ['lcd_crack','lcd_dead_pixel','lcd_line','lcd_bleed','lcd_touch_issue','lcd_flicker'] },
    { key: 'touchscreen',label: 'Touchscreen',          icon: '👆', mandatory: false, items: ['touch_dead_zone','touch_ghost','touch_delay','touch_crack'] },
    { key: 'camera',     label: 'Kamera',               icon: '📷', mandatory: false, items: ['cam_front_blur','cam_rear_blur','cam_front_dead','cam_rear_dead','cam_flash_dead','cam_lens_scratch'] },
    { key: 'speaker',    label: 'Speaker / Audio',      icon: '🔊', mandatory: false, items: ['spk_distorted','spk_dead','spk_low_volume','mic_dead','mic_noisy','earpiece_dead'] },
    { key: 'charging',   label: 'Charging / Baterai',   icon: '🔋', mandatory: true,  items: ['charge_port_loose','charge_port_dead','battery_swollen','battery_drain','battery_dead','wireless_dead'] },
    { key: 'faceid_fingerprint', label: 'Face ID / Fingerprint', icon: '🔐', mandatory: false, items: ['faceid_dead','fingerprint_dead','fingerprint_loose'] },
    { key: 'keyboard_trackpad',  label: 'Keyboard / Trackpad',  icon: '⌨️', mandatory: false, items: ['key_missing','key_stuck','key_dead','backlight_dead','trackpad_dead','trackpad_jumpy'] },
    { key: 'wifi_bluetooth',     label: 'WiFi / Bluetooth',      icon: '📶', mandatory: false, items: ['wifi_dead','wifi_weak','bt_dead','bt_pair_fail'] },
    { key: 'mainboard',   label: 'Mainboard / Komponen', icon: '🔧', mandatory: false, items: ['mb_corrosion','mb_short','mb_water_damage','mb_prior_repair','mb_missing_part'] },
];

export const checklistItemLabels = {
    body_scratch:'Goresan pada body', body_dent:'Penyok pada body', body_crack:'Retak pada casing', body_missing:'Bagian hilang', body_color_fade:'Warna pudar',
    lcd_crack:'Retak pada LCD', lcd_dead_pixel:'Dead pixel', lcd_line:'Garis pada layar', lcd_bleed:'Backlight bleed', lcd_touch_issue:'Touchscreen tidak responsif', lcd_flicker:'Layar berkedip',
    touch_dead_zone:'Dead zone', touch_ghost:'Ghost touch', touch_delay:'Touch delay', touch_crack:'Retak pada touchscreen',
    cam_front_blur:'Kamera depan blur', cam_rear_blur:'Kamera belakang blur', cam_front_dead:'Kamera depan mati', cam_rear_dead:'Kamera belakang mati', cam_flash_dead:'Flash mati', cam_lens_scratch:'Goresan lensa',
    spk_distorted:'Suara pecah', spk_dead:'Speaker mati', spk_low_volume:'Volume rendah', mic_dead:'Mikrofon mati', mic_noisy:'Mikrofon berisik', earpiece_dead:'Earpiece mati',
    charge_port_loose:'Port charging longgar', charge_port_dead:'Port charging mati', battery_swollen:'Baterai kembung', battery_drain:'Baterai cepat habis', battery_dead:'Baterai mati total', wireless_dead:'Wireless charging mati',
    faceid_dead:'Face ID tidak berfungsi', fingerprint_dead:'Fingerprint tidak berfungsi', fingerprint_loose:'Sensor fingerprint longgar',
    key_missing:'Keycap hilang', key_stuck:'Tombol macet', key_dead:'Tombol mati', backlight_dead:'Backlight keyboard mati', trackpad_dead:'Trackpad mati', trackpad_jumpy:'Trackpad loncat',
    wifi_dead:'WiFi mati', wifi_weak:'WiFi lemah', bt_dead:'Bluetooth mati', bt_pair_fail:'Bluetooth gagal pairing',
    mb_corrosion:'Korosi mainboard', mb_short:'Korsleting', mb_water_damage:'Water damage', mb_prior_repair:'Bekas perbaikan', mb_missing_part:'Komponen hilang',
};

// ═══════════════════════════════════════════════════════════
// QC CHECKLIST — Sprint 36A (16 items, ALL mandatory)
// ═══════════════════════════════════════════════════════════

export const qcChecklist = [
    { key: 'qc_all_functions', label: 'Semua fungsi utama berjalan normal' },
    { key: 'qc_charging',      label: 'Charging berfungsi normal' },
    { key: 'qc_camera',        label: 'Kamera depan & belakang OK' },
    { key: 'qc_mic_speaker',   label: 'Mic & Speaker OK' },
    { key: 'qc_wifi',          label: 'WiFi terhubung normal' },
    { key: 'qc_bluetooth',     label: 'Bluetooth pairing OK' },
    { key: 'qc_lcd',           label: 'LCD tidak ada dead pixel / garis' },
    { key: 'qc_touch',         label: 'Touchscreen responsif seluruh area' },
    { key: 'qc_fingerprint',   label: 'Fingerprint / Face ID berfungsi' },
    { key: 'qc_keyboard',      label: 'Keyboard semua tombol berfungsi' },
    { key: 'qc_trackpad',      label: 'Trackpad responsif' },
    { key: 'qc_stress_test',   label: 'Stress test 30 menit lulus' },
    { key: 'qc_burn_test',     label: 'Burn test 1 jam lulus' },
    { key: 'qc_battery_test',  label: 'Baterai bertahan minimal 2 jam' },
    { key: 'qc_physical',      label: 'Tidak ada kerusakan fisik baru' },
    { key: 'qc_cleaning',      label: 'Unit bersih dari debu/sisa perbaikan' },
];

// ═══════════════════════════════════════════════════════════
// PHOTO CATEGORIES — Sprint 36A
// ═══════════════════════════════════════════════════════════

export const photoCategories = [
    { key: 'intake',      label: 'Saat Masuk',       icon: '📥', required: true },
    { key: 'disassembly', label: 'Saat Dibongkar',    icon: '🔧', required: false },
    { key: 'repair',      label: 'Saat Perbaikan',    icon: '🔩', required: false },
    { key: 'completed',   label: 'Sesudah Selesai',   icon: '✅', required: true },
    { key: 'qc',          label: 'Saat QC',            icon: '🔍', required: true },
    { key: 'handover',    label: 'Saat Serah Terima',  icon: '🤝', required: false },
];

// ═══════════════════════════════════════════════════════════
// WARRANTY HELPERS — Sprint 36A
// ═══════════════════════════════════════════════════════════

export const warrantyTypes = [
    { key: 'service',  label: 'Garansi Jasa',    icon: '🔧' },
    { key: 'part',     label: 'Garansi Sparepart',icon: '🔩' },
];

export function warrantyStatusLabel(status) {
    return ({ active: 'Active', expired: 'Expired', claimed: 'Claimed', void: 'Void' }[status] || status);
}

export function isWarrantyExpiringSoon(expiredAt, daysThreshold = 3) {
    if (!expiredAt) return false;
    const days = Math.floor((new Date(expiredAt) - Date.now()) / 86400000);
    return days > 0 && days <= daysThreshold;
}
