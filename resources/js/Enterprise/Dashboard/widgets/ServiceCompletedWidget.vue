<template>
  <SkMetricCard
    :label="label"
    :value="value"
    format="number"
    color="success"
    :subtext="subtext"
  >
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
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

const value = computed(() => props.stats?.completed_services_today ?? 0);
const label = 'Servis Selesai';
const subtext = computed(() => `Total: ${props.stats?.total_completed ?? 0} selesai`);
</script>
