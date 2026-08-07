<template>
  <SkMetricCard :value="stats?.error_rate_pct || 0" suffix="%" label="Error Rate" :trend="trend" :color="thresholdColor(stats?.error_rate_pct, 1, 5)" icon="⚠️" />
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: Object, trend: { type: Number, default: 0 } });
function thresholdColor(v, w, c) { if (!v && v !== 0) return 'var(--text-muted)'; if (v >= c) return 'var(--danger)'; if (v >= w) return 'var(--warning)'; return 'var(--success)'; }
</script>
