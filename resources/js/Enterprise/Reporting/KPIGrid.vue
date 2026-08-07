<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
    <div v-for="metric in metrics" :key="metric.id"
      class="rounded-2xl border p-5 relative overflow-hidden group transition-all hover:-translate-y-0.5 hover:shadow-lg"
      :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)' }">

      <!-- Decorative accent -->
      <div class="absolute top-0 left-0 right-0 h-1" :style="{ background: metric.color || 'var(--primary)' }"></div>
      <div class="absolute top-0 right-0 w-24 h-24 rounded-full blur-3xl opacity-20 -mr-8 -mt-8"
        :style="{ background: metric.color || 'var(--primary)' }"></div>

      <div class="relative z-10">
        <div class="flex items-center justify-between mb-3">
          <div class="w-10 h-10 rounded-xl flex items-center justify-center"
            :style="{ background: `${metric.color || 'var(--primary)'}15`, color: metric.color || 'var(--primary)' }">
            <span class="text-lg">{{ metric.icon || '📊' }}</span>
          </div>
          <span class="text-[10px] font-bold uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">
            {{ metric.label }}
          </span>
        </div>

        <div>
          <h3 class="text-2xl font-extrabold tracking-tight" :style="{ color: 'var(--text-primary)' }">
            <span v-if="metric.format === 'currency'">Rp {{ formatNumber(metric.value) }}</span>
            <span v-else-if="metric.format === 'percent'">{{ formatNumber(metric.value) }}%</span>
            <span v-else>{{ formatNumber(metric.value) }}</span>
          </h3>

          <!-- Trend -->
          <div v-if="metric.trend !== undefined" class="flex items-center gap-1 mt-2">
            <span class="text-xs font-semibold" :class="metric.trend >= 0 ? 'text-emerald-600' : 'text-red-600'">
              {{ metric.trend >= 0 ? '↑' : '↓' }} {{ Math.abs(metric.trend) }}%
            </span>
            <span class="text-[10px]" :style="{ color: 'var(--text-muted)' }">vs sebelumnya</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  metrics: { type: Array, default: () => [] },
});

function formatNumber(n) {
  if (n === null || n === undefined) return '-';
  return new Intl.NumberFormat('id-ID').format(Number(n));
}
</script>
