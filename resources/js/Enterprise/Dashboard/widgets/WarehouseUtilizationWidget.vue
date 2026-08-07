<template>
  <SkMetricCard
    :label="'Utilization Gudang'"
    :value="stats?.utilization_pct ?? 0"
    format="number"
    :color="(stats?.utilization_pct ?? 0) > 90 ? 'danger' : (stats?.utilization_pct ?? 0) > 70 ? 'warning' : 'success'"
    :subtext="stats?.free_pct != null ? `${stats.free_pct}% free` : ''"
  >
    <template #icon>🏬</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
