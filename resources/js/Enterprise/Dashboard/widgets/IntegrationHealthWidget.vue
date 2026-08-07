<template>
  <SkMetricCard :value="stats?.integration_health || 0" suffix="%" label="Integration Health" :trend="trend" :color="scoreColor(stats?.integration_health)" icon="🔗" />
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: Object, trend: { type: Number, default: 0 } });
function scoreColor(v) { if (!v && v !== 0) return 'var(--text-muted)'; if (v >= 80) return 'var(--success)'; if (v >= 60) return 'var(--warning)'; return 'var(--danger)'; }
</script>
