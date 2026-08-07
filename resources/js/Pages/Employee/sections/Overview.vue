<template>
  <div class="space-y-5">
    <!-- KPI Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Total Employees</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ stats?.total_employees || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Present Today</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">{{ stats?.present_today || 0 }}</p>
        <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ stats?.attendance_rate || 0 }}%</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">On Leave</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--warning)' }">{{ stats?.on_leave || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Late Today</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--danger)' }">{{ stats?.late_today || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Payroll Pending</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ stats?.payroll_pending || 0 }}</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Attendance Today -->
      <SkCard title="🕐 Attendance Today" size="sm">
        <div v-if="stats?.attendance_list?.length" class="space-y-1">
          <div v-for="a in stats.attendance_list" :key="a.id" class="flex justify-between items-center py-1 text-sm">
            <div class="flex items-center gap-2">
              <span class="w-2 h-2 rounded-full" :style="{ background: a.status === 'present' ? 'var(--success)' : a.status === 'late' ? 'var(--warning)' : 'var(--danger)' }"></span>
              <span :style="{ color: 'var(--text-primary)' }">{{ a.employee_name }}</span>
            </div>
            <span class="text-xs" :style="{ color: 'var(--text-muted)' }">{{ a.clock_in || '-' }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No attendance data</p></div>
      </SkCard>

      <!-- Upcoming Leave -->
      <SkCard title="🏖️ Upcoming Leave" size="sm">
        <div v-if="stats?.upcoming_leave?.length" class="space-y-2">
          <div v-for="l in stats.upcoming_leave" :key="l.id" class="flex justify-between items-center py-1">
            <div>
              <p class="text-sm font-medium" :style="{ color: 'var(--text-primary)' }">{{ l.employee_name }}</p>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ l.leave_type }} · {{ formatDate(l.start_date) }} → {{ formatDate(l.end_date) }}</p>
            </div>
            <span class="px-2 py-0.5 rounded text-[10px] font-bold" :style="leaveStatusStyle(l.status)">{{ l.status }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No upcoming leave</p></div>
      </SkCard>

      <!-- Birthday This Month -->
      <SkCard title="🎂 Birthday This Month" size="sm">
        <div v-if="stats?.birthdays?.length" class="space-y-2">
          <div v-for="b in stats.birthdays" :key="b.id" class="flex items-center gap-3 py-1">
            <span class="text-lg">{{ b.gender === 'female' ? '👩' : '👨' }}</span>
            <div>
              <p class="text-sm font-medium" :style="{ color: 'var(--text-primary)' }">{{ b.full_name }}</p>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ b.position }} · {{ formatDateShort(b.birth_date) }}</p>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No birthdays this month</p></div>
      </SkCard>
    </div>

    <!-- Department Distribution -->
    <SkCard title="👥 Department Distribution" size="md" v-if="stats?.dept_distribution?.length">
      <div class="space-y-2">
        <div v-for="d in stats.dept_distribution" :key="d.department" class="flex items-center gap-3">
          <span class="text-xs w-24 text-right" :style="{ color: 'var(--text-muted)' }">{{ d.department }}</span>
          <div class="flex-1 h-5 rounded overflow-hidden" :style="{ background: 'var(--bg-hover)' }">
            <div class="h-full rounded" :style="{ width: barPct(d.count), background: 'var(--primary)', minWidth: d.count > 0 ? '2px' : '0' }"></div>
          </div>
          <span class="text-xs font-bold w-8 text-right" :style="{ color: 'var(--text-primary)' }">{{ d.count }}</span>
        </div>
      </div>
    </SkCard>

    <!-- Recent New Hires -->
    <SkCard title="🆕 Recent New Hires" size="md">
      <div v-if="stats?.recent_hires?.length" class="space-y-1">
        <div v-for="e in stats.recent_hires" :key="e.id" class="flex justify-between items-center py-1.5 text-sm"
          :style="{ borderBottom: '1px solid var(--border-light)' }">
          <div class="flex items-center gap-3">
            <span class="text-[10px] font-mono" :style="{ color: 'var(--text-muted)' }">{{ e.employee_id }}</span>
            <span :style="{ color: 'var(--text-primary)' }">{{ e.full_name }}</span>
            <span class="text-[10px] px-1.5 py-0.5 rounded font-bold" :style="{ background: 'var(--success-soft)', color: 'var(--success)' }">{{ e.position }}</span>
          </div>
          <span class="text-xs" :style="{ color: 'var(--text-muted)' }">{{ formatDate(e.join_date) }}</span>
        </div>
      </div>
      <div v-else class="text-center py-4"><p class="sk-caption">No recent hires</p></div>
    </SkCard>

    <!-- HR Alerts -->
    <div v-if="hrAlerts?.length" class="space-y-2">
      <div v-for="a in hrAlerts" :key="a.id" class="flex items-center gap-3 p-3 rounded-lg text-sm"
        :style="{ background: alertBg(a), color: 'var(--text-primary)', borderLeft: `4px solid ${alertColor(a)}` }">
        <span>{{ alertIcon(a) }}</span>
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
  hrAlerts: { type: Array, default: () => [] },
});

const maxDept = Math.max(...(props?.stats?.dept_distribution?.map(d => d.count) || [0]), 1);
function barPct(c) { return ((c || 0) / maxDept * 100).toFixed(1) + '%'; }
function formatDate(v) {
  if (!v) return '-';
  return new Date(v).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
}
function formatDateShort(v) {
  if (!v) return '-';
  return new Date(v).toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
}
function leaveStatusStyle(s) {
  if (s === 'approved') return { background: 'var(--success-soft)', color: 'var(--success)' };
  if (s === 'pending') return { background: 'var(--warning-soft)', color: 'var(--warning)' };
  return { background: 'var(--danger-soft)', color: 'var(--danger)' };
}
function alertBg(a) {
  if (a.severity === 'critical') return 'var(--danger-soft)';
  if (a.severity === 'warning') return 'var(--warning-soft)';
  return 'var(--info-soft)';
}
function alertColor(a) {
  if (a.severity === 'critical') return 'var(--danger)';
  if (a.severity === 'warning') return 'var(--warning)';
  return 'var(--info)';
}
function alertIcon(a) {
  if (a.type === 'contract') return '📋';
  if (a.type === 'attendance') return '⚠️';
  if (a.type === 'birthday') return '🎂';
  return '📌';
}
</script>
