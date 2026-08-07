<template>
  <div :class="`col-span-${field.cols || 1}`">
    <!-- Label -->
    <label v-if="field.label && field.type !== 'checkbox' && field.type !== 'switch'" class="block mb-1.5">
      <span class="sk-label-sm">{{ field.label }}</span>
      <span v-if="field.required" class="text-red-500 ml-0.5">*</span>
    </label>

    <!-- Dynamic Field Component -->
    <component
      :is="fieldComponent"
      v-if="fieldComponent"
      v-bind="fieldProps"
      :modelValue="value"
      :error="errorText"
      :disabled="disabled || field.disabled"
      :readonly="field.readonly"
      @update:modelValue="(v) => $emit('update', v)"
      @blur="$emit('blur')"
    />

    <!-- Fallback for unknown field types -->
    <div v-else class="px-3 py-2 rounded-lg border text-xs" :style="{ borderColor: 'var(--border-color)', color: 'var(--text-muted)' }">
      Unknown field type: {{ field.type }}
      <input
        :value="value"
        @input="$emit('update', $event.target.value)"
        class="w-full mt-1 px-2 py-1 rounded border text-sm"
        :style="{ borderColor: 'var(--border-color)' }"
      />
    </div>

    <!-- Helper / Error -->
    <p v-if="errorText" class="sk-error mt-1">{{ errorText }}</p>
    <p v-else-if="field.helper" class="sk-helper mt-1">{{ field.helper }}</p>
    <p v-else-if="field.description" class="sk-helper mt-1">{{ field.description }}</p>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { fieldRegistry } from '@/Enterprise/Form/FormRegistry.js';

const props = defineProps({
  field: { type: Object, required: true },
  value: { default: null },
  error: { default: null },
  disabled: { type: Boolean, default: false },
});

defineEmits(['update', 'blur']);

const fieldComponent = computed(() => fieldRegistry.getComponent(props.field.type));

const errorText = computed(() => {
  if (!props.error) return null;
  return Array.isArray(props.error) ? props.error[0] : props.error;
});

const fieldProps = computed(() => {
  const defaults = fieldRegistry.getDefaults(props.field.type);
  const { key, label, type, hidden, ...rest } = props.field;
  return {
    ...defaults,
    ...rest,
    placeholder: props.field.placeholder || props.field.label || '',
    id: props.field.key,
    name: props.field.key,
  };
});
</script>
