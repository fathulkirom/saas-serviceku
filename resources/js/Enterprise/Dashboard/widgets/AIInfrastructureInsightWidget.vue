<template>
  <SkMetricCard :value="stats?.ai_insight_count || 0" label="AI Infra Insights" :trend="trend" color="var(--primary)" icon="🤖" />
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: Object, trend: { type: Number, default: 0 } });
</script>
