<template>
  <SkMetricCard
    :label="'Material Shortage'"
    :value="stats?.shortages ?? 0"
    format="number"
    :color="(stats?.shortages ?? 0) > 0 ? 'danger' : 'success'"
    :subtext="stats?.mrp_suggestions != null ? `${stats.mrp_suggestions} suggestions` : ''"
  >
    <template #icon>🧱</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
