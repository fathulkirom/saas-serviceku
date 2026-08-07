<template>
  <div class="space-y-5">
    <!-- KPI Grid -->
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Business Health</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">{{ stats?.health_score || 0 }}/100</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Active Risks</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--danger)' }">{{ stats?.active_risks || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Recommendations</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--primary)' }">{{ stats?.pending_recommendations || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Insights Today</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--info)' }">{{ stats?.insights_today || 0 }}</p>
      </div>
      <div class="p-3 rounded-xl text-center" :style="{ background: 'var(--bg-hover)' }">
        <p class="text-[10px] uppercase tracking-wider" :style="{ color: 'var(--text-muted)' }">Forecast Accuracy</p>
        <p class="text-xl font-bold mt-1" :style="{ color: 'var(--success)' }">{{ stats?.forecast_accuracy || 0 }}%</p>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
      <!-- Top Insights -->
      <SkCard title="💡 Today's Insights" size="sm">
        <div v-if="stats?.top_insights?.length" class="space-y-2">
          <div v-for="i in stats.top_insights" :key="i.id" class="py-1.5 text-sm"
            :style="{ borderBottom: '1px solid var(--border-light)' }">
            <div class="flex items-center gap-2 mb-1">
              <span class="px-1.5 py-0.5 rounded text-[10px] font-bold" :style="insightTypeStyle(i.insight_type)">{{ i.insight_type }}</span>
              <span class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ i.module }}</span>
            </div>
            <p class="text-sm" :style="{ color: 'var(--text-primary)' }">{{ i.title }}</p>
            <p class="text-xs mt-0.5" :style="{ color: 'var(--text-muted)' }">{{ i.summary?.substring(0, 120) }}{{ (i.summary?.length || 0) > 120 ? '...' : '' }}</p>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No insights yet</p></div>
      </SkCard>

      <!-- Pending Recommendations -->
      <SkCard title="💡 AI Recommendations" size="sm">
        <div v-if="stats?.recommendations?.length" class="space-y-2">
          <div v-for="r in stats.recommendations" :key="r.id" class="flex justify-between items-center py-1 text-sm">
            <div class="min-w-0 flex-1">
              <p class="font-medium truncate" :style="{ color: 'var(--text-primary)' }">{{ r.title }}</p>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ r.module }} · {{ r.estimated_impact }}</p>
            </div>
            <div class="flex gap-1 ml-2">
              <button class="text-[10px] px-2 py-1 rounded font-bold" :style="{ background: 'var(--success-soft)', color: 'var(--success)' }">Accept</button>
            </div>
          </div>
        </div>
        <div v-else class="text-center py-4"><p class="sk-caption">No pending recommendations</p></div>
      </SkCard>

      <!-- Latest Predictions -->
      <SkCard title="📈 Latest Predictions" size="sm">
        <div v-if="stats?.predictions?.length" class="space-y-2">
          <div v-for="p in stats.predictions" :key="p.id" class="flex justify-between items-center py-1 text-sm">
            <div>
              <p class="font-medium" :style="{ color: 'var(--text-primary)' }">{{ p.title }}</p>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">{{ p.forecast_period }}</p>
            </div>
            <div class="text-right">
              <p class="text-sm font-bold" :style="{ color: 'var(--primary)' }">{{ p.predicted_value }}</p>
              <p class="text-[10px]" :style="{ color: 'var(--text-muted)' }">Confidence: {{ p.confidence }}%</p>
            </div>
          </div>
        </div>
      </SkCard>
    </div>

    <!-- Enterprise Chat Preview -->
    <SkCard title="💬 Enterprise AI Assistant" size="md">
      <div class="space-y-2">
        <div v-if="stats?.suggested_queries?.length" class="flex flex-wrap gap-1.5 mb-2">
          <button v-for="(q, i) in stats.suggested_queries" :key="i" class="text-xs px-2 py-1 rounded-full border"
            :style="{ borderColor: 'var(--border-light)', color: 'var(--text-secondary)', background: 'var(--bg-hover)' }">
            {{ q }}
          </button>
        </div>
        <div class="p-3 rounded-lg text-sm" :style="{ background: 'var(--bg-hover)', color: 'var(--text-secondary)' }">
          <span :style="{ color: 'var(--primary)' }">🤖 AI:</span> Ask me anything about your business — 
          "How many services today?", "Which products are low on stock?", "Show me overdue invoices", 
          "What's our profit this month?"
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

function insightTypeStyle(t) {
  const map = {
    daily_summary: { background: 'var(--primary-soft)', color: 'var(--primary)' },
    risk: { background: 'var(--danger-soft)', color: 'var(--danger)' },
    opportunity: { background: 'var(--success-soft)', color: 'var(--success)' },
    anomaly: { background: 'var(--warning-soft)', color: 'var(--warning)' },
    trend: { background: 'var(--info-soft)', color: 'var(--info)' },
  };
  return map[t] || { background: 'var(--bg-hover)', color: 'var(--text-muted)' };
}
</script>
