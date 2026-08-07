<template>
  <SkMetricCard
    :label="'AI Recommendations'"
    :value="stats?.pending_recommendations ?? 0"
    format="number"
    :color="(stats?.pending_recommendations ?? 0) > 0 ? 'primary' : 'success'"
    :subtext="stats?.accepted_today != null ? `${stats.accepted_today} accepted today` : ''"
  >
    <template #icon>💡</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
