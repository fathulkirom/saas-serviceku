<template>
  <AuthenticatedLayout>
    <template #header>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">Dashboard Kasir</h2>
          <p class="text-xs mt-0.5" :style="{ color: 'var(--text-muted)' }">Halo, {{ $page.props.auth.user.name }}! Kelola transaksi & faktur. — {{ currentDate }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <Link :href="route('keuangan.index')" class="btn-primary text-xs">Transaksi Kasir</Link>
          <Link :href="route('keuangan.index', {tab: 'pengeluaran'})" class="btn-secondary text-xs">Pengeluaran</Link>
          <Link :href="route('kas.index')" class="btn-secondary text-xs">Shift Kasir</Link>
        </div>
      </div>
    </template>

    <Skeleton v-if="!stats" type="stat" :count="4" />
    <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-5">
      <StatCard label="Pendapatan Hari Ini" color="green" variant="glass">
        <template #value>Rp {{ formatNumber(stats.revenue_today ?? 0) }}</template>
      </StatCard>
      <StatCard label="Penjualan Lunas" :value="stats.paid_sales ?? 0" color="green" variant="glass" />
      <StatCard label="Draft / Belum Lunas" :value="stats.draft_sales ?? 0" color="orange" variant="glass" />
      <StatCard label="Servis Selesai" :value="stats.ready_for_pickup ?? 0" color="blue" variant="glass" />
    </div>

    <div class="card p-5 mb-5">
      <h3 class="text-sm font-bold mb-4" :style="{ color: 'var(--text-primary)' }">Aksi Cepat</h3>
      <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <Link :href="route('keuangan.index')" class="flex flex-col items-center gap-2 px-3 py-3 rounded-xl transition-all"
          :style="{ background: 'var(--accent-light)', color: 'var(--accent-primary)' }">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
          <span class="text-xs font-semibold">Transaksi Baru</span>
        </Link>
        <Link :href="route('keuangan.index')" class="flex flex-col items-center gap-2 px-3 py-3 rounded-xl transition-all"
          :style="{ background: 'var(--bg-hover)', color: 'var(--text-secondary)' }">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
          <span class="text-xs font-semibold">Invoice</span>
        </Link>
        <Link :href="route('keuangan.index', {tab: 'pengeluaran'})" class="flex flex-col items-center gap-2 px-3 py-3 rounded-xl transition-all"
          :style="{ background: 'var(--bg-hover)', color: 'var(--text-secondary)' }">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"/></svg>
          <span class="text-xs font-semibold">Pengeluaran</span>
        </Link>
        <Link :href="route('kas.index')" class="flex flex-col items-center gap-2 px-3 py-3 rounded-xl transition-all"
          :style="{ background: 'var(--bg-hover)', color: 'var(--text-secondary)' }">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
          <span class="text-xs font-semibold">Shift Kasir</span>
        </Link>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <template v-if="!recentSales">
        <Skeleton type="table" :count="5" />
      </template>
      <template v-else>
        <KTable title="Penjualan Terbaru" :columns="salesColumns" :rows="recentSales"
          emptyIcon="sales" emptyTitle="Belum ada transaksi" emptyDescription="Belum ada transaksi hari ini.">
          <template #cell-status="{ row }">
            <Badge :status="row.status === 'paid' ? 'lunas' : 'draft'">{{ row.status === 'paid' ? 'Lunas' : 'Draft' }}</Badge>
          </template>
          <template #cell-total="{ row }">
            <span class="text-xs font-bold" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(row.total) }}</span>
          </template>
        </KTable>
      </template>
      <template v-if="!readyServices">
        <Skeleton type="table" :count="5" />
      </template>
      <template v-else>
        <KTable title="Servis Siap Diambil" :columns="pickupColumns" :rows="readyServices"
          emptyIcon="pickup_deliveries" emptyTitle="Tidak ada servis" emptyDescription="Tidak ada servis yang siap diambil." />
      </template>
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
import { useFormatter } from '@/Composables/useFormatter.js';

const props = defineProps({
  stats: Object,
  recentSales: Array,
  readyServices: Array,
  cashRegisterOpen: Boolean,
});

const { formatNumber, currentDate } = useFormatter();

const salesColumns = [
  { key: 'id', label: 'ID', bold: true },
  { key: 'customer', label: 'Pelanggan' },
  { key: 'total', label: 'Total', align: 'right' },
  { key: 'status', label: 'Status' },
];

const pickupColumns = [
  { key: 'customer_name', label: 'Pelanggan' },
  { key: 'device', label: 'Device' },
  { key: 'status', label: 'Status' },
];
</script>
