<template>
  <div class="space-y-5">
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Today's Jobs</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ stats?.today_jobs || 0 }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Completed Today</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">{{ stats?.completed_today || 0 }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Waiting Parts</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--warning)' }">{{ stats?.waiting_parts || 0 }}</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Avg Repair Time</p><p class="text-xl font-bold mt-1" :style="{ color: 'var(--info)' }">{{ stats?.avg_repair_hours || 0 }}h</p></div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }"><p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Performance</p><p class="text-xl font-bold mt-1" :style="{ color: stats?.performance_score >= 80 ? 'var(--success)' : 'var(--warning)' }">{{ stats?.performance_score || 0 }}%</p></div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <SkCard title="📋 Today's Jobs" size="sm">
        <div v-if="stats?.today_jobs_list?.length" class="space-y-2">
          <div v-for="j in stats.today_jobs_list" :key="j.id" class="flex justify-between items-center py-1 text-sm border-b" :style="{ borderColor: 'var(--border-light)' }">
            <div class="min-w-0 flex-1"><span class="font-bold" :style="{ color: 'var(--text-primary)' }">{{ j.service_number }}</span><p class="text-xs truncate" :style="{ color: 'var(--text-muted)' }">{{ j.customer_name }} · {{ j.device_name }}</p></div>
            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold ml-2" :style="priorityStyle(j.priority)">{{ j.priority }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No jobs today</p></div>
      </SkCard>

      <SkCard title="🔔 Notifications" size="sm">
        <div v-if="stats?.notifications?.length" class="space-y-1">
          <div v-for="n in stats.notifications" :key="n.id" class="flex items-center gap-2 py-1 text-sm">
            <span class="w-2 h-2 rounded-full" :style="{ background: n.severity === 'critical' ? 'var(--danger)' : 'var(--primary)' }"></span>
            <span :style="{ color: 'var(--text-primary)' }">{{ n.message }}</span>
            <span class="text-[10px] ml-auto" :style="{ color: 'var(--text-muted)' }">{{ formatTimeAgo(n.created_at) }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No notifications</p></div>
      </SkCard>
    </div>
  </div>
</template>

<script setup>
import SkCard from '@/Enterprise/Components/Cards/Card.vue';
defineProps({ data: Object, stats: Object });
function formatTimeAgo(v) { if (!v) return ''; const m = Math.floor((Date.now() - new Date(v).getTime()) / 60000); return m < 60 ? m + 'm ago' : Math.floor(m / 60) + 'h ago'; }
function priorityStyle(p) { return p === 'critical' ? { background: 'var(--danger-soft)', color: 'var(--danger)' } : p === 'high' ? { background: 'var(--warning-soft)', color: 'var(--warning)' } : { background: 'var(--info-soft)', color: 'var(--info)' }; }
</script>
