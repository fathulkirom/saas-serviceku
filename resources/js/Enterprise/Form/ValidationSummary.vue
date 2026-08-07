<template>
  <div v-if="hasErrors" class="rounded-xl border p-4 flex items-start gap-3"
    :style="{ borderColor: 'var(--danger-soft-border)', background: 'var(--danger-soft)' }">
    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" :style="{ color: 'var(--danger)' }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/>
    </svg>
    <div class="flex-1 min-w-0">
      <p class="text-sm font-bold mb-1" :style="{ color: 'var(--danger-text)' }">{{ errorCount }} kesalahan perlu diperbaiki:</p>
      <ul class="space-y-0.5">
        <li v-for="(msgs, field) in errors" :key="field" class="text-xs" :style="{ color: 'var(--danger-text)' }">
          <template v-if="Array.isArray(msgs)">{{ msgs[0] }}</template>
          <template v-else>{{ msgs }}</template>
        </li>
      </ul>
    </div>
    <button @click="$emit('clear')" class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0" :style="{ color: 'var(--danger)' }">
      <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
    </button>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  errors: { type: Object, default: () => ({}) },
});

defineEmits(['clear']);

const errorEntries = computed(() => Object.entries(props.errors).filter(([, v]) => {
  if (Array.isArray(v)) return v.length > 0;
  return !!v;
}));

const hasErrors = computed(() => errorEntries.value.length > 0);
const errorCount = computed(() => errorEntries.value.length);
</script>
