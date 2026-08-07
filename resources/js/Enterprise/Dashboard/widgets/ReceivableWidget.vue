<template>
  <SkMetricCard
    :label="label"
    :value="value"
    format="currency"
    color="danger"
    :subtext="subtext"
  >
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
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

const value = computed(() => props.stats?.receivables ?? 0);
const label = 'Piutang';
const subtext = computed(() => `${props.stats?.unpaid_invoices ?? 0} invoice belum lunas`);
</script>
