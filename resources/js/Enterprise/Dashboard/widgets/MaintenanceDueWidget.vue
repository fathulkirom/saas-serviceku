<template>
  <SkMetricCard
    :label="'Maintenance Due'"
    :value="stats?.maintenance_due ?? 0"
    format="number"
    color="warning"
    :subtext="stats?.maintenance_overdue ? `${stats.maintenance_overdue} overdue` : ''"
  >
    <template #icon>🔧</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
