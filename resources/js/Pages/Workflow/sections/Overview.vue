<template>
  <div class="space-y-5">
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Pending</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--warning)' }">{{ stats?.pending_approvals || 0 }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">SLA Breach</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--danger)' }">{{ stats?.sla_breached || 0 }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Active Workflows</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ stats?.active_workflows || 0 }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Avg Approval</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--info)' }">{{ stats?.avg_approval_hours || 0 }}h</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Escalated</p><p class="text-xl font-bold mt-1" :style="{ color: stats?.escalated > 0 ? 'var(--danger)' : 'var(--success)' }">{{ stats?.escalated || 0 }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Success Rate</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">{{ stats?.success_rate || 0 }}%</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <SkCard title="📥 My Pending Approvals" size="sm">
        <div v-if="stats?.my_approvals?.length" class="space-y-2">
          <div v-for="a in stats.my_approvals" :key="a.id" class="flex justify-between items-center py-1 text-sm border-b" :style="{ borderColor: 'var(--border-light)' }">
            <div class="min-w-0 flex-1"><p class="font-medium truncate" :style="{ color: 'var(--text-primary)' }">{{ a.title }}</p><p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ a.module_source }} · {{ a.requester_name }}</p></div>
            <span class="text-[10px] font-bold ml-2" :style="{ color: a.sla_breach ? 'var(--danger)' : 'var(--text-muted)' }">{{ formatTimeAgo(a.requested_at) }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No pending approvals</p></div>
      </SkCard>

      <SkCard title="⚠️ SLA Alerts" size="sm">
        <div v-if="stats?.sla_alerts?.length" class="space-y-2">
          <div v-for="s in stats.sla_alerts" :key="s.id" class="flex justify-between items-center py-1 text-sm">
            <span :style="{ color: 'var(--text-primary)' }">{{ s.entity_reference }}</span>
            <span class="text-xs font-bold" :style="{ color: s.status === 'breached' ? 'var(--danger)' : 'var(--warning)' }">{{ s.remaining }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">All SLAs met</p></div>
      </SkCard>

      <SkCard title="🔄 Recent Workflows" size="sm">
        <div v-if="stats?.recent_workflows?.length" class="space-y-2">
          <div v-for="w in stats.recent_workflows" :key="w.id" class="space-y-1 py-1">
            <div class="flex justify-between text-sm"><span class="font-medium" :style="{ color: 'var(--text-primary)' }">{{ w.workflow_name }}</span><span class="text-xs font-bold" :style="{ color: 'var(--primary)' }">{{ w.progress_pct }}%</span></div>
            <div class="h-1.5 rounded overflow-hidden" :style="{ background: 'var(--bg-hover)' }"><div class="h-full rounded" :style="{ width: (w.progress_pct || 0) + '%', background: 'var(--primary)' }"></div></div>
          </div>
        </div>
      </SkCard>
    </div>
  </div>
</template>

<script setup>
import SkCard from '@/Enterprise/Components/Cards/Card.vue';
defineProps({ data: Object, stats: Object });
function formatTimeAgo(v) { if (!v) return ''; const m = Math.floor((Date.now() - new Date(v).getTime()) / 60000); return m < 60 ? m + 'm ago' : m < 1440 ? Math.floor(m / 60) + 'h ago' : Math.floor(m / 1440) + 'd ago'; }
</script>
