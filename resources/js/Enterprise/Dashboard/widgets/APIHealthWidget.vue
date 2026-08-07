<template>
  <SkMetricCard
    :label="'API Health'"
    :value="stats?.api_health ?? 0"
    format="number"
    :color="(stats?.api_health ?? 0) >= 99 ? 'success' : (stats?.api_health ?? 0) >= 90 ? 'warning' : 'danger'"
    :subtext="stats?.uptime_pct != null ? `${stats.uptime_pct}% uptime` : ''"
  >
    <template #icon>🌐</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
