<template>
  <SkMetricCard :value="stats?.open_findings || 0" label="Open Findings" :trend="trend" :color="stats?.open_findings > 0 ? 'var(--warning)' : 'var(--success)'" icon="📋" />
</template>

<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: Object, trend: { type: Number, default: 0 } });
</script>
