<template>
  <SkMetricCard
    :label="'Saldo Kas'"
    :value="stats?.cash_balance ?? 0"
    format="currency"
    color="success"
    :subtext="stats?.bank_count ? `${stats.bank_count} rekening` : ''"
  >
    <template #icon>💵</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
