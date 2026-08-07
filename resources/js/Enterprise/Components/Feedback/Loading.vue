<template>
  <!-- Loading Spinner -->
  <div v-if="variant === 'spinner'" :class="classes" role="status">
    <svg class="animate-spin" :class="spinnerSizeClass" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
    </svg>
    <span v-if="text || $slots.default" class="ml-2" :class="textSizeClass">
      <slot>{{ text }}</slot>
    </span>
  </div>

  <!-- Skeleton -->
  <div v-else-if="variant === 'skeleton'" :style="{ width, height }">
    <div v-if="type === 'card'" class="space-y-3">
      <div class="skeleton-pulse h-4 w-3/4 rounded" />
      <div class="skeleton-pulse h-3 w-full rounded" />
      <div class="skeleton-pulse h-3 w-5/6 rounded" />
    </div>
    <div v-else-if="type === 'table'" class="space-y-2">
      <div class="skeleton-pulse h-8 w-full rounded-lg" />
      <div v-for="i in count" :key="i" class="skeleton-pulse h-10 w-full rounded-lg" />
    </div>
    <div v-else-if="type === 'text'" class="space-y-2">
      <div v-for="i in count" :key="i" class="skeleton-pulse h-3 rounded" :style="{ width: (90 - i * 10) + '%' }" />
    </div>
    <div v-else-if="type === 'stat'" class="grid grid-cols-2 sm:grid-cols-4 gap-4">
      <div v-for="i in count" :key="i" class="p-4 rounded-xl border" :style="{ borderColor: 'var(--border-color)' }">
        <div class="flex items-center gap-3">
          <div class="skeleton-pulse w-10 h-10 rounded-xl flex-shrink-0" />
          <div class="flex-1 space-y-2">
            <div class="skeleton-pulse h-3 w-3/4 rounded" />
            <div class="skeleton-pulse h-5 w-1/2 rounded" />
          </div>
        </div>
      </div>
    </div>
    <div v-else-if="type === 'circle'" :style="{ width: width || '40px', height: height || '40px' }" class="skeleton-pulse rounded-full" />
  </div>

  <!-- Overlay Loading -->
  <div v-else-if="variant === 'overlay'" class="fixed inset-0 z-50 flex items-center justify-center bg-white/60 backdrop-blur-sm">
    <div class="flex flex-col items-center gap-3">
      <div class="sk-animate-spin w-10 h-10 border-3 border-indigo-500 border-t-transparent rounded-full"></div>
      <p v-if="text" class="sk-label-sm">{{ text }}</p>
    </div>
  </div>

  <!-- Progress Bar -->
  <div v-else-if="variant === 'progress'" :class="classes">
    <div v-if="showLabel" class="flex items-center justify-between mb-1.5">
      <span :class="textSizeClass" :style="{ color: 'var(--text-secondary)' }">{{ label }}</span>
      <span class="text-xs font-semibold" :style="{ color: 'var(--text-secondary)' }">{{ percent }}%</span>
    </div>
    <div class="h-2 rounded-full overflow-hidden" :style="{ background: 'var(--bg-hover)' }">
      <div
        class="h-full rounded-full transition-all duration-500"
        :class="[animated ? 'animate-pulse-soft' : '']"
        :style="{ width: percent + '%', background: barColor }"
      ></div>
    </div>
  </div>

  <!-- Inline Loading -->
  <div v-else class="inline-flex items-center gap-2" :class="classes">
    <div class="sk-animate-spin w-4 h-4 border-2 border-current border-t-transparent rounded-full" :style="{ color }"></div>
    <span v-if="text" :class="textSizeClass" :style="{ color: 'var(--text-secondary)' }">{{ text }}</span>
  </div>
</template>

<script setup>
import { computed } from 'vue';

/**
 * Enterprise Loading — spinner, skeleton, overlay, progress, inline.
 *
 * @example
 * <SkLoading variant="spinner" size="lg" text="Memuat data..." />
 * <SkLoading variant="skeleton" type="table" :count="5" />
 * <SkLoading variant="overlay" text="Menyimpan..." />
 * <SkLoading variant="progress" :percent="65" label="Uploading" />
 * <SkLoading text="Loading..." />  <!-- inline -->
 */
const props = defineProps({
  variant: { type: String, default: 'inline' }, // spinner | skeleton | overlay | progress | inline
  size: { type: String, default: 'md' },        // sm | md | lg
  text: { type: String, default: '' },
  // Skeleton
  type: { type: String, default: 'text' },       // text | card | table | stat | circle
  count: { type: Number, default: 3 },
  width: { type: String, default: '' },
  height: { type: String, default: '' },
  // Progress
  percent: { type: Number, default: 0 },
  label: { type: String, default: '' },
  showLabel: { type: Boolean, default: true },
  color: { type: String, default: 'var(--primary)' },
  animated: { type: Boolean, default: false },
  extraClass: { type: String, default: '' },
});

const spinnerSizeClass = computed(() => ({
  sm: 'w-4 h-4', md: 'w-6 h-6', lg: 'w-8 h-8',
}[props.size] || 'w-6 h-6'));

const textSizeClass = computed(() => ({
  sm: 'text-xs', md: 'text-sm', lg: 'text-sm',
}[props.size] || 'text-sm'));

const barColor = computed(() => props.color || 'var(--primary)');

const classes = computed(() => [
  props.extraClass,
].filter(Boolean).join(' '));
</script>

<style scoped>
.skeleton-pulse {
  background: linear-gradient(90deg, var(--bg-hover) 25%, var(--border-light) 50%, var(--bg-hover) 75%);
  background-size: 200% 100%;
  animation: sk-skeleton 1.5s ease-in-out infinite;
}
</style>
