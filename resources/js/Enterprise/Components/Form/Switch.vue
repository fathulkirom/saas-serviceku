<template>
  <label :class="wrapperClass" class="inline-flex items-center gap-3 cursor-pointer select-none group">
    <div class="relative">
      <input
        type="checkbox"
        :checked="modelValue"
        :disabled="disabled"
        class="sr-only"
        @change="onToggle"
      />
      <div
        :class="trackClasses"
        :style="trackStyle"
      >
        <div
          :class="thumbClasses"
          :style="thumbStyle"
        >
          <!-- Icon inside thumb -->
          <svg v-if="modelValue && showCheck" class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
          </svg>
          <svg v-else-if="!modelValue && showCheck" class="w-2.5 h-2.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
          </svg>
        </div>
      </div>
    </div>

    <!-- Label -->
    <span v-if="label || $slots.label" :class="labelClasses">
      <slot name="label">{{ label }}</slot>
    </span>

    <!-- Description -->
    <span v-if="description" class="sk-caption">
      {{ description }}
    </span>
  </label>
</template>

<script setup>
import { computed } from 'vue';

/**
 * Enterprise Switch / Toggle component.
 *
 * @example
 * <SkSwitch v-model="enabled" label="Notifikasi Email" />
 * <SkSwitch v-model="dark" label="Dark Mode" size="sm" color="success" />
 */
const props = defineProps({
  modelValue: { type: Boolean, default: false },
  label: { type: String, default: '' },
  description: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  size: { type: String, default: 'md' },       // sm | md | lg
  color: { type: String, default: 'primary' },  // primary | success | danger | warning
  showCheck: { type: Boolean, default: false },
  wrapperClass: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);

const colors = {
  primary: 'var(--primary)',
  success: 'var(--success)',
  danger: 'var(--danger)',
  warning: 'var(--warning)',
};

const activeColor = computed(() => colors[props.color] || colors.primary);

const sizeMap = {
  sm: { track: 'w-8 h-5', thumb: 'w-3.5 h-3.5', translate: 'translate-x-3.5' },
  md: { track: 'w-10 h-6', thumb: 'w-4.5 h-4.5', translate: 'translate-x-[18px]' },
  lg: { track: 'w-12 h-7', thumb: 'w-5.5 h-5.5', translate: 'translate-x-[22px]' },
};

const currentSize = computed(() => sizeMap[props.size] || sizeMap.md);

const trackClasses = computed(() => [
  'rounded-full transition-all duration-200 flex items-center',
  currentSize.value.track,
  props.disabled ? 'opacity-40 cursor-not-allowed' : '',
].filter(Boolean).join(' '));

const trackStyle = computed(() => ({
  background: props.modelValue ? activeColor.value : 'var(--border-color)',
}));

const thumbClasses = computed(() => [
  'rounded-full transition-all duration-200 shadow-sm flex items-center justify-center',
  'transform',
  props.modelValue ? currentSize.value.translate : 'translate-x-0.5',
  currentSize.value.thumb,
].filter(Boolean).join(' '));

const thumbStyle = computed(() => ({
  background: 'white',
}));

const labelClasses = computed(() => [
  'text-sm font-medium',
  props.disabled ? 'opacity-50' : '',
].filter(Boolean).join(' '));

const onToggle = (e) => {
  emit('update:modelValue', e.target.checked);
};
</script>
