/**
 * Helper bersama untuk layout (LayoutNew, Sidebar, HeaderBar).
 * Memusatkan warna grup menu, aksen grup, dan deteksi route aktif
 * agar tidak diduplikasi di beberapa komponen layout.
 */

export const groupColors = {
    'Utama': { accent: 'var(--primary)', light: 'var(--primary-soft)', hex: '#7c3aed' },
    'Transaksi': { accent: '#10b981', light: 'rgba(16, 185, 129, 0.12)', hex: '#10b981' },
    'Manajemen': { accent: '#3b82f6', light: 'rgba(59, 130, 246, 0.12)', hex: '#3b82f6' },
    'Operasional': { accent: 'var(--primary)', light: 'var(--primary-soft)', hex: '#7c3aed' },
    'Keuangan': { accent: '#10b981', light: 'rgba(16, 185, 129, 0.12)', hex: '#10b981' },
    'Sistem & Laporan': { accent: '#3b82f6', light: 'rgba(59, 130, 246, 0.12)', hex: '#3b82f6' },
};

export function getGroupAccent(group) {
    return groupColors[group]?.accent || 'var(--primary)';
}

export function isActive(href) {
    const base = route('dashboard').replace(/\/+$/, '');
    const routeName = href.replace(base, '').replace(/^\//, '');
    return route().current(routeName + '*');
}
