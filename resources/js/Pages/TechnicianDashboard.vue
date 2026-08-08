<template>
  <AuthenticatedLayout>
    <div class="flex flex-col min-h animate-fade-in" :style="{ background: 'var(--bg-app)' }">
      <!-- Header -->
      <div class="px-4 sm:px-6 lg:px-8 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-3 sticky top-0 z-20" :style="{ background: 'var(--bg-topbar)', borderBottom: '1px solid var(--border-color)' }">
        <div>
          <h1 class="text-xl font-extrabold tracking-tight" :style="{ color: 'var(--text-primary)' }">Dashboard Teknisi</h1>
          <p class="text-sm mt-0.5" :style="{ color: 'var(--text-muted)' }">Halo, {{ $page.props.auth.user.name }}! Fokus pengerjaan unit. — {{ currentDate }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
          <Link :href="route('services.index', { status: 'dikerjakan' })" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-all shadow-sm hover:shadow-md" style="background: var(--color-primary)">
            Servis Saya
          </Link>
          <Link :href="route('servis-tools.index', {tab: 'inden'})" class="inline-flex items-center gap-1.5 px-5 py-2.5 rounded-xl text-sm font-bold text-white transition-all shadow-sm hover:shadow-md" style="background: var(--color-info)">
            Inden Part
          </Link>
        </div>
      </div>

      <div class="flex-1 px-4 sm:px-6 lg:px-8 max-w-[1400px] mx-auto w-full py-6 space-y-5">
        <!-- Priority Stats -->
        <Skeleton v-if="!stats" type="stat" :count="4" />
        <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-3">
          <div class="p-4 rounded-xl border animate-slide-up" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" style="background: var(--color-primary-soft)">📋</div>
              <div>
                <p class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Ditugaskan</p>
                <p class="text-2xl font-extrabold" :style="{ color: 'var(--text-primary)' }">{{ stats.assigned_to_me ?? 0 }}</p>
              </div>
            </div>
          </div>
          <div class="p-4 rounded-xl border animate-slide-up" :style="{ animationDelay: '50ms', background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" style="background: var(--color-info-soft)">🔧</div>
              <div>
                <p class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Dikerjakan</p>
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
              <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg" style="background: var(--color-warning-soft)">📊</div>
              <div>
                <p class="text-xs font-semibold" :style="{ color: 'var(--text-muted)' }">Bulan Ini</p>
                <p class="text-2xl font-extrabold" :style="{ color: 'var(--color-warning-text)' }">{{ stats.monthly_completed ?? 0 }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Main Content: Table + Summary -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
          <div class="lg:col-span-2 rounded-xl border overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <div class="p-4 border-b" :style="{ borderColor: 'var(--border-light)' }">
              <h3 class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">Servis Saya</h3>
            </div>
            <template v-if="!myServices"><Skeleton type="table" :count="5" /></template>
            <template v-else>
              <KTable title="" :columns="myServiceColumns" :rows="myServices" emptyIcon="services" emptyTitle="Belum ada servis" emptyDescription="Belum ada servis ditugaskan.">
                <template #cell-customer="{ row }">
                  <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center text-xs font-bold flex-shrink-0" style="background: var(--color-primary-soft); color: var(--color-primary-text)">
                      {{ getInitials(row.customer?.name) }}
                    </div>
                    <span class="text-sm font-medium" :style="{ color: 'var(--text-primary)' }">{{ row.customer?.name || '-' }}</span>
                  </div>
                </template>
                <template #cell-status="{ row }">
                  <Badge :status="row.status">{{ statusLabel(row.status) }}</Badge>
                </template>
              </KTable>
            </template>
          </div>
          <div class="p-5 rounded-xl border" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <h3 class="text-sm font-bold mb-4" :style="{ color: 'var(--text-primary)' }">Ringkasan</h3>
            <div class="space-y-4">
              <ProgressBar label="Progress Hari Ini" :value="stats?.daily_progress ?? 0" color="green" />
              <div class="flex items-center justify-between text-sm pt-4" :style="{ borderTop: '1px solid var(--border-light)' }">
                <span :style="{ color: 'var(--text-muted)' }">Komisi Bulan Ini</span>
                <span class="font-bold" :style="{ color: 'var(--color-success-text)' }">Rp {{ formatNumber(stats?.monthly_commission ?? 0) }}</span>
              </div>
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
import KTable from '@/Components/KTable.vue';
import Badge from '@/Components/Badge.vue';
import Skeleton from '@/Components/Skeleton.vue';
import ProgressBar from '@/Components/ProgressBar.vue';
import { useFormatter } from '@/Composables/useFormatter.js';
import { statusLabel } from '@/Utils/statusMaps.js';

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
