<template>
  <SkMetricCard
    :label="'MRR'"
    :value="stats?.mrr ?? 0"
    format="currency"
    color="success"
    :subtext="stats?.arr != null ? `ARR: Rp ${formatNumber(stats.arr)}` : ''"
  >
    <template #icon>💰</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
const props = defineProps({ stats: { type: Object, default: () => ({}) } });
function formatNumber(v) { return v != null ? Number(v).toLocaleString('id-ID') : '0'; }
</script>
