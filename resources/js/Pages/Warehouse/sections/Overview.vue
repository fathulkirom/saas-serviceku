<template>
  <div class="space-y-5">
    <!-- KPI Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Utilization</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ stats?.utilization_pct || 0 }}%</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Receiving</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">{{ stats?.receiving_today || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Picking Queue</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--warning)' }">{{ stats?.picking_queue || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Packing Queue</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--info)' }">{{ stats?.packing_queue || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Shipments Today</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ stats?.shipments_today || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Cycle Count Due</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--danger)' }">{{ stats?.cycle_count_due || 0 }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Warehouse States -->
      <SkCard title="🏬 Warehouse Status" size="sm">
        <div v-if="stats?.warehouses?.length" class="space-y-2">
          <div v-for="w in stats.warehouses" :key="w.code" class="space-y-1">
            <div class="flex justify-between text-sm">
              <span class="font-medium" :style="{ color: 'var(--text-primary)' }">{{ w.name }}</span>
              <span class="text-xs font-bold" :style="{ color: 'var(--primary)' }">{{ w.utilization_pct }}%</span>
            </div>
            <div class="h-1.5 rounded overflow-hidden" :style="{ background: 'var(--bg-hover)' }">
              <div class="h-full rounded" :style="{ width: barPct(w.utilization_pct), background: w.utilization_pct > 90 ? 'var(--danger)' : w.utilization_pct > 70 ? 'var(--warning)' : 'var(--success)' }"></div>
            </div>
          </div>
        </div>
      </SkCard>

      <!-- Pending Transfers -->
      <SkCard title="🔄 Pending Transfers" size="sm">
        <div v-if="stats?.pending_transfers?.length" class="space-y-2">
          <div v-for="t in stats.pending_transfers" :key="t.id" class="flex justify-between items-center py-1 text-sm">
            <div>
              <span :style="{ color: 'var(--text-primary)' }">{{ t.from_warehouse }} → {{ t.to_warehouse }}</span>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ t.item_count }} items · {{ formatDate(t.request_date) }}</p>
            </div>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold" :style="transferStatusStyle(t.status)">{{ t.status }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No pending transfers</p></div>
      </SkCard>

      <!-- Recent Variance -->
      <SkCard title="⚠️ Recent Variances" size="sm">
        <div v-if="stats?.recent_variances?.length" class="space-y-2">
          <div v-for="v in stats.recent_variances" :key="v.id" class="flex justify-between items-center py-1 text-sm">
            <span :style="{ color: 'var(--text-primary)' }">{{ v.item_name }}</span>
            <span class="font-bold" :style="{ color: v.variance > 0 ? 'var(--success)' : 'var(--danger)' }">
              {{ v.variance > 0 ? '+' : '' }}{{ v.variance }}
            </span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No variances</p></div>
      </SkCard>
    </div>

    <!-- Throughput Chart -->
    <SkCard title="📊 Warehouse Throughput (7 Days)" size="md" v-if="stats?.throughput?.length">
      <div class="space-y-1">
        <div v-for="d in stats.throughput" :key="d.date" class="flex items-center gap-3 text-xs">
          <span class="w-20 text-right" :style="{ color: 'var(--text-muted)' }">{{ formatDateShort(d.date) }}</span>
          <div class="flex-1 flex h-4 rounded overflow-hidden" :style="{ background: 'var(--bg-hover)' }">
            <div :style="{ width: inBar(d), background: 'var(--success)', minWidth: d.in_qty > 0 ? '2px' : '0' }"></div>
            <div :style="{ width: outBar(d), background: 'var(--danger)', minWidth: d.out_qty > 0 ? '2px' : '0' }"></div>
          </div>
          <span class="w-16 text-right font-bold" :style="{ color: 'var(--primary)' }">{{ (d.in_qty || 0) + (d.out_qty || 0) }}</span>
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

function formatDate(v) {
  if (!v) return '-';
  return new Date(v).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}
function formatDateShort(v) {
  if (!v) return '-';
  return new Date(v).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}
function barPct(v) { return (v || 0) + '%'; }
function transferStatusStyle(s) {
  if (s === 'approved') return { background: 'var(--success-soft)', color: 'var(--success)' };
  if (s === 'requested') return { background: 'var(--warning-soft)', color: 'var(--warning)' };
  if (s === 'in_transit') return { background: 'var(--info-soft)', color: 'var(--info)' };
  return { background: 'var(--bg-hover)', color: 'var(--text-muted)' };
}
const maxThroughput = 1;
function inBar(d) {
  const max = Math.max(maxThroughput, (d?.in_qty || 0) + (d?.out_qty || 0));
  return ((d?.in_qty || 0) / max * 100).toFixed(1) + '%';
}
function outBar(d) {
  const max = Math.max(maxThroughput, (d?.in_qty || 0) + (d?.out_qty || 0));
  return ((d?.out_qty || 0) / max * 100).toFixed(1) + '%';
}
</script>
