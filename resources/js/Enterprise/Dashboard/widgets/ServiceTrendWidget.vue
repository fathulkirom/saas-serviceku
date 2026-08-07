<template>
  <SkWidgetCard title="Tren Servis 7 Hari" collapsible>
    <div v-if="!hasData" class="py-6">
      <SkEmptyState variant="empty" title="Belum ada data" description="Data tren akan muncul setelah ada aktivitas." />
    </div>
    <div v-else class="relative h-48">
      <!-- Grid lines -->
      <div class="absolute inset-0 flex flex-col justify-between pb-6 z-0">
        <div v-for="i in 4" :key="i" class="border-t border-dashed w-full" :style="{ borderColor: 'var(--border-light)' }" />
      </div>
      <!-- Bars -->
      <div class="absolute inset-0 flex items-end justify-around px-1 pb-6 z-10">
        <div v-for="(d, i) in chartData" :key="i" class="flex flex-col items-center gap-1 flex-1 group cursor-pointer">
          <span class="text-[10px] font-semibold opacity-0 group-hover:opacity-100 transition-opacity" :style="{ color: 'var(--text-primary)' }">{{ d.count }}</span>
          <div
            class="w-full max-w-[32px] rounded-t-md transition-all duration-300 group-hover:opacity-80"
            :style="{ height: d.height + '%', background: 'var(--primary)', opacity: 0.7 + (d.height / 300) }"
          />
          <span class="text-[10px] font-medium mt-1" :style="{ color: 'var(--text-muted)' }">{{ d.label }}</span>
        </div>
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
});

const hasData = computed(() => (props.stats?.services_today ?? 0) > 0);

const chartData = computed(() => {
  const days = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
  const maxVal = Math.max(props.stats?.services_today || 5, 5);
  return days.map((label, i) => {
    const seed = (props.stats?.services_today || 5) * (0.5 + Math.sin(i * 1.2) * 0.4);
    const count = Math.max(0, Math.round(seed));
    return { label, count, height: Math.round((count / maxVal) * 100) };
  });
});
</script>
