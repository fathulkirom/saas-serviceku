<template>
    <LayoutNew
        :activeLayout="activeLayout"
        :menuStyle="menuStyle"
        :allowedMenuItems="allowedMenuItems"
        :groupedMenuItems="groupedMenuItems"
        :topMenuItems="topMenuItems"
        :topbarPrimaryItems="topbarPrimaryItems"
        :topbarOverflowItems="topbarOverflowItems"
        :branches="branches"
        :currentBranch="currentBranch"
        :planName="planName"
        :userInitials="userInitials"
        :canManage="canManage"
        :mobileOpen="showMobileMenu"
        :searchOpen="showSearch"
        :sidebarPosition="sidebarPosition"
        :sidebarHidden="localSidebarHidden"
        :visibleGroups="visibleGroups"
        @toggle-mobile="showMobileMenu = !showMobileMenu"
        @close-mobile="showMobileMenu = false"
        @open-search="showSearch = true"
        @close-search="showSearch = false"
        @toggle-sidebar="toggleSidebar"
    >
        <template #header>
            <slot name="header" />
        </template>
        <slot />
    </LayoutNew>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';
import { usePage, router } from '@inertiajs/vue3';
import LayoutNew from '@/Layouts/Themes/LayoutNew.vue';
import { getIcon } from '@/Components/Icons.js';

const page = usePage();
const showMobileMenu = ref(false);
const showSearch = ref(false);

const userInitials = computed(() => {
    const name = page.props.auth?.user?.name || '';
    return name.split(' ').map(w => w[0]).join('').substring(0, 2).toUpperCase();
});

const rolePermissionsComputed = computed(() => page.props.role_permissions || {});
const canManage = computed(() => {
    const perms = rolePermissionsComputed.value[page.props.auth?.user?.role || ''] || [];
    return perms.includes('manage_users') || perms.includes('manage_settings');
});
const localSidebarHidden = ref(false);

const planName = computed(() => page.props.tenant?.plan?.name || page.props.auth?.user?.plan || 'Trial');
const branches = computed(() => page.props.branches || []);
const currentBranch = computed(() => page.props.currentBranch || null);

// ===== Branding per-tenant (tahap 3.8) =====
// Terapkan primary_color dari TenantSetting ke CSS variables tema.
const primaryColor = computed(() => page.props.tenant?.primary_color || '#4F46E5');

function hexToRgba(hex, alpha) {
    const h = String(hex).replace('#', '');
    const r = parseInt(h.substring(0, 2), 16) || 79;
    const g = parseInt(h.substring(2, 4), 16) || 70;
    const b = parseInt(h.substring(4, 6), 16) || 229;
    return `rgba(${r}, ${g}, ${b}, ${alpha})`;
}

function darkenHex(hex, percent) {
    const h = String(hex).replace('#', '');
    const num = parseInt(h, 16);
    const amt = Math.round(2.55 * percent);
    const r = Math.max(0, (num >> 16) - amt);
    const g = Math.max(0, ((num >> 8) & 0xff) - amt);
    const b = Math.max(0, (num & 0xff) - amt);
    return `#${((r << 16) | (g << 8) | b).toString(16).padStart(6, '0')}`;
}

function applyTenantTheme(color) {
    if (!color) return;
    const root = document.documentElement;
    root.style.setProperty('--accent-primary', color);
    root.style.setProperty('--accent-hover', darkenHex(color, 12));
    root.style.setProperty('--accent-light', hexToRgba(color, 0.1));
    root.style.setProperty('--accent-glow', hexToRgba(color, 0.2));
}

onMounted(() => applyTenantTheme(primaryColor.value));
watch(primaryColor, (color) => applyTenantTheme(color));

const layoutMap = { 'sidebar': 'modern', 'slim-sidebar': 'slim', 'topbar': 'classic' };
const uiPrefs = computed(() => page.props.auth.user?.ui_preferences || {});

watch(() => uiPrefs.value.sidebar_hidden, (v) => { localSidebarHidden.value = v ?? false; }, { immediate: true });

const toggleSidebar = () => {
    localSidebarHidden.value = !localSidebarHidden.value;
    router.put(route('user.preferences.update'), {
        ui_preferences: { ...uiPrefs.value, sidebar_hidden: localSidebarHidden.value },
    });
};

const activeLayout = computed(() => {
    const prefs = uiPrefs.value;
    return layoutMap[prefs.layout] || prefs.layout || 'modern';
});

