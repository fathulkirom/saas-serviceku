<template>
  <div class="space-y-5">
    <!-- KPI Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Active Orders</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ stats?.active_orders || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Efficiency</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">{{ stats?.efficiency_pct || 0 }}%</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">OEE</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--info)' }">{{ stats?.oee_pct || 0 }}%</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Material Shortage</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--danger)' }">{{ stats?.shortages || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">QC Failed Today</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--danger)' }">{{ stats?.qc_failed || 0 }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Active Production -->
      <SkCard title="🏭 Active Production" size="sm">
        <div v-if="stats?.active_production?.length" class="space-y-2">
          <div v-for="p in stats.active_production" :key="p.id" class="space-y-1">
            <div class="flex justify-between text-sm">
              <span class="truncate font-medium" :style="{ color: 'var(--text-primary)' }">{{ p.product_name }}</span>
              <span class="text-xs font-bold ml-2" :style="{ color: 'var(--primary)' }">{{ p.produced_qty }}/{{ p.planned_qty }}</span>
            </div>
            <div class="h-1.5 rounded overflow-hidden" :style="{ background: 'var(--bg-hover)' }">
              <div class="h-full rounded" :style="{ width: progressPct(p), background: 'var(--primary)' }"></div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No active production</p></div>
      </SkCard>

      <!-- Machine Status -->
      <SkCard title="⚙️ Machine Status" size="sm">
        <div v-if="stats?.machines?.length" class="space-y-2">
          <div v-for="m in stats.machines" :key="m.code" class="flex justify-between items-center py-1 text-sm">
            <span :style="{ color: 'var(--text-primary)' }">{{ m.name }}</span>
            <div class="flex items-center gap-2">
              <span class="text-xs" :style="{ color: 'var(--text-muted)' }">{{ m.utilization_pct || 0 }}%</span>
              <span class="w-2 h-2 rounded-full" :style="{ background: m.status === 'running' ? 'var(--success)' : m.status === 'idle' ? 'var(--warning)' : 'var(--danger)' }"></span>
            </div>
          </div>
        </div>
      </SkCard>

      <!-- Recent QC Results -->
      <SkCard title="✅ Recent QC" size="sm">
        <div v-if="stats?.recent_qc?.length" class="space-y-2">
          <div v-for="q in stats.recent_qc" :key="q.id" class="flex justify-between items-center py-1 text-sm">
            <span class="truncate" :style="{ color: 'var(--text-primary)' }">{{ q.production_number }}</span>
            <span class="text-xs font-bold ml-2" :style="{ color: q.result === 'passed' ? 'var(--success)' : 'var(--danger)' }">{{ q.result === 'passed' ? '✅' : '❌' }} {{ q.passed_qty }}/{{ q.inspected_qty }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No QC records</p></div>
      </SkCard>
    </div>

    <!-- Scrap Rate -->
    <SkCard title="🗑️ Scrap Rate (7 Days)" size="md" v-if="stats?.scrap_trend?.length">
      <div class="space-y-1">
        <div v-for="s in stats.scrap_trend" :key="s.date" class="flex items-center gap-3 text-xs">
          <span class="w-20 text-right" :style="{ color: 'var(--text-muted)' }">{{ formatDateShort(s.date) }}</span>
          <div class="flex-1 h-4 rounded overflow-hidden" :style="{ background: 'var(--bg-hover)' }">
            <div class="h-full rounded" :style="{ width: scrapBar(s), background: scrapColor(s), minWidth: s.rate > 0 ? '2px' : '0' }"></div>
          </div>
          <span class="w-12 text-right font-bold" :style="{ color: scrapColor(s) }">{{ s.rate }}%</span>
        </div>
      </div>
    </SkCard>
  </div>
</template>

<script setup>
import SkCard from '@/Enterprise/Components/Cards/Card.vue';

defineProps({
  data: { type: Object, default: () => ({}) },
  stats: { type: Object, default: () => ({}) },
});

function formatDateShort(v) {
  if (!v) return '-';
  return new Date(v).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}
function progressPct(p) {
  return ((p.produced_qty || 0) / (p.planned_qty || 1) * 100).toFixed(0) + '%';
}
function scrapBar(s) {
  return Math.min((s.rate || 0) * 8, 100) + '%';
}
function scrapColor(s) {
  const r = s.rate || 0;
  return r > 5 ? 'var(--danger)' : r > 2 ? 'var(--warning)' : 'var(--success)';
}
</script>
