<template>
  <div class="space-y-5">
    <!-- KPI Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Active Projects</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ stats?.active_projects || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Total Budget</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--info)' }">Rp {{ formatNumber(stats?.total_budget) }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Tasks Due Today</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--warning)' }">{{ stats?.tasks_due_today || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Overdue Tasks</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--danger)' }">{{ stats?.tasks_overdue || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Open Issues</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--danger)' }">{{ stats?.open_issues || 0 }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Project Progress -->
      <SkCard title="📊 Project Progress" size="sm">
        <div v-if="stats?.projects?.length" class="space-y-2">
          <div v-for="p in stats.projects" :key="p.id" class="space-y-1">
            <div class="flex justify-between items-center text-sm">
              <span class="truncate" :style="{ color: 'var(--text-primary)' }">{{ p.project_name }}</span>
              <span class="text-xs font-bold ml-2" :style="{ color: 'var(--text-primary)' }">{{ p.progress_pct || 0 }}%</span>
            </div>
            <div class="h-1.5 rounded overflow-hidden" :style="{ background: 'var(--bg-hover)' }">
              <div class="h-full rounded" :style="{ width: (p.progress_pct || 0) + '%', background: progressColor(p.progress_pct) }"></div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No active projects</p></div>
      </SkCard>

      <!-- My Tasks -->
      <SkCard title="✅ My Tasks" size="sm">
        <div v-if="stats?.my_tasks?.length" class="space-y-2">
          <div v-for="t in stats.my_tasks" :key="t.id" class="flex items-center justify-between py-1 text-sm">
            <div class="flex items-center gap-2 min-w-0 flex-1">
              <span class="w-2 h-2 rounded-full flex-shrink-0" :style="{ background: priorityColor(t.priority) }"></span>
              <span class="truncate" :style="{ color: 'var(--text-primary)' }">{{ t.task_name }}</span>
            </div>
            <span class="text-[10px] ml-2" :style="{ color: t.due_today ? 'var(--danger)' : 'var(--text-muted)' }">{{ formatDate(t.due_date) }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No pending tasks</p></div>
      </SkCard>

      <!-- Upcoming Milestones -->
      <SkCard title="🎯 Upcoming Milestones" size="sm">
        <div v-if="stats?.milestones?.length" class="space-y-2">
          <div v-for="m in stats.milestones" :key="m.id" class="flex justify-between items-center py-1 text-sm">
            <div class="min-w-0 flex-1">
              <p class="font-medium truncate" :style="{ color: 'var(--text-primary)' }">{{ m.milestone_name }}</p>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ m.project_name }}</p>
            </div>
            <span class="text-xs font-bold ml-2" :style="{ color: m.near ? 'var(--warning)' : 'var(--text-muted)' }">{{ formatDate(m.deadline) }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No upcoming milestones</p></div>
      </SkCard>
    </div>

    <!-- Budget vs Actual -->
    <SkCard title="💰 Budget vs Actual" size="md" v-if="stats?.budget_summary?.length">
      <div class="space-y-2">
        <div v-for="b in stats.budget_summary" :key="b.id" class="flex items-center gap-3 text-xs">
          <span class="w-24 truncate text-right" :style="{ color: 'var(--text-muted)' }">{{ b.project_name }}</span>
          <div class="flex-1 h-4 rounded flex overflow-hidden" :style="{ background: 'var(--bg-hover)' }">
            <div :style="{ width: budgetBar(b.actual, b.budget), background: 'var(--danger)', minWidth: b.actual > 0 ? '2px' : '0' }" :title="'Actual: Rp ' + formatNumber(b.actual)"></div>
            <div :style="{ width: budgetRemaining(b.actual, b.budget), background: 'var(--bg-hover)', minWidth: '0' }" :title="'Remaining: Rp ' + formatNumber(b.budget - b.actual)"></div>
          </div>
          <span class="w-20 text-right font-bold" :style="{ color: b.actual > b.budget ? 'var(--danger)' : 'var(--success)' }">Rp {{ formatNumber(b.actual) }}</span>
        </div>
      </div>
    </SkCard>

    <!-- Recent Activity -->
    <SkCard title="🕐 Recent Activity" size="md">
      <div v-if="stats?.recent_activity?.length" class="space-y-1">
        <div v-for="a in stats.recent_activity" :key="a.id" class="flex justify-between items-center py-1.5 text-sm"
          :style="{ borderBottom: '1px solid var(--border-light)' }">
          <span :style="{ color: 'var(--text-primary)' }">{{ a.message }}</span>
          <span class="text-xs" :style="{ color: 'var(--text-muted)' }">{{ formatDate(a.created_at) }}</span>
        </div>
      </div>
      <div v-else class="text-center py-4"><p class="sk-caption">No recent activity</p></div>
    </SkCard>
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
function formatDate(v) {
  if (!v) return '-';
  return new Date(v).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}
function progressColor(p) {
  if (p >= 80) return 'var(--success)';
  if (p >= 50) return 'var(--primary)';
  if (p >= 25) return 'var(--warning)';
  return 'var(--danger)';
}
function priorityColor(p) {
  return p === 'critical' ? 'var(--danger)' : p === 'high' ? 'var(--warning)' : p === 'medium' ? 'var(--info)' : 'var(--text-muted)';
}
function budgetBar(actual, budget) {
  const max = Math.max(budget || 1, actual || 0);
  return ((actual || 0) / max * 100).toFixed(1) + '%';
}
function budgetRemaining(actual, budget) {
  const max = Math.max(budget || 1, actual || 0);
  return Math.max(0, ((budget || 0) - (actual || 0)) / max * 100).toFixed(1) + '%';
}
</script>
