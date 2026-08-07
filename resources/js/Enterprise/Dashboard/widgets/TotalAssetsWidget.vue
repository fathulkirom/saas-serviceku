<template>
  <SkMetricCard
    :label="'Total Asset'"
    :value="stats?.total_assets ?? 0"
    format="number"
    color="primary"
    :subtext="`Nilai: Rp ${formatNumber(stats?.total_value ?? 0)}`"
  >
    <template #icon>🏗️</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
const props = defineProps({ stats: { type: Object, default: () => ({}) } });
function formatNumber(v) { return v != null ? Number(v).toLocaleString('id-ID') : '0'; }
</script>
