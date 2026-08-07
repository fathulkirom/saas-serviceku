<template>
  <p :class="classes"><slot /></p>
</template>

<script setup>
import { computed } from 'vue';

/**
 * Enterprise Text — paragraph / body text.
 * variant: 'body' | 'body-sm' | 'body-xs' | 'subtitle' | 'subtitle-sm' | 'caption' | 'helper' | 'error' | 'success' | 'label' | 'label-sm'
 * muted: shortcut to apply text-secondary color
 *
 * @example
 * <SkText variant="subtitle">Deskripsi singkat halaman ini.</SkText>
 * <SkText variant="error">Field ini wajib diisi.</SkText>
 */
const props = defineProps({
  variant: { type: String, default: 'body' },
  muted: { type: Boolean, default: false },
  extraClass: { type: String, default: '' },
});

const variantClassMap = {
  body: 'sk-body',
  'body-sm': 'sk-body-sm',
  'body-xs': 'sk-body-xs',
  subtitle: 'sk-subtitle',
  'subtitle-sm': 'sk-subtitle-sm',
  caption: 'sk-caption',
  helper: 'sk-helper',
  error: 'sk-error',
  success: 'sk-success',
  label: 'sk-label',
  'label-sm': 'sk-label-sm',
};

const classes = computed(() => [
  variantClassMap[props.variant] || 'sk-body',
  props.extraClass,
].filter(Boolean).join(' '));
</script>
