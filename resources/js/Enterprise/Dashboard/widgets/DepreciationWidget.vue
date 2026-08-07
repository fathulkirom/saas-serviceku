<template>
  <SkMetricCard
    :label="'Penyusutan Bulan Ini'"
    :value="stats?.depreciation_mtd ?? 0"
    format="currency"
    color="info"
    :subtext="stats?.depreciation_ytd != null ? `YTD: Rp ${formatNumber(stats.depreciation_ytd)}` : ''"
  >
    <template #icon>📉</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
const props = defineProps({ stats: { type: Object, default: () => ({}) } });
function formatNumber(v) { return v != null ? Number(v).toLocaleString('id-ID') : '0'; }
</script>
