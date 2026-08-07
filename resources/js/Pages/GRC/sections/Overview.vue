<template>
  <div class="space-y-5">
    <!-- KPI Row -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Governance Score</p>
        <p class="text-xl font-bold mt-1" :style="{ color: scoreColor(stats?.governance_score) }">{{ stats?.governance_score || 0 }}%</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Critical Risks</p>
        <p class="text-xl font-bold mt-1" :style="{ color: stats?.critical_risks > 0 ? 'var(--danger)' : 'var(--success)' }">{{ stats?.critical_risks || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Compliance</p>
        <p class="text-xl font-bold mt-1" :style="{ color: scoreColor(stats?.compliance_pct) }">{{ stats?.compliance_pct || 0 }}%</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Open Findings</p>
        <p class="text-xl font-bold mt-1" :style="{ color: stats?.open_findings > 0 ? 'var(--warning)' : 'var(--success)' }">{{ stats?.open_findings || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Overdue CAPA</p>
        <p class="text-xl font-bold mt-1" :style="{ color: stats?.overdue_capa > 0 ? 'var(--danger)' : 'var(--success)' }">{{ stats?.overdue_capa || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Incidents (MTD)</p>
        <p class="text-xl font-bold mt-1" :style="{ color: stats?.incidents_mtd > 0 ? 'var(--warning)' : 'var(--success)' }">{{ stats?.incidents_mtd || 0 }}</p>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Risk Heatmap -->
      <SkCard title="🔥 Risk Heatmap" size="sm">
        <div v-if="stats?.risk_heatmap?.length" class="space-y-1">
          <div v-for="r in stats.risk_heatmap" :key="r.id" class="flex justify-between items-center py-1.5 text-sm border-b" :style="{ borderColor: 'var(--border-light)' }">
            <div class="min-w-0 flex-1">
              <p class="font-medium truncate" :style="{ color: 'var(--text-primary)' }">{{ r.risk_title }}</p>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ r.category }} · Owner: {{ r.risk_owner }}</p>
            </div>
            <span class="text-xs font-bold ml-2 px-2 py-0.5 rounded-full" :style="riskLevelStyle(r.risk_level)">{{ r.risk_score }} — {{ r.risk_level }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No active risks</p></div>
      </SkCard>

      <!-- Compliance Status -->
      <SkCard title="✅ Compliance Status" size="sm">
        <div v-if="stats?.compliance?.length" class="space-y-3">
          <div v-for="c in stats.compliance" :key="c.standard" class="space-y-1">
            <div class="flex justify-between text-sm">
              <span class="font-medium" :style="{ color: 'var(--text-primary)' }">{{ c.standard }}</span>
              <span class="text-xs font-bold" :style="{ color: complianceColor(c.pct) }">{{ c.pct }}%</span>
            </div>
            <div class="h-2 rounded overflow-hidden" :style="{ background: 'var(--bg-hover)' }">
              <div class="h-full rounded transition-all duration-500" :style="{ width: (c.pct || 0) + '%', background: complianceColor(c.pct) }"></div>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No compliance data</p></div>
      </SkCard>

      <!-- Recent Incidents -->
      <SkCard title="🚨 Recent Incidents" size="sm">
        <div v-if="stats?.incidents?.length" class="space-y-2">
          <div v-for="i in stats.incidents" :key="i.id" class="flex justify-between items-center py-1 text-sm border-b" :style="{ borderColor: 'var(--border-light)' }">
            <div class="min-w-0 flex-1">
              <p class="font-medium truncate" :style="{ color: 'var(--text-primary)' }">{{ i.incident_title }}</p>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ i.incident_type }} · {{ formatTimeAgo(i.reported_at) }}</p>
            </div>
            <span class="text-[10px] font-bold ml-2 px-2 py-0.5 rounded" :style="severityStyle(i.severity)">{{ i.status }}</span>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No recent incidents</p></div>
      </SkCard>
    </div>

    <!-- Second Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
      <!-- Control Effectiveness -->
      <SkCard title="🔒 Internal Control Effectiveness" size="sm">
        <div v-if="stats?.controls" class="grid grid-cols-3 gap-3">
          <div class="p-3 rounded-lg text-center" :style="{ background: 'var(--bg-hover)' }">
            <p class="text-lg font-bold" :style="{ color: 'var(--success)' }">{{ stats.controls.effective || 0 }}</p>
            <p class="text-[10px] uppercase" :style="{ color: 'var(--text-muted)' }">Effective</p>
          </div>
          <div class="p-3 rounded-lg text-center" :style="{ background: 'var(--bg-hover)' }">
            <p class="text-lg font-bold" :style="{ color: 'var(--warning)' }">{{ stats.controls.weak || 0 }}</p>
            <p class="text-[10px] uppercase" :style="{ color: 'var(--text-muted)' }">Weak</p>
          </div>
          <div class="p-3 rounded-lg text-center" :style="{ background: 'var(--bg-hover)' }">
            <p class="text-lg font-bold" :style="{ color: 'var(--danger)' }">{{ stats.controls.failed || 0 }}</p>
            <p class="text-[10px] uppercase" :style="{ color: 'var(--text-muted)' }">Failed</p>
          </div>
        </div>
      </SkCard>

      <!-- AI Risk Insights -->
      <SkCard title="🤖 AI Risk Insights" size="sm">
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
  </div>
</template>

<script setup>
import SkCard from '@/Enterprise/Components/Cards/Card.vue';

defineProps({ data: Object, stats: Object });

function scoreColor(v) {
  if (!v && v !== 0) return 'var(--text-muted)';
  if (v >= 80) return 'var(--success)';
  if (v >= 60) return 'var(--warning)';
  return 'var(--danger)';
}

function complianceColor(v) {
  if (!v && v !== 0) return 'var(--text-muted)';
  if (v >= 80) return 'var(--success)';
  if (v >= 60) return 'var(--warning)';
  return 'var(--danger)';
}

function riskLevelStyle(level) {
  const map = {
    critical: { background: '#dc262620', color: 'var(--danger)' },
    high: { background: '#f9731620', color: 'var(--warning)' },
    medium: { background: '#eab30820', color: 'var(--caution)' },
    low: { background: '#22c55e20', color: 'var(--success)' },
  };
  return map[level] || { background: 'var(--bg-hover)', color: 'var(--text-muted)' };
}

function severityStyle(severity) {
  const map = {
    critical: { background: '#dc262620', color: 'var(--danger)' },
    major: { background: '#f9731620', color: 'var(--warning)' },
    minor: { background: '#eab30820', color: 'var(--caution)' },
    observation: { background: '#3b82f620', color: 'var(--info)' },
  };
  return map[severity] || { background: 'var(--bg-hover)', color: 'var(--text-muted)' };
}

function formatTimeAgo(v) {
  if (!v) return '';
  const m = Math.floor((Date.now() - new Date(v).getTime()) / 60000);
  return m < 60 ? m + 'm ago' : m < 1440 ? Math.floor(m / 60) + 'h ago' : Math.floor(m / 1440) + 'd ago';
}
</script>
