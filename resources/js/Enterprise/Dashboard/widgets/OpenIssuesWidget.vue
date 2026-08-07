<template>
  <SkMetricCard
    :label="'Open Issues'"
    :value="stats?.open_issues ?? 0"
    format="number"
    color="danger"
    :subtext="stats?.escalated_issues ? `${stats.escalated_issues} escalated` : ''"
  >
    <template #icon>🐛</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
