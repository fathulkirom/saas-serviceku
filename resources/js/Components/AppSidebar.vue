<template>
  <aside
    class="fixed inset-y-0 left-0 z-40 flex flex-col transition-all duration-200 overflow-y-auto"
    :style="{
      width: collapsed ? 'var(--sidebar-collapsed)' : 'var(--sidebar-width)',
      background: 'var(--bg-sidebar)',
    }"
  >
    <!-- Logo -->
    <div class="flex items-center h-16 px-4 border-b shrink-0" :style="{ borderColor: 'rgba(255,255,255,.08)' }">
      <span v-if="!collapsed" class="text-lg font-extrabold tracking-tight" style="color: var(--text-sidebar-active)">Service<span style="color: var(--color-primary)">KU</span></span>
      <span v-else class="text-lg font-extrabold" style="color: var(--color-primary)">SK</span>
    </div>

    <!-- Menu Items -->
    <nav class="flex-1 px-3 py-4 space-y-0.5 overflow-y-auto">
      <template v-for="group in visibleGroups" :key="group.key">
        <p v-if="!collapsed && group.label" class="px-3 pt-4 pb-1 text-[10px] font-bold uppercase tracking-widest" style="color: rgba(255,255,255,.3)">{{ group.label }}</p>
        <a
          v-for="item in group.items"
          :key="item.key"
          :href="item.route ? route(item.route) : '#'"
          :class="[
            'flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors no-underline',
            isActive(item) ? 'active' : ''
          ]"
          :style="{
            color: isActive(item) ? 'var(--text-sidebar-active)' : 'var(--text-sidebar)',
            background: isActive(item) ? 'var(--bg-sidebar-active)' : 'transparent',
          }"
          @mouseenter="(e) => { if (!isActive(item)) e.target.style.background = 'var(--bg-sidebar-hover)' }"
          @mouseleave="(e) => { if (!isActive(item)) e.target.style.background = 'transparent' }"
        >
          <span class="w-5 h-5 flex items-center justify-center shrink-0 text-base" v-html="item.icon"></span>
          <span v-if="!collapsed" class="truncate">{{ item.label }}</span>
          <!-- Locked indicator -->
          <span v-if="!collapsed && item.locked" class="ml-auto text-[10px] px-1.5 py-0.5 rounded" style="background: var(--color-locked-soft); color: var(--color-locked-text)">🔒</span>
        </a>
      </template>
    </nav>

    <!-- Collapse Toggle -->
    <button @click="$emit('toggle')" class="mx-3 mb-4 p-2 rounded-lg text-xs transition-colors shrink-0" style="color: rgba(255,255,255,.3)" @mouseenter="(e) => e.target.style.background = 'var(--bg-sidebar-hover)'" @mouseleave="(e) => e.target.style.background = 'transparent'">
      {{ collapsed ? '→' : '←' }}
    </button>
  </aside>
</template>

<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';

const props = defineProps({
  collapsed: { type: Boolean, default: false },
});

defineEmits(['toggle']);

const page = usePage();
const role = computed(() => page.props.auth?.user?.role || 'technician');
const permissions = computed(() => page.props.role_permissions?.[role.value] || []);
const planAccess = computed(() => page.props.plan_access || {});
const businessType = computed(() => page.props.tenant?.business_type || 'full_service');

const isActive = (item) => {
  const path = window.location.pathname;
  return item.match ? new RegExp(item.match).test(path) : path.startsWith(item.path || '');
};

