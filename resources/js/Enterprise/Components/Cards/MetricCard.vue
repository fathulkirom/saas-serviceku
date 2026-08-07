<template>
  <div
    :class="classes"
    @click="clickable && $emit('click')"
  >
    <div class="flex items-start gap-4">
      <!-- Icon -->
      <div
        v-if="icon || $slots.icon"
        class="w-11 h-11 rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm"
        :style="{ background: iconBg }"
      >
        <slot name="icon">
          <span v-if="iconSvg" class="w-5 h-5" v-html="iconSvg" :style="{ color: colorVar }"></span>
          <span v-else class="text-xl">{{ icon }}</span>
        </slot>
      </div>

      <!-- Content -->
      <div class="flex-1 min-w-0">
        <p class="sk-label-sm uppercase tracking-wider mb-1">{{ label }}</p>
        <div class="flex items-baseline gap-2">
          <p class="text-2xl sm:text-3xl font-bold tracking-tight" :style="{ color: 'var(--text-primary)' }">
            <slot name="value">{{ formattedValue }}</slot>
          </p>
          <!-- Trend Badge -->
          <span
            v-if="trendValue !== undefined && trendValue !== 0"
            class="inline-flex items-center gap-0.5 text-xs font-semibold px-1.5 py-0.5 rounded-md"
            :style="{ background: trendBg, color: trendColor }"
          >
            <svg v-if="trendValue > 0" class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
            <svg v-else class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
            </svg>
            {{ Math.abs(trendValue) }}%
          </span>
        </div>
        <p v-if="subtext || $slots.subtext" class="sk-caption mt-1">
          <slot name="subtext">{{ subtext }}</slot>
        </p>
      </div>

      <!-- Action slot -->
      <div v-if="$slots.action" class="flex-shrink-0">
        <slot name="action" />
      </div>
    </div>

    <!-- Sparkline -->
    <div v-if="$slots.sparkline" class="mt-3">
      <slot name="sparkline" />
    </div>

    <!-- Progress -->
    <div v-if="progress !== undefined" class="mt-3">
      <div class="flex items-center justify-between mb-1">
        <span class="sk-caption">{{ progressLabel }}</span>
        <span class="sk-caption">{{ progress }}%</span>
      </div>
      <div class="h-1.5 rounded-full" :style="{ background: 'var(--bg-hover)' }">
        <div
          class="h-full rounded-full transition-all duration-500"
          :style="{ width: progress + '%', background: colorVar }"
        ></div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useFormatter } from '@/Composables/useFormatter.js';

/**
 * Enterprise Metric Card — untuk menampilkan KPI / metrik.
 *
 * @example
 * <SkMetricCard
 *   label="Pendapatan Hari Ini"
 *   :value="15000000"
 *   format="currency"
 *   trend="+12.5"
 *   color="success"
 *   icon="💰"
 * />
 */
const props = defineProps({
  label: { type: String, required: true },
  value: { type: [Number, String], default: 0 },
  format: { type: String, default: 'number' }, // number | currency | decimal | percent
  trend: { type: [Number, String], default: undefined },
  subtext: { type: String, default: '' },
  icon: { type: String, default: '' },
  iconSvg: { type: String, default: '' },
  color: { type: String, default: 'primary' }, // primary | success | warning | danger | info
  progress: { type: Number, default: undefined },
  progressLabel: { type: String, default: '' },
  clickable: { type: Boolean, default: false },
  extraClass: { type: String, default: '' },
});

defineEmits(['click']);

const { formatNumber, formatCurrency } = useFormatter();

const colorMap = {
  primary: 'var(--primary)',
  success: 'var(--success)',
  warning: 'var(--warning)',
  danger: 'var(--danger)',
  info: 'var(--info)',
};

const colorVar = computed(() => colorMap[props.color] || colorMap.primary);

const iconBg = computed(() => {
  return `color-mix(in srgb, ${colorVar.value} 10%, transparent)`;
});

const trendValue = computed(() => {
  if (props.trend === undefined || props.trend === null) return undefined;
  return Number(props.trend);
});

const trendBg = computed(() => {
  if (trendValue.value === undefined) return 'transparent';
  return trendValue.value > 0 ? 'var(--success-soft)' : 'var(--danger-soft)';
});

const trendColor = computed(() => {
  if (trendValue.value === undefined) return 'transparent';
  return trendValue.value > 0 ? 'var(--success-text)' : 'var(--danger-text)';
});

const formattedValue = computed(() => {
  const v = Number(props.value);
  if (isNaN(v)) return props.value;
  if (props.format === 'currency') return formatCurrency(v);
  if (props.format === 'decimal') return v.toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  if (props.format === 'percent') return v + '%';
  return formatNumber(v);
});

const classes = computed(() => [
  'relative rounded-2xl border transition-all duration-300 group',
  'p-5 sm:p-6',
  props.clickable ? 'hover:-translate-y-1 hover:shadow-lg cursor-pointer' : '',
  props.extraClass,
].filter(Boolean).join(' '));
</script>
