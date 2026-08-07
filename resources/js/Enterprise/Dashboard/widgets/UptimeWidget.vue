<template>
  <SkMetricCard :value="stats?.uptime_pct || 99.9" suffix="%" label="Uptime" :trend="trend" color="var(--success)" icon="⏱️" />
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: Object, trend: { type: Number, default: 0 } });
</script>
