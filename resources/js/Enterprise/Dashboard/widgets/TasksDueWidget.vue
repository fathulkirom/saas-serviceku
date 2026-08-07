<template>
  <SkMetricCard
    :label="'Task Due Today'"
    :value="stats?.tasks_due_today ?? 0"
    format="number"
    color="warning"
    :subtext="stats?.tasks_overdue ? `${stats.tasks_overdue} overdue` : ''"
  >
    <template #icon>✅</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
