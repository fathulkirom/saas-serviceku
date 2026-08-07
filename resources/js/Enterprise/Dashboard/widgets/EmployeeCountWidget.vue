<template>
  <SkMetricCard
    :label="'Total Karyawan'"
    :value="stats?.total_employees ?? 0"
    format="number"
    color="primary"
    :subtext="stats?.active_employees != null ? `${stats.active_employees} aktif` : ''"
  >
    <template #icon>👥</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
