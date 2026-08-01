<template>
  <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full font-bold leading-none transition-colors"
    :class="size === 'sm' ? 'text-[11px]' : 'text-xs'"
    :style="badgeStyle">
    <span v-if="dot" class="w-1.5 h-1.5 rounded-full flex-shrink-0" :style="{ background: dotColor }"></span>
    <slot>{{ label }}</slot>
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: { type: String, default: 'default' },
  status: { type: String, default: '' },
  variantBy: { type: String, default: 'service' },
  dot: { type: Boolean, default: false },
  size: { type: String, default: 'md' },
  label: { type: String, default: '' },
});

const colors = {
  default: { bg: '#f4f4f5', color: '#71717a', dot: '#a1a1aa' }, // zinc-100, zinc-500
  yellow: { bg: '#fef3c7', color: '#b45309', dot: '#f59e0b' }, // amber-100, amber-700
  orange: { bg: '#ffedd5', color: '#c2410c', dot: '#f97316' }, // orange-100, orange-700
  green: { bg: '#dcfce7', color: '#15803d', dot: '#22c55e' }, // green-100, green-700
  red: { bg: '#fee2e2', color: '#b91c1c', dot: '#ef4444' }, // red-100, red-700
  pink: { bg: '#fce7f3', color: '#be185d', dot: '#ec4899' }, // pink-100, pink-700
  blue: { bg: '#dbeafe', color: '#1d4ed8', dot: '#3b82f6' }, // blue-100, blue-700
  purple: { bg: '#e0e7ff', color: '#4338ca', dot: '#6366f1' }, // indigo-100, indigo-700
};

const statusVariantMap = {
  menunggu_alokasi: 'yellow', diterima: 'orange', dikerjakan: 'blue',
  indent: 'purple', onpartner: 'purple',
  menunggu_konfirmasi_pelanggan: 'red', menunggu_konfirmasi_internal: 'red',
  siap_diambil: 'green', selesai: 'green', diambil: 'green',
  cancel: 'red', void: 'default',
  lunas: 'green', paid: 'green', dp: 'yellow', partial: 'yellow',
  belum_bayar: 'red', unpaid: 'red', draft: 'yellow',
  tersedia: 'green', menipis: 'yellow', habis: 'red',
  active: 'green', suspended: 'yellow', trial: 'blue', expired: 'red',
};

const resolvedVariant = computed(() => {
  if (props.status && statusVariantMap[props.status]) {
    return statusVariantMap[props.status];
  }
  return props.variant;
});

const badgeStyle = computed(() => {
  const c = colors[resolvedVariant.value] || colors.default;
  return `background: ${c.bg}; color: ${c.color};`;
});

const dotColor = computed(() => {
  return (colors[resolvedVariant.value] || colors.default).dot;
});
</script>
