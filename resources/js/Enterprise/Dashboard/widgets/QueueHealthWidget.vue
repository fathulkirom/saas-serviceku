<template>
  <SkMetricCard :value="stats?.queue_pending || 0" label="Queue Pending" :trend="trend" :color="stats?.queue_pending > 100 ? 'var(--warning)' : 'var(--success)'" icon="📬" />
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: Object, trend: { type: Number, default: 0 } });
</script>
