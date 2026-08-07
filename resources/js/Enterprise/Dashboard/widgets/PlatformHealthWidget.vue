<template>
  <SkMetricCard
    :label="'Platform Health'"
    :value="stats?.health_score ?? 0"
    format="number"
    :color="(stats?.health_score ?? 0) >= 95 ? 'success' : (stats?.health_score ?? 0) >= 80 ? 'warning' : 'danger'"
    :subtext="stats?.uptime_pct != null ? `${stats.uptime_pct}% uptime` : ''"
  >
    <template #icon>💚</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
