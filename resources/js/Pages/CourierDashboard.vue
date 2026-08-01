<template>
  <AuthenticatedLayout>
    <div class="flex flex-col min-h-[calc(100vh-64px)] bg-zinc-50">
      <!-- Header CRM Style -->
      <div class="px-6 sm:px-8 py-6 bg-white border-b border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-20">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center border border-orange-100">
                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Dashboard Kurir</h1>
                <p class="text-sm text-zinc-500 font-medium mt-0.5">Halo, {{ $page.props.auth.user.name }}! Kelola pengiriman dan pengambilan. — {{ currentDate }}</p>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
          <Link :href="route('services.index', { status: 'selesai' })" class="inline-flex items-center gap-1.5 px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
            Pengambilan
          </Link>
        </div>
      </div>

      <div class="flex-1 p-6 sm:p-8 max-w-[1400px] mx-auto w-full">
        <Skeleton v-if="!stats" type="stat" :count="4" />
        <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <StatCard label="Siap Diambil" :value="stats.ready_for_pickup ?? 0" color="green" variant="glass" />
          <StatCard label="Sedang Diproses" :value="stats.in_progress ?? 0" color="blue" variant="glass" />
          <StatCard label="Selesai Hari Ini" :value="stats.completed_today ?? 0" color="green" variant="glass" />
          <StatCard label="Menunggu Part" :value="stats.waiting_parts ?? 0" color="orange" variant="glass" />
        </div>

        <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm mb-6">
          <h3 class="text-sm font-bold text-zinc-900 mb-4">Aksi Cepat</h3>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <Link :href="route('services.index', { status: 'siap_diambil' })" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border border-emerald-100 bg-emerald-50/50 hover:bg-emerald-50 text-emerald-700 transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
              <span class="text-xs font-semibold">Siap Diambil</span>
            </Link>
            <Link :href="route('services.index')" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border border-zinc-200 bg-white hover:bg-zinc-50 text-zinc-700 transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
              <span class="text-xs font-semibold">Semua Servis</span>
            </Link>
            <Link :href="route('services.index', { status: 'selesai' })" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border border-zinc-200 bg-white hover:bg-zinc-50 text-zinc-700 transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span class="text-xs font-semibold">Riwayat Selesai</span>
            </Link>
            <Link :href="route('services.index', { status: 'indent' })" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border border-zinc-200 bg-white hover:bg-zinc-50 text-zinc-700 transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
              <span class="text-xs font-semibold">Inden Part</span>
            </Link>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
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
          </div>
          <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
            <template v-if="!completedServices">
              <Skeleton type="table" :count="5" />
            </template>
            <template v-else>
              <KTable title="Servis Selesai" :columns="completedColumns" :rows="completedServices"
                emptyIcon="services" emptyTitle="Belum ada servis" emptyDescription="Belum ada servis selesai hari ini." />
            </template>
          </div>
        </div>
      </div>
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
