<template>
  <SkMetricCard
    :label="'Transaksi Hari Ini'"
    :value="stats?.revenue_today ?? 0"
    format="currency"
    color="warning"
    :subtext="`${stats?.paid_sales ?? stats?.services_today ?? 0} lunas`"
  >
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
      </svg>
    </template>
  </SkMetricCard>
</template>

<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
