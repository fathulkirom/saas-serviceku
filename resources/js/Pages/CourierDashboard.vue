<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">Dashboard Kurir</h2>
          <p class="text-xs mt-0.5" :style="{ color: 'var(--text-muted)' }">Halo, {{ $page.props.auth.user.name }}! Kelola pengiriman dan pengambilan. — {{ currentDate }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <Link :href="route('services.index', { status: 'selesai' })" class="btn-primary text-xs">Pengambilan</Link>
        </div>
      </div>
    </template>

    <Skeleton v-if="!stats" type="stat" :count="4" />
    <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
      <StatCard label="Siap Diambil" :value="stats.ready_for_pickup ?? 0" color="green" variant="glass" />
      <StatCard label="Sedang Diproses" :value="stats.in_progress ?? 0" color="blue" variant="glass" />
      <StatCard label="Selesai Hari Ini" :value="stats.completed_today ?? 0" color="green" variant="glass" />
      <StatCard label="Menunggu Part" :value="stats.waiting_parts ?? 0" color="orange" variant="glass" />
    </div>

    <div class="card p-5 mb-5">
      <h3 class="text-sm font-bold mb-4" :style="{ color: 'var(--text-primary)' }">Aksi Cepat</h3>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <Link :href="route('services.index', { status: 'siap_diambil' })" class="flex flex-col items-center gap-2 px-3 py-3 rounded-xl transition-all"
          :style="{ background: 'var(--accent-light)', color: 'var(--accent-primary)' }">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
          <span class="text-xs font-semibold">Siap Diambil</span>
        </Link>
        <Link :href="route('services.index')" class="flex flex-col items-center gap-2 px-3 py-3 rounded-xl transition-all"
          :style="{ background: 'var(--bg-hover)', color: 'var(--text-secondary)' }">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <span class="text-xs font-semibold">Semua Servis</span>
        </Link>
        <Link :href="route('services.index', { status: 'selesai' })" class="flex flex-col items-center gap-2 px-3 py-3 rounded-xl transition-all"
          :style="{ background: 'var(--bg-hover)', color: 'var(--text-secondary)' }">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span class="text-xs font-semibold">Riwayat Selesai</span>
        </Link>
        <Link :href="route('services.index', { status: 'indent' })" class="flex flex-col items-center gap-2 px-3 py-3 rounded-xl transition-all"
          :style="{ background: 'var(--bg-hover)', color: 'var(--text-secondary)' }">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span class="text-xs font-semibold">Inden Part</span>
        </Link>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <template v-if="!pickupServices">
        <Skeleton type="table" :count="5" />
      </template>
      <template v-else>
        <KTable title="Pengiriman Aktif" :columns="courierPickupColumns" :rows="pickupServices"
          emptyIcon="pickup_deliveries" emptyTitle="Tidak ada servis" emptyDescription="Tidak ada servis yang perlu diantar/diambil.">
          <template #cell-status="{ row }">
            <Badge :status="row.status">{{ statusLabel(row.status) }}</Badge>
          </template>
        </KTable>
      </template>
      <template v-if="!completedServices">
        <Skeleton type="table" :count="5" />
      </template>
      <template v-else>
        <KTable title="Servis Selesai" :columns="completedColumns" :rows="completedServices"
          emptyIcon="services" emptyTitle="Belum ada servis" emptyDescription="Belum ada servis selesai hari ini." />
      </template>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import KTable from '@/Components/KTable.vue';
import Badge from '@/Components/Badge.vue';
import Skeleton from '@/Components/Skeleton.vue';
import { useFormatter } from '@/Composables/useFormatter.js';
import { statusLabel } from '@/Utils/statusMaps.js';

const props = defineProps({
  stats: Object,
  pickupServices: Array,
  completedServices: Array,
});

const page = usePage();
const { formatNumber, currentDate } = useFormatter();

const currentBranch = computed(() => page.props.currentBranch || null);

const courierPickupColumns = [
  { key: 'customer_name', label: 'Pelanggan' },
  { key: 'device_type', label: 'Device' },
  { key: 'status', label: 'Status' },
  { key: 'scheduled_date', label: 'Jadwal', format: 'date' },
];

const completedColumns = [
  { key: 'customer_name', label: 'Pelanggan' },
  { key: 'device_type', label: 'Device' },
  { key: 'completed_at', label: 'Selesai', format: 'date' },
];
</script>
