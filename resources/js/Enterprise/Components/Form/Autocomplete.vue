<template>
  <div :class="wrapperClass">
    <div class="relative">
      <input
        ref="inputRef"
        :value="modelValue"
        type="text"
        :placeholder="placeholder"
        :disabled="disabled"
        :class="inputClasses"
        :style="inputStyle"
        @input="onInput"
        @focus="showDropdown = true"
        @blur="onBlur"
        @keydown="onKeydown"
      />
      <!-- Clear button -->
      <button
        v-if="modelValue && clearable"
        @mousedown.prevent="clear"
        class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 rounded-full flex items-center justify-center"
        :style="{ color: 'var(--text-muted)' }"
      >
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
      </button>
    </div>

    <!-- Dropdown -->
    <div
      v-if="showDropdown && filteredOptions.length > 0"
      class="absolute z-50 mt-1 w-full rounded-xl border shadow-lg overflow-hidden"
      :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)', maxHeight: maxHeight }"
    >
      <div class="overflow-y-auto py-1" :style="{ maxHeight: maxHeight }">
        <div
          v-for="(option, i) in filteredOptions"
          :key="optionKey ? option[optionKey] : i"
          class="px-4 py-2.5 text-sm cursor-pointer transition-colors flex items-center gap-2"
          :class="{ 'bg-indigo-50': highlightedIndex === i }"
          :style="{ color: 'var(--text-primary)' }"
          @mousedown.prevent="select(option)"
          @mouseenter="highlightedIndex = i"
        >
          <slot name="option" :option="option">
            {{ optionLabel ? option[optionLabel] : option }}
          </slot>
        </div>
      </div>
    </div>

    <!-- No results -->
    <div
      v-if="showDropdown && modelValue && filteredOptions.length === 0 && !loading"
      class="absolute z-50 mt-1 w-full rounded-xl border shadow-lg px-4 py-3 text-center"
      :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }"
    >
      <p class="sk-caption">Tidak ada hasil untuk "{{ modelValue }}"</p>
    </div>

    <!-- Loading -->
    <div
      v-if="showDropdown && loading"
      class="absolute z-50 mt-1 w-full rounded-xl border shadow-lg px-4 py-3 text-center"
      :style="{ background: 'var(--bg-card)', borderColor: 'var(--border-color)' }"
    >
      <div class="sk-animate-spin w-5 h-5 border-2 border-indigo-500 border-t-transparent rounded-full mx-auto"></div>
    </div>

    <p v-if="error" class="sk-error mt-1.5">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';

/**
 * Enterprise Autocomplete — input dengan suggestion dropdown.
 * Mendukung async search via `searchFn` atau local filtering.
 *
 * @example
 * <SkAutocomplete
 *   v-model="selected"
 *   :options="customers"
 *   optionLabel="name"
 *   optionKey="id"
 *   placeholder="Cari pelanggan..."
 * />
 */
const props = defineProps({
  modelValue: { type: [String, Number, Object], default: '' },
  options: { type: Array, default: () => [] },
  optionLabel: { type: String, default: 'label' },
  optionKey: { type: String, default: 'id' },
  placeholder: { type: String, default: 'Cari...' },
  disabled: { type: Boolean, default: false },
  clearable: { type: Boolean, default: true },
  loading: { type: Boolean, default: false },
  error: { type: String, default: '' },
  searchFn: { type: Function, default: null },
  maxHeight: { type: String, default: '240px' },
  wrapperClass: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'select']);
const inputRef = ref(null);
const showDropdown = ref(false);
const highlightedIndex = ref(-1);

// Sync modelValue to display text
const displayText = ref('');

watch(() => props.modelValue, (val) => {
  if (val && typeof val === 'object' && props.optionLabel) {
    displayText.value = val[props.optionLabel] || '';
  } else if (val && typeof val === 'string') {
    displayText.value = val;
  }
}, { immediate: true });

const onInput = (e) => {
  displayText.value = e.target.value;
  emit('update:modelValue', e.target.value);
  showDropdown.value = true;
  highlightedIndex.value = -1;
  if (props.searchFn) props.searchFn(e.target.value);
};

const filteredOptions = computed(() => {
  if (!displayText.value) return props.options.slice(0, 20);
  const q = displayText.value.toLowerCase();
  return props.options.filter(o => {
    const label = props.optionLabel ? o[props.optionLabel] : o;
    return label && String(label).toLowerCase().includes(q);
  }).slice(0, 20);
});

const select = (option) => {
  const label = props.optionLabel ? option[props.optionLabel] : option;
  displayText.value = label;
  emit('update:modelValue', option);
  emit('select', option);
  showDropdown.value = false;
};

const clear = () => {
  displayText.value = '';
  emit('update:modelValue', '');
  showDropdown.value = false;
};

const onBlur = () => {
  setTimeout(() => { showDropdown.value = false; }, 150);
};

const onKeydown = (e) => {
  if (!showDropdown.value) return;
  if (e.key === 'ArrowDown') {
    e.preventDefault();
    highlightedIndex.value = Math.min(highlightedIndex.value + 1, filteredOptions.value.length - 1);
  } else if (e.key === 'ArrowUp') {
    e.preventDefault();
    highlightedIndex.value = Math.max(highlightedIndex.value - 1, 0);
  } else if (e.key === 'Enter' && highlightedIndex.value >= 0) {
    e.preventDefault();
    select(filteredOptions.value[highlightedIndex.value]);
  } else if (e.key === 'Escape') {
    showDropdown.value = false;
  }
};

const inputClasses = computed(() => [
  'w-full rounded-xl border transition-all duration-200 outline-none',
  'h-11 text-sm px-3.5',
  'pr-8', // space for clear button
  props.error ? 'border-red-300' : '',
  'focus:border-indigo-400 focus:ring-2 focus:ring-indigo-50',
  props.disabled ? 'opacity-50 cursor-not-allowed' : '',
].filter(Boolean).join(' '));

const inputStyle = computed(() => ({
  background: props.disabled ? 'var(--bg-hover)' : 'var(--bg-input)',
  color: 'var(--text-primary)',
  borderColor: props.error ? 'var(--danger)' : 'var(--border-color)',
}));

defineExpose({ focus: () => inputRef.value?.focus(), blur: () => inputRef.value?.blur() });
</script>
