<template>
  <SkMetricCard
    :label="'Marketplace Sync'"
    :value="stats?.marketplace_orders ?? 0"
    format="number"
    color="primary"
    :subtext="stats?.last_sync_at ? `Last sync: ${formatTimeAgo(stats.last_sync_at)}` : ''"
  >
    <template #icon>🛍️</template>
  </SkMetricCard>
</template>
<script setup>
import SkMetricCard from '@/Enterprise/Components/Cards/MetricCard.vue';
const props = defineProps({ stats: { type: Object, default: () => ({}) } });
function formatTimeAgo(v) {
  if (!v) return '';
  const diff = Date.now() - new Date(v).getTime();
  const mins = Math.floor(diff / 60000);
  if (mins < 60) return mins + 'm ago';
  const hours = Math.floor(mins / 60);
  if (hours < 24) return hours + 'h ago';
  return Math.floor(hours / 24) + 'd ago';
}
</script>
