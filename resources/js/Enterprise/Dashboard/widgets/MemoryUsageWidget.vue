<template>
  <SkMetricCard :value="stats?.memory_pct || 0" suffix="%" label="Memory Usage" :trend="trend" :color="thresholdColor(stats?.memory_pct, 70, 90)" icon="🧠" />
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: Object, trend: { type: Number, default: 0 } });
function thresholdColor(v, w, c) { if (!v && v !== 0) return 'var(--text-muted)'; if (v >= c) return 'var(--danger)'; if (v >= w) return 'var(--warning)'; return 'var(--success)'; }
</script>
