<template>
  <SkMetricCard
    :label="'Penjualan Hari Ini'"
    :value="stats?.sales_today ?? 0"
    format="currency"
    color="success"
    :subtext="stats?.transactions_today ? `${stats.transactions_today} transaksi` : ''"
  >
    <template #icon>🛒</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
