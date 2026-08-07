<template>
  <SkMetricCard :value="stats?.security_alerts || 0" label="Security Alerts" :trend="trend" :color="stats?.security_alerts > 0 ? 'var(--danger)' : 'var(--success)'" icon="🛡️" />
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: Object, trend: { type: Number, default: 0 } });
</script>
