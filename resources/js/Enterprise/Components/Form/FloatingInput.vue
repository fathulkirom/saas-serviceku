<template>
  <div :class="wrapperClass">
    <div class="relative">
      <input
        ref="inputRef"
        :type="type"
        :value="modelValue"
        :placeholder="' '"
        :disabled="disabled"
        :readonly="readonly"
        :class="inputClasses"
        :style="inputStyle"
        @input="onInput"
        @focus="focused = true"
        @blur="focused = false"
      />
      <label
        :class="labelClasses"
        :style="labelStyle"
      >
        {{ label }}
        <span v-if="required" class="text-red-500 ml-0.5">*</span>
      </label>

      <!-- Right icon -->
      <div v-if="$slots.right" class="absolute right-3 top-1/2 -translate-y-1/2">
        <slot name="right" />
      </div>
    </div>

    <!-- Helper / Error -->
    <p v-if="error" class="sk-error mt-1.5">{{ error }}</p>
    <p v-else-if="helper" class="sk-helper mt-1.5">{{ helper }}</p>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';

/**
 * Enterprise Floating Label Input.
 * Label bergerak ke atas saat input terisi atau fokus.
 *
 * @example
 * <SkFloatingInput v-model="name" label="Nama Lengkap" required />
 * <SkFloatingInput v-model="email" label="Email" type="email" error="Email tidak valid" />
 */
const props = defineProps({
  modelValue: { type: [String, Number], default: '' },
  label: { type: String, required: true },
  type: { type: String, default: 'text' },
  placeholder: { type: String, default: '' },
  disabled: { type: Boolean, default: false },
  readonly: { type: Boolean, default: false },
  required: { type: Boolean, default: false },
  error: { type: String, default: '' },
  helper: { type: String, default: '' },
  size: { type: String, default: 'md' }, // sm | md | lg
  wrapperClass: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue']);
const focused = ref(false);
const inputRef = ref(null);

const hasValue = computed(() => props.modelValue !== '' && props.modelValue !== null && props.modelValue !== undefined);
const isActive = computed(() => focused.value || hasValue.value);

const sizeClasses = {
  sm: 'h-9 text-xs',
  md: 'h-11 text-sm',
  lg: 'h-13 text-sm',
};

const onInput = (e) => {
  emit('update:modelValue', e.target.value);
};

const inputClasses = computed(() => [
  'w-full rounded-xl border transition-all duration-200 outline-none',
  'pt-4 pb-1.5 px-3.5',
  sizeClasses[props.size] || sizeClasses.md,
  props.error ? 'border-red-300 focus:border-red-500 focus:ring-2 focus:ring-red-100' : '',
  !props.error ? 'focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50' : '',
  props.disabled ? 'opacity-50 cursor-not-allowed' : '',
].filter(Boolean).join(' '));

const inputStyle = computed(() => ({
  background: props.disabled ? 'var(--bg-hover)' : 'var(--bg-input)',
  color: 'var(--text-primary)',
  borderColor: props.error ? 'var(--danger)' : 'var(--border-color)',
}));

const labelClasses = computed(() => [
  'absolute left-3.5 transition-all duration-200 pointer-events-none select-none',
  'origin-left',
  isActive.value
    ? '-translate-y-2 scale-75 text-xs font-medium'
    : 'translate-y-0 scale-100 text-sm',
].filter(Boolean).join(' '));

const labelStyle = computed(() => ({
  color: isActive.value
    ? (props.error ? 'var(--danger)' : 'var(--primary)')
    : 'var(--text-muted)',
  top: isActive.value ? '0.625rem' : '50%',
  transform: isActive.value ? 'translateY(0) scale(0.75)' : 'translateY(-50%) scale(1)',
}));

defineExpose({ focus: () => inputRef.value?.focus(), blur: () => inputRef.value?.blur() });
</script>
