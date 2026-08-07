<template>
  <SkMetricCard
    :label="'Marketplace Orders'"
    :value="stats?.marketplace_orders ?? 0"
    format="number"
    color="info"
    :subtext="stats?.pending_sync ? `${stats.pending_sync} need sync` : ''"
  >
    <template #icon>🛍️</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
