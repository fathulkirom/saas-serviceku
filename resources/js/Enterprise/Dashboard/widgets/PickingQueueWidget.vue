<template>
  <SkMetricCard
    :label="'Picking Queue'"
    :value="stats?.picking_queue ?? 0"
    format="number"
    :color="(stats?.picking_queue ?? 0) > 10 ? 'danger' : (stats?.picking_queue ?? 0) > 5 ? 'warning' : 'success'"
    :subtext="stats?.picking_assigned ? `${stats.picking_assigned} assigned` : ''"
  >
    <template #icon>📤</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
