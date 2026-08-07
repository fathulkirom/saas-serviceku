<template>
  <div :class="classes" :style="cardStyle">
    <!-- Decorative accent bar (top) -->
    <div v-if="accent" class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl" :style="{ background: accentColorVar }"></div>

    <!-- Header -->
    <div v-if="title || $slots.header" class="flex items-center justify-between" :class="headerPadding">
      <div class="flex items-center gap-2.5 min-w-0">
        <div v-if="icon || $slots.icon" class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" :style="{ background: iconBg, color: iconColor }">
          <slot name="icon">
            <span class="text-base" v-html="icon"></span>
          </slot>
        </div>
        <div class="min-w-0">
          <h3 class="sk-label truncate" v-if="title">{{ title }}</h3>
          <p v-if="subtitle" class="sk-caption mt-0.5">{{ subtitle }}</p>
        </div>
      </div>
      <div v-if="$slots.action" class="flex-shrink-0 ml-3">
        <slot name="action" />
      </div>
    </div>

    <!-- Content -->
    <div :class="contentPadding">
      <slot />
    </div>

    <!-- Footer -->
    <div v-if="$slots.footer" class="px-5 py-3 border-t" :style="{ borderColor: 'var(--border-light)' }">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

/**
 * Enterprise Card — base card component.
 *
 * Variants:
 *   default — white card with border
 *   glass   — glassmorphism card
 *   danger  — red-tinted card
 *
 * Sizes:
 *   sm  — compact padding
 *   md  — default padding
 *   lg  — large padding
 *
 * @example
 * <SkCard title="Ringkasan" subtitle="Hari ini" icon="📊" accent="primary">
 *   <p>Content here</p>
 *   <template #action><SkButton size="sm">Lihat</SkButton></template>
 *   <template #footer>Footer content</template>
 * </SkCard>
 */
const props = defineProps({
  title: { type: String, default: '' },
  subtitle: { type: String, default: '' },
  icon: { type: String, default: '' },
  variant: { type: String, default: 'default' }, // default | glass | danger
  size: { type: String, default: 'md' },         // sm | md | lg
  accent: { type: String, default: '' },          // primary | success | warning | danger | info
  hover: { type: Boolean, default: false },
  extraClass: { type: String, default: '' },
});

const variantClasses = {
  default: 'card',
  glass: 'card-glass',
  danger: 'card',
};

const sizePadding = {
  sm: { header: 'px-4 pt-4', content: 'px-4 pb-4 pt-3' },
  md: { header: 'px-5 pt-5', content: 'px-5 pb-5 pt-4' },
  lg: { header: 'px-6 pt-6', content: 'px-6 pb-6 pt-5' },
};

const accentColors = {
  primary: 'var(--primary)',
  success: 'var(--success)',
  warning: 'var(--warning)',
  danger: 'var(--danger)',
  info: 'var(--info)',
};

const classes = computed(() => [
  variantClasses[props.variant] || variantClasses.default,
  'relative overflow-hidden',
  props.hover ? 'hover:shadow-md hover:-translate-y-0.5 cursor-pointer' : '',
  props.extraClass,
].filter(Boolean).join(' '));

const { header: headerPadding, content: contentPadding } = sizePadding[props.size] || sizePadding.md;

const accentColorVar = computed(() => accentColors[props.accent] || 'var(--primary)');
const iconColor = computed(() => accentColors[props.accent] || 'var(--primary)');
const iconBg = computed(() => props.accent ? `color-mix(in srgb, ${accentColorVar.value} 10%, transparent)` : 'var(--bg-hover)');

const cardStyle = computed(() => {
  if (props.variant === 'danger') {
    return { borderColor: 'var(--danger-soft-border)', background: 'var(--danger-soft)' };
  }
  return {};
});
</script>
