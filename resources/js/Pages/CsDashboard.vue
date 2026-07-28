<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">Dashboard CS</h2>
          <p class="text-xs mt-0.5" :style="{ color: 'var(--text-muted)' }">Halo, {{ $page.props.auth.user.name }}! Kelola penerimaan unit & alokasi teknisi. — {{ currentDate }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <Link :href="route('services.create')" class="btn-primary text-xs">+ Unit Masuk Baru</Link>
          <Link :href="route('customers.index')" class="btn-secondary text-xs">Data Member</Link>
        </div>
      </div>
    </template>

    <Skeleton v-if="!stats" type="stat" :count="4" />
    <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
      <StatCard label="Servis Hari Ini" :value="stats.services_today ?? 0" color="purple" variant="glass" />
      <StatCard label="Pelanggan Baru" :value="stats.new_customers_today ?? 0" color="green" variant="glass" />
      <StatCard label="Menunggu Alokasi" :value="stats.pending_allocation ?? 0" color="orange" variant="glass" />
      <StatCard label="Servis Aktif" :value="stats.active_services ?? 0" color="blue" variant="glass" />
    </div>

    <div class="card p-5 mb-5">
      <h3 class="text-sm font-bold mb-4" :style="{ color: 'var(--text-primary)' }">Aksi Cepat</h3>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <Link :href="route('services.create')" class="flex flex-col items-center gap-2 px-3 py-3 rounded-xl transition-all"
          :style="{ background: 'var(--accent-light)', color: 'var(--accent-primary)' }">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          <span class="text-xs font-semibold">Servis Baru</span>
        </Link>
        <Link :href="route('customers.create')" class="flex flex-col items-center gap-2 px-3 py-3 rounded-xl transition-all"
          :style="{ background: 'var(--bg-hover)', color: 'var(--text-secondary)' }">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
          <span class="text-xs font-semibold">Pelanggan Baru</span>
        </Link>
        <Link :href="route('keuangan.index')" class="flex flex-col items-center gap-2 px-3 py-3 rounded-xl transition-all"
          :style="{ background: 'var(--bg-hover)', color: 'var(--text-secondary)' }">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
          <span class="text-xs font-semibold">Transaksi</span>
        </Link>
        <Link :href="route('customers.index')" class="flex flex-col items-center gap-2 px-3 py-3 rounded-xl transition-all"
          :style="{ background: 'var(--bg-hover)', color: 'var(--text-secondary)' }">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857"/></svg>
          <span class="text-xs font-semibold">Data Member</span>
        </Link>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <template v-if="!recentServices">
        <Skeleton type="table" :count="5" />
      </template>
      <template v-else>
        <KTable title="Servis Terbaru" :columns="serviceColumns" :rows="recentServices"
          emptyIcon="services" emptyTitle="Belum ada servis" emptyDescription="Belum ada servis hari ini.">
          <template #cell-status="{ row }">
            <Badge :status="row.status">{{ statusLabel(row.status) }}</Badge>
          </template>
        </KTable>
      </template>
      <template v-if="!unallocatedServices">
        <Skeleton type="table" :count="5" />
      </template>
      <template v-else>
        <KTable title="Belum Dialokasi" :columns="unallocatedColumns" :rows="unallocatedServices"
          emptyIcon="services" emptyTitle="Semua sudah dialokasi" emptyDescription="Semua servis sudah memiliki teknisi.">
          <template #cell-status="{ row }">
            <Badge :status="row.status">{{ statusLabel(row.status) }}</Badge>
          </template>
        </KTable>
      </template>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import KTable from '@/Components/KTable.vue';
import Badge from '@/Components/Badge.vue';
import Skeleton from '@/Components/Skeleton.vue';
import { useFormatter } from '@/Composables/useFormatter.js';
import { statusLabel, statusBadgeVariant } from '@/Utils/statusMaps.js';

const props = defineProps({
  stats: Object,
  recentServices: Array,
  unallocatedServices: Array,
});

const { formatNumber, currentDate } = useFormatter();

const serviceColumns = [
  { key: 'customer_name', label: 'Pelanggan' },
  { key: 'device_type', label: 'Tipe' },
  { key: 'status', label: 'Status' },
];

const unallocatedColumns = [
  { key: 'customer_name', label: 'Pelanggan' },
  { key: 'device_type', label: 'Tipe' },
  { key: 'status', label: 'Status' },
];
</script>
