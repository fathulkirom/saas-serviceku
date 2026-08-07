<template>
  <SkMetricCard :value="stats?.active_sessions || 0" label="Active Sessions" :trend="trend" color="var(--primary)" icon="👥" />
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: Object, trend: { type: Number, default: 0 } });
</script>
