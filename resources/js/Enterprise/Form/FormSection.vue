<template>
  <div class="rounded-2xl border overflow-hidden" :style="{ borderColor: 'var(--border-color)', background: 'var(--bg-card)' }">
    <!-- Section Header -->
    <div
      v-if="section.label"
      class="flex items-center justify-between px-5 py-3.5 border-b cursor-pointer select-none"
      :class="{ 'cursor-pointer': section.collapsible }"
      :style="{ borderColor: 'var(--border-light)' }"
      @click="section.collapsible && (collapsed = !collapsed)"
    >
      <div class="flex items-center gap-2">
        <span v-if="section.icon" class="text-base">{{ section.icon }}</span>
        <h3 class="text-sm font-bold" :style="{ color: 'var(--text-primary)' }">{{ section.label }}</h3>
        <span v-if="section.description" class="sk-caption ml-2 hidden sm:inline">{{ section.description }}</span>
      </div>
      <svg
        v-if="section.collapsible"
        class="w-4 h-4 transition-transform duration-200"
        :class="{ 'rotate-180': !collapsed }"
        :style="{ color: 'var(--text-muted)' }"
        fill="none" stroke="currentColor" viewBox="0 0 24 24"
      >
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
      </svg>
    </div>

    <!-- Section Body -->
    <div v-show="!collapsed" :class="`px-5 py-4`">
      <div :class="`grid grid-cols-1 sm:grid-cols-${section.cols || 1} gap-4`">
        <FormField
          v-for="field in fields"
          :key="field.key"
          :field="field"
          :value="values[field.key]"
          :error="errors[field.key]"
          :disabled="isSubmitting"
          @update="(val) => $emit('update:field', field.key, val)"
          @blur="$emit('blur', field.key)"
        />
      </div>
      <div v-if="!fields.length" class="py-4 text-center">
        <p class="sk-caption">Tidak ada field di section ini.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import FormField from './FormField.vue';

const props = defineProps({
  section: { type: Object, default: () => ({}) },
  fields: { type: Array, default: () => [] },
  values: { type: Object, default: () => ({}) },
  errors: { type: Object, default: () => ({}) },
  isSubmitting: { type: Boolean, default: false },
});

defineEmits(['update:field', 'blur']);

const collapsed = ref(props.section?.collapsed || false);
</script>
