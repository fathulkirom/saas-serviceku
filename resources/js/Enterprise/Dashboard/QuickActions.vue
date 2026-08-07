<template>
  <div class="space-y-4">
    <SkText variant="label-sm" extraClass="uppercase tracking-wider">Aksi Cepat</SkText>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
      <Link
        v-for="action in visibleActions"
        :key="action.id"
        :href="action.url"
        class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border transition-all duration-200 hover:-translate-y-0.5 hover:shadow-md group"
        :style="{ borderColor: action.border || 'var(--border-color)', background: action.bg || 'var(--bg-card)' }"
      >
        <div
          class="w-9 h-9 rounded-lg flex items-center justify-center transition-transform group-hover:scale-110"
          :style="{ background: action.iconBg || 'var(--bg-hover)' }"
        >
          <span class="w-5 h-5" :style="{ color: action.iconColor || 'var(--text-secondary)' }" v-html="action.icon"></span>
        </div>
        <span class="text-xs font-semibold text-center" :style="{ color: action.textColor || 'var(--text-primary)' }">
          {{ action.label }}
        </span>
      </Link>
    </div>
    <div v-if="!visibleActions.length" class="py-4 text-center">
      <SkText variant="caption">Tidak ada aksi cepat tersedia untuk role ini.</SkText>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import SkText from '@/Enterprise/Components/Typography/Text.vue';

/**
 * QUICK ACTIONS — role-aware action buttons.
 *
 * Actions didefinisikan per role & feature.
 */
const props = defineProps({
  userRole: { type: String, default: '' },
  planAccess: { type: Object, default: () => ({}) },
});

const allActions = [
  {
    id: 'new_service',
    label: 'Servis Baru',
    url: '/services/create',
    roles: ['owner', 'admin', 'manager', 'cs'],
    features: ['services'],
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>',
    iconBg: 'var(--primary-soft)',
    iconColor: 'var(--primary)',
    border: 'var(--primary-soft-border)',
  },
  {
    id: 'new_sale',
    label: 'Penjualan Baru',
    url: '/keuangan',
    roles: ['owner', 'admin', 'manager', 'cashier', 'head_store'],
    features: ['sales'],
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>',
    iconBg: 'var(--success-soft)',
    iconColor: 'var(--success)',
  },
  {
    id: 'new_customer',
    label: 'Pelanggan Baru',
    url: '/customers/create',
    roles: ['owner', 'admin', 'manager', 'cs'],
    features: ['customers'],
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>',
    iconBg: 'var(--warning-soft)',
    iconColor: 'var(--warning)',
  },
  {
    id: 'new_product',
    label: 'Produk Baru',
    url: '/inventaris/create',
    roles: ['owner', 'admin', 'manager', 'head_store'],
    features: ['products'],
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
    iconBg: 'var(--info-soft)',
    iconColor: 'var(--info)',
  },
  {
    id: 'new_supplier',
    label: 'Supplier Baru',
    url: '/inventaris/suppliers/create',
    roles: ['owner', 'admin', 'manager'],
    features: ['purchases'],
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857"/></svg>',
    iconBg: 'var(--bg-hover)',
    iconColor: 'var(--text-secondary)',
  },
  {
    id: 'cash_register',
    label: 'Buka Kasir',
    url: '/kas',
    roles: ['cashier'],
    features: ['cash_register'],
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>',
    iconBg: 'var(--success-soft)',
    iconColor: 'var(--success)',
  },
  {
    id: 'export_report',
    label: 'Export Laporan',
    url: '/reports',
    roles: ['owner', 'admin', 'manager'],
    features: ['reports'],
    icon: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>',
    iconBg: 'var(--bg-hover)',
    iconColor: 'var(--text-secondary)',
  },
];

const visibleActions = computed(() => {
  return allActions.filter(action => {
    // Role check
    if (action.roles.length && !action.roles.includes(props.userRole)) return false;
    // Feature check
    if (action.features && action.features.length) {
      const hasFeature = action.features.some(f => {
        const level = props.planAccess[f];
        return level === 'full' || level === 'read_only' || level === undefined;
      });
      if (!hasFeature) return false;
    }
    return true;
  });
});
</script>
