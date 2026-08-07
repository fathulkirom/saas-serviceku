<template>
  <SkMetricCard
    :label="'Laba Bersih (MTD)'"
    :value="stats?.net_profit_mtd ?? 0"
    format="currency"
    :color="(stats?.net_profit_mtd ?? 0) >= 0 ? 'success' : 'danger'"
    :subtext="stats?.margin_pct != null ? `Margin ${stats.margin_pct}%` : ''"
  >
    <template #icon>💰</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
