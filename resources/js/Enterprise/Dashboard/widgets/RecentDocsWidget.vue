<template>
  <SkMetricCard
    :label="'Recent Docs'"
    :value="stats?.uploaded_today ?? 0"
    format="number"
    color="primary"
    :subtext="'Uploaded today'"
  >
    <template #icon>📄</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
