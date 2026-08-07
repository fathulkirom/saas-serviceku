<template>
  <SkMetricCard :value="stats?.webhook_pending || 0" label="Webhook Queue" :trend="trend" :color="stats?.webhook_pending > 50 ? 'var(--warning)' : 'var(--success)'" icon="🪝" />
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: Object, trend: { type: Number, default: 0 } });
</script>
