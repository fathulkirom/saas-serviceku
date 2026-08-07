<template>
  <SkMetricCard
    :label="'Payroll Pending'"
    :value="stats?.payroll_pending ?? 0"
    format="number"
    color="warning"
    :subtext="stats?.next_payroll_period ? `Period: ${stats.next_payroll_period}` : ''"
  >
    <template #icon>💰</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
