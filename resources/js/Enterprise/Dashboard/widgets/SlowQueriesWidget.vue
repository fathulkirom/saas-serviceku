<template>
  <SkMetricCard :value="stats?.slow_queries || 0" label="Slow Queries" :trend="trend" :color="stats?.slow_queries > 0 ? 'var(--warning)' : 'var(--success)'" icon="🐌" />
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: Object, trend: { type: Number, default: 0 } });
</script>
