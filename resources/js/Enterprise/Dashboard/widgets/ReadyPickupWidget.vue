<template>
  <SkMetricCard
    :label="'Siap Diambil'"
    :value="stats?.ready_for_pickup ?? 0"
    format="number"
    color="success"
    :subtext="value > 0 ? `${value} servis siap diambil` : 'Belum ada'"
  >
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
      </svg>
    </template>
  </SkMetricCard>
</template>

<script setup>
import { computed } from 'vue';
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
const props = defineProps({ stats: { type: Object, default: () => ({}) } });
const value = computed(() => props.stats?.ready_for_pickup ?? 0);
</script>
