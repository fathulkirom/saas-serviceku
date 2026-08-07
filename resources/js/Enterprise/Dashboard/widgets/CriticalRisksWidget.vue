<template>
  <SkMetricCard :value="stats?.critical_risks || 0" label="Critical Risks" :trend="trend" :color="stats?.critical_risks > 0 ? 'var(--danger)' : 'var(--success)'" icon="⚠️" />
</template>

<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: Object, trend: { type: Number, default: 0 } });
</script>
