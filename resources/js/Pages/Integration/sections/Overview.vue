<template>
  <div class="space-y-5">
    <!-- KPI Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">API Health</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">{{ stats?.api_health || 0 }}%</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Webhook Queue</p>
        <p class="text-xl font-bold mt-1" :style="{ color: stats?.webhook_queue > 10 ? 'var(--warning)' : 'var(--success)' }">{{ stats?.webhook_queue || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Active Connectors</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ stats?.active_connectors || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">API Calls (24h)</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--info)' }">{{ formatNumber(stats?.api_calls_24h) }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Integration Errors</p>
        <p class="text-xl font-bold mt-1" :style="{ color: stats?.errors_24h > 0 ? 'var(--danger)' : 'var(--success)' }">{{ stats?.errors_24h || 0 }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Connector Health -->
      <SkCard title="🔌 Connector Health" size="sm">
        <div v-if="stats?.connectors?.length" class="space-y-2">
          <div v-for="c in stats.connectors" :key="c.id" class="flex justify-between items-center py-1 text-sm">
            <div>
              <span :style="{ color: 'var(--text-primary)' }">{{ c.connector_name }}</span>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ c.connector_type }} · {{ c.provider }}</p>
            </div>
            <span class="w-2 h-2 rounded-full" :style="{ background: c.health_status === 'healthy' ? 'var(--success)' : c.health_status === 'degraded' ? 'var(--warning)' : 'var(--danger)' }"></span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No connectors configured</p></div>
      </SkCard>

      <!-- Recent Webhook Activity -->
      <SkCard title="🪝 Webhook Activity" size="sm">
        <div v-if="stats?.webhook_activity?.length" class="space-y-1">
          <div v-for="w in stats.webhook_activity" :key="w.id" class="flex justify-between items-center py-1 text-sm">
            <span class="truncate" :style="{ color: 'var(--text-primary)' }">{{ w.webhook_name }}</span>
            <span class="text-xs font-bold ml-2" :style="{ color: w.status === 'success' ? 'var(--success)' : 'var(--danger)' }">{{ w.status === 'success' ? '✅' : '❌' }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No webhook activity</p></div>
      </SkCard>

      <!-- API Usage Chart -->
      <SkCard title="📊 API Usage (7 Days)" size="sm">
        <div v-if="stats?.api_usage_trend?.length" class="space-y-1">
          <div v-for="d in stats.api_usage_trend" :key="d.date" class="flex items-center gap-2 text-xs">
            <span class="w-12 text-right" :style="{ color: 'var(--text-muted)' }">{{ formatDateShort(d.date) }}</span>
            <div class="flex-1 h-3 rounded overflow-hidden" :style="{ background: 'var(--bg-hover)' }">
              <div class="h-full rounded" :style="{ width: barPct(d.count), background: 'var(--primary)', minWidth: d.count > 0 ? '2px' : '0' }"></div>
            </div>
            <span class="w-8 text-right font-bold" :style="{ color: 'var(--text-primary)' }">{{ d.count }}</span>
          </div>
        </div>
      </SkCard>
    </div>

    <!-- Quick Links to Developer Portal -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
      <button class="p-3 rounded-lg text-sm font-medium text-center" :style="{ background: 'var(--bg-hover)', color: 'var(--primary)' }">🌐 Swagger UI</button>
      <button class="p-3 rounded-lg text-sm font-medium text-center" :style="{ background: 'var(--bg-hover)', color: 'var(--primary)' }">🪝 Webhook Tester</button>
      <button class="p-3 rounded-lg text-sm font-medium text-center" :style="{ background: 'var(--bg-hover)', color: 'var(--primary)' }">🔑 Token Generator</button>
      <button class="p-3 rounded-lg text-sm font-medium text-center" :style="{ background: 'var(--bg-hover)', color: 'var(--primary)' }">📋 API Explorer</button>
    </div>
  </div>
</template>

<script setup>
import SkCard from '@/Enterprise/Components/Cards/Card.vue';

defineProps({
  data: { type: Object, default: () => ({}) },
  stats: { type: Object, default: () => ({}) },
});

function formatNumber(v) {
  if (v == null) return '0';
  return Number(v).toLocaleString('id-ID');
}
function formatDateShort(v) {
  if (!v) return '-';
  return new Date(v).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}
function barPct(c) {
  const max = Math.max(...(stats?.api_usage_trend?.map(d => d.count) || [0]), 1);
  return ((c || 0) / max * 100).toFixed(1) + '%';
}
</script>
