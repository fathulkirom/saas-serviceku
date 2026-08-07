<template>
  <SkMetricCard
    :label="'Pendapatan Hari Ini'"
    :value="stats?.revenue_today ?? 0"
    format="currency"
    :trend="trend"
    color="success"
    :subtext="subtext"
  >
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </template>
  </SkMetricCard>
</template>

<script setup>
import { computed } from 'vue';
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';

defineProps({ stats: { type: Object, default: () => ({}) } });

const subtext = computed(() => 'Total pendapatan hari ini');
const trend = computed(() => undefined);
</script>
