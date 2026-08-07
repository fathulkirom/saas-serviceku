<template>
  <SkMetricCard
    :label="'Nilai Stok'"
    :value="stats?.stock_value ?? 0"
    format="currency"
    color="primary"
    :subtext="`${stats?.total_items ?? 0} item`"
  >
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
      </svg>
    </template>
  </SkMetricCard>
</template>

<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
