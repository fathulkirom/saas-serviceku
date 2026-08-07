<template>
  <SkMetricCard
    :label="label"
    :value="value"
    format="currency"
    :trend="trend"
    color="primary"
    :subtext="subtext"
  >
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
      </svg>
    </template>
  </SkMetricCard>
</template>

<script setup>
import { computed } from 'vue';
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';

const props = defineProps({
  stats: { type: Object, default: () => ({}) },
});

const value = computed(() => props.stats?.profit_today ?? 0);
const label = 'Profit Hari Ini';
const subtext = computed(() => {
  const margin = props.stats?.profit_margin;
  return margin ? `Margin: ${margin}%` : 'Hari ini';
});

const trend = computed(() => {
  return props.stats?.profit_trend ?? undefined;
});
</script>
