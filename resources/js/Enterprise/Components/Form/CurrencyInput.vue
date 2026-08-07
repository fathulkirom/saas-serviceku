<template>
  <div :class="wrapperClass">
    <div class="relative">
      <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm font-semibold select-none" :style="{ color: 'var(--text-muted)' }">
        {{ prefix || 'Rp' }}
      </span>
      <input
        ref="inputRef"
        :value="displayValue"
        type="text"
        inputmode="decimal"
        :placeholder="placeholder"
        :disabled="disabled"
        :class="inputClasses"
        :style="inputStyle"
        @input="onInput"
        @blur="onBlur"
        @focus="$emit('focus', $event)"
      />
    </div>
    <p v-if="error" class="sk-error mt-1.5">{{ error }}</p>
    <p v-else-if="helper" class="sk-helper mt-1.5">{{ helper }}</p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

/**
 * Enterprise Currency Input.
 * Format angka ke tampilan mata uang Indonesia, value asli tetap number.
 *
 * @example
 * <SkCurrencyInput v-model="price" placeholder="0" />
 * <SkCurrencyInput v-model="amount" prefix="$" error="Minimal Rp 10.000" />
 */
const props = defineProps({
  modelValue: { type: [Number, String], default: 0 },
  prefix: { type: String, default: 'Rp' },
  placeholder: { type: String, default: '0' },
  disabled: { type: Boolean, default: false },
  error: { type: String, default: '' },
  helper: { type: String, default: '' },
  wrapperClass: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'focus']);
const inputRef = ref(null);

const formatDisplay = (val) => {
  const num = Number(val);
  if (isNaN(num) || num === 0) return '';
  return new Intl.NumberFormat('id-ID').format(num);
};

const displayValue = ref(formatDisplay(props.modelValue));

watch(() => props.modelValue, (v) => {
  displayValue.value = formatDisplay(v);
});

const onInput = (e) => {
  // Remove non-digit
  const raw = e.target.value.replace(/[^0-9]/g, '');
  const num = parseInt(raw, 10) || 0;
  displayValue.value = raw ? new Intl.NumberFormat('id-ID').format(num) : '';
  emit('update:modelValue', num);
};

const onBlur = () => {
  const num = Number(props.modelValue);
  displayValue.value = formatDisplay(num);
};

const inputClasses = computed(() => [
  'w-full rounded-xl border transition-all duration-200 outline-none',
  'h-11 text-sm pl-14 pr-3.5',
  'focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50',
  props.error ? 'border-red-300' : '',
  props.disabled ? 'opacity-50 cursor-not-allowed' : '',
].filter(Boolean).join(' '));

const inputStyle = computed(() => ({
  background: props.disabled ? 'var(--bg-hover)' : 'var(--bg-input)',
  color: 'var(--text-primary)',
  borderColor: props.error ? 'var(--danger)' : 'var(--border-color)',
}));

defineExpose({ focus: () => inputRef.value?.focus() });
</script>
