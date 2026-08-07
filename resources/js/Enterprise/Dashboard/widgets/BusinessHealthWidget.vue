<template>
  <SkMetricCard
    :label="'Business Health'"
    :value="stats?.health_score ?? 0"
    format="number"
    :color="(stats?.health_score ?? 0) >= 80 ? 'success' : (stats?.health_score ?? 0) >= 60 ? 'warning' : 'danger'"
    :subtext="'Enterprise Scorecard'"
  >
    <template #icon>💚</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
