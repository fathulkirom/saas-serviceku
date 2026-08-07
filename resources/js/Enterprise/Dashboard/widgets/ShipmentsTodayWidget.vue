<template>
  <SkMetricCard
    :label="'Shipments Today'"
    :value="stats?.shipments_today ?? 0"
    format="number"
    color="primary"
    :subtext="stats?.delivered_today != null ? `${stats.delivered_today} delivered` : ''"
  >
    <template #icon>🚚</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
