<template>
  <AuthenticatedLayout>
    <div class="flex flex-col min-h animate-fade-in" :style="{ background: 'var(--bg-app)' }">
      <!-- Header -->
      <div class="px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sticky top-0 z-20" :style="{ background: 'var(--bg-topbar)', borderBottom: '1px solid var(--border-color)' }">
        <div>
          <h1 class="text-xl font-extrabold tracking-tight" :style="{ color: 'var(--text-primary)' }">Dashboard Kasir</h1>
          <p class="text-sm mt-0.5" :style="{ color: 'var(--text-muted)' }">Halo, {{ $page.props.auth.user.name }}! Kelola transaksi & faktur. — {{ currentDate }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <Link :href="route('keuangan.index')" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-all shadow-sm hover:shadow-md" style="background: var(--color-success)">
            Transaksi Kasir
          </Link>
          <Link :href="route('keuangan.index', {tab: 'pengeluaran'})" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-all shadow-sm hover:shadow-md" style="background: var(--color-warning)">
            Pengeluaran
          </Link>
        </div>
      </div>

      <div class="flex-1 px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto w-full py-6 space-y-5">
        <!-- Priority Stats -->
        <Skeleton v-if="!stats" type="stat" :count="4" />
        <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-3">
          <div class="p-4 rounded-xl border animate-slide-up" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" style="background: var(--color-success-soft)">💰</div>
              <div>
                <p class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Pendapatan Hari Ini</p>
                <p class="text-2xl font-extrabold" :style="{ color: 'var(--color-success-text)' }">Rp {{ formatNumber(stats.revenue_today ?? 0) }}</p>
              </div>
            </div>
          </div>
          <div class="p-4 rounded-xl border animate-slide-up" :style="{ animationDelay: '50ms', background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" style="background: var(--color-success-soft)">✅</div>
              <div>
                <p class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Penjualan Lunas</p>
                <p class="text-2xl font-extrabold" :style="{ color: 'var(--color-success-text)' }">{{ stats.paid_sales ?? 0 }}</p>
              </div>
            </div>
          </div>
          <div class="p-4 rounded-xl border animate-slide-up" :style="{ animationDelay: '100ms', background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" style="background: var(--color-warning-soft)">📝</div>
              <div>
                <p class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Draft / Belum Lunas</p>
                <p class="text-2xl font-extrabold" :style="{ color: 'var(--color-warning-text)' }">{{ stats.draft_sales ?? 0 }}</p>
              </div>
            </div>
          </div>
          <div class="p-4 rounded-xl border animate-slide-up" :style="{ animationDelay: '150ms', background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" style="background: var(--color-info-soft)">🔧</div>
              <div>
                <p class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Servis Selesai</p>
                <p class="text-2xl font-extrabold" :style="{ color: 'var(--color-info-text)' }">{{ stats.ready_for_pickup ?? 0 }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="p-5 rounded-xl border" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
          <h3 class="text-sm font-bold mb-3" :style="{ color: 'var(--text-primary)' }">Aksi Cepat</h3>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <Link :href="route('keuangan.index')" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border transition-all hover:-translate-y-0.5" :style="{ borderColor: 'var(--color-success-soft)', background: 'var(--color-success-soft)' }">
              <span class="text-xl">➕</span>
              <span class="text-xs font-bold" :style="{ color: 'var(--color-success-text)' }">Transaksi Baru</span>
            </Link>
            <Link :href="route('keuangan.index')" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border transition-all hover:-translate-y-0.5" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)' }">
              <span class="text-xl">🧾</span>
              <span class="text-xs font-semibold" :style="{ color: 'var(--text-secondary)' }">Invoice</span>
            </Link>
            <Link :href="route('keuangan.index', {tab: 'pengeluaran'})" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border transition-all hover:-translate-y-0.5" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)' }">
              <span class="text-xl">💸</span>
              <span class="text-xs font-semibold" :style="{ color: 'var(--text-secondary)' }">Pengeluaran</span>
            </Link>
            <Link :href="route('kas.index')" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border transition-all hover:-translate-y-0.5" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)' }">
              <span class="text-xl">🕐</span>
              <span class="text-xs font-semibold" :style="{ color: 'var(--text-secondary)' }">Shift Kasir</span>
            </Link>
          </div>
        </div>

        <!-- Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
          <div class="rounded-xl border overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="p-4 border-b" :style="{ borderColor: 'var(--border-light)' }">
              <h3 class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">Penjualan Terbaru</h3>
            </div>
            <template v-if="!recentSales"><Skeleton type="table" :count="5" /></template>
            <template v-else>
              <KTable title="" :columns="salesColumns" :rows="recentSales" emptyIcon="sales" emptyTitle="Belum ada transaksi" emptyDescription="Belum ada transaksi hari ini.">
                <template #cell-status="{ row }">
                  <Badge :status="row.status === 'paid' ? 'lunas' : 'draft'">{{ row.status === 'paid' ? 'Lunas' : 'Draft' }}</Badge>
                </template>
                <template #cell-total="{ row }">
                  <span class="text-xs font-bold" :style="{ color: 'var(--text-primary)' }">Rp {{ formatNumber(row.total) }}</span>
                </template>
              </KTable>
            </template>
          </div>
          <div class="rounded-xl border overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="p-4 border-b" :style="{ borderColor: 'var(--border-light)' }">
              <h3 class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">Servis Siap Diambil</h3>
            </div>
            <template v-if="!readyServices"><Skeleton type="table" :count="5" /></template>
            <template v-else>
              <KTable title="" :columns="pickupColumns" :rows="readyServices" emptyIcon="pickup_deliveries" emptyTitle="Tidak ada servis" emptyDescription="Tidak ada servis yang siap diambil." />
            </template>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
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