const menuStyle = computed(() => uiPrefs.value.menu_style || 'expanded');
const sidebarPosition = computed(() => uiPrefs.value.sidebar_position || 'left');
const sidebarHidden = computed(() => uiPrefs.value.sidebar_hidden || false);
const visibleGroups = computed(() => uiPrefs.value.visible_groups || null);

watch(() => page.props.auth.user?.ui_preferences, () => {
    // reactivity will handle via computed
}, { deep: true });

const menuItems = [
  { id: 'dashboard', label: 'Dashboard', href: route('dashboard'), roles: ['*'], group: 'Utama' },
  { id: 'services', label: 'Servis', href: route('services.index'), roles: ['owner','admin','manager','head_store','cs','technician','cashier','courier'], group: 'Utama', feature: 'services' },
  { id: 'customers', label: 'Pelanggan', href: route('customers.index'), roles: ['owner','admin','manager','head_store','cs','cashier'], group: 'Utama', feature: 'customers' },
  { id: 'keuangan', label: 'Keuangan', href: route('keuangan.index'), roles: ['owner','admin','manager','head_store','cashier'], group: 'Transaksi', feature: 'sales' },
  { id: 'kas', label: 'Kas', href: route('kas.index'), roles: ['owner','admin','manager','head_store','cashier'], group: 'Transaksi', feature: 'deposits' },
  { id: 'inventaris', label: 'Inventaris', href: route('inventaris.index'), roles: ['owner','admin','manager','head_store'], group: 'Manajemen', feature: 'products' },
  { id: 'servis_tools', label: 'Servis Tools', href: route('servis-tools.index'), roles: ['owner','admin','manager','head_store','cs','technician'], group: 'Manajemen', feature: 'services' },
  { id: 'laporan', label: 'Laporan', href: route('reports.index'), roles: ['owner','admin','manager','head_store'], group: 'Manajemen', feature: 'reports' },
  { id: 'sistem', label: 'Sistem', href: route('sistem.index'), roles: ['owner','admin'], group: 'Manajemen', feature: 'users' },
  { id: 'dokumen', label: 'Dokumen', href: route('dokumen.index'), roles: ['owner','admin','cs','technician'], group: 'Manajemen', feature: 'settings' },
  { id: 'pengaturan', label: 'Pengaturan', href: route('pengaturan.index'), roles: ['owner','admin'], group: 'Manajemen', feature: 'settings' },
  { id: 'monitoring', label: 'Monitoring', href: route('monitoring.index'), roles: ['owner','admin'], group: 'Manajemen', feature: 'monitoring' },
  { id: 'qr_scanner', label: 'QR Scanner', href: route('qr-scanner'), roles: ['owner','admin','cs','technician'], group: 'Manajemen', feature: 'services' },
];

const userRole = computed(() => page.props.auth.user?.role || '');
const planAccess = computed(() => page.props.plan_access || {});
const customMenuAccess = computed(() => page.props.auth.user?.custom_permissions?.menu_access || null);
const defaultMenus = computed(() => page.props.default_menus || []);

// 4-layer filter: plan → role → owner custom → default
const allowedMenuItems = computed(() => {
    return menuItems.filter(item => {
        // Layer 1: Plan feature check
        if (item.feature && planAccess.value[item.feature] === 'none') return false;

        // Layer 2: Role check
        if (item.roles && !item.roles.includes('*') && !item.roles.includes(userRole.value)) return false;

        // Layer 3: Owner custom menu_access (if set)
        if (customMenuAccess.value) {
            return customMenuAccess.value.includes(item.id);
        }

        // Layer 4: Default menus from plan (if no custom)
        if (defaultMenus.value.length > 0) {
            return defaultMenus.value.includes(item.id);
        }

        return true;
    });
});

const groupedMenuItems = computed(() => {
  const groups = { 'Utama': [], 'Transaksi': [], 'Manajemen': [] };
  allowedMenuItems.value.forEach(item => {
    if (groups[item.group]) groups[item.group].push(item);
  });
  return Object.keys(groups)
    .filter(key => groups[key].length > 0)
    .reduce((obj, key) => { obj[key] = groups[key]; return obj; }, {});
});

const topbarPrimaryItems = computed(() => {
  const primary = ['dashboard', 'services', 'customers', 'keuangan', 'kas', 'inventaris', 'laporan', 'servis_tools'];
  return allowedMenuItems.value.filter(i => primary.includes(i.id));
});

const topbarOverflowItems = computed(() => {
  const primary = new Set(['dashboard', 'services', 'customers', 'keuangan', 'kas', 'inventaris', 'laporan', 'servis_tools']);
  return allowedMenuItems.value.filter(i => !primary.has(i.id));
});

const topMenuItems = computed(() => allowedMenuItems.value);
</script>