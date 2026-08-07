<template>
  <SkMetricCard :value="stats?.api_avg_response_ms || 0" suffix="ms" label="API Response Time" :trend="trend" :color="thresholdColor(stats?.api_avg_response_ms, 300, 1000)" icon="🔌" />
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: Object, trend: { type: Number, default: 0 } });
function thresholdColor(v, w, c) { if (!v && v !== 0) return 'var(--text-muted)'; if (v >= c) return 'var(--danger)'; if (v >= w) return 'var(--warning)'; return 'var(--success)'; }
</script>
