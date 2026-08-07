<template>
  <SkWidgetCard title="Status Servis" collapsible>
    <div v-if="!hasData" class="py-6">
      <SkEmptyState variant="empty" title="Belum ada data" description="Data status servis akan muncul di sini." />
    </div>
    <div v-else class="space-y-3">
      <div v-for="bar in bars" :key="bar.label" class="flex items-center gap-3">
        <span class="text-xs font-medium w-24 flex-shrink-0 truncate" :style="{ color: 'var(--text-secondary)' }">{{ bar.label }}</span>
        <div class="flex-1 h-6 rounded-full overflow-hidden" :style="{ background: 'var(--bg-hover)' }">
          <div
            class="h-full rounded-full flex items-center justify-end px-2 transition-all duration-700"
            :style="{ width: bar.percent + '%', background: bar.color }"
          >
            <span v-if="bar.percent > 15" class="text-[10px] font-bold text-white">{{ bar.count }}</span>
          </div>
        </div>
        <span v-if="bar.percent <= 15" class="text-xs font-semibold flex-shrink-0 w-8 text-right" :style="{ color: 'var(--text-secondary)' }">{{ bar.count }}</span>
      </div>
    </div>
  </SkWidgetCard>
</template>

<script setup>
import { computed } from 'vue';
import SkWidgetCard from '@/Enterprise/Components/Cards/WidgetCard.vue';
import SkEmptyState from '@/Enterprise/Components/Empty/EmptyState.vue';

const props = defineProps({
  stats: { type: Object, default: () => ({}) },
  statusCounts: { type: Object, default: null },
});

const statusConfig = [
  { key: 'menunggu_alokasi', label: 'Menunggu', color: '#F59E0B' },
  { key: 'diterima', label: 'Diterima', color: '#3B82F6' },
  { key: 'dikerjakan', label: 'Dikerjakan', color: '#8B5CF6' },
  { key: 'indent', label: 'Indent', color: '#EC4899' },
  { key: 'menunggu_konfirmasi_pelanggan', label: 'Konfirmasi', color: '#EF4444' },
  { key: 'menunggu_konfirmasi_internal', label: 'Internal', color: '#F97316' },
  { key: 'siap_diambil', label: 'Siap Ambil', color: '#22C55E' },
  { key: 'selesai', label: 'Selesai', color: '#10B981' },
  { key: 'diambil', label: 'Diambil', color: '#6B7280' },
];

const counts = computed(() => {
  if (props.statusCounts) return props.statusCounts;
  return props.stats?.statusCounts || {};
});

const total = computed(() => {
  return Object.values(counts.value).reduce((sum, v) => sum + (Number(v) || 0), 0);
});

const hasData = computed(() => total.value > 0);

const bars = computed(() => {
  return statusConfig
    .map(c => {
      const count = Number(counts.value[c.key]) || 0;
      return {
        ...c,
        count,
        percent: total.value > 0 ? Math.max(2, Math.round((count / total.value) * 100)) : 0,
      };
    })
    .filter(b => b.count > 0)
    .sort((a, b) => b.count - a.count)
    .slice(0, 7);
});
</script>
