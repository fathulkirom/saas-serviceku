<template>
  <div class="space-y-5">
    <!-- KPI Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Platform Health</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">{{ stats?.health_score || 0 }}%</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Active Tenants</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ stats?.active_tenants || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">MRR</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">Rp {{ formatNumber(stats?.mrr) }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Security Alerts</p>
        <p class="text-xl font-bold mt-1" :style="{ color: stats?.security_alerts > 0 ? 'var(--danger)' : 'var(--success)' }">{{ stats?.security_alerts || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Failed Jobs</p>
        <p class="text-xl font-bold mt-1" :style="{ color: stats?.failed_jobs > 0 ? 'var(--danger)' : 'var(--success)' }">{{ stats?.failed_jobs || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Backup Status</p>
        <p class="text-xl font-bold mt-1" :style="{ color: stats?.backup_ok ? 'var(--success)' : 'var(--danger)' }">{{ stats?.backup_ok ? '✅' : '❌' }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Tenant Growth -->
      <SkCard title="🏢 Tenant Growth" size="sm">
        <div v-if="stats?.tenant_growth?.length" class="space-y-1">
          <div v-for="t in stats.tenant_growth" :key="t.month" class="flex items-center gap-2 text-xs">
            <span class="w-16 text-right" :style="{ color: 'var(--text-muted)' }">{{ t.month }}</span>
            <div class="flex-1 h-3 rounded overflow-hidden" :style="{ background: 'var(--bg-hover)' }">
              <div class="h-full rounded" :style="{ width: barPct(t.count), background: 'var(--primary)', minWidth: t.count > 0 ? '2px' : '0' }"></div>
            </div>
            <span class="w-6 text-right font-bold" :style="{ color: 'var(--text-primary)' }">{{ t.count }}</span>
          </div>
        </div>
      </SkCard>

      <!-- System Health -->
      <SkCard title="📡 System Health" size="sm">
        <div v-if="stats?.system_health?.length" class="space-y-2">
          <div v-for="s in stats.system_health" :key="s.component" class="flex justify-between items-center py-1 text-sm">
            <span :style="{ color: 'var(--text-primary)' }">{{ s.label }}</span>
            <span :style="{ color: s.status === 'healthy' ? 'var(--success)' : s.status === 'warning' ? 'var(--warning)' : 'var(--danger)' }">
              {{ s.status === 'healthy' ? '✅' : s.status === 'warning' ? '⚠️' : '❌' }} {{ s.value }}
            </span>
          </div>
        </div>
      </SkCard>

      <!-- Recent Alerts -->
      <SkCard title="🚨 Recent Alerts" size="sm">
        <div v-if="stats?.recent_alerts?.length" class="space-y-2">
          <div v-for="a in stats.recent_alerts" :key="a.id" class="py-1 text-sm"
            :style="{ borderBottom: '1px solid var(--border-light)' }">
            <div class="flex items-center gap-2">
              <span :style="{ color: a.severity === 'critical' ? 'var(--danger)' : 'var(--warning)' }">{{ a.severity === 'critical' ? '❌' : '⚠️' }}</span>
              <span class="font-medium" :style="{ color: 'var(--text-primary)' }">{{ a.message }}</span>
            </div>
            <p class="text-[10px] ml-5" :style="{ color: 'var(--text-muted)' }">{{ formatTimeAgo(a.created_at) }}</p>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No active alerts</p></div>
      </SkCard>
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
function barPct(c) {
  const max = Math.max(...(stats?.tenant_growth?.map(d => d.count) || [0]), 1);
  return ((c || 0) / max * 100).toFixed(1) + '%';
}
function formatTimeAgo(v) {
  if (!v) return '';
  const diff = Date.now() - new Date(v).getTime();
  const mins = Math.floor(diff / 60000);
  if (mins < 60) return mins + 'm ago';
  const hours = Math.floor(mins / 60);
  if (hours < 24) return hours + 'h ago';
  return Math.floor(hours / 24) + 'd ago';
}
</script>
