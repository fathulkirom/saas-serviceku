<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">Dashboard Teknisi</h2>
          <p class="text-xs mt-0.5" :style="{ color: 'var(--text-muted)' }">Halo, {{ $page.props.auth.user.name }}! Fokus pengerjaan unit. — {{ currentDate }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <Link :href="route('services.index', { status: 'dikerjakan' })" class="btn-primary text-xs">Servis Saya</Link>
          <Link :href="route('servis-tools.index', {tab: 'inden'})" class="btn-secondary text-xs">Inden Part</Link>
        </div>
      </div>
    </template>

    <Skeleton v-if="!stats" type="stat" :count="4" />
    <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
      <StatCard label="Ditugaskan" :value="stats.assigned_to_me ?? 0" color="purple" variant="glass" />
      <StatCard label="Dikerjakan" :value="stats.in_progress ?? 0" color="blue" variant="glass" />
      <StatCard label="Selesai Hari Ini" :value="stats.completed_today ?? 0" color="green" variant="glass" />
      <StatCard label="Bulan Ini" :value="stats.monthly_completed ?? 0" color="purple" variant="glass" />
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <div class="lg:col-span-2">
        <template v-if="!myServices">
          <Skeleton type="table" :count="5" />
        </template>
        <template v-else>
          <KTable title="Servis Saya" :columns="myServiceColumns" :rows="myServices"
            emptyIcon="services" emptyTitle="Belum ada servis" emptyDescription="Belum ada servis ditugaskan.">
            <template #cell-customer="{ row }">
              <div class="flex items-center gap-2.5">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center text-[10px] font-bold text-white flex-shrink-0"
                  :style="{ background: 'var(--accent-primary)' }">{{ getInitials(row.customer?.name) }}</div>
                <span class="text-sm">{{ row.customer?.name || '-' }}</span>
              </div>
            </template>
            <template #cell-status="{ row }">
              <Badge :status="row.status">{{ statusLabel(row.status) }}</Badge>
            </template>
          </KTable>
        </template>
      </div>
      <div>
        <div class="card p-5">
          <h3 class="text-sm font-bold mb-4" :style="{ color: 'var(--text-primary)' }">Ringkasan</h3>
          <div class="space-y-3">
            <ProgressBar label="Progress Hari Ini" :value="stats?.daily_progress ?? 0" color="green" />
            <div class="flex items-center justify-between text-xs" :style="{ color: 'var(--text-secondary)' }">
              <span>Komisi Bulan Ini</span>
              <span class="font-bold" :style="{ color: 'var(--accent-primary)' }">Rp {{ formatNumber(stats?.monthly_commission ?? 0) }}</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import StatCard from '@/Components/StatCard.vue';
import KTable from '@/Components/KTable.vue';
import Badge from '@/Components/Badge.vue';
import Skeleton from '@/Components/Skeleton.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import { useFormatter } from '@/Composables/useFormatter.js';
import { statusLabel, statusBadgeVariant } from '@/Utils/statusMaps.js';

const props = defineProps({
  stats: Object,
  myServices: Array,
});

const { formatNumber, currentDate, getInitials } = useFormatter();

const myServiceColumns = [
  { key: 'customer', label: 'Pelanggan' },
  { key: 'device_type', label: 'Tipe' },
  { key: 'status', label: 'Status' },
  { key: 'created_at', label: 'Masuk', format: 'date' },
];
</script>
