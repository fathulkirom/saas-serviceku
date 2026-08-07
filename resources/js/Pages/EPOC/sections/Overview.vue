<template>
  <div class="space-y-5">
    <!-- KPI Row 1 — Platform Health -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
      <div class="p-3 rounded-xl text-center cursor-pointer hover:opacity-80 transition" :style="{ background: 'var(--bg-hover)' }" @click="$emit('navigate','platform_health')">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Platform Health</p>
        <p class="text-xl font-bold mt-1" :style="{ color: scoreColor(stats?.health_score) }">{{ stats?.health_score || 0 }}%</p>
      </div>
      <div class="p-3 rounded-xl text-center cursor-pointer hover:opacity-80 transition" :style="{ background: 'var(--bg-hover)' }" @click="$emit('navigate','app_performance')">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">CPU</p>
        <p class="text-xl font-bold mt-1" :style="{ color: thresholdColor(stats?.cpu_pct, 70, 90) }">{{ stats?.cpu_pct || 0 }}%</p>
      </div>
      <div class="p-3 rounded-xl text-center cursor-pointer hover:opacity-80 transition" :style="{ background: 'var(--bg-hover)' }" @click="$emit('navigate','app_performance')">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Memory</p>
        <p class="text-xl font-bold mt-1" :style="{ color: thresholdColor(stats?.memory_pct, 70, 90) }">{{ stats?.memory_pct || 0 }}%</p>
      </div>
      <div class="p-3 rounded-xl text-center cursor-pointer hover:opacity-80 transition" :style="{ background: 'var(--bg-hover)' }" @click="$emit('navigate','infrastructure')">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Storage</p>
        <p class="text-xl font-bold mt-1" :style="{ color: thresholdColor(stats?.storage_pct, 80, 95) }">{{ stats?.storage_pct || 0 }}%</p>
      </div>
      <div class="p-3 rounded-xl text-center cursor-pointer hover:opacity-80 transition" :style="{ background: 'var(--bg-hover)' }" @click="$emit('navigate','database')">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Slow Queries</p>
        <p class="text-xl font-bold mt-1" :style="{ color: stats?.slow_queries > 0 ? 'var(--warning)' : 'var(--success)' }">{{ stats?.slow_queries || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center cursor-pointer hover:opacity-80 transition" :style="{ background: 'var(--bg-hover)' }" @click="$emit('navigate','api_monitoring')">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Error Rate</p>
        <p class="text-xl font-bold mt-1" :style="{ color: thresholdColor(stats?.error_rate_pct, 1, 5) }">{{ stats?.error_rate_pct || 0 }}%</p>
      </div>
    </div>

    <!-- KPI Row 2 — Queue & Jobs -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
      <div class="p-3 rounded-xl text-center cursor-pointer hover:opacity-80 transition" :style="{ background: 'var(--bg-hover)' }" @click="$emit('navigate','queue_jobs')">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Queue Jobs</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ stats?.queue_jobs || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center cursor-pointer hover:opacity-80 transition" :style="{ background: 'var(--bg-hover)' }" @click="$emit('navigate','queue_jobs')">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Failed Jobs</p>
        <p class="text-xl font-bold mt-1" :style="{ color: stats?.failed_jobs > 0 ? 'var(--danger)' : 'var(--success)' }">{{ stats?.failed_jobs || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center cursor-pointer hover:opacity-80 transition" :style="{ background: 'var(--bg-hover)' }" @click="$emit('navigate','integration_mon')">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Integration</p>
        <p class="text-xl font-bold mt-1" :style="{ color: scoreColor(stats?.integration_health) }">{{ stats?.integration_health || 0 }}%</p>
      </div>
      <div class="p-3 rounded-xl text-center cursor-pointer hover:opacity-80 transition" :style="{ background: 'var(--bg-hover)' }" @click="$emit('navigate','cache_session')">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Cache Hit</p>
        <p class="text-xl font-bold mt-1" :style="{ color: scoreColor(stats?.cache_hit_ratio) }">{{ stats?.cache_hit_ratio || 0 }}%</p>
      </div>
      <div class="p-3 rounded-xl text-center cursor-pointer hover:opacity-80 transition" :style="{ background: 'var(--bg-hover)' }" @click="$emit('navigate','security_ops')">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Sec Alerts</p>
        <p class="text-xl font-bold mt-1" :style="{ color: stats?.security_alerts > 0 ? 'var(--danger)' : 'var(--success)' }">{{ stats?.security_alerts || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center cursor-pointer hover:opacity-80 transition" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Uptime</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">{{ stats?.uptime_display || '99.9%' }}</p>
      </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Service Health Map -->
      <SkCard title="💚 Service Health Map" size="sm">
        <div v-if="stats?.service_health?.length" class="space-y-1.5">
          <div v-for="s in stats.service_health" :key="s.name" class="flex justify-between items-center py-1 text-sm border-b" :style="{ borderColor: 'var(--border-light)' }">
            <span :style="{ color: 'var(--text-primary)' }">{{ s.label }}</span>
            <span class="text-xs font-bold px-2 py-0.5 rounded-full" :style="healthBadgeStyle(s.status)">{{ s.status }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">All services healthy</p></div>
      </SkCard>

      <!-- Recent Deployments -->
      <SkCard title="🚀 Recent Deployments" size="sm">
        <div v-if="stats?.deployments?.length" class="space-y-2">
          <div v-for="d in stats.deployments" :key="d.id" class="flex justify-between items-center py-1 text-sm border-b" :style="{ borderColor: 'var(--border-light)' }">
            <div class="min-w-0 flex-1">
              <p class="font-medium" :style="{ color: 'var(--text-primary)' }">v{{ d.version }} <span class="text-[10px]" :style="{ color: 'var(--text-muted)' }">({{ d.environment }})</span></p>
            </div>
            <span class="text-[10px] font-bold ml-2" :style="{ color: d.status === 'success' ? 'var(--success)' : 'var(--danger)' }">{{ d.status }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No recent deployments</p></div>
      </SkCard>

      <!-- AI Operations Insights -->
      <SkCard title="🤖 AI Operations Insights" size="sm">
        <div v-if="stats?.ai_insights?.length" class="space-y-2">
          <div v-for="ai in stats.ai_insights" :key="ai.id" class="flex items-start gap-2 py-1.5 text-sm border-b" :style="{ borderColor: 'var(--border-light)' }">
            <span class="text-lg flex-shrink-0">{{ ai.icon || '💡' }}</span>
            <div class="min-w-0">
              <p :style="{ color: 'var(--text-primary)' }">{{ ai.insight }}</p>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">Confidence: {{ ai.confidence }}% · {{ formatTimeAgo(ai.created_at) }}</p>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No AI insights yet</p></div>
      </SkCard>
    </div>

    <!-- Second Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <!-- API Performance -->
      <SkCard title="🔌 Top APIs by Response Time" size="sm">
        <div v-if="stats?.top_apis?.length" class="space-y-2">
          <div v-for="api in stats.top_apis" :key="api.endpoint" class="flex justify-between items-center py-1 text-sm border-b" :style="{ borderColor: 'var(--border-light)' }">
            <div class="min-w-0 flex-1">
              <p class="font-medium text-xs truncate" :style="{ color: 'var(--text-primary)' }">{{ api.method }} {{ api.endpoint }}</p>
            </div>
            <span class="text-xs font-bold ml-2" :style="{ color: api.response_time_ms > 500 ? 'var(--warning)' : 'var(--success)' }">{{ api.response_time_ms }}ms</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No API data</p></div>
      </SkCard>

      <!-- Backup Status -->
      <SkCard title="💿 Backup Status" size="sm">
        <div v-if="stats?.backups" class="grid grid-cols-3 gap-3">
          <div class="p-3 rounded-lg text-center" :style="{ background: 'var(--bg-hover)' }">
            <p class="text-lg font-bold" :style="{ color: 'var(--success)' }">{{ stats.backups.completed || 0 }}</p>
            <p class="text-[10px] uppercase" :style="{ color: 'var(--text-muted)' }">Completed</p>
          </div>
          <div class="p-3 rounded-lg text-center" :style="{ background: 'var(--bg-hover)' }">
            <p class="text-lg font-bold" :style="{ color: 'var(--primary)' }">{{ stats.backups.in_progress || 0 }}</p>
            <p class="text-[10px] uppercase" :style="{ color: 'var(--text-muted)' }">In Progress</p>
          </div>
          <div class="p-3 rounded-lg text-center" :style="{ background: 'var(--bg-hover)' }">
            <p class="text-lg font-bold" :style="{ color: 'var(--danger)' }">{{ stats.backups.failed || 0 }}</p>
            <p class="text-[10px] uppercase" :style="{ color: 'var(--text-muted)' }">Failed</p>
          </div>
        </div>
      </SkCard>
    </div>
  </div>
</template>

<script setup>
import SkCard from '@/Enterprise/Components/Cards/Card.vue';

defineProps({ data: Object, stats: Object });
defineEmits(['navigate']);

function scoreColor(v) {
  if (!v && v !== 0) return 'var(--text-muted)';
  if (v >= 80) return 'var(--success)';
  if (v >= 60) return 'var(--warning)';
  return 'var(--danger)';
}

function thresholdColor(v, warn, crit) {
  if (!v && v !== 0) return 'var(--text-muted)';
  if (v >= crit) return 'var(--danger)';
  if (v >= warn) return 'var(--warning)';
  return 'var(--success)';
}

function healthBadgeStyle(status) {
  const map = {
    healthy: { background: '#22c55e20', color: 'var(--success)' },
    degraded: { background: '#f9731620', color: 'var(--warning)' },
    down: { background: '#dc262620', color: 'var(--danger)' },
  };
  return map[status] || { background: 'var(--bg-hover)', color: 'var(--text-muted)' };
}

function formatTimeAgo(v) {
  if (!v) return '';
  const m = Math.floor((Date.now() - new Date(v).getTime()) / 60000);
  return m < 60 ? m + 'm ago' : m < 1440 ? Math.floor(m / 60) + 'h ago' : Math.floor(m / 1440) + 'd ago';
}
</script>
