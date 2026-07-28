<template>
  <div>
    <div v-if="label" class="flex items-center justify-between mb-1.5">
      <span class="text-xs font-medium" :style="{ color: 'var(--text-secondary)' }">{{ label }}</span>
      <span class="text-xs font-bold" :style="{ color: barColor }">{{ value }}%</span>
    </div>
    <div class="w-full rounded-full overflow-hidden" :class="barHeight" :style="{ background: 'var(--bg-hover)' }">
      <div class="rounded-full transition-all duration-700 ease-out" :class="[barHeight, animated ? 'animate-pulse' : '']"
        :style="{ width: Math.min(100, Math.max(0, value)) + '%', background: barColor }" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  value: { type: Number, default: 0 },
  color: { type: String, default: 'green' },
  size: { type: String, default: 'md' },
  label: { type: String, default: '' },
  animated: { type: Boolean, default: false },
});

const barHeight = computed(() => props.size === 'sm' ? 'h-1.5' : 'h-2.5');

const barColor = computed(() => {
  const colors = {
    green: '#10b981', blue: '#3b82f6', yellow: '#f59e0b',
    red: '#ef4444', purple: '#7c3aed',
  };
  return colors[props.color] || colors.green;
});
</script>
