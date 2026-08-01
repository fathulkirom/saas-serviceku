<template>
  <AuthenticatedLayout>
    <div class="flex flex-col min-h-[calc(100vh-64px)] bg-zinc-50">
      <!-- Header CRM Style -->
      <div class="px-6 sm:px-8 py-6 bg-white border-b border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-20">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center border border-indigo-100">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm14 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Dashboard CS</h1>
                <p class="text-sm text-zinc-500 font-medium mt-0.5">Halo, {{ $page.props.auth.user.name }}! Kelola penerimaan unit & alokasi teknisi. — {{ currentDate }}</p>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
          <Link :href="route('services.create')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
            + Unit Masuk Baru
          </Link>
          <Link :href="route('customers.index')" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-50 text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
            Data Member
          </Link>
        </div>
      </div>

      <div class="flex-1 p-6 sm:p-8 max-w-[1400px] mx-auto w-full">
        <Skeleton v-if="!stats" type="stat" :count="4" />
        <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <StatCard label="Servis Hari Ini" :value="stats.services_today ?? 0" color="purple" variant="glass" />
          <StatCard label="Pelanggan Baru" :value="stats.new_customers_today ?? 0" color="green" variant="glass" />
          <StatCard label="Menunggu Alokasi" :value="stats.pending_allocation ?? 0" color="orange" variant="glass" />
          <StatCard label="Servis Aktif" :value="stats.active_services ?? 0" color="blue" variant="glass" />
        </div>

        <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm mb-6">
          <h3 class="text-sm font-bold text-zinc-900 mb-4">Aksi Cepat</h3>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <Link :href="route('services.create')" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border border-indigo-100 bg-indigo-50/50 hover:bg-indigo-50 text-indigo-700 transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
              <span class="text-xs font-semibold">Servis Baru</span>
            </Link>
            <Link :href="route('customers.create')" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border border-zinc-200 bg-white hover:bg-zinc-50 text-zinc-700 transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
              <span class="text-xs font-semibold">Pelanggan Baru</span>
            </Link>
            <Link :href="route('keuangan.index')" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border border-zinc-200 bg-white hover:bg-zinc-50 text-zinc-700 transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/></svg>
              <span class="text-xs font-semibold">Transaksi</span>
            </Link>
            <Link :href="route('customers.index')" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border border-zinc-200 bg-white hover:bg-zinc-50 text-zinc-700 transition-all">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857"/></svg>
              <span class="text-xs font-semibold">Data Member</span>
            </Link>
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
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
          </div>
          <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
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
        </div>
      </div>
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
