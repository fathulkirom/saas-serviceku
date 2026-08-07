<template>
  <SkMetricCard
    :label="'Predicted Revenue'"
    :value="stats?.predicted_revenue ?? 0"
    format="currency"
    color="success"
    :subtext="stats?.forecast_accuracy != null ? `${stats.forecast_accuracy}% accuracy` : ''"
  >
    <template #icon>📈</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
