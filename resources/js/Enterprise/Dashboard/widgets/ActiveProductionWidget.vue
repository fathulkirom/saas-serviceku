<template>
  <SkMetricCard
    :label="'Produksi Aktif'"
    :value="stats?.active_orders ?? 0"
    format="number"
    color="primary"
    :subtext="stats?.completed_today != null ? `${stats.completed_today} completed today` : ''"
  >
    <template #icon>🏭</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
