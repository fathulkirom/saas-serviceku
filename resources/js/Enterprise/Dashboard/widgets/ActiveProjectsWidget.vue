<template>
  <SkMetricCard
    :label="'Project Aktif'"
    :value="stats?.active_projects ?? 0"
    format="number"
    color="primary"
    :subtext="stats?.total_projects != null ? `Total: ${stats.total_projects}` : ''"
  >
    <template #icon>📁</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
