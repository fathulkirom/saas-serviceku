<template>
  <SkMetricCard
    :label="'Hadir Hari Ini'"
    :value="stats?.present_today ?? 0"
    format="number"
    color="success"
    :subtext="stats?.attendance_rate != null ? `${stats.attendance_rate}% rate` : ''"
  >
    <template #icon>🕐</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
