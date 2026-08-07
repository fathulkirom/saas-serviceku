<template>
  <SkMetricCard
    :label="'OEE'"
    :value="stats?.oee_pct ?? 0"
    format="number"
    :color="(stats?.oee_pct ?? 0) >= 85 ? 'success' : (stats?.oee_pct ?? 0) >= 60 ? 'warning' : 'danger'"
    :subtext="stats?.oee_pct != null ? 'Overall Equipment Effectiveness' : ''"
  >
    <template #icon>📊</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
