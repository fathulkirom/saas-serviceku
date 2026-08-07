<template>
  <SkMetricCard
    :label="'Menunggu Alokasi'"
    :value="stats?.pending_allocation ?? 0"
    format="number"
    color="warning"
    :subtext="'Belum ditugaskan ke teknisi'"
  >
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
    </template>
  </SkMetricCard>
</template>

<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
defineProps({ stats: { type: Object, default: () => ({}) } });
</script>
