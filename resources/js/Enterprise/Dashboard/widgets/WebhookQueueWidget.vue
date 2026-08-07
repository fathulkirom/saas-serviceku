<template>
  <SkMetricCard
    :label="'Webhook Queue'"
    :value="stats?.webhook_queue ?? 0"
    format="number"
    :color="(stats?.webhook_queue ?? 0) > 10 ? 'danger' : (stats?.webhook_queue ?? 0) > 5 ? 'warning' : 'success'"
    :subtext="stats?.webhook_failed ? `${stats.webhook_failed} failed` : ''"
  >
    <template #icon>🪝</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
