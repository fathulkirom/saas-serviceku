<template>
  <SkMetricCard
    :label="label"
    :value="value"
    format="currency"
    color="info"
    :subtext="subtext"
  >
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
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

const value = computed(() => props.stats?.cash_balance ?? 0);
const label = 'Saldo Kas';
const subtext = computed(() => {
  const diff = props.stats?.cash_difference;
  return diff !== undefined ? (diff >= 0 ? 'Surplus' : 'Defisit') : 'Hari ini';
});
</script>
