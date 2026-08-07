<template>
  <SkMetricCard
    :label="'Open Orders'"
    :value="stats?.open_orders ?? 0"
    format="number"
    color="warning"
    :subtext="stats?.pending_delivery ? `${stats.pending_delivery} pending delivery` : ''"
  >
    <template #icon>📦</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
