<template>
  <SkMetricCard
    :label="'Hutang Outstanding'"
    :value="stats?.ap_outstanding ?? 0"
    format="currency"
    color="danger"
    :subtext="stats?.ap_due_soon ? `${stats.ap_due_soon} segera jatuh tempo` : ''"
  >
    <template #icon>📤</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
