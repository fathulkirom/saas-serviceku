<template>
  <SkMetricCard :value="stats?.failed_jobs || 0" label="Failed Jobs" :trend="trend" :color="stats?.failed_jobs > 0 ? 'var(--danger)' : 'var(--success)'" icon="❌" />
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: Object, trend: { type: Number, default: 0 } });
</script>
