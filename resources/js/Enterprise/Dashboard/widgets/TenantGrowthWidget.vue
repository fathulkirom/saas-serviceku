<template>
  <SkMetricCard
    :label="'Active Tenants'"
    :value="stats?.active_tenants ?? 0"
    format="number"
    color="primary"
    :subtext="stats?.new_this_month != null ? `+${stats.new_this_month} this month` : ''"
  >
    <template #icon>🏢</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
