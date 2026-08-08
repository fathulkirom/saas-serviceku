<template>
  <AuthenticatedLayout>
    <div class="flex flex-col min-h animate-fade-in-[calc(100vh-64px)] bg-zinc-50">
      <!-- Header CRM Style -->
      <div class="px-6 sm:px-8 py-6 bg-white border-b border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-4 sticky top-0 z-20">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-indigo-50 rounded-xl flex items-center justify-center border border-indigo-100">
                <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">Dashboard Teknisi</h1>
                <p class="text-sm text-zinc-500 font-medium mt-0.5">Halo, {{ $page.props.auth.user.name }}! Fokus pengerjaan unit. — {{ currentDate }}</p>
            </div>
        </div>
        
        <!-- Action Buttons -->
        <div class="flex flex-wrap items-center gap-2">
          <Link :href="route('services.index', { status: 'dikerjakan' })" class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
            Servis Saya
          </Link>
          <Link :href="route('servis-tools.index', {tab: 'inden'})" class="inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-zinc-200 text-zinc-700 hover:bg-zinc-50 text-sm font-semibold rounded-xl shadow-sm hover:shadow-md transition-all">
            Inden Part
          </Link>
        </div>
      </div>

      <div class="flex-1 p-6 sm:p-8 max-w-[1400px] mx-auto w-full">
        <Skeleton v-if="!stats" type="stat" :count="4" />
        <div v-else class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
          <StatCard label="Ditugaskan" :value="stats.assigned_to_me ?? 0" color="purple" variant="glass" />
          <StatCard label="Dikerjakan" :value="stats.in_progress ?? 0" color="blue" variant="glass" />
          <StatCard label="Selesai Hari Ini" :value="stats.completed_today ?? 0" color="green" variant="glass" />
          <StatCard label="Bulan Ini" :value="stats.monthly_completed ?? 0" color="purple" variant="glass" />
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2">
            <template v-if="!myServices">
              <Skeleton type="table" :count="5" />
            </template>
            <template v-else>
              <div class="bg-white rounded-2xl border border-zinc-200 shadow-sm overflow-hidden">
                <KTable title="Servis Saya" :columns="myServiceColumns" :rows="myServices"
                  emptyIcon="services" emptyTitle="Belum ada servis" emptyDescription="Belum ada servis ditugaskan.">
                  <template #cell-customer="{ row }">
                    <div class="flex items-center gap-2.5">
                      <div class="w-8 h-8 rounded-xl bg-indigo-50 border border-indigo-100 flex items-center justify-center text-xs font-bold text-indigo-700 flex-shrink-0">
                        {{ getInitials(row.customer?.name) }}
                      </div>
                      <span class="text-sm font-medium text-zinc-900">{{ row.customer?.name || '-' }}</span>
                    </div>
                  </template>
                  <template #cell-status="{ row }">
                    <Badge :status="row.status">{{ statusLabel(row.status) }}</Badge>
                  </template>
                </KTable>
              </div>
            </template>
          </div>
          <div>
            <div class="bg-white p-6 rounded-2xl border border-zinc-200 shadow-sm">
              <h3 class="text-sm font-bold text-zinc-900 mb-4">Ringkasan</h3>
              <div class="space-y-4">
                <ProgressBar label="Progress Hari Ini" :value="stats?.daily_progress ?? 0" color="green" />
                <div class="flex items-center justify-between text-sm text-zinc-600 pt-4 border-t border-zinc-100">
                  <span>Komisi Bulan Ini</span>
                  <span class="font-bold text-indigo-600">Rp {{ formatNumber(stats?.monthly_commission ?? 0) }}</span>
                </div>
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
