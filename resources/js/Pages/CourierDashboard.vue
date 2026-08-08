<template>
  <AuthenticatedLayout>
    <div class="flex flex-col min-h animate-fade-in" :style="{ background: 'var(--bg-app)' }">
      <!-- Header -->
      <div class="px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sticky top-0 z-20" :style="{ background: 'var(--bg-topbar)', borderBottom: '1px solid var(--border-color)' }">
        <div>
          <h1 class="text-xl font-extrabold tracking-tight" :style="{ color: 'var(--text-primary)' }">Dashboard Kurir</h1>
          <p class="text-sm mt-0.5" :style="{ color: 'var(--text-muted)' }">Halo, {{ $page.props.auth.user.name }}! Kelola pengiriman dan pengambilan. — {{ currentDate }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <Link :href="route('services.index', { status: 'selesai' })" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-all shadow-sm hover:shadow-md" style="background: var(--color-warning)">
            Pengambilan
          </Link>
        </div>
      </div>

      <div class="flex-1 px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto w-full py-6 space-y-5">
        <!-- Priority Stats -->
        <Skeleton v-if="!stats" type="stat" :count="4" />
        <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-3">
          <div class="p-4 rounded-xl border animate-slide-up" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" style="background: var(--color-success-soft)">📦</div>
              <div>
                <p class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Siap Diambil</p>
                <p class="text-2xl font-extrabold" :style="{ color: 'var(--color-success-text)' }">{{ stats.ready_for_pickup ?? 0 }}</p>
              </div>
            </div>
          </div>
          <div class="p-4 rounded-xl border animate-slide-up" :style="{ animationDelay: '50ms', background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" style="background: var(--color-info-soft)">🔧</div>
              <div>
                <p class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Sedang Diproses</p>
                <p class="text-2xl font-extrabold" :style="{ color: 'var(--color-info-text)' }">{{ stats.in_progress ?? 0 }}</p>
              </div>
            </div>
          </div>
          <div class="p-4 rounded-xl border animate-slide-up" :style="{ animationDelay: '100ms', background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" style="background: var(--color-success-soft)">✅</div>
              <div>
                <p class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Selesai Hari Ini</p>
                <p class="text-2xl font-extrabold" :style="{ color: 'var(--color-success-text)' }">{{ stats.completed_today ?? 0 }}</p>
              </div>
            </div>
          </div>
          <div class="p-4 rounded-xl border animate-slide-up" :style="{ animationDelay: '150ms', background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" style="background: var(--color-warning-soft)">⏳</div>
              <div>
                <p class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Menunggu Part</p>
                <p class="text-2xl font-extrabold" :style="{ color: 'var(--color-warning-text)' }">{{ stats.waiting_parts ?? 0 }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="p-5 rounded-xl border" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
          <h3 class="text-sm font-bold mb-3" :style="{ color: 'var(--text-primary)' }">Aksi Cepat</h3>
          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <Link :href="route('services.index', { status: 'siap_diambil' })" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border transition-all hover:-translate-y-0.5" :style="{ borderColor: 'var(--color-success-soft)', background: 'var(--color-success-soft)' }">
              <span class="text-xl">📦</span>
              <span class="text-xs font-bold" :style="{ color: 'var(--color-success-text)' }">Siap Diambil</span>
            </Link>
            <Link :href="route('services.index')" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border transition-all hover:-translate-y-0.5" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)' }">
              <span class="text-xl">🔍</span>
              <span class="text-xs font-semibold" :style="{ color: 'var(--text-secondary)' }">Semua Servis</span>
            </Link>
            <Link :href="route('services.index', { status: 'selesai' })" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border transition-all hover:-translate-y-0.5" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)' }">
              <span class="text-xl">📋</span>
              <span class="text-xs font-semibold" :style="{ color: 'var(--text-secondary)' }">Riwayat Selesai</span>
            </Link>
            <Link :href="route('services.index', { status: 'indent' })" class="flex flex-col items-center justify-center gap-2 px-3 py-4 rounded-xl border transition-all hover:-translate-y-0.5" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)' }">
              <span class="text-xl">🔧</span>
              <span class="text-xs font-semibold" :style="{ color: 'var(--text-secondary)' }">Inden Part</span>
            </Link>
          </div>
        </div>

        <!-- Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
          <div class="rounded-xl border overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="p-4 border-b" :style="{ borderColor: 'var(--border-light)' }">
              <h3 class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">Pengiriman Aktif</h3>
            </div>
            <template v-if="!pickupServices"><Skeleton type="table" :count="5" /></template>
            <template v-else>
              <KTable title="" :columns="courierPickupColumns" :rows="pickupServices" emptyIcon="pickup_deliveries" emptyTitle="Tidak ada servis" emptyDescription="Tidak ada servis yang perlu diantar/diambil.">
                <template #cell-status="{ row }">
                  <Badge :status="row.status">{{ statusLabel(row.status) }}</Badge>
                </template>
              </KTable>
            </template>
          </div>
          <div class="rounded-xl border overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="p-4 border-b" :style="{ borderColor: 'var(--border-light)' }">
              <h3 class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">Servis Selesai</h3>
            </div>
            <template v-if="!completedServices"><Skeleton type="table" :count="5" /></template>
            <template v-else>
              <KTable title="" :columns="completedColumns" :rows="completedServices" emptyIcon="services" emptyTitle="Belum ada servis" emptyDescription="Belum ada servis selesai hari ini." />
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
