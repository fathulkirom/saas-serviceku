/**
 * Helper terpusat untuk halaman & komponen detail Servis.
 * Memuat helper status/label/warna dan formatter angka/tanggal/inisial
 * agar tidak diduplikasi di beberapa komponen Services.
 */

export const statusTimeline = [
    { key: 'menunggu_alokasi', label: 'Masuk' },
    { key: 'diterima', label: 'Diterima' },
    { key: 'dikerjakan', label: 'Dikerjakan' },
    { key: 'menunggu_konfirmasi_pelanggan', label: 'Konfirmasi' },
    { key: 'siap_diambil', label: 'Siap Diambil' },
    { key: 'selesai', label: 'Selesai' },
];

export function isStepDone(serviceStatus, key) {
    const order = statusTimeline.map(s => s.key);
    const currentIdx = order.indexOf(serviceStatus);
    const stepIdx = order.indexOf(key);
    return stepIdx <= currentIdx;
}

export function statusLabel(status) {
    return ({
        menunggu_alokasi: 'Pending',
        diterima: 'Diterima',
        dikerjakan: 'On Progress',
        menunggu_konfirmasi_pelanggan: 'Konfirmasi',
        menunggu_konfirmasi_internal: 'Konfirmasi Internal',
        siap_diambil: 'Siap Diambil',
        indent: 'Waiting Parts',
        onpartner: 'Partner',
        selesai: 'Finish',
        cancel: 'Cancel',
        void: 'Void',
        close: 'Close',
        diambil: 'Taken',
    }[status] || status);
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
