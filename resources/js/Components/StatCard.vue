<template>
  <div
    class="relative rounded-2xl border transition-all duration-300 group cursor-default"
    :class="[clickable ? 'hover:-translate-y-1 hover:shadow-[var(--shadow-glow)] cursor-pointer' : '', padded ? 'p-5 sm:p-6' : 'p-4', variantClass]"
    :style="variantStyle">
    <div class="absolute left-0 top-0 bottom-0 w-1 rounded-l-2xl transition-all duration-300" :style="{ background: colorHex }"></div>
    <div class="flex items-start gap-4">
      <div
        v-if="icon || $slots.icon || $slots.sparkline"
        class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm"
        :style="computedIconBg">
        <slot name="icon">
          <span v-if="iconSvg" class="w-5 h-5" v-html="iconSvg" :style="{ color: colorHex }"></span>
          <span v-else class="text-xl">{{ icon }}</span>
        </slot>
      </div>
      <div class="flex-1 min-w-0">
        <p class="text-xs font-semibold uppercase tracking-wider mb-1" :style="{ color: 'var(--text-muted)' }">{{ label }}</p>
        <div class="flex items-baseline gap-2">
          <p class="text-2xl sm:text-3xl font-bold tracking-tight" :style="{ color: 'var(--text-primary)' }">
            <slot name="value">{{ displayValue }}</slot>
          </p>
          <span
            v-if="trend !== undefined && trend !== 0"
            class="inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded-md"
            :style="{ background: trendBg, color: trendColor }">
            <svg v-if="trend > 0" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
            <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
            {{ Math.abs(trend) }}%
          </span>
        </div>
        <p v-if="subtext || $slots.subtext" class="text-xs mt-1" :style="{ color: 'var(--text-muted)' }">
          <slot name="subtext">{{ subtext }}</slot>
        </p>
      </div>
      <div v-if="$slots.action" class="flex-shrink-0">
        <slot name="action" />
      </div>
    </div>
    <div v-if="$slots.sparkline" class="mt-3">
      <slot name="sparkline" />
    </div>
    <div v-if="$slots.footer" class="mt-4 pt-4 border-t" :style="{ borderColor: 'var(--border-light)' }">
      <slot name="footer" />
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  label: { type: String, default: '' },
  value: { type: [String, Number], default: '' },
  icon: { type: String, default: '' },
  iconBg: { type: String, default: '' },
  subtext: { type: String, default: '' },
  trend: { type: Number, default: undefined },
  color: { type: String, default: 'primary' },
  clickable: { type: Boolean, default: false },
  padded: { type: Boolean, default: true },
  variant: { type: String, default: 'default' },
});

const colorMap = {
  primary: { hex: 'var(--accent-primary)', light: 'var(--accent-light)' },
  cyan: { hex: '#06b6d4', light: 'rgba(6,182,212,0.1)' },
  green: { hex: 'var(--success)', light: 'var(--success-bg)' },
  orange: { hex: 'var(--warning)', light: 'var(--warning-bg)' },
  red: { hex: 'var(--danger)', light: 'var(--danger-bg)' },
  blue: { hex: 'var(--info)', light: 'var(--info-bg)' },
  pink: { hex: '#ec4899', light: 'rgba(236,72,153,0.1)' },
};

const colorHex = computed(() => colorMap[props.color]?.hex || colorMap.primary.hex);
const colorLight = computed(() => colorMap[props.color]?.light || colorMap.primary.light);

const computedIconBg = computed(() => {
  if (props.iconBg) return { background: props.iconBg };
  return { background: `linear-gradient(135deg, ${colorHex.value}, ${colorHex.value}dd)` };
});

const variantClass = computed(() => props.variant === 'glass' ? 'glass' : '');
const variantStyle = computed(() => {
  if (props.variant === 'glass') return {};
  return { background: 'var(--bg-card)', borderColor: 'var(--border-color)' };
});

const displayValue = computed(() => {
  if (typeof props.value === 'number') {
    return new Intl.NumberFormat('id-ID').format(props.value);
  }
  return props.value;
});

const trendBg = computed(() => props.trend > 0 ? 'var(--success-bg)' : 'var(--danger-bg)');
const trendColor = computed(() => props.trend > 0 ? 'var(--success)' : 'var(--danger)');
</script>
