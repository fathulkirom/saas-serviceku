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
  default: { bg: 'var(--bg-hover)', color: 'var(--text-muted)', dot: 'var(--text-muted)' },
  yellow: { bg: 'var(--warning-bg)', color: 'var(--warning-text)', dot: 'var(--warning)' },
  orange: { bg: 'rgba(249,115,22,0.12)', color: '#ea580c', dot: '#f97316' },
  green: { bg: 'var(--success-bg)', color: 'var(--success-text)', dot: 'var(--success)' },
  red: { bg: 'var(--danger-bg)', color: 'var(--danger-text)', dot: 'var(--danger)' },
  pink: { bg: 'rgba(244,63,94,0.12)', color: '#e11d48', dot: '#f43f5e' },
  blue: { bg: 'var(--info-bg)', color: 'var(--info-text)', dot: 'var(--info)' },
  purple: { bg: 'var(--accent-light)', color: 'var(--accent-primary)', dot: 'var(--accent-primary)' },
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
