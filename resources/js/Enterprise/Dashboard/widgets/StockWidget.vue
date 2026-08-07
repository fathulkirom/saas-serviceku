<template>
  <SkMetricCard
    :label="'Stok Menipis'"
    :value="stats?.low_stock ?? 0"
    format="number"
    color="danger"
    :subtext="value > 0 ? `${value} produk butuh restock` : 'Semua stok aman'"
  >
    <template #icon>
      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
      </svg>
    </template>
    <template #value>
      <span :class="{ 'animate-pulse': (stats?.low_stock ?? 0) > 0 }">{{ stats?.low_stock ?? 0 }}</span>
    </template>
  </SkMetricCard>
</template>

<script setup>
import { computed } from 'vue';
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
const props = defineProps({ stats: { type: Object, default: () => ({}) } });
const value = computed(() => props.stats?.low_stock ?? 0);
</script>
