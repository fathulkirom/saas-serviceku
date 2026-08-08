<template>
  <AuthenticatedLayout>
    <div class="flex flex-col min-h-screen" :style="{ background: 'var(--bg-app)' }">
      <div class="px-4 sm:px-6 lg:px-8 max-w-5xl mx-auto w-full py-6 space-y-6">
        <div>
          <h1 class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">📊 Analytics</h1>
          <p class="text-sm mt-0.5" :style="{ color: 'var(--text-muted)' }">Ringkasan performa bisnis multi-cabang.</p>
        </div>

        <!-- Month Selector -->
        <div class="flex items-center gap-3">
          <KInput type="month" :model-value="month" @change="changeMonth" class="rounded-lg text-sm w-48" />
        </div>

        <!-- Service Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
          <div class="p-4 rounded-xl border" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <p class="text-xs" :style="{ color: 'var(--text-muted)' }">Total Servis</p>
            <p class="text-xl font-bold" :style="{ color: 'var(--text-primary)' }">{{ serviceStats?.total || 0 }}</p>
          </div>
          <div class="p-4 rounded-xl border" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <p class="text-xs" :style="{ color: 'var(--text-muted)' }">Selesai</p>
            <p class="text-xl font-bold sk-text-success">{{ serviceStats?.completed || 0 }}</p>
          </div>
          <div class="p-4 rounded-xl border" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <p class="text-xs" :style="{ color: 'var(--text-muted)' }">Aktif</p>
            <p class="text-xl font-bold sk-text-info">{{ serviceStats?.active || 0 }}</p>
          </div>
          <div class="p-4 rounded-xl border" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
            <p class="text-xs" :style="{ color: 'var(--text-muted)' }">Cancel</p>
            <p class="text-xl font-bold sk-text-danger">{{ serviceStats?.cancelled || 0 }}</p>
          </div>
        </div>

        <!-- Monthly Trend -->
        <div class="rounded-xl border p-5" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
          <h3 class="font-semibold text-sm mb-3" :style="{ color: 'var(--text-primary)' }">📈 Trend 6 Bulan</h3>
          <div class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead>
                <tr class="text-xs" :style="{ color: 'var(--text-muted)' }">
                  <th class="text-left py-1">Bulan</th>
                  <th class="text-right py-1">Revenue</th>
                  <th class="text-right py-1">Pengeluaran</th>
                  <th class="text-right py-1">Servis</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="t in trends" :key="t.month" class="border-t" :style="{ borderColor: 'var(--border-light)' }">
                  <td class="py-2 font-medium" :style="{ color: 'var(--text-primary)' }">{{ t.month }}</td>
                  <td class="py-2 text-right sk-text-success">Rp {{ formatNumber(t.revenue) }}</td>
                  <td class="py-2 text-right sk-text-danger">Rp {{ formatNumber(t.expenses) }}</td>
                  <td class="py-2 text-right" :style="{ color: 'var(--text-secondary)' }">{{ t.services }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Branch Revenue -->
        <div v-if="branchRevenue?.length" class="rounded-xl border overflow-hidden" :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }">
          <div class="p-4 border-b" :style="{ borderColor: 'var(--border-color)' }">
            <h3 class="font-semibold text-sm" :style="{ color: 'var(--text-primary)' }">🏢 Revenue per Cabang</h3>
          </div>
          <div class="divide-y" :style="{ borderColor: 'var(--border-light)' }">
            <div v-for="b in branchRevenue" :key="b.branch_id" class="px-4 py-3 flex items-center justify-between">
              <span class="text-sm font-semibold" :style="{ color: 'var(--text-primary)' }">{{ b.branch?.name || 'Cabang ' + b.branch_id }}</span>
              <div class="text-right">
                <p class="text-sm font-bold sk-text-success">Rp {{ formatNumber(b.revenue) }}</p>
                <p class="text-xs" :style="{ color: 'var(--text-muted)' }">{{ b.transactions }} transaksi</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>

<script setup>
import { router } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import KInput from '@/Components/KInput.vue';
import { useFormatter } from '@/Composables/useFormatter.js';

const props = defineProps({
  month: String, branchRevenue: Array, serviceStats: Object, trends: Array,
});
const { formatNumber } = useFormatter();

const changeMonth = (e) => router.get(route('analytics.index'), { month: e.target.value }, { preserveState: true });
</script>
