<template>
  <SkMetricCard
    :label="'Pending Approvals'"
    :value="stats?.pending_approvals ?? 0"
    format="number"
    :color="(stats?.pending_approvals ?? 0) > 5 ? 'warning' : 'success'"
    :subtext="stats?.my_approvals ? `${stats.my_approvals} need yours` : ''"
  >
    <template #icon>✅</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
