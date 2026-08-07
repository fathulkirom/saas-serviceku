<template>
  <SkMetricCard
    :label="'Knowledge Articles'"
    :value="stats?.kb_articles ?? 0"
    format="number"
    color="success"
    :subtext="stats?.total_views != null ? `${stats.total_views} total views` : ''"
  >
    <template #icon>📚</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
