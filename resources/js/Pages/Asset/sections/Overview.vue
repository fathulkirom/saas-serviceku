<template>
  <div class="space-y-5">
    <!-- KPI Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Total Assets</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ stats?.total_assets || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Total Value</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">Rp {{ formatNumber(stats?.total_value) }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Maintenance Due</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--warning)' }">{{ stats?.maintenance_due || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Overdue</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--danger)' }">{{ stats?.maintenance_overdue || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Depreciation MTD</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">Rp {{ formatNumber(stats?.depreciation_mtd) }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Upcoming Maintenance -->
      <SkCard title="🔧 Upcoming Maintenance" size="sm">
        <div v-if="stats?.upcoming_maintenance?.length" class="space-y-2">
          <div v-for="m in stats.upcoming_maintenance" :key="m.id" class="flex justify-between items-center py-1">
            <div class="min-w-0 flex-1">
              <p class="text-sm font-medium truncate" :style="{ color: 'var(--text-primary)' }">{{ m.asset_name }}</p>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ m.maintenance_type }} · {{ formatDate(m.scheduled_date) }}</p>
            </div>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold ml-2" :style="priorityStyle(m.priority)">{{ m.priority }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No upcoming maintenance</p></div>
      </SkCard>

      <!-- Expiring Soon -->
      <SkCard title="⏰ Expiring Soon" size="sm">
        <div v-if="stats?.expiring?.length" class="space-y-2">
          <div v-for="e in stats.expiring" :key="e.id" class="flex justify-between items-center py-1">
            <div>
              <p class="text-sm font-medium" :style="{ color: 'var(--text-primary)' }">{{ e.asset_name }}</p>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ e.type === 'warranty' ? '🛡️' : '🔒' }} {{ e.type_label }} · {{ e.days_remaining }}d left</p>
            </div>
            <span class="text-xs font-bold" :style="{ color: e.days_remaining <= 7 ? 'var(--danger)' : 'var(--warning)' }">{{ formatDate(e.expiry_date) }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No items expiring soon</p></div>
      </SkCard>

      <!-- Asset by Category -->
      <SkCard title="📊 Assets by Category" size="sm">
        <div v-if="stats?.category_distribution?.length" class="space-y-1.5">
          <div v-for="c in stats.category_distribution" :key="c.category" class="flex items-center gap-2 text-xs">
            <span class="w-16 truncate" :style="{ color: 'var(--text-muted)' }">{{ c.category }}</span>
            <div class="flex-1 h-3 rounded overflow-hidden" :style="{ background: 'var(--bg-hover)' }">
              <div class="h-full rounded" :style="{ width: catBarPct(c.count), background: 'var(--primary)', minWidth: c.count > 0 ? '2px' : '0' }"></div>
            </div>
            <span class="w-6 text-right font-bold" :style="{ color: 'var(--text-primary)' }">{{ c.count }}</span>
          </div>
        </div>
      </SkCard>
    </div>

    <!-- Recent Movements -->
    <SkCard title="🔄 Recent Asset Movements" size="md">
      <div v-if="stats?.recent_movements?.length" class="space-y-1">
        <div v-for="m in stats.recent_movements" :key="m.id" class="flex justify-between items-center py-1.5 text-sm"
          :style="{ borderBottom: '1px solid var(--border-light)' }">
          <div class="flex items-center gap-3">
            <span class="text-[10px] font-mono" :style="{ color: 'var(--text-muted)' }">{{ m.asset_code }}</span>
            <span :style="{ color: 'var(--text-primary)' }">{{ m.asset_name }}</span>
            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold" :style="movementTypeStyle(m.movement_type)">{{ m.movement_type }}</span>
          </div>
          <div class="flex items-center gap-4">
            <span class="text-xs" :style="{ color: 'var(--text-muted)' }">{{ m.from_location }} → {{ m.to_location }}</span>
            <span class="text-xs" :style="{ color: 'var(--text-muted)' }">{{ formatDate(m.movement_date) }}</span>
          </div>
        </div>
      </div>
      <div v-else class="text-center py-4"><p class="sk-caption">No recent movements</p></div>
    </SkCard>

    <!-- Alerts -->
    <div v-if="alerts?.length" class="space-y-2">
      <div v-for="a in alerts" :key="a.id" class="flex items-center gap-3 p-3 rounded-lg text-sm"
        :style="{ background: alertBg(a.severity), color: 'var(--text-primary)', borderLeft: `4px solid ${alertColor(a.severity)}` }">
        <span>{{ alertIcon(a.type) }}</span>
        <span class="flex-1">{{ a.message }}</span>
        <span class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ formatDate(a.created_at) }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import SkCard from '@/Enterprise/Components/Cards/Card.vue';

defineProps({
  data: { type: Object, default: () => ({}) },
  stats: { type: Object, default: () => ({}) },
  alerts: { type: Array, default: () => [] },
});

const maxCat = Math.max(...(props?.stats?.category_distribution?.map(c => c.count) || [0]), 1);
function catBarPct(c) { return ((c || 0) / maxCat * 100).toFixed(1) + '%'; }
function formatNumber(v) {
  if (v == null) return '0';
  return Number(v).toLocaleString('id-ID');
}
function formatDate(v) {
  if (!v) return '-';
  return new Date(v).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}
function priorityStyle(p) {
  const map = { critical: { background: 'var(--danger-soft)', color: 'var(--danger)' }, high: { background: 'var(--warning-soft)', color: 'var(--warning)' }, medium: { background: 'var(--info-soft)', color: 'var(--info)' }, low: { background: 'var(--bg-hover)', color: 'var(--text-muted)' } };
  return map[p] || map.low;
}
function movementTypeStyle(t) {
  const map = { transfer: 'var(--info-soft)', assignment: 'var(--success-soft)', disposal: 'var(--danger-soft)', maintenance: 'var(--warning-soft)' };
  return { background: map[t] || 'var(--bg-hover)', color: 'var(--text-primary)' };
}
function alertBg(s) { return s === 'critical' ? 'var(--danger-soft)' : s === 'warning' ? 'var(--warning-soft)' : 'var(--info-soft)'; }
function alertColor(s) { return s === 'critical' ? 'var(--danger)' : s === 'warning' ? 'var(--warning)' : 'var(--info)'; }
function alertIcon(t) { return t === 'maintenance' ? '🔧' : t === 'warranty' ? '🛡️' : t === 'insurance' ? '🔒' : t === 'calibration' ? '📐' : '📌'; }
</script>
