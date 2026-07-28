<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">Dashboard {{ $page.props.role_permissions?.[$page.props.auth?.user?.role || '']?.includes('work_on_services') ? 'Operational' : 'Limited' }}</h2>
          <p class="text-xs mt-0.5" :style="{ color: 'var(--text-muted)' }">{{ greeting }}, {{ $page.props.auth.user.name }} — {{ currentDate }} • Sync: {{ lastUpdated }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <Link v-if="isNotTechnician" :href="route('services.create')" class="btn-primary text-xs">+ Unit Baru</Link>
          <Link v-if="isNotTechnician" :href="route('keuangan.index')" class="btn-primary text-xs">+ Penjualan</Link>
          <Link v-if="isNotTechnician" :href="route('keuangan.index', {tab: 'pengeluaran'})" class="btn-secondary text-xs">+ Pengeluaran</Link>
          <Link :href="route('kas.index')" class="btn-secondary text-xs">Shift Kasir</Link>
        </div>
      </div>
    </template>

    <Skeleton v-if="!stats" type="stat" :count="4" />
    <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
      <StatCard label="Servis Hari Ini" :value="stats.services_today" color="purple" variant="glass" />
      <StatCard label="Pendapatan Hari Ini" value="Rp" color="green" variant="glass">
        <template #value>Rp {{ formatNumber(stats.revenue_today ?? 0) }}</template>
      </StatCard>
      <StatCard label="Servis Aktif" :value="stats.active_services ?? 0" color="blue" variant="glass" />
      <StatCard label="Stok Menipis" :value="stats.low_stock ?? 0" color="orange" variant="glass" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <div class="lg:col-span-2">
        <template v-if="!recentServices">
          <Skeleton type="table" :count="5" />
        </template>
        <template v-else>
          <KTable title="Servis Terbaru" :columns="serviceColumns" :rows="recentServices" hoverable="false"
            emptyIcon="services" emptyTitle="Belum ada servis" emptyDescription="Belum ada servis terbaru.">
            <template #cell-customer="{ row }">
              <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-[10px] font-bold text-white flex-shrink-0"
                  :style="{ background: 'var(--accent-primary)' }">{{ row.customer?.name?.charAt(0) || '?' }}</div>
                <span class="text-sm font-medium">{{ row.customer?.name || '-' }}</span>
              </div>
            </template>
            <template #cell-status="{ row }">
              <Badge :status="row.status">{{ statusLabel(row.status) }}</Badge>
            </template>
          </KTable>
        </template>
      </div>
      <div>
        <div class="card p-5 mb-5">
          <h3 class="text-sm font-bold mb-4" :style="{ color: 'var(--text-primary)' }">Status Servis</h3>
          <div class="space-y-3">
            <div v-for="s in statusList" :key="s.key" class="flex items-center justify-between">
              <span class="text-xs flex items-center gap-2" :style="{ color: 'var(--text-secondary)' }">
                <span class="w-2 h-2 rounded-full" :style="{ background: s.color }"></span>{{ s.label }}
              </span>
              <span class="text-xs font-bold" :style="{ color: 'var(--text-primary)' }">{{ s.count }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import KTable from '@/Components/KTable.vue';
import KCard from '@/Components/KCard.vue';
import Badge from '@/Components/Badge.vue';
import Skeleton from '@/Components/Skeleton.vue';
import { useFormatter } from '@/Composables/useFormatter.js';
import { statusLabel, statusBadgeVariant } from '@/Utils/statusMaps.js';

const props = defineProps({
  stats: Object,
  recentServices: Array,
  isNotTechnician: Boolean,
});

const { formatNumber, currentDate, greeting } = useFormatter();

const lastUpdated = ref(new Date().toLocaleTimeString('id-ID'));
let refreshInterval = null;

onMounted(() => {
  refreshInterval = setInterval(() => {
    router.reload({ only: ['stats', 'recentServices'], preserveScroll: true });
    lastUpdated.value = new Date().toLocaleTimeString('id-ID');
  }, 60000);
});

onUnmounted(() => {
  if (refreshInterval) clearInterval(refreshInterval);
});

const statusColors = {
  menunggu_alokasi: '#f39c12', diterima: '#f97316', dikerjakan: '#3b82f6',
  indent: '#a855f7', siap_diambil: '#10b981', selesai: '#059669', cancel: '#ef4444',
};

const statusList = computed(() => Object.entries(statusColors).map(([key, color]) => ({
  key, label: statusLabel(key), color, count: props.stats?.[key] || 0,
})));

const serviceColumns = [
  { key: 'customer', label: 'Pelanggan', bold: false },
  { key: 'device_type', label: 'Tipe' },
  { key: 'status', label: 'Status' },
];
</script>
