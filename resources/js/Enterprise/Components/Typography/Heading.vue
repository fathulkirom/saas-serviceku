<template>
  <component :is="tag" :class="classes"><slot /></component>
</template>

<script setup>
import { computed } from 'vue';

/**
 * Enterprise Heading — typography heading component.
 * level: 1-6 (maps to h1-h6, sk-heading-1 through sk-heading-6)
 * gradient: applies gradient text
 * Props: level, gradient, extraClass
 *
 * @example
 * <SkHeading level="1">Dashboard</SkHeading>
 * <SkHeading level="2" gradient>Selamat Pagi, User</SkHeading>
 */
const props = defineProps({
  level: { type: [Number, String], default: 1 },
  gradient: { type: Boolean, default: false },
  extraClass: { type: String, default: '' },
});

const tag = computed(() => `h${Math.min(Math.max(Number(props.level), 1), 6)}`);

const classes = computed(() => [
  `sk-heading-${props.level}`,
  props.gradient ? 'sk-text-gradient' : '',
  props.extraClass,
].filter(Boolean).join(' '));
</script>