// ── Menu Definitions (role-aware + entitlement-aware) ──
const menuByRole = {
  owner: [
    { key: 'dashboard', label: 'Dashboard', icon: '📊', route: 'dashboard', path: '/dashboard' },
    { key: 'services', label: 'Service', icon: '🔧', route: 'services.index', path: '/services', match: '^/services' },
    { key: 'sales', label: 'Penjualan', icon: '💰', route: 'sales.index', path: '/sales' },
    { key: 'customers', label: 'Customer', icon: '👥', route: 'customers.index', path: '/customers' },
    { key: 'inventaris', label: 'Inventaris', icon: '📦', route: 'inventaris.index', path: '/inventaris' },
    { key: 'keuangan', label: 'Keuangan', icon: '💳', route: 'keuangan.index', path: '/keuangan' },
    { key: 'pengaturan', label: 'Pengaturan', icon: '⚙️', route: 'pengaturan.index', path: '/pengaturan' },
    { key: 'reports', label: 'Laporan', icon: '📈', route: 'reports.index', path: '/reports' },
    { key: 'sistem', label: 'Sistem', icon: '🖥️', route: 'sistem.index', path: '/sistem' },
  ],
  admin: [
    { key: 'dashboard', label: 'Dashboard', icon: '📊', route: 'dashboard', path: '/dashboard' },
    { key: 'services', label: 'Service', icon: '🔧', route: 'services.index', path: '/services', match: '^/services' },
    { key: 'sales', label: 'Penjualan', icon: '💰', route: 'sales.index', path: '/sales' },
    { key: 'customers', label: 'Customer', icon: '👥', route: 'customers.index', path: '/customers' },
    { key: 'inventaris', label: 'Inventaris', icon: '📦', route: 'inventaris.index', path: '/inventaris' },
    { key: 'keuangan', label: 'Keuangan', icon: '💳', route: 'keuangan.index', path: '/keuangan' },
    { key: 'pengaturan', label: 'Pengaturan', icon: '⚙️', route: 'pengaturan.index', path: '/pengaturan' },
  ],
  cs: [
    { key: 'cs-dashboard', label: 'Dashboard CS', icon: '📊', route: 'cs.dashboard', path: '/cs-dashboard' },
    { key: 'services', label: 'Service', icon: '🔧', route: 'services.index', path: '/services', match: '^/services' },
    { key: 'customers', label: 'Customer', icon: '👥', route: 'customers.index', path: '/customers' },
    { key: 'inventaris', label: 'Inventaris', icon: '📦', route: 'inventaris.index', path: '/inventaris' },
    { key: 'kas', label: 'Kasir', icon: '💵', route: 'kas.index', path: '/kas' },
  ],
  technician: [
    { key: 'tech-dashboard', label: 'Dashboard', icon: '📊', route: 'technician.dashboard', path: '/technician-dashboard' },
    { key: 'services', label: 'Service Saya', icon: '🔧', route: 'services.index', path: '/services', match: '^/services' },
    { key: 'inventaris', label: 'Sparepart', icon: '📦', route: 'inventaris.index', path: '/inventaris' },
  ],
  cashier: [
    { key: 'cashier-dashboard', label: 'Dashboard Kasir', icon: '📊', route: 'cashier.dashboard', path: '/cashier-dashboard' },
    { key: 'kas', label: 'Kas', icon: '💵', route: 'kas.index', path: '/kas' },
    { key: 'keuangan', label: 'Keuangan', icon: '💳', route: 'keuangan.index', path: '/keuangan' },
  ],
  courier: [
    { key: 'courier-dashboard', label: 'Dashboard', icon: '📊', route: 'courier.dashboard', path: '/courier-dashboard' },
    { key: 'services', label: 'Pickup/Delivery', icon: '🚚', route: 'services.index', path: '/services' },
  ],
  manager: [
    { key: 'dashboard', label: 'Dashboard', icon: '📊', route: 'dashboard', path: '/dashboard' },
    { key: 'services', label: 'Service', icon: '🔧', route: 'services.index', path: '/services', match: '^/services' },
    { key: 'sales', label: 'Penjualan', icon: '💰', route: 'sales.index', path: '/sales' },
    { key: 'customers', label: 'Customer', icon: '👥', route: 'customers.index', path: '/customers' },
    { key: 'inventaris', label: 'Inventaris', icon: '📦', route: 'inventaris.index', path: '/inventaris' },
    { key: 'keuangan', label: 'Keuangan', icon: '💳', route: 'keuangan.index', path: '/keuangan' },
    { key: 'reports', label: 'Laporan', icon: '📈', route: 'reports.index', path: '/reports' },
  ],
};

const rawMenu = computed(() => menuByRole[role.value] || menuByRole.technician);

// Group menu items for the sidebar
const visibleGroups = computed(() => {
  const items = rawMenu.value.filter(item => {
    // Entitlement check: hide if module not available
    if (item.module && planAccess.value[item.module] === false) return false;
    return true;
  });

  return [{
    key: 'main',
    label: 'Menu',
    items,
  }];
});
</script>
